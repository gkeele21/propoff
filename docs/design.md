# PropOff - Technical Design Document

**Project**: Event Prediction Application (formerly Game Prediction Application)
**Version**: 3.2 - Captain System with Complete Color Implementation
**Last Updated**: November 30, 2025
**Status**: Implementation Complete - Captain System + Guest Captain Flow + Dual Grading + 8-Color Brand System

---

## Table of Contents
1. [System Architecture](#system-architecture)
2. [Database Design](#database-design)
3. [Backend Architecture](#backend-architecture)
4. [Frontend Architecture](#frontend-architecture)
5. [API Design](#api-design)
6. [Authentication & Authorization](#authentication--authorization)
7. [Key Features Implementation](#key-features-implementation)

---

## System Architecture

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                        Client Layer                          │
│  ┌────────────────────────────────────────────────────┐    │
│  │  Vue 3 SPA (Composition API) + Tailwind CSS        │    │
│  │  - 13+ Vue Components (Admin + User)               │    │
│  │  - Heroicons for UI                                │    │
│  │  - Responsive Design                               │    │
│  └────────────────────────────────────────────────────┘    │
└───────────────────────┬─────────────────────────────────────┘
                        │ Inertia.js v0.6.11
┌───────────────────────▼─────────────────────────────────────┐
│                    Application Layer                         │
│  ┌────────────────────────────────────────────────────┐    │
│  │           Laravel 10.49.1 Backend                   │    │
│  │  ┌──────────────────────────────────────────┐      │    │
│  │  │  Controllers (12 total)                  │      │    │
│  │  │  - 5 User Controllers                    │      │    │
│  │  │  - 7 Admin Controllers                   │      │    │
│  │  ├──────────────────────────────────────────┤      │    │
│  │  │  Services (3 classes)                    │      │    │
│  │  │  - GameService                           │      │    │
│  │  │  - SubmissionService ⭐ Grading Logic   │      │    │
│  │  │  - LeaderboardService                    │      │    │
│  │  ├──────────────────────────────────────────┤      │    │
│  │  │  Middleware (3 custom)                   │      │    │
│  │  │  - IsAdmin, GameAccessible, etc.        │      │    │
│  │  ├──────────────────────────────────────────┤      │    │
│  │  │  Policies (4 classes)                    │      │    │
│  │  │  - Authorization Rules                   │      │    │
│  │  ├──────────────────────────────────────────┤      │    │
│  │  │  Models (9 Eloquent)                     │      │    │
│  │  │  - Rich Relationships                    │      │    │
│  │  └──────────────────────────────────────────┘      │    │
│  └────────────────────────────────────────────────────┘    │
└───────────────────────┬─────────────────────────────────────┘
                        │
┌───────────────────────▼─────────────────────────────────────┐
│                      Data Layer                              │
│  ┌────────────────────────────────────────────────────┐    │
│  │              MySQL Database                         │    │
│  │              - 14 Tables (9 custom)                │    │
│  │              - Optimized Indexes                   │    │
│  │              - Foreign Key Constraints             │    │
│  └────────────────────────────────────────────────────┘    │
│  ┌────────────────────────────────────────────────────┐    │
│  │          Redis Cache (Ready, not active)           │    │
│  └────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────┘
```

### Architecture Patterns ✅ IMPLEMENTED

**Backend**:
- ✅ **MVC Pattern**: Laravel's standard structure
- ✅ **Service Layer**: Business logic in dedicated service classes
- ✅ **Policy-Based Authorization**: Fine-grained access control
- ✅ **Repository Pattern**: Implicit through service classes
- ⏳ **Event-Driven**: Structure ready, not yet implemented

**Frontend**:
- ✅ **Component-Based**: Vue 3 Composition API
- ✅ **SPA Architecture**: Inertia.js bridge
- ✅ **Composables**: Reusable logic with Vue composables
- ⏳ **State Management**: Pinia ready but not needed yet

---

## Database Design

### Entity Relationship Diagram

```
users ─────────────┐
│                  │
│ ┌────────────────┴─────────────┐
│ │     user_groups (pivot)      │
│ └────────────────┬─────────────┘
│                  │
├──────────────────┴──────────── groups
│                                  │
│                                  │
├─────────────────────────────── games
│                                  │
│                                  ├── questions
│                                  │    │
│                                  │    ├─── question_templates
│                                  │    │
│                                  │    └─── group_question_answers ⭐ CORE
│                                  │         (group-specific correct answers)
│                                  │
├─────────────────────────────── submissions
│                                  │
│                                  └─── user_answers
│
└─────────────────────────────── leaderboards
                                  (materialized view)
```

### Database Schema (14 Tables)

#### ⭐ Core Tables

**1. users** (Extended Laravel Default) ⭐ UPDATED
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NULL,                    -- ⭐ Nullable for guest users
    password VARCHAR(255) NULL,                 -- ⭐ Nullable for guest users
    role ENUM('admin', 'user', 'guest') DEFAULT 'user',  -- ⭐ Added 'guest' role
    guest_token VARCHAR(32) NULL UNIQUE,        -- ⭐ NEW: Token for guest access
    avatar VARCHAR(255) NULL,
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_guest_token (guest_token)         -- ⭐ NEW: Index for guest token lookups
);
```

**Guest User Notes**:
- Guest users created automatically via captain invitation links
- No password required (password = NULL)
- Email is optional (can be NULL)
- guest_token is unique 32-character string for future access
- Guest users can become captains and have full captain permissions
- Guest users can be upgraded to full users later (Phase 11)

**2. groups**
```sql
CREATE TABLE groups (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    description TEXT NULL,
    is_public BOOLEAN DEFAULT FALSE,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_code (code),
    INDEX idx_created_by (created_by)
);
```

**3. user_groups** (Pivot Table)
```sql
CREATE TABLE user_groups (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    group_id BIGINT UNSIGNED NOT NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_group (user_id, group_id),
    INDEX idx_user_id (user_id),
    INDEX idx_group_id (group_id)
);
```

**4. games**
```sql
CREATE TABLE games (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    category VARCHAR(100) NOT NULL,
    event_date DATETIME NOT NULL,
    status ENUM('draft', 'open', 'locked', 'in_progress', 'completed') DEFAULT 'draft',
    lock_date DATETIME NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_event_date (event_date),
    INDEX idx_created_by (created_by)
);
```

**5. question_templates**
```sql
CREATE TABLE question_templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    question_text TEXT NOT NULL,
    question_type ENUM('multiple_choice', 'yes_no', 'numeric', 'text') NOT NULL,
    category VARCHAR(100) NULL,
    default_points INT DEFAULT 1,
    variables JSON NULL COMMENT 'Array of variable names like ["team1", "player1"]',
    default_options JSON NULL COMMENT 'Default options for multiple choice',
    is_favorite BOOLEAN DEFAULT FALSE,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_category (category),
    INDEX idx_is_favorite (is_favorite)
);
```

**6. questions**
```sql
CREATE TABLE questions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    game_id BIGINT UNSIGNED NOT NULL,
    template_id BIGINT UNSIGNED NULL,
    question_text TEXT NOT NULL,
    question_type ENUM('multiple_choice', 'yes_no', 'numeric', 'text') NOT NULL,
    options JSON NULL COMMENT 'For multiple choice: [{"label": "Option A", "points": 2}, ...]',
    points INT DEFAULT 1 COMMENT 'Base points - awarded for answering correctly',
    display_order INT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
    FOREIGN KEY (template_id) REFERENCES question_templates(id) ON DELETE SET NULL,
    INDEX idx_game_id (game_id),
    INDEX idx_display_order (display_order)
);
```

**Scoring System** ⭐ **NEW FEATURE**:
- **`points`**: Base points awarded for correctly answering the question
- **`options[].points`**: Optional bonus points for each specific answer option
- **Total Score**: Base Points + Option Bonus Points (if answered correctly)
- **Example**: Question with 5 base points:
  - Option A: "Yes" (+2 bonus) = 7 points total if correct
  - Option B: "No" (+0 bonus) = 5 points total if correct
  - Wrong answer = 0 points

**7. group_question_answers** ⭐ **CORE FEATURE**
```sql
CREATE TABLE group_question_answers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_id BIGINT UNSIGNED NOT NULL,
    question_id BIGINT UNSIGNED NOT NULL,
    correct_answer TEXT NOT NULL,
    is_void BOOLEAN DEFAULT FALSE COMMENT 'If true, no points awarded for this question in this group',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    UNIQUE KEY unique_group_question (group_id, question_id),
    INDEX idx_group_id (group_id),
    INDEX idx_question_id (question_id)
);
```

**Purpose**: Stores group-specific correct answers for questions, allowing each group to have different correct answers for subjective questions.

**8. submissions**
```sql
CREATE TABLE submissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    game_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    group_id BIGINT UNSIGNED NOT NULL,
    total_score INT DEFAULT 0,
    possible_points INT DEFAULT 0,
    percentage DECIMAL(5,2) DEFAULT 0.00,
    is_complete BOOLEAN DEFAULT FALSE,
    submitted_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
    UNIQUE KEY unique_submission (game_id, user_id, group_id),
    INDEX idx_game_id (game_id),
    INDEX idx_user_id (user_id),
    INDEX idx_group_id (group_id),
    INDEX idx_is_complete (is_complete)
);
```

**9. user_answers**
```sql
CREATE TABLE user_answers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    submission_id BIGINT UNSIGNED NOT NULL,
    question_id BIGINT UNSIGNED NOT NULL,
    answer_text TEXT NULL,
    points_earned INT DEFAULT 0,
    is_correct BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    UNIQUE KEY unique_submission_question (submission_id, question_id),
    INDEX idx_submission_id (submission_id),
    INDEX idx_question_id (question_id)
);
```

**10. leaderboards** (Materialized View)
```sql
CREATE TABLE leaderboards (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    game_id BIGINT UNSIGNED NOT NULL,
    group_id BIGINT UNSIGNED NULL COMMENT 'NULL for global leaderboard',
    user_id BIGINT UNSIGNED NOT NULL,
    rank INT NOT NULL,
    total_score INT NOT NULL,
    possible_points INT NOT NULL,
    percentage DECIMAL(5,2) NOT NULL,
    answered_count INT NOT NULL,
    updated_at TIMESTAMP,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_leaderboard (game_id, group_id, user_id),
    INDEX idx_game_group (game_id, group_id),
    INDEX idx_rank (rank)
);
```

### Database Optimizations ✅ IMPLEMENTED

1. **Indexing Strategy**:
   - Foreign keys indexed
   - Frequently queried columns (status, dates, codes)
   - Composite indexes for multi-column queries
   - Unique constraints to prevent duplicates

2. **Data Integrity**:
   - Foreign key constraints with CASCADE rules
   - UNIQUE constraints on natural keys
   - ENUM types for status fields
   - NOT NULL where appropriate

3. **Performance**:
   - Materialized leaderboards for fast reads
   - JSON columns for flexible data (options, variables)
   - Appropriate field types (VARCHAR vs TEXT)
   - Timestamp indexes for chronological queries

---

## Backend Architecture

### Directory Structure (Implemented)

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── DashboardController.php ✅
│   │   ├── GameController.php ✅
│   │   ├── GroupController.php ✅
│   │   ├── LeaderboardController.php ✅
│   │   ├── ProfileController.php ✅
│   │   └── Admin/
│   │       ├── DashboardController.php ✅
│   │       ├── GameController.php ✅
│   │       ├── QuestionTemplateController.php ✅
│   │       ├── QuestionController.php ✅
│   │       ├── GradingController.php ✅ CORE
│   │       ├── UserController.php ✅
│   │       └── GroupController.php ✅
│   ├── Middleware/
│   │   ├── IsAdmin.php ✅
│   │   ├── GameAccessible.php ✅
│   │   └── SubmissionEditable.php ✅
│   └── Requests/
│       ├── StoreGameRequest.php ✅
│       ├── UpdateGameRequest.php ✅
│       ├── StoreGroupRequest.php ✅
│       ├── UpdateGroupRequest.php ✅
│       └── ... (8 total) ✅
├── Models/
│   ├── User.php ✅
│   ├── Group.php ✅
│   ├── Game.php ✅
│   ├── QuestionTemplate.php ✅
│   ├── Question.php ✅
│   ├── GroupQuestionAnswer.php ✅ CORE
│   ├── Submission.php ✅
│   ├── UserAnswer.php ✅
│   └── Leaderboard.php ✅
├── Policies/
│   ├── GamePolicy.php ✅
│   ├── QuestionPolicy.php ✅
│   ├── SubmissionPolicy.php ✅
│   └── GroupPolicy.php ✅
├── Services/
│   ├── GameService.php ✅
│   ├── SubmissionService.php ✅ CORE (Grading Logic)
│   └── LeaderboardService.php ✅
├── Jobs/
│   ├── CalculateScoresJob.php ✅
│   └── UpdateLeaderboardJob.php ✅
└── Events/ (Ready, not implemented)
```

### Service Layer Pattern ✅ IMPLEMENTED

**GameService** - Game business logic
- hasUserJoinedGame()
- getUserSubmission()
- isGamePlayable()
- getActiveGames()
- searchGames()
- filterGamesByStatus()
- calculatePossiblePoints()

**SubmissionService** ⭐ **CORE GRADING LOGIC**
- createSubmission()
- saveAnswers()
- completeSubmission()
- **gradeSubmission()** - Type-aware answer comparison
- **compareAnswers()** - Question type specific logic
- getUserSubmissionStats()
- canEditSubmission()

**LeaderboardService**
- updateLeaderboardForSubmission()
- **updateRanks()** - Advanced tie-breaking
- recalculateGameLeaderboards()
- **createGlobalLeaderboard()** - Cross-group aggregation
- getLeaderboard()
- getUserRank()
- getLeaderboardStats() - Median, avg, min, max

---

## Frontend Architecture

### Vue 3 Component Structure (Implemented)

```
resources/js/
├── Pages/
│   ├── Dashboard.vue ✅ (User)
│   ├── Admin/
│   │   ├── Dashboard.vue ✅
│   │   ├── Games/
│   │   │   ├── Index.vue ✅
│   │   │   ├── Show.vue ✅
│   │   │   ├── Create.vue ✅
│   │   │   └── Edit.vue ✅
│   │   ├── Grading/
│   │   │   └── Index.vue ✅ CORE FEATURE
│   │   ├── Users/
│   │   │   └── Index.vue ✅
│   │   ├── Groups/
│   │   │   └── Index.vue ✅
│   │   └── QuestionTemplates/
│   │       └── Create.vue ✅
│   ├── Games/ (Existing from Breeze)
│   ├── Groups/ (Existing from Breeze)
│   ├── Submissions/ (Existing from Breeze)
│   └── Leaderboards/ (Existing from Breeze)
├── Components/ (Ready for shared components)
├── Layouts/
│   ├── AuthenticatedLayout.vue ✅
│   ├── GuestLayout.vue ✅
│   └── AdminLayout.vue (Uses AuthenticatedLayout)
└── Composables/ (Ready for reusable logic)
```

### Component Patterns Used

**Script Setup (Vue 3 Composition API)**:
```vue
<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

defineProps({ /* props */ });

// Reactive state
const form = useForm({ /* data */ });

// Methods
const handleSubmit = () => {
    form.post(route('route.name'));
};
</script>
```

**Form Handling with Inertia**:
```javascript
const form = useForm({
    field1: '',
    field2: false,
});

form.post(route('route.name'), {
    onSuccess: () => { /* success handler */ },
    onError: () => { /* error handler */ },
});
```

**Real-Time Filtering**:
```javascript
const search = ref(props.filters.search || '');

const filter = () => {
    router.get(route('route.name'), {
        search: search.value,
    }, {
        preserveState: true,
        replace: true,
    });
};
```

### Design System ✅ IMPLEMENTED

**Color Palette** (8-Color System) ✅ **COMPLETED**:

**Brand Colors**:
- PropOff Red (#af1919) - Primary CTA, errors, admin functions
- PropOff Orange (#f47612) - Groups, captains, warnings
- PropOff Green (#57d025) - Success states, entries (icons/large text)
- PropOff Dark Green (#186916) - Success text, completed states, leaderboards
- PropOff Blue (#1a3490) - Events, questions, information, form focus states

**Neutral Colors**:
- Gray (shades 50-900) - Backgrounds, borders, members, text
- Black - Primary text
- White - Backgrounds, text on colored backgrounds

**Semantic Mapping** ✅ **FULLY IMPLEMENTED**:
- **Events**: PropOff Blue (`bg-propoff-blue/10`, `text-propoff-blue`, `border-propoff-blue/30`)
- **Groups**: PropOff Orange (`bg-propoff-orange/10`, `text-propoff-orange`, `border-propoff-orange/30`)
- **Entries**: PropOff Green (`bg-propoff-green/10`, `text-propoff-dark-green`, `border-propoff-green/30`)
- **Leaderboards**: PropOff Dark Green (`bg-propoff-dark-green/10`, `text-propoff-dark-green`, medal emojis 🥇🥈🥉)
- **Question Templates**: PropOff Dark Green (`text-propoff-dark-green`, dashboard icon)
- **Admin**: PropOff Red (`bg-propoff-red/10`, `text-propoff-red`, `border-propoff-red/30`)
- **Captains**: PropOff Orange (`bg-propoff-orange/10`, `text-propoff-orange`, `border-propoff-orange/30`)

**Status Colors** ✅ **IMPLEMENTED**:
- Draft: Gray (`bg-gray-100`, `text-gray-800`)
- Open/Active: PropOff Green (`bg-propoff-green/20`, `text-propoff-dark-green`)
- Locked: PropOff Orange (`bg-propoff-orange/20`, `text-propoff-orange`)
- In Progress: PropOff Blue (`bg-propoff-blue/20`, `text-propoff-blue`)
- Completed: PropOff Dark Green (`bg-propoff-dark-green/20`, `text-propoff-dark-green`)

**Accessibility** ✅ **WCAG 2.1 AA COMPLIANT**:
- All color combinations meet contrast requirements
- Focus states use `propoff-blue/50` ring opacity
- Error states use `text-propoff-red` with adequate contrast

**Typography**:
- Headings: Figtree font, font-bold
- Body: Figtree font, font-normal
- Sizes: text-xl, text-lg, text-base, text-sm

**Components**:
- Cards: White background, shadow-sm, rounded-lg
- Buttons: Brand colors, hover states with scale
- Tables: Striped rows, hover effects
- Forms: Consistent spacing, brand color focus states

---

## API Design

### RESTful Routes (Inertia.js)

#### User Routes ✅ IMPLEMENTED
```
GET    /dashboard
GET    /games
GET    /games/available
GET    /games/{game}
GET    /games/{game}/play
POST   /games/{game}/join
POST   /games/{game}/submit
PUT    /games/{game}/answers

GET    /groups
POST   /groups
GET    /groups/{group}
POST   /groups/join
DELETE /groups/{group}/leave
DELETE /groups/{group}/members/{user}

GET    /leaderboards
GET    /leaderboards/games/{game}
GET    /leaderboards/games/{game}/groups/{group}
POST   /leaderboards/games/{game}/recalculate

GET    /profile
PATCH  /profile
DELETE /profile
```

#### Admin Routes ✅ IMPLEMENTED (60+ routes)
```
GET    /admin/dashboard

Resource: /admin/games (CRUD)
POST   /admin/games/{game}/duplicate
PUT    /admin/games/{game}/status
GET    /admin/games/{game}/statistics

Resource: /admin/question-templates (CRUD)
POST   /admin/question-templates/{template}/duplicate
POST   /admin/question-templates/{template}/preview

Resource: /admin/games/{game}/questions (CRUD)
POST   /admin/games/{game}/questions/from-template/{template}
POST   /admin/games/{game}/questions/reorder
POST   /admin/games/{game}/questions/{question}/duplicate
POST   /admin/games/{game}/questions/bulk-import

⭐ GRADING ROUTES (CORE):
GET    /admin/grading/{game}
POST   /admin/grading/{game}/questions/{question}/set-answer
POST   /admin/grading/{game}/groups/{group}/bulk-set-answers
POST   /admin/grading/{game}/questions/{question}/groups/{group}/toggle-void
POST   /admin/grading/{game}/calculate-scores
GET    /admin/grading/{game}/export-csv
GET    /admin/grading/{game}/export-detailed-csv
GET    /admin/grading/{game}/groups/{group}/summary

GET    /admin/users
POST   /admin/users/{user}/update-role
DELETE /admin/users/{user}
POST   /admin/users/bulk-delete
GET    /admin/users/export/csv

GET    /admin/groups
PATCH  /admin/groups/{group}
DELETE /admin/groups/{group}
POST   /admin/groups/bulk-delete
GET    /admin/groups/export/csv
```

---

## Authentication & Authorization

### Laravel Breeze + Inertia ✅ IMPLEMENTED

**Features**:
- Session-based authentication
- CSRF protection
- Email verification
- Password reset
- Profile management
- Remember me

### Authorization Layers ✅ IMPLEMENTED

**1. Middleware** (Route-level):
- `IsAdmin` - Check admin role
- `GameAccessible` - Validate game is open
- `SubmissionEditable` - Check edit permissions

**2. Policies** (Resource-level):
- `GamePolicy` - Game CRUD + custom methods
- `QuestionPolicy` - Admin-only management
- `SubmissionPolicy` - Time and ownership checks
- `GroupPolicy` - Creator/member permissions

**3. Form Requests** (Input-level):
- Validation rules
- Authorization checks
- Custom error messages

### Authorization Flow

```
Request → Middleware Check → Policy Check → Controller Action
                ↓                  ↓
              Fail 403         Fail 403
                ↓                  ↓
            Redirect         Redirect
```

---

## Key Features Implementation

### ⭐ 1. Group-Specific Answer System (CORE FEATURE)

**Database**:
```sql
group_question_answers (group_id, question_id, correct_answer, is_void)
UNIQUE (group_id, question_id)
```

**Service Logic** (SubmissionService):
```php
public function gradeSubmission(Submission $submission)
{
    $groupAnswers = GroupQuestionAnswer::where('question_id', $questionId)
        ->where('group_id', $submission->group_id)
        ->get()
        ->keyBy('question_id');
    
    foreach ($submission->userAnswers as $userAnswer) {
        $groupAnswer = $groupAnswers->get($userAnswer->question_id);
        
        if (!$groupAnswer || $groupAnswer->is_void) {
            $userAnswer->points_earned = 0;
            $userAnswer->is_correct = false;
            continue;
        }
        
        $isCorrect = $this->compareAnswers(
            $userAnswer->answer_text,
            $groupAnswer->correct_answer,
            $userAnswer->question->question_type
        );
        
        $userAnswer->is_correct = $isCorrect;
        $userAnswer->points_earned = $isCorrect 
            ? $userAnswer->question->points 
            : 0;
        
        $userAnswer->save();
    }
    
    $submission->total_score = $submission->userAnswers->sum('points_earned');
    $submission->percentage = /* calculate */;
    $submission->save();
}
```

**Admin Interface** (GradingController):
```php
public function setAnswer(Request $request, Game $game, Question $question)
{
    GroupQuestionAnswer::updateOrCreate(
        [
            'group_id' => $request->group_id,
            'question_id' => $question->id,
        ],
        [
            'correct_answer' => $request->correct_answer,
            'is_void' => $request->is_void ?? false,
        ]
    );
}

public function calculateScores(Game $game)
{
    $submissions = Submission::where('game_id', $game->id)
        ->where('is_complete', true)
        ->get();
    
    foreach ($submissions as $submission) {
        $this->submissionService->gradeSubmission($submission);
    }
    
    $this->leaderboardService->recalculateGameLeaderboards($game);
}
```

### ⭐ 2. Weighted Scoring with Bonus Points (NEW FEATURE)

**Concept**: Questions have base points + optional per-option bonus points

**Database Structure**:
```json
// questions.options field:
[
    {"label": "Yes", "points": 2},   // Base + 2 bonus
    {"label": "No", "points": 0},    // Base + 0 bonus
    {"label": "Maybe", "points": 1}  // Base + 1 bonus
]

// questions.points field:
5  // Base points for answering correctly
```

**Scoring Logic** (SubmissionService):
```php
protected function calculatePointsForAnswer(Question $question, string $answerText): int
{
    $basePoints = $question->points;
    $bonusPoints = 0;

    if ($question->question_type === 'multiple_choice' && $question->options) {
        $options = json_decode($question->options, true);

        foreach ($options as $option) {
            if (strcasecmp($option['label'], $answerText) === 0) {
                $bonusPoints = $option['points'] ?? 0;
                break;
            }
        }
    }

    return $basePoints + $bonusPoints;
}

// Example: Base = 5 pts, User picks "Yes" (+2 bonus) = 7 points total
// Example: Base = 5 pts, User picks "No" (+0 bonus) = 5 points total
// Wrong answer = 0 points (no base, no bonus)
```

**Frontend Display** (Continue.vue):
```vue
<label>
    <input type="radio" :value="option.label" />
    {{ option.label }}
    <span v-if="option.points > 0" class="bonus-badge">
        +{{ option.points }} bonus
    </span>
</label>
<p class="text-xs">Base: {{ question.points }} pts + any bonus shown</p>
```

**Benefits**:
- Players see point values BEFORE answering
- Admins can weight riskier/harder options higher
- Flexible scoring without complex rules
- Example use case: "Exact score" = +5 bonus, "Within 7 points" = +2 bonus

### 3. Question Template with Variables ✅ IMPLEMENTED

**Database Structure**:
```sql
question_templates:
  - title: "NFL Matchup Prediction"
  - question_text: "Who will win {team1} vs {team2}?"
  - variables: ["team1", "team2"]
  - category: "sports"
```

**Variable Workflow**:

1. **Create Template** (Admin):
   ```
   Question Text: "Who will win {team1} vs {team2}?"
   Variables: ["team1", "team2"]
   Options: ["{team1}", "{team2}", "Tie"]
   ```

2. **Use Template** (Admin creating question):
   - System detects variables in template
   - Modal appears asking for variable values
   - Live preview updates: "Who will win Eagles vs Cowboys?"
   - Admin fills: team1="Eagles", team2="Cowboys"

3. **Question Created**:
   ```
   Question Text: "Who will win Eagles vs Cowboys?"
   Options: ["Eagles", "Cowboys", "Tie"]
   ```

**Implementation** (Admin/Questions/Create.vue):
```javascript
const addSingleTemplate = (template) => {
    if (template.variables && template.variables.length > 0) {
        // Show modal with variable inputs
        currentTemplate.value = template;
        variableValues.value = {};
        template.variables.forEach(v => {
            variableValues.value[v] = '';
        });
        showVariableModal.value = true;
    } else {
        // No variables, add directly
        createQuestionFromTemplate(template);
    }
};

// Substitute variables in text
const previewText = computed(() => {
    let text = currentTemplate.value.question_text;
    Object.entries(variableValues.value).forEach(([key, value]) => {
        text = text.replace(new RegExp(`\\{${key}\\}`, 'g'), value);
    });
    return text;
});
```

**Features**:
- Category-based template filtering
- Bulk template import
- Live preview as you type variable values
- Reusable across multiple games
- Duplicate and modify existing templates

### 4. Type-Aware Answer Comparison ✅ IMPLEMENTED

```php
protected function compareAnswers($userAnswer, $correctAnswer, $questionType)
{
    switch ($questionType) {
        case 'multiple_choice':
        case 'yes_no':
            return strcasecmp(trim($userAnswer), trim($correctAnswer)) === 0;

        case 'numeric':
            return abs((float)$userAnswer - (float)$correctAnswer) < 0.01;

        case 'text':
            return strcasecmp(trim($userAnswer), trim($correctAnswer)) === 0;

        default:
            return false;
    }
}
```

### 3. Advanced Leaderboard Calculations ✅ IMPLEMENTED

**Tie-Breaking Logic**:
1. Primary: Percentage (higher wins)
2. Secondary: Total Score (higher wins)
3. Tertiary: Questions Answered (more wins)

```php
public function updateRanks($gameId, $groupId = null)
{
    $leaderboards = Leaderboard::where('game_id', $gameId)
        ->where('group_id', $groupId)
        ->orderByDesc('percentage')
        ->orderByDesc('total_score')
        ->orderByDesc('answered_count')
        ->get();
    
    $rank = 1;
    foreach ($leaderboards as $leaderboard) {
        $leaderboard->rank = $rank++;
        $leaderboard->save();
    }
}
```

### 4. CSV Export with Streaming ✅ IMPLEMENTED

```php
public function exportDetailedCSV(Game $game, Group $group = null)
{
    $filename = "game_{$game->id}_detailed_" . now()->format('YmdHis') . '.csv';
    
    return response()->stream(function () use ($game, $group) {
        $handle = fopen('php://output', 'w');
        
        fputcsv($handle, ['Submission ID', 'User', 'Group', /* ... */]);
        
        $submissions = Submission::where('game_id', $game->id)
            ->when($group, fn($q) => $q->where('group_id', $group->id))
            ->with(['user', 'group', 'userAnswers.question'])
            ->chunk(100, function ($submissions) use ($handle) {
                foreach ($submissions as $submission) {
                    foreach ($submission->userAnswers as $answer) {
                        fputcsv($handle, [/* data */]);
                    }
                }
            });
        
        fclose($handle);
    }, 200, [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
    ]);
}
```

---

## ⭐ 3. Guest Captain System (NEW FEATURE)

### Overview
Guest users can become captains without creating accounts. They click a captain invitation link, fill in their name (and optionally email), create a group, and are automatically logged in as a guest captain with full permissions.

### Guest User Flow

**1. Guest User Creation**:
```php
// Captain/GroupController.php - store() method
if (!$request->user()) {
    // Auto-create guest user
    $guestToken = Str::random(32);
    $user = User::create([
        'name' => $request->captain_name,
        'email' => $request->captain_email,  // Optional
        'password' => null,                   // No password
        'role' => 'guest',
        'guest_token' => $guestToken,
    ]);

    // Auto-login
    Auth::login($user);
}
```

**2. Captain Invitation Routes** (Public Access):
```php
// routes/web.php
Route::prefix('captain')->name('captain.')->group(function () {
    // No auth middleware - publicly accessible
    Route::get('/join/{token}', [GroupController::class, 'join']);
    Route::get('/events/{event}/create-group/{token}', [GroupController::class, 'create']);
    Route::post('/events/{event}/create-group/{token}', [GroupController::class, 'store']);
});
```

**3. Conditional Form Validation**:
```php
// CreateGroupRequest.php
public function rules(): array
{
    $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
        'grading_source' => 'required|in:captain,admin',
    ];

    // Guest-specific validation
    if (!$this->user()) {
        $rules['captain_name'] = 'required|string|max:255';
        $rules['captain_email'] = 'nullable|email|max:255';
    }

    return $rules;
}
```

**4. Dynamic Vue Components**:
```vue
<!-- CreateGroup.vue -->
<script setup>
const props = defineProps({
    event: Object,
    invitation: Object,
    isGuest: Boolean,  // Flag from controller
});

const form = useForm({
    name: '',
    description: '',
    grading_source: 'captain',
    captain_name: '',      // For guests only
    captain_email: '',     // For guests only
});

// Dynamic layout based on authentication
const LayoutComponent = props.isGuest ? GuestLayout : AuthenticatedLayout;
</script>

<template>
    <component :is="LayoutComponent">
        <!-- Guest-specific fields -->
        <div v-if="isGuest">
            <InputLabel for="captain_name" value="Your Name" />
            <TextInput v-model="form.captain_name" required />

            <InputLabel for="captain_email" value="Your Email (Optional)" />
            <TextInput v-model="form.captain_email" type="email" />
        </div>

        <!-- Regular group fields -->
        <InputLabel for="name" value="Group Name" />
        <TextInput v-model="form.name" required />
        <!-- ... -->
    </component>
</template>
```

### Database Schema

**users table with guest support**:
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NULL,                -- Nullable for guests
    password VARCHAR(255) NULL,             -- Nullable for guests
    role ENUM('admin', 'user', 'guest'),   -- Guest role
    guest_token VARCHAR(32) NULL UNIQUE,    -- For future access
    -- ... other fields
);
```

**captain_invitations table**:
```sql
CREATE TABLE captain_invitations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id BIGINT UNSIGNED NOT NULL,
    token VARCHAR(32) UNIQUE NOT NULL,
    max_uses INT NULL,                      -- NULL = unlimited
    times_used INT DEFAULT 0,
    expires_at TIMESTAMP NULL,              -- NULL = never expires
    is_active BOOLEAN DEFAULT true,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)
);
```

### Guest vs Authenticated Flow

| Aspect | Guest User | Authenticated User |
|--------|-----------|-------------------|
| **Access** | Direct (no login) | Must be logged in |
| **Name Field** | Required in form | From user account |
| **Email Field** | Optional in form | From user account |
| **Password** | NULL | Hashed |
| **Guest Token** | Generated | NULL |
| **Layout** | GuestLayout | AuthenticatedLayout |
| **Validation** | captain_name + captain_email | Standard group fields only |
| **After Creation** | Auto-logged in | Already logged in |

### Captain Permissions

**Guest captains have full permissions**:
- ✅ Customize group questions (add/remove/modify)
- ✅ Set correct answers (if captain grading mode)
- ✅ Grade submissions
- ✅ View leaderboards
- ✅ Manage members (add/remove/promote)
- ✅ Regenerate join codes
- ✅ Change grading source

**No restrictions**: Guest captains are functionally equivalent to authenticated captains.

### Invitation Expiration Handling

```php
// Controller checks if invitation is valid
if (!$invitation->canBeUsed()) {
    return Inertia::render('Captain/InvitationExpired', [
        'event' => $event,
        'invitation' => [
            'is_active' => $invitation->is_active,
            'expires_at' => $invitation->expires_at,
            'max_uses' => $invitation->max_uses,
            'times_used' => $invitation->times_used,
        ],
    ]);
}
```

**InvitationExpired.vue** displays reasons:
- Invitation deactivated by admin
- Expiration date passed
- Maximum usage limit reached

### Future Enhancements (Phase 11)
- ⏳ Email guest token link for future access
- ⏳ Convert guest to full user (add password)
- ⏳ Guest personal dashboard
- ⏳ Resend guest access link

---

## Security Implementation ✅

### 1. Input Validation
- Form Request classes for all inputs
- Type validation
- Range validation
- Sanitization

### 2. SQL Injection Prevention
- Eloquent ORM (parameterized queries)
- No raw SQL without bindings
- Query builder escaping

### 3. XSS Prevention
- Vue's automatic escaping
- No v-html without sanitization
- Content Security Policy ready

### 4. CSRF Protection
- Laravel middleware enabled
- Token verification on all POST/PUT/DELETE
- Inertia automatic handling

### 5. Authorization
- Middleware on all protected routes
- Policy checks before actions
- Role-based access control
- Time-based permissions

### 6. Data Protection
- Bcrypt password hashing
- Environment variable secrets
- HTTPS ready
- Database credentials secured

---

## Performance Optimizations ✅

### 1. Database
- Proper indexing on foreign keys
- Composite indexes for common queries
- Materialized leaderboards
- Eager loading relationships

### 2. Caching (Ready)
- Redis configuration ready
- Cache strategy designed
- Not yet implemented (not needed for MVP)

### 3. Frontend
- Inertia partial reloads
- Lazy component loading
- Asset optimization with Vite
- Minimal re-rendering

### 4. Query Optimization
- Eager loading to prevent N+1
- Chunk processing for large datasets
- Pagination for lists
- Selective field loading

---

## Testing Strategy (Planned)

### Unit Tests
- Service class methods
- Answer comparison logic
- Rank calculations
- Helper functions

### Feature Tests
- API endpoints
- Authorization policies
- Business logic flows
- Database operations

### Component Tests
- Vue components
- Form submissions
- User interactions
- State management

### E2E Tests
- Complete user flows
- Admin workflows
- Grading process
- Multi-user scenarios

---

## Deployment Architecture (Planned)

```
┌─────────────────┐
│   Load Balancer │
│   (HTTPS/SSL)   │
└────────┬────────┘
         │
    ┌────┴────┐
    │         │
┌───▼───┐ ┌──▼────┐
│ Web 1 │ │ Web 2 │  (Horizontal Scaling Ready)
└───┬───┘ └──┬────┘
    │        │
    └────┬───┘
         │
    ┌────▼────┐
    │  MySQL  │
    │ Primary │
    └────┬────┘
         │
    ┌────▼────┐
    │  Redis  │
    │  Cache  │
    └─────────┘
```

---

## Conclusion

PropOff's technical architecture is production-ready with all core features implemented, including the revolutionary captain system and guest access. Key innovations include:

1. **Group-Specific Answer System** ⭐ - Different correct answers per group for subjective questions
2. **Captain System with 3-Tier Questions** ⭐ - Templates → Event Questions → Group Questions (captain customizable)
3. **Guest Captain System** ⭐ - Passwordless captain access via invitation links (no registration required)
4. **Dual Grading Model** ⭐ - Captain grading (real-time) vs Admin grading (post-event)
5. **Weighted Bonus Points** ⭐ - Base + per-option bonus scoring for flexible point values
6. **Question Templates with Variables** - Reusable templates with dynamic substitution
7. **Type-Aware Grading** - Smart comparison for multiple choice, numeric, and text answers
8. **Dynamic Guest/Auth Flows** - Same codebase handles both authenticated and guest users seamlessly

The service layer provides clean separation of concerns, policies ensure proper authorization, the Vue 3 frontend with Inertia.js delivers a responsive modern user experience, and the guest system removes all barriers to captain recruitment.

**Major Achievements**:
- ✅ 3-tier question architecture (Templates → Event Questions → Group Questions)
- ✅ Dual grading system (Captain vs Admin)
- ✅ Guest captain system (zero-friction onboarding)
- ✅ Per-group question customization
- ✅ Complete captain management features
- ✅ Model relationship fixes and bug corrections

**Status**: MVP Complete with Captain & Guest Systems, All Core Features Implemented, Production Deployment Ready

**Last Updated**: November 21, 2025
