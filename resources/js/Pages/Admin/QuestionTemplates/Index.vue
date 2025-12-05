<template>
    <Head title="Question Templates" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader
                title="Question Templates"
                subtitle="Manage reusable question templates"
                :crumbs="[
                    { label: 'Admin Dashboard', href: route('admin.dashboard') },
                    { label: 'Question Templates' }
                ]"
            >
                <template #actions>
                    <Link :href="route('admin.question-templates.create')">
                        <PrimaryButton>
                            <PlusIcon class="w-4 h-4 mr-2" />
                            Create Template
                        </PrimaryButton>
                    </Link>
                </template>
            </PageHeader>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Search and Filter -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <input 
                                    type="text" 
                                    v-model="filters.search" 
                                    @input="filterTemplates"
                                    placeholder="Search templates..." 
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-propoff-blue focus:ring-propoff-blue/50"
                                />
                            </div>
                            <div>
                                <select 
                                    v-model="filters.type" 
                                    @change="filterTemplates"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-propoff-blue focus:ring-propoff-blue/50"
                                >
                                    <option value="">All Types</option>
                                    <option value="multiple_choice">Multiple Choice</option>
                                    <option value="yes_no">Yes/No</option>
                                    <option value="numeric">Numeric</option>
                                    <option value="text">Text</option>
                                </select>
                            </div>
                            <div>
                                <select 
                                    v-model="filters.category" 
                                    @change="filterTemplates"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-propoff-blue focus:ring-propoff-blue/50"
                                >
                                    <option value="">All Categories</option>
                                    <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Templates Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="template in filteredTemplates" :key="template.id" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ template.title }}</h3>
                                    <span v-if="template.category" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ template.category }}
                                    </span>
                                </div>
                                <span :class="[
                                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                    template.question_type === 'multiple_choice' ? 'bg-propoff-blue/10 text-propoff-blue' :
                                    template.question_type === 'yes_no' ? 'bg-propoff-green/10 text-propoff-dark-green' :
                                    template.question_type === 'numeric' ? 'bg-propoff-orange/10 text-propoff-orange' :
                                    'bg-gray-100 text-gray-700'
                                ]">
                                    {{ template.question_type?.replace('_', ' ') }}
                                </span>
                            </div>

                            <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ template.question_text }}</p>

                            <!-- Points Display -->
                            <div class="mb-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-xs font-medium text-gray-700">Base Points:</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-propoff-green/10 text-propoff-dark-green">
                                        {{ template.default_points || 0 }} pts
                                    </span>
                                </div>

                                <!-- Show bonus points if options have them -->
                                <div v-if="hasOptionBonusPoints(template)" class="text-xs text-propoff-orange">
                                    <span class="font-medium">⭐ Bonus Points:</span>
                                    <div class="ml-4 mt-1 space-y-0.5">
                                        <div v-for="(option, index) in getOptionsWithBonus(template)" :key="index" class="text-gray-700">
                                            {{ option.label }}: <span class="font-semibold text-propoff-orange">+{{ option.points }} {{ option.points === 1 ? 'pt' : 'pts' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="template.variables && template.variables.length > 0" class="mb-4">
                                <p class="text-xs text-gray-500 mb-1">Variables:</p>
                                <div class="flex flex-wrap gap-1">
                                    <span v-for="variable in template.variables" :key="variable" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono bg-propoff-blue/10 text-propoff-blue">
                                        {{ '{' + variable + '}' }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                                <div class="flex space-x-2">
                                    <Link :href="route('admin.question-templates.edit', template.id)" class="text-propoff-blue hover:text-propoff-blue/80 text-sm font-medium">
                                        Edit
                                    </Link>
                                    <button @click="duplicateTemplate(template.id)" class="text-propoff-green hover:text-propoff-dark-green text-sm font-medium">
                                        Duplicate
                                    </button>
                                    <button @click="confirmDelete(template.id)" class="text-propoff-red hover:text-propoff-red/80 text-sm font-medium">
                                        Delete
                                    </button>
                                </div>
                                <!-- <Link :href="route('admin.question-templates.preview', template.id)" class="text-gray-600 hover:text-gray-900 text-sm font-medium">
                                    Preview
                                </Link> -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="filteredTemplates.length === 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-12 text-center">
                        <DocumentTextIcon class="mx-auto h-12 w-12 text-gray-400" />
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No templates found</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by creating a new question template.</p>
                        <div class="mt-6">
                            <Link :href="route('admin.question-templates.create')" class="inline-flex items-center px-4 py-2 bg-propoff-blue border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-propoff-blue/80">
                                <PlusIcon class="w-4 h-4 mr-2" />
                                Create Template
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PageHeader from '@/Components/PageHeader.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref, computed } from 'vue';
import { PlusIcon, DocumentTextIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    templates: Object,  // Pagination object, not array
});

const filters = ref({
    search: '',
    type: '',
    category: '',
});

const categories = computed(() => {
    if (!props.templates?.data) return [];
    const cats = props.templates.data
        .map(t => t.category)
        .filter(c => c)
        .filter((value, index, self) => self.indexOf(value) === index);
    return cats;
});

const filteredTemplates = computed(() => {
    if (!props.templates?.data) return [];
    let result = props.templates.data;

    if (filters.value.search) {
        const search = filters.value.search.toLowerCase();
        result = result.filter(t =>
            t.title?.toLowerCase().includes(search) ||
            t.question_text?.toLowerCase().includes(search)
        );
    }

    if (filters.value.type) {
        result = result.filter(t => t.question_type === filters.value.type);
    }

    if (filters.value.category) {
        result = result.filter(t => t.category === filters.value.category);
    }

    return result;
});

const filterTemplates = () => {
    // Filters are reactive, no action needed
};

const duplicateTemplate = (id) => {
    router.post(route('admin.question-templates.duplicate', id));
};

const confirmDelete = (id) => {
    if (confirm('Are you sure you want to delete this template?')) {
        router.delete(route('admin.question-templates.destroy', id));
    }
};

// Helper function to check if template has options with bonus points
const hasOptionBonusPoints = (template) => {
    if (!template.default_options) return false;

    try {
        const options = typeof template.default_options === 'string'
            ? JSON.parse(template.default_options)
            : template.default_options;

        if (!Array.isArray(options)) return false;

        return options.some(option => option.points && option.points > 0);
    } catch (e) {
        return false;
    }
};

// Helper function to get options that have bonus points
const getOptionsWithBonus = (template) => {
    if (!template.default_options) return [];

    try {
        const options = typeof template.default_options === 'string'
            ? JSON.parse(template.default_options)
            : template.default_options;

        if (!Array.isArray(options)) return [];

        return options.filter(option => option.points && option.points > 0);
    } catch (e) {
        return [];
    }
};
</script>
