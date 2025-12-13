<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\EventQuestion;
use App\Models\EventAnswer;
use App\Models\User;

class AmericaSaysChristmasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create an admin user
        $admin = User::where('role', 'admin')->first();

        if (!$admin) {
            $admin = User::first();
        }

        // Create the Christmas America Says event
        $event = Event::create([
            'name' => 'Christmas America Says 2024',
            'description' => 'A festive game of America Says with Christmas-themed questions!',
            'category' => 'Christmas',
            'event_date' => now()->addDays(7),
            'status' => 'open',
            'created_by' => $admin->id,
        ]);

        // Question 1: Things you hang on a Christmas tree
        $question1 = EventQuestion::create([
            'event_id' => $event->id,
            'question_text' => 'Name something you hang on a Christmas tree',
        ]);

        $answers1 = [
            ['answer' => 'Ornaments', 'display_order' => 1],
            ['answer' => 'Lights', 'display_order' => 2],
            ['answer' => 'Star', 'display_order' => 3],
            ['answer' => 'Tinsel', 'display_order' => 4],
            ['answer' => 'Candy Canes', 'display_order' => 5],
            ['answer' => 'Angel', 'display_order' => 6],
            ['answer' => 'Garland', 'display_order' => 7],
        ];

        foreach ($answers1 as $answerData) {
            EventAnswer::create([
                'event_id' => $event->id,
                'event_question_id' => $question1->id,
                'correct_answer' => $answerData['answer'],
                'display_order' => $answerData['display_order'],
            ]);
        }

        // Question 2: Popular Christmas songs
        $question2 = EventQuestion::create([
            'event_id' => $event->id,
            'question_text' => 'Name a popular Christmas song',
        ]);

        $answers2 = [
            ['answer' => 'Jingle Bells', 'display_order' => 1],
            ['answer' => 'Silent Night', 'display_order' => 2],
            ['answer' => 'Rudolph The Red Nosed Reindeer', 'display_order' => 3],
            ['answer' => 'White Christmas', 'display_order' => 4],
            ['answer' => 'Deck The Halls', 'display_order' => 5],
            ['answer' => 'Frosty The Snowman', 'display_order' => 6],
            ['answer' => 'Joy To The World', 'display_order' => 7],
        ];

        foreach ($answers2 as $answerData) {
            EventAnswer::create([
                'event_id' => $event->id,
                'event_question_id' => $question2->id,
                'correct_answer' => $answerData['answer'],
                'display_order' => $answerData['display_order'],
            ]);
        }

        // Question 3: Christmas foods
        $question3 = EventQuestion::create([
            'event_id' => $event->id,
            'question_text' => 'Name a food people eat at Christmas',
        ]);

        $answers3 = [
            ['answer' => 'Turkey', 'display_order' => 1],
            ['answer' => 'Ham', 'display_order' => 2],
            ['answer' => 'Cookies', 'display_order' => 3],
            ['answer' => 'Candy Canes', 'display_order' => 4],
            ['answer' => 'Gingerbread', 'display_order' => 5],
            ['answer' => 'Pie', 'display_order' => 6],
            ['answer' => 'Eggnog', 'display_order' => 7],
        ];

        foreach ($answers3 as $answerData) {
            EventAnswer::create([
                'event_id' => $event->id,
                'event_question_id' => $question3->id,
                'correct_answer' => $answerData['answer'],
                'display_order' => $answerData['display_order'],
            ]);
        }

        // Question 4: Things Santa does
        $question4 = EventQuestion::create([
            'event_id' => $event->id,
            'question_text' => 'Name something Santa Claus does on Christmas Eve',
        ]);

        $answers4 = [
            ['answer' => 'Delivers Presents', 'display_order' => 1],
            ['answer' => 'Eats Cookies', 'display_order' => 2],
            ['answer' => 'Says Ho Ho Ho', 'display_order' => 3],
            ['answer' => 'Rides His Sleigh', 'display_order' => 4],
            ['answer' => 'Goes Down Chimneys', 'display_order' => 5],
            ['answer' => 'Checks His List', 'display_order' => 6],
            ['answer' => 'Feeds Reindeer', 'display_order' => 7],
        ];

        foreach ($answers4 as $answerData) {
            EventAnswer::create([
                'event_id' => $event->id,
                'event_question_id' => $question4->id,
                'correct_answer' => $answerData['answer'],
                'display_order' => $answerData['display_order'],
            ]);
        }

        // Question 5: Christmas colors
        $question5 = EventQuestion::create([
            'event_id' => $event->id,
            'question_text' => 'Name a color associated with Christmas',
        ]);

        $answers5 = [
            ['answer' => 'Red', 'display_order' => 1],
            ['answer' => 'Green', 'display_order' => 2],
            ['answer' => 'White', 'display_order' => 3],
            ['answer' => 'Gold', 'display_order' => 4],
            ['answer' => 'Silver', 'display_order' => 5],
            ['answer' => 'Blue', 'display_order' => 6],
            ['answer' => 'Purple', 'display_order' => 7],
        ];

        foreach ($answers5 as $answerData) {
            EventAnswer::create([
                'event_id' => $event->id,
                'event_question_id' => $question5->id,
                'correct_answer' => $answerData['answer'],
                'display_order' => $answerData['display_order'],
            ]);
        }

        // Question 6: Christmas movies
        $question6 = EventQuestion::create([
            'event_id' => $event->id,
            'question_text' => 'Name a popular Christmas movie',
        ]);

        $answers6 = [
            ['answer' => 'Home Alone', 'display_order' => 1],
            ['answer' => 'Elf', 'display_order' => 2],
            ['answer' => 'The Grinch', 'display_order' => 3],
            ['answer' => 'A Christmas Story', 'display_order' => 4],
            ['answer' => 'Miracle On 34th Street', 'display_order' => 5],
            ['answer' => 'Its A Wonderful Life', 'display_order' => 6],
            ['answer' => 'The Polar Express', 'display_order' => 7],
        ];

        foreach ($answers6 as $answerData) {
            EventAnswer::create([
                'event_id' => $event->id,
                'event_question_id' => $question6->id,
                'correct_answer' => $answerData['answer'],
                'display_order' => $answerData['display_order'],
            ]);
        }

        // Question 7: Things you find under the tree
        $question7 = EventQuestion::create([
            'event_id' => $event->id,
            'question_text' => 'Name something you find under the Christmas tree',
        ]);

        $answers7 = [
            ['answer' => 'Presents', 'display_order' => 1],
            ['answer' => 'Wrapping Paper', 'display_order' => 2],
            ['answer' => 'Tree Skirt', 'display_order' => 3],
            ['answer' => 'Stockings', 'display_order' => 4],
            ['answer' => 'Toys', 'display_order' => 5],
            ['answer' => 'Ribbons', 'display_order' => 6],
            ['answer' => 'Bows', 'display_order' => 7],
        ];

        foreach ($answers7 as $answerData) {
            EventAnswer::create([
                'event_id' => $event->id,
                'event_question_id' => $question7->id,
                'correct_answer' => $answerData['answer'],
                'display_order' => $answerData['display_order'],
            ]);
        }

        // Question 8: Santas reindeer
        $question8 = EventQuestion::create([
            'event_id' => $event->id,
            'question_text' => 'Name one of Santas reindeer',
        ]);

        $answers8 = [
            ['answer' => 'Rudolph', 'display_order' => 1],
            ['answer' => 'Dasher', 'display_order' => 2],
            ['answer' => 'Dancer', 'display_order' => 3],
            ['answer' => 'Prancer', 'display_order' => 4],
            ['answer' => 'Vixen', 'display_order' => 5],
            ['answer' => 'Comet', 'display_order' => 6],
            ['answer' => 'Cupid', 'display_order' => 7],
        ];

        foreach ($answers8 as $answerData) {
            EventAnswer::create([
                'event_id' => $event->id,
                'event_question_id' => $question8->id,
                'correct_answer' => $answerData['answer'],
                'display_order' => $answerData['display_order'],
            ]);
        }

        $this->command->info('Christmas America Says event created with ' . $event->questions()->count() . ' questions!');
        $this->command->info('Event ID: ' . $event->id);
    }
}
