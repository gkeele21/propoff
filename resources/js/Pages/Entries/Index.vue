<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { CheckCircleIcon, ClockIcon } from '@heroicons/vue/24/outline';
import PageHeader from '@/Components/PageHeader.vue';
import Leaderboard from '../Groups/Leaderboard.vue';

defineProps({
    entries: Object,
});
</script>

<template>
    <Head title="My Entries" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader
                title="My Entries"
                subtitle="View and manage your event entries"
                :crumbs="[
                    { label: 'Dashboard', href: route('dashboard') },
                    { label: 'My Entries' }
                ]"
            />
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-surface overflow-hidden shadow-sm sm:rounded-lg border border-border">
                    <div class="p-6">
                        <div v-if="entries.data.length === 0" class="text-center py-12">
                            <p class="text-muted">You haven't submitted any events yet.</p>
                        </div>

                        <div v-else class="space-y-4">
                            <div
                                v-for="entry in entries.data"
                                :key="entry.id"
                                class="border border-border rounded-lg p-6 hover:shadow-md hover:bg-surface-elevated transition"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3">
                                            <Link
                                                :href="entry.is_complete
                                                    ? route('entries.show', entry.id)
                                                    : route('entries.continue', entry.id)"
                                                class="text-xl font-semibold text-body hover:text-primary/80"
                                            >
                                                {{ entry.event.name }}
                                            </Link>
                                            <span v-if="entry.is_complete" class="inline-flex items-center gap-1 text-success">
                                                <CheckCircleIcon class="w-5 h-5" />
                                                <span class="text-sm font-medium">Completed</span>
                                            </span>
                                            <span v-else class="inline-flex items-center gap-1 text-warning">
                                                <ClockIcon class="w-5 h-5" />
                                                <span class="text-sm font-medium">In Progress</span>
                                            </span>
                                        </div>

                                        <div v-if="entry.group" class="mt-2 text-sm text-muted">
                                            Group: {{ entry.group.name }}
                                        </div>

                                        <div v-if="entry.is_complete" class="mt-4">
                                            <div class="flex items-center gap-6 text-sm">
                                                <div>
                                                    <span class="text-muted">Score:</span>
                                                    <span class="ml-2 font-semibold text-body">
                                                        {{ entry.total_score }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <span class="text-muted">Max:</span>
                                                    <span class="ml-2 font-semibold text-body">
                                                        {{ entry.possible_points }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <span class="text-muted">Percentage:</span>
                                                    <span class="ml-2 font-semibold"
                                                        :class="{
                                                            'text-success': entry.percentage >= 80,
                                                            'text-warning': entry.percentage >= 60 && entry.percentage < 80,
                                                            'text-danger': entry.percentage < 60,
                                                        }"
                                                    >
                                                        {{ entry.percentage.toFixed(1) }}%
                                                    </span>
                                                </div>
                                                <div class="text-muted">
                                                    Submitted: {{ new Date(entry.submitted_at).toLocaleDateString() }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="ml-4">
                                        <Link
                                            :href="route('groups.leaderboard', entry.group.id)"
                                            class="inline-flex items-center px-4 py-2 bg-success border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-success/80"
                                        >
                                            View Leaderboard
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="entries.links.length > 3" class="mt-6 flex justify-center">
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                <component
                                    v-for="(link, index) in entries.links"
                                    :key="index"
                                    :is="link.url ? Link : 'span'"
                                    :href="link.url"
                                    class="relative inline-flex items-center px-4 py-2 border border-border text-sm font-medium"
                                    :class="{
                                        'bg-primary/10 border-primary text-primary': link.active,
                                        'bg-surface text-body hover:bg-surface-elevated': !link.active && link.url,
                                        'bg-surface-elevated text-muted cursor-not-allowed': !link.url,
                                        'rounded-l-md': index === 0,
                                        'rounded-r-md': index === entries.links.length - 1,
                                    }"
                                    v-html="link.label"
                                />
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
