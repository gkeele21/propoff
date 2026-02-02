<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import Button from '@/Components/Base/Button.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <GuestLayout>
        <Head title="Email Verification" />

        <h1 class="text-2xl font-bold text-body mb-4 text-center">Verify your email</h1>

        <p class="mb-6 text-sm text-muted text-center">
            Thanks for signing up! Please verify your email address by clicking the link we just sent you.
        </p>

        <div v-if="verificationLinkSent" class="mb-4 font-medium text-sm text-success text-center">
            A new verification link has been sent to your email address.
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <Button type="submit" variant="primary" size="lg" class="w-full" :disabled="form.processing">
                Resend Verification Email
            </Button>
        </form>

        <p class="mt-6 text-center text-sm text-muted">
            Wrong account?
            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="text-warning hover:text-body transition-colors font-medium"
            >
                Log out
            </Link>
        </p>
    </GuestLayout>
</template>
