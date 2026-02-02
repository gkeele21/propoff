<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';
import { useTheme } from '@/composables/useTheme';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const { theme, setTheme, bgMode, setBgMode } = useTheme();

const themeOptions = [
    { value: 'green', label: 'Green', color: '#57d025' },
    { value: 'blue', label: 'Blue', color: '#3b82f6' },
    { value: 'orange', label: 'Orange', color: '#f47612' },
];

const bgModeOptions = [
    { value: 'slate', label: 'Slate', color: '#404040' },
    { value: 'cream', label: 'Cream', color: '#f5f3ef' },
];
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-body leading-tight">Profile</h2>
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
                    <section class="max-w-xl">
                        <header>
                            <h2 class="text-lg font-medium text-body">Theme Preference</h2>
                            <p class="mt-1 text-sm text-muted">
                                Choose your preferred accent color for the app.
                            </p>
                        </header>

                        <div class="mt-6 flex gap-4">
                            <button
                                v-for="option in themeOptions"
                                :key="option.value"
                                type="button"
                                @click="setTheme(option.value)"
                                class="flex flex-col items-center gap-2 p-4 rounded-lg border-2 transition-all"
                                :class="[
                                    theme === option.value
                                        ? 'border-primary bg-surface-elevated'
                                        : 'border-border hover:border-border-strong hover:bg-surface-overlay',
                                ]"
                            >
                                <span
                                    class="w-10 h-10 rounded-full"
                                    :style="{ backgroundColor: option.color }"
                                ></span>
                                <span class="text-sm font-medium text-body">{{ option.label }}</span>
                            </button>
                        </div>

                        <header class="mt-8">
                            <h2 class="text-lg font-medium text-body">Background</h2>
                            <p class="mt-1 text-sm text-muted">
                                Choose your preferred page background color.
                            </p>
                        </header>

                        <div class="mt-6 flex gap-4">
                            <button
                                v-for="option in bgModeOptions"
                                :key="option.value"
                                type="button"
                                @click="setBgMode(option.value)"
                                class="flex flex-col items-center gap-2 p-4 rounded-lg border-2 transition-all"
                                :class="[
                                    bgMode === option.value
                                        ? 'border-primary bg-surface-elevated'
                                        : 'border-border hover:border-border-strong hover:bg-surface-overlay',
                                ]"
                            >
                                <span
                                    class="w-10 h-10 rounded-lg border border-border"
                                    :style="{ backgroundColor: option.color }"
                                ></span>
                                <span class="text-sm font-medium text-body">{{ option.label }}</span>
                            </button>
                        </div>
                    </section>
                </div>

                <div class="p-4 sm:p-8 bg-surface shadow sm:rounded-lg border border-border">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <div class="p-4 sm:p-8 bg-surface shadow sm:rounded-lg border border-border">
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
