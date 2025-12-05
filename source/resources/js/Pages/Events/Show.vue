<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { PlayIcon } from '@heroicons/vue/24/outline';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import PageHeader from '@/Components/PageHeader.vue';

const props = defineProps({
    event: Object,
});
</script>

<template>
    <Head :title="event.name" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader
                :title="event.name"
                :crumbs="[
                    { label: 'Dashboard', href: route('dashboard') },
                    { label: 'Events', href: route('events.index') },
                    { label: event.name }
                ]"
            >
                <template #metadata>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                        :class="{
                            'bg-gray-100 text-gray-800': event.status === 'draft',
                            'bg-propoff-green/20 text-propoff-dark-green': event.status === 'open',
                            'bg-propoff-orange/20 text-propoff-orange': event.status === 'locked',
                            'bg-propoff-dark-green/20 text-propoff-dark-green': event.status === 'completed',
                        }"
                    >
                        {{ event.status }}
                    </span>
                </template>
                <template #actions>
                    <Link
                        v-if="event.status === 'open'"
                        :href="route('events.show', event.id)"
                    >
                        <PrimaryButton>
                            <PlayIcon class="w-5 h-5 mr-2" />
                            Play Event
                        </PrimaryButton>
                    </Link>
                </template>
            </PageHeader>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Event Details -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Event Information</h3>
                                <dl class="space-y-3">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Description</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ event.description || 'No description' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Category</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ event.category }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Event Date</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ new Date(event.event_date).toLocaleString() }}
                                        </dd>
                                    </div>
                                    <div v-if="event.lock_date">
                                        <dt class="text-sm font-medium text-gray-500">Lock Date</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ new Date(event.lock_date).toLocaleString() }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistics</h3>
                                <dl class="space-y-3">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                                        <dd class="mt-1">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                :class="{
                                                    'bg-gray-100 text-gray-800': event.status === 'draft',
                                                    'bg-propoff-green/20 text-propoff-dark-green': event.status === 'active',
                                                    'bg-propoff-orange/20 text-propoff-orange': event.status === 'locked',
                                                    'bg-propoff-dark-green/20 text-propoff-dark-green': event.status === 'completed',
                                                }"
                                            >
                                                {{ event.status }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Total Questions</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ event.event_questions?.length || 0 }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Total Entries</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ event.entries_count || 0 }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Created By</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ event.creator.name }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Questions Preview -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Questions Preview</h3>
                            <p class="text-sm text-gray-500 mt-1">This event contains {{ event.event_questions?.length || 0 }} {{ (event.event_questions?.length || 0) === 1 ? 'question' : 'questions' }}</p>
                        </div>

                        <div v-if="!event.event_questions || event.event_questions.length === 0" class="text-center py-8 text-gray-500">
                            No questions available yet.
                        </div>

                        <div v-else class="space-y-3">
                            <div
                                v-for="(question, index) in event.event_questions.slice(0, 3)"
                                :key="question.id"
                                class="border rounded-lg p-4 bg-gray-50"
                            >
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-gray-700">Q{{ index + 1 }}.</span>
                                    <span class="text-gray-900">{{ question.question_text }}</span>
                                </div>
                                <div class="mt-2 flex items-center gap-4 text-sm text-gray-500">
                                    <span class="capitalize">{{ question.question_type.replace('_', ' ') }}</span>
                                    <span>{{ question.points }} {{ question.points === 1 ? 'point' : 'points' }}</span>
                                </div>
                            </div>
                            <div v-if="event.event_questions.length > 3" class="text-center text-sm text-gray-500 pt-2">
                                ... and {{ event.event_questions.length - 3 }} more {{ event.event_questions.length - 3 === 1 ? 'question' : 'questions' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
