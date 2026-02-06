# Events

## Overview

Events are prediction competitions where users answer questions. PropOff supports two event types: Game Quiz (traditional prediction questions) and America Says (live game show format).

## Event Types

### GameQuiz (Default)
- Traditional prediction/guessing event
- Users submit answers before event starts
- Post-event grading
- Group-based leaderboards

### AmericaSays
- Live game show format (like Family Feud)
- Real-time display with timer
- Ranked answers with animations
- Admin-controlled game flow

See [america-says.md](america-says.md) for America Says details.

## Event Status

| Status | Description | User Actions |
|--------|-------------|--------------|
| **Draft** | Event not ready | None |
| **Open** | Users can submit | Submit/edit answers |
| **Locked** | No new submissions | View only |
| **In Progress** | Event happening | View only |
| **Completed** | Event finished | View results |

## Event Management (Admin)

### Creating Events

```php
Event::create([
    'name' => 'Super Bowl LVIII',
    'category' => 'sports',
    'event_date' => '2025-02-09 18:30:00',
    'event_type' => 'GameQuiz',  // or 'AmericaSays'
    'status' => 'draft',
    'lock_date' => '2025-02-09 18:00:00',
]);
```

### Key Features
- Add questions from templates
- Generate captain invitations
- Manage event questions
- Set event-level answers (admin grading)
- Change event status
- Duplicate events
- View event statistics

## Groups and Grading Source

Each event can have **multiple groups**, and each group chooses its **grading source**:

### Grading Source Options

| Source | Who Sets Answers | Use Case |
|--------|-----------------|----------|
| **Admin** | Admin sets answers at event level | Centralized control, all groups share same answers |
| **Captain** | Captain sets answers for their group | Independent groups with different answer timing |

### Question Inheritance

When a group is created for an event:
1. Event questions are **copied** to the group as group questions
2. Captain can customize: toggle active, modify text, reorder, add custom
3. Changes to event questions do NOT auto-sync to existing groups

### Grading Source Lock

Once a group has answers set:
- `canChangeGradingSource()` returns `false`
- Prevents switching sources mid-grading
- Protects score integrity

### Workflow Examples

**Admin Grading (Simple)**:
1. Admin creates event with questions
2. Multiple groups created (all use "admin" grading source)
3. Event happens
4. Admin sets correct answers ONCE
5. All groups automatically graded

**Captain Grading (Flexible)**:
1. Admin creates event with questions
2. Groups created with "captain" grading source
3. Each captain customizes their questions
4. Each captain grades their own group on their timeline
5. Groups have independent answer reveal

## Code Locations

- `app/Models/Event.php`
- `app/Http/Controllers/Admin/EventController.php`
- `app/Http/Controllers/Admin/GradingController.php`
- `resources/js/Pages/Admin/Events/`

## Integration

- **Questions**: Events contain event questions (see [question-system.md](question-system.md))
- **Groups**: Multiple groups per event with independent questions (see [groups.md](groups.md))
- **Grading**: Admin or captain sets answers (see [grading-system.md](grading-system.md))
- **Captains**: Admins generate captain invitations per event (see [captain-system.md](captain-system.md))
