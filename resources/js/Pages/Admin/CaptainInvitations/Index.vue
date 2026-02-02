<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import PageHeader from '@/Components/PageHeader.vue';
import ToastNotification from '@/Components/ToastNotification.vue';

const props = defineProps({
    event: {
        type: Object,
        required: true,
    },
    invitations: {
        type: Array,
        required: true,
    },
});

const showCopiedToast = ref(false);

const createForm = useForm({
    max_uses: null,
    expires_at: null,
});

const submitCreate = () => {
    createForm.post(route('admin.events.captain-invitations.store', props.event.id), {
        preserveScroll: true,
        onSuccess: () => {
            createForm.reset();
        },
    });
};

const deactivateInvitation = (invitationId) => {
    router.post(route('admin.events.captain-invitations.deactivate', [props.event.id, invitationId]), {}, {
        preserveScroll: true,
    });
};

const reactivateInvitation = (invitationId) => {
    router.post(route('admin.events.captain-invitations.reactivate', [props.event.id, invitationId]), {}, {
        preserveScroll: true,
    });
};

const deleteInvitation = (invitationId) => {
    if (confirm('Are you sure you want to delete this invitation?')) {
        router.delete(route('admin.events.captain-invitations.destroy', [props.event.id, invitationId]), {
            preserveScroll: true,
        });
    }
};

const copyUrl = (url) => {
    navigator.clipboard.writeText(url);
    showCopiedToast.value = true;
    setTimeout(() => {
        showCopiedToast.value = false;
    }, 3000);
};
</script>

<template>
    <Head title="Captain Invitations" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader
                title="Captain Invitations"
                subtitle="Manage captain invitation links for this event"
                :crumbs="[
                    { label: 'Admin Dashboard', href: route('admin.dashboard') },
                    { label: 'Events', href: route('admin.events.index') },
                    { label: event.name, href: route('admin.events.show', event.id) },
                    { label: 'Captain Invitations' }
                ]"
            >
                <template #metadata>
                    <span class="font-medium text-body">{{ event.name }}</span>
                    <span class="text-subtle mx-2">•</span>
                    <span class="text-muted">{{ invitations.length }} invitation{{ invitations.length !== 1 ? 's' : '' }}</span>
                </template>
            </PageHeader>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Info Banner -->
                <div class="bg-primary/10 border border-primary/30 rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-primary mb-2">About Captain Invitations</h3>
                    <p class="text-sm text-primary">
                        Captain invitations allow users to create groups and become captains for this event.
                        Share the invitation URL with users who should have captain privileges.
                    </p>
                </div>

                <!-- Create Invitation -->
                <div class="bg-surface overflow-hidden shadow-sm sm:rounded-lg border border-border">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-body">Create New Captain Invitation Link</h3>
                        </div>

                        <form @submit.prevent="submitCreate" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-muted mb-2">
                                        Max Uses (Optional)
                                    </label>
                                    <input
                                        v-model="createForm.max_uses"
                                        type="number"
                                        min="1"
                                        class="w-full border-border bg-surface-inset text-body focus:border-primary focus:ring-primary/50 rounded-md shadow-sm"
                                        placeholder="Unlimited"
                                    />
                                    <p class="text-xs text-subtle mt-1">
                                        Leave empty for unlimited uses
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-muted mb-2">
                                        Expires At (Optional)
                                    </label>
                                    <input
                                        v-model="createForm.expires_at"
                                        type="datetime-local"
                                        class="w-full border-border bg-surface-inset text-body focus:border-primary focus:ring-primary/50 rounded-md shadow-sm"
                                    />
                                    <p class="text-xs text-subtle mt-1">
                                        Leave empty for no expiration
                                    </p>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button
                                    type="submit"
                                    :disabled="createForm.processing"
                                    class="bg-primary hover:bg-primary/80 text-white px-4 py-2 rounded font-semibold disabled:opacity-50"
                                >
                                    Generate Link
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Invitations List -->
                <div class="bg-surface overflow-hidden shadow-sm sm:rounded-lg border border-border">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-body mb-4">Active Invitations ({{ invitations.length }})</h3>

                        <div v-if="invitations.length === 0" class="text-center py-12">
                            <p class="text-muted">No captain invitations yet.</p>
                        </div>

                        <div v-else class="space-y-4">
                            <div
                                v-for="invitation in invitations"
                                :key="invitation.id"
                                class="border rounded-lg p-4"
                                :class="invitation.can_be_used ? 'border-success/30 bg-success/10' : 'border-border bg-surface-elevated'"
                            >
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <!-- Status Badge -->
                                        <div class="flex gap-2 mb-3">
                                            <span
                                                v-if="invitation.is_active && invitation.can_be_used"
                                                class="px-2 py-1 text-xs font-semibold rounded bg-success/10 text-success"
                                            >
                                                Active
                                            </span>
                                            <span
                                                v-else
                                                class="px-2 py-1 text-xs font-semibold rounded bg-danger/10 text-danger"
                                            >
                                                Inactive
                                            </span>
                                            <span
                                                v-if="invitation.max_uses"
                                                class="px-2 py-1 text-xs font-semibold rounded bg-primary/10 text-primary"
                                            >
                                                {{ invitation.times_used }} / {{ invitation.max_uses }} uses
                                            </span>
                                            <span
                                                v-else
                                                class="px-2 py-1 text-xs font-semibold rounded bg-primary/10 text-primary"
                                            >
                                                {{ invitation.times_used }} uses
                                            </span>
                                        </div>

                                        <!-- Invitation URL -->
                                        <div class="mb-3">
                                            <label class="text-xs text-muted block mb-1">Invitation URL</label>
                                            <div class="flex gap-2">
                                                <input
                                                    :value="invitation.url"
                                                    readonly
                                                    class="flex-1 text-sm border-border rounded-md bg-surface-inset text-body font-mono"
                                                />
                                                <button
                                                    @click="copyUrl(invitation.url)"
                                                    class="bg-surface-overlay hover:bg-surface-elevated text-body px-3 py-1 rounded text-sm font-semibold border border-border"
                                                >
                                                    Copy
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Details -->
                                        <div class="grid grid-cols-2 gap-4 text-sm">
                                            <div>
                                                <span class="text-muted">Created by:</span>
                                                <span class="font-semibold text-body ml-2">{{ invitation.creator.name }}</span>
                                            </div>
                                            <div>
                                                <span class="text-muted">Created:</span>
                                                <span class="text-body ml-2">{{ new Date(invitation.created_at).toLocaleDateString() }}</span>
                                            </div>
                                            <div v-if="invitation.expires_at">
                                                <span class="text-muted">Expires:</span>
                                                <span class="text-body ml-2">{{ new Date(invitation.expires_at).toLocaleString() }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex flex-col gap-2 ml-4">
                                        <button
                                            v-if="invitation.is_active"
                                            @click="deactivateInvitation(invitation.id)"
                                            class="text-sm bg-warning hover:bg-warning/80 text-white px-3 py-1 rounded"
                                        >
                                            Deactivate
                                        </button>
                                        <button
                                            v-else
                                            @click="reactivateInvitation(invitation.id)"
                                            class="text-sm bg-success hover:bg-success text-white px-3 py-1 rounded"
                                        >
                                            Reactivate
                                        </button>
                                        <button
                                            v-if="invitation.times_used === 0"
                                            @click="deleteInvitation(invitation.id)"
                                            class="text-sm bg-danger hover:bg-danger/80 text-white px-3 py-1 rounded"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toast Notification -->
        <ToastNotification :show="showCopiedToast" message="Invitation URL copied to clipboard!" />
    </AuthenticatedLayout>
</template>
