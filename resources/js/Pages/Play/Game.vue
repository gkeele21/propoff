<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Button from '@/Components/Base/Button.vue';
import Badge from '@/Components/Base/Badge.vue';
import Card from '@/Components/Base/Card.vue';
import Icon from '@/Components/Base/Icon.vue';
import PageHeader from '@/Components/Base/PageHeader.vue';
import QuestionCard from '@/Components/Domain/QuestionCard.vue';
import Toast from '@/Components/Feedback/Toast.vue';

const props = defineProps({
    group: Object,
    event: Object,
    entry: Object,
    questions: Array,
    submittingFor: {
        type: Object,
        default: null,
    },
    isGuest: {
        type: Boolean,
        default: false,
    },
});

// Track selected answers locally
const selectedAnswers = ref({});

// Initialize with existing answers
props.questions.forEach((question) => {
    if (question.user_answer) {
        selectedAnswers.value[question.id] = question.user_answer;
    }
});

// Toast state
const showToast = ref(false);
const toastMessage = ref('');
const saving = ref(false);

// Computed values
const answeredCount = computed(() => {
    return Object.keys(selectedAnswers.value).filter(
        (key) => selectedAnswers.value[key] !== null && selectedAnswers.value[key] !== ''
    ).length;
});

const totalQuestions = computed(() => props.questions.length);

const totalPoints = computed(() => {
    return props.questions.reduce((sum, q) => sum + (q.points || 0), 0);
});

// Check if any questions have been graded (have correct_answer set)
const hasGradedQuestions = computed(() => {
    return props.questions.some(q => q.correct_answer !== null);
});

// Get ordinal suffix for rank display
const getOrdinal = (n) => {
    const s = ['th', 'st', 'nd', 'rd'];
    const v = n % 100;
    return n + (s[(v - 20) % 10] || s[v] || s[0]);
};

// Format rank display
const rankDisplay = computed(() => {
    if (!props.entry.rank) return null;
    return getOrdinal(props.entry.rank) + ' Place';
});

const breadcrumbs = computed(() => {
    if (props.submittingFor) {
        return [
            { label: 'Home', href: route('play.hub', { code: props.group.code }) },
            { label: props.group.name, href: route('groups.questions', { group: props.group.id }) },
            { label: 'Members', href: route('groups.members.index', { group: props.group.id }) },
        ];
    }
    return [
        { label: 'Home', href: route('play.hub', { code: props.group.code }) },
    ];
});

// Handle answer selection
const selectAnswer = (questionId, value) => {
    selectedAnswers.value[questionId] = value;
    // Auto-save after selection
    saveAnswers();
};

// Save answers
const saveAnswers = () => {
    if (saving.value) return;

    saving.value = true;

    const answers = Object.entries(selectedAnswers.value)
        .filter(([_, value]) => value !== null && value !== '')
        .map(([questionId, answerText]) => ({
            group_question_id: parseInt(questionId),
            answer_text: answerText,
        }));

    if (answers.length === 0) {
        saving.value = false;
        return;
    }

    const routeParams = { code: props.group.code };
    if (props.submittingFor) {
        routeParams.for_user = props.submittingFor.id;
    }

    router.post(
        route('play.save', routeParams),
        { answers, for_user: props.submittingFor?.id },
        {
            preserveScroll: true,
            onSuccess: () => {
                showToastMessage('Answers saved');
            },
            onError: () => {
                showToastMessage('Error saving answers');
            },
            onFinish: () => {
                saving.value = false;
            },
        }
    );
};

// Toast helper
const showToastMessage = (message) => {
    toastMessage.value = message;
    showToast.value = true;
    setTimeout(() => {
        showToast.value = false;
    }, 3000);
};
</script>

<template>
    <Head :title="submittingFor ? `Play for ${submittingFor.name} - ${group.name}` : `Play - ${group.name}`" />

    <AuthenticatedLayout :group="group">
        <template #header>
            <PageHeader :title="submittingFor ? `Play for ${submittingFor.name}` : group.name" :crumbs="breadcrumbs">
                <template #metadata>
                    <span class="text-warning font-medium">
                        {{ answeredCount }} of {{ totalQuestions }} answered
                    </span>
                </template>
                <template #actions>
                    <Link :href="route('play.leaderboard', { code: group.code })">
                        <Button variant="primary" icon="trophy" size="sm">
                            Leaderboard
                        </Button>
                    </Link>
                </template>
            </PageHeader>
        </template>

        <!-- Main Content -->
        <div class="max-w-4xl mx-auto px-6 py-8">
            <!-- Captain Submitting For Banner -->
            <div v-if="submittingFor" class="bg-info/10 border border-info/30 rounded-lg p-4 mb-6">
                <div class="flex items-center gap-3">
                    <Icon name="user-pen" class="text-info" size="lg" />
                    <div>
                        <div class="font-semibold text-info">Captain Mode</div>
                        <div class="text-sm text-muted">
                            You are submitting answers on behalf of <span class="font-semibold text-body">{{ submittingFor.name }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Locked Banner (only show if not graded yet) -->
            <div v-if="group.is_locked && !hasGradedQuestions" class="bg-warning/10 border border-warning/30 rounded-lg p-4 mb-6">
                <div class="flex items-center gap-3">
                    <Icon name="lock" class="text-warning" size="lg" />
                    <div>
                        <div class="font-semibold text-warning">Entries Locked</div>
                        <div class="text-sm text-muted">
                            The entry cutoff has passed. Answers can no longer be changed.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results Summary (shown when locked and graded) -->
            <div v-if="group.is_locked && hasGradedQuestions" class="bg-surface-elevated rounded-xl p-8 text-center mb-6">
                <div v-if="rankDisplay" class="text-5xl font-bold text-success mb-2">{{ rankDisplay }}</div>
                <div v-else class="text-5xl font-bold text-body mb-2">Results</div>
                <div class="text-2xl text-body">{{ entry.total_score }} points</div>
                <div v-if="entry.total_participants > 0" class="text-muted mt-1">
                    of {{ entry.total_participants }} participants
                </div>
            </div>

            <!-- Intro Card -->
            <Card v-if="event" class="mb-6" border-color="warning">
                <div>
                    <h2 class="text-xl font-bold text-body mb-2">{{ event.name }}</h2>
                    <p v-if="event.description" class="text-muted mb-4">{{ event.description }}</p>
                    <p class="text-sm text-muted">Make your picks below. Your answers auto-save as you go.</p>
                </div>
            </Card>

            <!-- Questions Section -->
            <Card :header-padding="false" header-bg-class="bg-surface-header">
                <template #header>
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <h2 class="text-xl font-bold text-body">Questions</h2>
                                <Badge variant="primary-soft" size="sm">{{ totalQuestions }}</Badge>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Questions List -->
                <div class="space-y-4 p-6">
                    <div
                        v-for="(question, index) in questions"
                        :key="question.id"
                        class="bg-surface-elevated border border-border rounded-lg overflow-hidden"
                    >
                        <!-- Question Header with Points Earned (shown when graded) -->
                        <div v-if="hasGradedQuestions" class="flex justify-between items-center p-4 border-b border-border">
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

                        <div class="p-6">
                            <QuestionCard
                                :model-value="selectedAnswers[question.id]"
                                @update:model-value="selectAnswer(question.id, $event)"
                                :question="question.question_text"
                                :options="question.options"
                                :points="question.points"
                                :question-number="index + 1"
                                :show-letters="true"
                                :show-header="!hasGradedQuestions"
                                :disabled="group.is_locked"
                                :correct-answer="question.correct_answer"
                                :show-results="!!question.correct_answer && !question.is_void"
                            />
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="questions.length === 0" class="text-center py-12 text-muted">
                        <Icon name="circle-question" size="3x" class="mb-4 text-subtle" />
                        <p class="text-lg mb-2">No questions yet</p>
                        <p class="text-sm">Check back later for questions to answer.</p>
                    </div>
                </div>
            </Card>

            <!-- Guest upsell (shown when locked and is guest) -->
            <div v-if="group.is_locked && isGuest" class="mt-8 bg-surface border border-border rounded-xl p-6 text-center">
                <div class="text-lg font-semibold text-body mb-2">Want to save your results?</div>
                <div class="text-muted mb-4">Create an account to track your history across events.</div>
                <Link :href="route('register')">
                    <Button variant="primary">Create an account</Button>
                </Link>
            </div>

        </div>

        <!-- Toast Notification -->
        <Toast
            :show="showToast"
            :message="toastMessage"
            @close="showToast = false"
        />
    </AuthenticatedLayout>
</template>
