# PropOff - Requirements Overview

## 1. Project Overview

### 1.1 Purpose
Web-based prediction/guessing event platform where users submit answers to questions about events. Features a revolutionary captain system with 3-tier question architecture, dual grading modes, and hybrid authentication.

### 1.2 Tech Stack
- **Backend**: Laravel 10.49.1 (PHP 8.2+)
- **Frontend**: Vue 3 (Composition API, JavaScript)
- **Bridge**: Inertia.js v0.6.11
- **Database**: MySQL 8.0+
- **Styling**: Tailwind CSS + PropOff brand colors
- **Authentication**: Laravel Breeze (password + passwordless)

### 1.3 Target Users
- System administrators (global access)
- Regular users (password accounts)
- Guest captains (passwordless access)
- Group members (participants)

---

## 2. Core Features

This section provides a high-level overview. For detailed documentation, see the [features/](features/) folder.

### 2.1 Authentication System
Hybrid authentication with three user types.

**Key Capabilities**:
- Traditional password auth for admins/users
- Passwordless auth for guest captains
- Email-based user recognition (automatic linking)
- Anonymous participation option

**Details**: See [authentication.md](features/authentication.md)

### 2.2 Captain System
Per-group role with full management permissions.

**Key Capabilities**:
- Captain invitations (tokenized URLs)
- Per-group captain role (not global)
- Multiple captains per group
- Passwordless guest captains
- Full question/grading/member control

**Details**: See [captain-system.md](features/captain-system.md)

### 2.3 Question System
3-tier architecture with captain customization.

**Primary Architecture**:
- Question Templates (reusable with variables)
- Event Questions (concrete per event)
- Group Questions (captain customizable)

**Question Types**:
- Multiple choice (with bonus points)
- Yes/No
- Numeric
- Short text

**Details**: See [question-system.md](features/question-system.md)

### 2.4 Grading System
Dual grading model for flexibility.

**Two Modes**:
- **Captain Grading**: Real-time, captain-set answers
- **Admin Grading**: Post-event, admin-set answers

**Features**:
- Type-aware answer comparison
- Weighted scoring (base + bonus points)
- Question voiding
- Automatic score recalculation

**Details**: See [grading-system.md](features/grading-system.md)

### 2.5 Group Management
Groups participate independently in events.

**Key Features**:
- Unique join codes
- Group-specific questions
- Independent grading source
- Per-group leaderboards
- Member management

**Details**: See [groups.md](features/groups.md)

### 2.6 Event Management
Two event types with different workflows.

**Event Types**:
- **GameQuiz**: Traditional prediction questions
- **AmericaSays**: Live game show format

**Event Status Flow**:
Draft → Open → Locked → In Progress → Completed

**Details**: See [events.md](features/events.md) and [america-says.md](features/america-says.md)

---

## 3. User Roles & Permissions

### Admin (Global Superuser)
**Full system access**:
- Create/manage events and templates
- Generate captain invitations
- Set event-level answers (admin grading)
- Manage users and groups
- Access all features

### Captain (Per-Group Role)
**Limited to their groups**:
- Manage group questions
- Grade submissions (if captain grading)
- Manage group members
- Change grading source
- Generate join codes

### Regular User
- Register for events
- Submit answers
- View group leaderboards
- Join groups
- Update profile

### Guest User
- Become captain via invitation
- Full captain permissions
- Optional email (for history tracking)
- Anonymous participation option

---

## 4. Key Requirements Summary

### Functional Must-Haves
- ✅ Hybrid authentication (password + passwordless)
- ✅ Captain system with invitations
- ✅ 3-tier question architecture
- ✅ Dual grading system
- ✅ Group-specific questions
- ✅ Weighted scoring with bonus points
- ✅ Type-aware answer comparison
- ✅ Multiple event types (GameQuiz, AmericaSays)
- ✅ Mobile-responsive design

### Non-Functional Requirements
- **Performance**: Page load < 2 seconds
- **Scalability**: Support 100+ concurrent users
- **Security**: Role-based access, CSRF protection, password hashing
- **Usability**: Mobile-first, zero-friction captain onboarding
- **Reliability**: 99% uptime target
- **Accessibility**: WCAG 2.1 AA compliant

---

## 5. Technical Requirements

### Server Requirements
- PHP 8.2+
- MySQL 8.0+
- Composer
- Node.js 18+ (for frontend build)

### Browser Support
Modern browsers: Chrome, Firefox, Safari, Edge (latest versions)

---

## 6. Success Criteria

- ✅ 515 tests passing (98.6% pass rate)
- ✅ Zero-friction captain onboarding
- ✅ Score entry time under 2 minutes
- ✅ Mobile usage accounts for 50%+ of interactions
- ✅ Page load times under 2 seconds
- ✅ Email-based user recognition working
- ✅ Dual grading system fully functional

---

## 7. Documentation Map

For detailed information about specific features:

| Topic | Document |
|-------|----------|
| Captain System | [captain-system.md](features/captain-system.md) |
| Question System | [question-system.md](features/question-system.md) |
| Grading System | [grading-system.md](features/grading-system.md) |
| Authentication | [authentication.md](features/authentication.md) |
| Groups | [groups.md](features/groups.md) |
| Events | [events.md](features/events.md) |
| America Says | [america-says.md](features/america-says.md) |
| Database Schema | [technical/database-relationships.md](technical/database-relationships.md) |
| Scoring Logic | [technical/scoring-calculations.md](technical/scoring-calculations.md) |
| Frontend Patterns | [technical/frontend-patterns.md](technical/frontend-patterns.md) |
| Testing | [testing/README.md](testing/README.md) |

---

## 8. Key Innovations

PropOff's unique features that set it apart:

1. **Hybrid Authentication**: Password for admins + passwordless for guests in single system
2. **Email-Based User Recognition**: Returning captains automatically linked by email
3. **Per-Group Captain Role**: Not global - users can be captain of some groups, member of others
4. **3-Tier Question Architecture**: Templates → Event → Group (captain customizable)
5. **Dual Grading Model**: Groups choose captain or admin grading independently
6. **Weighted Bonus Points**: Transparent risk/reward visible before answering
7. **Group-Specific Questions**: Different groups can have different questions for same event
8. **Passwordless Captain Invitations**: Zero-friction onboarding via tokenized URLs

---

## 9. Implementation Status

### ✅ Completed (100% of MVP)

**Phase 0-9**: All core features implemented
- Hybrid authentication system
- Captain system with invitations
- 3-tier question architecture
- Dual grading system
- Weighted scoring
- Event management (GameQuiz + AmericaSays)
- Group management
- Member management
- Leaderboards (per-group only)
- Unified dashboard with role-based content
- Complete PropOff brand color system

**Testing**: 515 tests, 98.6% pass rate

### ⏳ Planned Features

**Phase 10+**: Future enhancements
- Email notifications
- Guest token access links
- Convert guest to full user
- Performance monitoring
- Analytics dashboard
- Badge/reward system

---

## 10. Major Design Decisions

### Why Hybrid Authentication?

**Decision**: Support both password and passwordless in single system

**Reasoning**:
- Security for admins (password required)
- Zero friction for guests (no password)
- Email-based linking for continuity
- Anonymous option for privacy
- Flexible engagement levels

### Why Per-Group Captains?

**Decision**: Captain role is per-group, not global

**Reasoning**:
- Users can be captain of some groups, member of others
- Multiple captains per group (no hierarchy)
- No approval bottleneck (invitation = instant captain)
- Matches real-world dynamics

### Why 3-Tier Questions?

**Decision**: Templates → Event Questions → Group Questions

**Reasoning**:
- Reusability (templates across events)
- Consistency (events use templates)
- Flexibility (captains customize per group)
- Captain empowerment

**Trade-off**: More storage, no cross-group leaderboards

### Why Dual Grading?

**Decision**: Groups choose captain or admin grading

**Reasoning**:
- Flexibility (real-time vs post-event)
- Subjective vs objective questions
- Trust and control options
- Different groups have different needs

---

**Version**: 2.0
**Last Updated**: 2025-12-15
**Note**: This document provides high-level requirements. For detailed specifications, see the feature-specific documentation in the `features/` folder.
