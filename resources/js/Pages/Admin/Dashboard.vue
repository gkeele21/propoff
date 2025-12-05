<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    CalendarDaysIcon,
    UserGroupIcon,
    DocumentTextIcon,
    UsersIcon,
    CogIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    adminStats: Object,
});
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <CogIcon class="h-6 w-6 text-gray-700" />
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Admin Dashboard
                </h2>
                <span class="ml-2 text-sm bg-propoff-red text-white px-3 py-1 rounded-full font-bold">
                    Admin
                </span>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Welcome Message -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-2xl font-bold text-gray-900">
                            System Administration
                        </h3>
                        <p class="mt-2 text-gray-600">
                            Manage your system, events, users, and more
                        </p>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <Link
                                :href="route('admin.events.index')"
                                class="flex flex-col items-center justify-center p-6 border-2 border-gray-200 rounded-lg hover:border-propoff-blue hover:bg-propoff-blue/10 transition"
                            >
                                <CalendarDaysIcon class="h-12 w-12 text-propoff-blue mb-3" />
                                <span class="font-semibold text-gray-900">Events</span>
                                <span class="text-xs text-gray-600 mt-1">{{ adminStats.total_events }} events</span>
                            </Link>

                            <Link
                                :href="route('admin.question-templates.index')"
                                class="flex flex-col items-center justify-center p-6 border-2 border-gray-200 rounded-lg hover:border-propoff-blue hover:bg-propoff-blue/10 transition"
                            >
                                <DocumentTextIcon class="h-12 w-12 text-propoff-dark-green mb-3" />
                                <span class="font-semibold text-gray-900">Templates</span>
                                <span class="text-xs text-gray-600 mt-1">{{ adminStats.total_templates }} templates</span>
                            </Link>

                            <Link
                                :href="route('admin.users.index')"
                                class="flex flex-col items-center justify-center p-6 border-2 border-gray-200 rounded-lg hover:border-gray-400 hover:bg-gray-100 transition"
                            >
                                <UsersIcon class="h-12 w-12 text-gray-600 mb-3" />
                                <span class="font-semibold text-gray-900">Users</span>
                                <span class="text-xs text-gray-600 mt-1">{{ adminStats.total_users }} users</span>
                            </Link>

                            <Link
                                :href="route('admin.groups.index')"
                                class="flex flex-col items-center justify-center p-6 border-2 border-gray-200 rounded-lg hover:border-propoff-orange hover:bg-propoff-orange/10 transition"
                            >
                                <UserGroupIcon class="h-12 w-12 text-propoff-orange mb-3" />
                                <span class="font-semibold text-gray-900">Groups</span>
                                <span class="text-xs text-gray-600 mt-1">{{ adminStats.total_groups }} groups</span>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Admin Stats Summary -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">System Overview</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
                            <div class="text-center p-4 bg-propoff-blue/10 rounded-lg border-2 border-propoff-blue/30">
                                <p class="text-3xl font-bold text-propoff-blue">{{ adminStats.total_events }}</p>
                                <p class="text-sm text-gray-600 mt-1">Events</p>
                            </div>
                            <div class="text-center p-4 bg-propoff-dark-green/10 rounded-lg border-2 border-propoff-dark-green/30">
                                <p class="text-3xl font-bold text-propoff-dark-green">{{ adminStats.total_templates }}</p>
                                <p class="text-sm text-gray-600 mt-1">Templates</p>
                            </div>
                            <div class="text-center p-4 bg-propoff-blue/10 rounded-lg border-2 border-propoff-blue/30">
                                <p class="text-3xl font-bold text-propoff-blue">{{ adminStats.total_questions }}</p>
                                <p class="text-sm text-gray-600 mt-1">Questions</p>
                            </div>
                            <div class="text-center p-4 bg-gray-100 rounded-lg border-2 border-gray-300">
                                <p class="text-3xl font-bold text-gray-700">{{ adminStats.total_users }}</p>
                                <p class="text-sm text-gray-600 mt-1">Users</p>
                            </div>
                            <div class="text-center p-4 bg-propoff-orange/10 rounded-lg border-2 border-propoff-orange/30">
                                <p class="text-3xl font-bold text-propoff-orange">{{ adminStats.total_groups }}</p>
                                <p class="text-sm text-gray-600 mt-1">Groups</p>
                            </div>
                            <div class="text-center p-4 bg-propoff-green/10 rounded-lg border-2 border-propoff-green/30">
                                <p class="text-3xl font-bold text-propoff-green">{{ adminStats.total_entries }}</p>
                                <p class="text-sm text-gray-600 mt-1">Entries</p>
                            </div>
                            <div class="text-center p-4 bg-propoff-dark-green/10 rounded-lg border-2 border-propoff-dark-green/30">
                                <p class="text-3xl font-bold text-propoff-dark-green">{{ adminStats.completed_entries }}</p>
                                <p class="text-sm text-gray-600 mt-1">Completed</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
