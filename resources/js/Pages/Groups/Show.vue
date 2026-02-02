<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { UserGroupIcon, PencilIcon, ArrowRightOnRectangleIcon } from '@heroicons/vue/24/outline';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { computed } from 'vue';

const props = defineProps({
    group: Object,
    recentEntries: Array,
    isMember: Boolean,
    isCaptain: Boolean,
    stats: Object,
});

const deleteForm = useForm({});

const leaveGroup = () => {
    if (confirm('Are you sure you want to leave this group?')) {
        router.post(route('groups.leave', props.group.id));
    }
};

const deleteGroup = () => {
    if (confirm('Are you sure you want to delete this group? This action cannot be undone.')) {
        deleteForm.delete(route('groups.destroy', props.group.id));
    }
};

const copyGroupCode = () => {
    navigator.clipboard.writeText(props.group.code || props.group.join_code);
    alert('Group code copied to clipboard!');
};

// Determine which member list to use (group.users or group.members)
const membersList = computed(() => {
    return props.group.members || props.group.users || [];
});

// Format datetime for display
const formatDateTime = (datetime) => {
    if (!datetime) return 'Not set';
    return new Date(datetime).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    });
};
</script>

<template>
    <Head :title="group.name" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader
                :title="group.name"
                :crumbs="[
                    { label: 'Dashboard', href: route('dashboard') },
                    { label: 'Groups', href: route('groups.index') },
                    { label: group.name }
                ]"
            >
                <template #metadata>
                    <UserGroupIcon class="w-5 h-5 text-warning inline mr-2" />
                    <span>{{ membersList.length }} members</span>
                    <template v-if="isCaptain">
                        <span class="text-gray-400 mx-2">•</span>
                        <span class="text-sm px-2 py-1 rounded bg-warning/20 text-warning">Captain</span>
                    </template>
                </template>
                <template #actions>
                    <Link
                        v-if="$page.props.auth.user.id === group.created_by"
                        :href="route('groups.edit', group.id)"
                        class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
                    >
                        <PencilIcon class="w-4 h-4 mr-2" />
                        Edit
                    </Link>
                    <button
                        v-if="isMember && $page.props.auth.user.id !== group.created_by"
                        @click="leaveGroup"
                        class="inline-flex items-center px-4 py-2 bg-danger border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-danger/80"
                    >
                        <ArrowRightOnRectangleIcon class="w-4 h-4 mr-2" />
                        Leave Group
                    </button>
                </template>
            </PageHeader>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Quick Actions (Captain Only) -->
                <div v-if="isCaptain" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <Link
                                :href="route('groups.invitation', group.id)"
                                class="flex flex-col items-center p-6 border-2 border-warning/20 bg-warning/10 rounded-lg hover:border-warning hover:bg-warning/20 transition"
                            >
                                <svg class="w-12 h-12 text-warning mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v6a2 2 0 01-2 2H8a2 2 0 01-2-2V9"></path>
                                </svg>
                                <span class="font-semibold">Invite Members</span>
                                <span class="text-sm text-gray-600 text-center mt-1">Get invitation link & QR code</span>
                            </Link>

                            <Link
                                :href="route('groups.questions.index', group.id)"
                                class="flex flex-col items-center p-6 border-2 border-gray-200 rounded-lg hover:border-primary hover:bg-primary/10 transition"
                            >
                                <svg class="w-12 h-12 text-primary mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="font-semibold">Manage Questions</span>
                                <span class="text-sm text-gray-600 text-center mt-1">Add, edit, or remove questions</span>
                            </Link>

                            <Link
                                :href="route('groups.grading.index', group.id)"
                                class="flex flex-col items-center p-6 border-2 border-gray-200 rounded-lg hover:border-success hover:bg-success/10 transition"
                            >
                                <svg class="w-12 h-12 text-success mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                </svg>
                                <span class="font-semibold">Set Answers</span>
                                <span class="text-sm text-gray-600 text-center mt-1">Set answers and calculate scores</span>
                            </Link>

                            <Link
                                :href="route('groups.members.index', group.id)"
                                class="flex flex-col items-center p-6 border-2 border-gray-200 rounded-lg hover:border-gray-400 hover:bg-gray-100 transition"
                            >
                                <svg class="w-12 h-12 text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <span class="font-semibold">Manage Members</span>
                                <span class="text-sm text-gray-600 text-center mt-1">View and manage group members</span>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Statistics (Captain Only) -->
                <div v-if="isCaptain && stats" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Statistics</h3>

                        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                            <div class="text-center p-4 bg-warning/10 rounded-lg">
                                <p class="text-3xl font-bold text-warning">{{ stats.total_members }}</p>
                                <p class="text-sm text-gray-600">Members</p>
                            </div>
                            <div class="text-center p-4 bg-warning/20 rounded-lg">
                                <p class="text-3xl font-bold text-warning">{{ stats.total_captains }}</p>
                                <p class="text-sm text-gray-600">Captains</p>
                            </div>
                            <div class="text-center p-4 bg-primary/10 rounded-lg">
                                <p class="text-3xl font-bold text-primary">{{ stats.total_questions }}</p>
                                <p class="text-sm text-gray-600">Questions</p>
                            </div>
                            <div class="text-center p-4 bg-success/10 rounded-lg">
                                <p class="text-3xl font-bold text-success">{{ stats.total_entries }}</p>
                                <p class="text-sm text-gray-600">Entries</p>
                            </div>
                            <div class="text-center p-4 bg-success/10 rounded-lg">
                                <p class="text-3xl font-bold text-success">{{ stats.answered_questions }}</p>
                                <p class="text-sm text-gray-600">Graded</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Group Info -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">About</h3>
                                <dl class="space-y-3">
                                    <div v-if="group.event">
                                        <dt class="text-sm font-medium text-gray-500">Event</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ group.event.name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Description</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ group.description || 'No description' }}</dd>
                                    </div>
                                    <div v-if="group.creator">
                                        <dt class="text-sm font-medium text-gray-500">Created by</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ group.creator.name }}</dd>
                                    </div>
                                    <div v-if="group.is_public !== undefined">
                                        <dt class="text-sm font-medium text-gray-500">Visibility</dt>
                                        <dd class="mt-1">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                :class="{
                                                    'bg-success/20 text-success': group.is_public,
                                                    'bg-gray-100 text-gray-800': !group.is_public,
                                                }"
                                            >
                                                {{ group.is_public ? 'Public' : 'Private' }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div v-if="isCaptain && group.grading_source">
                                        <dt class="text-sm font-medium text-gray-500">Grading Source</dt>
                                        <dd class="mt-1 text-sm text-gray-900 capitalize">{{ group.grading_source }}</dd>
                                    </div>
                                    <div v-if="isCaptain && group.entry_cutoff">
                                        <dt class="text-sm font-medium text-gray-500">Entry Cutoff</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ formatDateTime(group.entry_cutoff) }}
                                        </dd>
                                    </div>
                                    <div v-if="group.entries_count !== undefined">
                                        <dt class="text-sm font-medium text-gray-500">Total Entries</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ group.entries_count || 0 }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Group Code</h3>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm text-gray-600 mb-1">Share this code with others:</p>
                                            <p class="text-2xl font-mono font-bold text-gray-900">{{ group.code || group.join_code }}</p>
                                        </div>
                                        <button
                                            @click="copyGroupCode"
                                            class="px-3 py-2 bg-primary text-white text-sm rounded hover:bg-primary/80"
                                        >
                                            Copy
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Members -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Members ({{ membersList.length }})</h3>

                        <!-- Captain view: Table with roles -->
                        <div v-if="isCaptain && membersList.length > 0 && membersList[0].is_captain !== undefined" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Name
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Email
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Role
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Joined
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="member in membersList" :key="member.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="font-medium text-gray-900">{{ member.name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ member.email }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                v-if="member.is_captain"
                                                class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-warning/20 text-warning"
                                            >
                                                Captain
                                            </span>
                                            <span
                                                v-else
                                                class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800"
                                            >
                                                Guest
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ member.joined_at ? new Date(member.joined_at).toLocaleDateString() : 'N/A' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Member view: Cards -->
                        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div
                                v-for="user in membersList"
                                :key="user.id"
                                class="flex items-center gap-3 p-3 border rounded-lg"
                            >
                                <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                                    <span class="text-gray-700 font-semibold">
                                        {{ user.name.charAt(0).toUpperCase() }}
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-gray-900 truncate">
                                        {{ user.name }}
                                        <span v-if="user.id === group.created_by" class="text-xs text-gray-500">(Owner)</span>
                                    </div>
                                    <div class="text-sm text-gray-500 truncate">{{ user.email }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Entries (Member View) -->
                <div v-if="!isCaptain && recentEntries" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Entries</h3>

                        <div v-if="recentEntries.length === 0" class="text-center py-8 text-gray-500">
                            No entries yet from this group.
                        </div>

                        <div v-else class="space-y-3">
                            <div
                                v-for="entry in recentEntries"
                                :key="entry.id"
                                class="flex items-center justify-between p-4 border rounded-lg hover:shadow-md transition"
                            >
                                <div>
                                    <div class="font-medium text-gray-900">{{ entry.user.name }}</div>
                                    <div class="text-sm text-gray-600">{{ entry.event.name }}</div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ new Date(entry.submitted_at).toLocaleDateString() }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-semibold"
                                        :class="{
                                            'text-success': entry.percentage >= 80,
                                            'text-warning': entry.percentage >= 60 && entry.percentage < 80,
                                            'text-danger': entry.percentage < 60,
                                        }"
                                    >
                                        {{ entry.percentage.toFixed(1) }}%
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        {{ entry.total_score }} / {{ entry.possible_points }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Danger Zone (Captain Only) -->
                <div v-if="isCaptain" class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-2 border-danger/30">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-danger mb-4">Danger Zone</h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Deleting this group will remove all associated data. This action cannot be undone.
                        </p>
                        <button
                            @click="deleteGroup"
                            :disabled="deleteForm.processing"
                            class="bg-danger hover:bg-danger/80 text-white px-4 py-2 rounded font-semibold disabled:opacity-50"
                        >
                            {{ deleteForm.processing ? 'Deleting...' : 'Delete Group' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
