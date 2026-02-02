<template>
    <div>
        <!-- Breadcrumbs -->
        <Breadcrumb v-if="crumbs && crumbs.length > 0" :crumbs="crumbs" />

        <!-- Title Section with Actions -->
        <div class="flex justify-between items-start">
            <!-- Left side: Title and optional subtitle/metadata -->
            <div class="flex-1 min-w-0">
                <h2 class="font-semibold text-xl text-body leading-tight">
                    {{ title }}
                </h2>

                <!-- Subtitle text (if provided as prop) -->
                <p v-if="subtitle" class="text-sm text-muted mt-1">
                    {{ subtitle }}
                </p>

                <!-- Metadata slot for custom content like event status, date, etc. -->
                <div v-if="$slots.metadata" class="mt-2 text-sm text-muted">
                    <slot name="metadata" />
                </div>
            </div>

            <!-- Right side: Action buttons -->
            <div v-if="$slots.actions" class="flex items-center space-x-2 ml-4 flex-shrink-0">
                <slot name="actions" />
            </div>
        </div>
    </div>
</template>

<script setup>
import Breadcrumb from '@/Components/Breadcrumb.vue';

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
