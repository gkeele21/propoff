<?php

namespace App\Http\Controllers\Captain;

use App\Http\Controllers\Controller;
use App\Http\Requests\Captain\SetAnswerRequest;
use App\Models\EventAnswer;
use App\Models\Group;
use App\Models\GroupQuestion;
use App\Models\GroupQuestionAnswer;
use App\Services\EntryService;
use App\Services\LeaderboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GradingController extends Controller
{
    protected $entryService;
    protected $leaderboardService;

    public function __construct(EntryService $entryService, LeaderboardService $leaderboardService)
    {
        $this->entryService = $entryService;
        $this->leaderboardService = $leaderboardService;
    }

    /**
     * Set the correct answer for a specific question.
     */
    public function setAnswer(SetAnswerRequest $request, Group $group, GroupQuestion $groupQuestion)
    {
        // Ensure question belongs to this group
        if ($groupQuestion->group_id !== $group->id) {
            abort(404);
        }

        // Check if group uses captain grading
        if ($group->grading_source !== 'captain') {
            return back()->with('error', 'This group uses admin grading. Cannot set captain answers.');
        }

        // Create or update the answer
        GroupQuestionAnswer::updateOrCreate(
            [
                'group_id' => $group->id,
                'group_question_id' => $groupQuestion->id,
            ],
            [
                'question_id' => $groupQuestion->event_question_id, // Can be null for custom questions
                'correct_answer' => $request->correct_answer,
                'points_awarded' => $request->points_awarded,
                'is_void' => $request->is_void ?? false,
            ]
        );

        // Sync to admin grading if requested and question is linked to an event question
        if ($request->sync_to_admin && $groupQuestion->event_question_id) {
            EventAnswer::updateOrCreate(
                [
                    'event_id' => $group->event_id,
                    'event_question_id' => $groupQuestion->event_question_id,
                ],
                [
                    'correct_answer' => $request->correct_answer,
                    'is_void' => $request->is_void ?? false,
                    'set_at' => now(),
                    'set_by' => auth()->id(),
                ]
            );
        }

        // Recalculate scores for all entries in this group
        $entries = $group->entries()->where('is_complete', true)->get();
        foreach ($entries as $entry) {
            $this->entryService->calculateScore($entry);
        }

        // Update leaderboard
        $this->leaderboardService->updateLeaderboard($group->event, $group);

        return back()->with('success', 'Answer set successfully! Scores recalculated.');
    }

    /**
     * Toggle void status for a question.
     */
    public function toggleVoid(Request $request, Group $group, GroupQuestion $groupQuestion)
    {
        // Ensure question belongs to this group
        if ($groupQuestion->group_id !== $group->id) {
            abort(404);
        }

        // Check if group uses captain grading
        if ($group->grading_source !== 'captain') {
            return back()->with('error', 'This group uses admin grading. Cannot modify captain answers.');
        }

        $answer = GroupQuestionAnswer::where('group_id', $group->id)
            ->where('group_question_id', $groupQuestion->id)
            ->first();

        if (!$answer) {
            return back()->with('error', 'No answer set for this question yet.');
        }

        $answer->update(['is_void' => !$answer->is_void]);

        // Recalculate scores
        $entries = $group->entries()->where('is_complete', true)->get();
        foreach ($entries as $entry) {
            $this->entryService->calculateScore($entry);
        }

        // Update leaderboard
        $this->leaderboardService->updateLeaderboard($group->event, $group);

        $status = $answer->is_void ? 'voided' : 'unvoided';

        return back()->with('success', "Question {$status} successfully! Scores recalculated.");
    }
}
