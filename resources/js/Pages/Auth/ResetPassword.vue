<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import Button from '@/Components/Base/Button.vue';
import TextField from '@/Components/Form/TextField.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Reset Password" />

        <h1 class="text-2xl font-bold text-body mb-6 text-center">Reset your password</h1>

        <form @submit.prevent="submit" class="space-y-4">
            <TextField
                id="email"
                v-model="form.email"
                type="email"
                label="Email"
                :error="form.errors.email"
                required
                autofocus
                autocomplete="username"
            />

            <TextField
                id="password"
                v-model="form.password"
                type="password"
                label="New Password"
                :error="form.errors.password"
                required
                autocomplete="new-password"
            />

            <TextField
                id="password_confirmation"
                v-model="form.password_confirmation"
                type="password"
                label="Confirm New Password"
                :error="form.errors.password_confirmation"
                required
                autocomplete="new-password"
            />

            <Button type="submit" variant="primary" size="lg" class="w-full" :disabled="form.processing">
                Reset Password
            </Button>
        </form>
    </GuestLayout>
</template>
