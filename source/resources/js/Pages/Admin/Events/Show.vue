<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    PencilIcon,
    DocumentDuplicateIcon,
    TrashIcon,
    ChartBarIcon,
    DocumentTextIcon,
    ClipboardDocumentCheckIcon,
} from '@heroicons/vue/24/outline';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import PageHeader from '@/Components/PageHeader.vue';
import ToastNotification from '@/Components/ToastNotification.vue';

const props = defineProps({
    event: Object,
    stats: Object,
    invitations: {
        type: Array,
        default: () => []
    },
    availableGroups: {
        type: Array,
        default: () => []
    },
});

const showDeleteModal = ref(false);
const statusUpdating = ref(false);
const showCopiedToast = ref(false);

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const updateStatus = (newStatus) => {
    if (confirm(`Are you sure you want to change the event status to "${newStatus}"?`)) {
        statusUpdating.value = true;
        router.post(
            route('admin.events.updateStatus', props.event.id),
            { status: newStatus },
            {
                onFinish: () => {
                    statusUpdating.value = false;
                },
            }
        );
    }
};

const duplicateEvent = () => {
    if (confirm('Are you sure you want to duplicate this event?')) {
        router.post(route('admin.events.duplicate', props.event.id), {}, {
            onSuccess: () => {
                router.visit(route('admin.events.index'));
            }
        });
    }
};

const deleteEvent = () => {
    if (confirm('Are you sure you want to delete this event? This action cannot be undone.')) {
        router.delete(route('admin.events.destroy', props.event.id), {
            onSuccess: () => {
                router.visit(route('admin.events.index'));
            }
        });
    }
};

const getStatusClass = (status) => {
    const classes = {
        'draft': 'bg-gray-100 text-gray-800',
        'open': 'bg-propoff-green/20 text-propoff-dark-green',
        'locked': 'bg-propoff-orange/20 text-propoff-orange',
        'completed': 'bg-propoff-dark-green/20 text-propoff-dark-green',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

// Invitation management
const selectedGroupId = ref('');
const generatingInvitation = ref(false);

const generateInvitation = () => {
    if (!selectedGroupId.value) return;
    
    generatingInvitation.value = true;
    router.post(
        route('admin.events.generateInvitation', props.event.id),
        { group_id: selectedGroupId.value },
        {
            onFinish: () => {
                generatingInvitation.value = false;
                selectedGroupId.value = '';
            }
        }
    );
};

const copyInvitationLink = (token) => {
    const url = route('guest.join', token);
    navigator.clipboard.writeText(url).then(() => {
        showCopiedToast.value = true;
        setTimeout(() => {
            showCopiedToast.value = false;
        }, 3000);
    });
};

const deactivateInvitation = (invitationId) => {
    if (!confirm('Deactivate this invitation? Guests will no longer be able to use this link.')) {
        return;
    }
    
    router.post(route('admin.events.deactivateInvitation', [props.event.id, invitationId]));
};
</script>

<template>
    <Head :title="event.name" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader
                :title="event.name"
                :crumbs="[
                    { label: 'Admin Dashboard', href: route('admin.dashboard') },
                    { label: 'Events', href: route('admin.events.index') },
                    { label: event.name }
                ]"
            >
                <template #metadata>
                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                        :class="getStatusClass(event.status)"
                    >
                        {{ event.status }}
                    </span>
                    <span class="text-gray-400 mx-2">•</span>
                    <span>{{ formatDate(event.event_date) }}</span>
                </template>
                <template #actions>
                    <Link :href="route('admin.events.edit', event.id)">
                        <PrimaryButton>
                            <PencilIcon class="w-4 h-4 mr-2" />
                            Edit
                        </PrimaryButton>
                    </Link>
                </template>
            </PageHeader>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Quick Actions -->
                 <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <Link
                                :href="route('admin.events.event-questions.index', event.id)"
                                class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md border-2 border-gray-200 rounded-lg hover:border-propoff-blue hover:bg-propoff-blue/10 transition"
                            >
                                <div class="p-6 flex items-center">
                                    <DocumentTextIcon class="w-10 h-10 text-propoff-blue mr-4" />
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Manage Event</p>
                                        <p class="text-lg font-semibold text-gray-900">Questions</p>
                                    </div>
                                </div>
                            </Link>

                            <Link
                                :href="route('admin.events.event-answers.index', event.id)"
                                class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md border-2 border-gray-200 rounded-lg hover:border-propoff-blue hover:bg-propoff-blue/10 transition"
                            >
                                <div class="p-6 flex items-center">
                                    <ClipboardDocumentCheckIcon class="w-10 h-10 text-propoff-green mr-4" />
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Set Event</p>
                                        <p class="text-lg font-semibold text-gray-900">Answers</p>
                                    </div>
                                </div>
                            </Link>

                            <Link
                                :href="route('admin.events.captain-invitations.index', event.id)"
                                class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md border-2 border-gray-200 rounded-lg hover:border-propoff-blue hover:bg-propoff-blue/10 transition"
                            >
                                <div class="p-6 flex items-center">
                                    <svg class="w-10 h-10 text-propoff-blue mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Manage Captain</p>
                                        <p class="text-lg font-semibold text-gray-900">Invitations</p>
                                    </div>
                                </div>
                            </Link>

                            <!-- <Link
                                :href="route('admin.events.grading.index', event.id)"
                                class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition"
                            >
                                <div class="p-6 flex items-center">
                                    <ChartBarIcon class="w-10 h-10 text-propoff-dark-green mr-4" />
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">View</p>
                                        <p class="text-lg font-semibold text-gray-900">Grading Overview</p>
                                    </div>
                                </div>
                            </Link>

                            <Link
                                :href="route('admin.events.statistics', event.id)"
                                class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition"
                            >
                                <div class="p-6 flex items-center">
                                    <ChartBarIcon class="w-10 h-10 text-propoff-dark-green mr-4" />
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">View</p>
                                        <p class="text-lg font-semibold text-gray-900">Statistics</p>
                                    </div>
                                </div>
                            </Link> -->
                        </div>
                    </div>
                </div>
                
                <!-- Event Info -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Event Information</h3>
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium mt-2"
                                    :class="getStatusClass(event.status)"
                                >
                                    {{ event.status }}
                                </span>
                            </div>
                            <div class="flex space-x-2">
                                <button
                                    @click="duplicateEvent"
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                >
                                    <DocumentDuplicateIcon class="w-4 h-4 mr-2" />
                                    Duplicate
                                </button>
                                <button
                                    @click="deleteEvent"
                                    class="inline-flex items-center px-3 py-2 border border-propoff-red/30 shadow-sm text-sm leading-4 font-medium rounded-md text-propoff-red bg-white hover:bg-propoff-red/10"
                                >
                                    <TrashIcon class="w-4 h-4 mr-2" />
                                    Delete
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Description</p>
                                <p class="mt-1 text-gray-900">{{ event.description || 'No description' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Event Date</p>
                                <p class="mt-1 text-gray-900">{{ formatDate(event.event_date) }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Lock Date</p>
                                <p class="mt-1 text-gray-900">
                                    {{ event.lock_date ? formatDate(event.lock_date) : 'Not set' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Created</p>
                                <p class="mt-1 text-gray-900">{{ formatDate(event.created_at) }}</p>
                            </div>
                        </div>

                        <!-- Status Actions -->
                        <div class="mt-6 border-t pt-6">
                            <p class="text-sm font-medium text-gray-700 mb-3">Change Status</p>
                            <div class="flex space-x-2">
                                <button
                                    v-for="status in ['draft', 'open', 'locked', 'completed']"
                                    :key="status"
                                    @click="updateStatus(status)"
                                    :disabled="event.status === status || statusUpdating"
                                    class="px-4 py-2 text-sm font-medium rounded-md capitalize"
                                    :class="
                                        event.status === status
                                            ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                            : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'
                                    "
                                >
                                    {{ status }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistics</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="bg-propoff-blue/10 rounded-lg p-4">
                                <p class="text-sm text-propoff-blue font-medium">Total Questions</p>
                                <p class="text-2xl font-bold text-propoff-blue">{{ stats.total_questions }}</p>
                            </div>
                            <div class="bg-propoff-green/10 rounded-lg p-4">
                                <p class="text-sm text-propoff-green font-medium">Total Entries</p>
                                <p class="text-2xl font-bold text-propoff-dark-green">{{ stats.total_entries }}</p>
                            </div>
                            <div class="bg-propoff-dark-green/10 rounded-lg p-4">
                                <p class="text-sm text-propoff-dark-green font-medium">Completed</p>
                                <p class="text-2xl font-bold text-propoff-dark-green">{{ stats.completed_entries }}</p>
                            </div>
                            <div class="bg-propoff-orange/10 rounded-lg p-4">
                                <p class="text-sm text-propoff-orange font-medium">Average Score</p>
                                <p class="text-2xl font-bold text-propoff-orange">{{ stats.average_score?.toFixed(1) || 0 }}%</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Event Invitations -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Event Invitations</h3>
                        
                        <!-- Generate Invitation Form -->
                        <div class="mb-6 bg-gray-50 p-4 rounded-lg">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Generate invitation link for group:
                            </label>
                            <div class="flex gap-2">
                                <select 
                                    v-model="selectedGroupId" 
                                    class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-propoff-blue focus:ring-propoff-blue/50"
                                >
                                    <option value="">Select a group...</option>
                                    <option 
                                        v-for="group in availableGroups" 
                                        :key="group.id" 
                                        :value="group.id"
                                    >
                                        {{ group.name }}
                                    </option>
                                </select>
                                <button
                                    @click="generateInvitation"
                                    :disabled="!selectedGroupId || generatingInvitation"
                                    class="px-4 py-2 bg-propoff-blue text-white rounded-md hover:bg-propoff-blue/80 disabled:opacity-50 disabled:cursor-not-allowed font-semibold"
                                >
                                    {{ generatingInvitation ? 'Generating...' : 'Generate Link' }}
                                </button>
                            </div>
                        </div>

                        <!-- Existing Invitations List -->
                        <div v-if="invitations && invitations.length > 0" class="space-y-3">
                            <div
                                v-for="invitation in invitations"
                                :key="invitation.id"
                                class="border border-gray-200 rounded-lg p-4"
                            >
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium text-gray-900">{{ invitation.group.name }}</span>
                                        <span
                                            :class="invitation.is_active ? 'bg-propoff-green/20 text-propoff-dark-green' : 'bg-gray-100 text-gray-800'"
                                            class="px-2 py-1 text-xs rounded-full font-medium"
                                        >
                                            {{ invitation.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                    <button
                                        v-if="invitation.is_active"
                                        @click="deactivateInvitation(invitation.id)"
                                        class="px-3 py-1 bg-propoff-red text-white text-sm rounded hover:bg-propoff-red/80 font-semibold"
                                    >
                                        Deactivate
                                    </button>
                                </div>
                                
                                <div class="flex items-center gap-2 mb-2">
                                    <input
                                        :value="route('guest.join', invitation.token)"
                                        readonly
                                        class="flex-1 text-sm bg-gray-50 border border-gray-300 rounded px-3 py-2 font-mono"
                                    />
                                    <button
                                        @click="copyInvitationLink(invitation.token)"
                                        class="px-4 py-2 bg-gray-600 text-white text-sm rounded hover:bg-gray-700 font-semibold"
                                    >
                                        Copy Link
                                    </button>
                                </div>
                                
                                <p class="text-xs text-gray-500">
                                    Used {{ invitation.times_used }} {{ invitation.times_used === 1 ? 'time' : 'times' }}
                                </p>
                            </div>
                        </div>
                        
                        <div v-else class="text-center py-8 text-gray-500">
                            <p>No invitations generated yet</p>
                            <p class="text-sm mt-1">Select a group above to create an invitation link</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toast Notification -->
        <ToastNotification :show="showCopiedToast" message="Invitation link copied to clipboard!" />
    </AuthenticatedLayout>
</template>
