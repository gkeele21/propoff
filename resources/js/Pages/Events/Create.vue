<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import Button from '@/Components/Base/Button.vue';
import TextField from '@/Components/Form/TextField.vue';

const form = useForm({
    name: '',
    description: '',
    category: '',
    event_date: '',
    status: 'draft',
    lock_date: '',
});

const submit = () => {
    form.post(route('events.store'));
};
</script>

<template>
    <Head title="Create Event" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader
                title="Create New Event"
                :crumbs="[
                    { label: 'Events', href: route('events.index') },
                    { label: 'Create' }
                ]"
            />
        </template>

        <div class="py-8">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <!-- Name -->
                        <TextField
                            id="name"
                            v-model="form.name"
                            type="text"
                            label="Event Name"
                            :error="form.errors.name"
                            required
                            autofocus
                        />

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                Description
                            </label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                class="mt-1 block w-full border-gray-300 focus:border-primary focus:ring-primary/50 rounded-md shadow-sm"
                                rows="4"
                            ></textarea>
                            <p v-if="form.errors.description" class="mt-1 text-sm text-danger">
                                {{ form.errors.description }}
                            </p>
                        </div>

                        <!-- Category -->
                        <TextField
                            id="category"
                            v-model="form.category"
                            type="text"
                            label="Category"
                            :error="form.errors.category"
                            required
                            placeholder="e.g., NFL, NBA, March Madness"
                        />

                        <!-- Event Date -->
                        <TextField
                            id="event_date"
                            v-model="form.event_date"
                            type="datetime-local"
                            label="Event Date"
                            :error="form.errors.event_date"
                            required
                        />

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                                Status
                            </label>
                            <select
                                id="status"
                                v-model="form.status"
                                class="mt-1 block w-full border-gray-300 focus:border-primary focus:ring-primary/50 rounded-md shadow-sm"
                                required
                            >
                                <option value="draft">Draft</option>
                                <option value="active">Active</option>
                                <option value="locked">Locked</option>
                                <option value="completed">Completed</option>
                            </select>
                            <p v-if="form.errors.status" class="mt-1 text-sm text-danger">
                                {{ form.errors.status }}
                            </p>
                        </div>

                        <!-- Lock Date -->
                        <div>
                            <TextField
                                id="lock_date"
                                v-model="form.lock_date"
                                type="datetime-local"
                                label="Lock Date (Optional)"
                                :error="form.errors.lock_date"
                            />
                            <p class="mt-1 text-sm text-gray-500">
                                After this date, users won't be able to submit answers
                            </p>
                        </div>

                        <div class="flex items-center justify-end gap-4">
                            <a
                                :href="route('events.index')"
                                class="text-gray-600 hover:text-gray-900"
                            >
                                Cancel
                            </a>
                            <Button variant="primary" :disabled="form.processing">
                                Create Event
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
