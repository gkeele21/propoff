<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventQuestion;
use App\Models\Group;
use App\Models\Leaderboard;
use App\Models\QuestionTemplate;
use App\Models\Entry;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MyHomeController extends Controller
{
    /**
     * Display the user's home page.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user->role === 'admin';
        $isCaptain = $user->captainGroups()->exists();

        return Inertia::render('MyHome', [
            'isAdmin' => $isAdmin,
            'isCaptain' => $isCaptain,
            'userGroups' => $this->getUserGroups($user),
            'captainGroups' => $this->getCaptainGroups($user),
            'activeEvents' => $this->getActiveEvents($user),
            'recentResults' => $this->getRecentEntries($user),
            'stats' => $this->getUserStats($user),
        ]);
    }

    /**
     * Get all groups the user is a member of with entry status.
     */
    private function getUserGroups($user)
    {
        return $user->groups()
            ->with(['event', 'members'])
            ->withCount('members')
            ->get()
            ->map(function ($group) use ($user) {
                $isCaptain = $group->members()
                    ->wherePivot('user_id', $user->id)
                    ->wherePivot('is_captain', true)
                    ->exists();

                // Get user's entry for this group/event
                $userEntry = Entry::where('user_id', $user->id)
                    ->where('group_id', $group->id)
                    ->where('event_id', $group->event_id)
                    ->first();

                $entryData = null;
                if ($userEntry) {
                    $entryData = [
                        'id' => $userEntry->id,
                        'status' => $userEntry->is_complete ? 'completed' : 'in_progress',
                        'score' => $userEntry->total_score,
                        'percentage' => $userEntry->percentage,
                    ];
                }

                // Check if leaderboard has any entries (answers have been graded)
                $hasLeaderboardData = Leaderboard::where('event_id', $group->event_id)
                    ->where('group_id', $group->id)
                    ->exists();

                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'description' => $group->description,
                    'code' => $group->code,
                    'is_captain' => $isCaptain,
                    'grading_source' => $group->grading_source,
                    'members_count' => $group->members_count,
                    'user_entry' => $entryData,
                    'has_leaderboard' => $hasLeaderboardData,
                    'event' => [
                        'id' => $group->event->id,
                        'name' => $group->event->name,
                        'status' => $group->event->status,
                        'event_date' => $group->event->event_date,
                    ],
                ];
            });
    }

    /**
     * Get groups where user is a captain.
     */
    private function getCaptainGroups($user)
    {
        return $user->captainGroups()
            ->with(['event'])
            ->withCount(['members', 'entries', 'groupQuestions'])
            ->get()
            ->map(function ($group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'event' => [
                        'id' => $group->event->id,
                        'name' => $group->event->name,
                        'status' => $group->event->status,
                    ],
                    'members_count' => $group->members_count,
                    'questions_count' => $group->group_questions_count,
                    'entries_count' => $group->entries_count,
                ];
            });
    }

    /**
     * Get active and upcoming events for the user.
     */
    private function getActiveEvents($user)
    {
        return Event::whereIn('status', ['open', 'locked'])
            ->orderBy('event_date', 'asc')
            ->limit(6)
            ->get()
            ->map(function ($event) use ($user) {
                // Check if user has an entry for this event
                $userEntry = Entry::where('event_id', $event->id)
                    ->where('user_id', $user->id)
                    ->first();

                // Check if user has joined a group for this event
                $userGroup = $user->groups()
                    ->where('event_id', $event->id)
                    ->first();

                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'event_date' => $event->event_date,
                    'lock_date' => $event->lock_date,
                    'status' => $event->status,
                    'has_joined' => $userGroup !== null,
                    'has_submitted' => $userEntry !== null,
                    'entry_complete' => $userEntry?->is_complete ?? false,
                    'entry_id' => $userEntry?->id,
                    'group_id' => $userGroup?->id,
                ];
            });
    }

    /**
     * Get recent entries for the user.
     */
    private function getRecentEntries($user)
    {
        return Entry::where('user_id', $user->id)
            ->where('is_complete', true)
            ->with(['event', 'group'])
            ->orderBy('submitted_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($entry) {
                return [
                    'id' => $entry->id,
                    'event' => [
                        'id' => $entry->event->id,
                        'name' => $entry->event->name,
                    ],
                    'group' => [
                        'id' => $entry->group->id,
                        'name' => $entry->group->name,
                    ],
                    'total_score' => $entry->total_score,
                    'possible_points' => $entry->possible_points,
                    'percentage' => $entry->percentage,
                    'submitted_at' => $entry->submitted_at,
                ];
            });
    }

    /**
     * Get user statistics.
     */
    private function getUserStats($user)
    {
        return [
            'total_events' => Entry::where('user_id', $user->id)
                ->distinct('event_id')
                ->count('event_id'),
            'total_entries' => Entry::where('user_id', $user->id)->count(),
            'groups_count' => $user->groups()->count(),
            'captain_groups_count' => $user->captainGroups()->count(),
            'average_score' => (float) (Entry::where('user_id', $user->id)
                ->where('is_complete', true)
                ->avg('percentage') ?? 0),
        ];
    }


}
