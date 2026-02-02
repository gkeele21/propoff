<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    CalculatorIcon,
    DocumentArrowDownIcon,
    CheckCircleIcon,
    XCircleIcon,
} from '@heroicons/vue/24/outline';
import Button from '@/Components/Base/Button.vue';
import PageHeader from '@/Components/PageHeader.vue';

const props = defineProps({
    event: Object,
    questions: Array,
    groups: Array,
    groupAnswers: Object,
});

const selectedGroup = ref(null);
const editingAnswer = ref(null);

const answerForm = useForm({
    group_id: null,
    correct_answer: '',
    points_awarded: null,
    is_void: false,
});

const selectGroup = (group) => {
    selectedGroup.value = group;
    editingAnswer.value = null;
};

const getGroupAnswer = (groupId, questionId) => {
    const groupData = props.groupAnswers[groupId];
    if (!groupData) return null;
    return groupData.find(answer => answer.question_id === questionId);
};

const editAnswer = (question, group) => {
    const existing = getGroupAnswer(group.id, question.id);
    editingAnswer.value = { question, group };
    answerForm.group_id = group.id;
    answerForm.correct_answer = existing?.correct_answer || '';
    answerForm.points_awarded = existing?.points_awarded || null;
    answerForm.is_void = existing?.is_void || false;
};

const saveAnswer = (question) => {
    answerForm.post(
        route('admin.events.grading.setAnswer', {
            event: props.event.id,
            question: question.id
        }),
        {
            onSuccess: () => {
                editingAnswer.value = null;
                answerForm.reset();
            },
        }
    );
};

const toggleVoid = (question, group) => {
    router.post(
        route('admin.events.grading.toggleVoid', {
            event: props.event.id,
            question: question.id,
            group: group.id
        })
    );
};

const calculateScores = () => {
    if (confirm('This will recalculate scores for all entries in this event. Continue?')) {
        router.post(route('admin.events.grading.calculateScores', props.event.id));
    }
};

const exportCSV = () => {
    window.location.href = route('admin.events.grading.exportCSV', props.event.id);
};

const exportDetailedCSV = (groupId = null) => {
    if (groupId) {
        window.location.href = route('admin.events.grading.exportDetailedCSVByGroup', {
            event: props.event.id,
            group: groupId
        });
    } else {
        window.location.href = route('admin.events.grading.exportDetailedCSV', props.event.id);
    }
};
</script>

<template>
    <Head :title="`Grading - ${event.title}`" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader
                title="Grading"
                subtitle="Grade entries and set answers for groups"
                :crumbs="[
                    { label: 'Admin Dashboard', href: route('admin.dashboard') },
                    { label: 'Events', href: route('admin.events.index') },
                    { label: event.title, href: route('admin.events.show', event.id) },
                    { label: 'Grading' }
                ]"
            >
                <template #metadata>
                    <span class="font-medium text-gray-900">{{ event.title }}</span>
                    <span class="text-gray-400 mx-2">•</span>
                    <span>{{ groups.length }} groups</span>
                </template>
                <template #actions>
                    <button
                        @click="exportCSV"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50"
                    >
                        <DocumentArrowDownIcon class="w-4 h-4 mr-2" />
                        Export CSV
                    </button>
                    <Button variant="primary" @click="calculateScores">
                        <CalculatorIcon class="w-4 h-4 mr-2" />
                        Calculate Scores
                    </Button>
                </template>
            </PageHeader>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Instructions -->
                <div class="bg-primary/10 border-l-4 border-primary/30 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-primary" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-primary">
                                Set group-specific correct answers for each question. Questions can have different answers for different groups.
                                Mark questions as "void" to exclude them from scoring for specific groups.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Group Selection -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Select Group</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <button
                                v-for="group in groups"
                                :key="group.id"
                                @click="selectGroup(group)"
                                class="px-4 py-3 text-sm font-medium rounded-lg border-2 transition"
                                :class="
                                    selectedGroup?.id === group.id
                                        ? 'border-primary bg-primary/10 text-primary'
                                        : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50'
                                "
                            >
                                {{ group.name }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Questions List -->
                <div v-if="selectedGroup" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">
                                Questions for {{ selectedGroup.name }}
                            </h3>
                            <button
                                @click="exportDetailedCSV(selectedGroup.id)"
                                class="text-sm text-primary hover:text-primary/80"
                            >
                                Export Detailed CSV
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div
                                v-for="question in questions"
                                :key="question.id"
                                class="border rounded-lg p-4"
                            >
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2 mb-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                Q{{ question.display_order }}
                                            </span>
                                            <span
                                                :class="{
                                                    'bg-primary/10 text-primary': question.question_type === 'multiple_choice',
                                                    'bg-success/10 text-success': question.question_type === 'yes_no',
                                                    'bg-warning/10 text-warning': question.question_type === 'numeric',
                                                    'bg-gray-100 text-gray-700': question.question_type === 'text'
                                                }"
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                            >
                                                {{ question.question_type }}
                                            </span>
                                            <span class="text-sm text-gray-500">{{ question.points }} {{ question.points === 1 ? 'point' : 'points' }}</span>
                                        </div>
                                        <p class="text-gray-900 mb-2">{{ question.question_text }}</p>

                                        <div
                                            v-if="getGroupAnswer(selectedGroup.id, question.id)"
                                            class="mt-3 p-3 bg-gray-50 rounded"
                                        >
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-700">Correct Answer:</p>
                                                    <p class="text-gray-900">
                                                        {{ getGroupAnswer(selectedGroup.id, question.id).correct_answer }}
                                                    </p>
                                                    <p
                                                        v-if="getGroupAnswer(selectedGroup.id, question.id).points_awarded !== null"
                                                        class="text-sm text-success mt-1"
                                                    >
                                                        Custom Points: {{ getGroupAnswer(selectedGroup.id, question.id).points_awarded }} {{ getGroupAnswer(selectedGroup.id, question.id).points_awarded === 1 ? 'point' : 'points' }}
                                                        <span class="text-gray-500">(default: {{ question.points }} {{ question.points === 1 ? 'point' : 'points' }})</span>
                                                    </p>
                                                    <span
                                                        v-if="getGroupAnswer(selectedGroup.id, question.id).is_void"
                                                        class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-danger/10 text-danger mt-1"
                                                    >
                                                        <XCircleIcon class="w-3 h-3 mr-1" />
                                                        Void
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else class="mt-3 p-3 bg-yellow-50 rounded">
                                            <p class="text-sm text-warning">No answer set for this group</p>
                                        </div>

                                        <!-- Edit Form -->
                                        <div
                                            v-if="editingAnswer?.question.id === question.id && editingAnswer?.group.id === selectedGroup.id"
                                            class="mt-4 p-4 bg-primary/10 rounded-lg"
                                        >
                                            <form @submit.prevent="saveAnswer(question)">
                                                <div class="mb-3">
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                                        Correct Answer
                                                    </label>

                                                    <!-- Multiple Choice - Dropdown -->
                                                    <select
                                                        v-if="question.question_type === 'multiple_choice'"
                                                        v-model="answerForm.correct_answer"
                                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary focus:ring-primary/50"
                                                        required
                                                    >
                                                        <option value="">Select correct answer...</option>
                                                        <option
                                                            v-for="(option, index) in question.options"
                                                            :key="index"
                                                            :value="typeof option === 'object' ? option.label : option"
                                                        >
                                                            {{ typeof option === 'object' ? option.label : option }}
                                                        </option>
                                                    </select>

                                                    <!-- Yes/No - Dropdown -->
                                                    <select
                                                        v-else-if="question.question_type === 'yes_no'"
                                                        v-model="answerForm.correct_answer"
                                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary focus:ring-primary/50"
                                                        required
                                                    >
                                                        <option value="">Select correct answer...</option>
                                                        <option value="Yes">Yes</option>
                                                        <option value="No">No</option>
                                                    </select>

                                                    <!-- Text/Numeric - Text Input -->
                                                    <input
                                                        v-else
                                                        v-model="answerForm.correct_answer"
                                                        type="text"
                                                        required
                                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary focus:ring-primary/50"
                                                        placeholder="Enter correct answer..."
                                                    />
                                                </div>
                                                <div class="mb-3">
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                                        Points Awarded (Optional)
                                                    </label>
                                                    <input
                                                        v-model="answerForm.points_awarded"
                                                        type="number"
                                                        min="0"
                                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary focus:ring-primary/50"
                                                        :placeholder="`Leave blank for default (${question.points} ${question.points === 1 ? 'point' : 'points'})`"
                                                    />
                                                    <p class="text-xs text-gray-500 mt-1">
                                                        Set custom points for this answer (e.g., 3 for risky predictions, 1 for safe ones). Leave blank to use default {{ question.points }} {{ question.points === 1 ? 'point' : 'points' }}.
                                                    </p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="flex items-center">
                                                        <input
                                                            v-model="answerForm.is_void"
                                                            type="checkbox"
                                                            class="rounded border-gray-300 text-primary shadow-sm focus:border-primary focus:ring focus:ring-primary/50 focus:ring-opacity-50"
                                                        />
                                                        <span class="ml-2 text-sm text-gray-700">
                                                            Mark as void (exclude from scoring)
                                                        </span>
                                                    </label>
                                                </div>
                                                <div class="flex space-x-2">
                                                    <button
                                                        type="submit"
                                                        :disabled="answerForm.processing"
                                                        class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-md hover:bg-primary/80"
                                                    >
                                                        Save Answer
                                                    </button>
                                                    <button
                                                        type="button"
                                                        @click="editingAnswer = null"
                                                        class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300"
                                                    >
                                                        Cancel
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <div class="ml-4 flex flex-col space-y-2">
                                        <button
                                            @click="editAnswer(question, selectedGroup)"
                                            class="text-sm text-primary hover:text-primary/80"
                                        >
                                            Set Answer
                                        </button>
                                        <button
                                            @click="toggleVoid(question, selectedGroup)"
                                            class="text-sm text-gray-600 hover:text-gray-900"
                                        >
                                            Toggle Void
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <p v-if="questions.length === 0" class="text-center text-gray-500 py-8">
                                No questions in this event yet.
                            </p>
                        </div>
                    </div>
                </div>

                <div v-else class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-12 text-center text-gray-500">
                        Select a group above to set answers and manage grading
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
