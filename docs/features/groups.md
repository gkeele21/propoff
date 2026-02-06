# Groups

## Overview

Groups are the primary organizational unit in PropOff. Each group participates in events independently with their own questions, answers, and leaderboards. The primary user experience centers around the **Play Hub** - a unified page where users view group info, submit answers, and see leaderboards.

## Key Concepts

- **Group**: Collection of users participating together in events
- **Join Code**: Unique 8-character code for users to join groups (e.g., "BOWL2024")
- **Captain**: Per-group role with full management permissions (see [captain-system.md](captain-system.md))
- **Member**: Regular user or guest in a group
- **Grading Source**: Whether group uses captain or admin grading
- **Play Hub**: The central page for all group interaction (`/play/{code}`)
- **Lock Status**: Whether entries are still being accepted (controlled by `entry_cutoff`)

## Database Schema

```sql
groups:
  - name
  - code (unique)
  - description
  - event_id
  - grading_source ('captain' or 'admin')
  - created_by (user_id of creator)
  - entry_cutoff (timestamp, nullable - null means unlocked)

user_groups (pivot):
  - user_id
  - group_id
  - is_captain (boolean)
  - joined_at
```

## Smart Routing

When authenticated users navigate to `/play`, the **SmartRoutingService** determines where to send them:

```
User goes to /play
       │
       ▼
┌─────────────────────────┐
│ How many active groups? │
└─────────────────────────┘
       │
   ┌───┴───┬─────────┐
   │       │         │
  ZERO    ONE    MULTIPLE
   │       │         │
   ▼       ▼         ▼
Homepage  Play Hub   Group
   /       for that   Picker
          group      /groups/choose
```

**Active Group Definition**: Event status in `['draft', 'open', 'locked']` OR event date within last 7 days.

## Play Hub (`/play/{code}`)

The Play Hub is the unified interface for group participation. It adapts based on user role and group state.

### Everyone Sees
- **Header**: Group name, event name, date, member count, join code
- **Stats Row**: Questions, Graded, Members, Total Points (4 tiles)
- **My Entry Card**: Progress, score, action button (Start/Continue/View)
- **Game Status**: Open/Locked status, close time
- **Leaderboard**: Top 5 with ranks, "View Full" link, user's row highlighted

### Captains Also See
- "Captain" badge in header
- "Edit" link in header
- "Captain Controls" section with links to:
  - Manage Questions (`/groups/{id}/questions`)
  - Grade Answers (`/groups/{id}/grading`)
  - Manage Members (`/groups/{id}/members`)
- "Share" button for invitation link

### My Entry States

| State | Display |
|-------|---------|
| Not started + Unlocked | "Not started yet" + **Start Playing** button |
| In progress + Unlocked | "8 of 10 answered" + **Continue Playing** button |
| Submitted + Unlocked | "Submitted" + score + **Edit Answers** button |
| Submitted + Locked | "Submitted" + score + rank + **View My Answers** button |
| Not started + Locked | "You didn't submit" (no action button) |

## Lock Mechanism

Groups use an `entry_cutoff` timestamp to control when entries are locked:

- `entry_cutoff = NULL` → Group is **unlocked**, entries accepted
- `entry_cutoff <= now()` → Group is **locked**, no new entries

**Computed Property**: `$group->is_locked` returns `true` if `entry_cutoff` is set and in the past.

**Captain Controls**:
- Toggle lock via "Lock/Unlock Entries" button
- Locking sets `entry_cutoff` to current timestamp
- Unlocking clears `entry_cutoff` to `NULL`

**Note**: Pre-scheduled lock times (setting future `entry_cutoff` on group creation) are not yet implemented. Captains must manually toggle when ready.

## Creating Groups

### Via Captain Invitation

1. Click captain invitation link
2. Fill group details
3. Become captain automatically
4. See [captain-system.md](captain-system.md)

### Via Dashboard

1. Navigate to "Create Group"
2. Select event
3. Fill: name, description, grading source
4. Become captain of new group

## Joining Groups

### Authenticated Users

**Via Join Code**:
```
1. Navigate to "Join Group" or enter code on homepage
2. Enter group join code
3. Become member (not captain)
```

### Guest Players (No Account Required)

Guests can join and play without creating an account using the **Play Hub join flow**:

```
Guest visits /play/{code}
        │
        ▼
┌─────────────────┐
│ Cookie exists?  │
└─────────────────┘
     │         │
    YES        NO
     │         │
     ▼         ▼
"Welcome     Show Join Form:
 back!"      "What's your name?"
     │              │
     │              ▼
     │       ┌─────────────────┐
     │       │ Name exists in  │
     │       │ this group?     │
     │       └─────────────────┘
     │          │         │
     │         YES        NO
     │          │         │
     │          ▼         ▼
     │   Ask for last   Create guest,
     │   initial, then  set cookie,
     │   verify entry   go to hub
     │          │
     ▼          ▼
   Go to Play Hub
```

**Cookie System**:
- Cookie name: `propoff_guest`
- Duration: 90 days
- Scope: Domain-wide (works across all groups)
- Value: Guest's unique token (32 chars)

**Name Matching** (prevents duplicate entries):
1. Guest enters name (e.g., "Mike")
2. If name exists in group → ask for last initial
3. If "Mike T." exists → show verification ("Is this you? 8/10 answered")
4. If verified → link to existing entry
5. If not verified → create new guest

**Three Ways Back In**:
| Method | How it works |
|--------|--------------|
| **Cookie** | Automatic recognition |
| **URL** | `/play/{code}` - code is in their link |
| **Type code** | Enter code on homepage if cookie lost |

**Auto-Generated Codes**: 8-character random strings (e.g., "BOWL2024")

## Group Features

### For All Members
- View group leaderboard
- Submit answers to group questions
- See group members
- Leave group

### For Captains Only
- Manage questions
- Grade submissions (if captain grading)
- Manage members (add/remove/promote)
- Change grading source
- Regenerate join code

See [captain-system.md](captain-system.md) for captain details.

## Code Locations

### Models
- `app/Models/Group.php` - Group model with `is_locked` computed property

### Controllers
- `app/Http/Controllers/GroupController.php` - Standard group CRUD
- `app/Http/Controllers/PlayController.php` - Play Hub flow (hub, join, play, results)
- `app/Http/Controllers/GroupPickerController.php` - Multi-group chooser
- `app/Http/Controllers/HistoryController.php` - Past events/results
- `app/Http/Controllers/Captain/*.php` - Captain-specific actions

### Services
- `app/Services/SmartRoutingService.php` - Determines redirect for authenticated users

### Middleware
- `app/Http/Middleware/GuestCookieMiddleware.php` - Auto-recognizes returning guests

### Views
- `resources/js/Pages/Play/Hub.vue` - Main play hub
- `resources/js/Pages/Play/Join.vue` - Guest join form (3-step flow)
- `resources/js/Pages/Play/Questions.vue` - Answer questions
- `resources/js/Pages/Play/Results.vue` - View submitted answers
- `resources/js/Pages/Play/Leaderboard.vue` - Full leaderboard
- `resources/js/Pages/Groups/Choose.vue` - Group picker for multi-group users
- `resources/js/Pages/Groups/Show.vue` - Group detail (captain view)
- `resources/js/Pages/Groups/Index.vue` - List user's groups
- `resources/js/Pages/History.vue` - Past events with stats

**See Also:**
- [captain-system.md](captain-system.md) - Captain roles and permissions
- [authentication.md](authentication.md) - Guest player authentication
- [../specs/PropOffUserFlow.md](../specs/PropOffUserFlow.md) - Full user flow specification
