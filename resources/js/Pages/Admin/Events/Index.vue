<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Button from '@/Components/Base/Button.vue';
import Badge from '@/Components/Base/Badge.vue';
import Icon from '@/Components/Base/Icon.vue';

const props = defineProps({
    events: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || 'all');

const filterEvents = () => {
    router.get(route('admin.events.index'), {
        search: search.value,
        status: statusFilter.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const getStatusVariant = (status) => {
    const variants = {
        'draft': 'default',
        'open': 'success-soft',
        'locked': 'warning-soft',
        'in_progress': 'primary-soft',
        'completed': 'success-soft',
    };
    return variants[status] || 'default';
};

</script>

<template>
    <Head title="Manage Events" />

    <AuthenticatedLayout title="Events">
        <template #actions>
            <Link :href="route('admin.events.create')">
                <Button variant="primary" size="sm" icon="plus">
                    Create Event
                </Button>
            </Link>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="bg-surface shadow-sm sm:rounded-lg mb-6 border border-border">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-body mb-2">
                                    <Icon name="magnifying-glass" size="sm" class="mr-1" />
                                    Search
                                </label>
                                <input
                                    v-model="search"
                                    @input="filterEvents"
                                    type="text"
                                    placeholder="Search events..."
                                    class="w-full bg-surface-elevated border-border text-body placeholder-subtle rounded-md shadow-sm focus:border-primary focus:ring-primary/50"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-body mb-2">
                                    <Icon name="filter" size="sm" class="mr-1" />
                                    Status
                                </label>
                                <select
                                    v-model="statusFilter"
                                    @change="filterEvents"
                                    class="w-full bg-surface-elevated border-border text-body rounded-md shadow-sm focus:border-primary focus:ring-primary/50"
                                >
                                    <option value="all">All Statuses</option>
                                    <option value="draft">Draft</option>
                                    <option value="open">Open</option>
                                    <option value="locked">Locked</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Events List -->
                <div v-if="events.data.length === 0" class="text-center py-12 bg-surface rounded-lg shadow-sm border border-border">
                    <p class="text-muted">No events found.</p>
                </div>

                <div v-else class="bg-surface rounded-lg border border-border shadow-sm overflow-hidden">
                            <Link
                                v-for="(event, index) in events.data"
                                :key="event.id"
                                :href="route('admin.events.show', event.id)"
                                class="block"
                            >
                                <div
                                    class="hover:bg-surface-elevated transition-all duration-200 cursor-pointer"
                                    :class="index !== events.data.length - 1 ? 'border-b border-border' : ''"
                                >
                                    <div class="p-6">
                                        <div class="flex items-start justify-between gap-3 mb-3">
                                            <div class="flex items-center gap-3">
                                                <h3 class="text-xl font-semibold text-body">
                                                    {{ event.name }}
                                                </h3>
                                                <Badge :variant="getStatusVariant(event.status)">
                                                    {{ event.status }}
                                                </Badge>
                                            </div>
                                            <Link
                                                :href="route('admin.events.edit', event.id)"
                                                class="text-sm font-medium text-primary hover:text-primary/80 transition-colors"
                                                @click.stop
                                            >
                                                Edit
                                            </Link>
                                        </div>

                                        <p v-if="event.description" class="text-subtle mb-4 line-clamp-2">
                                            {{ event.description }}
                                        </p>

                                        <div class="flex flex-wrap items-center gap-4 text-sm text-muted">
                                            <span class="flex items-center gap-1">
                                                <Icon name="circle-question" size="sm" />
                                                {{ event.questions_count }} questions
                                            </span>
                                            <span class="flex items-center gap-1">
                                                <Icon name="users" size="sm" />
                                                {{ event.entries_count }} entries
                                            </span>
                                            <span class="flex items-center gap-1">
                                                <Icon name="calendar" size="sm" />
                                                {{ formatDate(event.event_date) }}
                                            </span>
                                            <span v-if="event.lock_date" class="flex items-center gap-1">
                                                <Icon name="lock" size="sm" />
                                                Lock: {{ formatDate(event.lock_date) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </Link>
                        </div>

                <!-- Pagination -->
                <div v-if="events.links.length > 3" class="mt-6 flex justify-center">
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                        <component
                            v-for="(link, index) in events.links"
                            :key="index"
                            :is="link.url ? Link : 'span'"
                            :href="link.url"
                            class="relative inline-flex items-center px-4 py-2 border border-border text-sm font-medium"
                            :class="{
                                'bg-primary/10 border-primary text-primary': link.active,
                                'bg-surface text-body hover:bg-surface-elevated': !link.active && link.url,
                                'bg-surface-elevated text-muted cursor-not-allowed': !link.url,
                                'rounded-l-md': index === 0,
                                'rounded-r-md': index === events.links.length - 1,
                            }"
                            v-html="link.label"
                        />
                    </nav>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
