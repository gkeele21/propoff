<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\QuestionTemplate;
use Illuminate\Support\Facades\DB;

class QuestionTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing templates (disable foreign key checks to allow deletion)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('question_templates')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $templates = [
            // Basketball Templates
            [
                'title' => 'Player Points Performance',
                'question_text' => 'Will {player_name} score over {points} points in this game?',
                'question_type' => 'yes_no',
                'default_options' => json_encode([
                    ['label' => 'Yes', 'points' => 5],
                    ['label' => 'No', 'points' => 0]
                ]),
                'variables' => ['player_name', 'points'],
                'category' => 'Basketball',
                'default_points' => 10,
            ],
            [
                'title' => 'Team Total Points Range',
                'question_text' => 'How many points will {team_name} score?',
                'question_type' => 'multiple_choice',
                'default_options' => json_encode([
                    ['label' => 'Under 100', 'points' => 0],
                    ['label' => '100-110', 'points' => 3],
                    ['label' => '111-120', 'points' => 5],
                    ['label' => 'Over 120', 'points' => 3]
                ]),
                'variables' => ['team_name'],
                'category' => 'Basketball',
                'default_points' => 15,
            ],
            [
                'title' => 'Triple Double Achievement',
                'question_text' => 'Will {player_name} record a triple-double?',
                'question_type' => 'yes_no',
                'default_options' => json_encode([
                    ['label' => 'Yes', 'points' => 20],
                    ['label' => 'No', 'points' => 0]
                ]),
                'variables' => ['player_name'],
                'category' => 'Basketball',
                'default_points' => 25,
            ],
            [
                'title' => 'Three-Pointers Made',
                'question_text' => 'How many three-pointers will {player_name} make?',
                'question_type' => 'numeric',
                'default_options' => null,
                'variables' => ['player_name'],
                'category' => 'Basketball',
                'default_points' => 12,
            ],
            [
                'title' => 'Game Winner Prediction',
                'question_text' => 'Which team will win the {team1} vs {team2} game?',
                'question_type' => 'multiple_choice',
                'default_options' => json_encode([
                    ['label' => '{team1}', 'points' => 10],
                    ['label' => '{team2}', 'points' => 10]
                ]),
                'variables' => ['team1', 'team2'],
                'category' => 'Basketball',
                'default_points' => 20,
            ],

            // Football Templates
            [
                'title' => 'Quarterback Passing Yards',
                'question_text' => 'Will {qb_name} throw for over {yards} yards?',
                'question_type' => 'yes_no',
                'default_options' => json_encode([
                    ['label' => 'Yes', 'points' => 8],
                    ['label' => 'No', 'points' => 0]
                ]),
                'variables' => ['qb_name', 'yards'],
                'category' => 'Football',
                'default_points' => 15,
            ],
            [
                'title' => 'Touchdown Count',
                'question_text' => 'How many touchdowns will {player_name} score?',
                'question_type' => 'multiple_choice',
                'default_options' => json_encode([
                    ['label' => '0', 'points' => 0],
                    ['label' => '1', 'points' => 5],
                    ['label' => '2', 'points' => 10],
                    ['label' => '3+', 'points' => 15]
                ]),
                'variables' => ['player_name'],
                'category' => 'Football',
                'default_points' => 18,
            ],
            [
                'title' => 'Field Goal Success',
                'question_text' => 'Will {kicker_name} make all field goal attempts?',
                'question_type' => 'yes_no',
                'default_options' => json_encode([
                    ['label' => 'Yes', 'points' => 12],
                    ['label' => 'No', 'points' => 0]
                ]),
                'variables' => ['kicker_name'],
                'category' => 'Football',
                'default_points' => 15,
            ],
            [
                'title' => 'Total Game Points',
                'question_text' => 'What will be the total combined score?',
                'question_type' => 'multiple_choice',
                'default_options' => json_encode([
                    ['label' => 'Under 40', 'points' => 5],
                    ['label' => '40-50', 'points' => 8],
                    ['label' => '51-60', 'points' => 8],
                    ['label' => 'Over 60', 'points' => 5]
                ]),
                'variables' => [],
                'category' => 'Football',
                'default_points' => 12,
            ],
            [
                'title' => 'Rushing Yards Achievement',
                'question_text' => 'Will {rb_name} rush for over {yards} yards?',
                'question_type' => 'yes_no',
                'default_options' => json_encode([
                    ['label' => 'Yes', 'points' => 7],
                    ['label' => 'No', 'points' => 0]
                ]),
                'variables' => ['rb_name', 'yards'],
                'category' => 'Football',
                'default_points' => 13,
            ],

            // Soccer Templates
            [
                'title' => 'Goal Scorer Prediction',
                'question_text' => 'Will {player_name} score a goal?',
                'question_type' => 'yes_no',
                'default_options' => json_encode([
                    ['label' => 'Yes', 'points' => 10],
                    ['label' => 'No', 'points' => 0]
                ]),
                'variables' => ['player_name'],
                'category' => 'Soccer',
                'default_points' => 15,
            ],
            [
                'title' => 'Match Result',
                'question_text' => 'What will be the result of {team1} vs {team2}?',
                'question_type' => 'multiple_choice',
                'default_options' => json_encode([
                    ['label' => '{team1} Win', 'points' => 10],
                    ['label' => 'Draw', 'points' => 8],
                    ['label' => '{team2} Win', 'points' => 10]
                ]),
                'variables' => ['team1', 'team2'],
                'category' => 'Soccer',
                'default_points' => 20,
            ],
            [
                'title' => 'Total Goals in Match',
                'question_text' => 'How many total goals will be scored?',
                'question_type' => 'multiple_choice',
                'default_options' => json_encode([
                    ['label' => '0-1', 'points' => 5],
                    ['label' => '2-3', 'points' => 8],
                    ['label' => '4-5', 'points' => 8],
                    ['label' => '6+', 'points' => 5]
                ]),
                'variables' => [],
                'category' => 'Soccer',
                'default_points' => 12,
            ],
            [
                'title' => 'Clean Sheet Prediction',
                'question_text' => 'Will {team_name} keep a clean sheet?',
                'question_type' => 'yes_no',
                'default_options' => json_encode([
                    ['label' => 'Yes', 'points' => 12],
                    ['label' => 'No', 'points' => 0]
                ]),
                'variables' => ['team_name'],
                'category' => 'Soccer',
                'default_points' => 15,
            ],
            [
                'title' => 'Yellow Card Count',
                'question_text' => 'How many yellow cards will be shown?',
                'question_type' => 'numeric',
                'default_options' => null,
                'variables' => [],
                'category' => 'Soccer',
                'default_points' => 10,
            ],

            // Baseball Templates
            [
                'title' => 'Home Run Prediction',
                'question_text' => 'Will {player_name} hit a home run?',
                'question_type' => 'yes_no',
                'default_options' => json_encode([
                    ['label' => 'Yes', 'points' => 15],
                    ['label' => 'No', 'points' => 0]
                ]),
                'variables' => ['player_name'],
                'category' => 'Baseball',
                'default_points' => 18,
            ],
            [
                'title' => 'Pitcher Strikeouts',
                'question_text' => 'How many strikeouts will {pitcher_name} record?',
                'question_type' => 'multiple_choice',
                'default_options' => json_encode([
                    ['label' => '0-3', 'points' => 3],
                    ['label' => '4-6', 'points' => 8],
                    ['label' => '7-9', 'points' => 10],
                    ['label' => '10+', 'points' => 15]
                ]),
                'variables' => ['pitcher_name'],
                'category' => 'Baseball',
                'default_points' => 16,
            ],
            [
                'title' => 'Total Runs Scored',
                'question_text' => 'What will be the total runs in the game?',
                'question_type' => 'multiple_choice',
                'default_options' => json_encode([
                    ['label' => 'Under 7', 'points' => 5],
                    ['label' => '7-10', 'points' => 8],
                    ['label' => '11-14', 'points' => 8],
                    ['label' => 'Over 14', 'points' => 5]
                ]),
                'variables' => [],
                'category' => 'Baseball',
                'default_points' => 12,
            ],
            [
                'title' => 'Batting Average Performance',
                'question_text' => 'Will {player_name} get at least {hits} hits?',
                'question_type' => 'yes_no',
                'default_options' => json_encode([
                    ['label' => 'Yes', 'points' => 8],
                    ['label' => 'No', 'points' => 0]
                ]),
                'variables' => ['player_name', 'hits'],
                'category' => 'Baseball',
                'default_points' => 12,
            ],
            [
                'title' => 'Stolen Base Attempt',
                'question_text' => 'Will {player_name} steal a base?',
                'question_type' => 'yes_no',
                'default_options' => json_encode([
                    ['label' => 'Yes', 'points' => 10],
                    ['label' => 'No', 'points' => 0]
                ]),
                'variables' => ['player_name'],
                'category' => 'Baseball',
                'default_points' => 14,
            ],

            // Hockey Templates
            [
                'title' => 'Goal Scorer Prediction',
                'question_text' => 'Will {player_name} score a goal?',
                'question_type' => 'yes_no',
                'default_options' => json_encode([
                    ['label' => 'Yes', 'points' => 12],
                    ['label' => 'No', 'points' => 0]
                ]),
                'variables' => ['player_name'],
                'category' => 'Hockey',
                'default_points' => 15,
            ],
            [
                'title' => 'Goalie Saves',
                'question_text' => 'Will {goalie_name} make over {saves} saves?',
                'question_type' => 'yes_no',
                'default_options' => json_encode([
                    ['label' => 'Yes', 'points' => 10],
                    ['label' => 'No', 'points' => 0]
                ]),
                'variables' => ['goalie_name', 'saves'],
                'category' => 'Hockey',
                'default_points' => 14,
            ],
            [
                'title' => 'Power Play Goals',
                'question_text' => 'Will {team_name} score a power play goal?',
                'question_type' => 'yes_no',
                'default_options' => json_encode([
                    ['label' => 'Yes', 'points' => 8],
                    ['label' => 'No', 'points' => 0]
                ]),
                'variables' => ['team_name'],
                'category' => 'Hockey',
                'default_points' => 12,
            ],
            [
                'title' => 'Total Goals in Game',
                'question_text' => 'How many total goals will be scored?',
                'question_type' => 'multiple_choice',
                'default_options' => json_encode([
                    ['label' => 'Under 5', 'points' => 5],
                    ['label' => '5-6', 'points' => 8],
                    ['label' => '7-8', 'points' => 8],
                    ['label' => 'Over 8', 'points' => 5]
                ]),
                'variables' => [],
                'category' => 'Hockey',
                'default_points' => 10,
            ],
            [
                'title' => 'Shutout Prediction',
                'question_text' => 'Will {goalie_name} record a shutout?',
                'question_type' => 'yes_no',
                'default_options' => json_encode([
                    ['label' => 'Yes', 'points' => 20],
                    ['label' => 'No', 'points' => 0]
                ]),
                'variables' => ['goalie_name'],
                'category' => 'Hockey',
                'default_points' => 25,
            ],

            // Tennis Templates
            [
                'title' => 'Match Winner',
                'question_text' => 'Who will win the {player1} vs {player2} match?',
                'question_type' => 'multiple_choice',
                'default_options' => json_encode([
                    ['label' => '{player1}', 'points' => 10],
                    ['label' => '{player2}', 'points' => 10]
                ]),
                'variables' => ['player1', 'player2'],
                'category' => 'Tennis',
                'default_points' => 15,
            ],
            [
                'title' => 'Total Sets Played',
                'question_text' => 'How many sets will be played?',
                'question_type' => 'multiple_choice',
                'default_options' => json_encode([
                    ['label' => '2 Sets', 'points' => 5],
                    ['label' => '3 Sets', 'points' => 8],
                    ['label' => '4 Sets', 'points' => 8],
                    ['label' => '5 Sets', 'points' => 5]
                ]),
                'variables' => [],
                'category' => 'Tennis',
                'default_points' => 12,
            ],
            [
                'title' => 'Aces Count',
                'question_text' => 'Will {player_name} serve over {aces} aces?',
                'question_type' => 'yes_no',
                'default_options' => json_encode([
                    ['label' => 'Yes', 'points' => 10],
                    ['label' => 'No', 'points' => 0]
                ]),
                'variables' => ['player_name', 'aces'],
                'category' => 'Tennis',
                'default_points' => 14,
            ],
            [
                'title' => 'Tiebreak Occurrence',
                'question_text' => 'Will there be a tiebreak in the match?',
                'question_type' => 'yes_no',
                'default_options' => json_encode([
                    ['label' => 'Yes', 'points' => 8],
                    ['label' => 'No', 'points' => 0]
                ]),
                'variables' => [],
                'category' => 'Tennis',
                'default_points' => 10,
            ],
            [
                'title' => 'Break Points Converted',
                'question_text' => 'How many break points will {player_name} convert?',
                'question_type' => 'numeric',
                'default_options' => null,
                'variables' => ['player_name'],
                'category' => 'Tennis',
                'default_points' => 12,
            ],

            // Golf Templates
            [
                'title' => 'Tournament Winner',
                'question_text' => 'Will {player_name} win the tournament?',
                'question_type' => 'yes_no',
                'default_options' => json_encode([
                    ['label' => 'Yes', 'points' => 25],
                    ['label' => 'No', 'points' => 0]
                ]),
                'variables' => ['player_name'],
                'category' => 'Golf',
                'default_points' => 30,
            ],
            [
                'title' => 'Under Par Score',
                'question_text' => 'Will {player_name} finish under par?',
                'question_type' => 'yes_no',
                'default_options' => json_encode([
                    ['label' => 'Yes', 'points' => 10],
                    ['label' => 'No', 'points' => 0]
                ]),
                'variables' => ['player_name'],
                'category' => 'Golf',
                'default_points' => 15,
            ],
            [
                'title' => 'Hole-in-One Prediction',
                'question_text' => 'Will there be a hole-in-one in the round?',
                'question_type' => 'yes_no',
                'default_options' => json_encode([
                    ['label' => 'Yes', 'points' => 30],
                    ['label' => 'No', 'points' => 0]
                ]),
                'variables' => [],
                'category' => 'Golf',
                'default_points' => 35,
            ],
            [
                'title' => 'Birdie Count',
                'question_text' => 'How many birdies will {player_name} make?',
                'question_type' => 'multiple_choice',
                'default_options' => json_encode([
                    ['label' => '0-2', 'points' => 3],
                    ['label' => '3-5', 'points' => 8],
                    ['label' => '6-8', 'points' => 10],
                    ['label' => '9+', 'points' => 15]
                ]),
                'variables' => ['player_name'],
                'category' => 'Golf',
                'default_points' => 16,
            ],
            [
                'title' => 'Top 10 Finish',
                'question_text' => 'Will {player_name} finish in the top 10?',
                'question_type' => 'yes_no',
                'default_options' => json_encode([
                    ['label' => 'Yes', 'points' => 15],
                    ['label' => 'No', 'points' => 0]
                ]),
                'variables' => ['player_name'],
                'category' => 'Golf',
                'default_points' => 18,
            ],

            // MMA Templates
            [
                'title' => 'Fight Winner',
                'question_text' => 'Who will win {fighter1} vs {fighter2}?',
                'question_type' => 'multiple_choice',
                'default_options' => json_encode([
                    ['label' => '{fighter1}', 'points' => 12],
                    ['label' => '{fighter2}', 'points' => 12]
                ]),
                'variables' => ['fighter1', 'fighter2'],
                'category' => 'MMA',
                'default_points' => 20,
            ],
            [
                'title' => 'Knockout Victory',
                'question_text' => 'Will the fight end in a knockout?',
                'question_type' => 'yes_no',
                'default_options' => json_encode([
                    ['label' => 'Yes', 'points' => 15],
                    ['label' => 'No', 'points' => 0]
                ]),
                'variables' => [],
                'category' => 'MMA',
                'default_points' => 18,
            ],
            [
                'title' => 'Fight Duration',
                'question_text' => 'Which round will the fight end in?',
                'question_type' => 'multiple_choice',
                'default_options' => json_encode([
                    ['label' => 'Round 1', 'points' => 10],
                    ['label' => 'Round 2', 'points' => 8],
                    ['label' => 'Round 3', 'points' => 8],
                    ['label' => 'Decision', 'points' => 5]
                ]),
                'variables' => [],
                'category' => 'MMA',
                'default_points' => 15,
            ],
            [
                'title' => 'Submission Victory',
                'question_text' => 'Will {fighter_name} win by submission?',
                'question_type' => 'yes_no',
                'default_options' => json_encode([
                    ['label' => 'Yes', 'points' => 18],
                    ['label' => 'No', 'points' => 0]
                ]),
                'variables' => ['fighter_name'],
                'category' => 'MMA',
                'default_points' => 20,
            ],
            [
                'title' => 'Total Takedowns',
                'question_text' => 'How many takedowns will there be?',
                'question_type' => 'numeric',
                'default_options' => null,
                'variables' => [],
                'category' => 'MMA',
                'default_points' => 12,
            ],

            // Boxing Templates
            [
                'title' => 'Fight Winner',
                'question_text' => 'Who will win {boxer1} vs {boxer2}?',
                'question_type' => 'multiple_choice',
                'default_options' => json_encode([
                    ['label' => '{boxer1}', 'points' => 12],
                    ['label' => '{boxer2}', 'points' => 12],
                    ['label' => 'Draw', 'points' => 5]
                ]),
                'variables' => ['boxer1', 'boxer2'],
                'category' => 'Boxing',
                'default_points' => 20,
            ],
            [
                'title' => 'Knockout Prediction',
                'question_text' => 'Will the fight end in a knockout?',
                'question_type' => 'yes_no',
                'default_options' => json_encode([
                    ['label' => 'Yes', 'points' => 15],
                    ['label' => 'No', 'points' => 0]
                ]),
                'variables' => [],
                'category' => 'Boxing',
                'default_points' => 18,
            ],
            [
                'title' => 'Total Rounds',
                'question_text' => 'How many rounds will the fight last?',
                'question_type' => 'multiple_choice',
                'default_options' => json_encode([
                    ['label' => '1-4 Rounds', 'points' => 10],
                    ['label' => '5-8 Rounds', 'points' => 8],
                    ['label' => '9-12 Rounds', 'points' => 5]
                ]),
                'variables' => [],
                'category' => 'Boxing',
                'default_points' => 15,
            ],
            [
                'title' => 'Knockdown Occurrence',
                'question_text' => 'Will {boxer_name} score a knockdown?',
                'question_type' => 'yes_no',
                'default_options' => json_encode([
                    ['label' => 'Yes', 'points' => 12],
                    ['label' => 'No', 'points' => 0]
                ]),
                'variables' => ['boxer_name'],
                'category' => 'Boxing',
                'default_points' => 15,
            ],
            [
                'title' => 'Unanimous Decision',
                'question_text' => 'Will the fight end in a unanimous decision?',
                'question_type' => 'yes_no',
                'default_options' => json_encode([
                    ['label' => 'Yes', 'points' => 8],
                    ['label' => 'No', 'points' => 0]
                ]),
                'variables' => [],
                'category' => 'Boxing',
                'default_points' => 12,
            ],

            // Esports Templates
            [
                'title' => 'Match Winner',
                'question_text' => 'Which team will win {team1} vs {team2}?',
                'question_type' => 'multiple_choice',
                'default_options' => json_encode([
                    ['label' => '{team1}', 'points' => 10],
                    ['label' => '{team2}', 'points' => 10]
                ]),
                'variables' => ['team1', 'team2'],
                'category' => 'Esports',
                'default_points' => 15,
            ],
            [
                'title' => 'First Blood',
                'question_text' => 'Which team will get first blood?',
                'question_type' => 'multiple_choice',
                'default_options' => json_encode([
                    ['label' => '{team1}', 'points' => 8],
                    ['label' => '{team2}', 'points' => 8]
                ]),
                'variables' => ['team1', 'team2'],
                'category' => 'Esports',
                'default_points' => 10,
            ],
            [
                'title' => 'Player MVP',
                'question_text' => 'Will {player_name} be the match MVP?',
                'question_type' => 'yes_no',
                'default_options' => json_encode([
                    ['label' => 'Yes', 'points' => 20],
                    ['label' => 'No', 'points' => 0]
                ]),
                'variables' => ['player_name'],
                'category' => 'Esports',
                'default_points' => 22,
            ],
            [
                'title' => 'Total Kills',
                'question_text' => 'How many total kills will {player_name} get?',
                'question_type' => 'numeric',
                'default_options' => null,
                'variables' => ['player_name'],
                'category' => 'Esports',
                'default_points' => 12,
            ],
            [
                'title' => 'Game Duration',
                'question_text' => 'How long will the game last?',
                'question_type' => 'multiple_choice',
                'default_options' => json_encode([
                    ['label' => 'Under 30 min', 'points' => 5],
                    ['label' => '30-40 min', 'points' => 8],
                    ['label' => '40-50 min', 'points' => 8],
                    ['label' => 'Over 50 min', 'points' => 5]
                ]),
                'variables' => [],
                'category' => 'Esports',
                'default_points' => 10,
            ],
        ];

        // Insert templates with display order (disable FK checks since we may not have users yet)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        foreach ($templates as $index => $template) {
            QuestionTemplate::create([
                ...$template,
                'display_order' => $index + 1,
                'created_by' => 1,
            ]);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('50 sports question templates created successfully!');
    }
}
