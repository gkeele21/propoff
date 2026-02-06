<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Button from '@/Components/Base/Button.vue';
import Badge from '@/Components/Base/Badge.vue';
import Icon from '@/Components/Base/Icon.vue';
import TextField from '@/Components/Form/TextField.vue';
import Select from '@/Components/Form/Select.vue';
import Checkbox from '@/Components/Form/Checkbox.vue';
import PageHeader from '@/Components/Base/PageHeader.vue';

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

// Helper for question type badges
const getQuestionTypeBadge = (type) => {
    const variants = {
        multiple_choice: 'primary-soft',
        yes_no: 'success-soft',
        numeric: 'warning-soft',
        text: 'default',
    };
    return variants[type] || 'default';
};

// Helper to get options for multiple choice questions
const getMultipleChoiceOptions = (question) => {
    if (!question.options) return [];
    return question.options.map(option => ({
        value: typeof option === 'object' ? option.label : option,
        label: typeof option === 'object' ? option.label : option,
    }));
};

const yesNoOptions = [
    { value: 'Yes', label: 'Yes' },
    { value: 'No', label: 'No' },
];
</script>

<template>
    <Head :title="`Grading - ${event.title}`" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader
                title="Grading"
                subtitle="Grade entries and set answers for groups"
                :crumbs="[
                    { label: 'Events', href: route('admin.events.index') },
                    { label: event.title, href: route('admin.events.show', event.id) },
                    { label: 'Grading' }
                ]"
            >
                <template #metadata>
                    <span class="font-medium text-body">{{ event.title }}</span>
                    <span class="text-muted mx-2">•</span>
                    <span class="text-muted">{{ groups.length }} groups</span>
                </template>
                <template #actions>
                    <Button variant="muted" icon="file-arrow-down" @click="exportCSV">
                        Export CSV
                    </Button>
                    <Button variant="primary" icon="calculator" @click="calculateScores">
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
                            <Icon name="circle-info" class="text-primary" />
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
                <div class="bg-surface overflow-hidden shadow-sm sm:rounded-lg border border-border">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-body mb-4">Select Group</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <button
                                v-for="group in groups"
                                :key="group.id"
                                @click="selectGroup(group)"
                                class="px-4 py-3 text-sm font-medium rounded-lg border-2 transition"
                                :class="
                                    selectedGroup?.id === group.id
                                        ? 'border-primary bg-primary/10 text-primary'
                                        : 'border-border bg-surface text-body hover:bg-surface-elevated'
                                "
                            >
                                {{ group.name }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Questions List -->
                <div v-if="selectedGroup" class="bg-surface overflow-hidden shadow-sm sm:rounded-lg border border-border">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-body">
                                Questions for {{ selectedGroup.name }}
                            </h3>
                            <Button variant="ghost" size="sm" @click="exportDetailedCSV(selectedGroup.id)">
                                Export Detailed CSV
                            </Button>
                        </div>

                        <div class="space-y-4">
                            <div
                                v-for="question in questions"
                                :key="question.id"
                                class="border border-border rounded-lg p-4"
                            >
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2 mb-2">
                                            <Badge variant="default" size="sm">
                                                Q{{ question.display_order }}
                                            </Badge>
                                            <Badge :variant="getQuestionTypeBadge(question.question_type)" size="sm">
                                                {{ question.question_type }}
                                            </Badge>
                                            <span class="text-sm text-muted">{{ question.points }} {{ question.points === 1 ? 'point' : 'points' }}</span>
                                        </div>
                                        <p class="text-body mb-2">{{ question.question_text }}</p>

                                        <div
                                            v-if="getGroupAnswer(selectedGroup.id, question.id)"
                                            class="mt-3 p-3 bg-surface-elevated rounded"
                                        >
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <p class="text-sm font-medium text-muted">Correct Answer:</p>
                                                    <p class="text-body">
                                                        {{ getGroupAnswer(selectedGroup.id, question.id).correct_answer }}
                                                    </p>
                                                    <p
                                                        v-if="getGroupAnswer(selectedGroup.id, question.id).points_awarded !== null"
                                                        class="text-sm text-success mt-1"
                                                    >
                                                        Custom Points: {{ getGroupAnswer(selectedGroup.id, question.id).points_awarded }} {{ getGroupAnswer(selectedGroup.id, question.id).points_awarded === 1 ? 'point' : 'points' }}
                                                        <span class="text-muted">(default: {{ question.points }} {{ question.points === 1 ? 'point' : 'points' }})</span>
                                                    </p>
                                                    <Badge
                                                        v-if="getGroupAnswer(selectedGroup.id, question.id).is_void"
                                                        variant="danger-soft"
                                                        size="sm"
                                                        class="mt-1"
                                                    >
                                                        <Icon name="circle-xmark" size="xs" class="mr-1" />
                                                        Void
                                                    </Badge>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else class="mt-3 p-3 bg-warning/10 rounded border border-warning/30">
                                            <p class="text-sm text-warning">No answer set for this group</p>
                                        </div>

                                        <!-- Edit Form -->
                                        <div
                                            v-if="editingAnswer?.question.id === question.id && editingAnswer?.group.id === selectedGroup.id"
                                            class="mt-4 p-4 bg-primary/10 rounded-lg border border-primary/30"
                                        >
                                            <form @submit.prevent="saveAnswer(question)">
                                                <div class="mb-3">
                                                    <!-- Multiple Choice - Dropdown -->
                                                    <Select
                                                        v-if="question.question_type === 'multiple_choice'"
                                                        v-model="answerForm.correct_answer"
                                                        label="Correct Answer"
                                                        :options="getMultipleChoiceOptions(question)"
                                                        placeholder="Select correct answer..."
                                                        required
                                                    />

                                                    <!-- Yes/No - Dropdown -->
                                                    <Select
                                                        v-else-if="question.question_type === 'yes_no'"
                                                        v-model="answerForm.correct_answer"
                                                        label="Correct Answer"
                                                        :options="yesNoOptions"
                                                        placeholder="Select correct answer..."
                                                        required
                                                    />

                                                    <!-- Text/Numeric - Text Input -->
                                                    <TextField
                                                        v-else
                                                        v-model="answerForm.correct_answer"
                                                        label="Correct Answer"
                                                        placeholder="Enter correct answer..."
                                                        required
                                                    />
                                                </div>
                                                <div class="mb-3">
                                                    <TextField
                                                        v-model="answerForm.points_awarded"
                                                        type="number"
                                                        label="Points Awarded (Optional)"
                                                        :placeholder="`Leave blank for default (${question.points} ${question.points === 1 ? 'point' : 'points'})`"
                                                        hint="Set custom points for this answer (e.g., 3 for risky predictions, 1 for safe ones)."
                                                    />
                                                </div>
                                                <div class="mb-4">
                                                    <Checkbox
                                                        v-model="answerForm.is_void"
                                                        label="Mark as void (exclude from scoring)"
                                                    />
                                                </div>
                                                <div class="flex gap-2">
                                                    <Button
                                                        variant="primary"
                                                        type="submit"
                                                        :loading="answerForm.processing"
                                                    >
                                                        Save Answer
                                                    </Button>
                                                    <Button
                                                        variant="outline"
                                                        @click="editingAnswer = null"
                                                    >
                                                        Cancel
                                                    </Button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <div class="ml-4 flex flex-col gap-2">
                                        <Button variant="ghost" size="sm" @click="editAnswer(question, selectedGroup)">
                                            Set Answer
                                        </Button>
                                        <Button variant="ghost" size="sm" @click="toggleVoid(question, selectedGroup)">
                                            Toggle Void
                                        </Button>
                                    </div>
                                </div>
                            </div>

                            <p v-if="questions.length === 0" class="text-center text-muted py-8">
                                No questions in this event yet.
                            </p>
                        </div>
                    </div>
                </div>

                <div v-else class="bg-surface overflow-hidden shadow-sm sm:rounded-lg border border-border">
                    <div class="p-12 text-center text-muted">
                        Select a group above to set answers and manage grading
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
