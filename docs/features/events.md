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

## Code Locations

- `app/Models/Event.php`
- `app/Http/Controllers/EventController.php`
- `app/Http/Controllers/Admin/EventController.php`
- `resources/js/Pages/Events/Show.vue`
- `resources/js/Pages/Admin/Events/`

## Integration

- **Questions**: Events contain event questions (see [question-system.md](question-system.md))
- **Groups**: Multiple groups per event with independent questions
- **Grading**: Admin can set event-level answers (see [grading-system.md](grading-system.md))
- **Captains**: Admins generate captain invitations per event
