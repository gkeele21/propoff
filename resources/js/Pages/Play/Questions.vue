<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Button from '@/Components/Base/Button.vue';
import Card from '@/Components/Base/Card.vue';
import Icon from '@/Components/Base/Icon.vue';
import PageHeader from '@/Components/PageHeader.vue';
import QuestionCard from '@/Components/Domain/QuestionCard.vue';
import Toast from '@/Components/Feedback/Toast.vue';

const props = defineProps({
    group: Object,
    entry: Object,
    questions: Array,
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
const submitting = ref(false);

// Computed values
const answeredCount = computed(() => {
    return Object.keys(selectedAnswers.value).filter(
        (key) => selectedAnswers.value[key] !== null && selectedAnswers.value[key] !== ''
    ).length;
});

const totalQuestions = computed(() => props.questions.length);

const breadcrumbs = computed(() => [
    { label: props.group.name, href: route('play.hub', { code: props.group.code }) },
    { label: 'Play' },
]);

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

    router.post(
        route('play.save', { code: props.group.code }),
        { answers },
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

// Submit entry
const submitEntry = () => {
    if (submitting.value) return;

    submitting.value = true;

    // Save answers first, then submit
    const answers = Object.entries(selectedAnswers.value)
        .filter(([_, value]) => value !== null && value !== '')
        .map(([questionId, answerText]) => ({
            group_question_id: parseInt(questionId),
            answer_text: answerText,
        }));

    router.post(
        route('play.save', { code: props.group.code }),
        { answers },
        {
            preserveScroll: true,
            onSuccess: () => {
                // Now submit
                router.post(
                    route('play.submit', { code: props.group.code }),
                    {},
                    {
                        onError: () => {
                            showToastMessage('Error submitting entry');
                            submitting.value = false;
                        },
                    }
                );
            },
            onError: () => {
                showToastMessage('Error saving answers');
                submitting.value = false;
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
    <Head :title="`Play - ${group.name}`" />

    <AuthenticatedLayout :group="group">
        <template #header>
            <PageHeader :title="group.name" :crumbs="breadcrumbs">
                <template #metadata>
                    {{ answeredCount }} of {{ totalQuestions }} answered
                </template>
            </PageHeader>
        </template>

        <!-- Main Content -->
        <div class="max-w-4xl mx-auto px-6 py-8">
            <!-- Questions -->
            <div class="space-y-4">
                <div
                    v-for="(question, index) in questions"
                    :key="question.id"
                >
                    <QuestionCard
                        :model-value="selectedAnswers[question.id]"
                        @update:model-value="selectAnswer(question.id, $event)"
                        :question="question.question_text"
                        :options="question.options"
                        :points="question.points"
                        :question-number="index + 1"
                        :show-letters="true"
                    />
                </div>
            </div>

            <!-- Submit Section -->
            <Card class="mt-8" border-color="warning">
                <div class="flex justify-between items-center">
                    <div>
                        <div class="font-semibold text-warning">Ready to submit?</div>
                        <div class="text-sm text-muted">{{ answeredCount }} of {{ totalQuestions }} questions answered</div>
                    </div>
                    <Button
                        variant="primary"
                        :loading="submitting"
                        :disabled="answeredCount === 0"
                        @click="submitEntry"
                    >
                        Submit Final Answers
                    </Button>
                </div>
            </Card>
        </div>

        <!-- Toast Notification -->
        <Toast
            :show="showToast"
            :message="toastMessage"
            @close="showToast = false"
        />
    </AuthenticatedLayout>
</template>
