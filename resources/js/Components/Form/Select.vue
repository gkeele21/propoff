<template>
    <div>
        <FormLabel v-if="label" :html-for="inputId" :required="required">{{ label }}</FormLabel>

        <select
            :id="inputId"
            :value="modelValue"
            @change="handleChange"
            :required="required"
            :disabled="disabled"
            :class="[
                'block rounded border transition-colors appearance-none bg-no-repeat',
                'focus:outline-none focus:ring-2',
                variantClasses,
                disabled ? 'cursor-not-allowed opacity-50' : '',
                sizes[size],
                shouldBeFullWidth ? 'w-full' : '',
            ]"
            :style="arrowStyle"
        >
            <option v-if="placeholder" value="" disabled>{{ placeholder }}</option>
            <option v-if="allowEmpty" :value="emptyValue">{{ emptyLabel }}</option>
            <option
                v-for="option in normalizedOptions"
                :key="option.value"
                :value="option.value"
            >
                {{ option.label }}
            </option>
        </select>

        <p v-if="error" class="mt-1 text-sm text-danger">{{ error }}</p>
        <p v-else-if="hint" class="mt-1 text-sm text-subtle">{{ hint }}</p>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import FormLabel from '@/Components/Form/FormLabel.vue';

const props = defineProps({
    modelValue: { type: [String, Number, null], default: '' },
    options: { type: Array, required: true },
    // For simple arrays of objects, specify which fields to use
    valueField: { type: String, default: 'value' },
    labelField: { type: [String, Array], default: 'label' },
    // Nested object support (e.g., option.user.name)
    labelObject: { type: String, default: '' },
    label: { type: String, default: '' },
    placeholder: { type: String, default: '' },
    required: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    error: { type: String, default: '' },
    hint: { type: String, default: '' },
    size: { type: String, default: 'md' },
    variant: { type: String, default: 'default' }, // 'default' | 'outline-light'
    autoWidth: { type: Boolean, default: false },
    allowEmpty: { type: Boolean, default: false },
    emptyValue: { type: [String, Number, null], default: '' },
    emptyLabel: { type: String, default: '-- Select --' },
});

const emit = defineEmits(['update:modelValue', 'change']);

const sizes = {
    sm: 'py-1.5 pl-3 pr-8 text-sm',
    md: 'py-2 pl-3 pr-8 text-base',
    lg: 'py-3 pl-3 pr-8 text-lg',
};

const inputId = computed(() => `select-${Math.random().toString(36).substr(2, 9)}`);

// Determine if full width should be applied
const shouldBeFullWidth = computed(() => !props.autoWidth);

// Arrow color based on variant
const arrowStyle = computed(() => {
    const color = props.variant === 'outline-light' ? 'white' : '%23374151'; // %23 = # for URL encoding (gray-700)
    const svg = `url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 10 6'%3E%3Cpath fill='${color}' d='M5 6L0 0h10z'/%3E%3C/svg%3E")`;
    return {
        backgroundImage: svg,
        backgroundPosition: 'right 0.6rem center',
        backgroundSize: '8px 5px',
    };
});

const variantClasses = computed(() => {
    if (props.error) {
        return 'border-danger focus:border-danger focus:ring-danger bg-white';
    }

    switch (props.variant) {
        case 'outline-light':
            // For dark backgrounds (e.g., primary header)
            return 'bg-transparent text-white border-white focus:ring-white/50 [&>option]:text-body';
        default:
            return props.disabled
                ? 'bg-surface border-border focus:border-primary focus:ring-primary'
                : 'bg-white border-border focus:border-primary focus:ring-primary';
    }
});


const normalizedOptions = computed(() => {
    return props.options.map(option => {
        // If option is a primitive (string/number), use it as both value and label
        if (typeof option !== 'object') {
            return { value: option, label: option };
        }

        // Get the value
        const value = option[props.valueField];

        // Get the label - support nested objects and multiple label fields
        let label;
        const source = props.labelObject ? option[props.labelObject] : option;

        if (Array.isArray(props.labelField)) {
            // Support array of fields to concatenate (e.g., ['first_name', 'last_name'])
            label = props.labelField.map(field => source?.[field]).filter(Boolean).join(' ');
        } else {
            label = source?.[props.labelField];
        }

        return { value, label };
    });
});

function handleChange(event) {
    const value = event.target.value;
    // Convert to number if it looks like a number and original modelValue was a number
    // But keep empty string as empty string (for "All" options)
    const parsedValue = value !== '' && !isNaN(value) && typeof props.modelValue === 'number'
        ? Number(value)
        : value;
    emit('update:modelValue', parsedValue);
    emit('change', parsedValue);
}
</script>
