<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import PageHeader from '@/Components/PageHeader.vue';

const props = defineProps({
    group: {
        type: Object,
        required: true,
    },
    members: {
        type: Array,
        required: true,
    },
});

const showAddGuestModal = ref(false);
const guestName = ref('');

const promoteToCaptain = (userId) => {
    if (confirm('Are you sure you want to promote this member to captain?')) {
        router.post(route('groups.members.promote', [props.group.id, userId]), {}, {
            preserveScroll: true,
        });
    }
};

const demoteFromCaptain = (userId) => {
    if (confirm('Are you sure you want to demote this captain to a regular member?')) {
        router.post(route('groups.members.demote', [props.group.id, userId]), {}, {
            preserveScroll: true,
        });
    }
};

const removeMember = (userId) => {
    if (confirm('Are you sure you want to remove this member from the group?')) {
        router.delete(route('groups.members.remove', [props.group.id, userId]), {
            preserveScroll: true,
        });
    }
};

const regenerateJoinCode = () => {
    if (confirm('Are you sure you want to regenerate the join code? The old code will stop working.')) {
        router.post(route('groups.members.regenerateJoinCode', props.group.id), {}, {
            preserveScroll: true,
        });
    }
};

const copyJoinCode = () => {
    navigator.clipboard.writeText(props.group.join_code);
    alert('Join code copied to clipboard!');
};

const addGuest = () => {
    if (!guestName.value.trim()) {
        alert('Please enter a guest name.');
        return;
    }

    router.post(route('groups.members.addGuest', props.group.id), {
        name: guestName.value.trim(),
    }, {
        preserveScroll: true,
        onSuccess: () => {
            guestName.value = '';
            showAddGuestModal.value = false;
        },
    });
};

const submitEntryFor = (userId) => {
    router.get(route('captain.entries.edit', [props.group.id, userId]));
};
</script>

<template>
    <Head title="Manage Members" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader
                title="Manage Members"
                subtitle="Manage members and permissions for your group"
                :crumbs="[
                    { label: 'Dashboard', href: route('dashboard') },
                    { label: group.name, href: route('groups.show', group.id) },
                    { label: 'Members' }
                ]"
            >
                <template #metadata>
                    <span class="font-medium text-gray-900">{{ group.name }}</span>
                    <span class="text-gray-400 mx-2">•</span>
                    <span>{{ members.length }} members</span>
                </template>
            </PageHeader>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Join Code Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Join Code</h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Share this code with members to join your group.
                        </p>
                        <div class="flex items-center gap-4">
                            <div class="flex-1 bg-gray-100 p-4 rounded-lg">
                                <p class="text-2xl font-mono font-bold text-center">
                                    {{ group.join_code }}
                                </p>
                            </div>
                            <button
                                @click="copyJoinCode"
                                class="bg-propoff-blue hover:bg-propoff-blue/80 text-white px-4 py-2 rounded font-semibold"
                            >
                                Copy Code
                            </button>
                            <button
                                @click="regenerateJoinCode"
                                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded font-semibold"
                            >
                                Regenerate
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Members List -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">Members ({{ members.length }})</h3>
                            <button
                                @click="showAddGuestModal = true"
                                class="bg-propoff-green hover:bg-propoff-green/80 text-white px-4 py-2 rounded font-semibold"
                            >
                                + Add Guest User
                            </button>
                        </div>

                        <div class="overflow-x-auto">
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
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Entries
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="member in members" :key="member.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="font-medium text-gray-900">
                                                {{ member.name }}
                                                <span
                                                    v-if="member.is_guest"
                                                    class="ml-2 px-2 py-0.5 text-xs leading-4 font-semibold rounded bg-blue-100 text-blue-700"
                                                >
                                                    Guest
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ member.email || 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                v-if="member.is_captain"
                                                class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-propoff-orange/10 text-propoff-orange"
                                            >
                                                Captain
                                            </span>
                                            <span
                                                v-else
                                                class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-700"
                                            >
                                                Member
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ new Date(member.joined_at).toLocaleDateString() }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ member.entries_count }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                            <button
                                                @click="submitEntryFor(member.id)"
                                                class="text-propoff-green hover:text-propoff-green/80 font-semibold"
                                            >
                                                Submit Entry
                                            </button>
                                            <button
                                                v-if="!member.is_captain"
                                                @click="promoteToCaptain(member.id)"
                                                class="text-propoff-blue hover:text-propoff-blue/80 font-semibold"
                                            >
                                                Promote
                                            </button>
                                            <button
                                                v-if="member.is_captain"
                                                @click="demoteFromCaptain(member.id)"
                                                class="text-propoff-orange hover:text-propoff-orange/80 font-semibold"
                                            >
                                                Demote
                                            </button>
                                            <button
                                                @click="removeMember(member.id)"
                                                class="text-propoff-red hover:text-propoff-red/80 font-semibold"
                                            >
                                                Remove
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Info Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-propoff-orange/10 border border-propoff-orange/30 rounded-lg p-4">
                        <h4 class="font-semibold text-propoff-orange mb-2">About Captains</h4>
                        <p class="text-sm text-propoff-orange">
                            Captains have full control over the group, including managing questions, grading, and member management.
                            You can promote multiple members to captain.
                        </p>
                    </div>
                    <div class="bg-propoff-orange/10 border border-propoff-orange/30 rounded-lg p-4">
                        <h4 class="font-semibold text-propoff-orange mb-2">⚠️ Note</h4>
                        <p class="text-sm text-propoff-orange">
                            You cannot remove members who have already submitted answers. You also cannot demote the last captain.
                        </p>
                    </div>
                </div>

                <!-- Add Guest Modal -->
                <div
                    v-if="showAddGuestModal"
                    class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
                    @click.self="showAddGuestModal = false"
                >
                    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                        <div class="mt-3">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Add Guest User</h3>
                            <div class="mb-4">
                                <label for="guest-name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Guest Name
                                </label>
                                <input
                                    id="guest-name"
                                    v-model="guestName"
                                    type="text"
                                    placeholder="Enter guest name"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-propoff-blue"
                                    @keyup.enter="addGuest"
                                />
                            </div>
                            <div class="flex justify-end gap-3">
                                <button
                                    @click="showAddGuestModal = false"
                                    class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded font-semibold"
                                >
                                    Cancel
                                </button>
                                <button
                                    @click="addGuest"
                                    class="px-4 py-2 bg-propoff-green hover:bg-propoff-green/80 text-white rounded font-semibold"
                                >
                                    Add Guest
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
