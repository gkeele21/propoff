<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Button from '@/Components/Base/Button.vue';
import TextField from '@/Components/Form/TextField.vue';
import PageHeader from '@/Components/PageHeader.vue';

const props = defineProps({
    group: Object,
    user: Object,
    entry: Object,
    questions: Array,
});

const answers = ref({});
const currentQuestionIndex = ref(0);

// Load existing answers if any
props.questions.forEach(question => {
    if (question.user_answer) {
        answers.value[question.id] = question.user_answer.answer_text;
    }
});

const currentQuestion = computed(() => {
    return props.questions[currentQuestionIndex.value];
});

const progress = computed(() => {
    const answered = Object.keys(answers.value).filter(key => answers.value[key]).length;
    const total = props.questions.length;
    return total > 0 ? (answered / total) * 100 : 0;
});

const isLastQuestion = computed(() => {
    return currentQuestionIndex.value === props.questions.length - 1;
});

const canGoNext = computed(() => {
    return currentQuestionIndex.value < props.questions.length - 1;
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

const submitForm = () => {
    // Check if all questions are answered
    const unanswered = props.questions.filter(q => !answers.value[q.id]);
    if (unanswered.length > 0) {
        if (!confirm(`You have ${unanswered.length} unanswered question(s). Do you want to submit anyway?`)) {
            return;
        }
    }

    if (confirm(`Are you sure you want to submit answers for ${props.user.name}?`)) {
        const answersArray = Object.entries(answers.value)
            .filter(([_, answer]) => answer) // Only include answered questions
            .map(([groupQuestionId, answerText]) => ({
                group_question_id: parseInt(groupQuestionId),
                answer_text: answerText,
            }));

        router.post(route('captain.entries.update', [props.group.id, props.user.id]), {
            answers: answersArray,
        });
    }
};

// Helper functions for options
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
    <Head :title="`Submit Entry for ${user.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader
                :title="`Submit Entry for ${user.name}`"
                subtitle="Answer questions on behalf of this user"
                :crumbs="[
                    { label: 'Dashboard', href: route('dashboard') },
                    { label: group.name, href: route('groups.show', group.id) },
                    { label: 'Members', href: route('groups.members.index', group.id) },
                    { label: user.name }
                ]"
            >
                <template #metadata>
                    <div class="flex items-center gap-4">
                        <div class="w-64">
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div
                                    class="bg-primary h-2 rounded-full transition-all duration-300"
                                    :style="{ width: `${progress}%` }"
                                ></div>
                            </div>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ Object.keys(answers).filter(k => answers[k]).length }} of {{ questions.length }} questions answered
                            </p>
                        </div>
                        <span v-if="user.is_guest" class="px-2 py-1 text-xs font-semibold rounded bg-blue-100 text-blue-700">
                            Guest User
                        </span>
                    </div>
                </template>
            </PageHeader>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Entry Cutoff Warning -->
                <div v-if="!group.accepting_entries" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <p class="text-sm text-red-800 font-semibold">
                        ⚠️ Entry cutoff has passed. Submissions are no longer allowed.
                    </p>
                </div>

                <div class="flex gap-6">
                    <!-- Question Navigation Sidebar -->
                    <div class="w-48 flex-shrink-0">
                        <div class="bg-white rounded-lg shadow-sm p-4 sticky top-6">
                            <h3 class="font-semibold text-gray-900 mb-3">Questions</h3>
                            <div class="space-y-2">
                                <button
                                    v-for="(question, index) in questions"
                                    :key="question.id"
                                    @click="goToQuestion(index)"
                                    class="w-full text-left px-3 py-2 rounded transition"
                                    :class="{
                                        'bg-primary text-white': index === currentQuestionIndex,
                                        'bg-success/10 text-success': answers[question.id] && index !== currentQuestionIndex,
                                        'bg-gray-100 text-gray-700 hover:bg-gray-200': !answers[question.id] && index !== currentQuestionIndex,
                                    }"
                                >
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium">Q{{ index + 1 }}</span>
                                        <span v-if="answers[question.id]" class="text-xs">✓</span>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Main Question Area -->
                    <div class="flex-1">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-8">
                                <!-- Question -->
                                <div class="mb-8">
                                    <div class="flex items-start justify-between mb-4">
                                        <h3 class="text-2xl font-bold text-gray-900">
                                            Question {{ currentQuestionIndex + 1 }}
                                        </h3>
                                        <span class="px-3 py-1 bg-primary/10 text-primary text-sm font-medium rounded">
                                            {{ currentQuestion.points }} {{ currentQuestion.points === 1 ? 'point' : 'points' }}
                                        </span>
                                    </div>
                                    <p class="text-lg text-gray-700">{{ currentQuestion.question_text }}</p>
                                </div>

                                <!-- Answer Input -->
                                <div class="mb-8">
                                    <!-- Multiple Choice -->
                                    <div v-if="currentQuestion.question_type === 'multiple_choice' && currentQuestion.options">
                                        <div class="space-y-3">
                                            <label
                                                v-for="(option, index) in currentQuestion.options"
                                                :key="index"
                                                class="flex items-center justify-between p-4 border-2 rounded-lg cursor-pointer transition"
                                                :class="{
                                                    'border-primary bg-primary/10': answers[currentQuestion.id] === getOptionValue(option),
                                                    'border-gray-200 hover:border-gray-300': answers[currentQuestion.id] !== getOptionValue(option),
                                                }"
                                            >
                                                <div class="flex items-center flex-1">
                                                    <input
                                                        type="radio"
                                                        v-model="answers[currentQuestion.id]"
                                                        :value="getOptionValue(option)"
                                                        class="h-4 w-4 text-primary focus:ring-primary/50"
                                                    />
                                                    <span class="ml-3 text-gray-900">{{ getOptionLabel(option) }}</span>
                                                </div>
                                                <div v-if="getOptionPoints(option) > 0" class="ml-4 flex items-center gap-1">
                                                    <span class="text-xs font-medium text-primary bg-primary/10 px-2 py-1 rounded">
                                                        +{{ getOptionPoints(option) }} bonus {{ getOptionPoints(option) === 1 ? 'pt' : 'pts' }}
                                                    </span>
                                                </div>
                                            </label>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-3">
                                            Base: {{ currentQuestion.points }} {{ currentQuestion.points === 1 ? 'pt' : 'pts' }} per question + any bonus shown
                                        </p>
                                    </div>

                                    <!-- Yes/No -->
                                    <div v-else-if="currentQuestion.question_type === 'yes_no'">
                                        <div class="space-y-3">
                                            <label
                                                class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition"
                                                :class="{
                                                    'border-primary bg-primary/10': answers[currentQuestion.id] === 'Yes',
                                                    'border-gray-200 hover:border-gray-300': answers[currentQuestion.id] !== 'Yes',
                                                }"
                                            >
                                                <input
                                                    type="radio"
                                                    v-model="answers[currentQuestion.id]"
                                                    value="Yes"
                                                    class="h-4 w-4 text-primary focus:ring-primary/50"
                                                />
                                                <span class="ml-3 text-gray-900">Yes</span>
                                            </label>
                                            <label
                                                class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition"
                                                :class="{
                                                    'border-primary bg-primary/10': answers[currentQuestion.id] === 'No',
                                                    'border-gray-200 hover:border-gray-300': answers[currentQuestion.id] !== 'No',
                                                }"
                                            >
                                                <input
                                                    type="radio"
                                                    v-model="answers[currentQuestion.id]"
                                                    value="No"
                                                    class="h-4 w-4 text-primary focus:ring-primary/50"
                                                />
                                                <span class="ml-3 text-gray-900">No</span>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- True/False -->
                                    <div v-else-if="currentQuestion.question_type === 'true_false'">
                                        <div class="space-y-3">
                                            <label
                                                class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition"
                                                :class="{
                                                    'border-primary bg-primary/10': answers[currentQuestion.id] === 'True',
                                                    'border-gray-200 hover:border-gray-300': answers[currentQuestion.id] !== 'True',
                                                }"
                                            >
                                                <input
                                                    type="radio"
                                                    v-model="answers[currentQuestion.id]"
                                                    value="True"
                                                    class="h-4 w-4 text-primary focus:ring-primary/50"
                                                />
                                                <span class="ml-3 text-gray-900">True</span>
                                            </label>
                                            <label
                                                class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition"
                                                :class="{
                                                    'border-primary bg-primary/10': answers[currentQuestion.id] === 'False',
                                                    'border-gray-200 hover:border-gray-300': answers[currentQuestion.id] !== 'False',
                                                }"
                                            >
                                                <input
                                                    type="radio"
                                                    v-model="answers[currentQuestion.id]"
                                                    value="False"
                                                    class="h-4 w-4 text-primary focus:ring-primary/50"
                                                />
                                                <span class="ml-3 text-gray-900">False</span>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Short Answer or Number -->
                                    <div v-else>
                                        <TextField
                                            v-model="answers[currentQuestion.id]"
                                            :type="currentQuestion.question_type === 'number' || currentQuestion.question_type === 'numeric' ? 'number' : 'text'"
                                            :placeholder="currentQuestion.question_type === 'number' || currentQuestion.question_type === 'numeric' ? 'Enter a number' : 'Enter your answer'"
                                        />
                                    </div>
                                </div>

                                <!-- Navigation -->
                                <div class="flex items-center justify-between">
                                    <button
                                        @click="previousQuestion"
                                        :disabled="!canGoPrevious"
                                        class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded disabled:opacity-50 disabled:cursor-not-allowed transition"
                                    >
                                        ← Previous
                                    </button>

                                    <div class="text-sm text-gray-600">
                                        Question {{ currentQuestionIndex + 1 }} of {{ questions.length }}
                                    </div>

                                    <button
                                        v-if="!isLastQuestion"
                                        @click="nextQuestion"
                                        class="px-4 py-2 bg-primary text-white hover:bg-primary/80 rounded transition"
                                    >
                                        Next →
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button (always visible at bottom) -->
                        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h4 class="font-medium text-blue-900">Captain Entry Submission</h4>
                                    <p class="text-sm text-blue-800 mt-1">
                                        You are submitting answers on behalf of <strong>{{ user.name }}</strong>.
                                        {{ user.is_guest ? 'This is a guest user.' : '' }}
                                    </p>
                                </div>
                                <Button
                                    variant="primary"
                                    @click="submitForm"
                                    class="ml-4"
                                    :disabled="!group.accepting_entries"
                                >
                                    Submit Entry
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
