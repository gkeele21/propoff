<template>
    <Head :title="`${group.name} - Leaderboard`" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader
                :title="`${group.name} Leaderboard`"
                subtitle="View rankings for this group"
                :crumbs="[
                    { label: 'Home', href: route('home') },
                    { label: 'Groups', href: route('groups.index') },
                    { label: group.name, href: route('groups.questions', group.id) },
                    { label: 'Leaderboard' }
                ]"
            >
                <template #metadata>
                    <span class="text-sm text-muted">{{ group.event.name }}</span>
                </template>
            </PageHeader>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Statistics -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="bg-surface overflow-hidden shadow-sm sm:rounded-lg p-6 border border-border">
                        <div class="text-sm text-muted">Total Participants</div>
                        <div class="text-2xl font-bold text-body">{{ leaderboard.data.length }}</div>
                    </div>
                    <div class="bg-surface overflow-hidden shadow-sm sm:rounded-lg p-6 border border-border">
                        <div class="text-sm text-muted">Average Score</div>
                        <div class="text-2xl font-bold text-body">{{ averagePercentage }}%</div>
                    </div>
                    <div class="bg-surface overflow-hidden shadow-sm sm:rounded-lg p-6 border border-border">
                        <div class="text-sm text-muted">Highest Score</div>
                        <div class="text-2xl font-bold text-body">{{ highestPercentage }}%</div>
                    </div>
                    <div class="bg-surface overflow-hidden shadow-sm sm:rounded-lg p-6 border border-border">
                        <div class="text-sm text-muted">Max Possible Points</div>
                        <div class="text-2xl font-bold text-body">{{ totalPossiblePoints }}</div>
                    </div>
                </div>

                <!-- Leaderboard -->
                <div class="bg-surface overflow-hidden shadow-sm sm:rounded-lg border border-border">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-body mb-4">Rankings</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-border">
                                <thead class="bg-surface-elevated">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-subtle uppercase tracking-wider">Rank</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-subtle uppercase tracking-wider">Player</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-subtle uppercase tracking-wider">Score</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-subtle uppercase tracking-wider">Percentage</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-subtle uppercase tracking-wider">Answered</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-subtle uppercase tracking-wider">Submitted</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-surface divide-y divide-border">
                                    <tr
                                        v-for="entry in leaderboard.data"
                                        :key="entry.id"
                                        :class="entry.user_id === $page.props.auth.user?.id ? 'bg-primary/10' : ''"
                                    >
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <!-- Medal emojis for top 3 -->
                                                <span v-if="entry.rank === 1" class="inline-flex flex-col items-center">
                                                    <span class="text-2xl">🥇</span>
                                                    <span class="text-xs font-bold -mt-1">{{ entry.rank }}st</span>
                                                </span>
                                                <span v-else-if="entry.rank === 2" class="inline-flex flex-col items-center">
                                                    <span class="text-2xl">🥈</span>
                                                    <span class="text-xs font-bold -mt-1">{{ entry.rank }}nd</span>
                                                </span>
                                                <span v-else-if="entry.rank === 3" class="inline-flex flex-col items-center">
                                                    <span class="text-2xl">🥉</span>
                                                    <span class="text-xs font-bold -mt-1">{{ entry.rank }}rd</span>
                                                </span>
                                                <!-- Ordinal numbers for 4th and below -->
                                                <span v-else class="text-lg font-bold text-body">
                                                    {{ entry.rank }}{{ entry.rank === 1 ? 'st' : entry.rank === 2 ? 'nd' : entry.rank === 3 ? 'rd' : 'th' }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div>
                                                    <div class="text-sm font-medium text-body">
                                                        {{ entry.user.name }}
                                                        <span v-if="entry.user_id === $page.props.auth.user?.id" class="ml-2 text-xs text-primary">(You)</span>
                                                    </div>
                                                    <div v-if="entry.user.email" class="text-sm text-muted">{{ entry.user.email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-semibold text-body">{{ entry.total_score }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <span class="text-sm font-medium text-body">{{ parseFloat(entry.percentage).toFixed(1) }}%</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-body">{{ entry.answered_count }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-muted">
                                            {{ entry.created_at ? formatDate(entry.created_at) : 'N/A' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-if="leaderboard.data.length === 0" class="text-center py-12">
                            <TrophyIcon class="mx-auto h-12 w-12 text-warning" />
                            <h3 class="mt-2 text-sm font-medium text-body">No entries yet</h3>
                            <p class="mt-1 text-sm text-muted">Be the first to complete this event!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { TrophyIcon } from '@heroicons/vue/24/outline';
import { computed } from 'vue';
import PageHeader from '@/Components/Base/PageHeader.vue';

const props = defineProps({
    group: Object,
    leaderboard: Object, // Paginated object
});

const averagePercentage = computed(() => {
    if (props.leaderboard.data.length === 0) return 0;
    const total = props.leaderboard.data.reduce((sum, entry) => sum + parseFloat(entry.percentage), 0);
    return (total / props.leaderboard.data.length).toFixed(1);
});

const highestPercentage = computed(() => {
    if (props.leaderboard.data.length === 0) return 0;
    return Math.max(...props.leaderboard.data.map(entry => parseFloat(entry.percentage))).toFixed(1);
});

const totalPossiblePoints = computed(() => {
    if (props.leaderboard.data.length === 0) return 0;
    // All entries should have the same possible points for the same group/event
    return props.leaderboard.data[0].possible_points;
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>
