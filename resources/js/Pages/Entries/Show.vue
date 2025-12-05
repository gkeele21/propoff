<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { CheckCircleIcon, XCircleIcon } from '@heroicons/vue/24/outline';
import { computed } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import PageHeader from '@/Components/PageHeader.vue';

const props = defineProps({
    entry: Object,
});

// Convert percentage to number for display
const percentage = computed(() => parseFloat(props.entry.percentage) || 0);

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
    <Head :title="`Entry - ${entry.event.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader
                title="Entry Results"
                :crumbs="[
                    { label: 'Dashboard', href: route('dashboard') },
                    { label: 'My Entries', href: route('entries.index') },
                    { label: entry.event.name }
                ]"
            >
                <template #metadata>
                    <span class="font-medium text-gray-900">{{ entry.event.name }}</span>
                    <span class="text-gray-400 mx-2">•</span>
                    <span v-if="entry.group">{{ entry.group.name }}</span>
                </template>
                <template #actions>
                    <Link :href="route('groups.leaderboard', entry.group.id)">
                        <PrimaryButton>
                            View Leaderboard
                        </PrimaryButton>
                    </Link>
                </template>
            </PageHeader>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Score Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-8">
                        <div class="text-center">
                            <div class="inline-flex items-center justify-center w-24 h-24 rounded-full mb-4 bg-propoff-orange">
                                <span class="text-3xl font-bold text-white">
                                    {{ percentage.toFixed(1) }}%
                                </span>
                            </div>

                            <h3 class="text-2xl font-bold text-gray-900 mb-2">
                                {{ entry.total_score }} {{ entry.total_score === 1 ? 'Point' : 'Points' }}
                            </h3>

                            <div class="flex items-center justify-center gap-6 text-sm text-gray-600">
                                <span>Submitted: {{ new Date(entry.submitted_at).toLocaleDateString() }}</span>
                                <span>Max Possible: {{ entry.possible_points }} {{ entry.possible_points === 1 ? 'point' : 'points' }}</span>
                                <span v-if="entry.group">Group: {{ entry.group.name }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Question Results -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Your Answers</h3>

                        <div class="space-y-6">
                            <div
                                v-for="(question, index) in entry.group.group_questions"
                                :key="question.id"
                                class="border rounded-lg p-6"
                                :class="{
                                    'border-propoff-green/30 bg-propoff-green/10': entry.user_answers.find(a => a.group_question_id === question.id)?.is_correct,
                                    'border-propoff-red/30 bg-propoff-red/10': entry.user_answers.find(a => a.group_question_id === question.id) && !entry.user_answers.find(a => a.group_question_id === question.id)?.is_correct,
                                    'border-gray-200': !entry.user_answers.find(a => a.group_question_id === question.id),
                                }"
                            >
                                <div class="flex items-start gap-4">
                                    <!-- Icon -->
                                    <div class="flex-shrink-0 mt-1">
                                        <CheckCircleIcon
                                            v-if="entry.user_answers.find(a => a.group_question_id === question.id)?.is_correct"
                                            class="w-6 h-6 text-propoff-green"
                                        />
                                        <XCircleIcon
                                            v-else-if="entry.user_answers.find(a => a.group_question_id === question.id)"
                                            class="w-6 h-6 text-propoff-red"
                                        />
                                        <div v-else class="w-6 h-6 rounded-full bg-gray-300"></div>
                                    </div>

                                    <!-- Content -->
                                    <div class="flex-1">
                                        <div class="flex items-start justify-between mb-3">
                                            <h4 class="font-semibold text-gray-900">
                                                Question {{ index + 1 }}
                                            </h4>
                                            <span class="text-sm font-medium"
                                                :class="{
                                                    'text-propoff-green': entry.user_answers.find(a => a.group_question_id === question.id)?.is_correct,
                                                    'text-propoff-red': entry.user_answers.find(a => a.group_question_id === question.id) && !entry.user_answers.find(a => a.group_question_id === question.id)?.is_correct,
                                                    'text-gray-600': !entry.user_answers.find(a => a.group_question_id === question.id),
                                                }"
                                            >
                                                {{
                                                    entry.user_answers.find(a => a.group_question_id === question.id)?.points_earned || 0
                                                }} {{ entry.user_answers.find(a => a.group_question_id === question.id)?.points_earned === 1 ? 'point' : 'points' }}
                                            </span>
                                        </div>

                                        <p class="text-gray-700 mb-4">{{ question.question_text }}</p>

                                        <!-- Multiple Choice Options -->
                                        <div v-if="question.question_type === 'multiple_choice' && question.options" class="space-y-2 mb-3">
                                            <!-- No correct answer set yet -->
                                            <div v-if="!question.correct_answer" class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg mb-3">
                                                <p class="text-sm text-yellow-800">
                                                    <strong>Note:</strong> Correct answer not set yet. Results will be updated once grading is complete.
                                                </p>
                                            </div>

                                            <div
                                                v-for="(option, optIndex) in question.options"
                                                :key="optIndex"
                                                class="flex items-center justify-between p-3 border-2 rounded-lg"
                                                :class="{
                                                    'border-propoff-green bg-propoff-green/10': question.correct_answer && getOptionValue(option) === question.correct_answer,
                                                    'border-propoff-red bg-propoff-red/10': question.correct_answer && getOptionValue(option) === entry.user_answers.find(a => a.group_question_id === question.id)?.answer_text && getOptionValue(option) !== question.correct_answer,
                                                    'border-gray-200': !question.correct_answer || (getOptionValue(option) !== question.correct_answer && getOptionValue(option) !== entry.user_answers.find(a => a.group_question_id === question.id)?.answer_text),
                                                }"
                                            >
                                                <div class="flex items-center gap-3">
                                                    <span class="text-gray-900">{{ getOptionLabel(option) }}</span>
                                                    <span
                                                        v-if="getOptionValue(option) === entry.user_answers.find(a => a.group_question_id === question.id)?.answer_text"
                                                        class="text-xs font-semibold px-2 py-1 rounded"
                                                        :class="{
                                                            'bg-propoff-green/20 text-propoff-green': question.correct_answer && getOptionValue(option) === question.correct_answer,
                                                            'bg-propoff-red/20 text-propoff-red': question.correct_answer && getOptionValue(option) !== question.correct_answer,
                                                            'bg-gray-200 text-gray-700': !question.correct_answer,
                                                        }"
                                                    >
                                                        Your Answer
                                                    </span>
                                                    <span
                                                        v-if="question.correct_answer && getOptionValue(option) === question.correct_answer"
                                                        class="text-xs font-semibold px-2 py-1 rounded bg-propoff-green/20 text-propoff-green"
                                                    >
                                                        ✓ Correct
                                                    </span>
                                                </div>
                                                <div v-if="getOptionPoints(option) > 0" class="text-xs font-medium text-propoff-blue">
                                                    +{{ getOptionPoints(option) }} bonus {{ getOptionPoints(option) === 1 ? 'pt' : 'pts' }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Yes/No or True/False -->
                                        <div v-else-if="question.question_type === 'yes_no' || question.question_type === 'true_false'" class="space-y-2 mb-3">
                                            <!-- No correct answer set yet -->
                                            <div v-if="!question.correct_answer" class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg mb-3">
                                                <p class="text-sm text-yellow-800">
                                                    <strong>Note:</strong> Correct answer not set yet. Results will be updated once grading is complete.
                                                </p>
                                            </div>

                                            <div
                                                v-for="option in (question.question_type === 'yes_no' ? ['Yes', 'No'] : ['True', 'False'])"
                                                :key="option"
                                                class="flex items-center gap-3 p-3 border-2 rounded-lg"
                                                :class="{
                                                    'border-propoff-green bg-propoff-green/10': question.correct_answer && option === question.correct_answer,
                                                    'border-propoff-red bg-propoff-red/10': question.correct_answer && option === entry.user_answers.find(a => a.group_question_id === question.id)?.answer_text && option !== question.correct_answer,
                                                    'border-gray-200': !question.correct_answer || (option !== question.correct_answer && option !== entry.user_answers.find(a => a.group_question_id === question.id)?.answer_text),
                                                }"
                                            >
                                                <span class="text-gray-900">{{ option }}</span>
                                                <span
                                                    v-if="option === entry.user_answers.find(a => a.group_question_id === question.id)?.answer_text"
                                                    class="text-xs font-semibold px-2 py-1 rounded"
                                                    :class="{
                                                        'bg-propoff-green/20 text-propoff-green': question.correct_answer && option === question.correct_answer,
                                                        'bg-propoff-red/20 text-propoff-red': question.correct_answer && option !== question.correct_answer,
                                                        'bg-gray-200 text-gray-700': !question.correct_answer,
                                                    }"
                                                >
                                                    Your Answer
                                                </span>
                                                <span
                                                    v-if="question.correct_answer && option === question.correct_answer"
                                                    class="text-xs font-semibold px-2 py-1 rounded bg-propoff-green/20 text-propoff-green"
                                                >
                                                    ✓ Correct
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Text/Numeric answers -->
                                        <div v-else class="space-y-2">
                                            <!-- No correct answer set yet -->
                                            <div v-if="!question.correct_answer" class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg mb-3">
                                                <p class="text-sm text-yellow-800">
                                                    <strong>Note:</strong> Correct answer not set yet. Results will be updated once grading is complete.
                                                </p>
                                            </div>

                                            <div class="p-3 rounded-lg border-2"
                                                :class="{
                                                    'bg-propoff-green/10 border-propoff-green': entry.user_answers.find(a => a.group_question_id === question.id)?.is_correct,
                                                    'bg-propoff-red/10 border-propoff-red': question.correct_answer && entry.user_answers.find(a => a.group_question_id === question.id) && !entry.user_answers.find(a => a.group_question_id === question.id)?.is_correct,
                                                    'bg-gray-50 border-gray-200': !question.correct_answer,
                                                }"
                                            >
                                                <div class="flex items-center gap-2 mb-1">
                                                    <span class="text-sm font-medium text-gray-600">Your Answer:</span>
                                                    <span
                                                        v-if="entry.user_answers.find(a => a.group_question_id === question.id)?.is_correct"
                                                        class="text-xs font-semibold px-2 py-1 rounded bg-propoff-green/20 text-propoff-green"
                                                    >
                                                        ✓ Correct
                                                    </span>
                                                    <span
                                                        v-else-if="question.correct_answer && entry.user_answers.find(a => a.group_question_id === question.id)"
                                                        class="text-xs font-semibold px-2 py-1 rounded bg-propoff-red/20 text-propoff-red"
                                                    >
                                                        Incorrect
                                                    </span>
                                                </div>
                                                <span class="text-gray-900 font-medium">
                                                    {{
                                                        entry.user_answers.find(a => a.group_question_id === question.id)?.answer_text ||
                                                        'No answer provided'
                                                    }}
                                                </span>
                                            </div>

                                            <!-- Show correct answer if available and answer was wrong -->
                                            <div
                                                v-if="
                                                    entry.user_answers.find(a => a.group_question_id === question.id) &&
                                                    !entry.user_answers.find(a => a.group_question_id === question.id)?.is_correct &&
                                                    question.correct_answer
                                                "
                                                class="p-3 bg-propoff-green/10 rounded-lg border-2 border-propoff-green"
                                            >
                                                <div class="flex items-center gap-2">
                                                    <span class="text-sm font-medium text-propoff-green">Correct Answer:</span>
                                                    <span class="text-propoff-dark-green font-semibold">
                                                        {{ question.correct_answer }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
