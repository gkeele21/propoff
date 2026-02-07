<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Badge from '@/Components/Base/Badge.vue';
import Button from '@/Components/Base/Button.vue';
import Card from '@/Components/Base/Card.vue';
import PageHeader from '@/Components/Base/PageHeader.vue';
import Icon from '@/Components/Base/Icon.vue';

const props = defineProps({
    group: Object,
    leaderboard: Object,
    userRow: Object,
});

// Get ordinal suffix
const getOrdinal = (n) => {
    const s = ['th', 'st', 'nd', 'rd'];
    const v = n % 100;
    return n + (s[(v - 20) % 10] || s[v] || s[0]);
};

const breadcrumbs = computed(() => [
    { label: 'Home', href: route('play.hub', { code: props.group.code }) },
    { label: 'Leaderboard' },
]);
</script>

<template>
    <Head :title="`Leaderboard - ${group.name}`" />

    <AuthenticatedLayout :group="group">
        <template #header>
            <PageHeader title="Leaderboard" :subtitle="group.event?.name" :crumbs="breadcrumbs">
                <template #actions>
                    <Link :href="route('play.game', { code: group.code })">
                        <Button
                            variant="accent"
                            size="sm"
                            icon="eye"
                        >
                            View Answers
                        </Button>
                    </Link>
                </template>
            </PageHeader>
        </template>

        <!-- Main Content -->
        <div class="max-w-4xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
            <Card>
                <div class="-m-5">
                    <!-- Column Headers -->
                    <div class="flex items-center py-3 px-4 sm:px-5 bg-surface-header border-b border-border text-xs font-medium text-subtle uppercase tracking-wider">
                        <div class="w-12 sm:w-14">Rank</div>
                        <div class="flex-1 min-w-0">Player</div>
                        <div class="w-16 sm:w-24 text-right">Points</div>
                        <div class="hidden sm:block w-24 text-right">Max</div>
                    </div>

                    <!-- Leaderboard Rows -->
                    <div class="divide-y divide-border">
                        <div
                            v-for="entry in leaderboard.data"
                            :key="entry.id"
                            class="flex items-center py-3 px-4 sm:px-5"
                            :class="{ 'bg-surface-elevated': entry.user_id === userRow?.user_id }"
                        >
                            <div class="w-12 sm:w-14 font-bold text-lg sm:text-xl text-primary">
                                {{ getOrdinal(entry.rank) }}
                            </div>
                            <div class="flex-1 min-w-0 truncate">
                                {{ entry.user?.name }}
                                <Badge v-if="entry.user_id === userRow?.user_id" variant="primary-soft" size="sm" class="ml-1 sm:ml-2">You</Badge>
                            </div>
                            <div class="w-16 sm:w-24 text-right font-semibold text-body">{{ entry.total_score }}</div>
                            <div class="hidden sm:block w-24 text-right text-muted">{{ entry.possible_points }}</div>
                        </div>
                    </div>

                    <!-- Empty state -->
                    <div v-if="leaderboard.data.length === 0" class="p-8 text-center">
                        <Icon name="trophy" size="3x" class="text-subtle mb-4" />
                        <p class="text-muted">No entries yet</p>
                    </div>
                </div>
            </Card>

            <!-- Pagination -->
            <div v-if="leaderboard.links && leaderboard.links.length > 3" class="mt-6 flex justify-center gap-2">
                <Link
                    v-for="link in leaderboard.links"
                    :key="link.label"
                    :href="link.url || '#'"
                    class="px-3 py-1 rounded text-sm"
                    :class="link.active
                        ? 'bg-primary text-white'
                        : 'bg-surface border border-border text-muted hover:border-primary'"
                    v-html="link.label"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
