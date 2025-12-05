<template>
    <Head title="Edit Group" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader
                title="Edit Group"
                :subtitle="`Edit ${group.name}`"
                :crumbs="[
                    { label: 'Dashboard', href: route('dashboard') },
                    { label: 'Groups', href: route('groups.index') },
                    { label: group.name, href: route('groups.show', group.id) },
                    { label: 'Edit' }
                ]"
            />
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6">
                        <!-- Group Name -->
                        <div class="mb-6">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Group Name <span class="text-propoff-red">*</span>
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                id="name"
                                placeholder="My Awesome Group"
                                class="w-full border-gray-300 focus:border-propoff-blue focus:ring-propoff-blue/50 rounded-md shadow-sm"
                                required
                                autofocus
                            />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-propoff-red">{{ form.errors.name }}</p>
                        </div>

                        <!-- Description -->
                        <div class="mb-6">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                Description (Optional)
                            </label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                class="w-full border-gray-300 focus:border-propoff-blue focus:ring-propoff-blue/50 rounded-md shadow-sm"
                                rows="3"
                                placeholder="Enter a description for your group"
                            ></textarea>
                            <p v-if="form.errors.description" class="mt-1 text-sm text-propoff-red">{{ form.errors.description }}</p>
                        </div>

                        <!-- Grading Source (conditionally editable) -->
                        <div v-if="canChangeGradingSource" class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Grading Source <span class="text-propoff-red">*</span>
                            </label>
                            <div class="space-y-3">
                                <label class="flex items-start p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition"
                                       :class="{ 'border-propoff-blue bg-propoff-blue/10': form.grading_source === 'captain' }">
                                    <input
                                        type="radio"
                                        v-model="form.grading_source"
                                        value="captain"
                                        class="mt-1 mr-3"
                                    />
                                    <div>
                                        <div class="font-semibold">Captain Grading</div>
                                        <div class="text-sm text-gray-600">
                                            You set the correct answers and grade entries in real-time.
                                        </div>
                                    </div>
                                </label>

                                <label class="flex items-start p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition"
                                       :class="{ 'border-propoff-blue bg-propoff-blue/10': form.grading_source === 'admin' }">
                                    <input
                                        type="radio"
                                        v-model="form.grading_source"
                                        value="admin"
                                        class="mt-1 mr-3"
                                    />
                                    <div>
                                        <div class="font-semibold">Admin Grading</div>
                                        <div class="text-sm text-gray-600">
                                            The admin sets answers after the event ends. Your group will be graded based on admin answers.
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <p v-if="form.errors.grading_source" class="mt-1 text-sm text-propoff-red">{{ form.errors.grading_source }}</p>
                        </div>

                        <!-- Entry Cutoff -->
                        <div class="mb-6">
                            <label for="entry_cutoff" class="block text-sm font-medium text-gray-700 mb-2">
                                Entry Cutoff (Optional)
                            </label>
                            <input
                                v-model="form.entry_cutoff"
                                type="datetime-local"
                                id="entry_cutoff"
                                class="w-full border-gray-300 focus:border-propoff-blue focus:ring-propoff-blue/50 rounded-md shadow-sm"
                            />
                            <p v-if="form.errors.entry_cutoff" class="mt-1 text-sm text-propoff-red">{{ form.errors.entry_cutoff }}</p>
                            <p class="mt-1 text-sm text-gray-500">
                                Set a deadline for when members can no longer submit or edit entries.
                                Leave blank to use the event's lock date.
                            </p>
                        </div>

                        <!-- Public/Private -->
                        <div class="mb-6">
                            <label class="flex items-center">
                                <input
                                    type="checkbox"
                                    v-model="form.is_public"
                                    class="rounded border-gray-300 text-propoff-blue focus:ring-propoff-blue/50"
                                />
                                <span class="ml-2 text-sm text-gray-700">Make this group public (others can find and join)</span>
                            </label>
                        </div>

                        <!-- Info Note -->
                        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <p class="text-sm text-blue-800">
                                <strong>Note:</strong> You cannot change the event after creating a group.
                                <span v-if="!canChangeGradingSource">
                                    Grading source cannot be changed once answers are set or entries are submitted.
                                </span>
                            </p>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t">
                            <Link
                                :href="route('groups.show', group.id)"
                                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center px-4 py-2 bg-propoff-blue border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-propoff-blue/80 disabled:opacity-50"
                            >
                                <span v-if="form.processing">Saving...</span>
                                <span v-else>Save Changes</span>
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
import PageHeader from '@/Components/PageHeader.vue';

const props = defineProps({
    group: {
        type: Object,
        required: true,
    },
    canChangeGradingSource: {
        type: Boolean,
        default: false,
    },
});

// Format datetime for datetime-local input (YYYY-MM-DDTHH:MM)
const formatDatetimeLocal = (datetime) => {
    if (!datetime) return '';
    const date = new Date(datetime);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${year}-${month}-${day}T${hours}:${minutes}`;
};

const form = useForm({
    name: props.group.name,
    description: props.group.description || '',
    grading_source: props.group.grading_source || 'captain',
    entry_cutoff: formatDatetimeLocal(props.group.entry_cutoff),
    is_public: props.group.is_public || false,
});

const submit = () => {
    form.patch(route('groups.update', props.group.id));
};
</script>
