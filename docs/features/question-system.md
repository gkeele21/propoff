# Question System

## Overview

PropOff uses a revolutionary **3-tier question architecture** that provides maximum flexibility: admins create reusable templates, events get concrete questions from templates, and groups inherit event questions with full captain customization.

**Key Innovation**: Questions flow from Templates → Event Questions → Group Questions, with captains able to customize at the group level.

## Key Concepts

- **Question Template**: Reusable question pattern with variables (created by admins)
- **Event Question**: Concrete question for a specific event (admin creates from templates)
- **Group Question**: Group-specific question inherited from event (captain customizable)
- **Variables**: Placeholders like `{team1}`, `{player1}` that get substituted when creating questions
- **Weighted Scoring**: Base points + optional bonus points per answer option

## 3-Tier Architecture

```
Question Templates (Reusable)
    ↓ Admin selects template + fills variables
Event Questions (Per Event)
    ↓ Auto-copied when group created
Group Questions (Per Group)
    ↓ Captain can customize
Player Sees Group Questions
```

### Tier 1: Question Templates

**Purpose**: Reusable patterns for common questions

**Database**: `question_templates`
```sql
- title: "NFL Matchup Prediction"
- question_text: "Who will win {team1} vs {team2}?"
- variables: ["team1", "team2"]
- category: "sports"
- default_points: 1
- default_options: ["{team1}", "{team2}", "Tie"]
```

**Features**:
- Variables for dynamic substitution
- Category-based filtering
- Favorite marking
- Bulk template import

**Created by**: Admins only

### Tier 2: Event Questions

**Purpose**: Concrete questions for a specific event

**Database**: `event_questions`
```sql
- event_id
- template_id (nullable)
- question_text: "Who will win Eagles vs Cowboys?"
- question_type: multiple_choice, yes_no, numeric, text
- options: [{"label": "Eagles", "points": 2}, {"label": "Cowboys", "points": 0}]
- points: 5  (base points)
- display_order
```

**Features**:
- Created from templates or custom
- Base points + per-option bonus points
- Drag-and-drop reordering
- Bulk import from other events

**Created by**: Admins only

### Tier 3: Group Questions

**Purpose**: Group-specific questions (captains customize)

**Database**: `group_questions`
```sql
- group_id
- event_question_id (nullable - null for custom questions)
- question_text
- question_type
- options
- points
- is_active (can be deactivated by captain)
- is_custom (true if captain-added, false if inherited)
- display_order
```

**Features**:
- Auto-created when group is created
- Captains can activate/deactivate
- Captains can modify text/points
- Captains can add custom questions
- Captains can reorder

**Managed by**: Captains

## Question Types

### 1. Multiple Choice

**Use Case**: Select from predefined options

```json
{
  "question_text": "Who will win the Super Bowl?",
  "question_type": "multiple_choice",
  "options": [
    {"label": "Eagles", "points": 2},
    {"label": "Chiefs", "points": 0},
    {"label": "49ers", "points": 1}
  ],
  "points": 5
}
```

**Scoring**: Base (5) + Option Bonus (0-2) = 5-7 points if correct

### 2. Yes/No

**Use Case**: Binary choice

```json
{
  "question_text": "Will there be overtime?",
  "question_type": "yes_no",
  "options": [
    {"label": "Yes", "points": 1},
    {"label": "No", "points": 0}
  ],
  "points": 3
}
```

### 3. Numeric

**Use Case**: Number input

```json
{
  "question_text": "How many total points will be scored?",
  "question_type": "numeric",
  "points": 5
}
```

**Comparison**: Floating-point tolerance (±0.01)

### 4. Short Text

**Use Case**: Free text response

```json
{
  "question_text": "Who will be MVP?",
  "question_type": "text",
  "points": 10
}
```

**Comparison**: Case-insensitive, trimmed

## Weighted Scoring System

**Concept**: Questions have base points + optional per-option bonus points.

### How It Works

1. **Admin sets base points**: Points awarded for answering correctly
2. **Admin sets option bonuses**: Additional points per answer choice
3. **Total score calculation**: Base + Bonus (if correct), 0 (if wrong)

### Example

```json
{
  "points": 5,  // Base points
  "options": [
    {"label": "Exact score: Eagles 31-24", "points": 5},  // +5 bonus
    {"label": "Within 7 points", "points": 2},            // +2 bonus
    {"label": "Other", "points": 0}                        // +0 bonus
  ]
}
```

**Scoring**:
- Exact score (correct): 5 + 5 = 10 points
- Within 7 (correct): 5 + 2 = 7 points
- Other (correct): 5 + 0 = 5 points
- Any (wrong): 0 points

**Benefits**:
- Players see point values before answering
- Admins can weight riskier/harder options higher
- Flexible scoring without complex rules
- Transparent risk/reward

## Variable Substitution

**Purpose**: Create reusable templates that adapt to different events.

### Creating a Template with Variables

```php
// Admin creates template
QuestionTemplate::create([
    'question_text' => 'Who will win {team1} vs {team2}?',
    'variables' => ['team1', 'team2'],
    'default_options' => ['{team1}', '{team2}', 'Tie'],
    'category' => 'sports'
]);
```

### Using Template to Create Event Question

**Admin workflow**:
1. Select template from list (filtered by event category)
2. Modal appears with variable input fields
3. Admin enters: `team1="Eagles"`, `team2="Cowboys"`
4. Live preview updates: "Who will win Eagles vs Cowboys?"
5. Admin confirms
6. Event question created with substituted text and options

**Frontend** (Admin/Questions/Create.vue):
```javascript
const previewText = computed(() => {
    let text = template.question_text;
    Object.entries(variableValues.value).forEach(([key, value]) => {
        text = text.replace(new RegExp(`\\{${key}\\}`, 'g'), value);
    });
    return text;
});
```

## Code Locations

### Models
- `app/Models/QuestionTemplate.php` - Template management
- `app/Models/EventQuestion.php` - Event-level questions
- `app/Models/GroupQuestion.php` - Group-level questions

### Controllers
- `app/Http/Controllers/Admin/QuestionTemplateController.php` - Template CRUD
- `app/Http/Controllers/Admin/EventQuestionController.php` - Event question management
- `app/Http/Controllers/Groups/QuestionController.php` - Captain question management

### Services
- No dedicated service (logic in controllers/models)

### Views
- `resources/js/Pages/Admin/QuestionTemplates/` - Template management (CRUD)
- `resources/js/Pages/Admin/Events/Show.vue` - Event question management (integrated)
- `resources/js/Pages/Groups/Show.vue` - Captain question customization (integrated)

## Workflows

### Admin Creates Template

1. Navigate to Admin Dashboard → Question Templates
2. Click "Create Template"
3. Fill form:
   - Title: "NFL Game Prediction"
   - Question text with variables: "Who will win {team1} vs {team2}?"
   - Variables: ["team1", "team2"]
   - Category: "sports"
   - Default options: ["{team1}", "{team2}", "Tie"]
4. Save template

### Admin Creates Event Questions from Templates

1. Navigate to event → Manage Questions
2. Click "Add from Templates"
3. Templates filtered by event category
4. Select template (e.g., "NFL Game Prediction")
5. Modal appears with variable inputs
6. Fill variables: team1="Eagles", team2="Cowboys"
7. Preview updates in real-time
8. Confirm creation
9. Event question created: "Who will win Eagles vs Cowboys?"

### Group Inherits Event Questions

**Automatic Process** (happens when group is created):

```php
// When group is created
foreach ($event->eventQuestions as $eventQuestion) {
    GroupQuestion::create([
        'group_id' => $group->id,
        'event_question_id' => $eventQuestion->id,
        'question_text' => $eventQuestion->question_text,
        'question_type' => $eventQuestion->question_type,
        'options' => $eventQuestion->options,
        'points' => $eventQuestion->points,
        'is_active' => true,
        'is_custom' => false,
        'display_order' => $eventQuestion->display_order,
    ]);
}
```

### Captain Customizes Group Questions

1. Captain navigates to group → "Manage Questions"
2. Sees list of inherited questions
3. Can:
   - **Deactivate**: Set `is_active = false` (question hidden from players)
   - **Reactivate**: Set `is_active = true`
   - **Modify**: Change question text or point values
   - **Reorder**: Drag-and-drop to change display_order
   - **Add Custom**: Create new question (not from event)
   - **Delete Custom**: Remove custom questions only
4. Changes save immediately
5. Players see updated question list

## Design Decisions

### Why 3-Tier Architecture?

**Decision**: Templates → Event Questions → Group Questions

**Reasoning**:
- **Reusability**: Templates used across multiple events
- **Consistency**: Events use standardized questions from templates
- **Flexibility**: Groups customize questions for their needs
- **Captain Empowerment**: Captains control their group's experience
- **Independence**: Groups can have completely different questions

**Trade-off**: More database storage (questions duplicated per group), but worth it for flexibility.

### Why Allow Captain Customization?

**Decision**: Captains can modify inherited questions.

**Reasoning**:
- Different groups have different interests
- Casual vs competitive groups need different point structures
- Some questions may not be relevant to a group
- Empowers captains to tailor experience

**Impact**: Groups cannot be compared across leaderboards (different questions).

### Why Weighted Scoring?

**Decision**: Base points + per-option bonus points.

**Reasoning**:
- Transparency: Players see point values before answering
- Flexibility: Easy to weight harder options higher
- Simplicity: No complex scoring rules needed
- Game-like: Risk/reward visible to players

## Common Patterns

### Create Question from Template

```php
// Controller
$eventQuestion = EventQuestion::create([
    'event_id' => $event->id,
    'template_id' => $template->id,
    'question_text' => $this->substituteVariables($template, $variableValues),
    'question_type' => $template->question_type,
    'options' => $this->substituteVariables($template->default_options, $variableValues),
    'points' => $template->default_points,
]);
```

### Captain Deactivates Question

```php
// Captain controller
$groupQuestion->update(['is_active' => false]);

// Players no longer see this question
$activeQuestions = $group->groupQuestions()->where('is_active', true)->get();
```

### Calculate Question Points

```php
// Submission service
$basePoints = $question->points;
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

$totalPoints = $basePoints + $bonusPoints;  // If correct, 0 if wrong
```

## Integration with Other Features

- **Captain System**: Captains customize group questions
- **Grading System**: Questions graded using group-specific correct answers
- **Events**: Events use question templates to create concrete questions
- **Scoring**: Weighted scoring based on base + bonus points

**See Also:**
- [captain-system.md](captain-system.md) - Captain permissions for questions
- [grading-system.md](grading-system.md) - How questions are graded
- [events.md](events.md) - Event question management

## Gotchas

1. **Group Questions Are Copied**: Changing event question doesn't update existing group questions
2. **Custom Questions Can't Be From Templates**: Captains create from scratch
3. **Variables Must Match**: Template variables and substitution keys must match exactly
4. **Active vs Inactive**: Inactive questions still exist but hidden from players
5. **Point Changes**: Changing points doesn't retroactively affect submitted answers

## Future Enhancements

- Question difficulty ratings
- Auto-suggestion for variable values
- Bulk question import from CSV
- Question analytics (most difficult, most popular)
- Community template sharing
