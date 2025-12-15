# America Says Game Type

## Overview

America Says is a live game show format (similar to Family Feud) where players guess popular answers to survey questions. Features real-time display, countdown timer, animated answer reveals, and themed visuals.

## Key Features

### Live Game Board
- Public display (no login required)
- Real-time updates (500ms polling)
- Ranked answers with first letter hints
- Typing animation on reveal
- Countdown timer with pause/resume
- Theme-based backgrounds and effects

### Admin Game Setup
- Control panel for game flow
- Question navigation (prev/next)
- Individual answer reveal/hide
- Timer controls (start/pause/reset)
- Real-time state synchronization

### Answer Management
- 7 ranked answers per question
- Drag-to-reorder by popularity
- Display order determines font size (#1 largest)
- Show first letters + underscores (hide word length)

## Themes

Auto-detected by event category:
- **Christmas**: Snowflakes, festive colors (category contains "christmas")
- **Halloween**: Spooky theme (category contains "halloween")
- **Sports**: Athletic theme (category contains "sports")
- **Default**: Standard blue gradient

## Database Schema

```sql
events:
  - event_type ('AmericaSays')
  - category (determines theme)

america_says_game_states:
  - event_id
  - current_question_id
  - timer_started_at (timestamp, nullable)
  - timer_paused_at (timestamp, nullable)
  - timer_duration (integer, default 30)
  - revealed_answer_ids (JSON array)
  - created_at
  - updated_at

event_answers:
  - event_id
  - event_question_id
  - correct_answer (answer text)
  - display_order (1-7, determines rank and font size)
```

## Workflows

### Admin Setup Workflow
1. Create event with type='AmericaSays'
2. Add questions (no template import)
3. Navigate to "Manage Ranked Answers"
4. Add 7 answers per question
5. Drag to reorder by popularity
6. Save answers

### Live Game Workflow
1. Admin opens game setup panel
2. Display board shown on projector/TV (public URL)
3. Admin starts timer (30 seconds default)
4. Admin reveals answers one by one as players guess
5. Admin navigates to next question when done
6. Repeat for all questions

## Display Features

### Answer Grid Layout
```
┌─────────────────────────────────┐
│        Question Text            │
│                                 │
│  [6]    [2]    [7]             │
│                                 │
│      [1]          [3]           │
│                                 │
│  [5]             [4]            │
│                                 │
│         ⏱️ Timer                 │
└─────────────────────────────────┘
```

Answer #1 (most popular) in center, largest font.

### Answer Reveal Animation
- Initially shows: `P_ _ _ _ _ _` (first letter + underscores, no length hint)
- On reveal: Types out full answer character by character
- Background color flash on reveal

### Timer Display
- Large centered countdown
- Pause/resume functionality
- Visual warning at 5 seconds remaining

## Code Locations

### Backend
- `app/Models/AmericaSaysGameState.php`
- `app/Http/Controllers/AmericaSaysController.php` (11 endpoints)
- `database/migrations/*_create_america_says_game_states_table.php`

### Frontend
- `resources/js/Pages/AmericaSays/GameBoard.vue` - Public display
- `resources/js/Pages/AmericaSays/GameSetup.vue` - Admin control panel
- `resources/js/Pages/AmericaSays/ManageAnswers.vue` - Answer management

## API Endpoints

```
GET  /america-says/{event}/board - Game board display (public)
GET  /america-says/{event}/setup - Admin setup panel
GET  /america-says/{event}/manage-answers - Answer management
GET  /america-says/{event}/state - Get current game state
POST /america-says/{event}/start-timer
POST /america-says/{event}/pause-timer
POST /america-says/{event}/reset-timer
POST /america-says/{event}/next-question
POST /america-says/{event}/previous-question
POST /america-says/{event}/toggle-answer/{answer}
POST /america-says/{event}/bulk-save-answers
```

## Design Decisions

### Why Separate Event Type?

**Decision**: AmericaSays is a separate event type, not a different app.

**Reasoning**:
- Reuses existing infrastructure (events, questions, users)
- Different UI/UX for live game show format
- Different grading model (ranked answers vs correct/incorrect)
- Seamless integration with PropOff system

### Why No Template Import?

**Decision**: America Says questions created manually, no template import.

**Reasoning**:
- Survey questions are event-specific
- No reusable patterns like traditional questions
- Ranked answers require custom input
- Simpler UI without template complexity

### Why 7 Answers?

**Decision**: Exactly 7 ranked answers per question.

**Reasoning**:
- Matches popular game show format
- Fits nicely in grid layout
- Enough variety without overwhelming display
- Easy to remember count for admins

## Integration with PropOff Features

**Used**:
- ✅ Event system (events table, status management)
- ✅ Question system (event_questions, event_answers)
- ✅ Admin permissions
- ✅ Color scheme and branding

**Not Used**:
- ❌ Groups (live game has no group submissions)
- ❌ Captains (admin controls everything)
- ❌ Submissions (no player answer recording)
- ❌ Grading (no scoring system)
- ❌ Leaderboards (no winners tracked)

## Quick Actions (Admin)

For AmericaSays events, Quick Actions show:
- 🎮 Open Game Board (public display)
- ⚙️ Game Setup (admin control)
- 📝 Manage Ranked Answers

For GameQuiz events, Quick Actions show:
- 📝 Manage Questions
- 👥 Captain Invitations
- ✅ Grading

## Gotchas

1. **Public Access**: Game board has no authentication (intentional for displays)
2. **Polling Updates**: Display polls every 500ms (high frequency)
3. **Theme Auto-Detection**: Based on category name, case-insensitive
4. **No Submissions**: Players don't submit answers (just visual display)
5. **Answer Count**: Must have exactly 7 answers per question

## Future Enhancements

- Configurable answer count (not fixed at 7)
- Sound effects on answer reveal
- Player buzzer integration (mobile devices)
- Score tracking per team
- Multiple themes with admin selection
- Custom timer durations per question
