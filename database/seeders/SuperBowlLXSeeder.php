<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Event;
use App\Models\Group;
use App\Models\QuestionTemplate;
use App\Models\EventQuestion;

class SuperBowlLXSeeder extends Seeder
{
    /**
     * Variable substitutions for Super Bowl LX.
     */
    protected array $variables = [
        // Teams
        'afc_team_or_visitor_team' => 'Patriots',
        'nfc_team_or_home_team' => 'Seahawks',

        // Team 1 (Patriots) Players
        'afc_qb_or_visitor_qb' => 'Drake Maye',
        'afc_rb1_or_visitor_rb1' => 'Rhamondre Stevenson',
        'afc_wr1_or_visitor_wr1' => 'Stefon Diggs',
        'afc_wr2_or_visitor_wr2' => 'Kayshon Boutte',

        // Team 2 (Seahawks) Players
        'nfc_qb_or_home_qb' => 'Sam Darnold',
        'nfc_rb1_or_home_rb1' => 'Kenneth Walker',
        'nfc_wr1_or_home_wr1' => 'J. Smith-Njigba',
        'nfc_wr2_or_home_wr2' => 'Cooper Kupp',

        // National Anthem
        'anthem_singer' => 'Charlie Puth',
        'anthem_duration' => '2 minutes',

        // Liquid Color
        'liquid_color_1' => 'Orange',
        'liquid_color_2' => 'Blue',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', 'bertkeele@gmail.com')->first();

        if (!$user) {
            $this->command->error('Admin user not found. Run DatabaseSeeder first.');
            return;
        }

        // Create Super Bowl LX event
        $event = Event::create([
            'name' => 'Super Bowl LX',
            'description' => 'Patriots vs. Seahawks',
            'event_date' => '2026-02-08 18:30:00',
            'status' => 'open',
            'category' => 'Football',
            'event_type' => 'GameQuiz',
            'created_by' => $user->id,
        ]);

        // Create group with captain grading
        $group = Group::create([
            'name' => "Bert's SB Bash",
            'code' => strtoupper(Str::random(6)),
            'description' => 'Super Bowl LX Party',
            'is_public' => false,
            'event_id' => $event->id,
            'grading_source' => 'captain',
            'created_by' => $user->id,
        ]);

        // Add Bert as captain of the group
        $group->users()->attach($user->id, [
            'joined_at' => now(),
            'is_captain' => true,
        ]);

        // Import all templates into the event
        $templates = QuestionTemplate::orderBy('display_order')->get();
        $questionCount = 0;

        foreach ($templates as $index => $template) {
            // Substitute variables in question text
            $questionText = $this->substituteVariables($template->question_text);

            // Substitute variables in options
            $options = $this->substituteOptionsVariables($template->default_options);

            EventQuestion::create([
                'event_id' => $event->id,
                'template_id' => $template->id,
                'question_text' => $questionText,
                'question_type' => $template->question_type,
                'options' => $options,
                'points' => $template->default_points,
                'display_order' => $index + 1,
            ]);

            $questionCount++;
        }

        $this->command->info("Super Bowl LX event created with {$questionCount} questions.");
        $this->command->info("Group 'Bert's SB Bash' created with captain grading.");
    }

    /**
     * Substitute variables in a string.
     */
    protected function substituteVariables(string $text): string
    {
        foreach ($this->variables as $key => $value) {
            $text = str_replace("{{$key}}", $value, $text);
        }
        return $text;
    }

    /**
     * Substitute variables in options array.
     */
    protected function substituteOptionsVariables(?array $options): ?array
    {
        if (!$options) {
            return null;
        }

        return array_map(function ($option) {
            if (is_string($option)) {
                return $this->substituteVariables($option);
            }

            if (is_array($option) && isset($option['label'])) {
                $option['label'] = $this->substituteVariables($option['label']);
            }

            return $option;
        }, $options);
    }
}
