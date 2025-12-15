# PropOff - Architecture & Design

## 1. System Architecture

### 1.1 Architecture Pattern
**Modern Monolithic MVC with SPA Frontend**

```
┌─────────────────────────────────────────┐
│         Presentation Layer              │
│    (Vue 3 Composition API + Vite)       │
│         Tailwind CSS Styling            │
└─────────────────────────────────────────┘
                   ↕ Inertia.js Protocol
┌─────────────────────────────────────────┐
│         Application Layer               │
│      (Laravel 10 Controllers)           │
│     - Inertia Responses                 │
│     - Request Validation                │
│     - Authorization (Policies)          │
└─────────────────────────────────────────┘
                   ↕ Service Layer
┌─────────────────────────────────────────┐
│         Business Logic Layer            │
│      (Laravel Services)                 │
│     - Submission Grading                │
│     - Scoring Calculations              │
│     - Leaderboard Rankings              │
└─────────────────────────────────────────┘
                   ↕ Eloquent ORM
┌─────────────────────────────────────────┐
│         Data Layer                      │
│      (MySQL 8.0+ Database)              │
│     - Migrations                        │
│     - Models & Relationships            │
└─────────────────────────────────────────┘
```

### 1.2 Technology Stack

**Backend**:
- Laravel 10.49.1 (PHP 8.2+)
- MySQL 8.0+
- Eloquent ORM
- Laravel Breeze (Authentication)

**Frontend**:
- Vue 3 (Composition API, JavaScript)
- Vite (Build tool)
- Tailwind CSS + PropOff brand colors
- Inertia.js (SPA bridge)

**Why Inertia.js?**
- Server-side routing (simpler than REST API)
- No API endpoints needed
- Automatic CSRF protection
- SPA-like experience without complexity

---

## 2. Core Design Patterns

### 2.1 Service Layer Pattern

Business logic is extracted into dedicated service classes:

```
Controllers → Services → Models
```

**Key Services**:
- `SubmissionService` - Grading logic and score calculation
- `LeaderboardService` - Ranking and tie-breaking

**Why Services?**
- Keeps controllers thin
- Reusable business logic
- Easier to test
- Clear separation of concerns

**Details**: See [technical/scoring-calculations.md](technical/scoring-calculations.md)

### 2.2 Policy-Based Authorization

All authorization uses Laravel Policies:

```php
// Check permission
if ($request->user()->can('update', $group)) {
    // Authorized
}
```

**Benefits**:
- Centralized authorization logic
- Consistent permission checks
- Easy to test and maintain

### 2.3 Factory Pattern for Testing

All models have factories for test data:

```php
$event = Event::factory()
    ->has(EventQuestion::factory()->count(5))
    ->create();
```

**Details**: See [testing/README.md](testing/README.md)

---

## 3. Database Design

### 3.1 Entity Relationship Overview

```
User ─┬─ user_groups (pivot with is_captain)
      ├─ Submissions (1:many)
      └─ CaptainInvitations (1:many)

Event ─┬─ EventQuestions (1:many)
       ├─ Groups (1:many)
       ├─ CaptainInvitations (1:many)
       ├─ EventAnswers (1:many)
       └─ AmericaSaysGameState (1:1)

Group ─┬─ user_groups (pivot)
       ├─ GroupQuestions (1:many)
       ├─ GroupQuestionAnswers (1:many)
       ├─ Submissions (1:many)
       └─ Event (many:1)

EventQuestion ─┬─ GroupQuestions (1:many)
               ├─ EventAnswers (1:many)
               └─ QuestionTemplate (many:1)

GroupQuestion ─┬─ UserAnswers (1:many)
               ├─ GroupQuestionAnswers (1:many)
               └─ EventQuestion (many:1)

Submission ─┬─ UserAnswers (1:many)
            ├─ User (many:1)
            ├─ Group (many:1)
            └─ Event (many:1)
```

**Details**: See [technical/database-relationships.md](technical/database-relationships.md)

### 3.2 Key Design Decisions

#### Hybrid Authentication System
**Decision**: Password auth for admins/users + passwordless for guest captains

**Why**:
- Security for admins (password required)
- Zero friction for guests (no password needed)
- Email-based user recognition (returning captains auto-linked)
- Anonymous option (no email required)

**Details**: See [features/authentication.md](features/authentication.md)

#### 3-Tier Question Architecture
**Decision**: Question Templates → Event Questions → Group Questions

**Why**:
- Reusability (templates across events)
- Consistency (events use templates)
- Flexibility (captains customize per group)
- Captain empowerment

**Details**: See [features/question-system.md](features/question-system.md)

#### Dual Grading Model
**Decision**: Groups choose between captain or admin grading

**Why**:
- Flexibility (real-time vs post-event)
- Subjective vs objective questions
- Captain empowerment vs admin control
- Different groups, different needs

**Details**: See [features/grading-system.md](features/grading-system.md)

#### Per-Group Captain Role
**Decision**: Captains stored in user_groups pivot with is_captain boolean

**Why**:
- User can be captain of some groups, member of others
- Multiple captains per group with equal control
- No approval bottleneck (invitation link = instant captain)
- Matches real-world group dynamics

**Details**: See [features/captain-system.md](features/captain-system.md)

---

## 4. Frontend Architecture

### 4.1 Vue 3 Composition API

All components use Composition API:

```vue
<script setup>
import { ref, computed } from 'vue'

const score = ref(0)
const doubled = computed(() => score.value * 2)
</script>
```

**Why Composition API?**
- Better code organization
- Easier to reuse logic
- Better TypeScript support (even without TS)
- Modern Vue standard

### 4.2 Inertia.js Integration

Pages are Vue components rendered by Inertia:

```php
// Controller
return Inertia::render('Events/Show', [
    'event' => $event,
]);
```

```vue
<!-- Vue component -->
<script setup>
const props = defineProps({
  event: Object
})
</script>
```

**Details**: See [technical/frontend-patterns.md](technical/frontend-patterns.md)

### 4.3 PropOff Brand Colors

8-color system with semantic mapping:

- **PropOff Red** (#af1919) - Admin, errors, primary CTA
- **PropOff Orange** (#f47612) - Groups, captains, warnings
- **PropOff Green** (#57d025) - Success states (icons)
- **PropOff Dark Green** (#186916) - Success text, completed
- **PropOff Blue** (#1a3490) - Events, information

**Accessibility**: WCAG 2.1 AA compliant (all color combinations meet contrast requirements)

**Details**: See [technical/frontend-patterns.md](technical/frontend-patterns.md)

---

## 5. Key Architectural Decisions

### 5.1 Passwordless Guest Captain System

**Decision**: Allow guests to become captains without creating accounts

**Why**:
- Zero friction onboarding
- No password management overhead
- Email-based user recognition (automatic linking)
- Optional anonymous participation
- Automatic history continuity for returning captains

**How It Works**:
- Admin generates captain invitation link
- Guest clicks link → creates group → auto-logged in
- Provide email (optional) for history tracking
- Same email = same user (automatic linking)
- Guest captains have full permissions

**Details**: See [features/authentication.md](features/authentication.md)

### 5.2 Group-Specific Questions

**Decision**: Each group has own questions in group_questions table

**Why**:
- Supports captain customization per group
- Different groups can have different questions
- Captains control their experience
- Enables independent scoring

**Trade-off**: No cross-group leaderboards (different questions make comparison meaningless)

**Details**: See [features/question-system.md](features/question-system.md)

### 5.3 Weighted Scoring with Bonus Points

**Decision**: Base points + per-option bonus points

**Why**:
- Transparency (players see point values before answering)
- Flexibility (admins weight riskier options higher)
- Simplicity (no complex rules)
- Game-like (visible risk/reward)

**How It Works**:
- Question has base points (e.g., 5)
- Options have bonus points (e.g., +2, +0, +1)
- Correct answer: base + bonus
- Wrong answer: 0

**Details**: See [features/question-system.md](features/question-system.md)

### 5.4 Unified Dashboard with Role-Based Content

**Decision**: Single dashboard for all users with conditional sections

**Why**:
- Simplified navigation (one "Dashboard" link)
- Progressive disclosure (users see relevant features)
- Reduced complexity
- Better UX (everything in one place)

**Implementation**:
- Admin section (red gradient) at top for admins
- Captain section (blue gradient) for captains
- Base features for all users
- Role-based conditional rendering

### 5.5 Feature-Based Organization

**Decision**: Organize Vue components by feature (Groups) not role (Captain)

**Why**:
- Reduces confusion between Groups/ and Captain/Groups/
- Groups all related functionality together
- Captain is a role, not a feature
- Aligns with RESTful structure

**Implementation**:
- `Groups/Questions/` (captain manages questions)
- `Groups/Grading/` (captain grades)
- `Groups/Members/` (captain manages members)
- `Groups/Show.vue` adapts based on captain status

---

## 6. Project Structure

### 6.1 Laravel Structure (Key Folders)

```
app/
├── Http/
│   ├── Controllers/      # Inertia controllers (thin)
│   ├── Requests/         # Form validation
│   ├── Middleware/       # Custom middleware
│   └── Policies/         # Authorization rules
├── Models/               # Eloquent models
├── Services/             # Business logic (thick)
└── Notifications/        # Email notifications

database/
├── migrations/           # Database schema
├── seeders/             # Test data
└── factories/           # Model factories

resources/
├── js/
│   ├── Pages/           # Inertia Vue pages
│   ├── Components/      # Reusable components
│   ├── Layouts/         # Page layouts
│   └── Composables/     # Reusable Vue logic
└── css/
    └── app.css          # Tailwind imports
```

### 6.2 Controller → Service → Model Flow

```
Request
  ↓
Controller (validate, authorize)
  ↓
Service (business logic)
  ↓
Model (database)
  ↓
Response (Inertia)
```

**Example**:
```php
// Controller (thin)
public function store(StoreEventRequest $request)
{
    $event = Event::create($request->validated());
    return redirect()->route('events.show', $event);
}

// Service (thick business logic)
public function gradeSubmission(Submission $submission)
{
    // Complex grading logic here
}
```

---

## 7. Performance Optimization

### 7.1 Database
- Eager loading to prevent N+1 queries
- Indexes on foreign keys and frequently queried columns
- Materialized leaderboards for fast reads

### 7.2 Frontend
- Vite code splitting
- Lazy loading routes
- Tailwind CSS purging
- Inertia partial reloads

### 7.3 Caching
- Route caching in production
- Config caching in production
- View caching (automatic)

**Details**: See [technical/frontend-patterns.md](technical/frontend-patterns.md)

---

## 8. Security

### 8.1 Built-in Laravel Security
- CSRF protection (automatic with Inertia)
- SQL injection prevention (Eloquent ORM)
- XSS protection (Vue auto-escaping)
- Password hashing (bcrypt)
- Mass assignment protection

### 8.2 Authorization Strategy
- Laravel Policies for resource authorization
- Middleware for route protection
- Role-based access control (admin, user, guest)
- Per-group captain permissions

---

## 9. Testing Strategy

**515 tests across 67 test files** (98.6% pass rate)

### 9.1 Test Types
- **Unit Tests**: Models, Services, Policies (isolated)
- **Feature Tests**: Controllers, Workflows (integrated)

### 9.2 Key Testing Areas
- Dual grading system (captain vs admin)
- Captain permissions and invitations
- Scoring calculations (accuracy critical)
- Authorization (security critical)
- Database relationships

**Details**: See [testing/README.md](testing/README.md)

---

## 10. Development Workflow

### 10.1 Local Development
```bash
# Backend
php artisan serve

# Frontend
npm run dev

# Tests
php artisan test
```

### 10.2 Code Organization Principles
- Controllers stay thin (routing and responses)
- Services contain business logic
- Models handle data and relationships
- Policies handle authorization
- Requests handle validation

---

## 11. Documentation Map

For detailed technical information:

| Topic | Document |
|-------|----------|
| Database Schema | [technical/database-relationships.md](technical/database-relationships.md) |
| Scoring Logic | [technical/scoring-calculations.md](technical/scoring-calculations.md) |
| Frontend Patterns | [technical/frontend-patterns.md](technical/frontend-patterns.md) |
| Testing | [testing/README.md](testing/README.md) |

For feature details:

| Feature | Document |
|---------|----------|
| Captain System | [features/captain-system.md](features/captain-system.md) |
| Question System | [features/question-system.md](features/question-system.md) |
| Grading System | [features/grading-system.md](features/grading-system.md) |
| Authentication | [features/authentication.md](features/authentication.md) |
| Groups | [features/groups.md](features/groups.md) |
| Events | [features/events.md](features/events.md) |
| America Says | [features/america-says.md](features/america-says.md) |

---

**Version**: 2.0
**Last Updated**: 2025-12-15
**Author**: Claude Code (Anthropic)

**Note**: This document provides architectural overview. For implementation details, see the technical/ and features/ documentation folders.
