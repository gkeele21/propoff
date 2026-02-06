# Frontend Patterns

## Technology Stack

- **Framework**: Vue 3 (Composition API)
- **Bridge**: Inertia.js v0.6.11
- **CSS**: Tailwind CSS with custom design system (see [CLAUDE.md](/CLAUDE.md))
- **Icons**: Font Awesome 6 via Icon component
- **Build Tool**: Vite 5.4.21

## Vue 3 Composition API

All components use `<script setup>` syntax:

```vue
<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'

const props = defineProps({
    event: Object,
    groups: Array,
})

const selectedGroup = ref(null)
const filteredGroups = computed(() => {
    // Computed logic
})

const handleSubmit = () => {
    // Method logic
}
</script>
```

## Inertia.js Patterns

### Controller Response
```php
return Inertia::render('Events/Show', [
    'event' => $event,
    'groups' => $groups,
]);
```

### Form Handling
```vue
<script setup>
const form = useForm({
    name: '',
    description: '',
    grading_source: 'captain',
})

const submit = () => {
    form.post(route('groups.store'), {
        onSuccess: () => {
            // Success handler
        },
        onError: () => {
            // Error handler
        },
    })
}
</script>
```

### Navigation with Filters
```vue
<script setup>
const search = ref(props.filters.search || '')

const filter = () => {
    router.get(route('events.index'), {
        search: search.value,
        status: selectedStatus.value,
    }, {
        preserveState: true,
        replace: true,
    })
}
</script>
```

## Component Structure

### Feature-Based Organization
```
resources/js/Pages/
├── Index.vue (homepage with join code form)
├── History.vue (past events and results)
├── Play/
│   ├── Hub.vue (main play hub - adapts for role)
│   ├── Join.vue (guest join flow - 3-step)
│   ├── Questions.vue (answer questions)
│   ├── Results.vue (view submission results)
│   └── Leaderboard.vue (full leaderboard)
├── Groups/
│   ├── Index.vue (list user's groups)
│   ├── Show.vue (captain hub - questions, grading, management)
│   ├── Create.vue (create new group)
│   ├── Edit.vue (edit group settings)
│   ├── Choose.vue (group picker for multi-group)
│   ├── Join.vue (join group form)
│   ├── Invitation.vue (invitation management)
│   ├── Leaderboard.vue (group leaderboard)
│   └── Members/Index.vue (member management)
├── Captain/
│   ├── CreateGroup.vue (guest + auth flows)
│   └── InvitationExpired.vue
├── Admin/
│   ├── Events/ (event management)
│   ├── QuestionTemplates/ (template management)
│   ├── Groups/ (group management)
│   └── Grading/ (grading interface)
└── AmericaSays/
    └── GameBoard.vue (live game display)
```

**Note**: The legacy `Entries/` folder has been removed. Entry management now happens through the Play Hub flow (`/play/{code}/play` and `/play/{code}/results`).

## Layouts

### Dynamic Layout Pattern
```vue
<script setup>
const props = defineProps({
    isGuest: Boolean,
})

const LayoutComponent = props.isGuest ? GuestLayout : AuthenticatedLayout
</script>

<template>
    <component :is="LayoutComponent">
        <!-- Content -->
    </component>
</template>
```

### Layout Files
- `AuthenticatedLayout.vue` - For logged-in users
- `GuestLayout.vue` - For guest users (no nav)
- `AdminLayout.vue` - Uses AuthenticatedLayout with admin nav

## Design System Colors

PropOff uses a **dark mode** design with **user-selectable accent colors** (themes). See [CLAUDE.md](/CLAUDE.md) for the complete color palette.

### Key Color Classes

**Theme-Adaptive (changes with user's theme)**:
- `primary` - Main accent color, actions, links
- `primary-hover` - Hover state for primary

**Semantic (consistent across themes)**:
- `success` (#57d025) - Positive states, confirmations
- `warning` (#f47612) - Caution, attention
- `danger` (#ef4444) - Errors, destructive actions
- `info` (#3b82f6) - Informational states

**Surfaces (dark mode hierarchy)**:
- `bg` (#404040) - Page background
- `surface` (#1f1f1f) - Cards, panels
- `surface-elevated` (#262626) - Elevated content
- `surface-inset` (#0f0f0f) - Stats, badges, buttons

**Text**:
- `body` (#f5f5f5) - Primary text
- `muted` (#a3a3a3) - Secondary text
- `subtle` (#737373) - Disabled, hints

### Usage Example
```vue
<template>
    <!-- Card on dark background -->
    <div class="bg-surface border border-border rounded-lg p-4">
        <h3 class="text-body">Event Name</h3>
        <p class="text-muted">Description here</p>
    </div>

    <!-- Primary action button -->
    <Button variant="primary">Submit</Button>

    <!-- Success state -->
    <Badge variant="success">Submitted</Badge>

    <!-- Rank badge (consistent styling) -->
    <span class="bg-surface-inset text-body px-2 py-1 rounded">3rd</span>
</template>
```

**Note**: The old `propoff-*` color classes are deprecated. See CLAUDE.md for migration guidance.

## Common Patterns

### Play Hub Pattern

The Play Hub (`Play/Hub.vue`) is the central player experience. It adapts based on role and state:

```vue
<script setup>
const props = defineProps({
    group: Object,
    entry: Object,         // User's current entry (null if not started)
    isCaptain: Boolean,
    leaderboard: Array,
    stats: Object,
})

// Entry state determines UI
const entryState = computed(() => {
    if (!props.entry) return 'not_started'
    if (props.entry.is_submitted) return 'submitted'
    return 'in_progress'
})
</script>

<template>
    <!-- Stats row for everyone -->
    <StatsRow :stats="stats" />

    <!-- My Entry card (adapts to state) -->
    <MyEntryCard :entry="entry" :group="group" />

    <!-- Captain Controls (captain only) -->
    <CaptainControls v-if="isCaptain" :group="group" />

    <!-- Leaderboard preview -->
    <LeaderboardPreview :entries="leaderboard" />
</template>
```

### Conditional Rendering (Role-Based)
```vue
<template>
    <!-- Admin section (only for admins) -->
    <div v-if="$page.props.auth.user.role === 'admin'"
         class="bg-gradient-to-r from-propoff-red/10 to-propoff-red/5">
        <!-- Admin content -->
    </div>

    <!-- Captain section (only for captains) -->
    <div v-if="isCaptain"
         class="bg-gradient-to-r from-propoff-blue/10 to-propoff-blue/5">
        <!-- Captain content -->
    </div>
</template>
```

### Modal Pattern
```vue
<script setup>
const showModal = ref(false)

const openModal = () => showModal.value = true
const closeModal = () => showModal.value = false
</script>

<template>
    <button @click="openModal">Open</button>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50">
        <div class="fixed inset-0 bg-black/50" @click="closeModal"></div>
        <div class="relative bg-white rounded-lg p-6">
            <!-- Modal content -->
            <button @click="closeModal">Close</button>
        </div>
    </div>
</template>
```

### Drag-and-Drop (Question Reordering)
```vue
<script setup>
import { ref } from 'vue'

const questions = ref(props.questions)

const handleDragStart = (event, index) => {
    event.dataTransfer.effectAllowed = 'move'
    event.dataTransfer.setData('index', index)
}

const handleDrop = (event, targetIndex) => {
    const sourceIndex = parseInt(event.dataTransfer.getData('index'))
    const items = [...questions.value]
    const [removed] = items.splice(sourceIndex, 1)
    items.splice(targetIndex, 0, removed)
    questions.value = items

    // Save new order
    saveOrder(questions.value.map((q, i) => ({ id: q.id, order: i })))
}
</script>

<template>
    <div v-for="(question, index) in questions"
         :key="question.id"
         draggable="true"
         @dragstart="handleDragStart($event, index)"
         @drop="handleDrop($event, index)"
         @dragover.prevent>
        {{ question.question_text }}
    </div>
</template>
```

### Breadcrumb Navigation
```vue
<script setup>
const breadcrumbs = [
    { label: 'Home', href: route('play') },
    { label: 'Events', href: route('events.index') },
    { label: props.event.name, href: null },  // Current page
]
</script>

<template>
    <nav class="flex text-sm text-gray-600">
        <Link v-for="(crumb, index) in breadcrumbs"
              :key="index"
              :href="crumb.href"
              :class="{ 'text-gray-900 font-semibold': !crumb.href }">
            {{ crumb.label }}
            <span v-if="index < breadcrumbs.length - 1" class="mx-2">/</span>
        </Link>
    </nav>
</template>
```

## Mobile-First Design

All components use Tailwind responsive prefixes:

```vue
<template>
    <!-- Mobile: Card layout -->
    <div class="block md:hidden space-y-4">
        <div v-for="item in items" class="bg-white p-4 rounded-lg">
            <!-- Card content -->
        </div>
    </div>

    <!-- Desktop: Table layout -->
    <div class="hidden md:block">
        <table class="w-full">
            <tr v-for="item in items">
                <!-- Table row -->
            </tr>
        </table>
    </div>
</template>
```

## Performance Optimization

### Lazy Loading
```vue
<script setup>
import { defineAsyncComponent } from 'vue'

const HeavyComponent = defineAsyncComponent(() =>
    import('./HeavyComponent.vue')
)
</script>
```

### Inertia Partial Reloads
```javascript
router.get(route('events.show', event.id), {}, {
    only: ['leaderboard'],  // Only reload leaderboard prop
    preserveState: true,
})
```

## Accessibility

- **WCAG 2.1 AA Compliant**: All color combinations meet contrast requirements
- **Focus States**: `focus:ring-2 focus:ring-propoff-blue/50`
- **Keyboard Navigation**: Tab order and keyboard shortcuts
- **Screen Readers**: Semantic HTML and ARIA labels

## Common Components

### PageHeader
```vue
<PageHeader
    :breadcrumbs="breadcrumbs"
    :title="event.name"
    :subtitle="event.description"
    :metadata="{ Date: event.event_date, Status: event.status }"
    :actions="[{ label: 'Edit', href: route('events.edit', event.id) }]"
/>
```

### Badge Component
```vue
<Badge variant="success">Active</Badge>
<Badge variant="warning">Pending</Badge>
<Badge variant="danger">Closed</Badge>
<Badge variant="primary-soft">+5 bonus</Badge>
<!-- See Components/Base/Badge.vue for all variants -->

## Testing Patterns

### Component Testing (Planned)
```javascript
import { mount } from '@vue/test-utils'
import EventCard from './EventCard.vue'

test('displays event name', () => {
    const wrapper = mount(EventCard, {
        props: { event: { name: 'Test Event' } }
    })
    expect(wrapper.text()).toContain('Test Event')
})
```

## Gotchas

1. **Inertia Props**: Access via `props` in script, `$page.props` in template
2. **Form Errors**: Inertia form errors in `form.errors.field`
3. **Route Helper**: Must use `route()` helper, not hardcoded URLs
4. **Preserve State**: Use `preserveState: true` for filtering/pagination
5. **Color Opacity**: Use `/10`, `/20` etc for alpha, not opacity classes
6. **Component Library**: Always use existing components (Button, Badge, Card, etc.) - see CLAUDE.md
7. **Icons**: Use `<Icon name="check" />` component, not inline SVGs
8. **Play vs Groups**: Play/ pages are for players; Groups/ pages are for captain management
9. **Guest Detection**: Check `$user->isGuest()` or `props.user.role === 'guest'`
