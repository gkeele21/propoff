<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Event;
use App\Models\QuestionTemplate;
use App\Models\EventQuestion;
use App\Models\Group;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    /**
     * Seed test data for comprehensive testing.
     */
    public function run(): void
    {
        // Create test users
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $captain1 = User::create([
            'name' => 'Captain One',
            'email' => 'captain1@test.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        $captain2 = User::create([
            'name' => 'Captain Two',
            'email' => 'captain2@test.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        $player1 = User::create([
            'name' => 'Player One',
            'email' => 'player1@test.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        $player2 = User::create([
            'name' => 'Player Two',
            'email' => 'player2@test.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        $player3 = User::create([
            'name' => 'Player Three',
            'email' => 'player3@test.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        // Create question templates
        QuestionTemplate::create([
            'title' => 'Winner Prediction',
            'category' => 'Sports',
            'question_text' => 'Who will win {TEAM_A} vs {TEAM_B}?',
            'question_type' => 'multiple_choice',
            'default_options' => ['Team A', 'Team B', 'Tie'],
            'default_points' => 10,
            'variables' => ['TEAM_A', 'TEAM_B'],
            'created_by' => $admin->id,
        ]);

        QuestionTemplate::create([
            'title' => 'Score Prediction',
            'category' => 'Sports',
            'question_text' => 'Will {PLAYER} score over/under {NUMBER} points?',
            'question_type' => 'multiple_choice',
            'default_options' => ['Over', 'Under'],
            'default_points' => 5,
            'variables' => ['PLAYER', 'NUMBER'],
            'created_by' => $admin->id,
        ]);

        QuestionTemplate::create([
            'title' => 'Yes/No Prediction',
            'category' => 'Sports',
            'question_text' => 'Will {TEAM} win this game?',
            'question_type' => 'yes_no',
            'default_options' => null,
            'default_points' => 5,
            'variables' => ['TEAM'],
            'created_by' => $admin->id,
        ]);

        QuestionTemplate::create([
            'title' => 'Point Total',
            'category' => 'Sports',
            'question_text' => 'How many total points will {PLAYER} score?',
            'question_type' => 'numeric',
            'default_options' => null,
            'default_points' => 15,
            'variables' => ['PLAYER'],
            'created_by' => $admin->id,
        ]);

        // Create test event
        $event = Event::create([
            'name' => 'Super Bowl LVIII',
            'category' => 'Football',
            'status' => 'open',
            'event_date' => now()->addDays(7),
            'created_by' => $admin->id,
        ]);

        // Add questions to event
        EventQuestion::create([
            'event_id' => $event->id,
            'question_text' => 'Who will win the Super Bowl?',
            'question_type' => 'multiple_choice',
            'options' => [
                ['label' => 'Kansas City Chiefs', 'points' => 0],
                ['label' => 'San Francisco 49ers', 'points' => 0],
            ],
            'points' => 10,
            'display_order' => 1,
        ]);

        EventQuestion::create([
            'event_id' => $event->id,
            'question_text' => 'Will Patrick Mahomes throw for over 300 yards?',
            'question_type' => 'yes_no',
            'options' => null,
            'points' => 5,
            'display_order' => 2,
        ]);

        EventQuestion::create([
            'event_id' => $event->id,
            'question_text' => 'How many total touchdowns will be scored?',
            'question_type' => 'numeric',
            'options' => null,
            'points' => 15,
            'display_order' => 3,
        ]);

        EventQuestion::create([
            'event_id' => $event->id,
            'question_text' => 'Who will be MVP?',
            'question_type' => 'text',
            'options' => null,
            'points' => 20,
            'display_order' => 4,
        ]);

        // Create admin-graded group
        $adminGroup = Group::create([
            'name' => 'Admin Graded Test Group',
            'code' => 'ADMIN123',
            'event_id' => $event->id,
            'grading_source' => 'admin',
            'created_by' => $admin->id,
        ]);

        // Add members to admin group
        $adminGroup->users()->attach($player1->id);
        $adminGroup->users()->attach($player2->id);

        $this->command->info('✅ Test data seeded successfully!');
        $this->command->info('');
        $this->command->info('Test Accounts:');
        $this->command->info('Admin: admin@test.com / password');
        $this->command->info('Captains: captain1@test.com, captain2@test.com / password');
        $this->command->info('Players: player1@test.com, player2@test.com, player3@test.com / password');
        $this->command->info('');
        $this->command->info('Test Event: Super Bowl LVIII');
        $this->command->info('Test Group: Admin Graded Test Group (code: ADMIN123)');
    }
}
