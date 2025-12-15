# Groups

## Overview

Groups are the primary organizational unit in PropOff. Each group participates in events independently with their own questions, answers, and leaderboards.

## Key Concepts

- **Group**: Collection of users participating together in events
- **Join Code**: Unique code for users to join groups
- **Captain**: Per-group role with full management permissions (see [captain-system.md](captain-system.md))
- **Member**: Regular user in a group
- **Grading Source**: Whether group uses captain or admin grading

## Database Schema

```sql
groups:
  - name
  - code (unique)
  - description
  - event_id
  - grading_source ('captain' or 'admin')
  - created_by (user_id of creator)

user_groups (pivot):
  - user_id
  - group_id
  - is_captain (boolean)
  - joined_at
```

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

**Via Join Code**:
```
1. Navigate to "Join Group"
2. Enter group join code
3. Become member (not captain)
```

**Auto-Generated Codes**: 8-character random strings (e.g., "ABC12XYZ")

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

- `app/Models/Group.php`
- `app/Http/Controllers/GroupController.php`
- `resources/js/Pages/Groups/Show.vue`
- `resources/js/Pages/Groups/Index.vue`
