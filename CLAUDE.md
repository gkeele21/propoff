# CLAUDE.md

This file contains project-specific guidance for Claude Code when working on this codebase.

## Design System

### Theme System

PropOff uses a **dark mode** design with **user-selectable accent colors** (themes) and **background modes**. Colors are defined as CSS variables with RGB triplets to support Tailwind's opacity modifiers.

**Available Themes (accent colors):**
- `green` (default) - Primary: #57d025
- `blue` - Primary: #3b82f6
- `orange` - Primary: #f47612

**Available Background Modes:**
- `slate` (default) - Page background: #404040
- `cream` - Page background: #f5f3ef

Classes are applied to `<html>`: `.theme-green`, `.theme-blue`, `.theme-orange`, `.bg-mode-slate`, `.bg-mode-cream`

### Color Palette (STRICT)

**DO NOT add new colors without explicit user approval.** Use only the following defined colors:

**Theme-Adaptive Colors** (change with user's theme):
| Tailwind Class | Usage |
|----------------|-------|
| `primary` | Main actions, links, focus states, accent color |
| `primary-hover` | Hover state for primary |

**Semantic Colors** (consistent across themes):
| Tailwind Class | Hex | Usage |
|----------------|-----|-------|
| `success` | #57d025 | Positive states, confirmations |
| `warning` | #f47612 | Caution, attention states |
| `danger` | #ef4444 | Errors, destructive actions |
| `info` | #3b82f6 | Informational, templates, imports |

**Surface Colors** (dark mode hierarchy - "Slate + Black Accents"):
| Tailwind Class | Hex | Usage |
|----------------|-----|-------|
| `bg` | #404040 | Page background (slate, user-selectable) |
| `surface` | #1f1f1f | Cards, panels |
| `surface-elevated` | #262626 | Question items, elevated content |
| `surface-overlay` | #333333 | Hover states on surfaces |
| `surface-inset` | #0f0f0f | Stats, answers, buttons (black accent) |
| `surface-header` | #171717 | Card headers |

**Text Colors:**
| Tailwind Class | Hex | Usage |
|----------------|-----|-------|
| `body` | #f5f5f5 | Primary text |
| `muted` | #a3a3a3 | Secondary text |
| `subtle` | #737373 | Disabled, hints |

**Border Colors:**
| Tailwind Class | Hex | Usage |
|----------------|-----|-------|
| `border` | #2e2e2e | Default borders |
| `border-strong` | #404040 | Emphasized borders |

**Static Colors:**
| Tailwind Class | Hex | Usage |
|----------------|-----|-------|
| `white` | #ffffff | Static white |
| `black` | #171717 | Static black |
| `secondary` | #525252 | Neutral gray |

### Using Color Variations

Use Tailwind's opacity modifiers:

```html
<!-- Hover states -->
<button class="bg-primary hover:bg-primary-hover">

<!-- Light backgrounds/tints -->
<div class="bg-primary/10">

<!-- Softer text -->
<span class="text-danger/80">

<!-- Borders -->
<div class="border border-border">
```

### Theme Management

Use the `useTheme` composable to manage themes and background modes:

```vue
<script setup>
import { useTheme } from '@/composables/useTheme';

const { theme, setTheme, themes, cycleTheme, bgMode, setBgMode, bgModes } = useTheme();
</script>

<template>
    <!-- Theme selector -->
    <select v-model="theme" @change="setTheme($event.target.value)">
        <option v-for="t in themes" :key="t" :value="t">{{ t }}</option>
    </select>

    <!-- Background mode selector -->
    <select v-model="bgMode" @change="setBgMode($event.target.value)">
        <option v-for="m in bgModes" :key="m" :value="m">{{ m }}</option>
    </select>

    <!-- Or cycle through themes -->
    <button @click="cycleTheme">Change Theme</button>
</template>
```

### Legacy Colors

The following aliases exist for backward compatibility but should be migrated:
- `propoff-red` → use `danger`
- `propoff-orange` → use `warning`
- `propoff-green` → use `success`
- `propoff-dark-green` → remove (use `success` with opacity)
- `propoff-blue` → use `primary`

---

## Component Library (STRICT)

**ALWAYS use the existing components below. DO NOT create new components without explicit user approval.**

If you need functionality not covered by these components, ASK the user first before creating anything new.

Custom components are organized in `resources/js/Components/`:

### Base Components (`Components/Base/`)
| Component | Usage |
|-----------|-------|
| `Icon` | Font Awesome icon wrapper: `<Icon name="check" size="sm" />` |
| `Button` | Variants: primary, secondary, danger, ghost. Sizes: xs, sm, md, lg |
| `Badge` | Status badges with variants and soft variants |
| `Card` | Flexible card with header/body/footer, border accents |
| `Modal` | Teleported modal with sizes, escape key support |
| `DataTable` | Table with slots for columns, rows, filters, pagination |
| `Pagination` | Full pagination controls |
| `Breadcrumbs` | Navigation breadcrumbs |
| `PageHeader` | Page title with breadcrumbs |
| `Stepper` | Multi-step wizard |
| `Timer` | Countdown/timer display |

### Form Components (`Components/Form/`)
| Component | Usage |
|-----------|-------|
| `TextField` | Text input with label, error, hint, icons |
| `TextArea` | Multi-line text with label/error |
| `Select` | Styled select dropdown |
| `Dropdown` | Teleported dropdown menu |
| `Checkbox` | Styled checkbox with label |
| `Toggle` | On/off switch |
| `Radio` | Radio button group |
| `DatePicker` | Date selection |
| `NumberInput` | Numeric input with +/- buttons |
| `MultiSelect` | Multi-value selection |
| `Autocomplete` | Search-as-you-type dropdown |
| `FormLabel` | Consistent form labels |

### Feedback Components (`Components/Feedback/`)
| Component | Usage |
|-----------|-------|
| `Toast` | Positioned toast notifications with auto-dismiss |
| `Confirm` | Confirmation dialog modal |
| `Banner` | Top banner notifications |
| `Tooltip` | Hover tooltips |
| `Error` | Error message display |

### Domain Components (`Components/Domain/`)
PropOff-specific business components:

| Component | Usage |
|-----------|-------|
| `Logo` | PropOff logo with diamond icon + wordmark, theme-aware |
| `QuestionCard` | Multiple choice question with card-style options, results mode |

**Logo usage:**
```vue
<Logo />                           <!-- Full logo (icon + wordmark), medium size -->
<Logo variant="icon" />            <!-- Diamond icon only -->
<Logo variant="wordmark" />        <!-- PROPOFF text only -->
<Logo size="lg" />                 <!-- Large size for hero sections -->
<Logo size="sm" link-to="/" />     <!-- Small clickable logo for nav -->
```

Props:
- `variant` - Logo variant: `full` (default), `icon`, `wordmark`
- `size` - Size preset: `sm`, `md` (default), `lg`, `xl`
- `linkTo` - Optional href to make logo clickable

**QuestionCard usage:**
```vue
<QuestionCard
    v-model="answer"
    question="Who will win the game?"
    :options="[
        { label: 'Team A', value: 'team_a', points: 5 },
        { label: 'Team B', value: 'team_b', points: 0 },
    ]"
    :points="10"
    :question-number="1"
    :correct-answer="correctAnswer"
    :show-results="showResults"
    :disabled="locked"
    :error="errors.answer"
/>
```

Props:
- `modelValue` - Selected answer value (v-model)
- `question` - Question text
- `options` - Array of options (string or {label, value, points})
- `points` - Base points for the question
- `questionNumber` - Optional question number to display
- `correctAnswer` - The correct answer value (for results mode)
- `showResults` - Show correct/incorrect styling
- `showHeader` - Show question text and points (default: true)
- `showLetters` - Show A, B, C prefixes (default: false)
- `disabled` - Disable selection
- `error` - Error message or boolean

### Icons (Font Awesome)

Use the Icon component with Font Awesome icon names (without `fa-` prefix):

```vue
<Icon name="check" />
<Icon name="xmark" size="lg" />
<Icon name="spinner" class="animate-spin" />
<Icon name="circle-info" variant="regular" />
```

Sizes: `xs`, `sm`, `md`, `lg`, `xl`, `2x`, `3x`, `4x`, `5x`
Variants: `solid` (default), `regular`

### Component Usage Examples

```vue
<script setup>
import Button from '@/Components/Base/Button.vue';
import TextField from '@/Components/Form/TextField.vue';
import Select from '@/Components/Form/Select.vue';
import Card from '@/Components/Base/Card.vue';
import Badge from '@/Components/Base/Badge.vue';
import Modal from '@/Components/Base/Modal.vue';
import Confirm from '@/Components/Feedback/Confirm.vue';
import Toast from '@/Components/Feedback/Toast.vue';
import Icon from '@/Components/Base/Icon.vue';
</script>

<template>
    <!-- Buttons -->
    <Button variant="primary" :loading="saving">Save</Button>
    <Button variant="danger" icon="trash">Delete</Button>
    <Button variant="ghost" size="sm">Cancel</Button>

    <!-- Form Fields -->
    <TextField v-model="name" label="Event Name" :error="errors.name" hint="Max 255 characters" />
    <Select v-model="status" label="Status" :options="statusOptions" />

    <!-- Cards -->
    <Card title="Event Details" border-color="primary">
        <template #headerActions>
            <Button variant="ghost" size="sm">Edit</Button>
        </template>
        <!-- content -->
    </Card>

    <!-- Badges -->
    <Badge variant="success">Active</Badge>
    <Badge variant="danger-soft">Closed</Badge>

    <!-- Modals -->
    <Modal :show="showModal" @close="showModal = false">
        <!-- content -->
    </Modal>

    <!-- Confirmation Dialog -->
    <Confirm
        :show="showConfirm"
        title="Delete Event?"
        message="This action cannot be undone."
        variant="danger"
        icon="triangle-exclamation"
        @confirm="handleDelete"
        @close="showConfirm = false"
    />
</template>
```

### Legacy Components (to be migrated)

The following old components in `Components/` root should be gradually replaced:
- `PrimaryButton.vue` → use `Button` with `variant="primary"`
- `SecondaryButton.vue` → use `Button` with `variant="secondary"`
- `DangerButton.vue` → use `Button` with `variant="danger"`
- `TextInput.vue` → use `TextField`
- `InputLabel.vue` → use `FormLabel`
- `InputError.vue` → errors are built into form components
- `Modal.vue` (root) → use `Base/Modal.vue`

---

## Tech Stack

- **Backend:** Laravel 12, PHP 8.4
- **Frontend:** Vue 3 with Inertia.js
- **Styling:** Tailwind CSS
- **Icons:** Font Awesome 6
- **Database:** MySQL/MariaDB

---

## Implementation Guidelines

### Quality Over Speed

Take time to implement correctly. Read specs thoroughly, check mockups, verify against this document. It's better to be slow and right than fast and wrong.

### Before Starting Any Page

1. **Read the spec** - Check `docs/specs/` for relevant spec files
2. **View the mockup** - Open HTML mockups in browser to see exact design
3. **Identify components** - Map each UI element to an existing component
4. **Check for patterns** - Look at similar existing pages for reference
5. **Review layouts** - Check `Layouts/` for structural patterns (nav, header, footer)

### Mockups vs Layouts

**Mockups define page content.** HTML mockups in `docs/specs/` show the content area design - cards, forms, data displays, etc. Match this content layout exactly.

**Layouts define structure.** The codebase has established patterns for:
- Navigation (logo, nav links, user menu)
- Page headers (PageHeader component with breadcrumbs)
- Content wrappers (max-width, padding, spacing)
- Footer

**When mockups don't show something (nav, logo, header structure), follow existing codebase patterns.** Look at similar pages like `Admin/Events/Show.vue` or `Admin/Grading/Index.vue` to see how they handle layout structure.

### Anti-Patterns (DO NOT DO)

**Colors:**
- ❌ DO NOT use arbitrary Tailwind colors (`bg-gray-800`, `text-blue-500`)
- ❌ DO NOT add new color variables to tailwind.config.js
- ❌ DO NOT use inline styles for colors
- ✅ DO use only colors defined in the Color Palette section above

**Components:**
- ❌ DO NOT create new components without asking first
- ❌ DO NOT use native HTML elements when a component exists (e.g., `<button>` instead of `<Button>`)
- ❌ DO NOT duplicate component functionality with custom code
- ✅ DO check the Component Library section before building anything

**Styling:**
- ❌ DO NOT add custom CSS classes when Tailwind utilities exist
- ❌ DO NOT use `@apply` to create new utility classes
- ❌ DO NOT override component styles with inline styles
- ✅ DO use component props and variants as designed

**Data Display:**
- ❌ DO NOT show percentages for scores (use points only)
- ❌ DO NOT use colored medals/icons for ranks (use consistent `surface-inset` badges)
- ❌ DO NOT add emoji icons to events or categories
- ✅ DO keep users feeling positive - points > percentages

### UI Consistency Rules

**Rank Badges:** Always use `bg-surface-inset` background with ordinal text (1st, 2nd, 3rd). Same styling everywhere - leaderboard, history, results.

**Points Display:** Show scores as points only (e.g., "68 pts"). Never show percentages.

**Bonus Points:** Display inline with answer text using `Badge` component with `variant="primary-soft"` (e.g., "Kansas City Chiefs +5 bonus").

**Breadcrumbs:** Use for sub-page navigation within a context. Format: `[Parent] / [Current Page]`

### Verification Checklist

Before considering any page complete:

- [ ] Uses ONLY colors from the Color Palette section
- [ ] Uses ONLY components from the Component Library section
- [ ] Page content matches the mockup design exactly
- [ ] Layout structure follows existing codebase patterns (nav, headers, wrappers)
- [ ] No new CSS colors or Tailwind color classes added
- [ ] No new components created
- [ ] Responsive behavior works on mobile
- [ ] Dark mode looks correct (it's the default)
- [ ] Points shown as points, not percentages
- [ ] Rank badges use consistent `surface-inset` styling

### When Unsure

If you're unsure about:
- **Which component to use** → Ask the user
- **How to style something** → Check the mockup, then ask
- **Whether to create something new** → Don't. Ask first.
- **Color choices** → Use only defined colors. If none fit, ask.
