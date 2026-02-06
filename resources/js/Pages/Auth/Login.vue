<script setup>
import Checkbox from '@/Components/Form/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import Button from '@/Components/Base/Button.vue';
import TextField from '@/Components/Form/TextField.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <h1 class="text-2xl font-bold text-body mb-6 text-center">Welcome back</h1>

        <div v-if="status" class="mb-4 font-medium text-sm text-success">
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

            <TextField
                id="password"
                v-model="form.password"
                type="password"
                label="Password"
                :error="form.errors.password"
                required
                autocomplete="current-password"
            />

            <div class="flex items-center justify-between">
<Checkbox v-model="form.remember" label="Remember me" labelVariant="muted" />
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm text-warning hover:text-body transition-colors"
                >
                    Forgot password?
                </Link>
            </div>

            <Button type="submit" variant="primary" size="lg" class="w-full" :disabled="form.processing">
                Log in
            </Button>
        </form>

        <p class="mt-6 text-center text-sm text-muted">
            Don't have an account?
            <Link :href="route('register')" class="text-primary hover:text-body transition-colors font-medium">
                Sign up
            </Link>
        </p>
    </GuestLayout>
</template>
