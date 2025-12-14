<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import { getThemeFromEvent } from './themes.js';
import GameSound from '@/Components/GameSound.vue';
import axios from 'axios';

const props = defineProps({
    eventId: Number,
});

// Game state
const currentQuestion = ref(null);
const answers = ref([]);
const revealedAnswerIds = ref([]);
const timerStartedAt = ref(null);
const timerPausedAt = ref(null);
const timerDuration = ref(30);
const remainingTime = ref(30);
const event = ref(null);

// Theme
const theme = computed(() => getThemeFromEvent(event.value));

// Timer display
const timerDisplay = computed(() => {
    const mins = Math.floor(remainingTime.value / 60);
    const secs = remainingTime.value % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
});

const timerWarning = computed(() => remainingTime.value <= 10);
const showAnswers = computed(() => timerStartedAt.value !== null && !timerPausedAt.value);
const showTimer = computed(() => timerStartedAt.value !== null);

// Polling interval
let pollInterval = null;
let timerInterval = null;

// Sound trigger for success
const successSoundTrigger = ref(0);

// Calculate remaining time based on server timestamp (Unix epoch in seconds)
const calculateRemainingTime = () => {
    if (!timerStartedAt.value) {
        remainingTime.value = timerDuration.value;
        return;
    }

    if (timerPausedAt.value) {
        // Timer is paused - calculate time at pause moment
        const elapsed = timerPausedAt.value - timerStartedAt.value;
        remainingTime.value = Math.max(0, timerDuration.value - elapsed);
        return;
    }

    // Timer is running - timestamps are Unix epoch in seconds
    const now = Math.floor(Date.now() / 1000);
    const elapsed = now - timerStartedAt.value;
    remainingTime.value = Math.max(0, timerDuration.value - elapsed);
};

// Fetch game state from server
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
        event.value = response.data;
    } catch (error) {
        console.error('Error fetching event:', error);
    }
};

// Check if answer is revealed
const isAnswerRevealed = (answerId) => {
    return revealedAnswerIds.value.includes(answerId);
};

// Get answer display (first letters + underscores, no gaps to hide word length)
const getAnswerDisplay = (answer) => {
    const words = answer.correct_answer.split(' ');

    // For each word, handle hyphenated parts
    const displayWords = words.map(word => {
        // Split on hyphens but keep the hyphens
        const parts = word.split('-');

        const displayParts = parts.map(part => {
            const firstLetter = part.charAt(0).toUpperCase();
            // Use 1.5x underscores per letter (part length + half, rounded)
            const underscoreCount = Math.floor((part.length - 1) * 1.5);
            const underscores = '_'.repeat(underscoreCount);
            return firstLetter + underscores;
        });

        // Join hyphenated parts with hyphens
        return displayParts.join('-');
    });

    // Join with spaces between words
    return displayWords.join(' ');
};

// Get font size based on display_order (rank) - scaled to fit on TV
const getAnswerFontSize = (displayOrder) => {
    const sizes = {
        1: '2.5rem',   // Most popular - biggest
        2: '2rem',
        3: '1.75rem',
        4: '1.5rem',
        5: '1.25rem',
        6: '1.1rem',
        7: '1rem',     // Least popular - smallest
    };
    return sizes[displayOrder] || '1.5rem';
};

// Get position class based on display_order
const getAnswerPositionClass = (displayOrder) => {
    // Layout: rank 1 in center, others arranged around it
    const positions = {
        1: 'col-span-2 row-start-2', // Center, biggest
        2: 'col-start-1 row-start-1',
        3: 'col-start-2 row-start-1',
        4: 'col-start-1 row-start-3',
        5: 'col-start-2 row-start-3',
        6: 'col-start-1 row-start-4',
        7: 'col-start-2 row-start-4',
    };
    return positions[displayOrder] || '';
};

// Watch for new answers being revealed and trigger success sound
watch(revealedAnswerIds, (newIds, oldIds) => {
    if (oldIds && oldIds.length > 0) {
        const newlyRevealed = newIds.filter(id => !oldIds.includes(id));
        if (newlyRevealed.length > 0) {
            successSoundTrigger.value++;
        }
    }
}, { deep: true });

// Start polling and timer
onMounted(() => {
    fetchEvent();
    fetchGameState();

    // Poll for game state updates every 500ms
    pollInterval = setInterval(fetchGameState, 500);

    // Update timer display every 100ms for smooth countdown
    timerInterval = setInterval(calculateRemainingTime, 100);
});

// Cleanup
onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
    if (timerInterval) clearInterval(timerInterval);
});
</script>

<template>
    <Head title="America Says - Game Board">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Coustard:wght@400;900&display=swap" rel="stylesheet">
    </Head>

    <div
        class="min-h-screen flex flex-col p-2 transition-colors duration-300 relative overflow-hidden"
        :style="{
            backgroundColor: theme.colors.background,
            color: theme.colors.text,
            fontFamily: theme.fonts.question,
        }"
    >
        <!-- Sound Component -->
        <GameSound :trigger="successSoundTrigger" sound-type="success" />

        <!-- Snowflakes (inside main container, behind content) -->
        <div
            class="fixed inset-0 pointer-events-none overflow-hidden"
            style="z-index: 0;"
        >
            <div class="snowflakes" aria-hidden="true">
                <div class="snowflake" v-for="n in 30" :key="n">❅</div>
            </div>
        </div>
        <!-- Question Display -->
        <div class="text-center mb-2 relative" style="z-index: 10;">
            <h1
                class="text-2xl mb-1 px-6 py-2 rounded-lg inline-block"
                :style="{
                    backgroundColor: theme.colors.primary,
                    color: 'white',
                    fontFamily: theme.fonts.question
                }"
            >
                {{ currentQuestion?.question_text || 'Waiting for game to Begin...' }}
            </h1>
        </div>

        <!-- Answers Grid (only shown when timer is running) -->
        <div
            v-if="answers.length > 0 && showAnswers"
            class="flex-1 grid grid-cols-2 gap-2 max-w-5xl mx-auto w-full relative py-1"
            style="z-index: 10;"
        >
            <div
                v-for="answer in answers"
                :key="answer.id"
                :class="getAnswerPositionClass(answer.display_order)"
                class="flex items-center justify-center"
            >
                <div
                    class="answer-box relative w-full h-full min-h-[40px] flex items-center justify-center p-1"
                    :style="{
                        fontSize: getAnswerFontSize(answer.display_order),
                        fontFamily: theme.fonts.answer,
                    }"
                >
                    <div
                        class="text-center"
                        :style="{
                            color: theme.colors.secondary
                        }"
                    >
                        <span v-if="!isAnswerRevealed(answer.id)" style="letter-spacing: -0.15em;">
                            {{ getAnswerDisplay(answer) }}
                        </span>
                        <span v-else class="uppercase typing-reveal">
                            {{ answer.correct_answer }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Timer Display (shown when timer has been started) -->
        <div
            v-if="showTimer"
            class="fixed bottom-4 right-4 text-3xl font-bold tabular-nums px-4 py-2 rounded-xl shadow-2xl"
            style="z-index: 100;"
            :style="{
                backgroundColor: timerWarning ? theme.colors.timerWarning : theme.colors.secondary,
                color: 'white',
                fontFamily: theme.fonts.timer,
            }"
            :class="{
                'animate-pulse': timerWarning,
            }"
        >
            {{ timerDisplay }}
        </div>
    </div>
</template>

<style scoped>
.answer-box {
    /* Removed blur effect to prevent text cutoff */
}

/* Typing reveal animation - faster */
.typing-reveal {
    display: inline-block;
    animation: typing 0.3s steps(15) forwards;
    overflow: hidden;
    white-space: nowrap;
    max-width: 0;
}

@keyframes typing {
    from {
        max-width: 0;
    }
    to {
        max-width: 100%;
    }
}

@keyframes flash {
    0%, 100% {
        opacity: 0;
    }
    50% {
        opacity: 1;
    }
}

/* Snowflakes animation for Christmas theme */
.snowflakes {
    position: absolute;
    top: -10%;
    left: 0;
    right: 0;
    bottom: 0;
    overflow: hidden;
}

.snowflake {
    position: absolute;
    top: -10%;
    color: #87CEEB;
    font-size: 2.5em;
    animation: fall linear infinite;
    user-select: none;
    font-weight: bold;
}

/* Generate varied positions and timings for 30 snowflakes */
.snowflake:nth-child(1) { left: 2%; animation-duration: 8s; animation-delay: 0s; font-size: 1.5em; }
.snowflake:nth-child(2) { left: 8%; animation-duration: 10s; animation-delay: 1s; font-size: 2.2em; }
.snowflake:nth-child(3) { left: 14%; animation-duration: 7s; animation-delay: 2s; font-size: 1.8em; }
.snowflake:nth-child(4) { left: 20%; animation-duration: 9s; animation-delay: 0.5s; font-size: 2em; }
.snowflake:nth-child(5) { left: 26%; animation-duration: 11s; animation-delay: 1.5s; font-size: 1.6em; }
.snowflake:nth-child(6) { left: 32%; animation-duration: 8.5s; animation-delay: 0.8s; font-size: 2.3em; }
.snowflake:nth-child(7) { left: 38%; animation-duration: 9.5s; animation-delay: 2.5s; font-size: 1.7em; }
.snowflake:nth-child(8) { left: 44%; animation-duration: 7.5s; animation-delay: 1.2s; font-size: 2.1em; }
.snowflake:nth-child(9) { left: 50%; animation-duration: 10.5s; animation-delay: 0.3s; font-size: 1.9em; }
.snowflake:nth-child(10) { left: 56%; animation-duration: 8.8s; animation-delay: 1.8s; font-size: 2em; }
.snowflake:nth-child(11) { left: 5%; animation-duration: 9s; animation-delay: 3s; font-size: 1.6em; }
.snowflake:nth-child(12) { left: 11%; animation-duration: 11s; animation-delay: 0.2s; font-size: 2.2em; }
.snowflake:nth-child(13) { left: 17%; animation-duration: 7.8s; animation-delay: 1.5s; font-size: 1.8em; }
.snowflake:nth-child(14) { left: 23%; animation-duration: 9.2s; animation-delay: 2.8s; font-size: 2em; }
.snowflake:nth-child(15) { left: 29%; animation-duration: 10.3s; animation-delay: 0.7s; font-size: 1.7em; }
.snowflake:nth-child(16) { left: 35%; animation-duration: 8.3s; animation-delay: 2.1s; font-size: 2.1em; }
.snowflake:nth-child(17) { left: 41%; animation-duration: 9.7s; animation-delay: 1.3s; font-size: 1.9em; }
.snowflake:nth-child(18) { left: 47%; animation-duration: 7.2s; animation-delay: 0.9s; font-size: 2.3em; }
.snowflake:nth-child(19) { left: 53%; animation-duration: 10.8s; animation-delay: 2.4s; font-size: 1.6em; }
.snowflake:nth-child(20) { left: 59%; animation-duration: 8.6s; animation-delay: 1.1s; font-size: 2em; }
.snowflake:nth-child(21) { left: 62%; animation-duration: 9.3s; animation-delay: 0.4s; font-size: 1.8em; }
.snowflake:nth-child(22) { left: 65%; animation-duration: 11.2s; animation-delay: 2.2s; font-size: 2.2em; }
.snowflake:nth-child(23) { left: 68%; animation-duration: 7.6s; animation-delay: 1.6s; font-size: 1.7em; }
.snowflake:nth-child(24) { left: 71%; animation-duration: 9.9s; animation-delay: 0.6s; font-size: 2.1em; }
.snowflake:nth-child(25) { left: 74%; animation-duration: 10.1s; animation-delay: 2.9s; font-size: 1.9em; }
.snowflake:nth-child(26) { left: 77%; animation-duration: 8.1s; animation-delay: 1.4s; font-size: 2em; }
.snowflake:nth-child(27) { left: 83%; animation-duration: 9.6s; animation-delay: 0.1s; font-size: 1.8em; }
.snowflake:nth-child(28) { left: 86%; animation-duration: 7.9s; animation-delay: 2.6s; font-size: 2.3em; }
.snowflake:nth-child(29) { left: 92%; animation-duration: 10.4s; animation-delay: 1.7s; font-size: 1.6em; }
.snowflake:nth-child(30) { left: 98%; animation-duration: 8.7s; animation-delay: 0.5s; font-size: 2.1em; }

@keyframes fall {
    to {
        transform: translateY(110vh);
    }
}
</style>
