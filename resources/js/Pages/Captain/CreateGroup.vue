<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import Button from '@/Components/Base/Button.vue';
import TextField from '@/Components/Form/TextField.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    event: {
        type: Object,
        required: true,
    },
    invitation: {
        type: Object,
        required: true,
    },
    isGuest: {
        type: Boolean,
        default: false,
    },
});

const form = useForm({
    name: '',
    description: '',
    grading_source: 'captain',
    captain_name: '',
    captain_email: '',
});

const submit = () => {
    form.post(route('captain.groups.store', [props.event.id, props.invitation.token]));
};

const LayoutComponent = props.isGuest ? GuestLayout : AuthenticatedLayout;
</script>

<template>
    <Head title="Create Group" />

    <component :is="LayoutComponent">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create Group for {{ event.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <!-- Invitation Info -->
                <div class="bg-primary/10 border border-primary/30 rounded-lg p-4 mb-6">
                    <h3 class="text-lg font-semibold text-primary mb-2">{{ event.name }}</h3>
                    <p class="text-sm text-primary mb-2">
                        You've been invited to play PropOff and to create your own group. This makes you the captain of your group for this event. That means you can add/remove questions, set the correct answers (if you choose that option), send an invite for others to join your group, and promote anyone else to be a captain.
                    </p>
                    <div class="text-sm text-primary">
                        <p v-if="invitation.max_uses">
                            Uses: {{ invitation.times_used }} / {{ invitation.max_uses }}
                        </p>
                        <p v-if="invitation.expires_at">
                            Expires: {{ new Date(invitation.expires_at).toLocaleString() }}
                        </p>
                    </div>
                </div>

                <!-- Create Group Form -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6">
                        <!-- Captain Info (for guests only) -->
                        <div v-if="isGuest" class="mb-8 pb-6 border-b">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Your Information</h3>

                            <div class="mb-4">
                                <TextField
                                    id="captain_name"
                                    v-model="form.captain_name"
                                    type="text"
                                    label="Your Name"
                                    :error="form.errors.captain_name"
                                    required
                                    autofocus
                                    placeholder="Enter your name"
                                />
                            </div>

                            <div class="mb-4">
                                <TextField
                                    id="captain_email"
                                    v-model="form.captain_email"
                                    type="email"
                                    label="Your Email (Optional - Recommended)"
                                    :error="form.errors.captain_email"
                                    placeholder="your.email@example.com (optional)"
                                />
                                <div class="mt-2 p-3 bg-primary/10 border border-primary/30 rounded-md">
                                    <p class="text-sm text-primary font-medium mb-1">
                                        📧 Why provide an email?
                                    </p>
                                    <ul class="text-xs text-primary space-y-1 ml-4 list-disc">
                                        <li>Track your captain history across multiple events</li>
                                        <li>View all your past entries and groups in one place</li>
                                        <li>If you've been a captain before, use the same email to see your history</li>
                                        <li>Skip it if this is a one-time event and you don't need history</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Group Info -->
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Group Information</h3>
                        </div>

                        <div class="mb-6">
                            <TextField
                                id="name"
                                v-model="form.name"
                                type="text"
                                label="Group Name"
                                :error="form.errors.name"
                                required
                                autofocus
                                placeholder="Enter your group name"
                            />
                        </div>

                        <div class="mb-6">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                Description (Optional)
                            </label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                class="mt-1 block w-full border-gray-300 focus:border-primary focus:ring-primary/50 rounded-md shadow-sm"
                                rows="3"
                                placeholder="Enter a description for your group"
                            ></textarea>
                            <p v-if="form.errors.description" class="mt-1 text-sm text-danger">
                                {{ form.errors.description }}
                            </p>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Grading Source
                            </label>
                            <div class="mt-2 space-y-3">
                                <label class="flex items-start p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition">
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
                                            Best for live events where you want immediate scoring.
                                        </div>
                                    </div>
                                </label>

                                <label class="flex items-start p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition">
                                    <input
                                        type="radio"
                                        v-model="form.grading_source"
                                        value="admin"
                                        class="mt-1 mr-3"
                                    />
                                    <div>
                                        <div class="font-semibold">Admin Grading</div>
                                        <div class="text-sm text-gray-600">
                                            The website admin sets answers after the event ends. Your group will be graded
                                            based on the admin's answers. Best for events where grading happens later.
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <p v-if="form.errors.grading_source" class="mt-1 text-sm text-danger">
                                {{ form.errors.grading_source }}
                            </p>
                        </div>

                        <div class="flex items-center justify-end">
                            <Button
                                variant="primary"
                                :disabled="form.processing"
                            >
                                Create Group & Become Captain
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </component>
</template>
