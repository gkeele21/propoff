<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import Button from '@/Components/Base/Button.vue';
import TextField from '@/Components/Form/TextField.vue';
import Card from '@/Components/Base/Card.vue';
import Icon from '@/Components/Base/Icon.vue';

const props = defineProps({
    group: Object,
    step: {
        type: String,
        default: 'name',
    },
    existingNames: {
        type: Array,
        default: () => [],
    },
    verifyEntry: Object,
});

const page = usePage();

// Get flash data for step navigation
const currentStep = computed(() => {
    if (page.props.flash?.step) {
        return page.props.flash.step;
    }
    return props.step;
});

const flashVerifyEntry = computed(() => page.props.flash?.verifyEntry || props.verifyEntry);
const flashExistingName = computed(() => page.props.flash?.existingName);

// Form for name step
const nameForm = useForm({
    name: '',
});

// Form for initial step
const initialForm = useForm({
    name: '',
    last_initial: '',
});

// Form for verify step
const verifyForm = useForm({
    name: '',
    last_initial: '',
    verified: false,
});

// Submit name
const submitName = () => {
    nameForm.post(route('play.join.process', { code: props.group.code }));
};

// Submit with initial
const submitInitial = () => {
    initialForm.name = nameForm.name || flashExistingName.value?.split(' ')[0] || '';
    initialForm.post(route('play.join.process', { code: props.group.code }));
};

// Verify and continue as existing user
const verifyAndContinue = () => {
    verifyForm.name = flashVerifyEntry.value?.name?.split(' ')[0] || '';
    verifyForm.last_initial = flashVerifyEntry.value?.name?.split(' ')[1]?.charAt(0) || '';
    verifyForm.verified = true;
    verifyForm.post(route('play.join.process', { code: props.group.code }));
};

// Create new user (reject verification)
const createNew = () => {
    // Go back to initial step to choose different initial
    nameForm.name = flashVerifyEntry.value?.name?.split(' ')[0] || '';
    nameForm.post(route('play.join.process', { code: props.group.code }));
};

// Format date helper
const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <Head :title="`Join ${group.name}`" />

    <GuestLayout>
        <div class="max-w-md mx-auto">
            <!-- Step 1: Name Input -->
            <div v-if="currentStep === 'name'" class="bg-surface border border-border rounded-2xl p-8">
                <h1 class="text-2xl font-bold text-body mb-2">Join {{ group.name }}</h1>
                <p class="text-muted mb-6">Enter your name to join the game</p>

                <form @submit.prevent="submitName">
                    <TextField
                        v-model="nameForm.name"
                        label="What's your name?"
                        placeholder="Enter your first name"
                        :error="nameForm.errors.name"
                        autofocus
                    />

                    <Button
                        type="submit"
                        variant="primary"
                        size="lg"
                        class="w-full mt-6"
                        :loading="nameForm.processing"
                    >
                        Continue
                        <Icon name="arrow-right" class="ml-2" size="sm" />
                    </Button>
                </form>

                <div class="mt-6 pt-6 border-t border-border text-center text-sm text-muted">
                    Already have an account?
                    <Link :href="route('login')" class="text-primary hover:underline">Log in</Link>
                </div>
            </div>

            <!-- Step 2: Last Initial -->
            <div v-else-if="currentStep === 'initial'" class="bg-surface border border-border rounded-2xl p-8">
                <h1 class="text-2xl font-bold text-body mb-2">One more thing...</h1>
                <p class="text-muted mb-6">
                    There's already a "{{ flashExistingName?.split(' ')[0] || nameForm.name }}" in this group.
                    What's your last initial?
                </p>

                <form @submit.prevent="submitInitial">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-body mb-2">Last initial</label>
                        <input
                            v-model="initialForm.last_initial"
                            type="text"
                            maxlength="1"
                            class="w-20 h-14 text-center text-2xl font-bold bg-surface-inset border border-border rounded-lg text-body focus:border-primary focus:ring-primary uppercase"
                            autofocus
                        />
                        <p class="text-sm text-muted mt-2">
                            You'll appear as "{{ flashExistingName?.split(' ')[0] || nameForm.name }} {{ initialForm.last_initial?.toUpperCase() || '?' }}."
                        </p>
                    </div>

                    <Button
                        type="submit"
                        variant="primary"
                        size="lg"
                        class="w-full"
                        :loading="initialForm.processing"
                        :disabled="!initialForm.last_initial"
                    >
                        Continue
                        <Icon name="arrow-right" class="ml-2" size="sm" />
                    </Button>
                </form>
            </div>

            <!-- Step 3: Verify -->
            <div v-else-if="currentStep === 'verify'" class="bg-surface border border-border rounded-2xl p-8">
                <h1 class="text-2xl font-bold text-body mb-2">Is this you?</h1>
                <p class="text-muted mb-6">
                    We found an existing entry for "{{ flashVerifyEntry?.name }}"
                </p>

                <div class="bg-surface-elevated border border-border rounded-xl p-5 mb-6">
                    <div class="font-semibold text-body mb-1">{{ flashVerifyEntry?.name }}</div>
                    <div class="text-muted">
                        {{ flashVerifyEntry?.answered }} of {{ flashVerifyEntry?.total }} questions answered
                    </div>
                </div>

                <div class="flex gap-3">
                    <Button
                        variant="primary"
                        class="flex-1"
                        :loading="verifyForm.processing"
                        @click="verifyAndContinue"
                    >
                        Yes, that's me
                    </Button>
                    <Button
                        variant="outline"
                        class="flex-1"
                        @click="createNew"
                    >
                        No, create new
                    </Button>
                </div>
            </div>

            <!-- Event Info -->
            <div v-if="group.event" class="mt-4 text-center text-sm text-muted">
                {{ group.event.name }}
                <span v-if="group.event.event_date"> • {{ formatDate(group.event.event_date) }}</span>
            </div>
        </div>
    </GuestLayout>
</template>
