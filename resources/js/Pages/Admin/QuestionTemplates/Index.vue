<template>
    <Head title="Question Templates" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader
                title="Question Templates"
                subtitle="Manage reusable question templates"
                :crumbs="[
                                        { label: 'Question Templates' }
                ]"
            >
                <template #actions>
                    <Link :href="route('admin.question-templates.create')">
                        <Button variant="primary" icon="plus">Create Template</Button>
                    </Link>
                </template>
            </PageHeader>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Search and Filter -->
                <div class="bg-surface overflow-hidden shadow-sm sm:rounded-lg mb-6 border border-border">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <input
                                    type="text"
                                    v-model="form.search"
                                    @input="debouncedFilter"
                                    placeholder="Search templates..."
                                    class="w-full rounded-md bg-surface-elevated border-border text-body placeholder-muted shadow-sm focus:border-primary focus:ring-primary/50"
                                />
                            </div>
                            <div>
                                <select
                                    v-model="form.type"
                                    @change="filterTemplates"
                                    class="w-full rounded-md bg-surface-elevated border-border text-body shadow-sm focus:border-primary focus:ring-primary/50"
                                >
                                    <option value="">All Types</option>
                                    <option value="multiple_choice">Multiple Choice</option>
                                    <option value="yes_no">Yes/No</option>
                                    <option value="numeric">Numeric</option>
                                    <option value="text">Text</option>
                                    <option value="ranked_answers">Ranked Answers</option>
                                </select>
                            </div>
                            <div>
                                <select
                                    v-model="form.category"
                                    @change="filterTemplates"
                                    class="w-full rounded-md bg-surface-elevated border-border text-body shadow-sm focus:border-primary focus:ring-primary/50"
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
                    <Link
                        v-for="template in templates.data"
                        :key="template.id"
                        :href="route('admin.question-templates.edit', template.id)"
                        class="bg-surface overflow-hidden shadow-sm sm:rounded-lg border border-border hover:border-primary transition-colors cursor-pointer block"
                    >
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-body mb-1">{{ template.title }}</h3>
                                    <span v-if="template.category" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-surface-elevated text-body">
                                        {{ template.category }}
                                    </span>
                                </div>
                                <Badge
                                    :variant="template.question_type === 'multiple_choice' ? 'info-soft' :
                                              template.question_type === 'yes_no' ? 'success-soft' :
                                              template.question_type === 'numeric' ? 'warning-soft' : 'default'"
                                    size="sm"
                                >
                                    {{ template.question_type?.replace('_', ' ') }}
                                </Badge>
                            </div>

                            <p class="text-sm text-muted mb-4 line-clamp-2">{{ template.question_text }}</p>

                            <!-- Points Display -->
                            <div class="mb-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-xs font-medium text-body">Base Points:</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-success/10 text-success">
                                        {{ template.default_points || 0 }} pts
                                    </span>
                                </div>

                                <!-- Show bonus points if options have them -->
                                <div v-if="hasOptionBonusPoints(template)" class="text-xs text-warning">
                                    <span class="font-medium">⭐ Bonus Points:</span>
                                    <div class="ml-4 mt-1 space-y-0.5">
                                        <div v-for="(option, index) in getOptionsWithBonus(template)" :key="index" class="text-body">
                                            {{ option.label }}: <span class="font-semibold text-warning">+{{ option.points }} {{ option.points === 1 ? 'pt' : 'pts' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="template.variables && template.variables.length > 0" class="mb-4">
                                <p class="text-xs text-muted mb-1">Variables:</p>
                                <div class="flex flex-wrap gap-1">
                                    <span v-for="variable in template.variables" :key="variable" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono bg-primary/10 text-primary">
                                        {{ '{' + variable + '}' }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center justify-end pt-4 border-t border-border">
                                <button @click.prevent="duplicateTemplate(template.id)" class="text-warning hover:text-warning/80 text-sm font-medium">
                                    Duplicate
                                </button>
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Empty State -->
                <div v-if="templates.data.length === 0" class="bg-surface overflow-hidden shadow-sm sm:rounded-lg border border-border">
                    <div class="p-12 text-center">
                        <DocumentTextIcon class="mx-auto h-12 w-12 text-muted" />
                        <h3 class="mt-2 text-sm font-medium text-body">No templates found</h3>
                        <p class="mt-1 text-sm text-muted">Get started by creating a new question template.</p>
                        <div class="mt-6">
                            <Link :href="route('admin.question-templates.create')">
                                <Button variant="primary" icon="plus">Create Template</Button>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <Pagination :data="templates" item-name="templates" class="mt-6" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PageHeader from '@/Components/PageHeader.vue';
import Button from '@/Components/Base/Button.vue';
import Badge from '@/Components/Base/Badge.vue';
import Pagination from '@/Components/Pagination.vue';
import { ref, computed, watch } from 'vue';
import { DocumentTextIcon } from '@heroicons/vue/24/outline';
import { debounce } from 'lodash';

const props = defineProps({
    templates: Object,  // Pagination object from Laravel
    categories: Array,
    filters: Object,
});

// Initialize form with current filters
const form = ref({
    search: props.filters?.search || '',
    type: props.filters?.type || '',
    category: props.filters?.category || '',
});

// Server-side filter function
const filterTemplates = () => {
    router.get(route('admin.question-templates.index'), {
        search: form.value.search || undefined,
        type: form.value.type || undefined,
        category: form.value.category || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

// Debounced version for search input
const debouncedFilter = debounce(() => {
    filterTemplates();
}, 300);

const duplicateTemplate = (id) => {
    router.post(route('admin.question-templates.duplicate', id));
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
