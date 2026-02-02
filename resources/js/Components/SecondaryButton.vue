<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center px-4 py-2 bg-surface-elevated border border-border rounded-md font-semibold text-xs text-body uppercase tracking-widest shadow-sm hover:bg-surface-overlay focus:outline-none focus:ring-2 focus:ring-primary/50 focus:ring-offset-2 focus:ring-offset-bg disabled:opacity-25 transition ease-in-out duration-150"
    >
        <slot />
    </button>
</template>
