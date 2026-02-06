<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Badge from '@/Components/Base/Badge.vue';
import PageHeader from '@/Components/Base/PageHeader.vue';
import QuestionCard from '@/Components/Domain/QuestionCard.vue';

const props = defineProps({
    group: Object,
    entry: Object,
    questions: Array,
    isGuest: Boolean,
});

// Get ordinal suffix
const getOrdinal = (n) => {
    const s = ['th', 'st', 'nd', 'rd'];
    const v = n % 100;
    return n + (s[(v - 20) % 10] || s[v] || s[0]);
};

const breadcrumbs = computed(() => [
    { label: props.group.name, href: route('play.hub', { code: props.group.code }) },
    { label: 'My Results' },
]);

// Format rank display
const rankDisplay = computed(() => {
    if (!props.entry.rank) return null;
    return getOrdinal(props.entry.rank) + ' Place';
});
</script>

<template>
    <Head :title="`Results - ${group.name}`" />

    <AuthenticatedLayout :group="group">
        <template #header>
            <PageHeader title="My Results" :subtitle="group.name" :crumbs="breadcrumbs" />
        </template>

        <!-- Main Content -->
        <div class="max-w-4xl mx-auto px-6 py-8">
            <!-- Results Summary -->
            <div class="bg-surface-elevated rounded-xl p-8 text-center mb-8">
                <div v-if="rankDisplay" class="text-5xl font-bold text-success mb-2">{{ rankDisplay }}</div>
                <div v-else class="text-5xl font-bold text-body mb-2">Results</div>
                <div class="text-2xl text-body">{{ entry.total_score }} points</div>
                <div v-if="entry.total_participants > 0" class="text-muted mt-1">
                    of {{ entry.total_participants }} participants
                </div>
            </div>

            <!-- Question Results -->
            <div class="space-y-4">
                <div
                    v-for="(question, index) in questions"
                    :key="question.id"
                >
                    <!-- Question Header with Points Earned -->
                    <div class="bg-surface-elevated border border-border rounded-xl overflow-hidden">
                        <div class="flex justify-between items-center p-4 border-b border-border">
                            <div class="font-semibold text-body">{{ question.question_text }}</div>
                            <Badge
                                v-if="question.is_void"
                                variant="warning-soft"
                            >
                                Voided
                            </Badge>
                            <Badge
                                v-else-if="question.is_correct"
                                variant="success-soft"
                            >
                                +{{ question.points_earned }} {{ question.points_earned === 1 ? 'point' : 'points' }}
                            </Badge>
                            <Badge
                                v-else-if="question.correct_answer"
                                variant="danger-soft"
                            >
                                0 points
                            </Badge>
                            <Badge
                                v-else
                                variant="default"
                            >
                                Pending
                            </Badge>
                        </div>

                        <div class="p-4">
                            <QuestionCard
                                :model-value="question.user_answer"
                                :question="question.question_text"
                                :options="question.options"
                                :points="question.points"
                                :correct-answer="question.correct_answer"
                                :show-results="!!question.correct_answer && !question.is_void"
                                :show-header="false"
                                :disabled="true"
                                :show-letters="true"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Guest upsell -->
            <div v-if="isGuest" class="mt-8 bg-surface border border-border rounded-xl p-6 text-center">
                <div class="text-lg font-semibold text-body mb-2">Want to save your results?</div>
                <div class="text-muted mb-4">Create an account to track your history across events.</div>
                <Link :href="route('register')">
                    <button class="px-6 py-2 bg-primary text-white rounded-lg font-semibold hover:bg-primary-hover transition-colors">
                        Create an account
                    </button>
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
