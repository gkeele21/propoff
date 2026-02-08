# Flash Toast Notifications Spec

This document outlines the implementation of consistent flash toast notifications across the PropOff application.

## Overview

Flash toast notifications provide user feedback for operations like create, update, delete, and other actions. They display success messages (green) and error messages (red) in the top-right corner of the screen.

## Implementation

### Composable: `useFlashToast`

Location: `resources/js/composables/useFlashToast.js`

```javascript
import { useFlashToast } from '@/composables/useFlashToast';

const {
    showErrorToast,
    showSuccessToast,
    errorMessage,
    successMessage,
    showFlashMessages,
    showError,
    showSuccess,
    hideAll
} = useFlashToast();
```

### Usage Pattern

#### 1. Import the composable and Toast component

```vue
<script setup>
import Toast from '@/Components/Feedback/Toast.vue';
import { useFlashToast } from '@/composables/useFlashToast';

const { showErrorToast, showSuccessToast, errorMessage, successMessage, showFlashMessages } = useFlashToast();
</script>
```

#### 2. Call `showFlashMessages` in router callbacks

```javascript
router.post(url, data, {
    onSuccess: (page) => {
        showFlashMessages(page.props);
    },
});

router.delete(url, {
    onSuccess: (page) => {
        showFlashMessages(page.props);
    },
    onError: () => {
        // Handle validation errors if needed
    },
});
```

#### 3. Add Toast components to template

```vue
<template>
    <!-- Your page content -->

    <!-- Flash Messages (add at end of template) -->
    <Toast
        :show="showErrorToast"
        :message="errorMessage"
        variant="error"
        position="top-right"
        @close="showErrorToast = false"
    />
    <Toast
        :show="showSuccessToast"
        :message="successMessage"
        variant="success"
        position="top-right"
        @close="showSuccessToast = false"
    />
</template>
```

### Backend Pattern

Controllers should return flash messages using Laravel's `back()->with()`:

```php
// Success
return back()->with('success', 'Item created successfully!');

// Error
return back()->with('error', 'Cannot delete item because...');

// Or with redirect
return redirect()->route('items.index')->with('success', 'Item deleted.');
```

---

## Pages Requiring Updates

### High Priority (user-facing operations with no feedback)

| File | Operations | Current State |
|------|-----------|---------------|
| `Admin/Users/Show.vue` | Update role, delete user | No flash handling, uses browser confirm() |
| `Admin/Users/Index.vue` | Update role, bulk delete | Uses confirm() but no toasts |
| `Admin/Groups/Show.vue` | Add/remove member | Only success toast, no error handling |
| `Admin/Events/EventForm.vue` | Create/update event, delete | No flash handling |

### Medium Priority (admin operations)

| File | Operations | Current State |
|------|-----------|---------------|
| `Admin/Events/Show.vue` | Grade, reorder, delete questions | Manual toasts but no error feedback |
| `Admin/Events/ImportQuestions.vue` | Bulk import, delete | Uses axios + alert() |
| `Admin/CaptainInvitations/Index.vue` | Create, deactivate, delete | No success/error toasts |
| `Admin/Groups/Index.vue` | Delete, bulk delete | No flash handling |
| `Admin/QuestionTemplates/TemplateForm.vue` | CRUD operations | No notifications |
| `Admin/QuestionTemplates/Index.vue` | Duplicate template | No notifications |

### Already Implemented

| File | Status |
|------|--------|
| `Groups/Members/Index.vue` | Uses `useFlashToast` composable |
| `Groups/Questions.vue` | Has manual toast handling |
| `Play/Hub.vue` | Has custom toast handling |
| `Play/Game.vue` | Has manual toast with error handling |

---

## Checklist for Each Page

When adding flash toast support to a page:

- [ ] Import `Toast` component
- [ ] Import and destructure `useFlashToast` composable
- [ ] Add `onSuccess` callback to all `router.post`, `router.delete`, `router.patch` calls
- [ ] Call `showFlashMessages(page.props)` in each `onSuccess`
- [ ] Add `onError` callback where validation errors are possible
- [ ] Add both Toast components at end of template
- [ ] Verify backend controller returns appropriate flash messages
- [ ] Replace any `alert()` or `confirm()` dialogs with proper modals/toasts

---

## Common Issues

### Toast only shows once
The composable handles this by resetting the show state before re-triggering:
```javascript
showErrorToast.value = false;
setTimeout(() => showErrorToast.value = true, 0);
```

### Flash message not appearing
Ensure the backend returns the flash in the session:
```php
return back()->with('error', 'Message here');  // Correct
return back();  // Wrong - no message
```

### Need custom error message (not from server)
Use the manual methods:
```javascript
showError('Something went wrong');
showSuccess('Operation completed');
```
