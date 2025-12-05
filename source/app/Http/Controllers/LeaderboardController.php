<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Leaderboard;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LeaderboardController extends Controller
{
    /**
     * Display the leaderboard for a specific group.
     */
    public function group(Group $group)
    {
        // Load group with event relationship
        $group->load('event');

        $leaderboard = Leaderboard::with('user')
            ->where('event_id', $group->event_id)
            ->where('group_id', $group->id)
            ->orderBy('total_score', 'desc')
            ->orderBy('answered_count', 'desc')
            ->paginate(50);

        // Update ranks
        $this->updateRanks($group->event_id, $group->id);

        return Inertia::render('Groups/Leaderboard', [
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
                'event' => [
                    'id' => $group->event->id,
                    'name' => $group->event->name,
                    'status' => $group->event->status,
                ],
            ],
            'leaderboard' => $leaderboard,
        ]);
    }

    /**
     * Update ranks for a specific group leaderboard.
     */
    protected function updateRanks($eventId, $groupId)
    {
        $entries = Leaderboard::where('event_id', $eventId)
            ->where('group_id', $groupId)
            ->orderBy('total_score', 'desc')
            ->orderBy('answered_count', 'desc')
            ->get();

        $rank = 1;
        foreach ($entries as $entry) {
            $entry->update(['rank' => $rank]);
            $rank++;
        }
    }
}
