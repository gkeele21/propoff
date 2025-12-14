<template>
    <Head title="Edit Question Template" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader
                title="Edit Question Template"
                :crumbs="[
                    { label: 'Admin Dashboard', href: route('admin.dashboard') },
                    { label: 'Question Templates', href: route('admin.question-templates.index') },
                    { label: 'Edit' }
                ]"
            >
                <template #metadata>
                    <span class="font-medium text-gray-900">{{ template.title }}</span>
                </template>
            </PageHeader>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <!-- Template Name -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">Template Name</label>
                            <input
                                type="text"
                                id="title"
                                v-model="form.title"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-propoff-blue focus:ring-propoff-blue/50"
                                required
                            />
                            <div v-if="form.errors.title" class="text-propoff-red text-sm mt-1">{{ form.errors.title }}</div>
                        </div>

                        <!-- Category -->
                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700">Categories (Optional)</label>
                            <input
                                type="text"
                                id="category"
                                v-model="form.category"
                                placeholder="e.g., football,nfl,sports"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-propoff-blue focus:ring-propoff-blue/50"
                            />
                            <div v-if="form.errors.category" class="text-propoff-red text-sm mt-1">{{ form.errors.category }}</div>
                            <p class="mt-1 text-sm text-gray-500">
                                Enter one or more categories separated by commas (e.g., "football,nfl,sports")
                            </p>
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
                        <div>
                            <label for="default_points" class="block text-sm font-medium text-gray-700">Base Points</label>
                            <input
                                type="number"
                                id="default_points"
                                v-model.number="form.default_points"
                                min="1"
                                step="1"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-propoff-blue focus:ring-propoff-blue/50"
                                required
                            />
                            <p class="mt-1 text-sm text-gray-500">
                                Points awarded for answering correctly (+ any option bonus)
                            </p>
                            <div v-if="form.errors.default_points" class="text-propoff-red text-sm mt-1">{{ form.errors.default_points }}</div>
                        </div>

                        <!-- Question Type -->
                        <div>
                            <label for="question_type" class="block text-sm font-medium text-gray-700">Question Type</label>
                            <select
                                id="question_type"
                                v-model="form.question_type"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-propoff-blue focus:ring-propoff-blue/50"
                                required
                            >
                                <option value="multiple_choice">Multiple Choice</option>
                                <option value="yes_no">Yes/No</option>
                                <option value="numeric">Numeric</option>
                                <option value="text">Text</option>
                                <option value="ranked_answers">Ranked Answers (AmericaSays)</option>
                            </select>
                            <div v-if="form.errors.question_type" class="text-propoff-red text-sm mt-1">{{ form.errors.question_type }}</div>
                        </div>

                        <!-- Question Text -->
                        <div>
                            <label for="question_text" class="block text-sm font-medium text-gray-700">Question Text</label>
                            <textarea 
                                id="question_text"
                                v-model="form.question_text" 
                                rows="4"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-propoff-blue focus:ring-propoff-blue/50"
                                placeholder="Use {variable} syntax for dynamic content"
                                required
                            ></textarea>
                            <p class="mt-1 text-sm text-gray-500">Use curly braces for variables, e.g., {team1}, {player1}</p>
                            <div v-if="form.errors.question_text" class="text-propoff-red text-sm mt-1">{{ form.errors.question_text }}</div>
                        </div>

                        <!-- Variables -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Variables</label>
                            <div class="space-y-2">
                                <div v-for="(variable, index) in form.variables" :key="index" class="flex items-center space-x-2">
                                    <span class="text-gray-600">{</span>
                                    <input 
                                        type="text" 
                                        v-model="form.variables[index]" 
                                        class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-propoff-blue focus:ring-propoff-blue/50"
                                        placeholder="variable_name"
                                    />
                                    <span class="text-gray-600">}</span>
                                    <button 
                                        type="button" 
                                        @click="removeVariable(index)" 
                                        class="px-3 py-2 bg-propoff-red text-white rounded-md hover:bg-propoff-red/80"
                                    >
                                        Remove
                                    </button>
                                </div>
                            </div>
                            <button 
                                type="button" 
                                @click="addVariable" 
                                class="mt-2 px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"
                            >
                                Add Variable
                            </button>
                        </div>

                        <!-- Options (for multiple choice) -->
                        <div v-if="form.question_type === 'multiple_choice'">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Answer Options</label>
                            <p class="text-sm text-gray-500 mb-3">
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
                                                class="w-full border-gray-300 focus:border-propoff-blue focus:ring-propoff-blue/50 rounded-md shadow-sm"
                                                :placeholder="`Option ${String.fromCharCode(65 + index)}`"
                                            />
                                            <div class="flex items-center gap-2">
                                                <label class="text-xs text-gray-500">Bonus:</label>
                                                <input
                                                    type="number"
                                                    v-model.number="form.default_options[index].points"
                                                    min="0"
                                                    step="1"
                                                    class="w-20 text-sm border-gray-300 focus:border-propoff-blue focus:ring-propoff-blue/50 rounded-md shadow-sm"
                                                    placeholder="0"
                                                />
                                                <span class="text-xs text-gray-400">+bonus pts (optional)</span>
                                            </div>
                                        </div>
                                        <button
                                            v-if="form.default_options.length > 2"
                                            type="button"
                                            @click="removeOption(index)"
                                            class="flex-shrink-0 px-3 py-2 text-sm text-propoff-red hover:text-propoff-red/80"
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
                                <label class="block text-sm font-medium text-gray-700">Ranked Answers</label>
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
                                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-propoff-orange text-white flex items-center justify-center font-bold">
                                        #{{ answer.display_order }}
                                    </div>

                                    <!-- Answer Input -->
                                    <div class="flex-1">
                                        <input
                                            v-model="answer.answer_text"
                                            type="text"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-propoff-blue focus:ring-propoff-blue/50"
                                            placeholder="Enter answer..."
                                        />
                                    </div>

                                    <!-- Reorder Buttons -->
                                    <div class="flex flex-col gap-1">
                                        <button
                                            type="button"
                                            @click="moveAnswerUp(index)"
                                            :disabled="index === 0"
                                            class="p-1 text-gray-600 hover:text-propoff-blue disabled:opacity-30 disabled:cursor-not-allowed"
                                            title="Move up (higher rank)"
                                        >
                                            <ArrowUpIcon class="w-5 h-5" />
                                        </button>
                                        <button
                                            type="button"
                                            @click="moveAnswerDown(index)"
                                            :disabled="index === form.template_answers.length - 1"
                                            class="p-1 text-gray-600 hover:text-propoff-blue disabled:opacity-30 disabled:cursor-not-allowed"
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
                                class="w-full p-4 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-propoff-blue hover:text-propoff-blue transition"
                            >
                                + Add Answer ({{ form.template_answers.length }}/7)
                            </button>

                            <div v-if="form.template_answers.length > 0" class="mt-3 bg-propoff-blue/10 border border-propoff-blue/30 rounded-lg p-3">
                                <p class="text-xs text-propoff-blue">
                                    <strong>Tip:</strong> Answer #1 should be the most popular/common response, #7 the least popular.
                                </p>
                            </div>
                        </div>

                        <!-- Preview -->
                        <div class="bg-propoff-blue/10 border border-propoff-blue/30 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-propoff-blue mb-2">Preview</h4>
                            <p class="text-sm text-gray-700 mb-2">{{ form.question_text }}</p>
                            <div v-if="form.variables.length > 0" class="text-xs text-gray-600">
                                <strong>Variables:</strong> 
                                <span v-for="(variable, index) in form.variables" :key="index" class="ml-1 font-mono">
                                    {{ '{' + variable + '}' }}{{ index < form.variables.length - 1 ? ',' : '' }}
                                </span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                            <Link :href="route('admin.question-templates.index')" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                                Cancel
                            </Link>
                            <button 
                                type="submit" 
                                :disabled="form.processing"
                                class="px-4 py-2 bg-propoff-blue text-white rounded-md hover:bg-propoff-blue/80 disabled:opacity-50"
                            >
                                {{ form.processing ? 'Updating...' : 'Update Template' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import PageHeader from '@/Components/PageHeader.vue';
import { ArrowUpIcon, ArrowDownIcon, TrashIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    template: Object,
});

// Normalize options to new format if needed (backward compatibility)
const normalizeOptions = (options) => {
    if (!options || options.length === 0) return [];

    // Check if already in new format
    if (typeof options[0] === 'object' && options[0].label !== undefined) {
        return options;
    }

    // Convert old format (strings) to new format (objects)
    return options.map(opt => ({
        label: opt,
        points: 0
    }));
};

const form = useForm({
    title: props.template.title,
    category: props.template.category || '',
    question_type: props.template.question_type,
    question_text: props.template.question_text,
    variables: props.template.variables || [],
    default_options: normalizeOptions(props.template.default_options),
    default_points: String(props.template.default_points || 1),
    template_answers: props.template.template_answers?.map(a => ({
        answer_text: a.answer_text,
        display_order: a.display_order
    })) || [],
});

const addVariable = () => {
    form.variables.push('');
};

const removeVariable = (index) => {
    form.variables.splice(index, 1);
};

const addOption = () => {
    form.default_options.push({ label: '', points: 0 });
};

const removeOption = (index) => {
    if (form.default_options.length > 2) {
        form.default_options.splice(index, 1);
    }
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
    form.put(route('admin.question-templates.update', props.template.id));
};
</script>
