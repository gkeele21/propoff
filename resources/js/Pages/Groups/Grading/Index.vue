<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import PageHeader from '@/Components/PageHeader.vue';

const props = defineProps({
    group: {
        type: Object,
        required: true,
    },
    questions: {
        type: Array,
        required: true,
    },
});

const answerForms = ref({});

// Initialize answer forms
props.questions.forEach(question => {
    answerForms.value[question.id] = useForm({
        correct_answer: question.answer?.correct_answer || '',
        points_awarded: question.answer?.points_awarded || null,
        is_void: question.answer?.is_void || false,
    });
});

const setAnswer = (questionId) => {
    answerForms.value[questionId].post(
        route('groups.grading.setAnswer', [props.group.id, questionId]),
        {
            preserveScroll: true,
            onSuccess: () => {
                // Optional: Show success message
            },
        }
    );
};

const toggleVoid = (questionId) => {
    answerForms.value[questionId].post(
        route('groups.grading.toggleVoid', [props.group.id, questionId]),
        {
            preserveScroll: true,
        }
    );
};

const getTypeLabel = (type) => {
    const labels = {
        multiple_choice: 'Multiple Choice',
        yes_no: 'Yes/No',
        numeric: 'Numeric',
        text: 'Text',
    };
    return labels[type] || type;
};
</script>

<template>
    <Head title="Grade Entries" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader
                title="Set Answers"
                :crumbs="[
                    { label: 'Dashboard', href: route('dashboard') },
                    { label: group.name, href: route('groups.show', group.id) },
                    { label: 'Set Answers' }
                ]"
            >
                <template #metadata>
                    <span class="font-medium text-gray-900">{{ group.name }}</span>
                    <span class="text-gray-400 mx-2">•</span>
                    <span>{{ group.event.name }}</span>
                </template>
            </PageHeader>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Info Banner -->
                <div
                    v-if="group.grading_source === 'admin'"
                    class="bg-propoff-orange/10 border border-propoff-orange/30 rounded-lg p-4 mb-6"
                >
                    <p class="text-sm text-propoff-orange">
                        ⚠️ This group uses <strong>Admin Grading</strong>. Scores will be calculated based on answers set by the admin, not captain answers.
                    </p>
                </div>

                <div
                    v-else
                    class="bg-propoff-blue/10 border border-propoff-blue/30 rounded-lg p-4 mb-6"
                >
                    <p class="text-sm text-propoff-blue">
                        ✓ This group uses <strong>Captain Grading</strong>. Set the correct answers below to grade entries in real-time.
                    </p>
                </div>

                <!-- Questions List -->
                <div class="space-y-6">
                    <div
                        v-for="question in questions"
                        :key="question.id"
                        class="bg-white overflow-hidden shadow-sm sm:rounded-lg"
                    >
                        <div class="p-6">
                            <!-- Question Header -->
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <span class="text-sm font-semibold text-gray-500">Q{{ question.order + 1 }}</span>
                                        <span
                                            :class="{
                                                'bg-propoff-blue/10 text-propoff-blue': question.question_type === 'multiple_choice',
                                                'bg-propoff-green/10 text-propoff-dark-green': question.question_type === 'yes_no',
                                                'bg-propoff-orange/10 text-propoff-orange': question.question_type === 'numeric',
                                                'bg-gray-100 text-gray-700': question.question_type === 'text'
                                            }"
                                            class="px-2 py-1 text-xs font-semibold rounded"
                                        >
                                            {{ getTypeLabel(question.question_type) }}
                                        </span>
                                        <span v-if="question.is_custom" class="px-2 py-1 text-xs font-semibold rounded bg-propoff-blue/10 text-propoff-blue">
                                            Custom
                                        </span>
                                        <span v-if="question.answer" class="px-2 py-1 text-xs font-semibold rounded bg-propoff-green/10 text-propoff-dark-green">
                                            Answer Set
                                        </span>
                                    </div>
                                    <p class="text-gray-900 font-medium mb-2">{{ question.question_text }}</p>
                                    <p class="text-sm text-gray-600">{{ question.points }} {{ question.points === 1 ? 'point' : 'points' }}</p>
                                </div>
                            </div>

                            <!-- Options (for multiple choice) -->
                            <div v-if="question.question_type === 'multiple_choice' && question.options" class="mb-4">
                                <p class="text-sm font-semibold text-gray-700 mb-2">Options:</p>
                                <div class="flex flex-wrap gap-2">
                                    <span
                                        v-for="(option, index) in question.options"
                                        :key="index"
                                        class="px-3 py-1 bg-gray-100 text-gray-700 rounded text-sm"
                                    >
                                        {{ typeof option === 'object' ? option.label : option }}
                                    </span>
                                </div>
                            </div>

                            <!-- Grading Form -->
                            <div v-if="group.grading_source === 'captain'" class="border-t pt-4 mt-4">
                                <form @submit.prevent="setAnswer(question.id)" class="space-y-4">
                                    <!-- Correct Answer -->
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                                            Correct Answer
                                        </label>

                                        <!-- Dropdown for Multiple Choice -->
                                        <select
                                            v-if="question.question_type === 'multiple_choice'"
                                            v-model="answerForms[question.id].correct_answer"
                                            class="w-full border-gray-300 focus:border-propoff-blue focus:ring-propoff-blue/50 rounded-md shadow-sm"
                                            required
                                        >
                                            <option value="">Select the correct answer...</option>
                                            <option
                                                v-for="(option, index) in question.options"
                                                :key="index"
                                                :value="typeof option === 'object' ? option.label : option"
                                            >
                                                {{ typeof option === 'object' ? option.label : option }}
                                            </option>
                                        </select>

                                        <!-- Dropdown for Yes/No -->
                                        <select
                                            v-else-if="question.question_type === 'yes_no'"
                                            v-model="answerForms[question.id].correct_answer"
                                            class="w-full border-gray-300 focus:border-propoff-blue focus:ring-propoff-blue/50 rounded-md shadow-sm"
                                            required
                                        >
                                            <option value="">Select the correct answer...</option>
                                            <option value="Yes">Yes</option>
                                            <option value="No">No</option>
                                        </select>

                                        <!-- Text input for Numeric and Text -->
                                        <input
                                            v-else
                                            v-model="answerForms[question.id].correct_answer"
                                            type="text"
                                            class="w-full border-gray-300 focus:border-propoff-blue focus:ring-propoff-blue/50 rounded-md shadow-sm"
                                            placeholder="Enter the correct answer"
                                            required
                                        />
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="flex gap-2">
                                        <button
                                            type="submit"
                                            :disabled="answerForms[question.id].processing"
                                            class="bg-propoff-blue hover:bg-propoff-blue/80 text-white px-4 py-2 rounded font-semibold disabled:opacity-50"
                                        >
                                            {{ question.answer ? 'Update Answer' : 'Set Answer' }}
                                        </button>

                                        <button
                                            v-if="question.answer"
                                            type="button"
                                            @click="toggleVoid(question.id)"
                                            class="bg-propoff-orange hover:bg-propoff-orange/80 text-white px-4 py-2 rounded font-semibold"
                                        >
                                            {{ question.answer.is_void ? 'Unvoid' : 'Void' }} Question
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Admin Grading Message -->
                            <div v-else class="border-t pt-4 mt-4">
                                <p class="text-sm text-gray-600">
                                    This group uses admin grading. The admin will set answers after the event.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="questions.length === 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-12 text-center">
                        <p class="text-gray-500 mb-4">No questions to grade yet.</p>
                        <Link
                            :href="route('groups.questions.index', group.id)"
                            class="text-propoff-blue hover:text-propoff-blue/80 font-semibold"
                        >
                            Manage Questions
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
