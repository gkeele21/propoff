<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import Button from '@/Components/Base/Button.vue';
import TextField from '@/Components/Form/TextField.vue';
import Icon from '@/Components/Base/Icon.vue';

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
        <!-- Group Info Header -->
        <div class="mb-6 pb-6 border-b border-border">
            <h1 class="text-2xl font-bold text-body mb-2">Join {{ group.name }}</h1>
            <div class="bg-primary/10 border border-primary/30 rounded-lg p-4">
                <p class="text-sm text-primary font-semibold mb-1">Event</p>
                <p class="text-lg font-bold text-primary">{{ group.event.name }}</p>
                <p class="text-sm text-primary mt-2">
                    Event Date: {{ formatDate(group.event.event_date) }}
                </p>
            </div>
        </div>

        <!-- Join Form -->
        <form @submit.prevent="submit" class="space-y-4">
            <h3 class="text-lg font-semibold text-body">Your Information</h3>

            <TextField
                id="name"
                v-model="form.name"
                type="text"
                label="Your Name"
                :error="form.errors.name"
                required
                autofocus
                placeholder="Enter your name"
            />

            <TextField
                id="email"
                v-model="form.email"
                type="email"
                label="Your Email (Optional)"
                :error="form.errors.email"
                placeholder="your.email@example.com"
            />

            <div class="p-3 bg-primary/10 border border-primary/30 rounded-md">
                <p class="text-sm text-primary font-medium mb-1 flex items-center gap-2">
                    <Icon name="envelope" size="sm" />
                    Why provide an email?
                </p>
                <ul class="text-xs text-primary space-y-1 ml-6 list-disc">
                    <li>Track your entries across multiple events</li>
                    <li>View your history from past groups</li>
                    <li>If you've played before, use the same email to see your stats</li>
                    <li>Skip it if this is a one-time thing</li>
                </ul>
            </div>

            <Button
                type="submit"
                variant="primary"
                size="lg"
                class="w-full"
                :loading="form.processing"
            >
                Join Group
            </Button>
        </form>
    </GuestLayout>
</template>
