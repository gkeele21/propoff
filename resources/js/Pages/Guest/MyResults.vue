<template>
    <Head title="My Results" />

    <div class="min-h-screen bg-bg">
        <!-- Simple header with logo -->
        <header class="bg-surface border-b border-border py-4 px-4 sm:px-6 lg:px-8">
            <Logo size="sm" link-to="/" />
        </header>

        <div class="py-12">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Welcome -->
                <div class="bg-surface overflow-hidden shadow-sm rounded-lg border border-border mb-6">
                    <div class="p-6">
                        <h1 class="text-2xl font-bold text-body mb-2">
                            Welcome back, {{ user.name }}!
                        </h1>
                        <p class="text-muted">Here are your game results</p>

                        <!-- Save This Link Notice -->
                        <div class="mt-4 bg-primary/10 border border-primary/30 rounded-lg p-4">
                            <div class="flex items-start gap-3">
                                <Icon name="circle-info" class="text-primary mt-0.5" />
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
                                        <Button
                                            variant="primary"
                                            size="xs"
                                            @click="copyLink"
                                        >
                                            {{ copied ? 'Copied!' : 'Copy' }}
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Entries -->
                <div class="bg-surface overflow-hidden shadow-sm rounded-lg border border-border">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-body mb-4">My Entries</h2>

                        <div v-if="entries.length === 0" class="text-center py-12">
                            <Icon name="trophy" size="3x" class="text-warning mx-auto mb-4" />
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
                                        <h3 class="font-semibold text-body">{{ entry.event_name }}</h3>
                                        <p class="text-sm text-muted">{{ entry.group_name }}</p>

                                        <div class="mt-2 flex items-center gap-4">
                                            <div>
                                                <span class="text-2xl font-bold text-success">{{ entry.total_score }}</span>
                                                <span class="text-sm text-muted ml-1">
                                                    / {{ entry.possible_points }} pts
                                                </span>
                                            </div>
                                        </div>

                                        <p v-if="entry.submitted_at" class="text-xs text-subtle mt-2">
                                            Submitted: {{ formatDate(entry.submitted_at) }}
                                        </p>
                                    </div>

                                    <div class="flex flex-col gap-2 items-end">
                                        <Badge
                                            :variant="entry.is_locked ? 'secondary' : 'success-soft'"
                                        >
                                            {{ entry.is_locked ? 'Locked' : 'Open' }}
                                        </Badge>

                                        <Link
                                            :href="entry.is_locked ? route('play.results', entry.group_code) : route('play.game', entry.group_code)"
                                        >
                                            <Button variant="primary" size="sm">
                                                {{ entry.is_locked ? 'View Results' : 'Play' }}
                                            </Button>
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Logo from '@/Components/Domain/Logo.vue';
import Icon from '@/Components/Base/Icon.vue';
import Button from '@/Components/Base/Button.vue';
import Badge from '@/Components/Base/Badge.vue';

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
