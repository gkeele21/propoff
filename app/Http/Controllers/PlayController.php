<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use App\Models\Group;
use App\Models\GroupQuestionAnswer;
use App\Models\Leaderboard;
use App\Models\User;
use App\Models\UserAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PlayController extends Controller
{
    /**
     * Display the Play Hub for a group.
     */
    public function hub(string $code)
    {
        $group = Group::where('code', $code)->firstOrFail();
        $group->load(['event']);

        // Check if user is recognized (authenticated or has guest cookie)
        $user = auth()->user();

        if (!$user) {
            // Redirect to join form if not recognized
            return redirect()->route('play.join', ['code' => $code]);
        }

        // Check if user is a member of this group
        $isMember = $group->users()->where('user_id', $user->id)->exists();

        if (!$isMember) {
            // Redirect to join form to join the group
            return redirect()->route('play.join', ['code' => $code]);
        }

        // Check if user is a captain
        $isCaptain = $user->isCaptainOf($group->id);
        $isGuest = $user->isGuest();

        // Get group stats
        $stats = $this->getGroupStats($group);

        // Get user's entry status
        $myEntry = $this->getMyEntryStatus($group, $user);

        // Get game status
        $gameStatus = [
            'is_locked' => $group->is_locked,
            'close_time' => $group->entry_cutoff ?? $group->event?->lock_date,
            'event_start' => $group->event?->event_date,
        ];

        // Get leaderboard preview (top 5 + user's row)
        $leaderboard = $this->getLeaderboardPreview($group, $user);

        return Inertia::render('Play/Hub', [
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
                'code' => $group->code,
                'description' => $group->description,
                'grading_source' => $group->grading_source,
                'entry_cutoff' => $group->entry_cutoff,
                'is_locked' => $group->is_locked,
                'event' => $group->event ? [
                    'id' => $group->event->id,
                    'name' => $group->event->name,
                    'event_date' => $group->event->event_date,
                    'status' => $group->event->status,
                ] : null,
            ],
            'stats' => $stats,
            'myEntry' => $myEntry,
            'gameStatus' => $gameStatus,
            'leaderboard' => $leaderboard,
            'isCaptain' => $isCaptain,
            'isGuest' => $isGuest,
        ]);
    }

    /**
     * Show the join form for guests.
     * Authenticated users are auto-added to the group.
     */
    public function joinForm(string $code)
    {
        $group = Group::where('code', $code)
            ->with('event')
            ->firstOrFail();

        // If user is already logged in
        if (auth()->check()) {
            $user = auth()->user();
            $isMember = $group->users()->where('user_id', $user->id)->exists();

            if ($isMember) {
                // Already a member - go to hub
                return redirect()->route('play.hub', ['code' => $code]);
            }

            // Not a member - auto-add them to the group
            $group->users()->attach($user->id, [
                'joined_at' => now(),
                'is_captain' => false,
            ]);

            return redirect()->route('play.hub', ['code' => $code])
                ->with('success', 'You joined ' . $group->name . '!');
        }

        // Guest user - show the join form
        return Inertia::render('Play/Join', [
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
                'code' => $group->code,
                'event' => $group->event ? [
                    'id' => $group->event->id,
                    'name' => $group->event->name,
                    'event_date' => $group->event->event_date,
                ] : null,
            ],
            'step' => 'name',
            'existingNames' => [],
            'verifyEntry' => null,
        ]);
    }

    /**
     * Process the join request.
     */
    public function processJoin(Request $request, string $code)
    {
        $group = Group::where('code', $code)->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'verified' => 'nullable|boolean',
        ]);

        $name = trim($validated['name']);

        // Check for exact match
        $exactMatch = $group->users()
            ->where('name', $name)
            ->first();

        // If verified, link to existing user immediately
        if ($exactMatch && ($validated['verified'] ?? false)) {
            return $this->linkGuestAndProceed($exactMatch, $group, $code);
        }

        // If exact match and not verified, show verification
        if ($exactMatch) {
            $entry = Entry::where('user_id', $exactMatch->id)
                ->where('group_id', $group->id)
                ->first();

            $answeredCount = $entry ? $entry->userAnswers()->count() : 0;
            $totalQuestions = $group->groupQuestions()->where('is_active', true)->count();

            return back()->with([
                'step' => 'verify',
                'verifyEntry' => [
                    'name' => $exactMatch->name,
                    'answered' => $answeredCount,
                    'total' => $totalQuestions,
                    'user_id' => $exactMatch->id,
                ],
            ]);
        }

        // No match - create new guest user and join group
        return $this->createGuestAndJoin($name, $group, $code);
    }

    /**
     * Display the questions page for answering.
     */
    public function questions(Request $request, string $code)
    {
        $group = Group::where('code', $code)->with('event')->firstOrFail();
        $currentUser = auth()->user();

        if (!$currentUser) {
            return redirect()->route('play.join', ['code' => $code]);
        }

        // Check if current user is a member
        if (!$group->users()->where('user_id', $currentUser->id)->exists()) {
            return redirect()->route('play.join', ['code' => $code]);
        }

        // Determine target user (self or someone else if captain)
        $targetUser = $currentUser;
        $submittingFor = null;

        if ($request->has('for_user') && $request->for_user != $currentUser->id) {
            // Check if current user is a captain of this group
            if (!$currentUser->isCaptainOf($group->id)) {
                abort(403, 'Only captains can submit for other users.');
            }

            // Get target user and verify they're in this group
            $targetUser = User::findOrFail($request->for_user);
            if (!$group->users()->where('user_id', $targetUser->id)->exists()) {
                abort(404, 'User is not a member of this group.');
            }

            $submittingFor = [
                'id' => $targetUser->id,
                'name' => $targetUser->name,
            ];
        }

        // Get or create entry for target user
        $entry = Entry::where('user_id', $targetUser->id)
            ->where('group_id', $group->id)
            ->first();

        if (!$entry) {
            // Don't create new entries if group is locked
            if ($group->is_locked) {
                return redirect()->route('play.hub', ['code' => $code])
                    ->with('error', 'This game is locked. New entries cannot be created.');
            }

            // Calculate possible points from group's active questions
            $possiblePoints = $group->groupQuestions()
                ->where('is_active', true)
                ->sum('points');

            $entry = Entry::create([
                'event_id' => $group->event_id,
                'user_id' => $targetUser->id,
                'group_id' => $group->id,
                'total_score' => 0,
                'possible_points' => $possiblePoints,
                'percentage' => 0,
            ]);
        }

        // Get leaderboard entry for rank (for results display)
        $leaderboardEntry = Leaderboard::where('event_id', $group->event_id)
            ->where('group_id', $group->id)
            ->where('user_id', $targetUser->id)
            ->first();

        $totalParticipants = Leaderboard::where('event_id', $group->event_id)
            ->where('group_id', $group->id)
            ->count();

        // Load questions with user's answers and results data
        $questions = $group->groupQuestions()
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get()
            ->map(function ($question) use ($entry, $group) {
                $userAnswer = $entry->userAnswers()
                    ->where('group_question_id', $question->id)
                    ->first();

                // Get correct answer based on grading source (for results display)
                $correctAnswer = null;
                $isVoid = false;

                if ($group->grading_source === 'captain') {
                    $groupAnswer = GroupQuestionAnswer::where('group_id', $group->id)
                        ->where('group_question_id', $question->id)
                        ->first();
                    $correctAnswer = $groupAnswer?->correct_answer;
                    $isVoid = $groupAnswer?->is_void ?? false;
                } else {
                    // Admin grading
                    if ($question->event_question_id) {
                        $eventAnswer = \App\Models\EventAnswer::where('event_id', $group->event_id)
                            ->where('event_question_id', $question->event_question_id)
                            ->first();
                        $correctAnswer = $eventAnswer?->correct_answer;
                        $isVoid = $eventAnswer?->is_void ?? false;
                    }
                }

                return [
                    'id' => $question->id,
                    'question_text' => $question->question_text,
                    'question_type' => $question->question_type,
                    'options' => $question->options,
                    'points' => $question->points,
                    'user_answer' => $userAnswer?->answer_text,
                    'correct_answer' => $correctAnswer,
                    'is_void' => $isVoid,
                    'points_earned' => $userAnswer?->points_earned ?? 0,
                    'is_correct' => $userAnswer?->is_correct ?? false,
                ];
            });

        $answeredCount = $entry->userAnswers()->count();

        return Inertia::render('Play/Game', [
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
                'code' => $group->code,
                'is_locked' => $group->is_locked,
            ],
            'event' => $group->event ? [
                'id' => $group->event->id,
                'name' => $group->event->name,
                'description' => $group->event->description,
            ] : null,
            'entry' => [
                'id' => $entry->id,
                'answered_count' => $answeredCount,
                'total_questions' => $questions->count(),
                'total_score' => $entry->total_score,
                'possible_points' => $entry->possible_points,
                'rank' => $leaderboardEntry?->rank,
                'total_participants' => $totalParticipants,
            ],
            'questions' => $questions,
            'submittingFor' => $submittingFor,
            'isGuest' => $currentUser->isGuest(),
        ]);
    }

    /**
     * Save answers for an entry.
     */
    public function saveAnswers(Request $request, string $code)
    {
        $group = Group::where('code', $code)->firstOrFail();
        $currentUser = auth()->user();

        if (!$currentUser) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Determine target user (self or someone else if captain)
        $targetUserId = $currentUser->id;

        if ($request->has('for_user') && $request->for_user != $currentUser->id) {
            // Check if current user is a captain of this group
            if (!$currentUser->isCaptainOf($group->id)) {
                abort(403, 'Only captains can submit for other users.');
            }

            // Verify target user is in this group
            if (!$group->users()->where('user_id', $request->for_user)->exists()) {
                abort(404, 'User is not a member of this group.');
            }

            $targetUserId = $request->for_user;
        }

        // Check if group is locked
        if ($group->is_locked) {
            return back()->with('error', 'This game is locked. You can no longer submit answers.');
        }

        $entry = Entry::where('user_id', $targetUserId)
            ->where('group_id', $group->id)
            ->first();

        if (!$entry) {
            return back()->with('error', 'No entry found.');
        }

        $validated = $request->validate([
            'answers' => 'required|array',
            'answers.*.group_question_id' => 'required|exists:group_questions,id',
            'answers.*.answer_text' => 'required|string',
        ]);

        DB::transaction(function () use ($entry, $validated, $group) {
            foreach ($validated['answers'] as $answerData) {
                // Get the group question
                $groupQuestion = $group->groupQuestions()
                    ->findOrFail($answerData['group_question_id']);

                $isCorrect = false;
                $pointsEarned = 0;

                // Check if there's a correct answer set
                $groupAnswer = GroupQuestionAnswer::where('group_id', $group->id)
                    ->where('group_question_id', $groupQuestion->id)
                    ->first();

                if ($groupAnswer && !$groupAnswer->is_void) {
                    $isCorrect = strcasecmp(
                        trim($answerData['answer_text']),
                        trim($groupAnswer->correct_answer)
                    ) === 0;

                    $pointsEarned = $isCorrect ? $groupQuestion->points : 0;
                }

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

            // Update entry totals
            $totalScore = $entry->userAnswers()->sum('points_earned');
            $percentage = $entry->possible_points > 0
                ? ($totalScore / $entry->possible_points) * 100
                : 0;

            $entry->update([
                'total_score' => $totalScore,
                'percentage' => $percentage,
            ]);
        });

        // Update leaderboard
        $this->updateLeaderboard($entry->fresh());

        return back()->with('success', 'Answers saved!');
    }

    /**
     * Display full leaderboard.
     */
    public function leaderboard(string $code)
    {
        $group = Group::where('code', $code)
            ->with('event')
            ->firstOrFail();

        $user = auth()->user();

        $leaderboard = Leaderboard::with('user')
            ->where('event_id', $group->event_id)
            ->where('group_id', $group->id)
            ->orderBy('rank')
            ->paginate(50);

        // Get current user's row if logged in
        $userRow = null;
        if ($user) {
            $userLeaderboard = Leaderboard::where('event_id', $group->event_id)
                ->where('group_id', $group->id)
                ->where('user_id', $user->id)
                ->first();

            if ($userLeaderboard) {
                $userRow = [
                    'rank' => $userLeaderboard->rank,
                    'name' => $user->name,
                    'total_score' => $userLeaderboard->total_score,
                    'possible_points' => $userLeaderboard->possible_points,
                    'user_id' => $user->id,
                ];
            } else {
                // Fall back to Entry if not on leaderboard yet
                $entry = Entry::where('event_id', $group->event_id)
                    ->where('group_id', $group->id)
                    ->where('user_id', $user->id)
                    ->first();

                if ($entry) {
                    // Calculate approximate rank based on score
                    $rank = Leaderboard::where('event_id', $group->event_id)
                        ->where('group_id', $group->id)
                        ->where('total_score', '>', $entry->total_score)
                        ->count() + 1;

                    $userRow = [
                        'rank' => $rank,
                        'name' => $user->name,
                        'total_score' => $entry->total_score,
                        'possible_points' => $entry->possible_points,
                        'user_id' => $user->id,
                    ];
                }
            }
        }

        return Inertia::render('Play/Leaderboard', [
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
                'code' => $group->code,
                'event' => [
                    'id' => $group->event->id,
                    'name' => $group->event->name,
                ],
            ],
            'leaderboard' => $leaderboard,
            'userRow' => $userRow,
        ]);
    }

    /**
     * Get group statistics.
     */
    protected function getGroupStats(Group $group): array
    {
        $totalQuestions = $group->groupQuestions()->where('is_active', true)->count();
        $gradedQuestions = $group->groupQuestionAnswers()->count();
        $totalMembers = $group->users()->count();
        $totalPoints = $group->groupQuestions()->where('is_active', true)->sum('points');

        return [
            'total_questions' => $totalQuestions,
            'graded_questions' => $gradedQuestions,
            'total_members' => $totalMembers,
            'total_points' => $totalPoints,
        ];
    }

    /**
     * Get current user's entry status.
     */
    protected function getMyEntryStatus(Group $group, User $user): ?array
    {
        $entry = Entry::where('user_id', $user->id)
            ->where('group_id', $group->id)
            ->first();

        $totalQuestions = $group->groupQuestions()->where('is_active', true)->count();

        if (!$entry) {
            return [
                'id' => null,
                'status' => 'not_started',
                'answered_count' => 0,
                'total_questions' => $totalQuestions,
                'score' => null,
                'rank' => null,
                'total_participants' => 0,
            ];
        }

        $answeredCount = $entry->userAnswers()->count();

        // Get leaderboard entry for rank
        $leaderboardEntry = Leaderboard::where('event_id', $group->event_id)
            ->where('group_id', $group->id)
            ->where('user_id', $user->id)
            ->first();

        $totalParticipants = Leaderboard::where('event_id', $group->event_id)
            ->where('group_id', $group->id)
            ->count();

        // Determine status: submitted if complete, in_progress if has answers, otherwise not_started
        $status = $entry->is_complete
            ? 'submitted'
            : ($answeredCount > 0 ? 'in_progress' : 'not_started');

        return [
            'id' => $entry->id,
            'status' => $status,
            'answered_count' => $answeredCount,
            'total_questions' => $totalQuestions,
            'score' => $entry->is_complete ? $entry->total_score : null,
            'rank' => $leaderboardEntry?->rank,
            'total_participants' => $totalParticipants,
        ];
    }

    /**
     * Get leaderboard preview (top 5 + user's row).
     */
    protected function getLeaderboardPreview(Group $group, User $user): array
    {
        $top5 = Leaderboard::with('user:id,name')
            ->where('event_id', $group->event_id)
            ->where('group_id', $group->id)
            ->orderBy('rank')
            ->limit(5)
            ->get()
            ->map(fn($entry) => [
                'rank' => $entry->rank,
                'name' => $entry->user->name,
                'score' => $entry->total_score,
                'user_id' => $entry->user_id,
            ]);

        // Get user's row if not in top 5
        $userRow = null;
        $userLeaderboard = Leaderboard::where('event_id', $group->event_id)
            ->where('group_id', $group->id)
            ->where('user_id', $user->id)
            ->first();

        if ($userLeaderboard && !$top5->contains('user_id', $user->id)) {
            $userRow = [
                'rank' => $userLeaderboard->rank,
                'name' => $user->name,
                'score' => $userLeaderboard->total_score,
                'user_id' => $user->id,
            ];
        }

        $totalParticipants = Leaderboard::where('event_id', $group->event_id)
            ->where('group_id', $group->id)
            ->count();

        return [
            'top5' => $top5,
            'userRow' => $userRow,
            'totalParticipants' => $totalParticipants,
            'currentUserId' => $user->id,
        ];
    }

    /**
     * Create a new guest user and join the group.
     */
    protected function createGuestAndJoin(string $name, Group $group, string $code)
    {
        $guestToken = Str::random(32);

        $user = User::create([
            'name' => $name,
            'email' => null,
            'password' => null,
            'role' => 'guest',
            'guest_token' => $guestToken,
        ]);

        // Add user to group
        $group->users()->attach($user->id, [
            'joined_at' => now(),
            'is_captain' => false,
        ]);

        // Login the user
        Auth::login($user);

        // Set cookie and redirect
        return redirect()->route('play.hub', ['code' => $code])
            ->with('success', 'Welcome to ' . $group->name . '!')
            ->cookie('propoff_guest', $guestToken, 60 * 24 * 90); // 90 days
    }

    /**
     * Link to existing guest user and proceed.
     */
    protected function linkGuestAndProceed(User $user, Group $group, string $code)
    {
        // Login the user
        Auth::login($user);

        // Set cookie if guest
        if ($user->isGuest() && $user->guest_token) {
            return redirect()->route('play.hub', ['code' => $code])
                ->with('success', 'Welcome back, ' . $user->name . '!')
                ->cookie('propoff_guest', $user->guest_token, 60 * 24 * 90);
        }

        return redirect()->route('play.hub', ['code' => $code])
            ->with('success', 'Welcome back, ' . $user->name . '!');
    }

    /**
     * Update leaderboard for an entry.
     */
    protected function updateLeaderboard(Entry $entry): void
    {
        $answeredCount = $entry->userAnswers()->count();

        // Calculate rank
        $rank = Leaderboard::where('event_id', $entry->event_id)
            ->where('group_id', $entry->group_id)
            ->where(function ($query) use ($entry) {
                $query->where('total_score', '>', $entry->total_score)
                    ->orWhere(function ($q) use ($entry) {
                        $q->where('total_score', '=', $entry->total_score)
                            ->where('percentage', '>', $entry->percentage);
                    });
            })
            ->count() + 1;

        Leaderboard::updateOrCreate(
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

        // Recalculate all ranks
        $this->recalculateRanks($entry->event_id, $entry->group_id);
    }

    /**
     * Recalculate ranks for all users in a group.
     */
    protected function recalculateRanks(int $eventId, int $groupId): void
    {
        $leaderboards = Leaderboard::where('event_id', $eventId)
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
}
