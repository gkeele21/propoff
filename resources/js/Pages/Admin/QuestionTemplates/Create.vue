<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Button from '@/Components/Base/Button.vue';
import TextField from '@/Components/Form/TextField.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { ArrowUpIcon, ArrowDownIcon, TrashIcon } from '@heroicons/vue/24/outline';

const form = useForm({
    title: '',
    category: '',
    question_text: '',
    question_type: 'multiple_choice',
    default_options: [
        { label: '', points: 0 },
        { label: '', points: 0 }
    ],
    variables: [],
    default_points: '1',
    template_answers: [],
});

const newVariable = ref('');

const addOption = () => {
    form.default_options.push({ label: '', points: 0 });
};

const removeOption = (index) => {
    if (form.default_options.length > 2) {
        form.default_options.splice(index, 1);
    }
};

const addVariable = () => {
    if (newVariable.value.trim()) {
        form.variables.push(newVariable.value.trim());
        newVariable.value = '';
    }
};

const removeVariable = (index) => {
    form.variables.splice(index, 1);
};

// Template Answers functions
const addTemplateAnswer = () => {
    const nextOrder = form.template_answers.length + 1;
    if (nextOrder <= 7) {
        form.template_answers.push({
            answer_text: '',
            display_order: nextOrder,
        });
    }
};

const removeTemplateAnswer = (index) => {
    form.template_answers.splice(index, 1);
    // Reorder remaining answers
    form.template_answers.forEach((answer, idx) => {
        answer.display_order = idx + 1;
    });
};

const moveAnswerUp = (index) => {
    if (index > 0) {
        const temp = form.template_answers[index];
        form.template_answers[index] = form.template_answers[index - 1];
        form.template_answers[index - 1] = temp;
        // Update display orders
        form.template_answers.forEach((answer, idx) => {
            answer.display_order = idx + 1;
        });
    }
};

const moveAnswerDown = (index) => {
    if (index < form.template_answers.length - 1) {
        const temp = form.template_answers[index];
        form.template_answers[index] = form.template_answers[index + 1];
        form.template_answers[index + 1] = temp;
        // Update display orders
        form.template_answers.forEach((answer, idx) => {
            answer.display_order = idx + 1;
        });
    }
};

// Computed property for category tags preview
const categoryTags = computed(() => {
    if (!form.category) return [];
    return form.category
        .split(',')
        .map(c => c.trim())
        .filter(c => c.length > 0);
});

const submit = () => {
    form.post(route('admin.question-templates.store'));
};
</script>

<template>
    <Head title="Create Question Template" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader
                title="Create Question Template"
                subtitle="Create a reusable question template"
                :crumbs="[
                    { label: 'Admin Dashboard', href: route('admin.dashboard') },
                    { label: 'Question Templates', href: route('admin.question-templates.index') },
                    { label: 'Create' }
                ]"
            />
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <!-- Template Name -->
                        <TextField
                            v-model="form.title"
                            label="Template Name"
                            :error="form.errors.title"
                            hint="A descriptive name for this template"
                            required
                        />

                        <!-- Category -->
                        <div>
                            <TextField
                                v-model="form.category"
                                label="Categories"
                                :error="form.errors.category"
                                hint="Enter one or more categories separated by commas (e.g., &quot;football,nfl,sports&quot;)"
                                placeholder="e.g., football,nfl,sports"
                            />
                            <!-- Category tags preview -->
                            <div v-if="categoryTags.length > 0" class="mt-2 flex gap-2 flex-wrap">
                                <span
                                    v-for="tag in categoryTags"
                                    :key="tag"
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                >
                                    {{ tag }}
                                </span>
                            </div>
                        </div>

                        <!-- Base Points -->
                        <TextField
                            v-model="form.default_points"
                            label="Base Points"
                            type="number"
                            :error="form.errors.default_points"
                            hint="Points awarded for answering correctly (+ any option bonus)"
                            min="1"
                            step="1"
                            required
                        />

                        <!-- Question Type -->
                        <div>
                            <label for="question_type" class="block text-sm font-medium text-gray-700 mb-1">Question Type</label>
                            <select
                                id="question_type"
                                v-model="form.question_type"
                                class="mt-1 block w-full border-gray-300 focus:border-primary focus:ring-primary/50 rounded-md shadow-sm"
                            >
                                <option value="multiple_choice">Multiple Choice</option>
                                <option value="yes_no">Yes/No</option>
                                <option value="numeric">Numeric</option>
                                <option value="text">Text</option>
                                <option value="ranked_answers">Ranked Answers</option>
                            </select>
                            <p v-if="form.errors.question_type" class="text-danger text-sm mt-1">{{ form.errors.question_type }}</p>
                        </div>

                        <!-- Question Text -->
                        <div>
                            <label for="question_text" class="block text-sm font-medium text-gray-700 mb-1">Question Text</label>
                            <textarea
                                id="question_text"
                                v-model="form.question_text"
                                rows="3"
                                class="mt-1 block w-full border-gray-300 focus:border-primary focus:ring-primary/50 rounded-md shadow-sm"
                                required
                            ></textarea>
                            <p v-if="form.errors.question_text" class="text-danger text-sm mt-1">{{ form.errors.question_text }}</p>
                            <p class="mt-1 text-sm text-gray-500">
                                Use {variable} syntax for dynamic content (e.g., "Who will win {team1} vs {team2}?")
                            </p>
                        </div>

                        <!-- Variables -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Variables</label>
                            <div class="mt-2 space-y-2">
                                <div
                                    v-for="(variable, index) in form.variables"
                                    :key="index"
                                    class="flex items-center gap-2"
                                >
                                    <span class="flex-1 px-3 py-2 bg-gray-100 rounded-md text-sm">{{ variable }}</span>
                                    <button
                                        type="button"
                                        @click="removeVariable(index)"
                                        class="px-3 py-2 text-sm text-danger hover:text-danger/80"
                                    >
                                        Remove
                                    </button>
                                </div>
                            </div>
                            <div class="mt-2 flex gap-2">
                                <input
                                    v-model="newVariable"
                                    type="text"
                                    class="flex-1 border-gray-300 focus:border-primary focus:ring-primary/50 rounded-md shadow-sm"
                                    placeholder="Variable name (e.g., team1)"
                                    @keyup.enter="addVariable"
                                />
                                <button
                                    type="button"
                                    @click="addVariable"
                                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
                                >
                                    Add Variable
                                </button>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">
                                Add variables that can be replaced when creating questions
                            </p>
                        </div>

                        <!-- Options (for multiple choice) -->
                        <div v-if="form.question_type === 'multiple_choice'">
                            <InputLabel value="Answer Options" />
                            <p class="mt-1 mb-3 text-sm text-gray-500">
                                Set the option labels and optional bonus points. Options can use {'{'}variable{'}'} syntax.
                            </p>
                            <div class="space-y-3">
                                <div
                                    v-for="(option, index) in form.default_options"
                                    :key="index"
                                    class="border border-gray-200 rounded-lg p-3 bg-gray-50"
                                >
                                    <div class="flex items-start gap-2">
                                        <span class="flex-shrink-0 w-8 h-8 flex items-center justify-center bg-gray-100 text-gray-700 font-medium rounded-full text-sm mt-1">
                                            {{ String.fromCharCode(65 + index) }}
                                        </span>
                                        <div class="flex-1 space-y-1">
                                            <input
                                                type="text"
                                                v-model="form.default_options[index].label"
                                                class="w-full border-gray-300 focus:border-primary focus:ring-primary/50 rounded-md shadow-sm"
                                                :placeholder="`Option ${String.fromCharCode(65 + index)}`"
                                            />
                                            <div class="flex items-center gap-2">
                                                <label class="text-xs text-gray-500">Bonus:</label>
                                                <input
                                                    type="number"
                                                    v-model.number="form.default_options[index].points"
                                                    min="0"
                                                    step="1"
                                                    class="w-20 text-sm border-gray-300 focus:border-primary focus:ring-primary/50 rounded-md shadow-sm"
                                                    placeholder="0"
                                                />
                                                <span class="text-xs text-gray-400">+bonus pts (optional)</span>
                                            </div>
                                        </div>
                                        <button
                                            v-if="form.default_options.length > 2"
                                            type="button"
                                            @click="removeOption(index)"
                                            class="flex-shrink-0 px-3 py-2 text-sm text-danger hover:text-danger/80"
                                        >
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button
                                type="button"
                                @click="addOption"
                                class="mt-3 w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition"
                            >
                                + Add Option
                            </button>
                        </div>

                        <!-- Template Answers (for Ranked Answers type) -->
                        <div v-if="form.question_type === 'ranked_answers'">
                            <div class="flex items-center justify-between mb-2">
                                <InputLabel value="Ranked Answers" />
                                <span class="text-xs text-gray-500">Required for Ranked Answers type</span>
                            </div>
                            <p class="mb-3 text-sm text-gray-500">
                                Add up to 7 ranked answers for this AmericaSays-style question.
                            </p>

                            <div v-if="form.template_answers.length > 0" class="space-y-3 mb-3">
                                <div
                                    v-for="(answer, index) in form.template_answers"
                                    :key="index"
                                    class="flex items-center gap-3 p-4 border border-gray-200 rounded-lg"
                                >
                                    <!-- Rank Number -->
                                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-warning text-white flex items-center justify-center font-bold">
                                        #{{ answer.display_order }}
                                    </div>

                                    <!-- Answer Input -->
                                    <div class="flex-1">
                                        <TextInput
                                            v-model="answer.answer_text"
                                            type="text"
                                            class="w-full"
                                            placeholder="Enter answer..."
                                        />
                                    </div>

                                    <!-- Reorder Buttons -->
                                    <div class="flex flex-col gap-1">
                                        <button
                                            type="button"
                                            @click="moveAnswerUp(index)"
                                            :disabled="index === 0"
                                            class="p-1 text-gray-600 hover:text-primary disabled:opacity-30 disabled:cursor-not-allowed"
                                            title="Move up (higher rank)"
                                        >
                                            <ArrowUpIcon class="w-5 h-5" />
                                        </button>
                                        <button
                                            type="button"
                                            @click="moveAnswerDown(index)"
                                            :disabled="index === form.template_answers.length - 1"
                                            class="p-1 text-gray-600 hover:text-primary disabled:opacity-30 disabled:cursor-not-allowed"
                                            title="Move down (lower rank)"
                                        >
                                            <ArrowDownIcon class="w-5 h-5" />
                                        </button>
                                    </div>

                                    <!-- Remove Button -->
                                    <button
                                        type="button"
                                        @click="removeTemplateAnswer(index)"
                                        class="p-2 text-red-600 hover:text-red-700 hover:bg-red-50 rounded"
                                        title="Remove answer"
                                    >
                                        <TrashIcon class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>

                            <button
                                v-if="form.template_answers.length < 7"
                                type="button"
                                @click="addTemplateAnswer"
                                class="w-full p-4 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-primary hover:text-primary transition"
                            >
                                + Add Answer ({{ form.template_answers.length }}/7)
                            </button>

                            <div v-if="form.template_answers.length > 0" class="mt-3 bg-primary/10 border border-primary/30 rounded-lg p-3">
                                <p class="text-xs text-primary">
                                    <strong>Tip:</strong> Answer #1 should be the most popular/common response, #7 the least popular.
                                </p>
                            </div>
                        </div>

                        <!-- Example Preview -->
                        <div class="bg-primary/10 rounded-lg p-4">
                            <h4 class="font-medium text-primary mb-2">Template Example</h4>
                            <p class="text-sm text-primary">
                                Question: {{ form.question_text || 'Enter question text...' }}
                            </p>
                            <div v-if="form.variables.length > 0" class="mt-2">
                                <p class="text-xs text-primary font-medium">Variables:</p>
                                <div class="flex flex-wrap gap-1 mt-1">
                                    <span
                                        v-for="variable in form.variables"
                                        :key="variable"
                                        class="px-2 py-1 bg-primary/20 text-primary text-xs rounded"
                                    >
                                        {{ variable }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center justify-between pt-4 border-t">
                            <Link
                                :href="route('admin.question-templates.index')"
                                class="text-gray-600 hover:text-gray-900"
                            >
                                Cancel
                            </Link>
                            <Button
                                variant="primary"
                                type="submit"
                                :disabled="form.processing"
                            >
                                Create Template
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
