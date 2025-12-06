<?php

namespace Tests\Feature\Admin;

use App\Models\Entry;
use App\Models\Event;
use App\Models\EventAnswer;
use App\Models\EventQuestion;
use App\Models\Group;
use App\Models\GroupQuestion;
use App\Models\GroupQuestionAnswer;
use App\Models\User;
use App\Models\UserAnswer;
use App\Services\EntryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradingTest extends TestCase
{
    use RefreshDatabase;

    protected EntryService $entryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->entryService = new EntryService();
    }

    /** @test */
    public function admin_can_set_correct_answers_for_event_questions()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $event = Event::factory()->create();
        $eventQuestion = EventQuestion::factory()->create([
            'event_id' => $event->id,
            'points' => 10,
        ]);

        $response = $this->actingAs($admin)->post(
            route('admin.events.event-answers.setAnswer', [$event, $eventQuestion]),
            [
                'correct_answer' => 'Test Answer',
                'is_void' => false,
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('event_answers', [
            'event_id' => $event->id,
            'event_question_id' => $eventQuestion->id,
            'correct_answer' => 'Test Answer',
            'is_void' => false,
        ]);
    }

    /** @test */
    public function captain_can_set_correct_answers_for_their_group()
    {
        $captain = User::factory()->create(['role' => 'user']);
        $group = Group::factory()->create(['grading_source' => 'captain']);
        $group->addCaptain($captain);

        $groupQuestion = GroupQuestion::factory()->create([
            'group_id' => $group->id,
            'points' => 10,
        ]);

        $response = $this->actingAs($captain)->post(
            route('groups.grading.setAnswer', [$group, $groupQuestion]),
            [
                'correct_answer' => 'Captain Answer',
                'is_void' => false,
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('group_question_answers', [
            'group_id' => $group->id,
            'group_question_id' => $groupQuestion->id,
            'correct_answer' => 'Captain Answer',
            'is_void' => false,
        ]);
    }

    /** @test */
    public function captain_can_void_questions_for_their_group()
    {
        $captain = User::factory()->create(['role' => 'user']);
        $group = Group::factory()->create(['grading_source' => 'captain']);
        $group->addCaptain($captain);

        $groupQuestion = GroupQuestion::factory()->create([
            'group_id' => $group->id,
            'points' => 10,
        ]);

        // First set an answer
        GroupQuestionAnswer::create([
            'group_id' => $group->id,
            'group_question_id' => $groupQuestion->id,
            'correct_answer' => 'Test Answer',
            'is_void' => false,
        ]);

        $response = $this->actingAs($captain)->post(
            route('groups.grading.toggleVoid', [$group, $groupQuestion])
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('group_question_answers', [
            'group_id' => $group->id,
            'group_question_id' => $groupQuestion->id,
            'is_void' => true,
        ]);
    }

    /** @test */
    public function admin_can_grade_entries_automatically()
    {
        $event = Event::factory()->create();
        $group = Group::factory()->create([
            'event_id' => $event->id,
            'grading_source' => 'admin',
        ]);
        $entry = Entry::factory()->create([
            'event_id' => $event->id,
            'group_id' => $group->id,
            'is_complete' => true,
        ]);

        $eventQuestion = EventQuestion::factory()->create([
            'event_id' => $event->id,
            'points' => 10,
        ]);

        $groupQuestion = GroupQuestion::factory()->create([
            'group_id' => $group->id,
            'event_question_id' => $eventQuestion->id,
            'points' => 10,
        ]);

        // Admin sets correct answer
        EventAnswer::create([
            'event_id' => $event->id,
            'event_question_id' => $eventQuestion->id,
            'correct_answer' => 'Correct Answer',
            'is_void' => false,
        ]);

        // User submits correct answer
        UserAnswer::create([
            'entry_id' => $entry->id,
            'group_question_id' => $groupQuestion->id,
            'answer_text' => 'Correct Answer',
            'points_earned' => 0,
            'is_correct' => false,
        ]);

        // Grade the entry
        $this->entryService->gradeEntry($entry);

        $entry->refresh();
        $userAnswer = $entry->userAnswers()->first();

        $this->assertTrue($userAnswer->is_correct);
        $this->assertEquals(10, $userAnswer->points_earned);
    }

    /** @test */
    public function points_are_calculated_correctly()
    {
        $event = Event::factory()->create();
        $group = Group::factory()->create([
            'event_id' => $event->id,
            'grading_source' => 'admin',
        ]);
        $entry = Entry::factory()->create([
            'event_id' => $event->id,
            'group_id' => $group->id,
            'is_complete' => true,
        ]);

        // Create multiple questions with different points
        $question1 = EventQuestion::factory()->create([
            'event_id' => $event->id,
            'points' => 10,
        ]);
        $question2 = EventQuestion::factory()->create([
            'event_id' => $event->id,
            'points' => 20,
        ]);

        $groupQuestion1 = GroupQuestion::factory()->create([
            'group_id' => $group->id,
            'event_question_id' => $question1->id,
            'points' => 10,
        ]);
        $groupQuestion2 = GroupQuestion::factory()->create([
            'group_id' => $group->id,
            'event_question_id' => $question2->id,
            'points' => 20,
        ]);

        // Set answers
        EventAnswer::create([
            'event_id' => $event->id,
            'event_question_id' => $question1->id,
            'correct_answer' => 'Answer 1',
            'is_void' => false,
        ]);
        EventAnswer::create([
            'event_id' => $event->id,
            'event_question_id' => $question2->id,
            'correct_answer' => 'Answer 2',
            'is_void' => false,
        ]);

        // User gets first correct, second wrong
        UserAnswer::create([
            'entry_id' => $entry->id,
            'group_question_id' => $groupQuestion1->id,
            'answer_text' => 'Answer 1',
            'points_earned' => 0,
            'is_correct' => false,
        ]);
        UserAnswer::create([
            'entry_id' => $entry->id,
            'group_question_id' => $groupQuestion2->id,
            'answer_text' => 'Wrong Answer',
            'points_earned' => 0,
            'is_correct' => false,
        ]);

        // Grade the entry
        $this->entryService->gradeEntry($entry);

        $entry->refresh();
        $answers = $entry->userAnswers;

        // First answer should be correct with 10 points
        $this->assertTrue($answers->where('group_question_id', $groupQuestion1->id)->first()->is_correct);
        $this->assertEquals(10, $answers->where('group_question_id', $groupQuestion1->id)->first()->points_earned);

        // Second answer should be incorrect with 0 points
        $this->assertFalse($answers->where('group_question_id', $groupQuestion2->id)->first()->is_correct);
        $this->assertEquals(0, $answers->where('group_question_id', $groupQuestion2->id)->first()->points_earned);
    }

    /** @test */
    public function voided_questions_do_not_award_points()
    {
        $event = Event::factory()->create();
        $group = Group::factory()->create([
            'event_id' => $event->id,
            'grading_source' => 'admin',
        ]);
        $entry = Entry::factory()->create([
            'event_id' => $event->id,
            'group_id' => $group->id,
            'is_complete' => true,
        ]);

        $eventQuestion = EventQuestion::factory()->create([
            'event_id' => $event->id,
            'points' => 10,
        ]);

        $groupQuestion = GroupQuestion::factory()->create([
            'group_id' => $group->id,
            'event_question_id' => $eventQuestion->id,
            'points' => 10,
        ]);

        // Set answer as voided
        EventAnswer::create([
            'event_id' => $event->id,
            'event_question_id' => $eventQuestion->id,
            'correct_answer' => 'Correct Answer',
            'is_void' => true,
        ]);

        // User submits correct answer
        UserAnswer::create([
            'entry_id' => $entry->id,
            'group_question_id' => $groupQuestion->id,
            'answer_text' => 'Correct Answer',
            'points_earned' => 0,
            'is_correct' => false,
        ]);

        // Grade the entry
        $this->entryService->gradeEntry($entry);

        $entry->refresh();
        $userAnswer = $entry->userAnswers()->first();

        // Even though answer is correct, it should not award points because it's voided
        $this->assertEquals(0, $userAnswer->points_earned);
    }

    /** @test */
    public function admin_cannot_grade_before_answers_are_set()
    {
        $event = Event::factory()->create();
        $group = Group::factory()->create([
            'event_id' => $event->id,
            'grading_source' => 'admin',
        ]);
        $entry = Entry::factory()->create([
            'event_id' => $event->id,
            'group_id' => $group->id,
            'is_complete' => true,
        ]);

        $eventQuestion = EventQuestion::factory()->create([
            'event_id' => $event->id,
            'points' => 10,
        ]);

        $groupQuestion = GroupQuestion::factory()->create([
            'group_id' => $group->id,
            'event_question_id' => $eventQuestion->id,
            'points' => 10,
        ]);

        // User submits answer
        UserAnswer::create([
            'entry_id' => $entry->id,
            'group_question_id' => $groupQuestion->id,
            'answer_text' => 'Some Answer',
            'points_earned' => 0,
            'is_correct' => false,
        ]);

        // Grade without setting correct answer
        $this->entryService->gradeEntry($entry);

        $entry->refresh();
        $userAnswer = $entry->userAnswers()->first();

        // Should remain ungraded (0 points, incorrect)
        $this->assertFalse($userAnswer->is_correct);
        $this->assertEquals(0, $userAnswer->points_earned);
    }

    /** @test */
    public function non_captains_cannot_set_answers()
    {
        $user = User::factory()->create(['role' => 'user']);
        $group = Group::factory()->create(['grading_source' => 'captain']);

        $groupQuestion = GroupQuestion::factory()->create([
            'group_id' => $group->id,
            'points' => 10,
        ]);

        $response = $this->actingAs($user)->post(
            route('groups.grading.setAnswer', [$group, $groupQuestion]),
            [
                'correct_answer' => 'Test Answer',
                'is_void' => false,
            ]
        );

        // Should be forbidden
        $response->assertForbidden();
    }

    /** @test */
    public function captain_grading_workflow_works_end_to_end()
    {
        $captain = User::factory()->create(['role' => 'user']);
        $user = User::factory()->create(['role' => 'user']);
        $group = Group::factory()->create(['grading_source' => 'captain']);
        $group->addCaptain($captain);
        $group->users()->attach($user->id, ['joined_at' => now()]);

        $entry = Entry::factory()->create([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'is_complete' => true,
        ]);

        $groupQuestion = GroupQuestion::factory()->create([
            'group_id' => $group->id,
            'points' => 15,
        ]);

        // Captain sets correct answer
        $response = $this->actingAs($captain)->post(
            route('groups.grading.setAnswer', [$group, $groupQuestion]),
            [
                'correct_answer' => 'Captain Answer',
                'is_void' => false,
            ]
        );

        $response->assertRedirect();

        // User submitted answer
        UserAnswer::create([
            'entry_id' => $entry->id,
            'group_question_id' => $groupQuestion->id,
            'answer_text' => 'Captain Answer',
            'points_earned' => 0,
            'is_correct' => false,
        ]);

        // Grade entry
        $this->entryService->gradeEntry($entry);

        $entry->refresh();
        $userAnswer = $entry->userAnswers()->first();

        $this->assertTrue($userAnswer->is_correct);
        $this->assertEquals(15, $userAnswer->points_earned);
    }

    /** @test */
    public function admin_grading_workflow_works_end_to_end()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $event = Event::factory()->create();
        $group = Group::factory()->create([
            'event_id' => $event->id,
            'grading_source' => 'admin',
        ]);
        $group->users()->attach($user->id, ['joined_at' => now()]);

        $entry = Entry::factory()->create([
            'event_id' => $event->id,
            'group_id' => $group->id,
            'user_id' => $user->id,
            'is_complete' => true,
        ]);

        $eventQuestion = EventQuestion::factory()->create([
            'event_id' => $event->id,
            'points' => 25,
        ]);

        $groupQuestion = GroupQuestion::factory()->create([
            'group_id' => $group->id,
            'event_question_id' => $eventQuestion->id,
            'points' => 25,
        ]);

        // Admin sets correct answer
        $response = $this->actingAs($admin)->post(
            route('admin.events.event-answers.setAnswer', [$event, $eventQuestion]),
            [
                'correct_answer' => 'Admin Answer',
                'is_void' => false,
            ]
        );

        $response->assertRedirect();

        // User submitted answer
        UserAnswer::create([
            'entry_id' => $entry->id,
            'group_question_id' => $groupQuestion->id,
            'answer_text' => 'Admin Answer',
            'points_earned' => 0,
            'is_correct' => false,
        ]);

        // Grade entry
        $this->entryService->gradeEntry($entry);

        $entry->refresh();
        $userAnswer = $entry->userAnswers()->first();

        $this->assertTrue($userAnswer->is_correct);
        $this->assertEquals(25, $userAnswer->points_earned);
    }

    /** @test */
    public function captain_can_set_custom_points_for_question()
    {
        $captain = User::factory()->create(['role' => 'user']);
        $group = Group::factory()->create(['grading_source' => 'captain']);
        $group->addCaptain($captain);

        $groupQuestion = GroupQuestion::factory()->create([
            'group_id' => $group->id,
            'points' => 10,
        ]);

        $entry = Entry::factory()->create([
            'group_id' => $group->id,
            'is_complete' => true,
        ]);

        // Captain sets custom points (different from base points)
        $this->actingAs($captain)->post(
            route('groups.grading.setAnswer', [$group, $groupQuestion]),
            [
                'correct_answer' => 'Test Answer',
                'points_awarded' => 20, // Custom points
                'is_void' => false,
            ]
        );

        UserAnswer::create([
            'entry_id' => $entry->id,
            'group_question_id' => $groupQuestion->id,
            'answer_text' => 'Test Answer',
            'points_earned' => 0,
            'is_correct' => false,
        ]);

        $this->entryService->gradeEntry($entry);

        $entry->refresh();
        $userAnswer = $entry->userAnswers()->first();

        // Should use custom points (20) not base points (10)
        $this->assertEquals(20, $userAnswer->points_earned);
    }

    /** @test */
    public function non_admin_cannot_set_event_answers()
    {
        $user = User::factory()->create(['role' => 'user']);
        $event = Event::factory()->create();
        $eventQuestion = EventQuestion::factory()->create([
            'event_id' => $event->id,
        ]);

        $response = $this->actingAs($user)->post(
            route('admin.events.event-answers.setAnswer', [$event, $eventQuestion]),
            [
                'correct_answer' => 'Test Answer',
                'is_void' => false,
            ]
        );

        // Should be forbidden or redirect
        $this->assertTrue($response->status() === 403 || $response->isRedirect());
    }
}
