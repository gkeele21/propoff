<template>
    <Head title="My Results" />

    <GuestLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Welcome -->
                <div class="bg-surface overflow-hidden shadow-sm sm:rounded-lg border border-border mb-6">
                    <div class="p-6">
                        <h2 class="text-2xl font-bold text-body mb-2">
                            Welcome back, {{ user.name }}!
                        </h2>
                        <p class="text-muted">Here are your game results</p>

                        <!-- Save This Link Notice -->
                        <div class="mt-4 bg-primary/10 border border-primary/30 rounded-lg p-4">
                            <div class="flex items-start gap-3">
                                <InformationCircleIcon class="w-5 h-5 text-primary mt-0.5" />
                                <div class="flex-1">
                                    <p class="text-sm text-primary font-medium">Save this link!</p>
                                    <p class="text-sm text-primary mt-1">
                                        Bookmark this page to return and view your results anytime.
                                    </p>
                                    <div class="mt-2 flex items-center gap-2">
                                        <input
                                            :value="currentUrl"
                                            readonly
                                            class="flex-1 text-xs bg-surface-elevated border border-primary/30 rounded px-2 py-1 text-body"
                                        />
                                        <button
                                            @click="copyLink"
                                            class="px-3 py-1 bg-primary text-white text-xs rounded hover:bg-primary/80"
                                        >
                                            {{ copied ? 'Copied!' : 'Copy' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Entries -->
                <div class="bg-surface overflow-hidden shadow-sm sm:rounded-lg border border-border">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-body mb-4">My Entries</h3>

                        <div v-if="entries.length === 0" class="text-center py-12">
                            <TrophyIcon class="w-16 h-16 text-warning mx-auto mb-4" />
                            <p class="text-muted">No entries yet</p>
                        </div>

                        <div v-else class="space-y-4">
                            <div
                                v-for="entry in entries"
                                :key="entry.id"
                                class="border border-border rounded-lg p-4 hover:border-muted hover:bg-surface-elevated transition"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-body">{{ entry.event_name }}</h4>
                                        <p class="text-sm text-muted">{{ entry.group_name }}</p>

                                        <div class="mt-2 flex items-center gap-4">
                                            <div>
                                                <span class="text-2xl font-bold text-success">{{ entry.percentage }}%</span>
                                                <span class="text-sm text-muted ml-1">
                                                    ({{ entry.total_score }}/{{ entry.possible_points }} {{ entry.possible_points === 1 ? 'point' : 'points' }})
                                                </span>
                                            </div>
                                        </div>

                                        <p v-if="entry.submitted_at" class="text-xs text-subtle mt-2">
                                            Submitted: {{ formatDate(entry.submitted_at) }}
                                        </p>
                                    </div>

                                    <div class="flex flex-col gap-2">
                                        <span
                                            :class="entry.is_complete ? 'bg-success/10 text-success' : 'bg-warning/10 text-warning'"
                                            class="px-3 py-1 rounded-full text-xs font-medium"
                                        >
                                            {{ entry.is_complete ? 'Complete' : 'In Progress' }}
                                        </span>
                                        
                                        <Link
                                            v-if="entry.can_edit"
                                            :href="route('entries.continue', entry.id)"
                                            class="px-3 py-1 bg-primary text-white text-xs rounded hover:bg-primary/80 text-center"
                                        >
                                            {{ entry.is_complete ? 'View' : 'Continue' }}
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { TrophyIcon, InformationCircleIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    user: Object,
    entries: Array,
});

const copied = ref(false);

const currentUrl = computed(() => {
    return window.location.href;
});

const copyLink = () => {
    navigator.clipboard.writeText(currentUrl.value);
    copied.value = true;
    setTimeout(() => {
        copied.value = false;
    }, 2000);
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>