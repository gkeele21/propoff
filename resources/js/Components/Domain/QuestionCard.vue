<template>
    <div class="space-y-3">
        <!-- Question Header -->
        <div v-if="showHeader" class="flex items-start justify-between mb-4">
            <div class="flex items-start gap-2">
                <span v-if="questionNumber" class="text-lg font-bold text-body">
                    Q{{ questionNumber }}.
                </span>
                <p class="text-lg text-body">{{ question }}</p>
            </div>
            <span v-if="points" class="px-3 py-1 bg-primary/10 text-primary text-sm font-medium rounded flex-shrink-0">
                {{ points }} {{ points === 1 ? 'pt' : 'pts' }}
            </span>
        </div>

        <!-- Options -->
        <div class="space-y-3">
            <div
                v-for="(option, index) in normalizedOptions"
                :key="index"
                class="flex items-center justify-between p-4 border-2 rounded-lg transition"
                :class="[
                    getOptionClasses(option),
                    disabled ? 'cursor-not-allowed opacity-60' : 'cursor-pointer'
                ]"
                @click="!disabled && $emit('update:modelValue', option.value)"
            >
                <div class="flex items-center flex-1 gap-3">
                    <!-- Result Icon (results mode only) -->
                    <div v-if="showResults" class="w-5 flex-shrink-0">
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
                                ? 'border-primary bg-primary'
                                : 'border-border-strong bg-transparent'
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
                </div>

                <!-- Bonus Points -->
                <div v-if="option.points && option.points > 0" class="ml-4 flex-shrink-0">
                    <span class="text-xs font-semibold text-primary bg-primary/15 px-2 py-1 rounded">
                        +{{ option.points }} bonus {{ option.points === 1 ? 'pt' : 'pts' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Points Hint -->
        <p v-if="points && hasBonus" class="text-xs text-subtle mt-2">
            Base: {{ points }} {{ points === 1 ? 'pt' : 'pts' }} + any bonus shown
        </p>

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

// Check if any option has bonus points
const hasBonus = computed(() => {
    return normalizedOptions.value.some(opt => opt.points > 0);
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
        if (isCorrect(option) && isSelected(option)) {
            return 'border-success bg-success/10';
        }
        if (isCorrect(option) && !isSelected(option)) {
            return 'border-success/50 bg-success/5';
        }
        if (!isCorrect(option) && isSelected(option)) {
            return 'border-danger bg-danger/10';
        }
        return 'border-border bg-surface-inset';
    }

    // Normal mode styling
    if (isSelected(option)) {
        return 'border-primary bg-primary/10';
    }
    return 'border-border bg-surface-inset hover:border-border-strong hover:bg-surface-header';
}
</script>
