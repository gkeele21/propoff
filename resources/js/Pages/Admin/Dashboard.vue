<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import { Head } from '@inertiajs/vue3';
import { CogIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    adminStats: Object,
});

// Build secondary stat strings
const eventsSubtitle = () => {
    const parts = [];
    if (props.adminStats.open_events > 0) {
        parts.push(`${props.adminStats.open_events} open`);
    }
    if (props.adminStats.draft_events > 0) {
        parts.push(`${props.adminStats.draft_events} draft`);
    }
    return parts.length > 0 ? parts.join(' · ') : 'No active events';
};

const usersSubtitle = () => {
    return `${props.adminStats.admin_users} admin${props.adminStats.admin_users !== 1 ? 's' : ''}`;
};

const groupsSubtitle = () => {
    return `${props.adminStats.total_group_members} total members`;
};
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <CogIcon class="h-6 w-6 text-muted" />
                <h2 class="font-semibold text-xl text-body leading-tight">
                    Admin Dashboard
                </h2>
                <span class="ml-2 text-sm bg-danger text-white px-3 py-1 rounded-full font-bold">
                    Admin
                </span>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <!-- Unified Stat Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <StatCard
                        title="Events"
                        :count="adminStats.total_events"
                        :subtitle="eventsSubtitle()"
                        :href="route('admin.events.index')"
                        border-color="primary"
                    />

                    <StatCard
                        title="Question Templates"
                        :count="adminStats.total_templates"
                        subtitle="Reusable templates"
                        :href="route('admin.question-templates.index')"
                        border-color="warning"
                    />

                    <StatCard
                        title="Users"
                        :count="adminStats.total_users"
                        :subtitle="usersSubtitle()"
                        :href="route('admin.users.index')"
                        border-color="gray-dark"
                    />

                    <StatCard
                        title="Groups"
                        :count="adminStats.total_groups"
                        :subtitle="groupsSubtitle()"
                        :href="route('admin.groups.index')"
                        border-color="success"
                    />
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
