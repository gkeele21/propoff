<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\QuestionTemplate;
use Illuminate\Support\Facades\DB;

class QuestionTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Football and Super Bowl question templates based on prop bet sheet.
     *
     * Token naming convention:
     * - {afc_team_or_visitor_team} / {nfc_team_or_home_team} - for teams
     * - {afc_qb_or_visitor_qb} / {nfc_qb_or_home_qb} - for QBs
     * - {afc_rb1_or_visitor_rb1} / {nfc_rb1_or_home_rb1} - for RBs
     * - {afc_wr1_or_visitor_wr1} / {nfc_wr1_or_home_wr1} - for WRs
     */
    public function run(): void
    {
        // Clear existing templates (disable foreign key checks to allow deletion)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('question_templates')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $templates = [
            // =====================================================
            // PRE-GAME / HALFTIME / POST-GAME (Super Bowl specific)
            // =====================================================

            // Q1 - National Anthem Duration
            [
                'title' => 'National Anthem Duration',
                'question_text' => 'How long will it take {anthem_singer} to sing the National Anthem?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => 'Under {anthem_duration}', 'points' => 0],
                    ['label' => '{anthem_duration} or more', 'points' => 0]
                ],
                'variables' => ['anthem_singer', 'anthem_duration'],
                'category' => 'Super Bowl,Football',
                'default_points' => 1,
            ],

            // Q2 - Coin Toss
            [
                'title' => 'Coin Toss Result',
                'question_text' => 'What will be the coin toss result?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => 'Heads', 'points' => 0],
                    ['label' => 'Tails', 'points' => 0]
                ],
                'variables' => [],
                'category' => 'Super Bowl,Football',
                'default_points' => 1,
            ],

            // Q3 - Liquid Color on Coach
            [
                'title' => 'Liquid Color on Winning Coach',
                'question_text' => 'What will be the color of liquid poured on the winning coach?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => '{liquid_color_1}', 'points' => 0],
                    ['label' => '{liquid_color_2}', 'points' => 0],
                    ['label' => 'Any other color or no liquid poured', 'points' => 0]
                ],
                'variables' => ['liquid_color_1', 'liquid_color_2'],
                'category' => 'Super Bowl,Football',
                'default_points' => 1,
            ],

            // Q4 - MVP Winner
            [
                'title' => 'MVP Winner',
                'question_text' => 'Who will win the MVP?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => '{afc_qb_or_visitor_qb} ({afc_team_or_visitor_team} quarterback)', 'points' => 0],
                    ['label' => '{nfc_qb_or_home_qb} ({nfc_team_or_home_team} quarterback)', 'points' => 0],
                    ['label' => 'Any other player', 'points' => 0]
                ],
                'variables' => ['afc_qb_or_visitor_qb', 'afc_team_or_visitor_team', 'nfc_qb_or_home_qb', 'nfc_team_or_home_team'],
                'category' => 'Super Bowl,Football',
                'default_points' => 3,
            ],

            // Q5 - MVP Thanks First
            [
                'title' => 'MVP Thanks First',
                'question_text' => 'Who will the MVP thank first?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => 'Teammates', 'points' => 0],
                    ['label' => 'God', 'points' => 0],
                    ['label' => 'Any other result', 'points' => 0]
                ],
                'variables' => [],
                'category' => 'Super Bowl,Football',
                'default_points' => 1,
            ],

            // =====================================================
            // GAME FIRSTS
            // =====================================================

            // Q6 - AFC/Visitor First Possession
            [
                'title' => 'Team 1 First Offensive Possession',
                'question_text' => 'What will the {afc_team_or_visitor_team} do on their first offensive possession?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => 'Score', 'points' => 0],
                    ['label' => 'Punt', 'points' => 0],
                    ['label' => 'Any other result', 'points' => 0]
                ],
                'variables' => ['afc_team_or_visitor_team'],
                'category' => 'Football',
                'default_points' => 1,
            ],

            // Q7 - NFC/Home First Possession
            [
                'title' => 'Team 2 First Offensive Possession',
                'question_text' => 'What will the {nfc_team_or_home_team} do on their first offensive possession?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => 'Score', 'points' => 0],
                    ['label' => 'Punt', 'points' => 0],
                    ['label' => 'Any other result', 'points' => 0]
                ],
                'variables' => ['nfc_team_or_home_team'],
                'category' => 'Football',
                'default_points' => 1,
            ],

            // Q8 - First Scoring Play
            [
                'title' => 'First Scoring Play',
                'question_text' => 'What will be the first scoring play?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => 'Offensive touchdown', 'points' => 0],
                    ['label' => 'Field Goal', 'points' => 0],
                    ['label' => 'Defensive or Kick / Punt Return score', 'points' => 0]
                ],
                'variables' => [],
                'category' => 'Football',
                'default_points' => 1,
            ],

            // Q9 - First AFC/Visitor TD Scorer
            [
                'title' => 'Team 1 Touchdown Scorer',
                'question_text' => 'Who scores the first {afc_team_or_visitor_team} touchdown?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => '{afc_rb1_or_visitor_rb1} - RB', 'points' => 0],
                    ['label' => '{afc_wr1_or_visitor_wr1} - WR', 'points' => 0],
                    ['label' => '{afc_wr2_or_visitor_wr2} - WR', 'points' => 0],
                    ['label' => 'Any other player or no touchdown', 'points' => 0]
                ],
                'variables' => ['afc_team_or_visitor_team', 'afc_rb1_or_visitor_rb1', 'afc_wr1_or_visitor_wr1', 'afc_wr2_or_visitor_wr2'],
                'category' => 'Football',
                'default_points' => 2,
            ],

            // Q10 - First NFC/Home TD Scorer
            [
                'title' => 'Team 2 Touchdown Scorer',
                'question_text' => 'Who scores the first {nfc_team_or_home_team} touchdown?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => '{nfc_rb1_or_home_rb1} - RB', 'points' => 0],
                    ['label' => '{nfc_wr1_or_home_wr1} - WR', 'points' => 0],
                    ['label' => '{nfc_wr2_or_home_wr2} - WR', 'points' => 0],
                    ['label' => 'Any other player or no touchdown', 'points' => 0]
                ],
                'variables' => ['nfc_team_or_home_team', 'nfc_rb1_or_home_rb1', 'nfc_wr1_or_home_wr1', 'nfc_wr2_or_home_wr2'],
                'category' => 'Football',
                'default_points' => 2,
            ],

            // Q11 - First Turnover
            [
                'title' => 'First Turnover',
                'question_text' => 'What will be the first turnover?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => 'Fumble', 'points' => 0],
                    ['label' => 'Interception', 'points' => 0],
                    ['label' => 'Turnover on Downs or No Turnovers', 'points' => 0]
                ],
                'variables' => [],
                'category' => 'Football',
                'default_points' => 1,
            ],

            // Q12 - First to 10 Points
            [
                'title' => 'First Team to 10 Points',
                'question_text' => 'Which team scores 10+ points first?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => '{afc_team_or_visitor_team}', 'points' => 0],
                    ['label' => '{nfc_team_or_home_team}', 'points' => 0]
                ],
                'variables' => ['afc_team_or_visitor_team', 'nfc_team_or_home_team'],
                'category' => 'Football',
                'default_points' => 1,
            ],

            // =====================================================
            // IN GAME OCCURRENCES
            // =====================================================

            // Q13 - Score in Last 2 Min of 1st Half
            [
                'title' => 'Score in Last 2 Minutes of 1st Half',
                'question_text' => 'Will a score occur in the last 2 minutes of the 1st half?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => 'Yes', 'points' => 0],
                    ['label' => 'No', 'points' => 0]
                ],
                'variables' => [],
                'category' => 'Football',
                'default_points' => 1,
            ],

            // Q14 - Score in Last 2 Min of Game
            [
                'title' => 'Score in Last 2 Minutes of Game',
                'question_text' => 'Will a score occur in the last 2 minutes of the game?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => 'Yes', 'points' => 0],
                    ['label' => 'No', 'points' => 0]
                ],
                'variables' => [],
                'category' => 'Football',
                'default_points' => 1,
            ],

            // Q15 - Safety
            [
                'title' => 'Safety Occurrence',
                'question_text' => 'Will any team get a safety?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => 'Yes', 'points' => 1],
                    ['label' => 'No', 'points' => 0]
                ],
                'variables' => [],
                'category' => 'Football',
                'default_points' => 1,
            ],

            // Q16 - Defensive/Special Teams TD
            [
                'title' => 'Defensive or Kick/Punt Return Touchdown',
                'question_text' => 'Will a defensive or Kick / Punt Return touchdown occur?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => 'Yes', 'points' => 1],
                    ['label' => 'No', 'points' => 0]
                ],
                'variables' => [],
                'category' => 'Football',
                'default_points' => 1,
            ],

            // Q17 - 2 Point Conversion Attempted
            [
                'title' => '2 Point Conversion Attempt',
                'question_text' => 'Will a 2 point conversion be attempted?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => 'Yes', 'points' => 0],
                    ['label' => 'No', 'points' => 0]
                ],
                'variables' => [],
                'category' => 'Football',
                'default_points' => 1,
            ],

            // Q18 - Kick Attempt Result
            [
                'title' => 'Kick Attempt Result',
                'question_text' => 'A kick attempt will:',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => 'Hit the uprights / crossbar', 'points' => 1],
                    ['label' => 'Always be successful', 'points' => 0],
                    ['label' => 'Miss short / wide or get blocked', 'points' => 0]
                ],
                'variables' => [],
                'category' => 'Football',
                'default_points' => 1,
            ],

            // Q19 - Non-Starting QB Pass Attempt
            [
                'title' => '3 Players Attempt a Pass',
                'question_text' => 'Will another player besides the starting quarterbacks attempt a pass?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => 'Yes', 'points' => 1],
                    ['label' => 'No', 'points' => 0]
                ],
                'variables' => [],
                'category' => 'Football',
                'default_points' => 1,
            ],

            // Q20 - Greater Distance
            [
                'title' => 'Greater Distance - FG vs Touchdown',
                'question_text' => 'Which will be of greater distance?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => 'Longest touchdown', 'points' => 0],
                    ['label' => 'Longest made field goal kick or tie', 'points' => 0]
                ],
                'variables' => [],
                'category' => 'Football',
                'default_points' => 1,
            ],

            // Q21 - 1 Yard Touchdown
            [
                'title' => '1 Yard Touchdown',
                'question_text' => 'Will there be a 1 yard touchdown?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => 'Yes', 'points' => 0],
                    ['label' => 'No', 'points' => 0]
                ],
                'variables' => [],
                'category' => 'Football',
                'default_points' => 1,
            ],

            // Q22 - 3 Consecutive Scores
            [
                'title' => '3 Consecutive Scores',
                'question_text' => 'Will either team score 3 consecutive times without the other team scoring?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => 'Yes', 'points' => 1],
                    ['label' => 'No', 'points' => 0]
                ],
                'variables' => [],
                'category' => 'Football',
                'default_points' => 1,
            ],

            // Q23 - Overtime
            [
                'title' => 'Overtime',
                'question_text' => 'Will the game go into overtime?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => 'Yes', 'points' => 2],
                    ['label' => 'No', 'points' => 0]
                ],
                'variables' => [],
                'category' => 'Football',
                'default_points' => 1,
            ],

            // =====================================================
            // GAME SCORING
            // =====================================================

            // Q24 - AFC/Visitor Halftime Points
            [
                'title' => 'Team 1 Halftime Points',
                'question_text' => 'How many points will the {afc_team_or_visitor_team} have at halftime?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => '0-14 Points', 'points' => 0],
                    ['label' => '15+ Points', 'points' => 0]
                ],
                'variables' => ['afc_team_or_visitor_team'],
                'category' => 'Football',
                'default_points' => 1,
            ],

            // Q25 - NFC/Home Halftime Points
            [
                'title' => 'Team 2 Halftime Points',
                'question_text' => 'How many points will the {nfc_team_or_home_team} have at halftime?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => '0-14 Points', 'points' => 0],
                    ['label' => '15+ Points', 'points' => 0]
                ],
                'variables' => ['nfc_team_or_home_team'],
                'category' => 'Football',
                'default_points' => 1,
            ],

            // Q26 - First to Score 2nd Half
            [
                'title' => 'First to Score in 2nd Half',
                'question_text' => 'Who will score first in the 2nd half?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => '{afc_team_or_visitor_team}', 'points' => 0],
                    ['label' => '{nfc_team_or_home_team}', 'points' => 0]
                ],
                'variables' => ['afc_team_or_visitor_team', 'nfc_team_or_home_team'],
                'category' => 'Football',
                'default_points' => 1,
            ],

            // Q27 - Last to Score
            [
                'title' => 'Last Team to Score',
                'question_text' => 'Which team will be the last to score?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => '{afc_team_or_visitor_team}', 'points' => 0],
                    ['label' => '{nfc_team_or_home_team}', 'points' => 0]
                ],
                'variables' => ['afc_team_or_visitor_team', 'nfc_team_or_home_team'],
                'category' => 'Football',
                'default_points' => 1,
            ],

            // Q28 - Game Winner
            [
                'title' => 'Game Winner',
                'question_text' => 'Who will win the game?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => '{afc_team_or_visitor_team}', 'points' => 0],
                    ['label' => '{nfc_team_or_home_team}', 'points' => 0]
                ],
                'variables' => ['afc_team_or_visitor_team', 'nfc_team_or_home_team'],
                'category' => 'Football',
                'default_points' => 3,
            ],

            // Q29 - AFC/Visitor Final Score
            [
                'title' => 'Team 1 Final Score Range',
                'question_text' => 'What will be the {afc_team_or_visitor_team} final score?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => '0-19 Points', 'points' => 0],
                    ['label' => '20-29 Points', 'points' => 0],
                    ['label' => '30-39 Points', 'points' => 0],
                    ['label' => '40 or more Points', 'points' => 0]
                ],
                'variables' => ['afc_team_or_visitor_team'],
                'category' => 'Football',
                'default_points' => 1,
            ],

            // Q30 - NFC/Home Final Score
            [
                'title' => 'Team 2 Final Score Range',
                'question_text' => 'What will be the {nfc_team_or_home_team} final score?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => '0-19 Points', 'points' => 0],
                    ['label' => '20-29 Points', 'points' => 0],
                    ['label' => '30-39 Points', 'points' => 0],
                    ['label' => '40 or more Points', 'points' => 0]
                ],
                'variables' => ['nfc_team_or_home_team'],
                'category' => 'Football',
                'default_points' => 1,
            ],

            // Q31 - Victory Margin
            [
                'title' => 'Victory Margin',
                'question_text' => 'What will be the final victory margin?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => '1-4 Points', 'points' => 0],
                    ['label' => '5-9 Points', 'points' => 0],
                    ['label' => '10 or more Points', 'points' => 0]
                ],
                'variables' => [],
                'category' => 'Football',
                'default_points' => 1,
            ],

            // Q32 - Highest Scoring Quarter
            [
                'title' => 'Highest Scoring Quarter',
                'question_text' => 'Which quarter will have the most points scored?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => '1st Quarter', 'points' => 0],
                    ['label' => '2nd Quarter', 'points' => 0],
                    ['label' => '3rd Quarter', 'points' => 0],
                    ['label' => '4th Quarter', 'points' => 0]
                ],
                'variables' => [],
                'category' => 'Football',
                'default_points' => 1,
            ],

            // =====================================================
            // GAME TOTALS
            // =====================================================

            // Q33 - Most Offensive Yards
            [
                'title' => 'Most Total Offensive Yards',
                'question_text' => 'Which team will have the most total offensive yards?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => '{afc_team_or_visitor_team}', 'points' => 0],
                    ['label' => '{nfc_team_or_home_team}', 'points' => 0]
                ],
                'variables' => ['afc_team_or_visitor_team', 'nfc_team_or_home_team'],
                'category' => 'Football',
                'default_points' => 2,
            ],

            // Q34 - Quarterback Total Yards
            [
                'title' => 'Quarterback Total Yards',
                'question_text' => 'Which quarterback will have more total yards?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => '{afc_qb_or_visitor_qb} ({afc_team_or_visitor_team})', 'points' => 0],
                    ['label' => '{nfc_qb_or_home_qb} ({nfc_team_or_home_team})', 'points' => 0]
                ],
                'variables' => ['afc_qb_or_visitor_qb', 'afc_team_or_visitor_team', 'nfc_qb_or_home_qb', 'nfc_team_or_home_team'],
                'category' => 'Football',
                'default_points' => 2,
            ],

            // Q35 - Most Pass TDs
            [
                'title' => 'Most Passing Touchdowns',
                'question_text' => 'Who will have the most passing touchdowns?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => '{afc_qb_or_visitor_qb} ({afc_team_or_visitor_team})', 'points' => 0],
                    ['label' => '{nfc_qb_or_home_qb} ({nfc_team_or_home_team})', 'points' => 0],
                    ['label' => 'Any other player or tie', 'points' => 0]
                ],
                'variables' => ['afc_qb_or_visitor_qb', 'afc_team_or_visitor_team', 'nfc_qb_or_home_qb', 'nfc_team_or_home_team'],
                'category' => 'Football',
                'default_points' => 2,
            ],

            // Q36 - Most Interceptions
            [
                'title' => 'Most Interceptions Thrown',
                'question_text' => 'Who will throw more interceptions?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => '{afc_qb_or_visitor_qb} ({afc_team_or_visitor_team})', 'points' => 0],
                    ['label' => '{nfc_qb_or_home_qb} ({nfc_team_or_home_team})', 'points' => 0],
                    ['label' => 'Any other player or tie', 'points' => 0]
                ],
                'variables' => ['afc_qb_or_visitor_qb', 'afc_team_or_visitor_team', 'nfc_qb_or_home_qb', 'nfc_team_or_home_team'],
                'category' => 'Football',
                'default_points' => 1,
            ],

            // Q37 - Most Rushing Yards
            [
                'title' => 'Most Rushing Yards',
                'question_text' => 'Who will have more rushing yards?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => '{afc_rb1_or_visitor_rb1} ({afc_team_or_visitor_team})', 'points' => 0],
                    ['label' => '{nfc_rb1_or_home_rb1} ({nfc_team_or_home_team})', 'points' => 0],
                    ['label' => 'Any other player or tie', 'points' => 0]
                ],
                'variables' => ['afc_rb1_or_visitor_rb1', 'afc_team_or_visitor_team', 'nfc_rb1_or_home_rb1', 'nfc_team_or_home_team'],
                'category' => 'Football',
                'default_points' => 2,
            ],

            // Q38 - Most Receptions
            [
                'title' => 'Most Receptions',
                'question_text' => 'Who will have the most receptions?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => '{afc_wr1_or_visitor_wr1} ({afc_team_or_visitor_team})', 'points' => 0],
                    ['label' => '{nfc_wr1_or_home_wr1} ({nfc_team_or_home_team})', 'points' => 0],
                    ['label' => 'Any other player or tie', 'points' => 0]
                ],
                'variables' => ['afc_wr1_or_visitor_wr1', 'afc_team_or_visitor_team', 'nfc_wr1_or_home_wr1', 'nfc_team_or_home_team'],
                'category' => 'Football',
                'default_points' => 2,
            ],

            // Q39 - Most Receiving Yards
            [
                'title' => 'Most Receiving Yards',
                'question_text' => 'Who will have the most receiving yards?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => '{afc_wr1_or_visitor_wr1} ({afc_team_or_visitor_team})', 'points' => 0],
                    ['label' => '{nfc_wr1_or_home_wr1} ({nfc_team_or_home_team})', 'points' => 0],
                    ['label' => 'Any other player or tie', 'points' => 0]
                ],
                'variables' => ['afc_wr1_or_visitor_wr1', 'afc_team_or_visitor_team', 'nfc_wr1_or_home_wr1', 'nfc_team_or_home_team'],
                'category' => 'Football',
                'default_points' => 2,
            ],

            // Q40 - Total QB Sacks
            [
                'title' => 'Total Quarterback Sacks',
                'question_text' => 'How many total quarterback sacks will the defenses get in the game?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => '3 or less', 'points' => 0],
                    ['label' => '4-5', 'points' => 0],
                    ['label' => '6 or more', 'points' => 0]
                ],
                'variables' => [],
                'category' => 'Football',
                'default_points' => 2,
            ],

            // =====================================================
            // COMMERCIALS (Super Bowl specific)
            // =====================================================

            // Q41 - Commercial - Automotive
            [
                'title' => 'First Commercial - Automotive',
                'question_text' => 'Which Automotive brand commercial will air first after kickoff?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => 'Hyundai', 'points' => 0],
                    ['label' => 'Kia', 'points' => 0],
                    ['label' => 'Ram/Jeep', 'points' => 0],
                    ['label' => 'Toyota', 'points' => 0]
                ],
                'variables' => [],
                'category' => 'Super Bowl,Football,Commercials',
                'default_points' => 1,
            ],

            // Q42 - Commercial - Beer
            [
                'title' => 'First Commercial - Beer',
                'question_text' => 'Which Beer brand commercial will air first after kickoff?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => 'Bud Light', 'points' => 0],
                    ['label' => 'Budweiser', 'points' => 0],
                    ['label' => 'Coors', 'points' => 0],
                    ['label' => 'Michelob Ultra', 'points' => 0]
                ],
                'variables' => [],
                'category' => 'Super Bowl,Football,Commercials',
                'default_points' => 1,
            ],

            // Q43 - Commercial - Candy
            [
                'title' => 'First Commercial - Candy',
                'question_text' => 'Which Candy brand commercial will air first after kickoff?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => 'M&M\'s', 'points' => 0],
                    ['label' => 'Nerds', 'points' => 0],
                    ['label' => 'Reese\'s', 'points' => 0],
                    ['label' => 'Snickers', 'points' => 0]
                ],
                'variables' => [],
                'category' => 'Super Bowl,Football,Commercials',
                'default_points' => 1,
            ],

            // Q44 - Commercial - Chips
            [
                'title' => 'First Commercial - Chips',
                'question_text' => 'Which Chips brand commercial will air first after kickoff?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => 'Cheetos', 'points' => 0],
                    ['label' => 'Doritos', 'points' => 0],
                    ['label' => 'Pringles', 'points' => 0],
                    ['label' => 'Tostitos', 'points' => 0]
                ],
                'variables' => [],
                'category' => 'Super Bowl,Football,Commercials',
                'default_points' => 1,
            ],

            // Q45 - Commercial - Fast Food
            [
                'title' => 'First Commercial - Fast Food',
                'question_text' => 'Which Fast Food brand commercial will air first after kickoff?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => 'Dunkin\'', 'points' => 0],
                    ['label' => 'Little Caesars', 'points' => 0],
                    ['label' => 'McDonald\'s', 'points' => 0],
                    ['label' => 'Taco Bell', 'points' => 0]
                ],
                'variables' => [],
                'category' => 'Super Bowl,Football,Commercials',
                'default_points' => 1,
            ],

            // Q46 - Commercial - Food Delivery
            [
                'title' => 'First Commercial - Food Delivery',
                'question_text' => 'Which Food Delivery brand commercial will air first after kickoff?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => 'DoorDash', 'points' => 0],
                    ['label' => 'Grubhub', 'points' => 0],
                    ['label' => 'Instacart', 'points' => 0],
                    ['label' => 'Uber Eats', 'points' => 0]
                ],
                'variables' => [],
                'category' => 'Super Bowl,Football,Commercials',
                'default_points' => 1,
            ],

            // Q47 - Commercial - Insurance
            [
                'title' => 'First Commercial - Insurance',
                'question_text' => 'Which Insurance brand commercial will air first after kickoff?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => 'Allstate', 'points' => 0],
                    ['label' => 'Geico', 'points' => 0],
                    ['label' => 'Progressive', 'points' => 0],
                    ['label' => 'State Farm', 'points' => 0]
                ],
                'variables' => [],
                'category' => 'Super Bowl,Football,Commercials',
                'default_points' => 1,
            ],

            // Q48 - Commercial - Soda
            [
                'title' => 'First Commercial - Soda',
                'question_text' => 'Which Soda brand commercial will air first after kickoff?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => 'Coca Cola', 'points' => 0],
                    ['label' => 'Dr. Pepper', 'points' => 0],
                    ['label' => 'Mtn Dew', 'points' => 0],
                    ['label' => 'Pepsi', 'points' => 0]
                ],
                'variables' => [],
                'category' => 'Super Bowl,Football,Commercials',
                'default_points' => 1,
            ],

            // Q49 - Commercial - Streaming
            [
                'title' => 'First Commercial - Streaming',
                'question_text' => 'Which Streaming brand commercial will air first after kickoff?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => 'Disney+', 'points' => 0],
                    ['label' => 'Max', 'points' => 0],
                    ['label' => 'Paramount+', 'points' => 0],
                    ['label' => 'Peacock', 'points' => 0]
                ],
                'variables' => [],
                'category' => 'Super Bowl,Football,Commercials',
                'default_points' => 1,
            ],

            // Q50 - Commercial - Tech
            [
                'title' => 'First Commercial - Tech',
                'question_text' => 'Which Tech brand commercial will air first after kickoff?',
                'question_type' => 'multiple_choice',
                'default_options' => [
                    ['label' => 'Apple', 'points' => 0],
                    ['label' => 'Google', 'points' => 0],
                    ['label' => 'T-Mobile', 'points' => 0],
                    ['label' => 'Verizon', 'points' => 0]
                ],
                'variables' => [],
                'category' => 'Super Bowl,Football,Commercials',
                'default_points' => 1,
            ],
        ];

        foreach ($templates as $index => $template) {
            QuestionTemplate::create([
                ...$template,
                'display_order' => $index + 1,
                'created_by' => 1,
            ]);
        }

        $this->command->info('50 Football question templates created successfully!');
    }
}
