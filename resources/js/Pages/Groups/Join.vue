<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    group: Object,
});

const form = useForm({
    code: props.group.code,
    name: '',
    email: '',
});

const submit = () => {
    form.post(route('groups.join'));
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Join Group" />

    <GuestLayout>
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div class="w-full sm:max-w-2xl mt-6 px-6 py-8 bg-white shadow-md overflow-hidden sm:rounded-lg">
                <!-- Group Info -->
                <div class="mb-8 pb-6 border-b">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Join {{ group.name }}</h2>
                    <div class="bg-propoff-blue/10 border border-propoff-blue/30 rounded-lg p-4 mt-4">
                        <p class="text-sm text-propoff-blue font-semibold mb-1">Event</p>
                        <p class="text-lg font-bold text-propoff-blue">{{ group.event.name }}</p>
                        <p class="text-sm text-propoff-blue mt-2">
                            Event Date: {{ formatDate(group.event.event_date) }}
                        </p>
                    </div>
                </div>

                <!-- Join Form -->
                <form @submit.prevent="submit">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Your Information</h3>

                    <div class="mb-4">
                        <InputLabel for="name" value="Your Name" />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full"
                            required
                            autofocus
                            placeholder="Enter your name"
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div class="mb-6">
                        <InputLabel for="email" value="Your Email (Optional - Recommended)" />
                        <TextInput
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="mt-1 block w-full"
                            placeholder="your.email@example.com (optional)"
                        />
                        <div class="mt-2 p-3 bg-propoff-blue/10 border border-propoff-blue/30 rounded-md">
                            <p class="text-sm text-propoff-blue font-medium mb-1">
                                📧 Why provide an email?
                            </p>
                            <ul class="text-xs text-propoff-blue space-y-1 ml-4 list-disc">
                                <li>Track your entries across multiple events</li>
                                <li>View your history from past groups</li>
                                <li>If you've played before, use the same email to see your stats</li>
                                <li>Skip it if this is a one-time thing</li>
                            </ul>
                        </div>
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div class="flex items-center justify-end">
                        <PrimaryButton
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            Join Group
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </GuestLayout>
</template>
