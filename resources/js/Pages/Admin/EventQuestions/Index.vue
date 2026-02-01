<template>
    <Head :title="`Questions - ${event.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader
                title="Event Questions"
                :crumbs="[
                    { label: 'Admin Dashboard', href: route('admin.dashboard') },
                    { label: 'Events', href: route('admin.events.index') },
                    { label: event.name, href: route('admin.events.show', event.id) },
                    { label: 'Questions' }
                ]"
            >
                <!-- <template #metadata>
                    <div class="flex items-center space-x-4">
                        <span class="font-medium text-gray-900">{{ event.name }}</span>
                        <span class="text-gray-400">•</span>
                        <span>{{ questions.length }} questions</span>
                        <span class="text-gray-400">•</span>
                        <span>{{ totalPoints }} total {{ totalPoints === 1 ? 'point' : 'points' }}</span>
                    </div>
                </template> -->
                <template #actions>
                    <Link :href="route('admin.events.event-questions.create', event.id)">
                        <PrimaryButton>
                            <PlusIcon class="w-4 h-4 mr-2" />
                            Add Question
                        </PrimaryButton>
                    </Link>
                </template>
            </PageHeader>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Event Info -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-gray-900">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">Total Questions</p>
                                <p class="text-2xl font-bold">{{ questions.length }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Total Points</p>
                                <p class="text-2xl font-bold">{{ totalPoints }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Event Date</p>
                                <p class="text-lg font-semibold">{{ formatDate(event.event_date) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Status</p>
                                <span :class="statusClass(event.status)" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium">
                                    {{ event.status }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                
                <!-- Questions List -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div v-if="questions.length === 0" class="text-center py-12">
                            <!-- <DocumentTextIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" /> -->
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Questions Yet</h3>
                            <!-- <p class="text-gray-600 mb-4">Get started by creating your first question</p>
                            <Link
                                :href="route('admin.events.event-questions.create', event.id)"
                                class="inline-flex items-center px-4 py-2 bg-propoff-blue border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-propoff-blue/80"
                            >
                                <PlusIcon class="w-4 h-4 mr-2" />
                                Add Question
                            </Link> -->
                        </div>

                        <div v-else>
                            <!-- Reorder Notice -->
                            <div v-if="isReordering" class="bg-propoff-orange/10 border border-propoff-orange/30 rounded-lg p-4 mb-4">
                                <div class="flex items-start gap-3">
                                    <ExclamationTriangleIcon class="w-5 h-5 text-propoff-orange mt-0.5" />
                                    <div class="flex-1">
                                        <p class="text-sm text-propoff-orange">
                                            Drag and drop questions to reorder them. Click "Save Order" when done.
                                        </p>
                                    </div>
                                    <div class="flex gap-2">
                                        <button
                                            @click="saveOrder"
                                            :disabled="form.processing"
                                            class="px-3 py-1.5 bg-propoff-green text-white text-sm rounded hover:bg-propoff-dark-green disabled:opacity-50"
                                        >
                                            Save Order
                                        </button>
                                        <button
                                            @click="cancelReorder"
                                            class="px-3 py-1.5 bg-gray-600 text-white text-sm rounded hover:bg-gray-700"
                                        >
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold">Questions ({{ questions.length }})</h3>
                                <button
                                    v-if="!isReordering && questions.length > 1"
                                    @click="startReorder"
                                    class="inline-flex items-center px-3 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
                                >
                                    <ArrowsUpDownIcon class="w-4 h-4 mr-2" />
                                    Reorder Questions
                                </button>
                            </div>

                            <div class="space-y-6">
                                <div
                                    v-for="(question, index) in displayQuestions"
                                    :key="question.id"
                                    :draggable="isReordering"
                                    @dragstart="dragStart(index)"
                                    @dragover.prevent
                                    @drop="drop(index)"
                                    :class="{
                                        'cursor-move': isReordering,
                                        'border-l-4 border-l-propoff-blue': draggedIndex === index,
                                        'opacity-50': draggedIndex === index
                                    }"
                                    class="border border-gray-200 rounded-lg overflow-hidden hover:border-gray-300 transition bg-gray-50"
                                >
                                    <!-- Question Header -->
                                    <div class="bg-white px-6 py-4 border-b border-gray-200">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="flex items-center gap-3">
                                                <span class="inline-flex items-center justify-center w-10 h-10 bg-propoff-blue text-white font-bold rounded-full text-lg">
                                                    {{ question.order_number }}
                                                </span>
                                                <div>
                                                    <p class="text-lg font-semibold text-gray-900">{{ question.question_text }}</p>
                                                    <div class="flex items-center gap-2 mt-1">
                                                        <span :class="typeClass(question.type)" class="px-2 py-0.5 rounded text-xs font-medium">
                                                            {{ formatType(question.type) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2 flex-shrink-0">
                                                <span class="px-3 py-1.5 bg-propoff-blue/10 text-propoff-blue text-sm font-semibold rounded-lg">
                                                    {{ question.points }} {{ question.points === 1 ? 'point' : 'points' }}
                                                </span>
                                                <Link
                                                    :href="route('admin.events.event-questions.edit', [event.id, question.id])"
                                                    class="p-2 text-propoff-blue hover:bg-propoff-blue/10 rounded"
                                                    title="Edit"
                                                >
                                                    <PencilIcon class="w-5 h-5" />
                                                </Link>
                                                <button
                                                    @click="duplicateQuestion(question.id)"
                                                    class="p-2 text-propoff-green hover:bg-propoff-green/10 rounded"
                                                    title="Duplicate"
                                                >
                                                    <DocumentDuplicateIcon class="w-5 h-5" />
                                                </button>
                                                <button
                                                    @click="deleteQuestion(question.id)"
                                                    class="p-2 text-propoff-red hover:bg-propoff-red/10 rounded"
                                                    title="Delete"
                                                >
                                                    <TrashIcon class="w-5 h-5" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Options Preview (Multiple Choice) -->
                                    <div v-if="question.type === 'multiple_choice' && question.options" class="px-6 py-4">
                                        <div class="space-y-2">
                                            <div
                                                v-for="(option, optIndex) in question.options"
                                                :key="optIndex"
                                                class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-lg"
                                            >
                                                <div class="flex items-center gap-3">
                                                    <span class="w-6 h-6 flex items-center justify-center bg-gray-100 text-gray-600 text-sm font-medium rounded-full">
                                                        {{ String.fromCharCode(65 + optIndex) }}
                                                    </span>
                                                    <span class="text-gray-900">{{ getOptionLabel(option) }}</span>
                                                </div>
                                                <div v-if="getOptionPoints(option) > 0" class="flex items-center gap-1">
                                                    <span class="text-xs font-medium text-propoff-green bg-propoff-green/10 px-2 py-1 rounded">
                                                        +{{ getOptionPoints(option) }} bonus {{ getOptionPoints(option) === 1 ? 'pt' : 'pts' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-3 pl-1">
                                            Base: {{ question.points }} {{ question.points === 1 ? 'pt' : 'pts' }} for correct answer + any bonus shown
                                        </p>
                                    </div>

                                    <!-- Yes/No Preview -->
                                    <div v-else-if="question.type === 'yes_no'" class="px-6 py-4">
                                        <div class="flex gap-3">
                                            <div class="flex-1 p-3 bg-white border border-gray-200 rounded-lg text-center">
                                                <span class="text-gray-900 font-medium">Yes</span>
                                            </div>
                                            <div class="flex-1 p-3 bg-white border border-gray-200 rounded-lg text-center">
                                                <span class="text-gray-900 font-medium">No</span>
                                            </div>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-3 pl-1">
                                            {{ question.points }} {{ question.points === 1 ? 'pt' : 'pts' }} for correct answer
                                        </p>
                                    </div>

                                    <!-- Ranked Answers for AmericaSays -->
                                    <div v-else-if="question.type === 'ranked_answers'" class="px-6 py-4">
                                        <div v-if="question.event_answers && question.event_answers.length > 0" class="bg-white border border-propoff-blue/20 rounded-lg p-4">
                                            <p class="text-sm font-semibold text-propoff-blue mb-3">Ranked Answers ({{ question.event_answers.length }}):</p>
                                            <ol class="space-y-2">
                                                <li v-for="(answer, ansIndex) in question.event_answers" :key="answer.id"
                                                    class="flex items-center gap-3 p-2 bg-gray-50 rounded">
                                                    <span class="w-6 h-6 flex items-center justify-center bg-propoff-blue/10 text-propoff-blue text-sm font-bold rounded-full">
                                                        {{ ansIndex + 1 }}
                                                    </span>
                                                    <span class="text-gray-900">{{ answer.correct_answer }}</span>
                                                </li>
                                            </ol>
                                        </div>
                                        <div v-else class="bg-propoff-orange/10 border border-propoff-orange/30 rounded-lg p-4">
                                            <p class="text-sm text-propoff-orange">⚠️ No answers set yet</p>
                                        </div>
                                    </div>

                                    <!-- Numeric/Text Preview -->
                                    <div v-else class="px-6 py-4">
                                        <div class="p-3 bg-white border border-gray-200 rounded-lg">
                                            <span class="text-gray-400 italic">
                                                {{ question.type === 'numeric' ? 'Enter a number...' : 'Enter text answer...' }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-3 pl-1">
                                            {{ question.points }} {{ question.points === 1 ? 'pt' : 'pts' }} for correct answer
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bulk Import Modal (placeholder) -->
                <div v-if="showBulkImport" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
                    <div class="bg-white rounded-lg p-6 max-w-md w-full">
                        <h3 class="text-lg font-medium mb-4">Bulk Import Questions</h3>
                        <p class="text-gray-600 mb-4">This feature allows you to import questions from another event.</p>
                        <p class="text-sm text-propoff-orange mb-4">⚠️ This feature is coming soon!</p>
                        <button
                            @click="showBulkImport = false"
                            class="w-full px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    PlusIcon,
    ArrowLeftIcon,
    DocumentTextIcon,
    PencilIcon,
    TrashIcon,
    DocumentDuplicateIcon,
    InformationCircleIcon,
    DocumentPlusIcon,
    ArrowDownTrayIcon,
    CheckCircleIcon,
    ArrowsUpDownIcon,
    ExclamationTriangleIcon
} from '@heroicons/vue/24/outline';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import PageHeader from '@/Components/PageHeader.vue';

const props = defineProps({
    event: Object,
    questions: Array,
});

const form = useForm({
    event_questions: []
});

const isReordering = ref(false);
const draggedIndex = ref(null);
const displayQuestions = ref([...props.questions]);
const showBulkImport = ref(false);

const totalPoints = computed(() => {
    return props.questions.reduce((sum, q) => sum + q.points, 0);
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const statusClass = (status) => {
    const classes = {
        draft: 'bg-gray-100 text-gray-800',
        open: 'bg-propoff-green/20 text-propoff-dark-green',
        locked: 'bg-propoff-orange/20 text-propoff-orange',
        completed: 'bg-propoff-dark-green/20 text-propoff-dark-green',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const typeClass = (type) => {
    const classes = {
        multiple_choice: 'bg-propoff-blue/10 text-propoff-blue',
        yes_no: 'bg-propoff-green/10 text-propoff-dark-green',
        numeric: 'bg-propoff-orange/10 text-propoff-orange',
        text: 'bg-gray-100 text-gray-800',
        ranked_answers: 'bg-propoff-orange/10 text-propoff-orange',
    };
    return classes[type] || 'bg-gray-100 text-gray-800';
};

const formatType = (type) => {
    return type.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

// Helper functions to handle both old format (string) and new format (object with label/points)
const getOptionLabel = (option) => {
    if (typeof option === 'string') return option;
    return option.label || option;
};

const getOptionPoints = (option) => {
    if (typeof option === 'string') return 0;
    return option.points || 0;
};

const deleteQuestion = (questionId) => {
    if (confirm('Are you sure you want to delete this question? This action cannot be undone.')) {
        router.delete(route('admin.events.event-questions.destroy', [props.event.id, questionId]), {
            preserveScroll: true,
            onSuccess: () => {
                // Reload page to get updated questions list
                router.visit(route('admin.events.event-questions.index', props.event.id));
            }
        });
    }
};

const duplicateQuestion = (questionId) => {
    router.post(route('admin.events.event-questions.duplicate', [props.event.id, questionId]), {}, {
        preserveScroll: true,
        onSuccess: () => {
            // Reload page to get updated questions list
            router.visit(route('admin.events.event-questions.index', props.event.id));
        }
    });
};

const startReorder = () => {
    isReordering.value = true;
    displayQuestions.value = [...props.questions];
};

const cancelReorder = () => {
    isReordering.value = false;
    displayQuestions.value = [...props.questions];
    draggedIndex.value = null;
};

const dragStart = (index) => {
    draggedIndex.value = index;
};

const drop = (dropIndex) => {
    if (draggedIndex.value === null || draggedIndex.value === dropIndex) return;
    
    const items = [...displayQuestions.value];
    const draggedItem = items[draggedIndex.value];
    items.splice(draggedIndex.value, 1);
    items.splice(dropIndex, 0, draggedItem);
    
    displayQuestions.value = items;
    draggedIndex.value = null;
};

const saveOrder = () => {
    form.event_questions = displayQuestions.value.map((q, index) => ({
        id: q.id,
        order: index + 1  // Start at 1, not 0
    }));

    form.post(route('admin.events.event-questions.reorder', props.event.id), {
        preserveScroll: true,
        onSuccess: () => {
            isReordering.value = false;
            // Reload to get updated data
            router.visit(route('admin.events.event-questions.index', props.event.id));
        }
    });
};
</script>