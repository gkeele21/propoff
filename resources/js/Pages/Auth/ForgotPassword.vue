<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import Button from '@/Components/Base/Button.vue';
import TextField from '@/Components/Form/TextField.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Forgot Password" />

        <h1 class="text-2xl font-bold text-body mb-4 text-center">Forgot password?</h1>

        <p class="mb-6 text-sm text-muted text-center">
            No problem. Enter your email and we'll send you a reset link.
        </p>

        <div v-if="status" class="mb-4 font-medium text-sm text-success text-center">
            {{ status }}
        </div>

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

            <Button type="submit" variant="primary" size="lg" class="w-full" :disabled="form.processing">
                Email Password Reset Link
            </Button>
        </form>

        <p class="mt-6 text-center text-sm text-muted">
            Remember your password?
            <Link :href="route('login')" class="text-warning hover:text-body transition-colors font-medium">
                Log in
            </Link>
        </p>
    </GuestLayout>
</template>
