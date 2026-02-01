<template>
    <span
        :class="[
            'inline-flex items-center font-semibold',
            variants[variant],
            sizes[size],
            roundedClass
        ]"
    >
        <Icon v-if="icon" :name="icon" :size="iconSize" class="mr-1" />
        <slot />
    </span>
</template>

<script setup>
import { computed } from 'vue';
import Icon from '@/Components/Base/Icon.vue';

const props = defineProps({
    variant: { type: String, default: 'primary' },
    size: { type: String, default: 'md' },
    icon: { type: String, default: '' },
    rounded: { type: String, default: '' },
});

const roundedClass = computed(() => {
    if (!props.rounded) return 'rounded';
    return `rounded-${props.rounded}`;
});

const variants = {
    primary: 'bg-primary text-white',
    secondary: 'bg-secondary text-white',
    accent: 'bg-warning text-white',
    success: 'bg-success text-white',
    danger: 'bg-danger text-white',
    warning: 'bg-warning text-body',
    info: 'bg-info text-white',
    outline: 'bg-transparent border border-primary text-primary',
    dark: 'bg-black text-white',
    light: 'bg-white/20 text-white border border-white/30',           // For dark backgrounds
    'outline-light': 'bg-transparent border border-white/50 text-white', // Outline on dark backgrounds
    // Soft variants with low opacity backgrounds (per spec)
    'danger-soft': 'bg-danger/20 text-danger',
    'warning-soft': 'bg-warning/30 text-warning',
    'success-soft': 'bg-success/20 text-primary',
    'info-soft': 'bg-info/20 text-primary',
    'primary-soft': 'bg-primary/15 text-primary',
};

const sizes = {
    sm: 'px-1.5 py-0.5 text-xs',
    md: 'px-2 py-0.5 text-sm',
    lg: 'px-3 py-1 text-base',
};

const iconSize = computed(() => props.size === 'lg' ? 'sm' : 'xs');
</script>
