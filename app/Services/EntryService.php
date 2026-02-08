<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventAnswer;
use App\Models\EventQuestion;
use App\Models\Group;
use App\Models\GroupQuestion;
use App\Models\GroupQuestionAnswer;
use App\Models\Entry;
use App\Models\User;
use App\Models\UserAnswer;

class EntryService
{
    /**
     * Create a new entry for a user.
     */
    public function createEntry(Event $event, User $user, Group $group): Entry
    {
        // Calculate possible points from group's active questions
        // Before answers are set, include maximum possible bonuses
        $possiblePoints = $this->calculateTheoreticalMax($group);

        return Entry::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'group_id' => $group->id,
            'total_score' => 0,
            'possible_points' => $possiblePoints,
            'percentage' => 0,
            'is_complete' => false,
            'submitted_at' => null,
        ]);
    }

    /**
     * Save or update user answers for an entry.
     */
    public function saveAnswers(Entry $entry, array $answers): void
    {
        foreach ($answers as $answerData) {
            UserAnswer::updateOrCreate(
                [
                    'entry_id' => $entry->id,
                    'group_question_id' => $answerData['group_question_id'],
                ],
                [
                    'answer_text' => $answerData['answer_text'],
                    'points_earned' => 0, // Will be calculated during grading
                    'is_correct' => false, // Will be determined during grading
                ]
            );
        }
    }

    /**
     * Mark entry as complete.
     */
    public function completeEntry(Entry $entry): void
    {
        $entry->update([
            'is_complete' => true,
            'submitted_at' => now(),
        ]);
    }

    /**
     * Grade an entry based on group's grading source (captain or admin).
     *
     * DUAL GRADING SYSTEM:
     * - If group uses 'captain' grading: Uses group_question_answers table
     * - If group uses 'admin' grading: Uses event_answers table
     */
    public function gradeEntry(Entry $entry): void
    {
        $group = $entry->group;
        $userAnswers = $entry->userAnswers()->with('groupQuestion')->get();
        $totalScore = 0;
        $possiblePoints = 0;

        foreach ($userAnswers as $userAnswer) {
            $groupQuestion = $userAnswer->groupQuestion;

            if (!$groupQuestion) {
                // Skip if group question not found
                continue;
            }

            // Get correct answer based on grading source
            $correctAnswer = null;
            $isVoid = false;
            $pointsAwarded = null;

            if ($group->grading_source === 'captain') {
                // CAPTAIN GRADING: Use group_question_answers
                $groupAnswer = GroupQuestionAnswer::where('group_id', $group->id)
                    ->where('group_question_id', $groupQuestion->id)
                    ->first();

                if ($groupAnswer) {
                    $correctAnswer = $groupAnswer->correct_answer;
                    $isVoid = $groupAnswer->is_void;
                    $pointsAwarded = $groupAnswer->points_awarded;
                }
            } else {
                // ADMIN GRADING: Use event_answers
                if ($groupQuestion->event_question_id) {
                    $eventAnswer = EventAnswer::where('event_id', $entry->event_id)
                        ->where('event_question_id', $groupQuestion->event_question_id)
                        ->first();

                    if ($eventAnswer) {
                        $correctAnswer = $eventAnswer->correct_answer;
                        $isVoid = $eventAnswer->is_void;
                        // Admin answers don't have custom points_awarded
                    }
                }
            }

            // Skip if question is voided
            if ($isVoid) {
                $userAnswer->update([
                    'points_earned' => 0,
                    'is_correct' => false,
                ]);
                continue;
            }

            // Skip if no correct answer is set yet
            if (!$correctAnswer) {
                $userAnswer->update([
                    'points_earned' => 0,
                    'is_correct' => false,
                ]);
                // Still count towards possible points using USER'S selected answer
                $possiblePoints += $this->calculatePointsForAnswer($groupQuestion, $userAnswer->answer_text);
                continue;
            }

            // Check if answer is correct
            $isCorrect = false;
            $pointsEarned = 0;

            if ($this->compareAnswers($userAnswer->answer_text, $correctAnswer, $groupQuestion->question_type)) {
                $isCorrect = true;

                // Determine points earned
                if ($pointsAwarded !== null) {
                    // Use custom points if set
                    $pointsEarned = $pointsAwarded;
                } else {
                    // Calculate base + bonus for the chosen option
                    $pointsEarned = $this->calculatePointsForAnswer(
                        $groupQuestion,
                        $userAnswer->answer_text
                    );
                }
                $totalScore += $pointsEarned;

                // Only add to possible points if correct - wrong answers are lost points
                $possiblePoints += $pointsEarned;
            }
            // If wrong, don't add to possiblePoints - those points can no longer be earned

            $userAnswer->update([
                'points_earned' => $pointsEarned,
                'is_correct' => $isCorrect,
            ]);
        }

        // Add max possible points for unanswered questions ONLY if group is not locked
        // Once locked, unanswered questions = lost points (can't answer anymore)
        if (!$group->is_locked) {
            $answeredQuestionIds = $userAnswers->pluck('group_question_id')->toArray();
            $unansweredQuestions = $group->groupQuestions()
                ->where('is_active', true)
                ->whereNotIn('id', $answeredQuestionIds)
                ->get();

            foreach ($unansweredQuestions as $question) {
                $possiblePoints += $this->calculateMaxPointsForQuestion($question);
            }
        }

        // Update entry totals
        $percentage = $possiblePoints > 0 ? round(($totalScore / $possiblePoints) * 100, 2) : 0;

        $entry->update([
            'total_score' => $totalScore,
            'possible_points' => $possiblePoints,
            'percentage' => $percentage,
        ]);
    }

    /**
     * Recalculate score for an entry (alias for gradeEntry).
     * Used when answers are updated after entry.
     */
    public function calculateScore(Entry $entry): void
    {
        $this->gradeEntry($entry);
    }

    /**
     * Compare user answer with correct answer based on question type.
     */
    protected function compareAnswers(string $userAnswer, string $correctAnswer, string $questionType): bool
    {
        // Trim and normalize
        $userAnswer = trim($userAnswer);
        $correctAnswer = trim($correctAnswer);

        switch ($questionType) {
            case 'multiple_choice':
            case 'yes_no':
                // Case-insensitive comparison
                return strcasecmp($userAnswer, $correctAnswer) === 0;

            case 'numeric':
                // Numeric comparison with tolerance
                $userNum = (float) $userAnswer;
                $correctNum = (float) $correctAnswer;
                $tolerance = 0.01; // Allow small rounding errors
                return abs($userNum - $correctNum) <= $tolerance;

            case 'text':
                // Case-insensitive and trimmed comparison
                return strcasecmp($userAnswer, $correctAnswer) === 0;

            default:
                return $userAnswer === $correctAnswer;
        }
    }

    /**
     * Calculate points for a specific answer (base + option bonus).
     */
    protected function calculatePointsForAnswer(GroupQuestion $groupQuestion, string $answerText): int
    {
        $basePoints = $groupQuestion->points;
        $bonusPoints = 0;

        // For multiple choice, check if answer has bonus points
        if ($groupQuestion->question_type === 'multiple_choice' && $groupQuestion->options) {
            $options = is_string($groupQuestion->options) ? json_decode($groupQuestion->options, true) : $groupQuestion->options;

            if (is_array($options)) {
                foreach ($options as $option) {
                    // Handle new format: {label: "Yes", points: 2}
                    if (is_array($option) && isset($option['label'])) {
                        if (strcasecmp(trim($option['label']), trim($answerText)) === 0) {
                            $bonusPoints = $option['points'] ?? 0;
                            break;
                        }
                    }
                }
            }
        }

        return $basePoints + $bonusPoints;
    }

    /**
     * Calculate maximum possible points for a question.
     */
    protected function calculateMaxPointsForQuestion(GroupQuestion $groupQuestion): int
    {
        $basePoints = $groupQuestion->points;
        $maxBonus = 0;

        // For multiple choice, find the highest bonus
        if ($groupQuestion->question_type === 'multiple_choice' && $groupQuestion->options) {
            $options = is_string($groupQuestion->options) ? json_decode($groupQuestion->options, true) : $groupQuestion->options;

            if (is_array($options)) {
                foreach ($options as $option) {
                    // Handle new format: {label: "Yes", points: 2}
                    if (is_array($option) && isset($option['points'])) {
                        $maxBonus = max($maxBonus, $option['points'] ?? 0);
                    }
                }
            }
        }

        return $basePoints + $maxBonus;
    }

    /**
     * Get entry statistics for a user.
     */
    public function getUserEntryStats(User $user): array
    {
        return [
            'total_entries' => Entry::where('user_id', $user->id)->count(),
            'completed_entries' => Entry::where('user_id', $user->id)->where('is_complete', true)->count(),
            'average_score' => Entry::where('user_id', $user->id)
                ->where('is_complete', true)
                ->avg('percentage') ?? 0,
            'best_score' => Entry::where('user_id', $user->id)
                ->where('is_complete', true)
                ->max('percentage') ?? 0,
            'total_points' => Entry::where('user_id', $user->id)
                ->where('is_complete', true)
                ->sum('total_score'),
        ];
    }

    /**
     * Get all entries for an event.
     */
    public function getEventEntries(Event $event, bool $completedOnly = true)
    {
        $query = Entry::where('event_id', $event->id)
            ->with(['user', 'group']);

        if ($completedOnly) {
            $query->where('is_complete', true);
        }

        return $query->orderBy('submitted_at', 'desc')->get();
    }

    /**
     * Check if entry can be edited.
     */
    public function canEditEntry(Entry $entry): bool
    {
        // Check if event is past lock date
        if ($entry->event->lock_date && now()->isAfter($entry->event->lock_date)) {
            return false;
        }

        // Check if event status allows editing
        if (in_array($entry->event->status, ['completed', 'in_progress'])) {
            return false;
        }

        return true;
    }

    /**
     * Calculate theoretical max points for a group.
     * Includes base points + maximum possible bonus for each question.
     * This is the same for everyone - the ceiling if you picked all underdogs and got them right.
     */
    public function calculateTheoreticalMax(Group $group): int
    {
        $possiblePoints = 0;
        $questions = $group->groupQuestions()->active()->get();

        foreach ($questions as $question) {
            $possiblePoints += $this->calculateMaxPointsForQuestion($question);
        }

        return $possiblePoints;
    }

    /**
     * Calculate theoretical max points for an event.
     * Includes base points + maximum possible bonus for each question.
     */
    public function calculateTheoreticalMaxForEvent(Event $event): int
    {
        $possiblePoints = 0;
        $questions = $event->eventQuestions()->get();

        foreach ($questions as $question) {
            $basePoints = $question->points;
            $maxBonus = 0;

            if ($question->question_type === 'multiple_choice' && $question->options) {
                $options = is_string($question->options) ? json_decode($question->options, true) : $question->options;
                if (is_array($options)) {
                    foreach ($options as $option) {
                        if (is_array($option) && isset($option['points'])) {
                            $maxBonus = max($maxBonus, $option['points'] ?? 0);
                        }
                    }
                }
            }

            $possiblePoints += $basePoints + $maxBonus;
        }

        return $possiblePoints;
    }
}
