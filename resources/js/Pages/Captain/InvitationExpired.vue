<template>
    <Head title="Invitation Expired" />

    <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="flex justify-center">
                <div class="bg-propoff-red/10 rounded-full p-3">
                    <ExclamationTriangleIcon class="h-12 w-12 text-propoff-red" />
                </div>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Invitation Expired
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                This captain invitation is no longer valid
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
                <div class="space-y-6">
                    <!-- Event Info -->
                    <div class="border-l-4 border-propoff-blue pl-4">
                        <h3 class="text-lg font-semibold text-gray-900">{{ event.name }}</h3>
                        <p class="text-xs text-gray-500 mt-1">
                            Event Date: {{ formatDate(event.event_date) }}
                        </p>
                    </div>

                    <!-- Reason -->
                    <div class="bg-propoff-red/10 border border-propoff-red/30 rounded-lg p-4">
                        <h4 class="text-sm font-semibold text-propoff-red mb-2">Why can't I use this invitation?</h4>
                        <ul class="text-sm text-propoff-red space-y-1">
                            <li v-if="!invitation.is_active" class="flex items-start">
                                <span class="mr-2">•</span>
                                <span>The invitation has been deactivated by the admin</span>
                            </li>
                            <li v-if="invitation.expires_at && isPast(invitation.expires_at)" class="flex items-start">
                                <span class="mr-2">•</span>
                                <span>The invitation expired on {{ formatDate(invitation.expires_at) }}</span>
                            </li>
                            <li v-if="invitation.max_uses && invitation.times_used >= invitation.max_uses" class="flex items-start">
                                <span class="mr-2">•</span>
                                <span>The invitation has reached its maximum usage limit ({{ invitation.max_uses }})</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Actions -->
                    <div class="space-y-3">
                        <p class="text-sm text-gray-600 text-center">
                            Please contact the event administrator for a new invitation link.
                        </p>
                        <Link
                            :href="route('login')"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-propoff-blue hover:bg-propoff-blue/80 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-propoff-blue"
                        >
                            Go to Login
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    event: Object,
    invitation: Object,
});

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const isPast = (date) => {
    if (!date) return false;
    return new Date(date) < new Date();
};
</script>
