# Captain System

## Overview

PropOff's captain system is a revolutionary per-group role that empowers users to manage their own groups without requiring admin involvement. Captains can customize questions, grade submissions, manage members, and fully control their group's experience.

**Key Innovation**: Captains are **per-group roles**, not global user roles. A user can be a captain of some groups while being a regular member of others.

## Key Concepts

- **Captain**: A per-group role with full control over that group's questions, grading, and members
- **Captain Invitation**: A tokenized URL that allows anyone to become a captain by creating a group
- **Passwordless Captain**: Guest users who become captains without creating an account
- **Multi-Captain**: Groups can have multiple captains with equal permissions

## How It Works

### 1. Captain Role Storage

Captains are stored via an `is_captain` boolean in the `user_groups` pivot table:

```sql
user_groups:
  - user_id
  - group_id
  - is_captain (boolean)  -- TRUE for captains
  - joined_at
```

**Why per-group?**
- More flexible than global "captain" role
- Users can have different roles in different groups
- Multiple captains per group with equal control
- No approval process needed

### 2. Becoming a Captain

**Three Paths to Captain Status:**

1. **Create a Group via Invitation Link** (Most Common)
   - Admin generates captain invitation for an event
   - User clicks invitation link
   - User creates group and automatically becomes captain

2. **Create a Group from Dashboard** (Authenticated Users)
   - Logged-in user creates group from dashboard
   - Automatically becomes captain of that group

3. **Promoted by Existing Captain**
   - Current captain promotes group member to captain
   - Multiple captains can coexist

### 3. Captain Invitations

**Database Structure:**
```sql
captain_invitations:
  - token (unique 32-char string)
  - event_id
  - max_uses (nullable - unlimited if null)
  - times_used
  - expires_at (nullable - never expires if null)
  - is_active
```

**Invitation Features:**
- Admin generates invitations per event
- Copy-to-clipboard URL functionality
- Track usage count
- Set expiration dates
- Set maximum usage limits
- Deactivate/reactivate invitations

**URL Format:**
```
/captain/events/{event}/create-group/{token}
```

## Captain Permissions

Captains have **full control** over their groups:

### Question Management
- ✅ View all group questions
- ✅ Activate/deactivate inherited event questions
- ✅ Add custom questions (not from event)
- ✅ Modify question text and point values
- ✅ Reorder questions via drag-and-drop
- ✅ Delete custom questions

### Grading (If Captain Grading Mode)
- ✅ Set correct answers for all questions
- ✅ Void specific questions (no points awarded)
- ✅ Trigger score recalculation
- ✅ Real-time grading interface

### Member Management
- ✅ View all group members
- ✅ Add members (via join code)
- ✅ Remove members from group
- ✅ Promote members to captain
- ✅ Regenerate group join code
- ✅ Cannot demote the last captain (safety)

### Group Management
- ✅ Change grading source (Captain vs Admin)
- ✅ View group statistics
- ✅ Access group leaderboard
- ✅ Manage group settings

## Code Locations

### Models
- `app/Models/User.php` - captainGroups() relationship, isCaptainOf() helper
- `app/Models/Group.php` - captains() relationship, addCaptain(), removeCaptain()
- `app/Models/CaptainInvitation.php` - Invitation management

### Controllers
- `app/Http/Controllers/Captain/GroupController.php` - Group creation from invitations
- `app/Http/Controllers/GroupController.php` - Standard group operations
- `app/Http/Controllers/Groups/QuestionController.php` - Question management
- `app/Http/Controllers/Groups/GradingController.php` - Captain grading
- `app/Http/Controllers/Groups/MemberController.php` - Member management
- `app/Http/Controllers/Admin/CaptainInvitationController.php` - Admin invitation management

### Middleware
- `app/Http/Middleware/EnsureIsCaptainOfGroup.php` - Protects captain-only routes

### Policies
- `app/Policies/GroupPolicy.php` - Authorization for group actions

### Views
- `resources/js/Pages/Captain/CreateGroup.vue` - Group creation (guest + auth)
- `resources/js/Pages/Captain/Invitation.vue` - Invitation display
- `resources/js/Pages/Captain/InvitationExpired.vue` - Expired invitation
- `resources/js/Pages/Groups/Show.vue` - Unified group view (adapts for captains)
- `resources/js/Pages/Groups/Questions/Index.vue` - Question management
- `resources/js/Pages/Groups/Grading/Index.vue` - Grading interface
- `resources/js/Pages/Groups/Members/Index.vue` - Member management

## Workflows

### Captain Invitation Flow (Guest)

1. Admin generates captain invitation for event
2. Guest user clicks invitation link
3. Sees create group form (no login required)
4. Provides captain name (required) and email (optional)
5. System creates guest user with role='guest'
6. Auto-logs in guest user
7. Creates group and assigns as captain
8. Redirects to unified dashboard (shows captain section)

**Details:** See [authentication.md](authentication.md) for passwordless auth details.

### Captain Invitation Flow (Authenticated)

1. Logged-in user clicks captain invitation link
2. Sees create group form (pre-filled with user data)
3. Fills group details (name, description, grading source)
4. Creates group and automatically becomes captain
5. Redirects to unified dashboard

### Question Customization Workflow

1. Captain navigates to "Manage Questions" from group page
2. Sees list of inherited event questions
3. Can:
   - Toggle is_active to deactivate unwanted questions
   - Modify question text or point values
   - Reorder via drag-and-drop
   - Add custom questions not from event
4. Changes save immediately
5. Players see updated question list when entering

### Member Promotion Workflow

1. Captain navigates to "Manage Members"
2. Sees list of all group members with roles
3. Clicks "Promote to Captain" on member
4. Confirmation modal appears
5. Member gains captain permissions immediately
6. Both users now have equal captain control

## Design Decisions

### Why Per-Group Captain Role?

**Decision**: Store captains in `user_groups` pivot table with `is_captain` boolean instead of global role.

**Reasoning**:
- Flexibility - users can be captain of some groups, member of others
- No approval bottleneck - anyone with invitation link becomes captain
- Multiple captains per group with equal control
- Better matches real-world group dynamics
- No need for complex permission hierarchies

**Trade-off**: More complex permission checking, but worth it for flexibility.

### Why Allow Multiple Captains?

**Decision**: Groups can have unlimited captains with equal permissions.

**Reasoning**:
- Co-organizers can share responsibility
- No single point of failure if captain unavailable
- Easier group management for large groups
- Natural collaboration model

**Safety**: Last captain cannot be demoted (prevent orphaned groups).

### Why Passwordless Guest Captains?

**Decision**: Allow guests to become captains without creating accounts.

**Reasoning**:
- Zero friction onboarding
- No password management overhead
- Perfect for casual/one-time events
- Email optional for history tracking

**Details**: See [authentication.md](authentication.md).

## Common Patterns

### Check if User is Captain

```php
// In controller
if (!$user->isCaptainOf($group->id)) {
    abort(403, 'You must be a captain of this group');
}

// Using middleware
Route::middleware('captain.of.group')->group(function () {
    // Protected captain routes
});
```

### Get User's Captain Groups

```php
// Get all groups where user is captain
$captainGroups = $user->captainGroups;

// Check if user is captain of any group
$isCaptain = $user->captainGroups->isNotEmpty();

// Get groups where user is member but not captain
$memberGroups = $user->groups->diff($user->captainGroups);
```

### Promote User to Captain

```php
// In controller
$group->addCaptain($user->id);

// Or via pivot table update
$group->members()->updateExistingPivot($user->id, [
    'is_captain' => true
]);
```

## Integration with Other Features

- **Question System**: Captains customize group questions inherited from event questions
- **Grading System**: If captain grading mode, captains set correct answers
- **Authentication**: Guest captains use passwordless auth via invitation links
- **Dashboard**: Captain section appears on unified dashboard for all captains

**See Also:**
- [question-system.md](question-system.md) - 3-tier question architecture
- [grading-system.md](grading-system.md) - Captain vs Admin grading
- [authentication.md](authentication.md) - Passwordless guest captains
- [groups.md](groups.md) - Group management

## Gotchas

1. **Cannot Demote Last Captain**: Safety measure prevents groups from being orphaned
2. **Guest Captains Need Token**: Guest users can only access via invitation links
3. **Captain Status is Per-Group**: Don't confuse with global admin role
4. **Invitation Expiration**: Check canBeUsed() before allowing group creation
5. **Multiple Captains Have Equal Power**: No hierarchy among captains

## Future Enhancements

- Email captain token for future access (guests)
- Captain analytics dashboard
- Captain leaderboard across events
- Captain reputation system
- Bulk captain invitation generation
