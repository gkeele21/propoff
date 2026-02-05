<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import Logo from '@/Components/Domain/Logo.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

const joinGroupForm = useForm({
    code: '',
});

const joinGroup = () => {
    joinGroupForm.post(route('groups.join'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="PropOff - Game Prediction Platform" />

    <div class="relative min-h-screen bg-surface-inset selection:bg-primary selection:text-white overflow-hidden">
        <!-- Aurora Background Blobs -->
        <div class="absolute top-20 left-10 w-72 h-72 rounded-full blur-[100px] bg-[#1a3490]/30"></div>
        <div class="absolute top-40 right-20 w-96 h-96 rounded-full blur-[120px] bg-warning/20"></div>
        <div class="absolute bottom-40 left-1/3 w-80 h-80 rounded-full blur-[100px] bg-success/25"></div>
        <div class="absolute bottom-20 right-1/4 w-64 h-64 rounded-full blur-[80px] bg-danger/15"></div>

        <!-- Content Container -->
        <div class="relative min-h-screen">
            <!-- Navigation -->
            <div v-if="canLogin" class="fixed top-0 right-0 p-6 text-end z-10">
                <Link
                    v-if="$page.props.auth.user"
                    :href="['admin', 'manager'].includes($page.props.auth.user.role) ? route('admin.events.index') : route('dashboard')"
                    class="font-semibold text-muted hover:text-body transition-colors"
                >My Home</Link>
                <Link
                    v-if="$page.props.auth.user"
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="ms-4 font-semibold text-muted hover:text-body transition-colors"
                >Log Out</Link>
                <template v-else>
                    <Link
                        :href="route('login')"
                        class="font-semibold text-muted hover:text-body transition-colors"
                    >Log in</Link>
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="ms-4 px-4 py-2 bg-surface border border-border rounded-lg font-semibold text-body hover:bg-surface-elevated transition-colors"
                    >Register</Link>
                </template>
            </div>

            <div class="max-w-7xl mx-auto p-6 lg:p-8 pt-20">
                <!-- Logo and Title -->
                <div class="flex flex-col items-center mb-12">
                    <div class="flex items-center gap-3 mb-6 backdrop-blur-sm">
                        <Logo variant="full" size="xl" />
                    </div>
                    <h1 class="text-5xl md:text-6xl font-bold text-body mb-4 text-center">
                        Predict. Compete. <span class="text-warning">Win.</span>
                    </h1>
                    <p class="text-xl text-muted text-center max-w-2xl">
                        Test your prediction skills in the ultimate prop betting challenge. Join groups, answer questions about game events, climb the leaderboard, and prove you're the best.
                    </p>
                </div>

                <!-- Feature Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-12">
                    <!-- Prop Questions Card -->
                    <div class="p-6 backdrop-blur-sm border rounded-lg bg-surface/80 border-border/50 hover:bg-surface-elevated/80 transition-colors">
                        <div class="flex items-center mb-4">
                            <div class="h-12 w-12 flex items-center justify-center rounded-full mr-4 bg-transparent border border-danger icon-glow-danger">
                                <svg class="w-6 h-6 text-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </div>
                            <h2 class="text-xl font-semibold text-body">Prop Questions</h2>
                        </div>
                        <p class="text-muted text-sm">Answer prediction questions about real game events.</p>
                    </div>

                    <!-- Group Competition Card -->
                    <div class="p-6 backdrop-blur-sm border rounded-lg bg-surface/80 border-border/50 hover:bg-surface-elevated/80 transition-colors">
                        <div class="flex items-center mb-4">
                            <div class="h-12 w-12 flex items-center justify-center rounded-full mr-4 bg-transparent border border-[#1a3490] icon-glow-primary">
                                <svg class="w-6 h-6 text-[#1a3490]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <h2 class="text-xl font-semibold text-body">Group Competition</h2>
                        </div>
                        <p class="text-muted text-sm">Compete with friends in private groups.</p>
                    </div>

                    <!-- Live Leaderboards Card -->
                    <div class="p-6 backdrop-blur-sm border rounded-lg bg-surface/80 border-border/50 hover:bg-surface-elevated/80 transition-colors">
                        <div class="flex items-center mb-4">
                            <div class="h-12 w-12 flex items-center justify-center rounded-full mr-4 bg-transparent border border-success icon-glow-success">
                                <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <h2 class="text-xl font-semibold text-body">Live Leaderboards</h2>
                        </div>
                        <p class="text-muted text-sm">Track your rank in real-time.</p>
                    </div>
                </div>

                <!-- Join Group with Code -->
                <div class="mt-16 max-w-2xl mx-auto bg-surface-elevated border border-border rounded-xl p-8 text-center">
                    <h2 class="text-2xl font-bold text-body mb-2">Have a Group Code?</h2>
                    <p class="text-muted mb-4">Enter your code below to join</p>

                    <form @submit.prevent="joinGroup" class="flex flex-col sm:flex-row gap-4">
                        <div class="flex-1">
                            <input
                                v-model="joinGroupForm.code"
                                type="text"
                                placeholder="ABCD1234"
                                maxlength="8"
                                class="w-full px-4 py-3 bg-surface-inset border border-border rounded-lg text-body placeholder-muted text-center text-lg font-mono tracking-widest uppercase input-focus-glow"
                                :class="{ 'border-danger': joinGroupForm.errors.code }"
                            />
                            <p v-if="joinGroupForm.errors.code" class="mt-2 text-sm text-danger">
                                {{ joinGroupForm.errors.code }}
                            </p>
                        </div>
                        <button
                            type="submit"
                            :disabled="joinGroupForm.processing || !joinGroupForm.code"
                            class="btn-primary px-8 py-3 text-white font-bold rounded-lg disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="joinGroupForm.processing">Joining...</span>
                            <span v-else>Join</span>
                        </button>
                    </form>
                </div>

                <!-- Call to Action -->
                <div class="flex justify-center mt-12 pb-8">
                    <Link
                        v-if="!$page.props.auth.user"
                        :href="route('register')"
                        class="btn-success px-8 py-4 text-white font-bold text-lg rounded-lg"
                    >
                        Get Started Free
                    </Link>
                    <Link
                        v-else
                        :href="['admin', 'manager'].includes($page.props.auth.user.role) ? route('admin.events.index') : route('dashboard')"
                        class="btn-success px-8 py-4 text-white font-bold text-lg rounded-lg"
                    >
                        Go to My Home
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Icon containers with outline + glow */
.icon-glow-danger {
    box-shadow: 0 0 15px rgba(175, 25, 25, 0.3), 0 0 30px rgba(175, 25, 25, 0.15);
}
.icon-glow-primary {
    box-shadow: 0 0 15px rgba(26, 52, 144, 0.3), 0 0 30px rgba(26, 52, 144, 0.15);
}
.icon-glow-success {
    box-shadow: 0 0 15px rgba(87, 208, 37, 0.3), 0 0 30px rgba(87, 208, 37, 0.15);
}

/* Input focus state with glow */
.input-focus-glow:focus {
    outline: none;
    box-shadow: 0 0 0 1px rgb(var(--color-text)), 0 0 0 3px #1a3490, 0 0 15px rgba(26, 52, 144, 0.3);
    border-color: transparent;
}

/* Button styles with hover glow */
.btn-primary {
    background-color: #1a3490;
    transition: all 0.2s ease;
}
.btn-primary:hover:not(:disabled) {
    box-shadow: 0 0 0 1px rgb(var(--color-text)), 0 0 0 3px #1a3490, 0 0 20px rgba(26, 52, 144, 0.4);
}

.btn-success {
    background-color: rgb(var(--color-success));
    transition: all 0.2s ease;
}
.btn-success:hover:not(:disabled) {
    box-shadow: 0 0 0 1px rgb(var(--color-text)), 0 0 0 3px rgb(var(--color-success)), 0 0 20px rgba(87, 208, 37, 0.4);
}
</style>
