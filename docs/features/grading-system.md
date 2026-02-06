# Grading System

## Overview

PropOff features a revolutionary **Dual Grading Model** where groups choose between real-time captain grading or post-event admin grading. This provides flexibility for different event types and group preferences.

**Key Innovation**: Each group independently chooses their grading source (captain or admin), allowing different grading workflows for the same event.

## Key Concepts

- **Grading Source**: Enum ('captain' or 'admin') stored on groups table
- **Captain Grading**: Captains set correct answers, immediate scoring
- **Admin Grading**: Admins set event-level answers, post-event scoring
- **Group Question Answers**: Captain-set answers (per group per question)
- **Event Answers**: Admin-set answers (per event per question)
- **Voiding**: Marking questions as void (no points awarded)

## Dual Grading Model

```
┌──────────────────────────────────────────────┐
│              Event Created                    │
│         (Event Questions Defined)             │
└──────────────┬───────────────────────────────┘
               │
       ┌───────┴────────┐
       │                │
┌──────▼──────┐  ┌──────▼──────┐
│  Group A    │  │  Group B    │
│ Captain     │  │ Admin       │
│ Grading     │  │ Grading     │
└──────┬──────┘  └──────┬──────┘
       │                │
┌──────▼──────┐  ┌──────▼──────┐
│ GroupQuestion│  │ EventAnswer │
│ Answers      │  │ Table       │
└──────────────┘  └──────────────┘
```

### Captain Grading Mode

**When**: Real-time grading during or after event

**Who**: Group captains set correct answers

**Database**: `group_question_answers` table
```sql
- group_id
- group_question_id
- correct_answer
- is_void (question voided for this group)
```

**Use Cases**:
- Subjective questions (different correct answers per group)
- Live scoring during event
- Captain-controlled grading
- Groups want independence

### Admin Grading Mode

**When**: Post-event grading by admin

**Who**: Admin sets event-level answers

**Database**: `event_answers` table
```sql
- event_id
- event_question_id
- correct_answer
- is_void (question voided for all groups using admin grading)
```

**Use Cases**:
- Objective questions (single correct answer)
- Post-event scoring
- Centralized grading control
- Groups want admin authority

## How Groups Choose Grading Source

### At Group Creation

```php
// Group creation form
Group::create([
    'name' => 'My Group',
    'event_id' => $event->id,
    'grading_source' => 'captain',  // or 'admin'
]);
```

### Changing Grading Source

Captains can change grading source **only before answers are set**:

```php
// Check if source can be changed
if ($group->canChangeGradingSource()) {
    $group->update(['grading_source' => 'admin']);
}
```

**Lock Mechanism**: Once ANY answer is set (captain or admin), the grading source is locked. This prevents:
- Score inconsistencies from mid-grading source changes
- Confusion about which answers apply
- Accidental data loss

**Method**: `Group::canChangeGradingSource()` returns `false` if any answers exist for the group's questions.

**Impact**: Changes which answer table is used for grading.

## Grading Logic

### Score Calculation Flow

```php
// SubmissionService::gradeSubmission()
public function gradeSubmission(Submission $submission)
{
    $group = $submission->group;

    // Determine which answer table to use
    if ($group->grading_source === 'captain') {
        // Use GroupQuestionAnswer table
        $answers = GroupQuestionAnswer::where('group_question_id', $questionId)
            ->where('group_id', $group->id)
            ->get();
    } else {
        // Use EventAnswer table
        $answers = EventAnswer::where('event_question_id', $questionId)
            ->where('event_id', $group->event_id)
            ->get();
    }

    // Grade each user answer
    foreach ($submission->userAnswers as $userAnswer) {
        $correctAnswer = $answers->get($userAnswer->question_id);

        // Check if question is voided
        if ($correctAnswer->is_void) {
            $userAnswer->points_earned = 0;
            $userAnswer->is_correct = false;
            continue;
        }

        // Compare answers (type-aware)
        $isCorrect = $this->compareAnswers(
            $userAnswer->answer_text,
            $correctAnswer->correct_answer,
            $userAnswer->question->question_type
        );

        // Calculate points
        $userAnswer->is_correct = $isCorrect;
        $userAnswer->points_earned = $isCorrect
            ? $this->calculatePoints($userAnswer->question, $userAnswer->answer_text)
            : 0;

        $userAnswer->save();
    }

    // Update submission totals
    $submission->total_score = $submission->userAnswers->sum('points_earned');
    $submission->possible_points = $this->calculatePossiblePoints($submission);
    $submission->percentage = ($submission->total_score / $submission->possible_points) * 100;
    $submission->save();

    // Update leaderboard
    LeaderboardService::updateLeaderboard($submission);
}
```

### Type-Aware Answer Comparison

```php
protected function compareAnswers($userAnswer, $correctAnswer, $questionType)
{
    switch ($questionType) {
        case 'multiple_choice':
        case 'yes_no':
            // Case-insensitive exact match
            return strcasecmp(trim($userAnswer), trim($correctAnswer)) === 0;

        case 'numeric':
            // Floating-point tolerance
            return abs((float)$userAnswer - (float)$correctAnswer) < 0.01;

        case 'text':
            // Case-insensitive, trimmed
            return strcasecmp(trim($userAnswer), trim($correctAnswer)) === 0;

        default:
            return false;
    }
}
```

## Captain Grading Interface

### Setting Answers

**Captain Workflow**:
1. Navigate to group → "Grade Submissions"
2. Sees list of all group questions
3. For each question:
   - View all user answers
   - Set correct answer
   - Mark as void (optional)
4. Click "Calculate Scores"
5. Scores update immediately

**Frontend** (integrated into Groups/Show.vue):
```vue
<template>
  <div v-for="question in questions" :key="question.id">
    <h3>{{ question.question_text }}</h3>

    <!-- User answers -->
    <div v-for="answer in question.user_answers">
      {{ answer.user.name }}: {{ answer.answer_text }}
    </div>

    <!-- Set correct answer -->
    <input v-model="correctAnswers[question.id]" />

    <!-- Void option -->
    <label>
      <input type="checkbox" v-model="voidedQuestions[question.id]" />
      Void this question
    </label>
  </div>

  <button @click="saveAnswersAndGrade">Calculate Scores</button>
</template>
```

### Bulk Set Answers

```php
// Captain controller
public function bulkSetAnswers(Request $request, Group $group)
{
    foreach ($request->answers as $questionId => $answer) {
        GroupQuestionAnswer::updateOrCreate(
            [
                'group_id' => $group->id,
                'group_question_id' => $questionId,
            ],
            [
                'correct_answer' => $answer['correct_answer'],
                'is_void' => $answer['is_void'] ?? false,
            ]
        );
    }

    // Recalculate all submissions for this group
    SubmissionService::recalculateGroupSubmissions($group);
}
```

## Admin Grading Interface

### Setting Event-Level Answers

**Admin Workflow**:
1. Navigate to event → "Grading"
2. Sees list of all event questions
3. For each question:
   - View all user answers across all groups
   - Set event-level correct answer
   - Mark as void (affects all groups using admin grading)
4. Click "Calculate Scores"
5. Scores update for all groups using admin grading

**Controller** (Admin/EventAnswerController.php):
```php
public function setAnswer(Request $request, Event $event, EventQuestion $question)
{
    EventAnswer::updateOrCreate(
        [
            'event_id' => $event->id,
            'event_question_id' => $question->id,
        ],
        [
            'correct_answer' => $request->correct_answer,
            'is_void' => $request->is_void ?? false,
        ]
    );

    // Recalculate submissions for groups using admin grading
    $groups = $event->groups()->where('grading_source', 'admin')->get();
    foreach ($groups as $group) {
        SubmissionService::recalculateGroupSubmissions($group);
    }
}
```

## Voiding Questions

**Purpose**: Mark questions that should not be scored (e.g., error, unfair, canceled)

### Voiding in Captain Mode

```php
// Void for specific group only
GroupQuestionAnswer::updateOrCreate(
    ['group_id' => $group->id, 'group_question_id' => $question->id],
    ['is_void' => true]
);
```

**Impact**: Question worth 0 points for this group only.

### Voiding in Admin Mode

```php
// Void for all groups using admin grading
EventAnswer::updateOrCreate(
    ['event_id' => $event->id, 'event_question_id' => $question->id],
    ['is_void' => true]
);
```

**Impact**: Question worth 0 points for all groups using admin grading.

## Code Locations

### Models
- `app/Models/GroupQuestionAnswer.php` - Captain-set answers
- `app/Models/EventAnswer.php` - Admin-set answers
- `app/Models/Submission.php` - User submissions
- `app/Models/UserAnswer.php` - Individual user answers

### Services
- `app/Services/SubmissionService.php` - Core grading logic
- `app/Services/LeaderboardService.php` - Leaderboard updates

### Controllers
- `app/Http/Controllers/Groups/GradingController.php` - Captain grading
- `app/Http/Controllers/Admin/EventAnswerController.php` - Admin grading
- `app/Http/Controllers/Admin/GradingController.php` - Admin grading interface

### Views
- `resources/js/Pages/Groups/Show.vue` - Captain grading (integrated into group page)
- `resources/js/Pages/Admin/Grading/Index.vue` - Admin grading interface

## Design Decisions

### Why Dual Grading Model?

**Decision**: Allow groups to choose captain or admin grading.

**Reasoning**:
- **Flexibility**: Different groups have different needs
- **Real-time vs Post-event**: Some want immediate scoring, others want admin control
- **Subjective vs Objective**: Some questions have group-specific answers
- **Trust**: Some groups trust captain grading, others want admin authority
- **Independence**: Groups control their own experience

**Trade-off**: More complex grading logic, but worth it for flexibility.

### Why Store grading_source on Groups?

**Decision**: Groups table has `grading_source` enum column.

**Reasoning**:
- Per-group choice (not per-event)
- Can change grading source mid-event
- Clear which table to query
- Simple logic: if captain, use GroupQuestionAnswers; if admin, use EventAnswers

### Why Two Separate Answer Tables?

**Decision**: GroupQuestionAnswers and EventAnswers instead of single table.

**Reasoning**:
- Clear separation of concerns
- Captain answers independent from admin answers
- Groups can have different answers for same question
- Easier to query and maintain
- No need for complex polymorphic relationships

**Alternative Considered**: Single `answers` table with `answer_source` column (rejected for complexity).

## Common Patterns

### Check Grading Source

```php
// In controller
if ($group->grading_source === 'captain') {
    // Use captain grading interface
} else {
    // Use admin grading or show waiting message
}
```

### Recalculate Scores After Grading

```php
// After setting answers
$submissions = $group->submissions()->where('is_complete', true)->get();
foreach ($submissions as $submission) {
    SubmissionService::gradeSubmission($submission);
}

// Update leaderboard
LeaderboardService::recalculateGroupLeaderboards($group);
```

### Calculate Possible Points

```php
public function calculatePossiblePoints(Submission $submission)
{
    $group = $submission->group;
    $activeQuestions = $group->groupQuestions()->where('is_active', true)->get();

    $possiblePoints = 0;
    foreach ($activeQuestions as $question) {
        // Check if question is voided
        if ($group->grading_source === 'captain') {
            $answer = GroupQuestionAnswer::where('group_id', $group->id)
                ->where('group_question_id', $question->id)
                ->first();
        } else {
            $answer = EventAnswer::where('event_id', $group->event_id)
                ->where('event_question_id', $question->event_question_id)
                ->first();
        }

        // Skip voided questions
        if ($answer && $answer->is_void) continue;

        // Add base points + max bonus points
        $possiblePoints += $question->points;
        if ($question->question_type === 'multiple_choice') {
            $maxBonus = max(array_column(json_decode($question->options, true), 'points'));
            $possiblePoints += $maxBonus;
        }
    }

    return $possiblePoints;
}
```

## Integration with Other Features

- **Captain System**: Captains set answers if captain grading mode
- **Question System**: Questions graded based on group-specific questions
- **Scoring**: Uses weighted scoring (base + bonus points)
- **Leaderboards**: Auto-updated after grading

**See Also:**
- [captain-system.md](captain-system.md) - Captain permissions for grading
- [question-system.md](question-system.md) - Question types and scoring
- [technical/scoring-calculations.md](../technical/scoring-calculations.md) - Detailed scoring logic

## Gotchas

1. **Changing Grading Source**: Changes which table is used, may cause score changes
2. **Voided Questions**: Reduce possible_points for percentage calculation
3. **Active vs Inactive**: Inactive questions not included in grading
4. **Type-Aware Comparison**: Numeric uses tolerance, text is case-insensitive
5. **Recalculation Needed**: After setting answers, must trigger recalculation

## Future Enhancements

- Hybrid grading mode (some questions captain, some admin)
- Partial credit for numeric answers
- Custom comparison functions per question
- Grading history/audit log
- Bulk void operations
- Auto-grading based on API data
