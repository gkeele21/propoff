<script setup>
import Button from '@/Components/Base/Button.vue';
import TextField from '@/Components/Form/TextField.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name || '',
    email: user.email || '',
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-body">Profile Information</h2>

            <p class="mt-1 text-sm text-muted">
                Update your account's profile information and email address.
            </p>
        </header>

        <form @submit.prevent="form.patch(route('profile.update'))" class="mt-6 space-y-6">
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

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="text-sm mt-2 text-body">
                    Your email address is unverified.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="underline text-sm text-muted hover:text-body rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary/50"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 font-medium text-sm text-success"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <Button type="submit" variant="primary" :disabled="form.processing">Save</Button>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" class="text-sm text-muted">Saved.</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
