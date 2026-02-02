<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import Button from '@/Components/Base/Button.vue';
import TextField from '@/Components/Form/TextField.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <h1 class="text-2xl font-bold text-body mb-6 text-center">Create your account</h1>

        <form @submit.prevent="submit" class="space-y-4">
            <TextField
                id="name"
                v-model="form.name"
                type="text"
                label="Name"
                :error="form.errors.name"
                required
                autofocus
                autocomplete="name"
            />

            <TextField
                id="email"
                v-model="form.email"
                type="email"
                label="Email"
                :error="form.errors.email"
                required
                autocomplete="username"
            />

            <TextField
                id="password"
                v-model="form.password"
                type="password"
                label="Password"
                :error="form.errors.password"
                required
                autocomplete="new-password"
            />

            <TextField
                id="password_confirmation"
                v-model="form.password_confirmation"
                type="password"
                label="Confirm Password"
                :error="form.errors.password_confirmation"
                required
                autocomplete="new-password"
            />

            <Button type="submit" variant="primary" size="lg" class="w-full" :disabled="form.processing">
                Register
            </Button>
        </form>

        <p class="mt-6 text-center text-sm text-muted">
            Already have an account?
            <Link :href="route('login')" class="text-warning hover:text-body transition-colors font-medium">
                Log in
            </Link>
        </p>
    </GuestLayout>
</template>
