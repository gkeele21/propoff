<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import Button from '@/Components/Base/Button.vue';
import TextField from '@/Components/Form/TextField.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Confirm Password" />

        <h1 class="text-2xl font-bold text-body mb-4 text-center">Confirm your password</h1>

        <p class="mb-6 text-sm text-muted text-center">
            This is a secure area. Please confirm your password before continuing.
        </p>

        <form @submit.prevent="submit" class="space-y-4">
            <TextField
                id="password"
                v-model="form.password"
                type="password"
                label="Password"
                :error="form.errors.password"
                required
                autocomplete="current-password"
                autofocus
            />

            <Button type="submit" variant="primary" size="lg" class="w-full" :disabled="form.processing">
                Confirm
            </Button>
        </form>
    </GuestLayout>
</template>
