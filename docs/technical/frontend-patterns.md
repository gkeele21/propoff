# Frontend Patterns

## Technology Stack

- **Framework**: Vue 3 (Composition API)
- **Bridge**: Inertia.js v0.6.11
- **CSS**: Tailwind CSS + PropOff brand colors
- **Icons**: Heroicons
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
├── Dashboard.vue (unified for all users)
├── Events/
│   ├── Index.vue
│   ├── Show.vue
│   └── Continue.vue
├── Groups/
│   ├── Index.vue
│   ├── Show.vue (adapts for captain vs member)
│   ├── Questions/Index.vue (captain only)
│   ├── Grading/Index.vue (captain only)
│   ├── Members/Index.vue (captain only)
│   └── Leaderboard.vue
├── Captain/
│   ├── CreateGroup.vue (guest + auth flows)
│   ├── Invitation.vue
│   └── InvitationExpired.vue
├── Admin/
│   ├── Events/
│   ├── QuestionTemplates/
│   ├── CaptainInvitations/
│   └── EventAnswers/
└── AmericaSays/
    ├── GameBoard.vue
    ├── GameSetup.vue
    └── ManageAnswers.vue
```

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

## PropOff Brand Colors

### Color Palette
```javascript
// tailwind.config.js
colors: {
    'propoff-red': '#af1919',        // Admin, errors, primary CTA
    'propoff-orange': '#f47612',     // Groups, captains, warnings
    'propoff-green': '#57d025',      // Success states, entries
    'propoff-dark-green': '#186916', // Success text, completed
    'propoff-blue': '#1a3490',       // Events, information
}
```

### Semantic Mapping
- **Events**: Blue backgrounds, borders, text
- **Groups**: Orange backgrounds, borders, text
- **Admin**: Red backgrounds, borders, text
- **Captains**: Orange (same as groups)
- **Success**: Green (icons) + Dark Green (text)
- **Entries**: Dark Green

### Usage Example
```vue
<template>
    <!-- Event card -->
    <div class="bg-propoff-blue/10 border-propoff-blue/30 border">
        <h3 class="text-propoff-blue">Event Name</h3>
    </div>

    <!-- Group card -->
    <div class="bg-propoff-orange/10 border-propoff-orange/30 border">
        <h3 class="text-propoff-orange">Group Name</h3>
    </div>

    <!-- Success message -->
    <div class="bg-propoff-green/10 text-propoff-dark-green">
        ✓ Submission complete
    </div>
</template>
```

## Common Patterns

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
    { label: 'Dashboard', href: route('dashboard') },
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

### StatusBadge
```vue
<StatusBadge :status="event.status" />
<!-- Automatically styled based on status (draft, open, locked, etc.) -->
```

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
