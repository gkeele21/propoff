<template>
    <nav class="flex items-center space-x-2 text-sm text-gray-600 mb-3">
        <template v-for="(crumb, index) in crumbs" :key="index">
            <!-- Separator (not shown for first item) -->
            <svg
                v-if="index > 0"
                class="w-4 h-4 text-gray-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 5l7 7-7 7"
                />
            </svg>

            <!-- Breadcrumb item -->
            <Link
                v-if="crumb.href && index < crumbs.length - 1"
                :href="crumb.href"
                class="hover:text-gray-900 transition-colors duration-150"
            >
                {{ crumb.label }}
            </Link>
            <span
                v-else
                class="font-medium"
                :class="index === crumbs.length - 1 ? 'text-gray-900' : 'text-gray-600'"
            >
                {{ crumb.label }}
            </span>
        </template>
    </nav>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    crumbs: {
        type: Array,
        required: true,
        validator: (crumbs) => {
            return crumbs.every(
                (crumb) =>
                    typeof crumb.label === 'string' &&
                    (crumb.href === undefined || typeof crumb.href === 'string')
            );
        },
    },
});
</script>
