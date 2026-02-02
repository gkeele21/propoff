# Backend Requirements for Admin Events Show Redesign

## Overview

This document outlines the backend routes, controller methods, and data structures needed to support the redesigned Admin Events Show page.

## Required Routes

### 1. Show Event with Questions (Update Existing)

**Route**: `GET /admin/events/{event}`
**Name**: `admin.events.show`
**Controller**: `EventController@show`

**Changes Needed**:
- Add `questions` to the props passed to the view
- Include question options, correct answers, and user answer counts
- Ensure questions are ordered by `display_order` or `order_number`

**Props to Pass**:
```php
return Inertia::render('Admin/Events/Show', [
    'event' => $event,
    'questions' => $event->eventQuestions()
        ->with(['eventAnswers'])
        ->orderBy('display_order')
        ->get()
        ->map(fn($q) => [
            'id' => $q->id,
            'question_text' => $q->question_text,
            'question_type' => $q->question_type,
            'options' => $q->options, // Array of options
            'points' => $q->points,
            'display_order' => $q->display_order,
            'correct_answer' => $q->correct_answer,
            'user_answers_count' => $q->userAnswers()->count(),
        ]),
    'stats' => [
        'total_questions' => $event->eventQuestions()->count(),
        'total_entries' => $event->entries()->count(),
        'completed_entries' => $event->entries()->where('is_completed', true)->count(),
        'average_score' => $event->entries()->avg('score'),
    ],
]);
```

### 2. Set Answer for Question (NEW ROUTE)

**Route**: `POST /admin/events/{event}/questions/{question}/set-answer`
**Name**: `admin.events.questions.set-answer`
**Controller**: `EventQuestionController@setAnswer` (NEW METHOD)

**Request Body**:
```json
{
    "answer": "Option A" // or whatever the answer value is
}
```

**Expected Behavior**:
1. Validate the answer value against available options
2. Update the `event_question.correct_answer` field (or `event_answers` table for ranked answers)
3. Trigger score recalculation for all entries in this event:
   - Recalculate each entry's score based on correct answers
   - Update entry records with new scores
4. Return success response with updated question

**Response**:
```json
{
    "message": "Answer saved and scores calculated",
    "question": {
        "id": 123,
        "correct_answer": "Option A"
    }
}
```

**Example Implementation**:
```php
public function setAnswer(Request $request, Event $event, EventQuestion $question)
{
    $request->validate([
        'answer' => 'required|string',
    ]);

    // Update the correct answer
    $question->update([
        'correct_answer' => $request->answer,
    ]);

    // Recalculate scores for all entries
    $this->recalculateEventScores($event);

    return back()->with('success', 'Answer saved and scores calculated');
}

private function recalculateEventScores(Event $event)
{
    $entries = $event->entries;

    foreach ($entries as $entry) {
        $score = $this->calculateEntryScore($entry);
        $entry->update(['score' => $score]);
    }
}

private function calculateEntryScore(Entry $entry)
{
    // Your existing scoring logic
    // Compare user answers to correct answers
    // Sum up points earned
    return $totalScore;
}
```

### 3. Store New Question (Update Existing)

**Route**: `POST /admin/events/{event}/event-questions`
**Name**: `admin.events.event-questions.store`
**Controller**: `EventQuestionController@store`

**Changes Needed**:
- Accept question data from modal (question_text, options, points)
- Default `question_type` to 'multiple_choice' for now
- Set `display_order` to next available number
- Return to Events Show page instead of Questions Index

**Request Body**:
```json
{
    "question_text": "Who will win the game?",
    "options": [
        {"label": "Team A", "points": 5},
        {"label": "Team B", "points": 0}
    ],
    "points": 10
}
```

### 4. Update Question (Update Existing)

**Route**: `PATCH /admin/events/{event}/event-questions/{question}`
**Name**: `admin.events.event-questions.update`
**Controller**: `EventQuestionController@update`

**Changes Needed**:
- Accept same structure as store
- Return to Events Show page instead of Questions Index

### 5. Duplicate Question (Ensure Exists)

**Route**: `POST /admin/events/{event}/event-questions/{question}/duplicate`
**Name**: `admin.events.event-questions.duplicate`
**Controller**: `EventQuestionController@duplicate`

**Expected Behavior**:
- Create copy of question with incremented `display_order`
- Copy all question data (text, options, points)
- Create corresponding group questions if needed
- Return to Events Show page

### 6. Delete Question (Ensure Exists)

**Route**: `DELETE /admin/events/{event}/event-questions/{question}`
**Name**: `admin.events.event-questions.destroy`
**Controller**: `EventQuestionController@destroy`

**Expected Behavior**:
- Delete the question
- Reorder remaining questions (update display_order values)
- Return to Events Show page

### 7. Reorder Questions (Ensure Exists)

**Route**: `POST /admin/events/{event}/event-questions/reorder`
**Name**: `admin.events.event-questions.reorder`
**Controller**: `EventQuestionController@reorder`

**Request Body**:
```json
{
    "question_id": 123,
    "new_order": 2
}
```

**Expected Behavior**:
- Move question to new position
- Adjust display_order of other questions accordingly
- Update both `event_questions` and `group_questions` tables
- Return to Events Show page

## Data Structure Requirements

### EventQuestion Model

Ensure the following fields exist:

```php
// event_questions table
- id
- event_id
- question_text
- question_type (enum: 'multiple_choice', 'yes_no', 'numeric', 'text', 'ranked_answers')
- options (JSON) // Array of objects: [{label: string, points: number}]
- points (integer) // Base points for question
- display_order (integer)
- correct_answer (string, nullable)
- created_at
- updated_at
```

### Options Format

The `options` field should be stored as JSON in this format:

```json
[
    {"label": "Option A", "points": 0},
    {"label": "Option B", "points": 5},
    {"label": "Option C", "points": 0}
]
```

Legacy format (array of strings) should be converted to new format when loading.

## Navigation Changes

### Update Links

1. **"Manage Invitations" button** should navigate to:
   - Route: `admin.events.captain-invitations.index`
   - Path: `/admin/events/{event}/captain-invitations`

2. **"Import from Template" button** should navigate to:
   - Route: `admin.events.event-questions.create`
   - Path: `/admin/events/{event}/event-questions/create`
   - This is the existing Add Question page that handles template import

## Testing Checklist

- [ ] Event Show page loads with questions
- [ ] Add Custom Question modal opens and saves
- [ ] Edit Question modal opens with pre-populated data
- [ ] Edit Question shows warning if user answers exist
- [ ] Answer selection and Save Answer works
- [ ] Scores recalculate when answer is set
- [ ] Drag and drop reordering works
- [ ] Duplicate question creates copy
- [ ] Delete question removes and reorders
- [ ] Import from Template navigates correctly
- [ ] Manage Invitations navigates correctly

## Migration Considerations

### Deprecating EventQuestions Index

The existing `/admin/events/{event}/event-questions` (Index page) may no longer be needed since all functionality is now on the Events Show page.

**Options**:
1. Redirect `admin.events.event-questions.index` to `admin.events.show`
2. Remove the route entirely
3. Keep it for backward compatibility temporarily

**Recommendation**: Redirect for now, remove after testing period.

```php
// In routes/web.php or EventQuestionController
Route::get('/admin/events/{event}/event-questions', function (Event $event) {
    return redirect()->route('admin.events.show', $event);
})->name('admin.events.event-questions.index');
```

## Performance Considerations

1. **Eager Loading**: Load question relationships (`eventAnswers`, `userAnswers`) when fetching questions
2. **Caching**: Consider caching event statistics (total_entries, average_score) for large events
3. **Score Calculation**: Run score recalculation in a queued job for events with many entries (>100)

## Security Considerations

1. **Authorization**: Ensure user has permission to manage the event
2. **Validation**: Validate answer values against available options
3. **Sanitization**: Sanitize question text and option labels to prevent XSS

## Future Enhancements

1. Support for other question types (yes_no, numeric, text, ranked_answers)
2. Bulk answer setting
3. Answer history tracking
4. Question statistics and analytics
5. Keyboard shortcuts for quick actions
