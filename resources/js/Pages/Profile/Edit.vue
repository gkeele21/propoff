<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/Base/PageHeader.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import SetPasswordForm from './Partials/SetPasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    hasPassword: {
        type: Boolean,
        default: true,
    },
    hasEmail: {
        type: Boolean,
        default: true,
    },
});
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader title="Profile" />
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-surface shadow sm:rounded-lg border border-border">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <div class="p-4 sm:p-8 bg-surface shadow sm:rounded-lg border border-border">
                    <!-- Show SetPasswordForm if user doesn't have a password yet -->
                    <SetPasswordForm v-if="!hasPassword && hasEmail" class="max-w-xl" />
                    <!-- Show UpdatePasswordForm if user already has a password -->
                    <UpdatePasswordForm v-else-if="hasPassword" class="max-w-xl" />
                    <!-- Show message if user needs to set email first -->
                    <div v-else class="max-w-xl">
                        <h2 class="text-lg font-medium text-body">Set Password</h2>
                        <p class="mt-1 text-sm text-muted">
                            Please add your email address above before setting a password.
                        </p>
                    </div>
                </div>

                <!-- Only show delete form if user has a password (required for confirmation) -->
                <div v-if="hasPassword" class="p-4 sm:p-8 bg-surface shadow sm:rounded-lg border border-border">
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
