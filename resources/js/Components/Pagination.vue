<template>
    <div v-if="data.data && data.data.length > 0" class="bg-surface overflow-hidden shadow-sm sm:rounded-lg border border-border">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div class="text-sm text-muted">
                    Showing <span class="font-medium text-body">{{ data.from }}</span> to <span class="font-medium text-body">{{ data.to }}</span> of <span class="font-medium text-body">{{ data.total }}</span> {{ itemName }}
                </div>
                <div class="flex items-center space-x-2">
                    <component
                        v-for="link in data.links"
                        :key="link.label"
                        :is="link.url ? Link : 'span'"
                        :href="link.url || undefined"
                        :class="[
                            'px-3 py-2 text-sm font-medium rounded-md',
                            link.active
                                ? 'bg-primary text-white'
                                : link.url
                                    ? 'text-body hover:bg-surface-overlay'
                                    : 'text-subtle cursor-not-allowed'
                        ]"
                        :preserve-state="link.url ? preserveState : undefined"
                        :preserve-scroll="link.url ? preserveScroll : undefined"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    data: {
        type: Object,
        required: true,
    },
    itemName: {
        type: String,
        default: 'items',
    },
    preserveState: {
        type: Boolean,
        default: true,
    },
    preserveScroll: {
        type: Boolean,
        default: true,
    },
});
</script>
