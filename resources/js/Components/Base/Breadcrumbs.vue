<template>
    <nav class="flex items-center gap-2 text-sm" aria-label="Breadcrumb">
        <!-- Home link -->
        <Link
            v-if="showHome"
            :href="homeHref"
            :class="variant === 'light' ? 'text-subtle hover:text-primary' : 'text-gray-300 hover:text-white'"
            class="transition-colors"
        >
            <Icon name="home" size="sm" />
            <span class="sr-only">Home</span>
        </Link>

        <template v-for="(item, index) in items" :key="index">
            <!-- Separator -->
            <i v-if="index > 0 || showHome" :class="variant === 'light' ? 'text-muted' : 'text-gray-400'" class="fa-solid fa-caret-right"></i>

            <!-- Breadcrumb item -->
            <Link
                v-if="item.route || item.href"
                :href="item.href || route(item.route, item.params)"
                :class="variant === 'light' ? 'text-subtle hover:text-primary' : 'text-gray-300 hover:text-white'"
                class="transition-colors"
            >
                {{ item.text }}
            </Link>
            <span v-else :class="variant === 'light' ? 'text-body font-medium' : 'text-white font-medium'">
                {{ item.text }}
            </span>
        </template>
    </nav>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import Icon from '@/Components/Base/Icon.vue';

defineProps({
    items: { type: Array, required: true },
    showHome: { type: Boolean, default: false },
    homeHref: { type: String, default: '/' },
    variant: { type: String, default: 'dark' }, // 'dark' for dark backgrounds, 'light' for light backgrounds
});
</script>
