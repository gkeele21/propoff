# Authentication System

## Overview

PropOff features a unique **Hybrid Authentication System** that combines traditional password-based authentication for admins and regular users with passwordless authentication for guest captains. This provides both security and zero-friction onboarding.

**Key Innovation**: Three user types with different authentication methods in a single unified system.

## User Types

| User Type | Password? | Email? | Access Method | Primary Use Case |
|-----------|-----------|--------|---------------|------------------|
| **Admin** | ✅ Required | ✅ Required | `/login` page | Site administrators |
| **Regular User** | ✅ Required | ✅ Required | `/login` or `/register` | Users who want permanent accounts |
| **Guest Captain** | ❌ No password | ⚠️ Optional | Captain invitation link | Event captains (passwordless) |
| **Guest Player** | ❌ No password | ❌ None | Play Hub join flow | Players who just want to participate |

## Traditional Authentication (Admins & Regular Users)

**Technology**: Laravel Breeze + Sanctum

**Features**:
- Email + password registration
- Email + password login
- Email verification
- Password reset via email
- Remember me functionality
- Profile management
- Password strength requirements (min 8 chars)

**Database**:
```sql
users:
  - email (unique, not null)
  - password (hashed, not null)
  - role (admin, user)
  - email_verified_at
```

**Security**:
- Bcrypt password hashing
- CSRF protection
- Session-based authentication
- Email verification enforced

## Passwordless Authentication (Guest Captains)

**Purpose**: Allow anyone to become a captain without creating an account.

### How It Works

**Guest User Creation Flow**:

1. Admin generates captain invitation link
2. Guest user clicks link (not logged in)
3. Sees create group form
4. Provides:
   - Captain name (required)
   - Captain email (optional)
5. System checks if email exists:
   - **If email provided & exists**: Link to existing user account
   - **If email provided & new**: Create new guest user with email
   - **If email not provided**: Create anonymous guest user
6. Auto-login guest user
7. Create group and assign as captain

**Database**:
```sql
users:
  - email (nullable for guests)
  - password (null for guests)
  - role ('guest')
  - guest_token (unique 32-char string)
```

### Email-Based User Recognition

**Key Feature**: Returning captains automatically recognized by email.

**Workflow**:
```php
// Controller logic
if (!$request->user()) {
    if ($request->captain_email) {
        // Check for existing user with this email
        $user = User::where('email', $request->captain_email)->first();

        if ($user) {
            // Existing user found - link to account
            Auth::login($user);
        } else {
            // New email - create guest user
            $user = User::create([
                'name' => $request->captain_name,
                'email' => $request->captain_email,
                'password' => null,
                'role' => 'guest',
                'guest_token' => Str::random(32),
            ]);
            Auth::login($user);
        }
    } else {
        // No email - create anonymous guest
        $user = User::create([
            'name' => $request->captain_name,
            'email' => null,
            'password' => null,
            'role' => 'guest',
            'guest_token' => Str::random(32),
        ]);
        Auth::login($user);
    }
}
```

**Benefits**:
- Returning captains use same account across events
- History tracking for captains who provide email
- Anonymous option for one-time participation
- No password management overhead

### Anonymous vs Email-Tracked Guests

**Anonymous (No Email)**:
- ✅ Zero friction - just provide name
- ✅ Perfect for one-time events
- ❌ No history across events
- ❌ Cannot retrieve account later

**Email-Tracked (With Email)**:
- ✅ History continuity across events
- ✅ Can be contacted via email
- ✅ Automatic account linking on return
- ⚠️ Slight friction (must provide email)

### Guest Access Restrictions

Guest users can only access app via invitation links:
- ❌ Cannot login at `/login` page (no password)
- ❌ Cannot reset password (no password to reset)
- ✅ Can access via captain invitation links
- ✅ Can become captains with full permissions
- ✅ Can participate in events
- ⏳ Can upgrade to full account (future feature)

## Guest Player Authentication (Play Hub Flow)

**Purpose**: Allow anyone to play in a group without creating an account.

### Cookie-Based Recognition

Guest players are recognized via a persistent cookie:

| Property | Value |
|----------|-------|
| **Cookie Name** | `propoff_guest` |
| **Cookie Value** | Guest's unique token (32 chars) |
| **Duration** | 90 days |
| **Scope** | Domain-wide (works across all groups) |

**Type**: This is a session cookie (not a tracking cookie) - no consent banner needed.

### Guest Player Join Flow

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
Auto-login   Show Join Form
via token    with name input
     │              │
     │              ▼
     │       Name matching
     │       (see below)
     │              │
     ▼              ▼
   Play Hub with entry access
```

### Name Matching Logic

Prevents duplicate entries when guest loses cookie:

1. Guest enters name (e.g., "Mike")
2. If no "Mike" in group → create guest, set cookie, done
3. If "Mike" exists → ask for last initial
4. Guest enters initial (e.g., "T")
5. If "Mike T." exists → show verification ("8/10 answered - is this you?")
6. If verified → link to existing guest (set cookie)
7. If not verified → create "Mike T." as new guest

**Key Decision**: Ask for last initial immediately if name exists (don't show verification first). This handles "two Mikes" edge case automatically.

### Three Ways Back In

| Method | How It Works |
|--------|--------------|
| **Cookie** | Automatic - guest recognized on return |
| **URL** | `/play/{code}` - the join code is in the URL |
| **Type Code** | Enter code on homepage if cookie lost and URL forgotten |

### Database Structure

Guest players use the same `users` table:
```sql
users:
  - name (display name, e.g., "Mike T.")
  - email (null for guest players)
  - password (null)
  - role ('guest')
  - guest_token (unique 32-char string, stored in cookie)
```

### Guest Player vs Guest Captain

| Aspect | Guest Player | Guest Captain |
|--------|--------------|---------------|
| **Entry Point** | Play Hub (`/play/{code}`) | Captain Invitation link |
| **Email** | Never collected | Optional |
| **Recognition** | Cookie-based | Email linking (if provided) |
| **Permissions** | Play, view leaderboard | Full captain control |
| **Use Case** | Quick participation | Group management |

### Code Locations

- `app/Http/Controllers/PlayController.php` - Join flow, cookie handling
- `app/Http/Middleware/GuestCookieMiddleware.php` - Auto-login via cookie
- `resources/js/Pages/Play/Join.vue` - 3-step join form UI

## Guest Captain Permissions

**Full Parity**: Guest captains have identical permissions to regular captains.

Guests can:
- ✅ Create groups
- ✅ Manage group questions
- ✅ Grade submissions (if captain grading)
- ✅ Manage members
- ✅ View leaderboards
- ✅ Participate in events

Guests cannot:
- ❌ Login via `/login` page
- ❌ Reset password
- ❌ Access admin features (even if promoted - require password first)

## Code Locations

### Controllers
- `app/Http/Controllers/Captain/GroupController.php` - Guest captain creation
- `app/Http/Controllers/Auth/*` - Traditional auth (Breeze)

### Middleware
- `app/Http/Middleware/Authenticate.php` - Auth check
- `app/Http/Middleware/EnsureEmailIsVerified.php` - Email verification (traditional only)

### Views
- `resources/js/Pages/Captain/CreateGroup.vue` - Dual flow (guest + auth)
- `resources/js/Pages/Auth/*` - Traditional auth pages (Breeze)
- `resources/js/Layouts/GuestLayout.vue` - Layout for guest users
- `resources/js/Layouts/AuthenticatedLayout.vue` - Layout for authenticated users

## Workflows

### Traditional User Registration

1. Navigate to `/register`
2. Provide: name, email, password, password confirmation
3. Click "Register"
4. Email verification sent
5. Verify email
6. Login and access dashboard

### Guest Captain Creation (First Time)

1. Click captain invitation link
2. See create group form (no login redirect)
3. Provide: captain name (required), captain email (optional)
4. Fill group details
5. Submit form
6. Guest user auto-created
7. Auto-logged in
8. Group created, assigned as captain
9. Redirect to dashboard (shows captain section)

### Returning Guest Captain (Same Email)

1. Click new captain invitation link for different event
2. Provide same email as before
3. System recognizes email → links to existing account
4. Auto-logged in as existing user
5. Create new group for new event
6. All previous history maintained

### Anonymous Guest Captain

1. Click captain invitation link
2. Provide name only (skip email)
3. Anonymous guest user created
4. Auto-logged in
5. Create group
6. No history tracking (new user each time)

## Design Decisions

### Why Hybrid Authentication?

**Decision**: Support both password and passwordless auth in single system.

**Reasoning**:
- **Security for admins**: Admins need strong authentication
- **Security for regular users**: Users want permanent accounts
- **Zero friction for guests**: Remove barriers for captains
- **Flexibility**: Users choose their engagement level

**Trade-off**: More complex auth logic, but worth it for UX.

### Why Email-Based Linking?

**Decision**: Intentionally link users by email (no uniqueness check).

**Reasoning**:
- Supports returning captain continuity
- Simple mental model: "Use same email = same account"
- No password sharing risk (guests have no password)
- User controls their identity via email

**Security Consideration**:
- Not a vulnerability - guests have no privileged access by default
- Only become captains by creating/being promoted to groups
- Email linking is intentional feature, not bug

### Why Allow Anonymous Guests?

**Decision**: Email optional for guest captains.

**Reasoning**:
- Maximum flexibility
- Perfect for casual/one-time events
- Some users prefer privacy
- No forced data collection

**Trade-off**: Anonymous users create new account each time (no history).

## Common Patterns

### Check User Type

```php
// In controller
if ($user->role === 'guest') {
    // Guest user - handle accordingly
} elseif ($user->role === 'admin') {
    // Admin user
} else {
    // Regular user
}
```

### Conditional Form Validation

```php
// CreateGroupRequest
public function rules(): array
{
    $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'grading_source' => 'required|in:captain,admin',
    ];

    // Guest-specific validation
    if (!$this->user()) {
        $rules['captain_name'] = 'required|string|max:255';
        $rules['captain_email'] = 'nullable|email|max:255';  // Not unique!
    }

    return $rules;
}
```

### Dynamic Layout

```vue
<!-- CreateGroup.vue -->
<script setup>
const props = defineProps({
    isGuest: Boolean,
});

const LayoutComponent = props.isGuest ? GuestLayout : AuthenticatedLayout;
</script>

<template>
    <component :is="LayoutComponent">
        <!-- Guest-specific fields -->
        <div v-if="isGuest">
            <TextInput v-model="form.captain_name" required />
            <TextInput v-model="form.captain_email" type="email" />
        </div>

        <!-- Regular group fields -->
        <TextInput v-model="form.name" required />
    </component>
</template>
```

## Integration with Other Features

- **Captain System**: Guest users can be captains with full permissions
- **Groups**: Guests create and manage groups like regular users
- **Events**: Guests participate in events
- **Dashboard**: Unified dashboard adapts for guest users

**See Also:**
- [captain-system.md](captain-system.md) - Guest captains and invitations
- [groups.md](groups.md) - Group creation and management

## Security Considerations

### Not Vulnerabilities

**Email Linking by Design**:
- Guest users have no password to compromise
- Only become captains by creating groups (invitation required)
- Email linking is intentional for history tracking
- Not a security risk - it's a feature

**No Email Uniqueness**:
- Intentionally allow duplicate emails for guests
- Enables automatic account linking
- Regular users still have unique emails (enforced)

### Actual Security Measures

**Admin Accounts**:
- ✅ Password required (cannot be guest)
- ✅ Email verification enforced
- ✅ Strong password requirements

**Guest Token**:
- ✅ Unique 32-character random string
- ✅ Used for future access (Phase 11)
- ✅ Cannot be guessed

**Captain Invitations**:
- ✅ Tokenized URLs (cannot be guessed)
- ✅ Optional expiration and usage limits
- ✅ Can be deactivated by admin

## Gotchas

1. **Guests Cannot Login**: No password means no login page access
2. **Email Not Unique for Guests**: Intentional design decision
3. **Guest Token for Future**: Currently unused, reserved for Phase 11
4. **Dynamic Layouts**: Components must handle isGuest prop
5. **Conditional Validation**: Forms validate differently for guests

## Future Enhancements (Phase 11)

- Email guest token for future access
- Convert guest to full user (add password)
- Guest personal dashboard
- Resend guest access link
- Guest account management
