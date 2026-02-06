<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Button from '@/Components/Base/Button.vue';
import TextField from '@/Components/Form/TextField.vue';
import PageHeader from '@/Components/PageHeader.vue';

const props = defineProps({
    entry: Object,
});

const currentQuestionIndex = ref(0);
const answers = ref({});

// Load existing answers
props.entry.user_answers.forEach(answer => {
    answers.value[answer.group_question_id] = answer.answer_text;
});

const currentQuestion = computed(() => {
    return props.entry.group.group_questions[currentQuestionIndex.value];
});

const progress = computed(() => {
    const answered = Object.keys(answers.value).length;
    const total = props.entry.group.group_questions.length;
    return (answered / total) * 100;
});

const isLastQuestion = computed(() => {
    return currentQuestionIndex.value === props.entry.group.group_questions.length - 1;
});

const canGoNext = computed(() => {
    return currentQuestionIndex.value < props.entry.group.group_questions.length - 1;
});

const canGoPrevious = computed(() => {
    return currentQuestionIndex.value > 0;
});

const nextQuestion = () => {
    if (canGoNext.value) {
        currentQuestionIndex.value++;
    }
};

const previousQuestion = () => {
    if (canGoPrevious.value) {
        currentQuestionIndex.value--;
    }
};

const goToQuestion = (index) => {
    currentQuestionIndex.value = index;
};

const saveAnswers = () => {
    const answersArray = Object.entries(answers.value).map(([groupQuestionId, answerText]) => ({
        group_question_id: parseInt(groupQuestionId),
        answer_text: answerText,
    }));

    router.post(route('entries.saveAnswers', props.entry.id), {
        answers: answersArray,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            // Show success message
        },
    });
};

const submitForm = () => {
    if (confirm('Are you sure you want to submit? You won\'t be able to change your answers after submitting.')) {
        // Save answers first
        const answersArray = Object.entries(answers.value).map(([groupQuestionId, answerText]) => ({
            group_question_id: parseInt(groupQuestionId),
            answer_text: answerText,
        }));

        router.post(route('entries.saveAnswers', props.entry.id), {
            answers: answersArray,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                // Then submit and redirect to confirmation page
                router.post(route('entries.submit', props.entry.id), {}, {
                    onSuccess: () => {
                        router.get(route('entries.confirmation', props.entry.id));
                    }
                });
            },
        });
    }
};

// Helper functions to handle both old format (string) and new format (object with label/points)
const getOptionLabel = (option) => {
    if (typeof option === 'string') return option;
    return option.label || option;
};

const getOptionValue = (option) => {
    if (typeof option === 'string') return option;
    return option.label || option;
};

const getOptionPoints = (option) => {
    if (typeof option === 'string') return 0;
    return option.points || 0;
};
</script>

<template>
    <Head :title="`${entry.event.name} - Quiz`" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader
                :title="entry.event.name"
                subtitle="Answer all questions and submit your entry"
                :crumbs="[
                    { label: 'Dashboard', href: route('dashboard') },
                    { label: 'My Entries', href: route('entries.index') },
                    { label: 'Continue Entry' }
                ]"
            >
                <template #metadata>
                    <div class="w-64">
                        <div class="w-full bg-surface-elevated rounded-full h-2">
                            <div
                                class="bg-primary h-2 rounded-full transition-all duration-300"
                                :style="{ width: `${progress}%` }"
                            ></div>
                        </div>
                        <p class="mt-1 text-sm text-muted">
                            {{ Object.keys(answers).length }} of {{ entry.group.group_questions.length }} questions answered
                        </p>
                    </div>
                </template>
            </PageHeader>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="flex gap-6">
                    <!-- Question Navigation Sidebar -->
                    <div class="w-48 flex-shrink-0">
                        <div class="bg-surface rounded-lg shadow-sm p-4 sticky top-6 border border-border">
                            <h3 class="font-semibold text-body mb-3">Questions</h3>
                            <div class="space-y-2">
                                <button
                                    v-for="(question, index) in entry.group.group_questions"
                                    :key="question.id"
                                    @click="goToQuestion(index)"
                                    class="w-full text-left px-3 py-2 rounded transition"
                                    :class="{
                                        'bg-primary text-white': index === currentQuestionIndex,
                                        'bg-success/10 text-success': answers[question.id] && index !== currentQuestionIndex,
                                        'bg-surface-elevated text-body hover:bg-surface-overlay': !answers[question.id] && index !== currentQuestionIndex,
                                    }"
                                >
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium">Q{{ index + 1 }}</span>
                                        <span v-if="answers[question.id]" class="text-xs">✓</span>
                                    </div>
                                </button>
                            </div>

                            <div class="mt-4 pt-4 border-t border-border">
                                <button
                                    @click="saveAnswers"
                                    class="w-full px-3 py-2 text-sm text-body bg-surface-elevated hover:bg-surface-overlay rounded transition"
                                >
                                    Save Progress
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Main Question Area -->
                    <div class="flex-1">
                        <div class="bg-surface overflow-hidden shadow-sm sm:rounded-lg border border-border">
                            <div class="p-8">
                                <!-- Question -->
                                <div class="mb-8">
                                    <div class="flex items-start justify-between mb-4">
                                        <h3 class="text-2xl font-bold text-body">
                                            Question {{ currentQuestionIndex + 1 }}
                                        </h3>
                                        <span class="px-3 py-1 bg-primary/10 text-primary text-sm font-medium rounded">
                                            {{ currentQuestion.points }} {{ currentQuestion.points === 1 ? 'point' : 'points' }}
                                        </span>
                                    </div>
                                    <p class="text-lg text-body">{{ currentQuestion.question_text }}</p>
                                </div>

                                <!-- Answer Input -->
                                <div class="mb-8">
                                    <!-- Multiple Choice -->
                                    <div v-if="currentQuestion.question_type === 'multiple_choice' && currentQuestion.options">
                                        <div class="space-y-3">
                                            <label
                                                v-for="(option, index) in currentQuestion.options"
                                                :key="index"
                                                class="flex items-center justify-between p-4 border-2 rounded-lg cursor-pointer transition-all focus-within-glow"
                                                :class="{
                                                    'border-primary bg-primary/10 checked-glow': answers[currentQuestion.id] === getOptionValue(option),
                                                    'border-border bg-surface-inset hover:border-border-strong hover:bg-surface-overlay': answers[currentQuestion.id] !== getOptionValue(option),
                                                }"
                                            >
                                                <input
                                                    type="radio"
                                                    v-model="answers[currentQuestion.id]"
                                                    :value="getOptionValue(option)"
                                                    class="sr-only"
                                                />
                                                <div class="flex items-center flex-1">
                                                    <span class="text-body">{{ getOptionLabel(option) }}</span>
                                                </div>
                                                <div v-if="getOptionPoints(option) > 0" class="ml-4 flex items-center gap-1">
                                                    <span class="text-xs font-medium text-primary bg-primary/10 px-2 py-1 rounded">
                                                        +{{ getOptionPoints(option) }} bonus {{ getOptionPoints(option) === 1 ? 'pt' : 'pts' }}
                                                    </span>
                                                </div>
                                            </label>
                                        </div>
                                        <p class="text-xs text-muted mt-3">
                                            Base: {{ currentQuestion.points }} {{ currentQuestion.points === 1 ? 'pt' : 'pts' }} per question + any bonus shown
                                        </p>
                                    </div>

                                    <!-- Yes/No -->
                                    <div v-else-if="currentQuestion.question_type === 'yes_no'">
                                        <div class="space-y-3">
                                            <label
                                                class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all focus-within-glow"
                                                :class="{
                                                    'border-primary bg-primary/10 checked-glow': answers[currentQuestion.id] === 'Yes',
                                                    'border-border bg-surface-inset hover:border-border-strong hover:bg-surface-overlay': answers[currentQuestion.id] !== 'Yes',
                                                }"
                                            >
                                                <input
                                                    type="radio"
                                                    v-model="answers[currentQuestion.id]"
                                                    value="Yes"
                                                    class="sr-only"
                                                />
                                                <span class="text-body">Yes</span>
                                            </label>
                                            <label
                                                class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all focus-within-glow"
                                                :class="{
                                                    'border-primary bg-primary/10 checked-glow': answers[currentQuestion.id] === 'No',
                                                    'border-border bg-surface-inset hover:border-border-strong hover:bg-surface-overlay': answers[currentQuestion.id] !== 'No',
                                                }"
                                            >
                                                <input
                                                    type="radio"
                                                    v-model="answers[currentQuestion.id]"
                                                    value="No"
                                                    class="sr-only"
                                                />
                                                <span class="text-body">No</span>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- True/False -->
                                    <div v-else-if="currentQuestion.question_type === 'true_false'">
                                        <div class="space-y-3">
                                            <label
                                                class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all focus-within-glow"
                                                :class="{
                                                    'border-primary bg-primary/10 checked-glow': answers[currentQuestion.id] === 'True',
                                                    'border-border bg-surface-inset hover:border-border-strong hover:bg-surface-overlay': answers[currentQuestion.id] !== 'True',
                                                }"
                                            >
                                                <input
                                                    type="radio"
                                                    v-model="answers[currentQuestion.id]"
                                                    value="True"
                                                    class="sr-only"
                                                />
                                                <span class="text-body">True</span>
                                            </label>
                                            <label
                                                class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all focus-within-glow"
                                                :class="{
                                                    'border-primary bg-primary/10 checked-glow': answers[currentQuestion.id] === 'False',
                                                    'border-border bg-surface-inset hover:border-border-strong hover:bg-surface-overlay': answers[currentQuestion.id] !== 'False',
                                                }"
                                            >
                                                <input
                                                    type="radio"
                                                    v-model="answers[currentQuestion.id]"
                                                    value="False"
                                                    class="sr-only"
                                                />
                                                <span class="text-body">False</span>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Short Answer or Number -->
                                    <div v-else>
                                        <TextField
                                            v-model="answers[currentQuestion.id]"
                                            :type="currentQuestion.question_type === 'number' ? 'number' : 'text'"
                                            :placeholder="currentQuestion.question_type === 'number' ? 'Enter a number' : 'Enter your answer'"
                                        />
                                    </div>
                                </div>

                                <!-- Navigation -->
                                <div class="flex items-center justify-between">
                                    <button
                                        @click="previousQuestion"
                                        :disabled="!canGoPrevious"
                                        class="px-4 py-2 text-body bg-surface-elevated hover:bg-surface-overlay rounded disabled:opacity-50 disabled:cursor-not-allowed transition"
                                    >
                                        ← Previous
                                    </button>

                                    <div class="text-sm text-muted">
                                        Question {{ currentQuestionIndex + 1 }} of {{ entry.group.group_questions.length }}
                                    </div>

                                    <button
                                        v-if="!isLastQuestion"
                                        @click="nextQuestion"
                                        class="px-4 py-2 bg-primary text-white hover:bg-primary/80 rounded transition"
                                    >
                                        Next →
                                    </button>
                                    <!-- <PrimaryButton
                                        v-else
                                        @click="submitForm"
                                        class="px-6 py-2"
                                    >
                                        Submit Answers
                                    </PrimaryButton> -->
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button (always visible at bottom) -->
                        <div class="mt-6 bg-warning/10 border border-warning/30 rounded-lg p-4">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h4 class="font-medium text-warning">Ready to submit?</h4>
                                    <p class="text-sm text-warning mt-1">
                                        Make sure you've answered all questions before submitting.
                                    </p>
                                </div>
                                <Button
                                    variant="primary"
                                    @click="submitForm"
                                    class="ml-4"
                                >
                                    Submit Final Answers
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
