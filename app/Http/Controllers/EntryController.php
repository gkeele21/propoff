<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Entry;
use App\Models\UserAnswer;
use App\Models\GroupQuestionAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class EntryController extends Controller
{
    /**
     * Display a listing of user's entries.
     */
    public function index()
    {
        $entries = Entry::with(['event', 'group', 'submittedByCaptain'])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(15);

        return Inertia::render('Entries/Index', [
            'entries' => $entries,
        ]);
    }

    /**
     * Start a new entry for an event.
     */
    public function start(Request $request, Event $event)
    {
        // Check if event is playable
        if ($event->status !== 'open') {
            return back()->with('error', 'This event is not currently available.');
        }

        if ($event->lock_date && $event->lock_date->isPast()) {
            return back()->with('error', 'This event is locked.');
        }

        $validated = $request->validate([
            'group_id' => 'required|exists:groups,id',
        ]);

        // Check if user is a member of the selected group
        $group = \App\Models\Group::findOrFail($validated['group_id']);
        if (!auth()->user()->groups()->where('groups.id', $group->id)->exists()) {
            return back()->with('error', 'You are not a member of the selected group.');
        }

        // Check if group belongs to this event
        if ($group->event_id !== $event->id) {
            return back()->with('error', 'This group is not participating in this event.');
        }

        // Check if user already has an incomplete entry
        $existingEntry = Entry::where('event_id', $event->id)
            ->where('user_id', auth()->id())
            ->where('group_id', $validated['group_id'])
            ->where('is_complete', false)
            ->first();

        if ($existingEntry) {
            return redirect()->route('entries.continue', $existingEntry);
        }

        // Check if user already has a completed entry
        $completedEntry = Entry::where('event_id', $event->id)
            ->where('user_id', auth()->id())
            ->where('group_id', $validated['group_id'])
            ->where('is_complete', true)
            ->first();

        if ($completedEntry) {
            return redirect()->route('entries.show', $completedEntry)
                ->with('info', 'You have already completed this event.');
        }

        // Calculate possible points from group's active questions
        $possiblePoints = $group->groupQuestions()
            ->where('is_active', true)
            ->sum('points');

        // Create new entry
        $entry = Entry::create([
            'event_id' => $event->id,
            'user_id' => auth()->id(),
            'group_id' => $validated['group_id'],
            'total_score' => 0,
            'possible_points' => $possiblePoints,
            'percentage' => 0,
            'is_complete' => false,
        ]);

        return redirect()->route('entries.continue', $entry);
    }

    /**
     * Continue working on an incomplete entry.
     */
    public function continue(Entry $entry)
    {
        $this->authorize('update', $entry);

        if ($entry->is_complete) {
            return redirect()->route('entries.show', $entry);
        }

        $entry->load([
            'event',
            'group.groupQuestions' => function ($query) {
                $query->where('is_active', true)
                    ->orderBy('display_order');
            },
            'userAnswers',
        ]);

        return Inertia::render('Entries/Continue', [
            'entry' => $entry,
        ]);
    }

    /**
     * Save answers for an entry.
     */
    public function saveAnswers(Request $request, Entry $entry)
    {
        $this->authorize('update', $entry);

        if ($entry->is_complete) {
            return back()->with('error', 'This entry is already complete.');
        }

        $validated = $request->validate([
            'answers' => 'required|array',
            'answers.*.group_question_id' => 'required|exists:group_questions,id',
            'answers.*.answer_text' => 'required|string',
        ]);

        DB::transaction(function () use ($entry, $validated) {
            foreach ($validated['answers'] as $answerData) {
                // Get the group question
                $groupQuestion = $entry->group->groupQuestions()
                    ->findOrFail($answerData['group_question_id']);

                $isCorrect = false;
                $pointsEarned = 0;

                // Check if there's a correct answer set by the captain/admin
                $groupAnswer = GroupQuestionAnswer::where('group_id', $entry->group_id)
                    ->where('group_question_id', $groupQuestion->id)
                    ->first();

                if ($groupAnswer) {
                    $isCorrect = strcasecmp(
                        trim($answerData['answer_text']),
                        trim($groupAnswer->correct_answer)
                    ) === 0;

                    $pointsEarned = $isCorrect ? $groupQuestion->points : 0;
                }

                // Update or create the user answer
                UserAnswer::updateOrCreate(
                    [
                        'entry_id' => $entry->id,
                        'group_question_id' => $answerData['group_question_id'],
                    ],
                    [
                        'answer_text' => $answerData['answer_text'],
                        'points_earned' => $pointsEarned,
                        'is_correct' => $isCorrect,
                    ]
                );
            }
        });

        return back()->with('success', 'Answers saved!');
    }

    /**
     * Submit/complete an entry.
     */
    public function submit(Entry $entry)
    {
        $this->authorize('update', $entry);

        if ($entry->is_complete) {
            return back()->with('error', 'This entry is already complete.');
        }

        // Calculate total score
        $totalScore = $entry->userAnswers()->sum('points_earned');
        $percentage = $entry->possible_points > 0
            ? ($totalScore / $entry->possible_points) * 100
            : 0;

        $entry->update([
            'total_score' => $totalScore,
            'percentage' => $percentage,
            'is_complete' => true,
            'submitted_at' => now(),
        ]);

        // Update leaderboard
        $this->updateLeaderboard($entry);

        return redirect()->route('entries.show', $entry)
            ->with('success', 'Entry completed!');
    }

    /**
     * Display the specified entry.
     */
    public function show(Entry $entry)
    {
        $this->authorize('view', $entry);

        $entry->load([
            'event',
            'group.groupQuestions' => function ($query) {
                $query->orderBy('display_order');
            },
            'userAnswers.groupQuestion',
            'user',
            'submittedByCaptain',
        ]);

        // Add correct answers to each group question based on grading source
        $group = $entry->group;

        if ($group->grading_source === 'captain') {
            // Load captain answers
            $captainAnswers = \App\Models\GroupQuestionAnswer::where('group_id', $group->id)
                ->get()
                ->keyBy('group_question_id');

            foreach ($entry->group->groupQuestions as $question) {
                $answer = $captainAnswers->get($question->id);
                $question->correct_answer = $answer ? $answer->correct_answer : null;
                $question->is_void = $answer ? $answer->is_void : false;
            }
        } else {
            // Load admin answers
            $adminAnswers = \App\Models\EventAnswer::where('event_id', $entry->event_id)
                ->get()
                ->keyBy('event_question_id');

            foreach ($entry->group->groupQuestions as $question) {
                if ($question->event_question_id) {
                    $answer = $adminAnswers->get($question->event_question_id);
                    $question->correct_answer = $answer ? $answer->correct_answer : null;
                    $question->is_void = $answer ? $answer->is_void : false;
                } else {
                    $question->correct_answer = null;
                    $question->is_void = false;
                }
            }
        }

        return Inertia::render('Entries/Show', [
            'entry' => $entry,
        ]);
    }

    /**
     * Update the leaderboard for an entry.
     */
    protected function updateLeaderboard(Entry $entry)
    {
        $answeredCount = $entry->userAnswers()->count();

        // Calculate rank within the group for this event
        $rank = \App\Models\Leaderboard::where('event_id', $entry->event_id)
            ->where('group_id', $entry->group_id)
            ->where(function ($query) use ($entry) {
                $query->where('total_score', '>', $entry->total_score)
                    ->orWhere(function ($q) use ($entry) {
                        $q->where('total_score', '=', $entry->total_score)
                            ->where('percentage', '>', $entry->percentage);
                    });
            })
            ->count() + 1;

        $leaderboard = \App\Models\Leaderboard::updateOrCreate(
            [
                'event_id' => $entry->event_id,
                'user_id' => $entry->user_id,
                'group_id' => $entry->group_id,
            ],
            [
                'rank' => $rank,
                'total_score' => $entry->total_score,
                'possible_points' => $entry->possible_points,
                'percentage' => $entry->percentage,
                'answered_count' => $answeredCount,
            ]
        );

        // Recalculate ranks for all entries in this group/event
        $this->recalculateRanks($entry->event_id, $entry->group_id);
    }

    /**
     * Recalculate ranks for all users in a group for an event.
     */
    protected function recalculateRanks($eventId, $groupId)
    {
        $leaderboards = \App\Models\Leaderboard::where('event_id', $eventId)
            ->where('group_id', $groupId)
            ->orderBy('total_score', 'desc')
            ->orderBy('percentage', 'desc')
            ->get();

        $currentRank = 1;
        foreach ($leaderboards as $leaderboard) {
            $leaderboard->update(['rank' => $currentRank]);
            $currentRank++;
        }
    }

    /**
     * Delete an entry (only if incomplete).
     */
    public function destroy(Entry $entry)
    {
        $this->authorize('delete', $entry);

        if ($entry->is_complete) {
            return back()->with('error', 'Cannot delete a completed entry.');
        }

        $entry->delete();

        return redirect()->route('entries.index')
            ->with('success', 'Entry deleted.');
    }

    /**
     * Show entry confirmation page with personal link
     */
    public function confirmation(Entry $entry)
    {
        // Verify user owns this entry
        if ($entry->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to entry.');
        }

        $entry->load(['event', 'group', 'user']);

        // Get personal link for guests
        $personalLink = null;
        if (auth()->user()->isGuest() && auth()->user()->guest_token) {
            $personalLink = route('guest.results', auth()->user()->guest_token);
        }

        return Inertia::render('Entries/Confirmation', [
            'entry' => [
                'id' => $entry->id,
                'event_id' => $entry->event_id,
                'total_score' => $entry->total_score,
                'possible_points' => $entry->possible_points,
                'percentage' => $entry->percentage,
                'submitted_at' => $entry->submitted_at,
            ],
            'event' => [
                'id' => $entry->event->id,
                'name' => $entry->event->name,
            ],
            'group' => [
                'id' => $entry->group->id,
                'name' => $entry->group->name,
            ],
            'personalLink' => $personalLink,
        ]);
    }
}
