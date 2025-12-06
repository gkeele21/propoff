<?php

namespace Tests\Feature;

use App\Models\Entry;
use App\Models\Event;
use App\Models\Group;
use App\Models\Leaderboard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_view_their_dashboard()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('userGroups')
            ->has('captainGroups')
            ->has('activeEvents')
            ->has('recentResults')
            ->has('stats')
        );
    }

    /** @test */
    public function dashboard_shows_users_events()
    {
        $user = User::factory()->create();
        $event1 = Event::factory()->create(['status' => 'open']);
        $event2 = Event::factory()->create(['status' => 'open']);
        $group1 = Group::factory()->create(['event_id' => $event1->id]);
        $group2 = Group::factory()->create(['event_id' => $event2->id]);

        $group1->users()->attach($user->id, ['joined_at' => now()]);
        $group2->users()->attach($user->id, ['joined_at' => now()]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('userGroups', function ($userGroups) use ($group1, $group2) {
                return count($userGroups) === 2 &&
                    collect($userGroups)->pluck('id')->contains($group1->id) &&
                    collect($userGroups)->pluck('id')->contains($group2->id);
            })
        );
    }

    /** @test */
    public function dashboard_shows_users_groups()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();
        $group1 = Group::factory()->create([
            'event_id' => $event->id,
            'name' => 'Group A',
        ]);
        $group2 = Group::factory()->create([
            'event_id' => $event->id,
            'name' => 'Group B',
        ]);

        $group1->users()->attach($user->id, ['joined_at' => now()]);
        $group2->users()->attach($user->id, ['joined_at' => now()]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->where('userGroups', function ($groups) {
                return count($groups) === 2;
            })
        );
    }

    /** @test */
    public function dashboard_shows_upcoming_events()
    {
        $user = User::factory()->create();

        Event::factory()->create([
            'status' => 'open',
            'event_date' => now()->addDays(5),
        ]);
        Event::factory()->create([
            'status' => 'open',
            'event_date' => now()->addDays(10),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->has('activeEvents', 2)
        );
    }

    /** @test */
    public function dashboard_shows_completed_entries()
    {
        $user = User::factory()->create();
        $event1 = Event::factory()->create();
        $event2 = Event::factory()->create();
        $group1 = Group::factory()->create(['event_id' => $event1->id]);
        $group2 = Group::factory()->create(['event_id' => $event2->id]);

        Entry::factory()->create([
            'user_id' => $user->id,
            'event_id' => $event1->id,
            'group_id' => $group1->id,
            'is_complete' => true,
            'submitted_at' => now()->subDays(1),
        ]);

        Entry::factory()->create([
            'user_id' => $user->id,
            'event_id' => $event2->id,
            'group_id' => $group2->id,
            'is_complete' => true,
            'submitted_at' => now()->subDays(2),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->has('recentResults', 2)
        );
    }

    /** @test */
    public function admin_dashboard_shows_different_data_than_user_dashboard()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $adminResponse = $this->actingAs($admin)->get(route('dashboard'));
        $userResponse = $this->actingAs($user)->get(route('dashboard'));

        $adminResponse->assertInertia(fn ($page) => $page
            ->where('isAdmin', true)
        );

        $userResponse->assertInertia(fn ($page) => $page
            ->where('isAdmin', false)
        );
    }

    /** @test */
    public function guest_cannot_access_dashboard()
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function dashboard_shows_captain_groups()
    {
        $captain = User::factory()->create();
        $event = Event::factory()->create();
        $group = Group::factory()->create(['event_id' => $event->id]);
        $group->addCaptain($captain);

        $response = $this->actingAs($captain)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->where('isCaptain', true)
            ->has('captainGroups', 1)
        );
    }

    /** @test */
    public function dashboard_shows_user_statistics()
    {
        $user = User::factory()->create();
        $event1 = Event::factory()->create();
        $event2 = Event::factory()->create();
        $group1 = Group::factory()->create(['event_id' => $event1->id]);
        $group2 = Group::factory()->create(['event_id' => $event2->id]);

        Entry::factory()->create([
            'user_id' => $user->id,
            'event_id' => $event1->id,
            'group_id' => $group1->id,
            'is_complete' => true,
            'percentage' => 80.0,
        ]);

        Entry::factory()->create([
            'user_id' => $user->id,
            'event_id' => $event2->id,
            'group_id' => $group2->id,
            'is_complete' => true,
            'percentage' => 90.0,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->has('stats')
            ->where('stats.total_entries', 2)
            ->where('stats.average_score', 85)
        );
    }

    /** @test */
    public function dashboard_shows_only_users_own_data()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $event = Event::factory()->create();
        $group = Group::factory()->create(['event_id' => $event->id]);

        // User1's entry
        Entry::factory()->create([
            'user_id' => $user1->id,
            'event_id' => $event->id,
            'group_id' => $group->id,
            'is_complete' => true,
        ]);

        // User2's entry
        Entry::factory()->create([
            'user_id' => $user2->id,
            'event_id' => $event->id,
            'group_id' => $group->id,
            'is_complete' => true,
        ]);

        $response = $this->actingAs($user1)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->where('stats.total_entries', 1) // Only user1's entry
        );
    }

    /** @test */
    public function dashboard_shows_entry_status_for_each_group()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();
        $group = Group::factory()->create(['event_id' => $event->id]);
        $group->users()->attach($user->id, ['joined_at' => now()]);

        Entry::factory()->create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'group_id' => $group->id,
            'is_complete' => false,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->where('userGroups.0.user_entry.status', 'in_progress')
        );
    }

    /** @test */
    public function dashboard_shows_if_group_has_leaderboard_data()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();
        $group = Group::factory()->create(['event_id' => $event->id]);
        $group->users()->attach($user->id, ['joined_at' => now()]);

        // Create leaderboard entry
        Leaderboard::create([
            'event_id' => $event->id,
            'group_id' => $group->id,
            'user_id' => $user->id,
            'rank' => 1,
            'total_score' => 100,
            'possible_points' => 100,
            'percentage' => 100.0,
            'answered_count' => 10,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->where('userGroups.0.has_leaderboard', true)
        );
    }

    /** @test */
    public function dashboard_shows_captain_status_for_each_group()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();
        $group = Group::factory()->create(['event_id' => $event->id]);
        $group->addCaptain($user);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->where('userGroups.0.is_captain', true)
        );
    }

    /** @test */
    public function dashboard_shows_member_count_for_captain_groups()
    {
        $captain = User::factory()->create();
        $event = Event::factory()->create();
        $group = Group::factory()->create(['event_id' => $event->id]);
        $group->addCaptain($captain);

        // Add members
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $group->users()->attach($user1->id, ['joined_at' => now()]);
        $group->users()->attach($user2->id, ['joined_at' => now()]);

        $response = $this->actingAs($captain)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->where('captainGroups.0.members_count', function ($count) {
                return $count >= 2; // At least the 2 members we added
            })
        );
    }

    /** @test */
    public function dashboard_shows_only_open_and_locked_events()
    {
        $user = User::factory()->create();

        Event::factory()->create(['status' => 'open']);
        Event::factory()->create(['status' => 'locked']);
        Event::factory()->create(['status' => 'completed']); // Should not show
        Event::factory()->create(['status' => 'draft']); // Should not show

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->has('activeEvents', 2) // Only open and locked
        );
    }

    /** @test */
    public function dashboard_limits_active_events_to_six()
    {
        $user = User::factory()->create();

        // Create 10 events
        for ($i = 0; $i < 10; $i++) {
            Event::factory()->create([
                'status' => 'open',
                'event_date' => now()->addDays($i),
            ]);
        }

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->has('activeEvents', 6) // Limited to 6
        );
    }

    /** @test */
    public function dashboard_limits_recent_results_to_five()
    {
        $user = User::factory()->create();

        // Create 8 completed entries - each for a different event/group
        for ($i = 0; $i < 8; $i++) {
            $event = Event::factory()->create();
            $group = Group::factory()->create(['event_id' => $event->id]);

            Entry::factory()->create([
                'user_id' => $user->id,
                'event_id' => $event->id,
                'group_id' => $group->id,
                'is_complete' => true,
                'submitted_at' => now()->subDays($i),
            ]);
        }

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->has('recentResults', 5) // Limited to 5
        );
    }

    /** @test */
    public function dashboard_shows_grading_source_for_groups()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();
        $captainGroup = Group::factory()->create([
            'event_id' => $event->id,
            'grading_source' => 'captain',
        ]);
        $adminGroup = Group::factory()->create([
            'event_id' => $event->id,
            'grading_source' => 'admin',
        ]);

        $captainGroup->users()->attach($user->id, ['joined_at' => now()]);
        $adminGroup->users()->attach($user->id, ['joined_at' => now()]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->where('userGroups', function ($groups) {
                $captainGraded = collect($groups)->where('grading_source', 'captain')->count();
                $adminGraded = collect($groups)->where('grading_source', 'admin')->count();
                return $captainGraded === 1 && $adminGraded === 1;
            })
        );
    }

    /** @test */
    public function dashboard_shows_event_status_for_active_events()
    {
        $user = User::factory()->create();

        Event::factory()->create([
            'status' => 'open',
            'event_date' => now()->addDays(5),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->where('activeEvents.0.status', 'open')
        );
    }

    /** @test */
    public function dashboard_shows_if_user_has_joined_event()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['status' => 'open']);
        $group = Group::factory()->create(['event_id' => $event->id]);
        $group->users()->attach($user->id, ['joined_at' => now()]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->where('activeEvents', function ($events) use ($event) {
                $eventData = collect($events)->firstWhere('id', $event->id);
                return $eventData && $eventData['has_joined'] === true;
            })
        );
    }

    /** @test */
    public function dashboard_shows_total_events_participated()
    {
        $user = User::factory()->create();
        $event1 = Event::factory()->create();
        $event2 = Event::factory()->create();
        $group1 = Group::factory()->create(['event_id' => $event1->id]);
        $group2 = Group::factory()->create(['event_id' => $event2->id]);

        Entry::factory()->create([
            'user_id' => $user->id,
            'event_id' => $event1->id,
            'group_id' => $group1->id,
        ]);

        Entry::factory()->create([
            'user_id' => $user->id,
            'event_id' => $event2->id,
            'group_id' => $group2->id,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->where('stats.total_events', 2)
        );
    }

    /** @test */
    public function dashboard_calculates_groups_count()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();
        $group1 = Group::factory()->create(['event_id' => $event->id]);
        $group2 = Group::factory()->create(['event_id' => $event->id]);
        $group3 = Group::factory()->create(['event_id' => $event->id]);

        $group1->users()->attach($user->id, ['joined_at' => now()]);
        $group2->users()->attach($user->id, ['joined_at' => now()]);
        $group3->users()->attach($user->id, ['joined_at' => now()]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->where('stats.groups_count', 3)
        );
    }

    /** @test */
    public function dashboard_shows_captain_groups_count()
    {
        $captain = User::factory()->create();
        $event = Event::factory()->create();
        $group1 = Group::factory()->create(['event_id' => $event->id]);
        $group2 = Group::factory()->create(['event_id' => $event->id]);

        $group1->addCaptain($captain);
        $group2->addCaptain($captain);

        $response = $this->actingAs($captain)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->where('stats.captain_groups_count', 2)
        );
    }
}
