<template>
    <Head :title="`User: ${user.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader
                :title="user.name"
                :crumbs="[
                    { label: 'Users', href: route('admin.users.index') },
                    { label: user.name }
                ]"
            >
                <template #metadata>
                    <span :class="[
                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                        user.role === 'admin' ? 'bg-danger/10 text-danger' : 'bg-gray-100 text-gray-700'
                    ]">
                        {{ user.role }}
                    </span>
                    <span class="text-gray-400 mx-2">•</span>
                    <span>{{ user.email }}</span>
                </template>
            </PageHeader>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- User Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">User Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium text-gray-500">Name</label>
                                <p class="text-gray-900">{{ user.name }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Email</label>
                                <p class="text-gray-900">{{ user.email }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Role</label>
                                <span :class="[
                                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                    user.role === 'admin' ? 'bg-danger/10 text-danger' : 'bg-gray-100 text-gray-700'
                                ]">
                                    {{ user.role }}
                                </span>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Member Since</label>
                                <p class="text-gray-900">{{ new Date(user.created_at).toLocaleDateString() }}</p>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <button @click="showRoleModal = true" class="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary/80 mr-2">
                                Change Role
                            </button>
                            <button @click="confirmDelete" class="px-4 py-2 bg-danger text-white rounded-md hover:bg-danger/80">
                                Delete User
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <TrophyIcon class="w-8 h-8 text-warning mr-3" />
                                <div>
                                    <p class="text-sm text-gray-500">Total Entries</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ stats?.total_entries || 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <UserGroupIcon class="w-8 h-8 text-warning mr-3" />
                                <div>
                                    <p class="text-sm text-gray-500">Groups</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ stats?.groups_joined || 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <ChartBarIcon class="w-8 h-8 text-success mr-3" />
                                <div>
                                    <p class="text-sm text-gray-500">Average Score</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ Number(stats?.average_score || 0).toFixed(1) }}%</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <StarIcon class="w-8 h-8 text-success mr-3" />
                                <div>
                                    <p class="text-sm text-gray-500">Best Score</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ Number(stats?.best_score || 0).toFixed(1) }}%</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Entries -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Recent Entries</h3>
                        <div v-if="!recentEntries || recentEntries.length === 0" class="text-center py-6 text-gray-500">
                            No entries yet
                        </div>
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Group</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="entry in recentEntries" :key="entry.id">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ entry.event?.name || 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ entry.group?.name || 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="font-semibold">{{ entry.total_score }}</span> / {{ entry.possible_points }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="[
                                                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                                entry.is_complete ? 'bg-success/10 text-success' : 'bg-warning/10 text-warning'
                                            ]">
                                                {{ entry.is_complete ? 'Complete' : 'In Progress' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ new Date(entry.submitted_at || entry.updated_at).toLocaleDateString() }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Leaderboard Positions -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Top Leaderboard Positions</h3>
                        <div v-if="!leaderboardPositions || leaderboardPositions.length === 0" class="text-center py-6 text-gray-500">
                            No leaderboard entries yet
                        </div>
                        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-for="position in leaderboardPositions" :key="position.id" class="border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-semibold text-gray-900">{{ position.event?.name || 'Unknown Event' }}</h4>
                                    <span class="text-lg font-bold text-success">#{{ position.rank }}</span>
                                </div>
                                <p class="text-sm text-gray-500">Group: {{ position.group?.name || 'N/A' }}</p>
                                <p class="text-sm text-gray-600 mt-2">
                                    Score: <span class="font-semibold">{{ position.percentage?.toFixed(1) || 0 }}%</span>
                                    ({{ position.total_score }}/{{ position.possible_points }})
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Role Change Modal -->
        <div v-if="showRoleModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-semibold mb-4">Change User Role</h3>
                <form @submit.prevent="updateRole">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Role</label>
                        <select v-model="roleForm.role" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary/50">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="flex justify-end space-x-2">
                        <button type="button" @click="showRoleModal = false" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary/80">
                            Update Role
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
import { TrophyIcon, UserGroupIcon, ChartBarIcon, StarIcon } from '@heroicons/vue/24/outline';
import PageHeader from '@/Components/PageHeader.vue';

const props = defineProps({
    user: Object,
    stats: Object,
    recentEntries: Array,
    leaderboardPositions: Array,
});

const showRoleModal = ref(false);
const roleForm = useForm({
    role: props.user.role,
});

const updateRole = () => {
    roleForm.post(route('admin.users.updateRole', props.user.id), {
        onSuccess: () => {
            showRoleModal.value = false;
        },
    });
};

const confirmDelete = () => {
    if (confirm(`Are you sure you want to delete ${props.user.name}? This action cannot be undone.`)) {
        router.delete(route('admin.users.destroy', props.user.id));
    }
};
</script>
