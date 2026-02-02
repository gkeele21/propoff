<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Base/Modal.vue';
import Button from '@/Components/Base/Button.vue';
import Icon from '@/Components/Base/Icon.vue';
import TextField from '@/Components/Form/TextField.vue';
import NumberInput from '@/Components/Form/NumberInput.vue';
import Banner from '@/Components/Feedback/Banner.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    question: {
        type: Object,
        default: null,
    },
    eventId: {
        type: [String, Number],
        required: true,
    },
});

const emit = defineEmits(['close', 'success', 'delete']);

// Check if we're editing (has question) or adding (no question)
const isEditing = computed(() => !!props.question);

// Normalize options to new format if needed
const normalizeOptions = (options) => {
    if (!options || options.length === 0) {
        return [
            { label: '', points: 0 },
            { label: '', points: 0 },
        ];
    }
    // Check if already in new format (has objects with 'label' key)
    if (typeof options[0] === 'object' && options[0].label !== undefined) {
        return options.map(opt => ({ ...opt }));
    }
    // Convert old format (strings) to new format (objects)
    return options.map(opt => ({
        label: opt,
        points: 0
    }));
};

// Form initialization
const form = useForm({
    question_text: '',
    points: 10,
    options: normalizeOptions([]),
});

// Watch for question changes to populate form
watch(() => props.question, (newQuestion) => {
    if (newQuestion) {
        form.question_text = newQuestion.question_text || '';
        form.points = newQuestion.points || 10;
        form.options = normalizeOptions(newQuestion.options);
    } else {
        // Reset for adding new question
        form.reset();
        form.options = normalizeOptions([]);
    }
}, { immediate: true });

// Option management
const addOption = () => {
    form.options.push({ label: '', points: 0 });
};

const removeOption = (index) => {
    if (form.options.length > 2) {
        form.options.splice(index, 1);
    }
};

// Form submission
const submit = () => {
    if (isEditing.value) {
        // Update existing question
        form.patch(
            route('admin.events.event-questions.update', [props.eventId, props.question.id]),
            {
                onSuccess: () => {
                    emit('success');
                    emit('close');
                },
            }
        );
    } else {
        // Create new question
        form.post(
            route('admin.events.event-questions.store', props.eventId),
            {
                onSuccess: () => {
                    emit('success');
                    emit('close');
                    form.reset();
                },
            }
        );
    }
};

// Close handler
const handleClose = () => {
    if (!form.processing) {
        emit('close');
        form.reset();
    }
};
</script>

<template>
    <Modal :show="show" max-width="lg" @close="handleClose">
        <div class="p-6">
            <!-- Modal Header -->
            <div class="mb-6">
                <h2 class="text-xl font-bold text-body">
                    {{ isEditing ? 'Edit Question' : 'Add Custom Question' }}
                </h2>
            </div>

            <!-- Warning Banner (Edit mode only) -->
            <Banner
                v-if="isEditing && question?.user_answers_count > 0"
                variant="warning"
                icon="triangle-exclamation"
                class="mb-6"
            >
                <template #title>Warning: Existing Answers</template>
                This question has {{ question.user_answers_count }} submitted {{ question.user_answers_count === 1 ? 'answer' : 'answers' }}.
                Changing the question text or options may affect grading accuracy.
            </Banner>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Question Text -->
                <TextField
                    v-model="form.question_text"
                    label="Question Text"
                    :error="form.errors.question_text"
                    required
                    multiline
                    :rows="4"
                    placeholder="Enter your question here..."
                />

                <!-- Base Points -->
                <NumberInput
                    v-model="form.points"
                    label="Base Points"
                    :error="form.errors.points"
                    :min="1"
                    required
                    hint="Points awarded for answering (+ any option bonus)"
                />

                <!-- Answer Options -->
                <div class="space-y-3">
                    <label class="block text-sm font-medium text-body">
                        Answer Options
                        <span class="text-danger">*</span>
                    </label>
                    <p class="text-sm text-subtle mb-3">
                        Set bonus points for each option. Leave at 0 for no bonus (players get only base question points).
                    </p>

                    <div class="space-y-2">
                        <div
                            v-for="(option, index) in form.options"
                            :key="index"
                            class="flex items-start gap-2"
                        >
                            <!-- Option Letter -->
                            <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center bg-primary/15 text-primary font-medium rounded-full text-sm mt-2">
                                {{ String.fromCharCode(65 + index) }}
                            </div>

                            <!-- Option Input Container -->
                            <div class="flex-1 flex items-start gap-2">
                                <!-- Option Text Input -->
                                <div class="flex-1">
                                    <TextField
                                        v-model="form.options[index].label"
                                        :placeholder="`Option ${String.fromCharCode(65 + index)}`"
                                        required
                                    />
                                </div>

                                <!-- Bonus Input Group -->
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <label class="text-xs text-subtle whitespace-nowrap">Bonus:</label>
                                    <NumberInput
                                        v-model="form.options[index].points"
                                        :min="0"
                                        :step="1"
                                        placeholder="0"
                                        class="w-16"
                                    />
                                </div>
                            </div>

                            <!-- Remove Button -->
                            <Button
                                type="button"
                                variant="ghost"
                                size="sm"
                                @click="removeOption(index)"
                                :disabled="form.options.length <= 2"
                                class="mt-2"
                            >
                                <Icon name="trash" size="sm" class="text-danger" />
                            </Button>
                        </div>
                    </div>

                    <!-- Add Option Button -->
                    <Button
                        type="button"
                        variant="secondary"
                        size="sm"
                        @click="addOption"
                    >
                        <Icon name="plus" class="mr-2" size="sm" />
                        Add Option
                    </Button>

                    <p v-if="form.errors.options" class="text-sm text-danger">
                        {{ form.errors.options }}
                    </p>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                    <Button
                        type="button"
                        variant="outline"
                        class="!border-primary !text-primary hover:!bg-primary/10"
                        @click="handleClose"
                        :disabled="form.processing"
                    >
                        Cancel
                    </Button>
                    <Button
                        v-if="isEditing"
                        type="button"
                        variant="danger"
                        @click="emit('delete', question)"
                        :disabled="form.processing"
                    >
                        Delete
                    </Button>
                    <Button
                        type="submit"
                        variant="primary"
                        :loading="form.processing"
                    >
                        {{ isEditing ? 'Save' : 'Add Question' }}
                    </Button>
                </div>
            </form>
        </div>
    </Modal>
</template>
