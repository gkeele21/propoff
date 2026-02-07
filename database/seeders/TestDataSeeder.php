<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Event;
use App\Models\EventQuestion;
use App\Models\Group;
use App\Models\GroupQuestion;
use App\Models\Entry;
use App\Models\UserAnswer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    /**
     * Seed data for testing scoring, answers, and leaderboard.
     */
    public function run(): void
    {
        // Use existing bertkeele user as captain
        $captain = User::where('email', 'bertkeele@gmail.com')->first();

        if (!$captain) {
            $this->command->error('bertkeele@gmail.com user not found! Run DatabaseSeeder first.');
            return;
        }

        // Create 4 players
        $players = [];
        for ($i = 1; $i <= 4; $i++) {
            $players[] = User::firstOrCreate(
                ['email' => "player{$i}@test.com"],
                [
                    'name' => "Player {$i}",
                    'password' => Hash::make('password'),
                    'role' => 'user',
                ]
            );
        }

        // Create event
        $event = Event::firstOrCreate(
            ['name' => 'Scoring Test Event'],
            [
                'category' => 'Sports',
                'status' => 'open',
                'event_date' => now()->addDays(7),
                'created_by' => $captain->id,
            ]
        );

        // Create event questions
        $q1 = EventQuestion::firstOrCreate(
            ['event_id' => $event->id, 'question_text' => 'Who will win?'],
            [
                'question_type' => 'multiple_choice',
                'options' => [
                    ['label' => 'Team A', 'points' => 0],
                    ['label' => 'Team B', 'points' => 5], // Bonus for picking underdog
                ],
                'points' => 10,
                'display_order' => 1,
            ]
        );

        $q2 = EventQuestion::firstOrCreate(
            ['event_id' => $event->id, 'question_text' => 'Over or under 50 points?'],
            [
                'question_type' => 'multiple_choice',
                'options' => [
                    ['label' => 'Over', 'points' => 0],
                    ['label' => 'Under', 'points' => 0],
                ],
                'points' => 5,
                'display_order' => 2,
            ]
        );

        $q3 = EventQuestion::firstOrCreate(
            ['event_id' => $event->id, 'question_text' => 'Will there be overtime?'],
            [
                'question_type' => 'multiple_choice',
                'options' => [
                    ['label' => 'Yes', 'points' => 10], // Big bonus for picking OT
                    ['label' => 'No', 'points' => 0],
                ],
                'points' => 5,
                'display_order' => 3,
            ]
        );

        // Create group with captain grading
        $group = Group::firstOrCreate(
            ['code' => 'SCORE123'],
            [
                'name' => 'Scoring Test Group',
                'event_id' => $event->id,
                'grading_source' => 'captain',
                'created_by' => $captain->id,
            ]
        );

        // Add captain to group
        if (!$group->users()->where('user_id', $captain->id)->exists()) {
            $group->users()->attach($captain->id, [
                'joined_at' => now(),
                'is_captain' => true,
            ]);
        }

        // Create group questions from event questions
        $eventQuestions = [$q1, $q2, $q3];
        $groupQuestions = [];
        foreach ($eventQuestions as $eq) {
            $groupQuestions[] = GroupQuestion::firstOrCreate(
                ['group_id' => $group->id, 'event_question_id' => $eq->id],
                [
                    'question_text' => $eq->question_text,
                    'question_type' => $eq->question_type,
                    'options' => $eq->options,
                    'points' => $eq->points,
                    'display_order' => $eq->display_order,
                    'is_active' => true,
                    'is_custom' => false,
                ]
            );
        }

        // Add players and create their entries with answers
        $playerAnswers = [
            // Player 1: Team A, Over, No
            ['Team A', 'Over', 'No'],
            // Player 2: Team A, Under, No
            ['Team A', 'Under', 'No'],
            // Player 3: Team B, Over, Yes
            ['Team B', 'Over', 'Yes'],
            // Player 4: Team B, Under, Yes
            ['Team B', 'Under', 'Yes'],
        ];

        foreach ($players as $index => $player) {
            // Add player to group
            if (!$group->users()->where('user_id', $player->id)->exists()) {
                $group->users()->attach($player->id, [
                    'joined_at' => now(),
                    'is_captain' => false,
                ]);
            }

            // Create entry
            $entry = Entry::firstOrCreate(
                ['user_id' => $player->id, 'group_id' => $group->id],
                [
                    'event_id' => $event->id,
                    'is_complete' => true,
                    'submitted_at' => now(),
                ]
            );

            // Create answers
            $answers = $playerAnswers[$index];
            foreach ($groupQuestions as $qIndex => $gq) {
                UserAnswer::firstOrCreate(
                    ['entry_id' => $entry->id, 'group_question_id' => $gq->id],
                    ['answer_text' => $answers[$qIndex]]
                );
            }
        }

        $this->command->info('');
        $this->command->info('✅ Test data seeded!');
        $this->command->info('');
        $this->command->info('Group Code: SCORE123');
        $this->command->info('Captain: Bert Keele (bertkeele@gmail.com)');
        $this->command->info('');
        $this->command->info('Players (password: "password"):');
        $this->command->info('  player1@test.com - player4@test.com');
        $this->command->info('');
        $this->command->info('Questions:');
        $this->command->info('  1. Who will win? (10pts) - Team A, Team B (+5 bonus)');
        $this->command->info('  2. Over or under 50 points? (5pts)');
        $this->command->info('  3. Will there be overtime? (5pts) - Yes (+10 bonus), No');
        $this->command->info('');
        $this->command->info('Player answers:');
        $this->command->info('  Player 1: Team A, Over, No');
        $this->command->info('  Player 2: Team A, Under, No');
        $this->command->info('  Player 3: Team B, Over, Yes');
        $this->command->info('  Player 4: Team B, Under, Yes');
        $this->command->info('');
        $this->command->info('Login as Bert, set answers, then check leaderboard!');
    }
}
