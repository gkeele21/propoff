<template>
    <div :class="[centered ? 'text-center' : '', containerClass]">
        <div :class="[sizes[size].title, weights[weight], titleClass]">
            <slot name="title">{{ title }}</slot>
        </div>
        <p v-if="subtitle || $slots.subtitle" :class="['mt-2', sizes[size].subtitle, subtitleClass]">
            <slot name="subtitle">{{ subtitle }}</slot>
        </p>
        <div v-if="$slots.actions" class="mt-4">
            <slot name="actions" />
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    title: { type: String, default: '' },
    subtitle: { type: String, default: '' },
    size: { type: String, default: 'lg' }, // sm, md, lg, xl
    weight: { type: String, default: 'bold' }, // normal, medium, semibold, bold
    centered: { type: Boolean, default: false },
    containerClass: { type: String, default: '' },
    titleClass: { type: String, default: 'text-body' },
    subtitleClass: { type: String, default: 'text-subtle' },
});

const sizes = {
    sm: {
        title: 'text-lg',
        subtitle: 'text-sm',
    },
    md: {
        title: 'text-xl',
        subtitle: 'text-base',
    },
    lg: {
        title: 'text-2xl',
        subtitle: 'text-base',
    },
    xl: {
        title: 'text-3xl',
        subtitle: 'text-lg',
    },
};

const weights = {
    normal: 'font-normal',
    medium: 'font-medium',
    semibold: 'font-semibold',
    bold: 'font-bold',
};
</script>
