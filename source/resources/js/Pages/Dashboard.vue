<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    TrophyIcon,
    UserGroupIcon,
    ChartBarIcon,
} from '@heroicons/vue/24/outline';
import ToastNotification from '@/Components/ToastNotification.vue';

const props = defineProps({
    isAdmin: Boolean,
    isCaptain: Boolean,
    userGroups: Array,
    captainGroups: Array,
    activeEvents: Array,
    recentResults: Array,
    stats: Object,
});

const showCopiedToast = ref(false);
const showJoinGroupModal = ref(false);
const joinGroupForm = useForm({
    code: '',
});

const joinGroup = () => {
    joinGroupForm.post(route('groups.join'), {
        onSuccess: () => {
            showJoinGroupModal.value = false;
            joinGroupForm.reset();
        },
    });
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getOrdinal = (num) => {
    const j = num % 10;
    const k = num % 100;
    if (j === 1 && k !== 11) return num + 'st';
    if (j === 2 && k !== 12) return num + 'nd';
    if (j === 3 && k !== 13) return num + 'rd';
    return num + 'th';
};

const getStatusColor = (status) => {
    const colors = {
        'draft': 'bg-gray-100 text-gray-800',
        'open': 'bg-propoff-green/20 text-propoff-dark-green',
        'locked': 'bg-propoff-orange/20 text-propoff-orange',
        'completed': 'bg-propoff-dark-green/20 text-propoff-dark-green',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const copyJoinCode = (code) => {
    navigator.clipboard.writeText(code);
    showCopiedToast.value = true;
    setTimeout(() => {
        showCopiedToast.value = false;
    }, 3000);
};

const copyMagicLink = (link) => {
    navigator.clipboard.writeText(link);
    showCopiedToast.value = true;
    setTimeout(() => {
        showCopiedToast.value = false;
    }, 3000);
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Welcome Message -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-2xl font-bold text-gray-900">
                            Welcome back, {{ $page.props.auth.user.name }}!
                            <span v-if="isAdmin" class="ml-2 text-sm bg-propoff-red/20 text-propoff-red px-3 py-1 rounded-full">Admin</span>
                            <span v-else-if="isCaptain" class="ml-2 text-sm bg-propoff-orange/20 text-propoff-orange px-3 py-1 rounded-full">Captain</span>
                        </h3>
                        <p class="mt-2 text-gray-600">
                            <span v-if="isAdmin">Manage your system and play events</span>
                            <span v-else-if="isCaptain">Manage your groups and play events</span>
                            <span v-else>Here's what's happening with your events</span>
                        </p>
                    </div>
                </div>

                <!-- Magic Link Banner (for guest users) -->
                <div v-if="$page.props.flash?.show_magic_link && $page.props.auth.user.role === 'guest'" class="bg-gradient-to-r from-propoff-blue to-propoff-blue overflow-hidden shadow-lg sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-start">
                            <svg class="h-6 w-6 text-white mt-1 mr-3 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                            </svg>
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-white mb-2">🔑 Save Your Personal Access Link!</h3>
                                <p class="text-blue-100 text-sm mb-3">
                                    Since you don't have a password, use this magic link to return to your account anytime. Bookmark it or save it somewhere safe!
                                </p>
                                <div class="bg-white bg-opacity-20 rounded-lg p-3 mb-3">
                                    <p class="text-white font-mono text-xs break-all">{{ $page.props.flash?.magic_link }}</p>
                                </div>
                                <button
                                    @click="copyMagicLink($page.props.flash?.magic_link)"
                                    class="inline-flex items-center px-4 py-2 bg-white text-propoff-blue rounded-md hover:bg-propoff-blue/10 transition font-medium text-sm"
                                >
                                    <svg class="h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184" />
                                    </svg>
                                    Copy Link
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <!-- <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>

                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">

                            <button
                                @click="showJoinGroupModal = true"
                                class="flex flex-col items-center justify-center p-6 border-2 border-propoff-orange/20 bg-propoff-orange/10 rounded-lg hover:border-propoff-orange hover:bg-propoff-orange/20 transition"
                            >
                                <UserGroupIcon class="h-12 w-12 text-propoff-orange mb-3" />
                                <span class="font-semibold text-gray-900">Join New Group</span>
                                <span class="text-xs text-gray-600 text-center mt-1">Enter a join code</span>
                            </button>

                            <Link
                                :href="route('entries.index')"
                                class="flex flex-col items-center justify-center p-6 border-2 border-gray-200 rounded-lg hover:border-propoff-green hover:bg-propoff-green/10 transition"
                            >
                                <ChartBarIcon class="h-12 w-12 text-propoff-green mb-3" />
                                <span class="font-semibold text-gray-900">My Entries</span>
                                <span class="text-xs text-gray-600 text-center mt-1">View answers</span>
                            </Link>

                        </div>
                    </div>
                </div> -->

                <!-- Statistics Cards -->
                <!-- <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="rounded-md bg-propoff-blue p-3">
                                    <TrophyIcon class="h-6 w-6 text-white" />
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Events Played</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ stats.total_events }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="rounded-md bg-propoff-green p-3">
                                    <ChartBarIcon class="h-6 w-6 text-white" />
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Entries</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ stats.total_entries }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="rounded-md bg-propoff-orange p-3">
                                    <UserGroupIcon class="h-6 w-6 text-white" />
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">My Groups</p>
                                    <p class="text-2xl font-semibold text-gray-900">
                                        {{ stats.groups_count }}
                                        <span v-if="isCaptain" class="text-sm text-propoff-orange">({{ stats.captain_groups_count }} captain)</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="rounded-md bg-propoff-dark-green p-3">
                                    <ClockIcon class="h-6 w-6 text-white" />
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Avg Score</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ stats.average_score?.toFixed(1) || 0 }}%</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> -->


                <!-- My Groups Section -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">
                                My Groups
                            </h3>
                            <button
                                @click="showJoinGroupModal = true"
                                class="text-sm text-propoff-blue hover:text-propoff-blue/80 font-semibold"
                            >
                                + Join New Group
                            </button>
                        </div>

                        <div v-if="userGroups.length === 0" class="text-center py-12">
                            <UserGroupIcon class="mx-auto h-12 w-12 text-gray-400" />
                            <p class="mt-2 text-sm text-gray-500">You haven't joined any groups yet</p>
                            <button
                                @click="showJoinGroupModal = true"
                                class="mt-4 text-sm text-propoff-blue hover:text-propoff-blue/80 font-semibold"
                            >
                                Join a group to get started
                            </button>
                        </div>

                        <div v-else class="space-y-4">
                            <div
                                v-for="group in userGroups"
                                :key="group.id"
                                class="border border-gray-200 p-4 rounded-lg hover:shadow-md transition"
                            >
                                <!-- Group Header -->
                                <div class="flex justify-between items-start mb-3">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-lg flex items-center gap-2">
                                            {{ group.name }}
                                            <span
                                                v-if="group.is_captain"
                                                class="text-xs bg-propoff-blue text-white px-2 py-1 rounded-full font-bold"
                                            >
                                                CAPTAIN
                                            </span>
                                        </h4>
                                        <p class="text-sm text-gray-600">{{ group.event.name }}</p>
                                        <div class="flex items-center gap-4 mt-1">
                                            <span :class="getStatusColor(group.event.status)" class="text-xs px-2 py-1 rounded">
                                                {{ group.event.status }}
                                            </span>
                                            <span class="text-xs text-gray-500">
                                                {{ group.members_count }} members
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-gray-500">Join Code</p>
                                        <button
                                            @click="copyJoinCode(group.code)"
                                            class="font-mono text-sm font-semibold text-gray-900 hover:text-propoff-blue"
                                        >
                                            {{ group.code }}
                                        </button>
                                    </div>
                                </div>

                                <!-- Group Actions -->
                                <div class="mt-3 pt-3 border-t border-gray-200 flex gap-3 flex-wrap">
                                    <!-- Smart button based on entry status -->
                                    <button
                                        v-if="group.user_entry && group.user_entry.status === 'in_progress'"
                                        @click="$inertia.visit(route('entries.continue', group.user_entry.id))"
                                        class="inline-flex items-center px-4 py-2 bg-propoff-green text-white rounded hover:bg-propoff-dark-green text-sm font-semibold"
                                    >
                                        Continue
                                    </button>
                                    <Link
                                        v-else-if="group.user_entry && group.user_entry.status === 'completed'"
                                        :href="route('entries.show', group.user_entry.id)"
                                        class="inline-flex items-center px-4 py-2 bg-propoff-blue text-white rounded hover:bg-propoff-blue/80 text-sm font-semibold"
                                    >
                                        View Entry
                                    </Link>
                                    <button
                                        v-else-if="group.event.status === 'open'"
                                        @click="$inertia.post(route('entries.start', group.event.id), { group_id: group.id })"
                                        class="inline-flex items-center px-4 py-2 bg-propoff-blue text-white rounded hover:bg-propoff-blue/80 text-sm font-semibold"
                                    >
                                        Enter Event
                                    </button>
                                    <Link
                                        v-if="group.has_leaderboard"
                                        :href="route('groups.leaderboard', group.id)"
                                        class="inline-flex items-center px-4 py-2 bg-propoff-dark-green text-white rounded hover:bg-propoff-dark-green/80 text-sm font-semibold"
                                    >
                                        View Leaderboard
                                    </Link>
                                    <Link
                                        v-if="group.is_captain"
                                        :href="route('groups.show', group.id)"
                                        class="inline-flex items-center px-4 py-2 bg-propoff-orange text-white rounded hover:bg-propoff-orange/80 text-sm font-semibold"
                                    >
                                        Manage Group
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Events Section -->
                <!-- <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Active Events</h3>
                            <Link
                                :href="route('dashboard')"
                                class="text-sm text-propoff-blue hover:text-propoff-blue/80"
                            >
                                View all
                            </Link>
                        </div>

                        <div v-if="activeEvents.length === 0" class="text-center py-12">
                            <TrophyIcon class="mx-auto h-12 w-12 text-gray-400" />
                            <p class="mt-2 text-sm text-gray-500">No active events at the moment</p>
                        </div>

                        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div
                                v-for="event in activeEvents"
                                :key="event.id"
                                class="border rounded-lg p-4 hover:shadow-md transition"
                            >
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-semibold text-gray-900">{{ event.name }}</h4>
                                    <span :class="getStatusColor(event.status)" class="text-xs px-2 py-1 rounded">
                                        {{ event.status }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 mb-3">
                                    {{ formatDate(event.event_date) }}
                                </p>

                                <div class="flex flex-col gap-2">
                                    <Link
                                        v-if="event.has_submitted && event.entry_id"
                                        :href="route('entries.show', event.entry_id)"
                                        class="text-center px-4 py-2 bg-propoff-green/20 text-propoff-dark-green rounded font-semibold text-sm"
                                    >
                                        ✓ View Entry
                                    </Link>
                                    <Link
                                        v-else-if="event.has_joined && event.group_id"
                                        :href="route('events.show', event.id)"
                                        class="text-center px-4 py-2 bg-propoff-blue text-white rounded font-semibold text-sm hover:bg-propoff-blue/80"
                                    >
                                        Play Now
                                    </Link>
                                    <Link
                                        v-else
                                        :href="route('events.show', event.id)"
                                        class="text-center px-4 py-2 bg-gray-200 text-gray-800 rounded font-semibold text-sm hover:bg-gray-300"
                                    >
                                        View Event
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> -->

                <!-- Recent Entries Section -->
                <div v-if="recentResults.length > 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Recent Entries</h3>
                            <Link
                                :href="route('entries.index')"
                                class="text-sm text-propoff-blue hover:text-propoff-blue/80"
                            >
                                View all
                            </Link>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Event</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Group</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rank</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Score</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Max</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pct</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Submitted</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="result in recentResults" :key="result.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <Link
                                                :href="route('entries.show', result.id)"
                                                class="text-sm font-medium text-propoff-blue hover:text-propoff-blue/80"
                                            >
                                                {{ result.event.name }}
                                            </Link>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ result.group.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ result.rank ? getOrdinal(result.rank) : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                            {{ result.total_score }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ result.possible_points }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                            {{ result.percentage }}%
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatDate(result.submitted_at) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Toast Notification -->
        <ToastNotification :show="showCopiedToast" message="Join code copied to clipboard!" />

        <!-- Join Group Modal -->
        <div
            v-if="showJoinGroupModal"
            class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
            @click.self="showJoinGroupModal = false"
        >
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Join a Group</h3>
                    <form @submit.prevent="joinGroup">
                        <div class="mb-4">
                            <label for="join-code" class="block text-sm font-medium text-gray-700 mb-2">
                                Enter Join Code
                            </label>
                            <input
                                id="join-code"
                                v-model="joinGroupForm.code"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-propoff-blue/50 focus:border-propoff-blue uppercase font-mono"
                                placeholder="ABCD1234"
                                required
                                autofocus
                            />
                            <p v-if="joinGroupForm.errors.code" class="mt-1 text-sm text-propoff-red">
                                {{ joinGroupForm.errors.code }}
                            </p>
                        </div>
                        <div class="flex gap-3 justify-end">
                            <button
                                type="button"
                                @click="showJoinGroupModal = false"
                                class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="joinGroupForm.processing"
                                class="px-4 py-2 bg-propoff-blue text-white rounded-md hover:bg-propoff-blue/80 disabled:opacity-50"
                            >
                                Join Group
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
