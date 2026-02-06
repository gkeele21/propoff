# PropOff User Flow Specification

> Living document capturing the redesigned group experience for PropOff.
>
> **Mockup:** [PropOffUserFlow.html](PropOffUserFlow.html)

---

## Overview

The goal is to create a simple, intuitive flow where:
1. **Admins** create Events and set up questions
2. **Captains** create Groups for those Events and invite players
3. **Players** (including guests) can join, play, and see results with minimal friction

---

## Core Concepts

### Roles

| Role | Description |
|------|-------------|
| **Admin** | Creates Events, imports/adds questions, sends captain invitations |
| **Captain** | Creates a Group for an Event, manages questions/answers, invites players |
| **Player** | Joins a Group, answers questions, views leaderboard |
| **Guest** | A Player without a registered account - can still play and see results |

### Entity Relationships

```
Event (created by Admin)
  └── Group (created by Captain, belongs to Event)
        ├── GroupQuestions (copied from EventQuestions on group creation)
        ├── Members (users in the group, via user_groups pivot)
        ├── Entries (player submissions)
        └── Leaderboard (rankings)
```

---

## Decisions Made

### 1. Group Status Model (Simplified)

**Old:** Events had `draft`, `open`, `locked`, `completed` statuses

**New:** Groups are either **Locked** or **Not Locked**

- Captain can **set a lock time** on group creation → auto-locks at that time
- Captain can **manually toggle** the lock when ready
- No need for "finished" status - derive from event date if needed for historical queries

### 2. Kill "My Home" - Use Smart Routing

**Old:** Dashboard page showing all groups

**New:** No dedicated home page. After login, smart routing:

```
User logs in (or returns via cookie)
         │
         ▼
┌─────────────────────────┐
│ How many active groups? │
└─────────────────────────┘
         │
    ┌────┴────┬─────────┐
    │         │         │
   ZERO      ONE     MULTIPLE
    │         │         │
    ▼         ▼         ▼
"Join a    Go to     Group Picker
 Group"    that       page/modal
 prompt    group
```

### 3. History Page

- Accessed via **"History"** link in nav (visible to all users)
- Shows past events with scores, rankings, filters by year
- Summary stats: events played, wins, avg score, total points

### 4. Naming Consistency

| Element | Name |
|---------|------|
| Dashboard | ~~My Home~~ (removed) |
| Entry section | **My Entry** |
| History page | **History** |

### 5. Navigation Structure

**All users see:**
- History
- Profile dropdown

**When in a group context:**
- Group name appears in the nav bar (highlighted, with dropdown chevron)
- Clicking it returns to the Play Hub
- Dropdown allows switching between groups (for multi-group users)

**Admins/Captains also see:**
- Events
- Templates (manager only)
- Groups (manager only)

### 6. Sub-Page Navigation

Use **breadcrumbs** for navigation within group context:
- `Super Bowl Party / Play` - Answer questions page
- `Super Bowl Party / My Results` - View results page
- `Super Bowl Party / Grade Answers` - Captain grading page

Breadcrumbs link back to the Play Hub. Combined with the group name in the nav, users always have two ways back to the hub.

### 7. UI Consistency Rules

**Rank badges:** Use consistent `surface-inset` background with ordinal text (1st, 2nd, 3rd) everywhere - leaderboard, history, results summary. No colored medals or varying backgrounds.

**Points over percentages:** Display scores as points only. Avoid percentages as they can make players feel worse about their performance.

**Bonus points:** Display inline with the answer option text (e.g., "Kansas City Chiefs +5 bonus") matching the QuestionCard component pattern.

---

## Page Designs

### Group Hub (`/play/{code}` or `/groups/{id}`)

The unified group page that adapts based on role.

**Everyone sees:**
- Header: Group name, event name, date, member count, join code (in primary color)
- Stats row: Questions, Graded, Members, Total Points (4 tiles with colored top borders)
- My Entry card: Progress, score, "Continue Playing" / "Start Playing" button
- Game Status card: Open/Locked status, close time, question count
- Leaderboard: Top 5 with medals, "View Full" link, user's row highlighted

**Captains also see:**
- "Captain" badge in header
- "Edit" link in header
- "Captain Controls" button (orange) → opens modal
- "Share" button (blue)
- "Captain Graded" / "Admin Graded" badge

**Captain Controls Modal:**
- Manage section:
  - Questions (count, "Manage" link)
  - Grade Answers (graded count, "Grade" link)
  - Members (player/captain count, "Manage" link)
- Share section:
  - Copy Link button
  - QR Code button
  - Email button

### Answer Questions Page (`/play/{code}/play`)

- Breadcrumbs: `[Group Name] / Play`
- Header: Event name, progress meta (e.g., "8 of 10 answered")
- Question cards with:
  - Question text (no "Question #" label needed)
  - Points badge in top-right (e.g., "10 points")
  - Options with bonus points inline (e.g., "Kansas City Chiefs +5 bonus")
- Submit section at bottom with warning styling

### View Results Page (`/play/{code}/results`)

- Breadcrumbs: `[Group Name] / My Results`
- Results summary: Large rank (e.g., "3rd Place") and score (e.g., "68 points")
- Question cards showing:
  - Question text
  - Points earned badge in header (+10 pts success, 0 pts danger)
  - Options with correct/incorrect icons
  - No "Your pick" badge needed - the selected option is visually obvious

### Captain Grading Page (`/groups/{id}/grade`)

- Breadcrumbs: `[Group Name] / Grade Answers`
- **Info banner at top:** "Captain Grading Mode — Set the correct answers here as results come in. This is for grading your group's entries, not your personal predictions. To play, use the Play Hub."
- Stats row: Questions, Graded, Members, Max Possible
- Question cards with answer selection and void toggle

### Group Picker (`/groups/choose` or modal)

Shown when user has multiple active groups.

- Header: "Choose a Group"
- List of groups with:
  - Group name + Captain badge if applicable
  - Event name
  - Meta: member count, progress, rank
- Clicking a group → navigates to that group

### History Page (`/history`)

- Header: "History" with subtitle "Your past events and results"
- Filter pills: All, This Year, 2023, 2022, etc.
- **Stats row at top** (before the list):
  - Events Played (count)
  - Podium Finishes (stacked display: counts on top, "1st | 2nd | 3rd" labels below)
  - Avg from 1st (point differential, e.g., "-8.3 pts")
  - Avg Rank (e.g., "2.4")
- List of past events:
  - Event name + Group name
  - Date + player count
  - Rank badge (ordinal: 1st, 2nd, 3rd - consistent `surface-inset` background, same as leaderboard)
  - Score in points (no percentages)

**Note:** No event icons (sports emojis) since events can be named anything and we don't capture an icon field.

**Note:** Avoid percentages throughout the app - focus on points to keep players feeling positive.

### My Entry States

| State | Display |
|-------|---------|
| Not started + Open | "Not started yet" + **Start Playing** button |
| In progress + Open | "8 of 10 answered" + **Continue Playing** button |
| Submitted + Open | "Submitted" + score + **Edit Answers** button (if allowed) |
| Submitted + Locked | "Submitted" + score + rank + **View My Answers** button |
| Not started + Locked | "You didn't submit" (no action button) |

---

## Guest Flow

### The Problem

Guests need to:
1. Join without creating an account
2. Play the game
3. See leaderboard and their submission
4. Return later and still access their data

### Current Issues

- Guests lose context when navigating away
- Leaderboard requires `verified` middleware
- "Bookmark this link" banner didn't work in testing
- Re-joining via code creates duplicate accounts

### Solution: Cookie + Code Fallback

**The join code is the universal backup key.** Guests will always have it (from link, QR, or text).

#### Join Form UI

Simple form shown when guest isn't recognized:

```
┌─────────────────────────────────────────┐
│  Join [Group Name]                      │
│                                         │
│  What's your name?                      │
│  ┌─────────────────────────────────┐    │
│  │                                 │    │
│  └─────────────────────────────────┘    │
│                                         │
│  ┌─────────────────────────────────┐    │
│  │          Continue          →    │    │
│  └─────────────────────────────────┘    │
│                                         │
│  Already have an account? Log in        │
└─────────────────────────────────────────┘
```

**Note:** Button text should be neutral (not "Join Game") since returning users who lost their cookie would be hesitant thinking they're creating a duplicate entry. "Continue" or "Let's Go" works for both new and returning users.

#### Cookie Strategy

| What | Value |
|------|-------|
| Cookie name | `propoff_guest` |
| Cookie value | Guest token (32 chars) |
| Duration | 90 days |
| Scope | **Domain-wide** (one cookie works across all groups) |

This is a **session cookie** (not a tracking cookie) - no consent banner needed.

A guest with a domain-wide cookie can be in multiple groups. The cookie identifies WHO they are, and the group membership is tracked in the database.

#### Guest Return Flow

```
Guest arrives at /play/BOWL24
              │
              ▼
     ┌─────────────────┐
     │ Cookie exists?  │
     └─────────────────┘
          │         │
         YES        NO
          │         │
          ▼         ▼
   "Welcome back,   "What's your name?"
    Mike!"                │
          │               ▼
          │      ┌─────────────────┐
          │      │ Name exists in  │
          │      │ this group?     │
          │      └─────────────────┘
          │         │           │
          │        YES          NO
          │         │           │
          │         ▼           ▼
          │   "Is this you?"  Create new
          │   [Verify entry]   guest
          │         │
          ▼         ▼
   ┌───────────────────────┐
   │  Show Group Hub page  │
   │  (leaderboard, play)  │
   └───────────────────────┘
```

#### Three Ways Back In

| Method | How it works |
|--------|--------------|
| **Cookie** | Automatic - we recognize them |
| **URL** | `/play/BOWL24` - code is in the URL they already have |
| **Type code** | Fallback - enter code on homepage if cookie lost and URL forgotten |

#### Name Matching Logic

If no cookie found and guest enters a name:

```
Guest enters "Mike"
       │
       ▼
┌──────────────────────────┐
│ Does "Mike" exist in     │
│ this group?              │
└──────────────────────────┘
       │
   ┌───┴───┐
   │       │
  YES      NO
   │       │
   ▼       ▼
Ask for   Create guest
last      "Mike", set
initial   cookie, done
   │
   ▼
┌──────────────────────────┐
│ "What's your last        │
│  initial?"               │
│                          │
│  You'll appear as        │
│  "Mike T."               │
└──────────────────────────┘
   │
   ▼
┌──────────────────────────┐
│ Does "Mike T." exist?    │
└──────────────────────────┘
   │
   ┌───┴───┐
   │       │
  YES      NO
   │       │
   ▼       ▼
Show      Create guest
verify:   "Mike T.",
"8/10     set cookie,
answered, done
is this
you?"
```

**Key decisions:**
- Ask for last initial **immediately** if name exists (don't show verification first)
- This handles the "two Mikes" edge case automatically
- Verification only shown after name + initial matches an existing entry
- Verification shows just progress (e.g., "8/10 answered") - keep it simple

---

## Routes (Proposed)

### Public/Guest Routes

```
GET  /play/{code}              → Group hub (smart: join or resume)
GET  /play/{code}/join         → Join form (if not recognized)
POST /play/{code}/join         → Process join
GET  /play/{code}/leaderboard  → Full leaderboard
GET  /play/{code}/my-entry     → View own submission
```

### Authenticated Routes

```
GET  /groups/choose            → Group picker (multiple active groups)
GET  /groups/{id}              → Group hub (by ID for captains)
GET  /groups/{id}/manage       → Captain: manage questions
GET  /groups/{id}/grade        → Captain: set answers
GET  /groups/{id}/members      → Captain: manage members
GET  /history                  → Past events/results
```

### Entry Routes

```
POST /play/{code}/start        → Start entry
POST /play/{code}/save         → Save answers (auto-save)
POST /play/{code}/submit       → Submit entry
```

---

### Guest Edge Cases

| Scenario | How it's handled |
|----------|------------------|
| **Multiple devices** | Name matching works across devices - guest re-enters name on new device |
| **Clears cookies mid-game** | Name matching recovers session |
| **Loses cookie + forgets URL** | Can type code on homepage, then name matching |
| **Accidentally joins twice** | Name matching catches duplicate, prompts "Is this you?" |

### Guest → Registered User Conversion

**Approach:** Quiet link, not pushy.

**When shown:** Only when:
- Game is locked
- User is a guest
- User has submitted their entry

**Placement:** Bottom of "My Entry" card:
```
┌─────────────────────────────────────────┐
│  MY ENTRY                               │
│  ────────────────                       │
│  ✓ Submitted                            │
│  Score: 68 pts                          │
│  Rank: 3rd of 12                        │
│                                         │
│  [View My Answers →]                    │
│                                         │
│  ─────────────────────────────────────  │
│  Want to save your results?             │
│  Create an account →                    │
└─────────────────────────────────────────┘
```

**On conversion:**
- Guest record upgraded (same user ID, add email/password)
- All entries/history preserved
- Cookie replaced with standard session

---

## Still To Discuss

### Captain Features
- [ ] Lock toggle vs scheduled lock time
- [ ] Promoting other captains
- [ ] Member management (remove, ban)

### Notifications
- [ ] Email when game is about to lock?
- [ ] Push notifications?

### Other Edge Cases
- [ ] Captain leaves their own group
- [ ] What happens to group if all captains leave?

---

## Mockups

All mockups are in `/docs/specs/`:

- **PropOffUserFlow.html** - Interactive mockup with all pages:
  - Play Hub (player + captain views)
  - Answer Questions (Play)
  - View Results
  - Captain Grading
  - History
  - Group Picker

To view: `open docs/specs/PropOffUserFlow.html`

---

## Implementation Status

> **Last Updated**: 2026-02-06

### Fully Implemented ✅

| Feature | Notes |
|---------|-------|
| **Play Hub** | `/play/{code}` - Full hub with stats, entry card, leaderboard preview |
| **Smart Routing** | 0 groups → homepage, 1 group → play hub, multiple → chooser |
| **Guest Cookie System** | `propoff_guest` cookie, 90-day duration, domain-wide |
| **Name Matching** | With last initial logic for duplicate handling |
| **Guest Verification** | "Is this you?" flow when name matches existing entry |
| **Lock/Unlock Toggle** | Manual toggle via `entry_cutoff` field |
| **Breadcrumbs Navigation** | Via PageHeader component on all play pages |
| **History Page** | With year filtering and stats row |
| **Leaderboard** | Both preview (top 5) and full view |
| **Captain Controls** | Full question, grading, member management |
| **Group Picker** | `/groups/choose` for multi-group users |
| **Points Display** | Points only, no percentages |

### Partial / Different Implementation ⚠️

| Feature | Spec | Actual Implementation |
|---------|------|----------------------|
| **Scheduled Locks** | "Captain can set a lock time on group creation" | Manual toggle only - no pre-scheduled lock times |
| **Nav Group Dropdown** | "Group name in nav with dropdown chevron for switching" | Uses breadcrumbs instead; group switching via `/groups/choose` |
| **Dual Guest Flows** | Single unified flow | Two flows coexist: GuestController (EventInvitation-based) and PlayController (Play Hub flow). PlayController is the primary flow. |

### Route Mapping

| Spec Route | Actual Route | Status |
|------------|--------------|--------|
| `/play/{code}` | `/play/{code}` | ✅ Implemented |
| `/play/{code}/join` | `/play/{code}/join` | ✅ Implemented |
| `/play/{code}/play` | `/play/{code}/play` | ✅ Implemented |
| `/play/{code}/leaderboard` | `/play/{code}/leaderboard` | ✅ Implemented |
| `/play/{code}/my-entry` | `/play/{code}/results` | ✅ Renamed to "results" |
| `/groups/choose` | `/groups/choose` | ✅ Implemented |
| `/groups/{id}/grade` | `/groups/{group}/grading` | ✅ Slightly different naming |
| `/groups/{id}/members` | `/groups/{group}/members` | ✅ Implemented |
| `/history` | `/history` | ✅ Implemented |

### Database Implementation Notes

**Lock Mechanism**: Uses `entry_cutoff` timestamp field instead of boolean `is_locked`:
- `entry_cutoff = null` → Group is unlocked
- `entry_cutoff <= now()` → Group is locked
- `is_locked` is a computed property from this field

**Guest Players vs Guest Captains**:
- Guest players: Created via Play Hub join flow, stored with `role='guest'`, identified by `propoff_guest` cookie
- Guest captains: Created via Captain Invitation flow, same database structure, but accessed via different entry point

---

## Changelog

| Date | Changes |
|------|---------|
| 2024-02-05 | Initial spec created from conversation |
| 2026-02-05 | Added UI consistency rules, navigation patterns, sub-page designs, history stats layout |
| 2026-02-05 | Renamed from group-flow-spec.md to PropOffUserFlow.md, consolidated mockups |
| 2026-02-06 | Added Implementation Status section documenting actual state vs spec |

