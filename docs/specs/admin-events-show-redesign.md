# Admin Events Show Page Redesign

## Overview

This specification documents the redesign of the Admin Events Show page to consolidate event and question management into a single, streamlined interface.

## Goals

1. **Consolidation**: Move question management from separate EventQuestions/Index page into Events/Show
2. **Visual Preview**: Display all questions using the same QuestionCard component that users see
3. **Inline Answers**: Allow admins to set correct answers directly on the page
4. **Drag & Drop**: Enable easy reordering of questions
5. **Quick Actions**: Provide edit, clone, and delete actions on each question
6. **Template Import**: Streamline the process of importing question templates

## Key Changes

### 1. Compact Event Header

**Location**: Top of the page (white header section with shadow)

**Layout**:
- Single row with title, status badge, event date, and total entries count (all inline)
- Description as subtitle below the title row
- "Manage Invitations" (secondary) and "Edit Event" (primary) buttons on the right side

**Components**:
- Event name (h1, 2rem, bold)
- Status badge (success badge with event status)
- Event date (📅 icon + formatted date, subtle gray)
- Total entries count (👥 icon + count, subtle gray)
- Description (subtitle, 1rem, gray)
- Manage Invitations button (secondary, small)
- Edit Event button (primary)

**Design Decision**:
- No breadcrumbs (removed for cleaner, more compact header)
- All key info visible at a glance in one line
- Status is display-only here (edit via Edit Event form)
- Manage Invitations button provides access to dedicated invitations page

### 2. Questions Section

**Location**: Below the event information

**Header Actions**:
- "Add Custom Question" button (opens modal for creating new custom question from scratch)
- "Import from Template" button (navigates to dedicated Import Questions page)

**Question Display**:
- Each question shown in its own card (white background)
- Drag handle (⋮⋮) for reordering (always visible, left of question number)
- Question number badge (circular, primary color, 2.5rem)
- Question text (1.125rem, bold) with inline metadata:
  - Type badge (e.g., "Multiple Choice" - soft primary background)
  - Points (e.g., "10 pts" - gray text, normal weight)
  - All on same line with question text, wrapping if needed
- QuestionCard component showing all options as users see them
- Action buttons: Edit button (ghost) + 3-dot menu (Duplicate, Delete)

### 3. Answer Selection & Saving

**Flow**:
1. Admin clicks on any answer option in the QuestionCard
2. "Save Answer & Calculate Scores" button appears
3. Clicking the button:
   - Saves the answer to the database
   - Triggers score calculation for all entries
   - Shows success toast notification
   - Updates the "Current Answer" display

**Status Independence**:
- Answer setting is available in ANY status (draft, open, locked, completed)
- This provides maximum flexibility for admins

**Current Answer Display**:
- Shows below the question card when an answer is set
- Format: ✓ Current Answer: [Option Label]

### 4. Drag & Drop Reordering

**Always Available**:
- Drag handle (⋮⋮) visible on every question card
- No separate "reorder mode" - drag anytime

**Interaction**:
- Click and drag the handle to reorder questions
- Visual feedback (opacity, border) during drag
- Changes persist immediately on drop

**Technical Note**:
- Reuses existing reorder logic from EventQuestions/Index
- Updates both event_questions and group_questions tables

### 5. Add & Edit Question Modal

**Unified Modal Approach**:
- Same modal used for both adding new custom questions AND editing existing questions
- When adding: all fields are blank
- When editing: fields pre-populated with existing question data

**Modal Contents**:
- Question text (textarea)
- Answer options with bonus points (add/remove options)
- Base points value (number input)
- Warning banner (conditional - only shows when editing a question with existing user answers)
- Submit button ("Add Question" or "Update Question" depending on mode)

**Warning Banner** (Edit mode only):
- Shows when `user_answers_count > 0`
- Orange warning box with icon
- Message: "This question has X submitted answers. Changing the question text or options may affect grading accuracy."
- Prevents accidental breaking changes to questions users have already answered

**Excluded from Modal**:
- Question statistics (created date, updated date, answer count) - not needed for core editing workflow
- Question type selector - type is set at creation and cannot be changed

**Design Decision**:
- Modal is simpler and faster than full-page form for basic question editing
- Keep warning banner for safety (prevents breaking existing entries)
- Drop statistics to reduce clutter (not critical for editing workflow)
- Separate Import Questions functionality to dedicated page (see section 6)

### 6. Import Questions

**Separate Flow for Template Import**:
- "Import from Template" button in Questions section header
- Navigates to dedicated Import Questions page (existing Add Question page)
- Allows selecting from question templates
- Supports variable substitution (team names, dates, etc.)
- Can import multiple questions at once
- Returns to Events Show page after import

**Design Decision**:
- Import is fundamentally different from creating custom questions
- Needs space for template selection, preview, and variable substitution
- Keeping as separate page provides better UX than cramming into modal

### 7. Question Actions

**Add Custom Question** (Button in section header):
- Opens modal for creating new custom question
- See section 5 for modal details

**Import from Template** (Button in section header):
- Navigates to Import Questions page
- See section 6 above

**Edit** (Pencil Icon on each question):
- Opens modal with pre-populated question data
- Same modal as Add Custom Question
- See section 5 above

**Duplicate** (Copy Icon - in 3-dot menu):
- Creates copy of question with next order number
- Preserves all question data (text, options, points)
- Creates corresponding group questions
- Shows success toast

**Delete** (Trash Icon - in 3-dot menu):
- Shows confirmation dialog
- "Delete Question?" with warning message
- Deletes question and reorders remaining questions
- Shows success toast

## Removed Features

### Quick Actions Section
The old "Quick Actions" section with large cards linking to separate pages has been removed:
- ~~Manage Questions~~ (now inline on this page)
- ~~Set Event Answers~~ (now inline on this page)
- ~~Captain Invitations~~ (removed - GameQuiz is legacy functionality)
- ~~Host Game~~ (AmericaSays - not needed)
- ~~Launch Game Board~~ (AmericaSays - not needed)

**Decision**: Most actions are now inline, reducing need for navigation

### Event Invitations Section
The invitations management section at the bottom of the page has been removed:
- GameQuiz functionality is legacy and being phased out
- If needed in the future, invitations would be managed on a dedicated page

### Breadcrumbs
Removed from the page header to create a cleaner, more compact design.

### Separate EventQuestions Pages
The following pages may no longer be needed:
- `Admin/EventQuestions/Index.vue` - functionality moved to Events/Show
- Navigation to questions index removed

**Note**: EventQuestions/Edit page is still used (navigated to via Edit button on each question).

## Technical Implementation

### Backend Requirements

**New Route Needed**:
```php
// Set answer for a specific question
POST /admin/events/{event}/questions/{question}/set-answer
```

**Expected Behavior**:
- Validate the answer value
- Update the event_question's correct_answer field (or event_answers table for multiple choice)
- Trigger score recalculation for all entries in this event
- Return success/error response

**Existing Routes Used**:
- `admin.events.event-questions.update` - Update question (navigates to edit page)
- `admin.events.event-questions.duplicate` - Duplicate question
- `admin.events.event-questions.destroy` - Delete question
- `admin.events.event-questions.reorder` - Reorder questions
- `admin.events.event-questions.create` - Add new question (navigates to add page)

### Frontend Props Required

```js
{
    event: Object,           // Event details (name, description, event_date, status, etc.)
    questions: Array,        // Array of event questions with options
    stats: Object,          // Statistics (total_entries, etc.)
}
```

### Question Data Structure

Each question should include:
```js
{
    id: Number,
    question_text: String,
    question_type: String,      // 'multiple_choice' for now
    options: Array,             // [{ label: String, points: Number }]
    points: Number,
    order_number: Number,       // or display_order
    correct_answer: String|Null, // Current answer if set
    event_answers: Array|Null,  // For ranked_answers type
}
```

## User Experience Flow

### Setting Answers for an Event

1. Admin navigates to Events → [Event Name]
2. Sees all questions displayed with QuestionCard components
3. Scrolls through questions, viewing them as users would see them
4. For each question, clicks the correct answer
5. "Save Answer" button appears
6. Clicks button → Answer saved, scores calculated, toast confirms success
7. "Current Answer" indicator appears below question
8. Repeats for remaining questions
9. All answers visible at a glance with checkmark indicators

### Reordering Questions

1. Admin sees questions aren't in desired order
2. Clicks and drags question by its drag handle (⋮⋮)
3. Drops question in new position
4. Order persists immediately, toast confirms success

### Adding Custom Questions

1. Admin wants to create a new custom question
2. Clicks "Add Custom Question" button
3. Modal opens with blank form
4. Fills in question text, options, and points
5. Clicks "Add Question"
6. Modal closes, new question appears in the list with success toast

### Importing Questions from Templates

1. Admin has new event for upcoming Super Bowl
2. Clicks "Import from Template" button
3. Navigates to Import Questions page
4. Selects template (e.g., "Super Bowl LIX Questions")
5. Reviews questions and applies variable substitutions (team names, dates, etc.)
6. Clicks "Import Questions"
7. Returns to Events Show page
8. Imported questions appear in the list

### Editing Questions

1. Admin notices a typo or wants to adjust points
2. Clicks Edit (pencil icon) on the question
3. Modal opens with pre-populated question data
4. If question has existing user answers, warning banner appears
5. Makes changes
6. Clicks "Update Question"
7. Modal closes, changes reflected immediately with success toast

## Design System Compliance

All components use the project's design system:

**Colors**:
- Primary (#1a3490) for question numbers, primary buttons
- Success (#57d025) for "Save Answer" button, completion indicators
- Danger (#af1919) for delete actions
- Border (#d4d4d4) for page background (provides contrast with white cards)
- White (#ffffff) for header, cards, question wrappers
- Subtle (#525252) for meta text (date, entries)
- Semantic colors used throughout (surface, border, body, subtle, muted)

**Components**:
- Button (with variants and icons)
- Card (with title and headerActions slots)
- Badge (for status, type indicators)
- Confirm (for delete confirmation)
- Toast (for notifications)
- QuestionCard (domain component)
- Dropdown (for 3-dot action menu)
- Icon (Font Awesome wrapper)

**Layout**:
- max-w-7xl container with padding
- Responsive grids (2/4 columns)
- Consistent spacing (space-y-6, gap-4, etc.)
- Card-based sections

## Future Enhancements

1. **Inline Question Creation**: Replace separate Create page with modal
2. **Variable Substitution UI**: Better interface for template variables during import
3. **Bulk Answer Setting**: Set answers for multiple questions at once
4. **Question Filtering**: Filter by type, status, or search
5. **Keyboard Shortcuts**: Quick navigation and actions
6. **Undo/Redo**: For answer changes and reordering
7. **Answer History**: Show previous answers and when they were set
8. **Preview Mode**: Toggle between admin view and user view
9. **Question Types**: Support yes_no, numeric, text, ranked_answers types inline

## Migration Path

1. ✅ Update Events/Show.vue with new layout and functionality
2. ⏳ Create `admin.events.set-answer` route and controller method
3. ⏳ Update EventQuestionController@show to load questions with answers
4. ⏳ Test answer setting and score calculation
5. ⏳ Test all question CRUD operations (edit, duplicate, delete)
6. ⏳ Test template import functionality
7. ⏳ Test drag & drop reordering
8. ⏳ Remove old "Manage Questions" link from Quick Actions
9. ⏳ Consider deprecating EventQuestions/Index page (or redirect to Events/Show)
10. ⏳ Update documentation and user guides

## Success Metrics

- **Reduced Clicks**: Setting answers requires 2 clicks instead of navigating through multiple pages
- **Visual Clarity**: Admins can see all questions in user-facing format
- **Time Savings**: Event setup time reduced by 50%
- **Error Reduction**: Fewer mistakes due to better visual preview
- **User Satisfaction**: Admins report easier workflow

## Questions & Decisions Log

**Q: Should answer setting be restricted by event status?**
A: No - allow in any status for maximum flexibility. Admins may need to update answers after event is locked or completed.

**Q: Modal vs. separate page for adding/editing custom questions?**
A: Use a unified modal for both Add and Edit custom questions. The modal includes the warning banner (when editing questions with existing answers) but excludes statistics. This provides a faster workflow while maintaining safety.

**Q: Should we separate Import Template from Add Custom Question?**
A: Yes - "Import from Template" gets its own button that navigates to a dedicated page. Template import is fundamentally different (template selection, variable substitution, bulk import) and needs more space than a modal provides.

**Q: What should the modal include?**
A: Question text, options with bonus points, base points, and a warning banner (conditional - only when editing questions with existing user answers). Exclude statistics (created/updated dates) as they're not critical for the editing workflow.

**Q: Should there be a separate "Reorder Mode"?**
A: No - show drag handles (⋮⋮) on every question at all times. Changes persist immediately on drop.

**Q: What about the Event Invitations section?**
A: Removed - GameQuiz functionality is legacy and being phased out. No need for invitation management on this page.

**Q: Should we show breadcrumbs?**
A: No - removed to create a cleaner, more compact header design.

**Q: What background color should the page use?**
A: Use `border` (#d4d4d4) instead of `surface` (#f5f5f5) to provide better contrast with white cards.

**Q: Should we add a "Manage Invitations" button?**
A: Yes - add as a secondary button next to "Edit Event" that navigates to the Captain Invitations page (`admin.events.captain-invitations.index`).

**Q: Should we support all question types immediately?**
A: No - start with multiple_choice only. Add others later as needed.

**Q: Keep EventQuestions/Index page?**
A: Likely deprecate - questions belong to events and this page provides better context. Redirect or remove link.
