<template>
    <div class="space-y-3">
        <!-- Question Header -->
        <div v-if="showHeader" class="flex items-start justify-between mb-4">
            <div class="flex items-start gap-2">
                <span v-if="questionNumber" class="text-lg font-bold text-black">
                    Q{{ questionNumber }}.
                </span>
                <p class="text-lg text-black">{{ question }}</p>
            </div>
            <span v-if="points" class="px-3 py-1 bg-primary/10 text-primary text-sm font-medium rounded flex-shrink-0">
                {{ points }} {{ points === 1 ? 'pt' : 'pts' }}
            </span>
        </div>

        <!-- Options -->
        <div class="space-y-3">
            <label
                v-for="(option, index) in normalizedOptions"
                :key="index"
                class="flex items-center justify-between p-4 border-2 rounded-lg transition"
                :class="[
                    getOptionClasses(option),
                    disabled ? 'cursor-not-allowed opacity-60' : 'cursor-pointer'
                ]"
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
                            class="text-muted"
                            size="sm"
                        />
                        <Icon
                            v-else-if="!isCorrect(option) && isSelected(option)"
                            name="xmark"
                            class="text-danger"
                            size="sm"
                        />
                    </div>

                    <!-- Radio Input -->
                    <input
                        type="radio"
                        :name="inputName"
                        :value="option.value"
                        :checked="modelValue === option.value"
                        @change="$emit('update:modelValue', option.value)"
                        :disabled="disabled"
                        class="h-4 w-4 text-primary focus:ring-primary/50 border-border"
                        :class="disabled ? 'cursor-not-allowed' : 'cursor-pointer'"
                    />

                    <!-- Letter Prefix (optional) -->
                    <span v-if="showLetters" class="font-semibold text-gray-dark w-6">
                        {{ getLetter(index) }})
                    </span>

                    <!-- Option Label -->
                    <span :class="[
                        'text-black',
                        showResults && isCorrect(option) ? 'font-semibold text-success' : ''
                    ]">
                        {{ option.label }}
                    </span>
                </div>

                <!-- Bonus Points -->
                <div v-if="option.points && option.points > 0" class="ml-4 flex-shrink-0">
                    <span class="text-xs font-medium text-primary bg-primary/10 px-2 py-1 rounded">
                        +{{ option.points }} bonus {{ option.points === 1 ? 'pt' : 'pts' }}
                    </span>
                </div>
            </label>
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
        return 'border-border bg-white';
    }

    // Normal mode styling
    if (isSelected(option)) {
        return 'border-primary bg-primary/10';
    }
    return 'border-border hover:border-gray-light';
}
</script>
