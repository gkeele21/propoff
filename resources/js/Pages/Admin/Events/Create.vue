<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PageHeader from '@/Components/PageHeader.vue';

const form = useForm({
    name: '',
    description: '',
    event_date: '',
    lock_date: '',
    status: 'draft',
    category: '',
    event_type: 'GameQuiz',
});

const submit = () => {
    form.post(route('admin.events.store'));
};
</script>

<template>
    <Head title="Create Event" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader
                title="Create New Event"
                subtitle="Set up a new event for your groups"
                :crumbs="[
                    { label: 'Admin Dashboard', href: route('admin.dashboard') },
                    { label: 'Events', href: route('admin.events.index') },
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
                            <InputLabel for="name" value="Event Name" />
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
                                Give your event a descriptive name
                            </p>
                        </div>

                        <!-- Description -->
                        <div>
                            <InputLabel for="description" value="Description" />
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="4"
                                class="mt-1 block w-full border-gray-300 focus:border-propoff-blue focus:ring-propoff-blue/50 rounded-md shadow-sm"
                            ></textarea>
                            <InputError :message="form.errors.description" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-500">
                                Provide details about this event
                            </p>
                        </div>

                        <!-- Event Date -->
                        <div>
                            <InputLabel for="event_date" value="Event Date" />
                            <input
                                id="event_date"
                                v-model="form.event_date"
                                type="datetime-local"
                                class="mt-1 block w-full border-gray-300 focus:border-propoff-blue focus:ring-propoff-blue/50 rounded-md shadow-sm"
                                required
                            />
                            <InputError :message="form.errors.event_date" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-500">
                                When will this event take place?
                            </p>
                        </div>

                        <!-- Lock Date -->
                        <div>
                            <InputLabel for="lock_date" value="Lock Date (Optional)" />
                            <input
                                id="lock_date"
                                v-model="form.lock_date"
                                type="datetime-local"
                                class="mt-1 block w-full border-gray-300 focus:border-propoff-blue focus:ring-propoff-blue/50 rounded-md shadow-sm"
                            />
                            <InputError :message="form.errors.lock_date" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-500">
                                When should entries be locked? (entries cannot be changed after this date)
                            </p>
                        </div>

                        <!-- Event Type -->
                        <div>
                            <InputLabel for="event_type" value="Event Type" />
                            <select
                                id="event_type"
                                v-model="form.event_type"
                                class="mt-1 block w-full border-gray-300 focus:border-propoff-blue focus:ring-propoff-blue/50 rounded-md shadow-sm"
                                required
                            >
                                <option value="GameQuiz">Game Quiz</option>
                                <option value="AmericaSays">America Says</option>
                            </select>
                            <InputError :message="form.errors.event_type" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-500">
                                Choose the type of game for this event
                            </p>
                        </div>

                        <!-- Category -->
                        <div>
                            <InputLabel for="category" value="Category (Optional)" />
                            <TextInput
                                id="category"
                                v-model="form.category"
                                type="text"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="form.errors.category" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-500">
                                <span v-if="form.event_type === 'AmericaSays'">
                                    For America Says: Use "Christmas", "Halloween", or "Sports" for themed styling
                                </span>
                                <span v-else>
                                    Category for organizing events (e.g., Football, Basketball)
                                </span>
                            </p>
                        </div>

                        <!-- Status -->
                        <div>
                            <InputLabel for="status" value="Status" />
                            <select
                                id="status"
                                v-model="form.status"
                                class="mt-1 block w-full border-gray-300 focus:border-propoff-blue focus:ring-propoff-blue/50 rounded-md shadow-sm"
                            >
                                <option value="draft">Draft</option>
                                <option value="open">Open</option>
                                <option value="locked">Locked</option>
                                <option value="completed">Completed</option>
                            </select>
                            <InputError :message="form.errors.status" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-500">
                                Set the current status of the event
                            </p>
                        </div>

                        <!-- Status Descriptions -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">Status Guide:</h4>
                            <ul class="text-sm text-gray-600 space-y-1">
                                <li><strong>Draft:</strong> Event is being created, not visible to users</li>
                                <li><strong>Open:</strong> Event is active and accepting entries</li>
                                <li><strong>Locked:</strong> Event is closed for new entries</li>
                                <li><strong>Completed:</strong> Event is finished and results are final</li>
                            </ul>
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center justify-between pt-4 border-t">
                            <Link
                                :href="route('admin.events.index')"
                                class="text-gray-600 hover:text-gray-900"
                            >
                                Cancel
                            </Link>
                            <PrimaryButton
                                type="submit"
                                :disabled="form.processing"
                            >
                                Create Event
                            </PrimaryButton>
                        </div>
                    </form>
                </div>

                <!-- Next Steps Card -->
                <div class="mt-6 bg-propoff-blue/10 border border-propoff-blue/30 rounded-lg p-4">
                    <h4 class="font-medium text-propoff-blue mb-2">Next Steps</h4>
                    <p class="text-sm text-propoff-blue">
                        After creating the event, you'll be able to:
                    </p>
                    <ul class="mt-2 text-sm text-propoff-blue list-disc list-inside space-y-1">
                        <li>Add questions to your event</li>
                        <li>Set group-specific correct answers</li>
                        <li>Manage event settings and status</li>
                        <li>View entries and statistics</li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
