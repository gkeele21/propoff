<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { getThemeFromEvent } from '../../AmericaSays/themes.js';
import axios from 'axios';

const props = defineProps({
    eventId: Number,
    event: Object,
});

// Game state
const currentQuestion = ref(null);
const answers = ref([]);
const revealedAnswerIds = ref([]);
const timerStartedAt = ref(null);
const timerPausedAt = ref(null);
const timerDuration = ref(30);
const remainingTime = ref(30);
const eventDetails = ref(null);
const allQuestions = ref([]);

// Theme
const theme = computed(() => getThemeFromEvent(eventDetails.value));

// Timer display
const timerDisplay = computed(() => {
    const mins = Math.floor(remainingTime.value / 60);
    const secs = remainingTime.value % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
});

const timerWarning = computed(() => remainingTime.value <= 10);
const timerRunning = computed(() => timerStartedAt.value && !timerPausedAt.value);

// Count of revealed answers
const revealedCount = computed(() => revealedAnswerIds.value.length);
const totalAnswers = computed(() => answers.value.length);

// Polling interval
let pollInterval = null;
let timerInterval = null;

// Calculate remaining time (Unix epoch in seconds)
const calculateRemainingTime = () => {
    if (!timerStartedAt.value) {
        remainingTime.value = timerDuration.value;
        return;
    }

    if (timerPausedAt.value) {
        const elapsed = timerPausedAt.value - timerStartedAt.value;
        remainingTime.value = Math.max(0, timerDuration.value - elapsed);
        return;
    }

    const now = Math.floor(Date.now() / 1000);
    const elapsed = now - timerStartedAt.value;
    remainingTime.value = Math.max(0, timerDuration.value - elapsed);
};

// Fetch game state
const fetchGameState = async () => {
    try {
        const response = await axios.get(`/api/america-says/events/${props.eventId}/game-state`);
        const data = response.data;

        currentQuestion.value = data.current_question;
        answers.value = data.current_question?.event_answers || [];
        revealedAnswerIds.value = data.revealed_answer_ids || [];
        timerStartedAt.value = data.timer_started_at;
        timerPausedAt.value = data.timer_paused_at;
        timerDuration.value = data.timer_duration;

        calculateRemainingTime();
    } catch (error) {
        console.error('Error fetching game state:', error);
    }
};

// Fetch event details
const fetchEvent = async () => {
    try {
        const response = await axios.get(`/api/america-says/events/${props.eventId}`);
        eventDetails.value = response.data;
    } catch (error) {
        console.error('Error fetching event:', error);
    }
};

// Fetch all questions
const fetchAllQuestions = async () => {
    try {
        const response = await axios.get(`/api/america-says/events/${props.eventId}/questions`);
        allQuestions.value = response.data;
    } catch (error) {
        console.error('Error fetching questions:', error);
    }
};

// Timer controls
const startTimer = async () => {
    try {
        await axios.post(`/api/america-says/events/${props.eventId}/start-timer`);
        await fetchGameState();
    } catch (error) {
        console.error('Error starting timer:', error);
    }
};

const pauseTimer = async () => {
    try {
        await axios.post(`/api/america-says/events/${props.eventId}/pause-timer`);
        await fetchGameState();
    } catch (error) {
        console.error('Error pausing timer:', error);
    }
};

const resetTimer = async () => {
    try {
        await axios.post(`/api/america-says/events/${props.eventId}/reset-timer`);
        await fetchGameState();
    } catch (error) {
        console.error('Error resetting timer:', error);
    }
};

// Answer controls
const toggleAnswer = async (answerId, isCurrentlyRevealed) => {
    try {
        if (isCurrentlyRevealed) {
            await axios.post(`/api/america-says/events/${props.eventId}/unreveal-answer`, {
                answer_id: answerId,
            });
        } else {
            await axios.post(`/api/america-says/events/${props.eventId}/reveal-answer`, {
                answer_id: answerId,
            });
        }
        await fetchGameState();
    } catch (error) {
        console.error('Error toggling answer:', error);
    }
};

// Navigation controls
const goToNextQuestion = async () => {
    try {
        await axios.post(`/api/america-says/events/${props.eventId}/next-question`);
        await fetchGameState();
    } catch (error) {
        console.error('Error going to next question:', error);
    }
};

const goToPreviousQuestion = async () => {
    try {
        await axios.post(`/api/america-says/events/${props.eventId}/previous-question`);
        await fetchGameState();
    } catch (error) {
        console.error('Error going to previous question:', error);
    }
};

// Check if answer is revealed
const isAnswerRevealed = (answerId) => {
    return revealedAnswerIds.value.includes(answerId);
};

// Get current question index
const currentQuestionIndex = computed(() => {
    if (!currentQuestion.value) return -1;
    return allQuestions.value.findIndex(q => q.id === currentQuestion.value.id);
});

const questionProgress = computed(() => {
    if (allQuestions.value.length === 0) return '';
    return `${currentQuestionIndex.value + 1} / ${allQuestions.value.length}`;
});

// Start polling and timer
onMounted(() => {
    fetchEvent();
    fetchAllQuestions();
    fetchGameState();

    // Poll less frequently on admin (2 seconds) since it's just for control
    pollInterval = setInterval(fetchGameState, 2000);
    timerInterval = setInterval(calculateRemainingTime, 100);
});

// Cleanup
onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
    if (timerInterval) clearInterval(timerInterval);
});
</script>

<template>
    <Head title="America Says - Host Game" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader
                title="Host Game"
                :subtitle="event?.name"
                :crumbs="[
                    { label: 'Admin Dashboard', href: route('admin.dashboard') },
                    { label: 'Events', href: route('admin.events.index') },
                    { label: event?.name || 'Event', href: route('admin.events.show', event?.id) },
                    { label: 'Host Game' }
                ]"
            />
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Question Progress and Timer Controls Combined -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4">
                        <div class="grid grid-cols-2 gap-6">
                            <!-- Left: Question Progress with Navigation -->
                            <div class="flex items-center gap-3 pr-6 border-r border-gray-300">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold">Question {{ questionProgress }}</h3>
                                    <p class="text-sm text-gray-600 mt-1">{{ currentQuestion?.question_text }}</p>
                                </div>

                                <!-- Navigation Buttons -->
                                <div class="flex gap-2">
                                    <button
                                        @click="goToPreviousQuestion"
                                        :disabled="currentQuestionIndex <= 0"
                                        class="px-3 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed text-sm"
                                    >
                                        ← Previous
                                    </button>
                                    <button
                                        @click="goToNextQuestion"
                                        :disabled="currentQuestionIndex >= allQuestions.length - 1"
                                        class="px-3 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed text-sm"
                                    >
                                        Next →
                                    </button>
                                </div>
                            </div>

                            <!-- Right: Timer -->
                            <div class="flex items-center gap-3 pl-6">
                                <div
                                    class="text-3xl font-bold tabular-nums px-4 py-2 rounded-lg"
                                    :class="{
                                        'bg-red-100 text-red-600': timerWarning,
                                        'bg-green-100 text-green-600': !timerWarning && timerRunning,
                                        'bg-gray-100 text-gray-600': !timerRunning,
                                    }"
                                >
                                    {{ timerDisplay }}
                                </div>

                                <!-- Timer Buttons -->
                                <div class="flex gap-2">
                                    <button
                                        v-if="!timerRunning"
                                        @click="startTimer"
                                        class="px-3 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm"
                                    >
                                        Start
                                    </button>
                                    <button
                                        v-else
                                        @click="pauseTimer"
                                        class="px-3 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 text-sm"
                                    >
                                        Pause
                                    </button>
                                    <button
                                        @click="resetTimer"
                                        class="px-3 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 text-sm"
                                    >
                                        Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Answers Checklist -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">Answers</h3>
                            <span class="text-sm font-semibold"
                                  :style="{ color: theme.colors.primary }">
                                {{ revealedCount }} / {{ totalAnswers }} Found
                            </span>
                        </div>

                        <div v-if="answers.length === 0" class="text-center py-8 text-gray-500">
                            No answers available
                        </div>

                        <div v-else class="grid grid-cols-2 gap-3">
                            <div
                                v-for="answer in answers"
                                :key="answer.id"
                                class="flex items-center p-3 border rounded-lg transition-all cursor-pointer hover:shadow-md"
                                :class="{
                                    'border-green-500 bg-green-50': isAnswerRevealed(answer.id),
                                    'border-gray-200': !isAnswerRevealed(answer.id),
                                }"
                                @click="toggleAnswer(answer.id, isAnswerRevealed(answer.id))"
                            >
                                <input
                                    type="checkbox"
                                    :checked="isAnswerRevealed(answer.id)"
                                    @change.stop="toggleAnswer(answer.id, isAnswerRevealed(answer.id))"
                                    class="h-5 w-5 rounded mr-3 cursor-pointer"
                                    :style="{
                                        accentColor: theme.colors.primary
                                    }"
                                />
                                <div class="flex-1">
                                    <span class="font-semibold">{{ answer.correct_answer }}</span>
                                    <span class="ml-2 text-xs text-gray-500">(#{{ answer.display_order }})</span>
                                </div>
                                <div v-if="isAnswerRevealed(answer.id)" class="text-green-600 font-bold text-sm">
                                    ✓
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
