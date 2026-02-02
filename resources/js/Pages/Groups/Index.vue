<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { PlusIcon, UserGroupIcon } from '@heroicons/vue/24/outline';
import Button from '@/Components/Base/Button.vue';
import TextField from '@/Components/Form/TextField.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { ref } from 'vue';

const props = defineProps({
    userGroups: Array,
    publicGroups: Array,
});

const showJoinModal = ref(false);
const joinForm = useForm({
    code: '',
});

const joinGroup = () => {
    joinForm.post(route('groups.join'), {
        onSuccess: () => {
            showJoinModal.value = false;
            joinForm.reset();
        },
    });
};
</script>

<template>
    <Head title="Groups" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader
                title="Groups"
                subtitle="Your groups and available groups to join"
                :crumbs="[
                    { label: 'Dashboard', href: route('dashboard') },
                    { label: 'Groups' }
                ]"
            >
                <template #actions>
                    <button
                        @click="showJoinModal = true"
                        class="inline-flex items-center px-4 py-2 bg-surface-elevated border border-border rounded-md font-semibold text-xs text-body uppercase tracking-widest hover:bg-surface-overlay"
                    >
                        Join Group
                    </button>
                    <Link :href="route('groups.create')">
                        <Button variant="primary">
                            <PlusIcon class="w-5 h-5 mr-2" />
                            Create Group
                        </Button>
                    </Link>
                </template>
            </PageHeader>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- My Groups -->
                <div class="bg-surface overflow-hidden shadow-sm sm:rounded-lg border border-border">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-body mb-4">My Groups</h3>

                        <div v-if="userGroups.length === 0" class="text-center py-8 text-muted">
                            You're not a member of any groups yet.
                        </div>

                        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <Link
                                v-for="group in userGroups"
                                :key="group.id"
                                :href="route('groups.show', group.id)"
                                class="border border-border rounded-lg p-4 hover:shadow-md hover:bg-surface-elevated transition"
                            >
                                <div class="flex items-start gap-3">
                                    <UserGroupIcon class="w-8 h-8 text-warning flex-shrink-0" />
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-semibold text-body truncate">{{ group.name }}</h4>
                                        <p class="text-sm text-muted mt-1 line-clamp-2">{{ group.description }}</p>
                                        <div class="mt-2 flex items-center text-sm text-subtle">
                                            <span>{{ group.users_count }} members</span>
                                            <span class="mx-2">•</span>
                                            <span class="font-mono text-xs">{{ group.code }}</span>
                                        </div>
                                    </div>
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Public Groups -->
                <div class="bg-surface overflow-hidden shadow-sm sm:rounded-lg border border-border">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-body mb-4">Public Groups</h3>

                        <div v-if="publicGroups.length === 0" class="text-center py-8 text-muted">
                            No public groups available.
                        </div>

                        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div
                                v-for="group in publicGroups"
                                :key="group.id"
                                class="border border-border rounded-lg p-4 hover:shadow-md hover:bg-surface-elevated transition"
                            >
                                <div class="flex items-start gap-3">
                                    <UserGroupIcon class="w-8 h-8 text-muted flex-shrink-0" />
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-semibold text-body truncate">{{ group.name }}</h4>
                                        <p class="text-sm text-muted mt-1 line-clamp-2">{{ group.description }}</p>
                                        <div class="mt-2 text-sm text-subtle">
                                            {{ group.users_count }} members
                                        </div>
                                        <Link
                                            :href="route('groups.show', group.id)"
                                            class="mt-3 inline-block text-primary hover:text-primary/80 text-sm"
                                        >
                                            View Group →
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Join Group Modal -->
        <div
            v-if="showJoinModal"
            class="fixed inset-0 bg-black/80 flex items-center justify-center z-50"
            @click.self="showJoinModal = false"
        >
            <div class="bg-surface-elevated rounded-lg p-6 max-w-md w-full mx-4 border border-border">
                <h3 class="text-lg font-semibold text-body mb-4">Join a Group</h3>
                <form @submit.prevent="joinGroup">
                    <div class="mb-4">
                        <TextField
                            id="code"
                            v-model="joinForm.code"
                            type="text"
                            label="Group Code"
                            placeholder="Enter 8-character code"
                            required
                            autofocus
                        />
                        <p class="mt-1 text-sm text-muted">
                            Enter the group code provided by the group creator
                        </p>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button
                            type="button"
                            @click="showJoinModal = false"
                            class="px-4 py-2 text-muted hover:text-body"
                        >
                            Cancel
                        </button>
                        <Button variant="primary" type="submit" :disabled="joinForm.processing">
                            Join Group
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
