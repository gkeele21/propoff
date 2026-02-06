<template>
    <div class="bg-surface border-b border-border shadow-sm sticky top-16 z-40">
        <div class="py-4 px-4 sm:px-6 lg:px-8">
            <!-- Breadcrumbs -->
            <Breadcrumb v-if="crumbs && crumbs.length > 0" :crumbs="crumbs" class="mb-2" />

            <!-- Title Section with Actions -->
            <div class="flex justify-between items-center">
                <!-- Left side: Title and optional subtitle/metadata -->
                <div class="flex-1 min-w-0">
                    <h1 class="font-bold text-xl text-body leading-tight flex items-center gap-3">
                        {{ title }}
                        <slot name="titleSuffix" />
                    </h1>

                    <!-- Subtitle text (if provided as prop) -->
                    <p v-if="subtitle" class="text-sm text-muted mt-1">
                        {{ subtitle }}
                    </p>

                    <!-- Metadata slot for custom content like event status, date, etc. -->
                    <div v-if="$slots.metadata" class="mt-1 text-sm text-muted">
                        <slot name="metadata" />
                    </div>
                </div>

                <!-- Right side: Action buttons -->
                <div v-if="$slots.actions" class="flex items-center gap-3 ml-4 flex-shrink-0">
                    <slot name="actions" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import Breadcrumb from '@/Components/Base/Breadcrumb.vue';

defineProps({
    title: {
        type: String,
        required: true,
    },
    subtitle: {
        type: String,
        default: null,
    },
    crumbs: {
        type: Array,
        default: () => [],
    },
});
</script>
