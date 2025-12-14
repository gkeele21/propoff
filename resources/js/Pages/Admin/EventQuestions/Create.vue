<template>
    <Head :title="`Create Questions - ${event.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader
                title="Add Questions"
                subtitle="Add questions from templates or create custom questions"
                :crumbs="[
                    { label: 'Admin Dashboard', href: route('admin.dashboard') },
                    { label: 'Events', href: route('admin.events.index') },
                    { label: event.name, href: route('admin.events.show', event.id) },
                    { label: 'Questions', href: route('admin.events.event-questions.index', event.id) },
                    { label: 'Add' }
                ]"
            >
                <template #metadata>
                    <span class="font-medium text-gray-900">{{ event.name }}</span>
                    <span class="text-gray-400 mx-2">•</span>
                    <span>{{ currentQuestions.length }} questions</span>
                </template>
            </PageHeader>
        </template>

        <div class="py-12">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
                <!-- Event Context -->
                <div class="bg-propoff-blue/10 border border-propoff-blue/30 rounded-lg p-4 mb-6">
                    <div class="flex items-start gap-3">
                        <InformationCircleIcon class="w-5 h-5 text-propoff-blue mt-0.5" />
                        <div>
                            <h3 class="font-semibold text-propoff-blue">{{ event.name }}</h3>
                            <p class="text-sm text-propoff-blue mt-1">
                                Current Questions: {{ currentQuestions.length }} | Event Date: {{ formatDate(event?.event_date) }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- LEFT: Template Search -->
                    <div class="lg:col-span-2">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="border-b border-gray-200 p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                                    <DocumentPlusIcon class="w-5 h-5 inline mr-2" />
                                    Find Template Questions
                                </h3>

                                <!-- Category Search Input -->
                                <div class="mb-6">
                                    <label for="category-search" class="block text-sm font-medium text-gray-700 mb-2">
                                        Search by Category
                                    </label>
                                    <div class="flex gap-2">
                                        <input
                                            id="category-search"
                                            type="text"
                                            v-model="categorySearch"
                                            @keyup.enter="findTemplatesByCategory"
                                            class="flex-1 border-gray-300 focus:border-propoff-blue focus:ring-propoff-blue/50 rounded-md shadow-sm"
                                            placeholder="Enter category (e.g., football, nfl, sports)"
                                        />
                                        <button
                                            @click="findTemplatesByCategory"
                                            :disabled="!categorySearch.trim() || isSearching"
                                            class="px-4 py-2 bg-propoff-blue text-white rounded-md hover:bg-propoff-blue/80 disabled:bg-propoff-blue/50 disabled:cursor-not-allowed"
                                        >
                                            {{ isSearching ? 'Searching...' : 'Find' }}
                                        </button>
                                    </div>
                                </div>

                                <!-- Search Results -->
                                <div v-if="searchPerformed">
                                    <div v-if="filteredTemplates.length === 0" class="text-center py-8 text-gray-500">
                                        No templates found for category "{{ lastSearchTerm }}".
                                    </div>

                                    <div v-else class="space-y-3">
                                        <p class="text-sm text-gray-700 mb-3">
                                            Found {{ filteredTemplates.length }} template{{ filteredTemplates.length !== 1 ? 's' : '' }} for "{{ lastSearchTerm }}"
                                        </p>

                                        <!-- Select All / Deselect All -->
                                        <div class="flex gap-2 pb-4 border-b">
                                            <button @click="selectAllTemplates"
                                                    class="px-3 py-1 text-sm bg-propoff-blue text-white rounded hover:bg-propoff-blue/80">
                                                Select All
                                            </button>
                                            <button @click="deselectAllTemplates"
                                                    class="px-3 py-1 text-sm bg-gray-200 text-gray-800 rounded hover:bg-gray-300">
                                                Deselect All
                                            </button>
                                            <button @click="bulkCreateSelected"
                                                    :disabled="selectedTemplates.length === 0"
                                                    class="ml-auto px-4 py-1 text-sm bg-propoff-green text-white rounded hover:bg-propoff-dark-green disabled:bg-propoff-green/50">
                                                Import {{ selectedTemplates.length }} Selected
                                            </button>
                                        </div>

                                        <!-- Template List -->
                                        <div class="space-y-2 max-h-[500px] overflow-y-auto">
                                            <div v-for="template in filteredTemplates" :key="template.id"
                                                 class="flex items-start gap-3 p-3 border border-gray-200 rounded hover:bg-gray-50">

                                                <!-- Checkbox -->
                                                <input
                                                    type="checkbox"
                                                    :checked="isTemplateSelected(template.id)"
                                                    @change="toggleTemplateSelection(template)"
                                                    class="mt-1"
                                                />

                                                <!-- Template Info -->
                                                <div class="flex-1">
                                                    <div class="flex items-center gap-2 mb-1">
                                                        <h4 class="font-semibold text-gray-900">{{ template.title }}</h4>
                                                        <span :class="['px-2 py-0.5 text-xs rounded', typeClass(template.question_type)]">
                                                            {{ formatType(template.question_type) }}
                                                        </span>
                                                    </div>
                                                    <p class="text-sm text-gray-600 mb-2">{{ template.question_text }}</p>

                                                    <!-- Variables Badge -->
                                                    <div v-if="template.variables?.length" class="text-xs">
                                                        <span class="bg-propoff-orange/10 text-propoff-orange px-2 py-1 rounded">
                                                            {{ template.variables.length }} variable{{ template.variables.length !== 1 ? 's' : '' }}:
                                                            {{ template.variables.join(', ') }}
                                                        </span>
                                                    </div>

                                                    <!-- Template Answers Preview -->
                                                    <div v-if="template.template_answers?.length" class="mt-2 text-xs">
                                                        <div class="bg-propoff-blue/10 border border-propoff-blue/30 rounded p-2">
                                                            <p class="font-semibold text-propoff-blue mb-1">Ranked Answers ({{ template.template_answers.length }}):</p>
                                                            <ol class="list-decimal list-inside text-propoff-blue space-y-0.5">
                                                                <li v-for="answer in template.template_answers" :key="answer.id">
                                                                    {{ answer.answer_text }}
                                                                </li>
                                                            </ol>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div v-else class="text-center py-8 text-gray-500">
                                    <p>Enter a category and click "Find" to search for template questions.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT: Current Questions -->
                    <div>
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                                    <ListBulletIcon class="w-5 h-5 inline mr-2" />
                                    Current Questions ({{ currentQuestions.length }})
                                </h3>

                                <div v-if="currentQuestions.length === 0" class="text-center py-8 text-gray-500">
                                    <p>No questions added yet.</p>
                                    <p class="text-sm mt-2">Search templates or create a custom question below.</p>
                                </div>

                                <div v-else class="space-y-2 max-h-96 overflow-y-auto">
                                    <div v-for="(question, index) in currentQuestions" :key="question.id"
                                         class="p-2 bg-gray-50 rounded border border-gray-200 text-sm">

                                        <div class="flex justify-between items-start gap-2">
                                            <div class="flex-1">
                                                <p class="font-semibold text-gray-900 line-clamp-2">
                                                    {{ question.question_text }}
                                                </p>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    Order: {{ question.display_order }} | {{ question.points }} {{ question.points === 1 ? 'point' : 'points' }}
                                                </p>
                                            </div>
                                            <button @click="deleteQuestion(question.id)"
                                                    class="text-propoff-red hover:text-propoff-red/80 flex-shrink-0">
                                                <TrashIcon class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Add Manual Question Button - Always show -->
                                <button @click="showManualForm = !showManualForm"
                                        class="w-full mt-4 px-3 py-2 text-sm bg-propoff-blue text-white rounded hover:bg-propoff-blue/80">
                                    {{ showManualForm ? '- Hide Form' : '+ Add Custom Question' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Manual Question Form (if expanded) -->
                <div v-if="showManualForm" class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Create Custom Question
                        </h3>
                        <button @click="showManualForm = false" class="text-gray-500 hover:text-gray-700">
                            <XMarkIcon class="w-5 h-5" />
                        </button>
                    </div>

                    <form @submit.prevent="submitManual" class="space-y-6">
                        <!-- Question Text -->
                        <div>
                            <label for="question_text" class="block text-sm font-medium text-gray-700 mb-2">
                                Question Text <span class="text-propoff-red">*</span>
                            </label>
                            <textarea
                                id="question_text"
                                v-model="form.question_text"
                                rows="4"
                                class="w-full border-gray-300 focus:border-propoff-blue focus:ring-propoff-blue/50 rounded-md shadow-sm"
                                placeholder="Enter your question here..."
                                required
                            ></textarea>
                            <p v-if="form.errors.question_text" class="mt-1 text-sm text-propoff-red">
                                {{ form.errors.question_text }}
                            </p>
                        </div>

                        <!-- Question Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Question Type <span class="text-propoff-red">*</span>
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <label
                                    v-for="type in questionTypes"
                                    :key="type.value"
                                    :class="[
                                        'flex items-center p-3 border-2 rounded-lg cursor-pointer transition',
                                        form.question_type === type.value
                                            ? 'border-propoff-blue bg-propoff-blue/10'
                                            : 'border-gray-200 hover:border-gray-300'
                                    ]"
                                >
                                    <input
                                        type="radio"
                                        v-model="form.question_type"
                                        :value="type.value"
                                        class="text-propoff-blue focus:ring-propoff-blue/50"
                                    />
                                    <span class="ml-2 text-sm font-medium text-gray-900">{{ type.label }}</span>
                                </label>
                            </div>
                        </div>

                        <!-- Options (for multiple choice) -->
                        <div v-if="form.question_type === 'multiple_choice'" class="space-y-3">
                            <label class="block text-sm font-medium text-gray-700">
                                Answer Options <span class="text-propoff-red">*</span>
                            </label>
                            <p class="text-sm text-gray-500 mb-2">
                                Set bonus points for each option. Leave at 0 for no bonus (players get only base question points).
                            </p>

                            <div class="space-y-2">
                                <div
                                    v-for="(option, index) in form.options"
                                    :key="index"
                                    class="flex items-start gap-2"
                                >
                                    <span class="flex-shrink-0 w-8 h-8 flex items-center justify-center bg-gray-100 text-gray-700 font-medium rounded-full text-sm mt-1">
                                        {{ String.fromCharCode(65 + index) }}
                                    </span>
                                    <div class="flex-1 space-y-1">
                                        <input
                                            type="text"
                                            v-model="form.options[index].label"
                                            class="w-full border-gray-300 focus:border-propoff-blue focus:ring-propoff-blue/50 rounded-md shadow-sm"
                                            :placeholder="`Option ${String.fromCharCode(65 + index)}`"
                                        />
                                        <div class="flex items-center gap-2">
                                            <label class="text-xs text-gray-500">Bonus:</label>
                                            <input
                                                type="number"
                                                v-model.number="form.options[index].points"
                                                min="0"
                                                step="1"
                                                class="w-20 text-sm border-gray-300 focus:border-propoff-blue focus:ring-propoff-blue/50 rounded-md shadow-sm"
                                                placeholder="0"
                                            />
                                            <span class="text-xs text-gray-400">+bonus pts (optional)</span>
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        @click="removeOption(index)"
                                        class="flex-shrink-0 p-2 text-propoff-red hover:bg-propoff-red/10 rounded mt-1"
                                        :disabled="form.options.length <= 2"
                                    >
                                        <TrashIcon class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>

                            <button
                                type="button"
                                @click="addOption"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            >
                                <PlusIcon class="w-4 h-4 mr-2" />
                                Add Option
                            </button>
                        </div>

                        <!-- Points and Order -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="points" class="block text-sm font-medium text-gray-700 mb-2">
                                    Base Points <span class="text-propoff-red">*</span>
                                </label>
                                <input
                                    type="number"
                                    id="points"
                                    v-model.number="form.points"
                                    min="1"
                                    class="w-full border-gray-300 focus:border-propoff-blue focus:ring-propoff-blue/50 rounded-md shadow-sm"
                                    required
                                />
                                <p class="text-xs text-gray-500 mt-1">
                                    Points awarded for answering (+ any option bonus)
                                </p>
                            </div>

                            <div>
                                <label for="order" class="block text-sm font-medium text-gray-700 mb-2">
                                    Order Number
                                </label>
                                <input
                                    type="number"
                                    id="order"
                                    v-model.number="form.order"
                                    min="0"
                                    class="w-full border-gray-300 focus:border-propoff-blue focus:ring-propoff-blue/50 rounded-md shadow-sm"
                                />
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t">
                            <button
                                type="button"
                                @click="showManualForm = false"
                                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center px-4 py-2 bg-propoff-blue border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-propoff-blue/80 disabled:opacity-50"
                            >
                                <span v-if="form.processing">Creating...</span>
                                <span v-else>Save</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Consolidated Variable Input Modal -->
                <div v-if="showConsolidatedVariableModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <!-- Backdrop -->
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeConsolidatedVariableModal"></div>

                    <!-- Modal -->
                    <div class="flex min-h-full items-center justify-center p-4">
                        <div class="relative bg-white rounded-lg shadow-xl max-w-3xl w-full max-h-[90vh] overflow-hidden flex flex-col">
                            <!-- Header -->
                            <div class="border-b border-gray-200 px-6 py-4">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        Fill in Variables for {{ selectedTemplates.length }} Question{{ selectedTemplates.length !== 1 ? 's' : '' }}
                                    </h3>
                                    <button @click="closeConsolidatedVariableModal" class="text-gray-400 hover:text-gray-500">
                                        <XMarkIcon class="w-6 h-6" />
                                    </button>
                                </div>
                                <p class="mt-1 text-sm text-gray-500">
                                    The following variables were found across your selected templates.
                                    Fill them out once, and they'll be applied to all questions.
                                </p>
                            </div>

                            <!-- Body - Scrollable -->
                            <div class="flex-1 overflow-y-auto px-6 py-4">
                                <div class="space-y-4">
                                    <div
                                        v-for="variable in distinctVariables"
                                        :key="variable"
                                        class="variable-input-group"
                                    >
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            {{ variable }}
                                        </label>

                                        <!-- Show which templates use this variable -->
                                        <p class="text-xs text-gray-500 mb-2">
                                            Used in {{ getTemplateCountForVariable(variable) }} question(s)
                                        </p>

                                        <input
                                            type="text"
                                            v-model="consolidatedVariables[variable]"
                                            :placeholder="`Enter value for ${variable}`"
                                            class="w-full border-gray-300 focus:border-propoff-blue focus:ring-propoff-blue/50 rounded-md shadow-sm"
                                        />
                                    </div>
                                </div>

                                <!-- Preview Section -->
                                <div v-if="previewQuestion" class="mt-6 p-4 bg-gray-50 rounded border border-gray-200">
                                    <h4 class="text-sm font-semibold text-gray-700 mb-2">Preview (first question):</h4>
                                    <p class="text-gray-900">{{ previewQuestionText }}</p>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="border-t border-gray-200 px-6 py-4 flex items-center justify-end gap-3">
                                <button
                                    @click="closeConsolidatedVariableModal"
                                    type="button"
                                    class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                                >
                                    Cancel
                                </button>
                                <button
                                    @click="submitConsolidatedImport"
                                    type="button"
                                    :disabled="!allVariablesFilled"
                                    class="px-4 py-2 bg-propoff-blue border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-propoff-blue/80 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    Import {{ selectedTemplates.length }} Question{{ selectedTemplates.length !== 1 ? 's' : '' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import { router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    InformationCircleIcon,
    DocumentPlusIcon,
    ListBulletIcon,
    TrashIcon,
    PlusIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';
import PageHeader from '@/Components/PageHeader.vue';
import axios from 'axios';

const props = defineProps({
    event: Object,
    currentQuestions: Array,
    nextOrder: Number,
});

// Category Search State
const categorySearch = ref('');
const lastSearchTerm = ref('');
const searchPerformed = ref(false);
const filteredTemplates = ref([]);
const isSearching = ref(false);

// Template Selection State
const selectedTemplates = ref([]);

// Consolidated Variable Modal State
const showConsolidatedVariableModal = ref(false);
const consolidatedVariables = ref({});

// Manual Form State
const showManualForm = ref(false);

// Form for manual creation
const questionTypes = [
    { value: 'multiple_choice', label: 'Multiple Choice' },
    { value: 'yes_no', label: 'Yes/No' },
    { value: 'numeric', label: 'Numeric' },
    { value: 'text', label: 'Text' },
];

const form = useForm({
    question_text: '',
    question_type: 'multiple_choice',
    points: 1,
    order: props.nextOrder || 1,
    options: [
        { label: '', points: 0 },
        { label: '', points: 0 }
    ],
});

// Computed: Get all distinct variables across selected templates
const distinctVariables = computed(() => {
    const variableSet = new Set();

    selectedTemplates.value.forEach(template => {
        if (template.variables && Array.isArray(template.variables)) {
            template.variables.forEach(v => variableSet.add(v));
        }
    });

    return Array.from(variableSet).sort();
});

// Computed: Check if all variables are filled
const allVariablesFilled = computed(() => {
    return distinctVariables.value.every(variable => {
        return consolidatedVariables.value[variable] &&
               consolidatedVariables.value[variable].trim() !== '';
    });
});

// Computed: Preview question with variables replaced
const previewQuestion = computed(() => {
    return selectedTemplates.value.find(t => t.variables && t.variables.length > 0) || null;
});

const previewQuestionText = computed(() => {
    if (!previewQuestion.value) return '';

    let text = previewQuestion.value.question_text;

    Object.keys(consolidatedVariables.value).forEach(variable => {
        const value = consolidatedVariables.value[variable];
        if (value) {
            text = text.replace(new RegExp(`\\{${variable}\\}`, 'g'), value);
        }
    });

    return text;
});

// Methods

// Search for templates by category
const findTemplatesByCategory = async () => {
    if (!categorySearch.value.trim()) {
        return;
    }

    isSearching.value = true;

    try {
        const response = await axios.get(
            route('admin.events.event-questions.searchTemplates', props.event.id),
            {
                params: { category: categorySearch.value.trim() }
            }
        );

        filteredTemplates.value = response.data.templates;
        lastSearchTerm.value = response.data.search_term;
        searchPerformed.value = true;
    } catch (error) {
        console.error('Error searching templates:', error);
        alert('Error searching for templates. Please try again.');
    } finally {
        isSearching.value = false;
    }
};

// Toggle template selection
const toggleTemplateSelection = (template) => {
    const index = selectedTemplates.value.findIndex(t => t.id === template.id);
    if (index > -1) {
        selectedTemplates.value.splice(index, 1);
    } else {
        selectedTemplates.value.push(template);
    }
};

// Check if template is selected
const isTemplateSelected = (templateId) => {
    return selectedTemplates.value.some(t => t.id === templateId);
};

// Select all templates
const selectAllTemplates = () => {
    selectedTemplates.value = [...filteredTemplates.value];
};

// Deselect all templates
const deselectAllTemplates = () => {
    selectedTemplates.value = [];
};

// Bulk create selected templates
const bulkCreateSelected = () => {
    if (selectedTemplates.value.length === 0) {
        return;
    }

    // Check if any selected templates have variables
    const hasVariables = selectedTemplates.value.some(t =>
        t.variables && t.variables.length > 0
    );

    if (hasVariables) {
        openConsolidatedVariableModal();
    } else {
        // No variables, import directly
        submitBulkImport();
    }
};

// Open consolidated variable modal
const openConsolidatedVariableModal = () => {
    // Initialize consolidated variables
    consolidatedVariables.value = {};
    distinctVariables.value.forEach(variable => {
        consolidatedVariables.value[variable] = '';
    });

    showConsolidatedVariableModal.value = true;
};

// Close consolidated variable modal
const closeConsolidatedVariableModal = () => {
    showConsolidatedVariableModal.value = false;
    consolidatedVariables.value = {};
};

// Helper: Count how many templates use a variable
const getTemplateCountForVariable = (variable) => {
    return selectedTemplates.value.filter(template => {
        return template.variables && template.variables.includes(variable);
    }).length;
};

// Submit bulk import with consolidated variables
const submitConsolidatedImport = () => {
    const payload = {
        templates: selectedTemplates.value.map(template => ({
            template_id: template.id,
            variable_values: template.variables
                ? template.variables.reduce((acc, variable) => {
                    acc[variable] = consolidatedVariables.value[variable] || '';
                    return acc;
                }, {})
                : {}
        }))
    };

    router.post(
        route('admin.events.event-questions.bulkCreateFromTemplates', props.event.id),
        payload,
        {
            onSuccess: () => {
                closeConsolidatedVariableModal();
                selectedTemplates.value = [];
                categorySearch.value = '';
                searchPerformed.value = false;
                filteredTemplates.value = [];
            },
            onError: (errors) => {
                console.error('Error importing templates:', errors);
                alert('Error importing templates. Please try again.');
            }
        }
    );
};

// Submit bulk import without variables
const submitBulkImport = () => {
    const payload = {
        templates: selectedTemplates.value.map(template => ({
            template_id: template.id,
            variable_values: {}
        }))
    };

    router.post(
        route('admin.events.event-questions.bulkCreateFromTemplates', props.event.id),
        payload,
        {
            onSuccess: () => {
                selectedTemplates.value = [];
                categorySearch.value = '';
                searchPerformed.value = false;
                filteredTemplates.value = [];
            },
            onError: (errors) => {
                console.error('Error importing templates:', errors);
                alert('Error importing templates. Please try again.');
            }
        }
    );
};

// Delete question
const deleteQuestion = (questionId) => {
    if (confirm('Delete this question?')) {
        router.delete(
            route('admin.events.event-questions.destroy', [props.event.id, questionId]),
            {
                onSuccess: () => {
                    router.visit(route('admin.events.event-questions.create', props.event.id));
                }
            }
        );
    }
};

// Format type
const formatType = (type) => {
    if (!type) return 'Unknown';
    return type.split('_')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
};

// Format date
const formatDate = (date) => {
    if (!date) return 'No date';
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
};

// Type class
const typeClass = (type) => {
    if (!type) return 'bg-gray-100 text-gray-800';
    const classes = {
        multiple_choice: 'bg-propoff-blue/10 text-propoff-blue',
        yes_no: 'bg-propoff-green/10 text-propoff-dark-green',
        numeric: 'bg-propoff-orange/10 text-propoff-orange',
        text: 'bg-gray-100 text-gray-800',
    };
    return classes[type] || 'bg-gray-100 text-gray-800';
};

// Add option
const addOption = () => {
    form.options.push({ label: '', points: 0 });
};

// Remove option
const removeOption = (index) => {
    if (form.options.length > 2) {
        form.options.splice(index, 1);
    }
};

// Submit manual question
const submitManual = () => {
    form.post(route('admin.events.event-questions.store', props.event.id), {
        onSuccess: () => {
            showManualForm.value = false;
            form.reset();
        }
    });
};
</script>
