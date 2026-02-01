<template>
    <div class="flex items-start gap-2">
        <input
            :id="inputId"
            type="radio"
            :name="name"
            :value="value"
            :checked="modelValue === value"
            @change="$emit('update:modelValue', value)"
            :required="required"
            :disabled="disabled"
            :class="[
                'border-border text-primary mt-0.5',
                'focus:ring-primary focus:ring-offset-0',
                disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
            ]"
        />
        <label
            v-if="label"
            :for="inputId"
            :class="[
                'select-none',
                disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer',
                'text-gray-dark'
            ]"
        >
            {{ label }}
            <span v-if="required" class="text-danger">*</span>
            <p v-if="description" class="text-sm text-subtle mt-0.5">{{ description }}</p>
        </label>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: { type: [String, Number, Boolean], default: '' },
    value: { type: [String, Number, Boolean], required: true },
    name: { type: String, required: true },
    label: { type: String, default: '' },
    description: { type: String, default: '' },
    required: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
});

defineEmits(['update:modelValue']);

const inputId = computed(() => `radio-${props.name}-${Math.random().toString(36).substr(2, 9)}`);
</script>
