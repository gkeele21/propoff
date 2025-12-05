<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PageHeader from '@/Components/PageHeader.vue';

const props = defineProps({
    events: Array,
});

const form = useForm({
    name: '',
    event_id: '',
    grading_source: 'admin',
});

const submit = () => {
    form.post(route('admin.groups.store'));
};
</script>

<template>
    <Head title="Create Group" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader
                title="Create New Group"
                subtitle="Set up a new group"
                :crumbs="[
                    { label: 'Admin Dashboard', href: route('admin.dashboard') },
                    { label: 'Groups', href: route('admin.groups.index') },
                    { label: 'Create' }
                ]"
            />
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <!-- Name -->
                        <div>
                            <InputLabel for="name" value="Group Name" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                                autofocus
                            />
                            <InputError :message="form.errors.name" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-500">
                                Choose a descriptive name for your group (e.g., "Friends", "Family", "Office League")
                            </p>
                        </div>

                        <!-- Event -->
                        <div>
                            <InputLabel for="event_id" value="Event" />
                            <select
                                id="event_id"
                                v-model="form.event_id"
                                class="mt-1 block w-full border-gray-300 focus:border-propoff-blue focus:ring-propoff-blue/50 rounded-md shadow-sm"
                                required
                            >
                                <option value="">Select an event...</option>
                                <option v-for="event in events" :key="event.id" :value="event.id">
                                    {{ event.name }} ({{ new Date(event.event_date).toLocaleDateString() }})
                                </option>
                            </select>
                            <InputError :message="form.errors.event_id" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-500">
                                Select the event this group will participate in
                            </p>
                        </div>

                        <!-- Grading Source -->
                        <div>
                            <InputLabel for="grading_source" value="Grading Mode" />
                            <select
                                id="grading_source"
                                v-model="form.grading_source"
                                class="mt-1 block w-full border-gray-300 focus:border-propoff-blue focus:ring-propoff-blue/50 rounded-md shadow-sm"
                                required
                            >
                                <option value="admin">Admin Grading - Use admin-set event answers</option>
                                <option value="captain">Captain Grading - Captain sets correct answers</option>
                            </select>
                            <InputError :message="form.errors.grading_source" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-500">
                                Choose who will provide the correct answers for grading
                            </p>
                        </div>

                        <!-- Info Box -->
                        <div class="bg-propoff-blue/10 border border-propoff-blue/30 rounded-lg p-4">
                            <h4 class="font-medium text-propoff-blue mb-2">What happens next?</h4>
                            <ul class="text-sm text-propoff-blue space-y-1">
                                <li>• A unique group code will be automatically generated</li>
                                <li>• You can add members manually or share the group code</li>
                                <li>• Members can join using the group code</li>
                                <li>• You can generate event invitations for this group</li>
                            </ul>
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center justify-between pt-4 border-t">
                            <Link
                                :href="route('admin.groups.index')"
                                class="text-gray-600 hover:text-gray-900"
                            >
                                Cancel
                            </Link>
                            <PrimaryButton
                                type="submit"
                                :disabled="form.processing"
                            >
                                Create Group
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
