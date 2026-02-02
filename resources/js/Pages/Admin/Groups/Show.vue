<template>
    <Head title="Group Details" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader
                :title="group.name"
                :crumbs="[
                    { label: 'Admin Dashboard', href: route('admin.dashboard') },
                    { label: 'Groups', href: route('admin.groups.index') },
                    { label: group.name }
                ]"
            >
                <template #metadata>
                    <span>{{ stats.members_count }} members</span>
                    <span class="text-gray-400 mx-2">•</span>
                    <span>Code: <span class="font-mono font-bold">{{ group.code }}</span></span>
                </template>
                <template #actions>
                    <Link :href="route('admin.groups.edit', group.id)">
                        <Button variant="primary">
                            <PencilIcon class="w-4 h-4 mr-2" />
                            Edit Group
                        </Button>
                    </Link>
                </template>
            </PageHeader>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Group Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="mb-6">
                            <h3 class="text-2xl font-bold text-gray-900">{{ group.name }}</h3>
                            <p class="text-gray-600 mt-1">Join Code: <span class="font-mono font-bold">{{ group.code }}</span></p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="text-sm text-gray-600">Members</div>
                                <div class="text-2xl font-bold text-gray-900">{{ stats.members_count }}</div>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="text-sm text-gray-600">Total Entries</div>
                                <div class="text-2xl font-bold text-gray-900">{{ stats.total_entries }}</div>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="text-sm text-gray-600">Events Played</div>
                                <div class="text-2xl font-bold text-gray-900">{{ stats.events_count }}</div>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="text-sm text-gray-600">Avg Score</div>
                                <div class="text-2xl font-bold text-gray-900">{{ stats.average_score }}%</div>
                            </div>
                        </div>

                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Created By</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ group.created_by?.name || 'Unknown' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Created</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ formatDate(group.created_at) }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- Members -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Members</h3>
                            <button
                                @click="showAddMember = true"
                                class="px-4 py-2 bg-primary text-white text-sm rounded-md hover:bg-primary/80"
                            >
                                Add Member
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="member in members" :key="member.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ member.name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">{{ member.email }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="[
                                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                                member.role === 'admin' ? 'bg-danger/10 text-danger' : 'bg-gray-100 text-gray-700'
                                            ]">
                                                {{ member.role }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatDate(member.pivot.created_at) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button
                                                @click="removeMember(member.id)"
                                                class="text-danger hover:text-danger/80"
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

                <!-- Recent Activity -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Entries</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Event</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Score</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="entry in recent_entries" :key="entry.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ entry.user.name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ entry.event.title }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ entry.percentage }}%</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="[
                                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                                entry.status === 'completed' ? 'bg-success/10 text-success' : 'bg-warning/10 text-warning'
                                            ]">
                                                {{ entry.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatDate(entry.submitted_at || entry.created_at) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Member Modal -->
        <div v-if="showAddMember" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 max-w-md w-full">
                <h3 class="text-lg font-semibold mb-4">Add Member to Group</h3>
                <form @submit.prevent="addMember">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">User Email</label>
                        <input
                            v-model="addMemberForm.email"
                            type="email"
                            required
                            class="w-full border-gray-300 focus:border-primary focus:ring-primary/50 rounded-md shadow-sm"
                        />
                    </div>
                    <div class="flex justify-end space-x-2">
                        <button
                            type="button"
                            @click="showAddMember = false"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary/80"
                        >
                            Add Member
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { PencilIcon } from '@heroicons/vue/24/outline';
import Button from '@/Components/Base/Button.vue';
import PageHeader from '@/Components/PageHeader.vue';

const props = defineProps({
    group: Object,
    stats: Object,
    members: Array,
    recent_entries: Array,
});

const showAddMember = ref(false);

const addMemberForm = useForm({
    email: '',
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const addMember = () => {
    addMemberForm.post(route('admin.groups.addUser', props.group.id), {
        onSuccess: () => {
            showAddMember.value = false;
            addMemberForm.reset();
        },
    });
};

const removeMember = (userId) => {
    if (confirm('Remove this member from the group?')) {
        router.delete(route('admin.groups.remove-user', { group: props.group.id, user: userId }));
    }
};
</script>
