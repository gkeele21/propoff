<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Button from '@/Components/Base/Button.vue';
import Badge from '@/Components/Base/Badge.vue';
import Card from '@/Components/Base/Card.vue';
import Icon from '@/Components/Base/Icon.vue';
import Dropdown from '@/Components/Form/Dropdown.vue';
import QuestionCard from '@/Components/Domain/QuestionCard.vue';
import QuestionModal from '@/Components/Domain/QuestionModal.vue';
import Confirm from '@/Components/Feedback/Confirm.vue';
import Toast from '@/Components/Feedback/Toast.vue';

const props = defineProps({
    event: {
        type: Object,
        required: true,
    },
    questions: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        default: () => ({}),
    },
});

// Format date helper
const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
};

// Question management
const showAddEditModal = ref(false);
const editingQuestion = ref(null);
const selectedAnswers = ref({}); // Track selected answers per question
const showDeleteConfirm = ref(false);
const questionToDelete = ref(null);
const toastMessage = ref('');
const showToast = ref(false);

// Open modal for adding new question
const openAddModal = () => {
    editingQuestion.value = null;
    showAddEditModal.value = true;
};

// Open modal for editing question
const openEditModal = (question) => {
    editingQuestion.value = question;
    showAddEditModal.value = true;
};

// Close modal
const closeModal = () => {
    showAddEditModal.value = false;
    editingQuestion.value = null;
};

// Navigate to import page
const navigateToImport = () => {
    router.visit(route('admin.events.import-questions', props.event.id));
};

// Handle answer selection
const selectAnswer = (questionId, answerValue) => {
    selectedAnswers.value[questionId] = answerValue;
};

// Check if answer is selected for question
const hasSelectedAnswer = (questionId) => {
    return selectedAnswers.value[questionId] !== undefined;
};

// Save answer for question
const saveAnswer = (question) => {
    const answer = selectedAnswers.value[question.id];
    if (!answer) return;

    router.post(
        route('admin.events.questions.set-answer', [props.event.id, question.id]),
        { answer },
        {
            onSuccess: () => {
                delete selectedAnswers.value[question.id];
                showToastMessage('Answer saved and scores calculated');
            },
            onError: () => {
                showToastMessage('Error saving answer');
            },
        }
    );
};

// Duplicate question
const duplicateQuestion = (question) => {
    router.post(
        route('admin.events.event-questions.duplicate', [props.event.id, question.id]),
        {},
        {
            onSuccess: () => {
                showToastMessage('Question duplicated successfully');
            },
        }
    );
};

// Toggle void for question
const toggleVoid = (question) => {
    router.post(
        route('admin.events.event-answers.toggleVoid', [props.event.id, question.id]),
        {},
        {
            onSuccess: () => {
                showToastMessage(question.is_void ? 'Question unvoided' : 'Question voided');
            },
        }
    );
};

// Delete question (show confirmation)
const confirmDelete = (question) => {
    questionToDelete.value = question;
    showDeleteConfirm.value = true;
};

// Execute delete
const deleteQuestion = () => {
    if (!questionToDelete.value) return;

    router.delete(
        route('admin.events.event-questions.destroy', [props.event.id, questionToDelete.value.id]),
        {
            onSuccess: () => {
                showToastMessage('Question deleted successfully');
                questionToDelete.value = null;
                showDeleteConfirm.value = false;
            },
        }
    );
};

// Drag and drop reordering
const draggedQuestion = ref(null);

const handleDragStart = (question) => {
    draggedQuestion.value = question;
};

const handleDragOver = (e) => {
    e.preventDefault();
};

const handleDrop = (targetQuestion) => {
    if (!draggedQuestion.value || draggedQuestion.value.id === targetQuestion.id) {
        return;
    }

    // Reorder on backend
    router.post(
        route('admin.events.event-questions.reorder', props.event.id),
        {
            question_id: draggedQuestion.value.id,
            new_order: targetQuestion.display_order,
        },
        {
            onSuccess: () => {
                showToastMessage('Questions reordered');
            },
        }
    );

    draggedQuestion.value = null;
};

// Toast helper
const showToastMessage = (message) => {
    toastMessage.value = message;
    showToast.value = true;
    setTimeout(() => {
        showToast.value = false;
    }, 3000);
};

// Get status badge variant
const getStatusVariant = (status) => {
    const variants = {
        draft: 'default',
        open: 'success',
        locked: 'warning',
        completed: 'success',
    };
    return variants[status] || 'default';
};
</script>

<template>
    <Head :title="event.name" />

    <AuthenticatedLayout>
        <!-- Compact Event Header -->
        <div class="bg-white shadow-sm mb-8">
            <div class="max-w-7xl mx-auto px-6 py-6">
                <div class="flex justify-between items-start gap-6">
                    <!-- Left: Title, Status, Meta Info -->
                    <div class="flex-1">
                        <div class="flex items-center gap-3 flex-wrap mb-2">
                            <h1 class="text-2xl font-bold text-body">{{ event.name }}</h1>
                            <Badge :variant="getStatusVariant(event.status)">
                                {{ event.status }}
                            </Badge>
                            <span class="text-subtle">
                                📅 {{ formatDate(event.event_date) }}
                            </span>
                            <span class="text-subtle">
                                👥 {{ stats.total_entries || 0 }} entries
                            </span>
                        </div>
                        <p class="text-muted">{{ event.description }}</p>
                    </div>

                    <!-- Right: Action Buttons -->
                    <div class="flex gap-2">
                        <Link :href="route('admin.events.captain-invitations.index', event.id)">
                            <Button variant="accent" size="sm">
                                Manage Invitations
                            </Button>
                        </Link>
                        <Link :href="route('admin.events.edit', event.id)">
                            <Button variant="primary">
                                <Icon name="pencil" class="mr-2" size="sm" />
                                Edit Event
                            </Button>
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-6 pb-12">
            <!-- Questions Section -->
            <Card :header-padding="false">
                <template #header>
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <h2 class="text-xl font-bold text-body">Questions</h2>
                                <Badge variant="primary-soft" size="sm">{{ questions.length }}</Badge>
                            </div>
                            <div class="flex gap-2">
                                <Button variant="secondary" size="sm" @click="navigateToImport">
                                    <Icon name="file-import" class="mr-2" size="sm" />
                                    Import from Template
                                </Button>
                                <Button variant="primary" size="sm" @click="openAddModal">
                                    <Icon name="plus" class="mr-2" size="sm" />
                                    Add Custom Question
                                </Button>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Questions List -->
                <div class="space-y-6 p-6">
                    <div
                        v-for="(question, index) in questions"
                        :key="question.id"
                        class="bg-white border-2 border-border rounded-lg p-6 transition-opacity"
                        :class="{ 'opacity-50': draggedQuestion?.id === question.id }"
                        draggable="true"
                        @dragstart="handleDragStart(question)"
                        @dragover="handleDragOver"
                        @drop="handleDrop(question)"
                    >
                        <!-- Question Header -->
                        <div class="flex items-start gap-4 mb-4">
                            <!-- Drag Handle -->
                            <div class="flex-shrink-0 cursor-move text-muted hover:text-body" title="Drag to reorder">
                                <Icon name="grip-vertical" size="lg" />
                            </div>

                            <!-- Question Info -->
                            <div class="flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-lg font-semibold text-body">{{ index + 1 }}. {{ question.question_text }}</h3>
                                    <Badge variant="primary-soft" size="sm">Multiple Choice</Badge>
                                    <Badge v-if="question.is_void" variant="danger" size="sm">Voided</Badge>
                                    <span class="text-sm text-subtle">{{ question.points }} pts</span>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center gap-2">
                                <Button variant="ghost" size="sm" @click="openEditModal(question)">
                                    <Icon name="pencil" />
                                </Button>
                                <Dropdown>
                                    <template #trigger>
                                        <Button variant="ghost" size="sm">
                                            <Icon name="ellipsis-vertical" />
                                        </Button>
                                    </template>
                                    <template #content>
                                        <button
                                            @click="duplicateQuestion(question)"
                                            class="w-full text-left px-4 py-2 text-sm hover:bg-surface flex items-center gap-2"
                                        >
                                            <Icon name="copy" size="sm" />
                                            Duplicate
                                        </button>
                                        <button
                                            @click="toggleVoid(question)"
                                            class="w-full text-left px-4 py-2 text-sm hover:bg-surface flex items-center gap-2"
                                            :class="question.is_void ? 'text-success' : 'text-warning'"
                                        >
                                            <Icon :name="question.is_void ? 'check-circle' : 'ban'" size="sm" />
                                            {{ question.is_void ? 'Unvoid' : 'Void' }} Question
                                        </button>
                                        <button
                                            @click="confirmDelete(question)"
                                            class="w-full text-left px-4 py-2 text-sm hover:bg-surface text-danger flex items-center gap-2"
                                        >
                                            <Icon name="trash" size="sm" />
                                            Delete
                                        </button>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Question Card (User View) -->
                        <div class="mb-4">
                            <QuestionCard
                                :model-value="selectedAnswers[question.id]"
                                @update:model-value="selectAnswer(question.id, $event)"
                                :question="question.question_text"
                                :options="question.options"
                                :points="question.points"
                                :correct-answer="question.correct_answer"
                                :show-results="false"
                                :show-header="false"
                            />
                        </div>

                        <!-- Current Answer Display -->
                        <div v-if="question.correct_answer" class="mb-4 text-sm text-success flex items-center gap-2">
                            <Icon name="check" size="sm" />
                            Current Answer: {{ question.correct_answer }}
                        </div>

                        <!-- Save Answer Button (conditional) -->
                        <div v-if="hasSelectedAnswer(question.id)" class="flex justify-end">
                            <Button variant="success" @click="saveAnswer(question)">
                                Save Answer
                            </Button>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="questions.length === 0" class="text-center py-12 text-muted">
                        <Icon name="circle-question" size="3x" class="mb-4 text-gray-light" />
                        <p class="text-lg mb-2">No questions yet</p>
                        <p class="text-sm mb-4">Get started by adding a custom question or importing from a template</p>
                        <div class="flex justify-center gap-2">
                            <Button variant="secondary" @click="navigateToImport">
                                Import from Template
                            </Button>
                            <Button variant="primary" @click="openAddModal">
                                Add Custom Question
                            </Button>
                        </div>
                    </div>
                </div>
            </Card>
        </div>

        <!-- Add/Edit Question Modal -->
        <QuestionModal
            :show="showAddEditModal"
            :question="editingQuestion"
            :event-id="event.id"
            @close="closeModal"
            @success="showToastMessage('Question saved successfully')"
        />

        <!-- Delete Confirmation Modal -->
        <Confirm
            :show="showDeleteConfirm"
            title="Delete Question?"
            message="This action cannot be undone. The question will be removed from the event."
            variant="danger"
            icon="triangle-exclamation"
            @confirm="deleteQuestion"
            @close="showDeleteConfirm = false"
        />

        <!-- Toast Notification -->
        <Toast
            :show="showToast"
            :message="toastMessage"
            @close="showToast = false"
        />
    </AuthenticatedLayout>
</template>
