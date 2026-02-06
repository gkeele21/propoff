<?php

namespace App\Services;

use App\Models\User;

class SmartRoutingService
{
    /**
     * Get the appropriate redirect URL for a user based on their active groups.
     *
     * Logic:
     * - 0 active groups: Go to homepage (join a group prompt)
     * - 1 active group: Go directly to that group's Play Hub
     * - Multiple groups: Go to group chooser page
     */
    public function getRedirectForUser(User $user): string
    {
        $activeGroups = $this->getActiveGroups($user);
        $count = $activeGroups->count();

        if ($count === 0) {
            return route('home');
        }

        if ($count === 1) {
            return route('play.hub', ['code' => $activeGroups->first()->code]);
        }

        return route('groups.choose');
    }

    /**
     * Get active groups for a user.
     * Active = event is not completed and not locked more than 7 days ago.
     */
    public function getActiveGroups(User $user)
    {
        return $user->groups()
            ->with('event')
            ->whereHas('event', function ($query) {
                $query->whereIn('status', ['draft', 'open', 'locked'])
                    ->orWhere('event_date', '>=', now()->subDays(7));
            })
            ->get();
    }
}
