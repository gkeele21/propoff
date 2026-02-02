<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Button from '@/Components/Base/Button.vue';
import TextField from '@/Components/Form/TextField.vue';
import FormLabel from '@/Components/Form/FormLabel.vue';
import Confirm from '@/Components/Feedback/Confirm.vue';

const props = defineProps({
    event: Object,
});

const form = useForm({
    name: props.event.name,
    description: props.event.description,
    event_date: props.event.event_date?.substring(0, 16) || '', // Format for datetime-local
    lock_date: props.event.lock_date?.substring(0, 16) || '',
    status: props.event.status,
    category: props.event.category || '',
    event_type: props.event.event_type || 'GameQuiz',
});

const showDeleteConfirm = ref(false);

const submit = () => {
    form.patch(route('admin.events.update', props.event.id));
};

const deleteEvent = () => {
    router.delete(route('admin.events.destroy', props.event.id));
};
</script>

<template>
    <Head :title="`Edit ${event.name}`" />

    <AuthenticatedLayout>
        <!-- Header Bar with Actions -->
        <div class="bg-surface border-b border-border">
            <div class="max-w-7xl mx-auto px-6 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-body">Edit Event</h1>
                        <p class="text-muted text-sm mt-1">{{ event.name }}</p>
                    </div>
                    <div class="flex gap-3">
                        <Link :href="route('admin.events.show', event.id)">
                            <Button variant="outline" size="sm">
                                Cancel
                            </Button>
                        </Link>
                        <Button variant="danger" size="sm" @click="showDeleteConfirm = true">
                            Delete
                        </Button>
                        <Button variant="primary" size="sm" @click="submit" :disabled="form.processing">
                            Save
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
                                Entries cannot be changed after this date
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
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation -->
        <Confirm
            :show="showDeleteConfirm"
            title="Delete Event?"
            message="This will permanently delete this event and all associated questions and entries. This action cannot be undone."
            confirm-text="Delete"
            variant="danger"
            @confirm="deleteEvent"
            @close="showDeleteConfirm = false"
        />
    </AuthenticatedLayout>
</template>
