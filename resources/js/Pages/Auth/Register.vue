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

        <form @submit.prevent="submit">
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
                class="mt-4"
            />

            <TextField
                id="password"
                v-model="form.password"
                type="password"
                label="Password"
                :error="form.errors.password"
                required
                autocomplete="new-password"
                class="mt-4"
            />

            <TextField
                id="password_confirmation"
                v-model="form.password_confirmation"
                type="password"
                label="Confirm Password"
                :error="form.errors.password_confirmation"
                required
                autocomplete="new-password"
                class="mt-4"
            />

            <div class="flex items-center justify-end mt-4">
                <Link
                    :href="route('login')"
                    class="underline text-sm text-primary hover:text-warning rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-warning"
                >
                    Already registered?
                </Link>

                <Button variant="primary" class="ms-4" :disabled="form.processing">
                    Register
                </Button>
            </div>
        </form>
    </GuestLayout>
</template>
