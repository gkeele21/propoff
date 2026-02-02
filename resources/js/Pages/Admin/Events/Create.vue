<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Button from '@/Components/Base/Button.vue';
import TextField from '@/Components/Form/TextField.vue';
import FormLabel from '@/Components/Form/FormLabel.vue';

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
        <!-- Header Bar with Actions -->
        <div class="bg-surface border-b border-border">
            <div class="max-w-7xl mx-auto px-6 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-body">Create New Event</h1>
                        <p class="text-muted text-sm mt-1">Set up a new event for your groups</p>
                    </div>
                    <div class="flex gap-3">
                        <Link :href="route('admin.events.index')">
                            <Button variant="outline" size="sm">
                                Cancel
                            </Button>
                        </Link>
                        <Button variant="primary" size="sm" @click="submit" :disabled="form.processing">
                            Create Event
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-surface overflow-hidden shadow-sm sm:rounded-lg border border-border">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <!-- Name -->
                        <TextField
                            v-model="form.name"
                            label="Event Name"
                            :error="form.errors.name"
                            required
                        />

                        <!-- Description -->
                        <TextField
                            v-model="form.description"
                            label="Description"
                            :error="form.errors.description"
                            multiline
                            :rows="4"
                        />

                        <!-- Event Date -->
                        <div>
                            <FormLabel>Event Date</FormLabel>
                            <input
                                v-model="form.event_date"
                                type="datetime-local"
                                class="mt-1 block w-full bg-surface-inset border border-border text-body focus:border-primary focus:ring-1 focus:ring-primary rounded-lg px-4 py-2"
                                required
                            />
                            <p v-if="form.errors.event_date" class="mt-1 text-sm text-danger">{{ form.errors.event_date }}</p>
                            <p class="mt-1 text-sm text-muted">
                                When will this event take place?
                            </p>
                        </div>

                        <!-- Lock Date -->
                        <div>
                            <FormLabel>Lock Date (Optional)</FormLabel>
                            <input
                                v-model="form.lock_date"
                                type="datetime-local"
                                class="mt-1 block w-full bg-surface-inset border border-border text-body focus:border-primary focus:ring-1 focus:ring-primary rounded-lg px-4 py-2"
                            />
                            <p v-if="form.errors.lock_date" class="mt-1 text-sm text-danger">{{ form.errors.lock_date }}</p>
                            <p class="mt-1 text-sm text-muted">
                                When should entries be locked? (entries cannot be changed after this date)
                            </p>
                        </div>

                        <!-- Event Type -->
                        <div>
                            <FormLabel>Event Type</FormLabel>
                            <select
                                v-model="form.event_type"
                                class="mt-1 block w-full bg-surface-inset border border-border text-body focus:border-primary focus:ring-1 focus:ring-primary rounded-lg px-4 py-2"
                                required
                            >
                                <option value="GameQuiz">Game Quiz</option>
                                <option value="AmericaSays">America Says</option>
                            </select>
                            <p v-if="form.errors.event_type" class="mt-1 text-sm text-danger">{{ form.errors.event_type }}</p>
                            <p class="mt-1 text-sm text-muted">
                                Choose the type of game for this event
                            </p>
                        </div>

                        <!-- Category -->
                        <div>
                            <TextField
                                v-model="form.category"
                                label="Category (Optional)"
                                :error="form.errors.category"
                            />
                            <p class="mt-1 text-sm text-muted">
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
                            <FormLabel>Status</FormLabel>
                            <select
                                v-model="form.status"
                                class="mt-1 block w-full bg-surface-inset border border-border text-body focus:border-primary focus:ring-1 focus:ring-primary rounded-lg px-4 py-2"
                            >
                                <option value="draft">Draft</option>
                                <option value="open">Open</option>
                                <option value="locked">Locked</option>
                                <option value="completed">Completed</option>
                            </select>
                            <p v-if="form.errors.status" class="mt-1 text-sm text-danger">{{ form.errors.status }}</p>
                            <p class="mt-1 text-sm text-muted">
                                Set the current status of the event
                            </p>
                        </div>

                        <!-- Status Descriptions -->
                        <div class="bg-surface-elevated rounded-lg p-4">
                            <h4 class="text-sm font-medium text-body mb-2">Status Guide:</h4>
                            <ul class="text-sm text-muted space-y-1">
                                <li><strong>Draft:</strong> Event is being created, not visible to users</li>
                                <li><strong>Open:</strong> Event is active and accepting entries</li>
                                <li><strong>Locked:</strong> Event is closed for new entries</li>
                                <li><strong>Completed:</strong> Event is finished and results are final</li>
                            </ul>
                        </div>
                    </form>
                </div>

                <!-- Next Steps Card -->
                <div class="mt-6 bg-primary/10 border border-primary/30 rounded-lg p-4">
                    <h4 class="font-medium text-primary mb-2">Next Steps</h4>
                    <p class="text-sm text-primary">
                        After creating the event, you'll be able to:
                    </p>
                    <ul class="mt-2 text-sm text-primary list-disc list-inside space-y-1">
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
