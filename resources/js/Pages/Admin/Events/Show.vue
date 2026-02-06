<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import Button from '@/Components/Base/Button.vue';
import Badge from '@/Components/Base/Badge.vue';
import Card from '@/Components/Base/Card.vue';
import Icon from '@/Components/Base/Icon.vue';
import TextField from '@/Components/Form/TextField.vue';
import Radio from '@/Components/Form/Radio.vue';
import Modal from '@/Components/Base/Modal.vue';
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

// Handle delete from modal (close modal and show confirmation)
const handleDeleteFromModal = (question) => {
    showAddEditModal.value = false;
    editingQuestion.value = null;
    questionToDelete.value = question;
    showDeleteConfirm.value = true;
};

// Drag and drop reordering
const draggedQuestion = ref(null);
const dropTarget = ref(null);
const dropPosition = ref(null); // 'before' or 'after'
let scrollInterval = null;

const handleDragStart = (question) => {
    draggedQuestion.value = question;
};

const handleDrag = (e) => {
    // Auto-scroll when dragging near viewport edges
    const scrollThreshold = 100; // pixels from edge to start scrolling
    const scrollSpeed = 15; // pixels per frame

    // Clear any existing scroll interval
    if (scrollInterval) {
        clearInterval(scrollInterval);
        scrollInterval = null;
    }

    // e.clientY is 0 when drag ends or is outside viewport
    if (e.clientY === 0) return;

    const viewportHeight = window.innerHeight;

    if (e.clientY < scrollThreshold) {
        // Near top - scroll up
        scrollInterval = setInterval(() => {
            window.scrollBy(0, -scrollSpeed);
        }, 16);
    } else if (e.clientY > viewportHeight - scrollThreshold) {
        // Near bottom - scroll down
        scrollInterval = setInterval(() => {
            window.scrollBy(0, scrollSpeed);
        }, 16);
    }
};

const handleDragEnd = () => {
    // Clean up scroll interval
    if (scrollInterval) {
        clearInterval(scrollInterval);
        scrollInterval = null;
    }
    draggedQuestion.value = null;
    dropTarget.value = null;
    dropPosition.value = null;
};

const handleDragOver = (e, question) => {
    e.preventDefault();
    if (!draggedQuestion.value || draggedQuestion.value.id === question.id) {
        dropTarget.value = null;
        dropPosition.value = null;
        return;
    }

    // Determine if dropping before or after based on mouse position
    const rect = e.currentTarget.getBoundingClientRect();
    const midpoint = rect.top + rect.height / 2;
    dropTarget.value = question.id;
    dropPosition.value = e.clientY < midpoint ? 'before' : 'after';
};

const handleDragLeave = (e) => {
    // Only clear if leaving the element entirely (not entering a child)
    if (!e.currentTarget.contains(e.relatedTarget)) {
        dropTarget.value = null;
        dropPosition.value = null;
    }
};

const recentlyDroppedId = ref(null);

const handleDrop = (targetQuestion) => {
    // Store values before clearing
    const currentDropPosition = dropPosition.value;
    const droppedQuestionId = draggedQuestion.value?.id;

    // Clear visual indicators
    dropTarget.value = null;
    dropPosition.value = null;

    if (!draggedQuestion.value || draggedQuestion.value.id === targetQuestion.id) {
        return;
    }

    // Calculate new order based on drop position
    let newOrder = targetQuestion.display_order;
    if (currentDropPosition === 'after') {
        newOrder = targetQuestion.display_order + 1;
    }

    // Reorder on backend
    router.post(
        route('admin.events.event-questions.reorder', props.event.id),
        {
            question_id: draggedQuestion.value.id,
            new_order: newOrder,
        },
        {
            preserveScroll: false,
            onSuccess: () => {
                showToastMessage('Questions reordered');
                // Mark the dropped question for highlight effect
                recentlyDroppedId.value = droppedQuestionId;
                // Scroll to the dropped question after page refresh settles
                setTimeout(() => {
                    const element = document.getElementById(`question-${droppedQuestionId}`);
                    if (element) {
                        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    // Clear highlight after animation
                    setTimeout(() => {
                        recentlyDroppedId.value = null;
                    }, 1500);
                }, 300);
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
        open: 'success-soft',
        locked: 'warning-soft',
        completed: 'success-soft',
    };
    return variants[status] || 'default';
};

// Create My Group
const showCreateGroupModal = ref(false);
const createGroupForm = useForm({
    name: '',
    grading_source: 'captain',
});

const submitCreateGroup = () => {
    createGroupForm.post(route('admin.events.createMyGroup', props.event.id), {
        onSuccess: () => {
            showCreateGroupModal.value = false;
            createGroupForm.reset();
            showToastMessage('Group created! You are now a captain.');
        },
    });
};
</script>

<template>
    <Head :title="event.name" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader
                :title="event.name"
                :subtitle="event.description"
                :crumbs="[
                    { text: 'Events', href: route('admin.events.index') },
                    { text: event.name }
                ]"
            >
                <template #metadata>
                    <div class="flex items-center gap-3 flex-wrap">
                        <Badge :variant="getStatusVariant(event.status)">
                            {{ event.status }}
                        </Badge>
                        <span class="text-subtle">
                            {{ formatDate(event.event_date) }}
                        </span>
                        <span class="text-subtle">
                            {{ stats.total_entries || 0 }} entries
                        </span>
                        <Link :href="route('admin.events.edit', event.id)" class="text-primary hover:text-primary-hover transition-colors">
                            Edit
                        </Link>
                    </div>
                </template>
                <template #actions>
                    <Button variant="secondary" size="sm" class="!bg-info !text-white !border-info hover:!bg-info/80" @click="showCreateGroupModal = true">
                        <Icon name="users" class="mr-2" size="sm" />
                        Create My Group
                    </Button>
                    <Link :href="route('admin.events.captain-invitations.index', event.id)">
                        <Button variant="accent" size="sm">
                            <Icon name="paper-plane" class="mr-2" size="sm" />
                            Manage Invitations
                        </Button>
                    </Link>
                </template>
            </PageHeader>
        </template>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-6 py-8">
            <!-- Stats Row -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-surface-inset border border-border border-t-4 border-t-success rounded-lg p-5 text-center">
                    <div class="text-3xl font-bold text-success mb-1">{{ questions.length }}</div>
                    <div class="text-xs text-muted uppercase tracking-wider">Questions</div>
                </div>
                <div class="bg-surface-inset border border-border border-t-4 border-t-warning rounded-lg p-5 text-center">
                    <div class="text-3xl font-bold text-warning mb-1">{{ stats.graded_count || 0 }}</div>
                    <div class="text-xs text-muted uppercase tracking-wider">Answered</div>
                </div>
                <div class="bg-surface-inset border border-border border-t-4 border-t-primary rounded-lg p-5 text-center">
                    <div class="text-3xl font-bold text-primary mb-1">{{ stats.total_entries || 0 }}</div>
                    <div class="text-xs text-muted uppercase tracking-wider">Entries</div>
                </div>
                <div class="bg-surface-inset border border-border border-t-4 border-t-info rounded-lg p-5 text-center">
                    <div class="text-3xl font-bold text-info mb-1">{{ stats.total_points || 0 }}</div>
                    <div class="text-xs text-muted uppercase tracking-wider">Total Points</div>
                </div>
            </div>

            <!-- Questions Section -->
            <Card :header-padding="false" header-bg-class="bg-surface-header">
                <template #header>
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <h2 class="text-xl font-bold text-body">Questions</h2>
                                <Badge variant="primary-soft" size="sm">{{ questions.length }}</Badge>
                            </div>
                            <div class="flex gap-2">
                                <Button variant="secondary" size="sm" class="!bg-info !text-white !border-info hover:!bg-info/80" @click="navigateToImport">
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
                <div class="space-y-4 p-6">
                    <div
                        v-for="(question, index) in questions"
                        :key="question.id"
                        :id="`question-${question.id}`"
                        class="relative"
                    >
                        <!-- Drop indicator - before -->
                        <div
                            v-if="dropTarget === question.id && dropPosition === 'before'"
                            class="absolute -top-2 left-0 right-0 h-1 bg-primary rounded-full z-10"
                        />

                        <!-- Question Card -->
                        <div
                            class="bg-surface-elevated border border-border rounded-lg p-6 transition-all"
                            :class="{
                                'opacity-50': draggedQuestion?.id === question.id,
                                'border-primary/50': dropTarget === question.id,
                                'ring-2 ring-primary ring-offset-2 ring-offset-surface': recentlyDroppedId === question.id,
                            }"
                            draggable="true"
                            @dragstart="handleDragStart(question)"
                            @drag="handleDrag"
                            @dragend="handleDragEnd"
                            @dragover="handleDragOver($event, question)"
                            @dragleave="handleDragLeave"
                            @drop="handleDrop(question)"
                        >
                        <!-- Question Header -->
                        <div class="flex items-start gap-3 mb-4">
                            <!-- Drag Handle -->
                            <div class="flex-shrink-0 cursor-move text-muted hover:text-body mt-0.5" title="Drag to reorder">
                                <Icon name="grip-vertical" size="lg" />
                            </div>

                            <!-- Question Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-lg font-semibold text-body">{{ index + 1 }}. {{ question.question_text }}</h3>
                                    <Badge
                                        :variant="question.question_type === 'multiple_choice' ? 'info-soft' :
                                                  question.question_type === 'yes_no' ? 'success-soft' :
                                                  question.question_type === 'numeric' ? 'warning-soft' : 'default'"
                                        size="sm"
                                    >
                                        {{ question.question_type?.replace('_', ' ') }}
                                    </Badge>
                                    <Badge v-if="question.is_void" variant="danger" size="sm">Voided</Badge>
                                    <span class="text-sm text-subtle">{{ question.points }} {{ question.points === 1 ? 'pt' : 'pts' }}</span>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center gap-2">
                                <Button variant="ghost" size="sm" @click="openEditModal(question)">
                                    Edit
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    class="!text-warning"
                                    @click="toggleVoid(question)"
                                >
                                    {{ question.is_void ? 'Unvoid' : 'Void' }}
                                </Button>
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
                                :show-results="!!question.correct_answer"
                                :show-header="false"
                                selection-color="info"
                                :show-result-icons="false"
                            />
                        </div>

                            <!-- Save Answer Button (conditional) -->
                            <div v-if="hasSelectedAnswer(question.id)" class="flex justify-end">
                                <Button variant="secondary" class="!bg-info !text-white !border-info hover:!bg-info/80" @click="saveAnswer(question)">
                                    Save Answer
                                </Button>
                            </div>
                        </div>

                        <!-- Drop indicator - after -->
                        <div
                            v-if="dropTarget === question.id && dropPosition === 'after'"
                            class="absolute -bottom-2 left-0 right-0 h-1 bg-primary rounded-full z-10"
                        />
                    </div>

                    <!-- Empty State -->
                    <div v-if="questions.length === 0" class="text-center py-12 text-muted">
                        <Icon name="circle-question" size="3x" class="mb-4 text-gray-light" />
                        <p class="text-lg mb-2">No questions yet</p>
                        <p class="text-sm mb-4">Get started by adding a custom question or importing from a template</p>
                        <div class="flex justify-center gap-2">
                            <Button variant="secondary" class="!bg-info !text-white !border-info hover:!bg-info/80" @click="navigateToImport">
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
            @delete="handleDeleteFromModal"
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

        <!-- Create My Group Modal -->
        <Modal :show="showCreateGroupModal" @close="showCreateGroupModal = false" max-width="md">
            <div class="p-6">
                <h2 class="text-xl font-bold text-body mb-4">Create My Group</h2>
                <p class="text-muted text-sm mb-6">
                    Create your own group for this event. You'll become the captain and can invite members to join.
                </p>

                <form @submit.prevent="submitCreateGroup" class="space-y-4">
                    <TextField
                        v-model="createGroupForm.name"
                        label="Group Name"
                        placeholder="Enter a name for your group"
                        :error="createGroupForm.errors.name"
                        required
                    />

                    <div>
                        <label class="block text-sm font-semibold text-muted mb-2">Grading Source</label>
                        <div class="space-y-2">
                            <Radio
                                v-model="createGroupForm.grading_source"
                                name="grading_source"
                                value="captain"
                                label="Captain Sets Answers"
                                description="You control when and how questions are graded"
                            />
                            <Radio
                                v-model="createGroupForm.grading_source"
                                name="grading_source"
                                value="admin"
                                label="Use Admin Answers"
                                description="Scores calculated from official event answers"
                            />
                        </div>
                        <p v-if="createGroupForm.errors.grading_source" class="text-danger text-sm mt-1">
                            {{ createGroupForm.errors.grading_source }}
                        </p>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <Button type="button" variant="outline" class="!border-primary !text-primary hover:!bg-primary/10" @click="showCreateGroupModal = false">
                            Cancel
                        </Button>
                        <Button type="submit" variant="secondary" class="!bg-info !text-white !border-info hover:!bg-info/80" :loading="createGroupForm.processing">
                            <Icon name="users" class="mr-2" size="sm" />
                            Create Group
                        </Button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
