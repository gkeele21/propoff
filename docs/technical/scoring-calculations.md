# Scoring Calculations

## Overview

PropOff uses a weighted scoring system with base points + optional bonus points per answer option. Grading is type-aware with different comparison logic for each question type.

## Weighted Scoring Formula

```
Total Points (if correct) = Base Points + Option Bonus Points
Total Points (if wrong) = 0
```

### Example

Question: "Who will win?"
- Base points: 5
- Options:
  - "Eagles" (+2 bonus) = 7 total if correct
  - "Cowboys" (+0 bonus) = 5 total if correct
  - "Tie" (+1 bonus) = 6 total if correct

## Grading Flow

```
User submits answers
    ↓
Grading triggered (manual or auto)
    ↓
For each user answer:
    ↓
Check grading source (captain vs admin)
    ↓
Fetch correct answer from appropriate table
    ↓
Check if question is voided
    ↓
Compare user answer to correct answer (type-aware)
    ↓
Calculate points earned
    ↓
Update user_answer (points_earned, is_correct)
    ↓
Update submission totals (total_score, percentage)
    ↓
Update leaderboard
```

## Type-Aware Answer Comparison

### Multiple Choice
```php
public function compareMultipleChoice($userAnswer, $correctAnswer)
{
    return strcasecmp(trim($userAnswer), trim($correctAnswer)) === 0;
}
```
- Case-insensitive
- Trimmed whitespace
- Exact match

### Yes/No
```php
public function compareYesNo($userAnswer, $correctAnswer)
{
    return strcasecmp(trim($userAnswer), trim($correctAnswer)) === 0;
}
```
- Same as multiple choice
- Binary comparison

### Numeric
```php
public function compareNumeric($userAnswer, $correctAnswer)
{
    return abs((float)$userAnswer - (float)$correctAnswer) < 0.01;
}
```
- Floating-point tolerance (±0.01)
- Handles decimals
- Prevents rounding errors

### Text
```php
public function compareText($userAnswer, $correctAnswer)
{
    return strcasecmp(trim($userAnswer), trim($correctAnswer)) === 0;
}
```
- Case-insensitive
- Trimmed whitespace
- Exact match after normalization

## Points Calculation

### Base Points
```php
$basePoints = $question->points;  // e.g., 5
```

### Bonus Points (Multiple Choice Only)
```php
$bonusPoints = 0;

if ($question->question_type === 'multiple_choice' && $question->options) {
    $options = json_decode($question->options, true);

    foreach ($options as $option) {
        if (strcasecmp($option['label'], $userAnswer) === 0) {
            $bonusPoints = $option['points'] ?? 0;
            break;
        }
    }
}
```

### Total Points
```php
if ($isCorrect) {
    $totalPoints = $basePoints + $bonusPoints;
} else {
    $totalPoints = 0;
}
```

## Voiding Questions

When a question is voided:
```php
if ($answer->is_void) {
    $userAnswer->points_earned = 0;
    $userAnswer->is_correct = false;
    // Excluded from possible_points calculation
}
```

**Impact**:
- User gets 0 points for voided question
- Question excluded from possible_points
- Percentage unaffected (denominator reduced)

## Possible Points Calculation

```php
public function calculatePossiblePoints(Submission $submission)
{
    $group = $submission->group;
    $activeQuestions = $group->groupQuestions()
        ->where('is_active', true)
        ->get();

    $possiblePoints = 0;

    foreach ($activeQuestions as $question) {
        // Check if question is voided
        if ($this->isQuestionVoided($question, $group)) {
            continue;  // Skip voided questions
        }

        // Add base points
        $possiblePoints += $question->points;

        // Add max bonus points (for multiple choice)
        if ($question->question_type === 'multiple_choice' && $question->options) {
            $options = json_decode($question->options, true);
            $maxBonus = max(array_column($options, 'points'));
            $possiblePoints += $maxBonus;
        }
    }

    return $possiblePoints;
}
```

## Percentage Calculation

```php
$percentage = ($submission->total_score / $submission->possible_points) * 100;

// Handle edge case: no questions
if ($submission->possible_points === 0) {
    $percentage = 0;
}

$submission->percentage = round($percentage, 2);  // 2 decimal places
```

## Leaderboard Ranking

### Tie-Breaking Logic

```php
public function updateRanks($eventId, $groupId)
{
    $leaderboards = Leaderboard::where('event_id', $eventId)
        ->where('group_id', $groupId)
        ->orderByDesc('percentage')        // 1. Higher percentage wins
        ->orderByDesc('total_score')       // 2. Higher score wins
        ->orderByDesc('answered_count')    // 3. More questions answered wins
        ->get();

    $rank = 1;
    foreach ($leaderboards as $leaderboard) {
        $leaderboard->rank = $rank++;
        $leaderboard->save();
    }
}
```

**Tie-Breaking Order**:
1. Percentage (higher wins)
2. Total Score (higher wins)
3. Answered Count (more wins)

## Grading Service Implementation

### Main Grading Method

```php
public function gradeSubmission(Submission $submission)
{
    $group = $submission->group;

    // Determine answer source
    if ($group->grading_source === 'captain') {
        $answers = GroupQuestionAnswer::where('group_id', $group->id)
            ->get()
            ->keyBy('group_question_id');
    } else {
        // Map group_questions to event_questions for admin grading
        $eventQuestionIds = $group->groupQuestions()
            ->whereNotNull('event_question_id')
            ->pluck('event_question_id', 'id');

        $answers = EventAnswer::where('event_id', $group->event_id)
            ->whereIn('event_question_id', $eventQuestionIds->values())
            ->get()
            ->keyBy('event_question_id');
    }

    // Grade each user answer
    foreach ($submission->userAnswers as $userAnswer) {
        $question = $userAnswer->groupQuestion;

        // Get correct answer
        if ($group->grading_source === 'captain') {
            $correctAnswer = $answers->get($question->id);
        } else {
            $correctAnswer = $answers->get($question->event_question_id);
        }

        // Check if voided
        if (!$correctAnswer || $correctAnswer->is_void) {
            $userAnswer->points_earned = 0;
            $userAnswer->is_correct = false;
            $userAnswer->save();
            continue;
        }

        // Compare answers
        $isCorrect = $this->compareAnswers(
            $userAnswer->answer_text,
            $correctAnswer->correct_answer,
            $question->question_type
        );

        // Calculate points
        $pointsEarned = $isCorrect
            ? $this->calculatePoints($question, $userAnswer->answer_text)
            : 0;

        $userAnswer->points_earned = $pointsEarned;
        $userAnswer->is_correct = $isCorrect;
        $userAnswer->save();
    }

    // Update submission totals
    $submission->total_score = $submission->userAnswers->sum('points_earned');
    $submission->possible_points = $this->calculatePossiblePoints($submission);
    $submission->percentage = $this->calculatePercentage(
        $submission->total_score,
        $submission->possible_points
    );
    $submission->save();

    // Update leaderboard
    LeaderboardService::updateLeaderboard($submission);
}
```

## Code Locations

- `app/Services/SubmissionService.php` - Core grading logic
- `app/Services/LeaderboardService.php` - Ranking and tie-breaking
- `app/Http/Controllers/Groups/GradingController.php` - Captain grading
- `app/Http/Controllers/Admin/GradingController.php` - Admin grading

## Common Patterns

### Trigger Grading

```php
// Grade single submission
SubmissionService::gradeSubmission($submission);

// Grade all submissions for group
$submissions = $group->submissions()->where('is_complete', true)->get();
foreach ($submissions as $submission) {
    SubmissionService::gradeSubmission($submission);
}

// Update leaderboard after grading
LeaderboardService::recalculateGroupLeaderboards($group);
```

### Check if Question Voided

```php
private function isQuestionVoided(GroupQuestion $question, Group $group)
{
    if ($group->grading_source === 'captain') {
        $answer = GroupQuestionAnswer::where('group_id', $group->id)
            ->where('group_question_id', $question->id)
            ->first();
    } else {
        $answer = EventAnswer::where('event_id', $group->event_id)
            ->where('event_question_id', $question->event_question_id)
            ->first();
    }

    return $answer && $answer->is_void;
}
```

## Design Decisions

### Why Floating-Point Tolerance for Numeric?

**Decision**: Use ±0.01 tolerance instead of exact match.

**Reasoning**:
- Prevents rounding errors
- Handles decimal input
- More forgiving for users
- Common practice for numeric comparison

### Why Base + Bonus Instead of Single Points?

**Decision**: Separate base points and per-option bonus points.

**Reasoning**:
- Transparency - players see risk/reward
- Flexibility - admins weight harder options
- Simplicity - no complex formulas
- Game-like - encourages strategic answering

### Why Exclude Voided from Possible Points?

**Decision**: Voided questions reduce denominator in percentage calculation.

**Reasoning**:
- Fair to players - not penalized for voided questions
- Admin flexibility - can void unfair questions
- Percentage remains meaningful
- Standard practice

## Gotchas

1. **Type Matters**: Numeric uses tolerance, text doesn't
2. **Case Insensitive**: All text comparisons ignore case
3. **Whitespace Trimmed**: Leading/trailing spaces ignored
4. **Voided Questions**: Excluded from both numerator and denominator
5. **Inactive Questions**: Still exist but hidden from players
6. **Grading Source**: Determines which answer table to use

## Future Enhancements

- Partial credit for numeric answers (within range)
- Custom comparison functions per question
- Regex matching for text answers
- Multiple correct answers support
- Time-based scoring (faster = more points)
