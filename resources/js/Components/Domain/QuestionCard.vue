<template>
    <div class="space-y-3">
        <!-- Question Header -->
        <div v-if="showHeader" class="flex items-start justify-between mb-4">
            <div class="flex items-start gap-2">
                <span v-if="questionNumber" class="text-lg font-bold text-body">
                    {{ questionNumber }}.
                </span>
                <p class="text-lg text-body">{{ question }}</p>
            </div>
            <span v-if="points" class="px-3 py-1 bg-primary/10 text-primary text-sm font-medium rounded flex-shrink-0">
                {{ points }} {{ points === 1 ? 'point' : 'points' }}
            </span>
        </div>

        <!-- Options -->
        <div class="space-y-3">
            <label
                v-for="(option, index) in normalizedOptions"
                :key="index"
                :for="`${inputName}-${index}`"
                class="flex items-center justify-between p-4 border-2 rounded-lg transition-all focus-within-glow"
                :class="[
                    getOptionClasses(option),
                    disabled ? 'cursor-not-allowed opacity-60' : 'cursor-pointer'
                ]"
            >
                <!-- Hidden Radio Input for Keyboard Navigation -->
                <input
                    :id="`${inputName}-${index}`"
                    type="radio"
                    :name="inputName"
                    :value="option.value"
                    :checked="isSelected(option)"
                    :disabled="disabled"
                    @change="$emit('update:modelValue', option.value)"
                    class="sr-only"
                />
                <div class="flex items-center flex-1 gap-3">
                    <!-- Result Icon (results mode only, when showResultIcons is true) -->
                    <div v-if="showResults && showResultIcons" class="w-5 flex-shrink-0">
                        <Icon
                            v-if="isCorrect(option) && isSelected(option)"
                            name="check"
                            class="text-success"
                            size="sm"
                        />
                        <Icon
                            v-else-if="isCorrect(option) && !isSelected(option)"
                            name="arrow-right"
                            class="text-success"
                            size="sm"
                        />
                        <Icon
                            v-else-if="!isCorrect(option) && isSelected(option)"
                            name="xmark"
                            class="text-danger"
                            size="sm"
                        />
                    </div>

                    <!-- Custom Radio Button -->
                    <div
                        class="w-[18px] h-[18px] rounded-full border-2 flex-shrink-0 relative transition-colors"
                        :class="[
                            isSelected(option)
                                ? (selectionColor === 'info' ? 'border-info bg-info' : 'border-primary bg-primary')
                                : (showResults && isCorrect(option) ? 'border-success bg-success' : 'border-border-strong bg-transparent')
                        ]"
                    >
                        <!-- Inner dot when selected -->
                        <div
                            v-if="isSelected(option)"
                            class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-1.5 h-1.5 rounded-full bg-bg"
                        ></div>
                    </div>

                    <!-- Letter Prefix (optional) -->
                    <span v-if="showLetters" class="font-semibold text-muted w-6">
                        {{ getLetter(index) }})
                    </span>

                    <!-- Option Label -->
                    <span :class="[
                        'text-body',
                        showResults && isCorrect(option) ? 'font-semibold text-success' : ''
                    ]">
                        {{ option.label }}
                    </span>

                    <!-- Bonus Points -->
                    <span v-if="option.points && option.points > 0" class="text-xs font-semibold text-primary bg-primary/15 px-2 py-1 rounded">
                        +{{ option.points }} bonus {{ option.points === 1 ? 'point' : 'points' }}
                    </span>
                </div>
            </label>
        </div>

        <!-- Error Message -->
        <p v-if="error" class="text-sm text-danger mt-2">
            <Icon name="circle-exclamation" size="xs" class="mr-1" />
            {{ typeof error === 'string' ? error : 'Please select an answer' }}
        </p>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import Icon from '@/Components/Base/Icon.vue';

const props = defineProps({
    modelValue: { type: [String, Number], default: '' },
    question: { type: String, default: '' },
    options: { type: Array, required: true },
    points: { type: Number, default: 0 },
    questionNumber: { type: Number, default: null },
    correctAnswer: { type: [String, Number], default: null },
    showResults: { type: Boolean, default: false },
    showHeader: { type: Boolean, default: true },
    showLetters: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    error: { type: [String, Boolean], default: false },
    name: { type: String, default: '' },
    selectionColor: { type: String, default: 'primary' }, // 'primary' or 'info'
    showResultIcons: { type: Boolean, default: true }, // Show check/x/arrow icons in results mode
});

defineEmits(['update:modelValue']);

const alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

// Generate unique input name for radio group
const inputName = computed(() => {
    return props.name || `question-${Math.random().toString(36).substr(2, 9)}`;
});

// Normalize options to always have { label, value, points } format
const normalizedOptions = computed(() => {
    return props.options.map((option, index) => {
        if (typeof option === 'string') {
            return { label: option, value: option, points: 0 };
        }
        return {
            label: option.label || option.text || option,
            value: option.value || option.label || option.text || option,
            points: option.points || option.bonus || 0,
        };
    });
});

function getLetter(index) {
    return alphabet.charAt(index);
}

function isSelected(option) {
    return props.modelValue === option.value;
}

function isCorrect(option) {
    return props.correctAnswer !== null && option.value === props.correctAnswer;
}

function getOptionClasses(option) {
    // Results mode styling
    if (props.showResults) {
        // Selection takes precedence (for pending selections)
        if (isSelected(option) && !isCorrect(option)) {
            return props.selectionColor === 'info'
                ? 'border-info bg-info/10 checked-glow'
                : 'border-primary bg-primary/10 checked-glow';
        }
        if (isCorrect(option) && isSelected(option)) {
            return 'border-success bg-success/10 checked-glow';
        }
        if (isCorrect(option) && !isSelected(option)) {
            return 'border-success bg-success/10';
        }
        if (!isCorrect(option) && isSelected(option)) {
            return props.selectionColor === 'info'
                ? 'border-info bg-info/10 checked-glow'
                : 'border-danger bg-danger/10 checked-glow';
        }
        return 'border-border bg-surface-inset hover:border-border-strong hover:bg-surface-overlay';
    }

    // Normal mode styling
    if (isSelected(option)) {
        return props.selectionColor === 'info'
            ? 'border-info bg-info/10 checked-glow'
            : 'border-primary bg-primary/10 checked-glow';
    }
    return 'border-border bg-surface-inset hover:border-border-strong hover:bg-surface-overlay';
}
</script>
