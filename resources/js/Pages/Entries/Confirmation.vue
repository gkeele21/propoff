<template>
    <Head title="Entry Complete" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader
                title="Entry Complete"
                subtitle="Your entry has been submitted successfully"
                :crumbs="[
                    { label: 'Dashboard', href: route('dashboard') },
                    { label: 'My Entries', href: route('entries.index') },
                    { label: 'Confirmation' }
                ]"
            >
                <template #metadata>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success/10 text-success">
                        Submitted
                    </span>
                </template>
            </PageHeader>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <!-- Success Message -->
                <div class="bg-surface overflow-hidden shadow-sm sm:rounded-lg border border-border mb-6">
                    <div class="p-6 text-center">
                        <div class="w-16 h-16 bg-success/20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <CheckCircleIcon class="w-10 h-10 text-success" />
                        </div>
                        <h3 class="text-2xl font-bold text-body mb-2">
                            Entry Complete!
                        </h3>
                        <p class="text-muted mb-4">
                            Your answers for <strong>{{ event.name }}</strong> have been submitted successfully.
                        </p>

                        <!-- Score Display -->
                        <div class="inline-flex items-center gap-4 bg-primary/10 rounded-lg px-6 py-4">
                            <div>
                                <div class="text-4xl font-bold text-primary">
                                    {{ entry.percentage }}%
                                </div>
                                <div class="text-sm text-muted">
                                    {{ entry.total_score }} {{ entry.total_score === 1 ? 'point' : 'points' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Personal Link (for guests only) -->
                <div v-if="personalLink" class="bg-warning/10 border-2 border-warning/30 rounded-lg shadow-lg mb-6">
                    <div class="p-6">
                        <div class="flex items-start gap-3">
                            <ExclamationTriangleIcon class="w-8 h-8 text-warning flex-shrink-0" />
                            <div class="flex-1">
                                <h4 class="text-lg font-bold text-warning mb-2">
                                    ⭐ SAVE THIS LINK! ⭐
                                </h4>
                                <p class="text-warning mb-4">
                                    This is your personal results link. Save it now to view your results anytime without logging in!
                                </p>
                                
                                <div class="bg-surface-elevated border-2 border-warning rounded-lg p-4">
                                    <div class="flex items-center gap-2 mb-2">
                                        <input
                                            :value="personalLink"
                                            readonly
                                            class="flex-1 text-sm bg-surface border border-border rounded px-3 py-2 font-mono text-body"
                                        />
                                        <button
                                            @click="copyPersonalLink"
                                            class="px-4 py-2 bg-warning text-white font-semibold rounded hover:bg-warning/80 flex items-center gap-2"
                                        >
                                            <ClipboardDocumentIcon class="w-5 h-5" />
                                            {{ linkCopied ? 'Copied!' : 'Copy Link' }}
                                        </button>
                                    </div>

                                    <div class="text-xs text-muted space-y-1">
                                        <p>📧 Email this link to yourself</p>
                                        <p>📋 Save it in your notes</p>
                                        <p>🔖 Bookmark this page</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-surface overflow-hidden shadow-sm sm:rounded-lg border border-border">
                    <div class="p-6">
                        <h4 class="font-semibold text-body mb-4">What's Next?</h4>

                        <div class="space-y-3">
                            <Link
                                v-if="personalLink"
                                :href="personalLink"
                                class="block w-full px-4 py-3 bg-primary text-white text-center rounded-lg hover:bg-primary/80 font-semibold"
                            >
                                View My Results
                            </Link>

                            <Link
                                :href="route('groups.leaderboard', group.id)"
                                class="block w-full px-4 py-3 bg-success text-white text-center rounded-lg hover:bg-success/80 font-semibold"
                            >
                                View {{ group.name }} Leaderboard
                            </Link>

                            <Link
                                :href="route('dashboard')"
                                class="block w-full px-4 py-3 bg-surface-elevated text-body text-center rounded-lg hover:bg-surface-overlay font-semibold border border-border"
                            >
                                Back to Dashboard
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Reminder -->
                <div v-if="personalLink" class="mt-6 text-center text-sm text-muted">
                    <p>💡 Don't forget to save your personal results link above!</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    CheckCircleIcon,
    ExclamationTriangleIcon,
    ClipboardDocumentIcon
} from '@heroicons/vue/24/outline';
import PageHeader from '@/Components/PageHeader.vue';

const props = defineProps({
    entry: Object,
    event: Object,
    group: Object,
    personalLink: String,
});

const linkCopied = ref(false);

const copyPersonalLink = () => {
    navigator.clipboard.writeText(props.personalLink);
    linkCopied.value = true;
    setTimeout(() => {
        linkCopied.value = false;
    }, 3000);
};
</script>
