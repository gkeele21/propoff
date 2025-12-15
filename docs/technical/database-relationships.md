# Database Relationships

## Entity Relationship Overview

```
users ─────────────┐
│                  │
│ ┌────────────────┴─────────────┐
│ │     user_groups (pivot)       │
│ │     - is_captain              │
│ └────────────────┬─────────────┘
│                  │
├──────────────────┴──────────── groups
│                                  │
│                                  ├── grading_source enum
│                                  │
├─────────────────────────────── events
│                                  │
│                                  ├── event_questions
│                                  │    │
│                                  │    ├─── question_templates
│                                  │    │
│                                  │    └─── event_answers ⭐ (admin grading)
│                                  │
│                                  └── group_questions ⭐ (per group)
│                                       │
│                                       └─── group_question_answers ⭐ (captain grading)
│
├─────────────────────────────── submissions
│                                  │
│                                  └─── user_answers
│
├─────────────────────────────── captain_invitations
│
└─────────────────────────────── leaderboards (materialized)
```

## Core Tables

### users (Extended Laravel)
```sql
- id
- name
- email (nullable for guests)
- password (nullable for guests)
- role enum('admin', 'user', 'guest')
- guest_token (32-char, for guests)
- email_verified_at
```

**Relationships**:
- `belongsToMany(Group)` via user_groups
- `hasMany(CaptainInvitation)` - invitations created
- `hasMany(Submission)`
- `captainGroups()` - groups where is_captain=true

### groups
```sql
- id
- name
- code (unique, 8-char)
- description
- event_id
- grading_source enum('captain', 'admin')
- created_by (user_id)
```

**Relationships**:
- `belongsToMany(User)` via user_groups
- `belongsTo(Event)`
- `hasMany(GroupQuestion)`
- `hasMany(GroupQuestionAnswer)`
- `hasMany(Submission)`
- `captains()` - users where is_captain=true

### events
```sql
- id
- name
- category
- event_date
- event_type enum('GameQuiz', 'AmericaSays')
- status enum('draft', 'open', 'locked', 'in_progress', 'completed')
- lock_date
- created_by
```

**Relationships**:
- `hasMany(EventQuestion)`
- `hasMany(Group)`
- `hasMany(CaptainInvitation)`
- `hasMany(EventAnswer)`
- `hasOne(AmericaSaysGameState)`

## Question System Tables

### question_templates
```sql
- id
- title
- question_text (with {variables})
- variables (JSON array)
- question_type enum
- default_points
- default_options (JSON)
- category
```

**Relationships**:
- `hasMany(EventQuestion)` - questions created from this template

### event_questions
```sql
- id
- event_id
- template_id (nullable)
- question_text
- question_type enum('multiple_choice', 'yes_no', 'numeric', 'text')
- options (JSON array with bonus points)
- points (base points)
- display_order
```

**Relationships**:
- `belongsTo(Event)`
- `belongsTo(QuestionTemplate)`
- `hasMany(GroupQuestion)` - inherited by groups
- `hasMany(EventAnswer)` - admin-set answers

### group_questions
```sql
- id
- group_id
- event_question_id (nullable for custom)
- question_text
- question_type
- options (JSON)
- points
- is_active (captain can deactivate)
- is_custom (true if captain-added)
- display_order
```

**Relationships**:
- `belongsTo(Group)`
- `belongsTo(EventQuestion)` - source question (null for custom)
- `hasMany(GroupQuestionAnswer)` - captain-set answers
- `hasMany(UserAnswer)`

## Grading Tables

### event_answers (Admin Grading)
```sql
- id
- event_id
- event_question_id
- correct_answer
- is_void
```

**Relationships**:
- `belongsTo(Event)`
- `belongsTo(EventQuestion)`

### group_question_answers (Captain Grading)
```sql
- id
- group_id
- group_question_id
- correct_answer
- is_void
```

**Relationships**:
- `belongsTo(Group)`
- `belongsTo(GroupQuestion)`

## Submission Tables

### submissions
```sql
- id
- event_id
- user_id
- group_id
- total_score
- possible_points
- percentage
- is_complete
- submitted_at
```

**Relationships**:
- `belongsTo(Event)`
- `belongsTo(User)`
- `belongsTo(Group)`
- `hasMany(UserAnswer)`

### user_answers
```sql
- id
- submission_id
- group_question_id
- answer_text
- points_earned
- is_correct
```

**Relationships**:
- `belongsTo(Submission)`
- `belongsTo(GroupQuestion)` - Note: group_question, not event_question

## Other Tables

### captain_invitations
```sql
- id
- event_id
- token (unique 32-char)
- max_uses (nullable)
- times_used
- expires_at (nullable)
- is_active
- created_by
```

### leaderboards (Materialized View)
```sql
- id
- event_id
- group_id (per-group leaderboards only)
- user_id
- rank
- total_score
- possible_points
- percentage
- answered_count
```

### america_says_game_states
```sql
- id
- event_id (unique)
- current_question_id
- current_timer_seconds
- is_timer_running
- revealed_answer_ids (JSON array)
```

## Key Design Decisions

### Why group_questions instead of event_questions?

**Decision**: Copy event questions to group questions when group is created.

**Reasoning**:
- Captains can customize questions per group
- Different groups have different questions
- Supports per-group activation/deactivation
- Enables captain independence

**Trade-off**: More storage, but maximum flexibility.

### Why two answer tables?

**Decision**: event_answers (admin) and group_question_answers (captain).

**Reasoning**:
- Clear separation of grading sources
- Groups choose which table to use
- Supports different answers per group
- No complex polymorphic relationships

### Why materialized leaderboards?

**Decision**: Store calculated leaderboards instead of computing on-demand.

**Reasoning**:
- Faster page loads
- Complex tie-breaking logic
- Historical data preservation
- Performance optimization

## Common Query Patterns

### Get Captain Groups for User
```php
$user->captainGroups()  // Groups where is_captain=true
```

### Get Active Group Questions
```php
$group->groupQuestions()->where('is_active', true)->orderBy('display_order')->get()
```

### Get Correct Answer for Question
```php
if ($group->grading_source === 'captain') {
    $answer = GroupQuestionAnswer::where('group_id', $group->id)
        ->where('group_question_id', $question->id)
        ->first();
} else {
    $answer = EventAnswer::where('event_id', $event->id)
        ->where('event_question_id', $question->event_question_id)
        ->first();
}
```

### Get User Submission for Group
```php
Submission::where('event_id', $event->id)
    ->where('user_id', $user->id)
    ->where('group_id', $group->id)
    ->with('userAnswers.groupQuestion')
    ->first()
```

## Indexes

**Performance Critical Indexes**:
- `users.email` - Login lookups
- `users.guest_token` - Guest access
- `groups.code` - Join code lookups
- `user_groups(user_id, group_id)` - Composite for relationships
- `captain_invitations.token` - Invitation validation
- `submissions(event_id, user_id, group_id)` - Composite unique
- `leaderboards(event_id, group_id)` - Leaderboard queries

## Foreign Key Constraints

All relationships use `ON DELETE CASCADE` where appropriate:
- Deleting user → deletes submissions, user_groups
- Deleting event → deletes event_questions, groups, invitations
- Deleting group → deletes group_questions, submissions, user_groups
- Deleting submission → deletes user_answers

**Exception**: template_id is `ON DELETE SET NULL` (preserve questions if template deleted)
