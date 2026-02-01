<template>
    <div>
        <FormLabel v-if="label" :html-for="inputId" :required="required">{{ label }}</FormLabel>

        <div class="flex items-center">
            <!-- Decrement button -->
            <button
                v-if="showControls"
                type="button"
                @click="decrement"
                :disabled="disabled || (min !== undefined && modelValue <= min)"
                aria-label="Decrease value"
                :class="[
                    'flex items-center justify-center border border-r-0 rounded-l transition-colors',
                    'focus:outline-none focus:ring-1 focus:ring-primary',
                    disabled ? 'bg-surface cursor-not-allowed text-muted' : 'bg-white hover:bg-surface text-body',
                    sizes[size].button,
                    error ? 'border-danger' : 'border-border'
                ]"
            >
                <Icon name="minus" :size="size === 'sm' ? 'xs' : 'sm'" />
            </button>

            <!-- Input -->
            <input
                :id="inputId"
                type="number"
                :value="modelValue"
                @input="handleInput"
                :min="min"
                :max="max"
                :step="step"
                :required="required"
                :disabled="disabled"
                :aria-describedby="descriptionId"
                :aria-invalid="!!error"
                :class="[
                    'text-center border transition-colors text-body',
                    'focus:outline-none focus:ring-1',
                    '[appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none',
                    error
                        ? 'border-danger focus:border-danger focus:ring-danger'
                        : 'border-border focus:border-primary focus:ring-primary',
                    disabled ? 'bg-surface cursor-not-allowed text-muted' : 'bg-white',
                    showControls ? 'rounded-none' : 'rounded',
                    sizes[size].input
                ]"
            />

            <!-- Increment button -->
            <button
                v-if="showControls"
                type="button"
                @click="increment"
                :disabled="disabled || (max !== undefined && modelValue >= max)"
                aria-label="Increase value"
                :class="[
                    'flex items-center justify-center border border-l-0 rounded-r transition-colors',
                    'focus:outline-none focus:ring-1 focus:ring-primary',
                    disabled ? 'bg-surface cursor-not-allowed text-muted' : 'bg-white hover:bg-surface text-body',
                    sizes[size].button,
                    error ? 'border-danger' : 'border-border'
                ]"
            >
                <Icon name="plus" :size="size === 'sm' ? 'xs' : 'sm'" />
            </button>
        </div>

        <p v-if="error" :id="descriptionId" class="mt-1 text-sm text-danger">{{ error }}</p>
        <p v-else-if="hint" :id="descriptionId" class="mt-1 text-sm text-subtle">{{ hint }}</p>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import Icon from '@/Components/Base/Icon.vue';
import FormLabel from '@/Components/Form/FormLabel.vue';

const props = defineProps({
    modelValue: { type: Number, default: 0 },
    label: { type: String, default: '' },
    min: { type: Number, default: undefined },
    max: { type: Number, default: undefined },
    step: { type: Number, default: 1 },
    required: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    showControls: { type: Boolean, default: true },
    error: { type: String, default: '' },
    hint: { type: String, default: '' },
    size: { type: String, default: 'md' },
});

const emit = defineEmits(['update:modelValue']);

const sizes = {
    sm: { input: 'w-16 py-1 text-sm', button: 'w-8 h-8' },
    md: { input: 'w-20 py-2 text-base', button: 'w-10 h-10' },
    lg: { input: 'w-24 py-3 text-lg', button: 'w-12 h-12' },
};

const inputId = computed(() => `number-${Math.random().toString(36).substr(2, 9)}`);
const descriptionId = computed(() => (props.error || props.hint) ? `${inputId.value}-desc` : undefined);

function handleInput(event) {
    const value = Number(event.target.value);
    emit('update:modelValue', value);
}

function increment() {
    if (props.max !== undefined && props.modelValue >= props.max) return;
    emit('update:modelValue', props.modelValue + props.step);
}

function decrement() {
    if (props.min !== undefined && props.modelValue <= props.min) return;
    emit('update:modelValue', props.modelValue - props.step);
}
</script>
