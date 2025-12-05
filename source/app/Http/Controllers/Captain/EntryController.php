<?php

namespace App\Http\Controllers\Captain;

use App\Http\Controllers\Controller;
use App\Models\Entry;
use App\Models\Group;
use App\Models\User;
use App\Models\UserAnswer;
use App\Services\EntryService;
use App\Services\LeaderboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EntryController extends Controller
{
    protected $entryService;
    protected $leaderboardService;

    public function __construct(EntryService $entryService, LeaderboardService $leaderboardService)
    {
        $this->entryService = $entryService;
        $this->leaderboardService = $leaderboardService;
    }

    /**
     * Calculate initial possible points including max bonuses.
     */
    protected function calculateInitialPossiblePoints(Group $group): int
    {
        $possiblePoints = 0;
        $questions = $group->groupQuestions()->active()->get();

        foreach ($questions as $question) {
            $basePoints = $question->points;
            $maxBonus = 0;

            // For multiple choice, find the highest bonus
            if ($question->question_type === 'multiple_choice' && $question->options) {
                $options = is_string($question->options) ? json_decode($question->options, true) : $question->options;

                if (is_array($options)) {
                    foreach ($options as $option) {
                        if (is_array($option) && isset($option['points'])) {
                            $maxBonus = max($maxBonus, $option['points'] ?? 0);
                        }
                    }
                }
            }

            $possiblePoints += ($basePoints + $maxBonus);
        }

        return $possiblePoints;
    }

    /**
     * Show form to submit answers on behalf of a user.
     */
    public function edit(Group $group, User $user)
    {
        // Load event relationship for acceptingEntries check
        $group->load('event');

        // Check user is in group
        if (!$group->members()->where('user_id', $user->id)->exists()) {
            abort(404, 'User is not a member of this group.');
        }

        // Check if entry cutoff has passed
        if (!$group->acceptingEntries()) {
            return redirect()
                ->route('groups.members.index', $group)
                ->with('error', 'Entry cutoff has passed. Cannot submit or edit entries.');
        }

        // Get or create entry
        $entry = Entry::firstOrCreate(
            [
                'event_id' => $group->event_id,
                'user_id' => $user->id,
                'group_id' => $group->id,
            ],
            [
                'total_score' => 0,
                'possible_points' => $this->calculateInitialPossiblePoints($group),
                'percentage' => 0,
                'is_complete' => false,
            ]
        );

        // Load questions and existing answers
        $questions = $group->groupQuestions()
            ->active()
            ->orderBy('display_order')
            ->get()
            ->map(function ($question) use ($entry) {
                // Get user's existing answer if any
                $userAnswer = UserAnswer::where('entry_id', $entry->id)
                    ->where('group_question_id', $question->id)
                    ->first();

                return [
                    'id' => $question->id,
                    'question_text' => $question->question_text,
                    'question_type' => $question->question_type,
                    'options' => $question->options,
                    'points' => $question->points,
                    'order' => $question->display_order,
                    'is_custom' => $question->is_custom,
                    'user_answer' => $userAnswer ? [
                        'id' => $userAnswer->id,
                        'answer_text' => $userAnswer->answer_text,
                        'points_earned' => $userAnswer->points_earned,
                        'is_correct' => $userAnswer->is_correct,
                    ] : null,
                ];
            });

        return Inertia::render('Captain/SubmitForUser', [
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
                'grading_source' => $group->grading_source,
                'entry_cutoff' => $group->entry_cutoff,
                'accepting_entries' => $group->acceptingEntries(),
                'event' => [
                    'id' => $group->event->id,
                    'name' => $group->event->name,
                    'status' => $group->event->status,
                    'lock_date' => $group->event->lock_date,
                ],
            ],
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_guest' => $user->isGuest(),
            ],
            'entry' => [
                'id' => $entry->id,
                'is_complete' => $entry->is_complete,
                'submitted_at' => $entry->submitted_at,
                'submitted_by_captain_id' => $entry->submitted_by_captain_id,
                'total_score' => $entry->total_score,
                'possible_points' => $entry->possible_points,
                'percentage' => $entry->percentage,
            ],
            'questions' => $questions,
        ]);
    }

    /**
     * Submit/update answers on behalf of a user.
     */
    public function update(Request $request, Group $group, User $user)
    {
        // Load event relationship for acceptingEntries check
        $group->load('event');

        // Check if entry cutoff has passed
        if (!$group->acceptingEntries()) {
            return back()->with('error', 'Entry cutoff has passed. Cannot submit or edit entries.');
        }

        $validated = $request->validate([
            'answers' => 'required|array',
            'answers.*.group_question_id' => 'required|exists:group_questions,id',
            'answers.*.answer_text' => 'required|string',
        ]);

        // Get or create entry
        $entry = Entry::firstOrCreate(
            [
                'event_id' => $group->event_id,
                'user_id' => $user->id,
                'group_id' => $group->id,
            ],
            [
                'total_score' => 0,
                'possible_points' => $this->calculateInitialPossiblePoints($group),
                'percentage' => 0,
                'is_complete' => false,
            ]
        );

        // Save captain submission info
        $entry->update([
            'submitted_by_captain_id' => auth()->id(),
            'submitted_by_captain_at' => now(),
            'is_complete' => true,
            'submitted_at' => now(),
        ]);

        // Save user answers
        foreach ($validated['answers'] as $answerData) {
            UserAnswer::updateOrCreate(
                [
                    'entry_id' => $entry->id,
                    'group_question_id' => $answerData['group_question_id'],
                ],
                [
                    'answer_text' => $answerData['answer_text'],
                    'points_earned' => 0, // Will be calculated during grading
                    'is_correct' => false, // Will be determined during grading
                ]
            );
        }

        // Calculate score (if answers are already set)
        $this->entryService->gradeEntry($entry);

        // Update leaderboard
        $this->leaderboardService->updateLeaderboard($group->event, $group);

        return redirect()
            ->route('groups.members.index', $group)
            ->with('success', "Answers submitted for {$user->name}!");
    }

    /**
     * List all members with their entry status for captain to manage.
     */
    public function index(Group $group)
    {
        // Load event relationship for acceptingEntries check
        $group->load('event');

        $members = $group->members()
            ->withPivot('is_captain', 'joined_at')
            ->orderByPivot('is_captain', 'desc')
            ->orderByPivot('joined_at', 'asc')
            ->get()
            ->map(function ($member) use ($group) {
                // Get entry for this member in this group
                $entry = Entry::where('user_id', $member->id)
                    ->where('group_id', $group->id)
                    ->where('event_id', $group->event_id)
                    ->first();

                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'is_captain' => $member->pivot->is_captain,
                    'is_guest' => $member->isGuest(),
                    'joined_at' => $member->pivot->joined_at,
                    'entry' => $entry ? [
                        'id' => $entry->id,
                        'is_complete' => $entry->is_complete,
                        'submitted_at' => $entry->submitted_at,
                        'submitted_by_captain_id' => $entry->submitted_by_captain_id,
                        'total_score' => $entry->total_score,
                        'percentage' => $entry->percentage,
                    ] : null,
                ];
            });

        return Inertia::render('Captain/ManageEntries', [
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
                'entry_cutoff' => $group->entry_cutoff,
                'accepting_entries' => $group->acceptingEntries(),
                'event' => [
                    'id' => $group->event->id,
                    'name' => $group->event->name,
                    'status' => $group->event->status,
                    'lock_date' => $group->event->lock_date,
                ],
            ],
            'members' => $members,
        ]);
    }
}
