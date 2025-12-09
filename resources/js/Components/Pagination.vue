<template>
    <div v-if="data.data && data.data.length > 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Showing <span class="font-medium">{{ data.from }}</span> to <span class="font-medium">{{ data.to }}</span> of <span class="font-medium">{{ data.total }}</span> {{ itemName }}
                </div>
                <div class="flex items-center space-x-2">
                    <Link
                        v-for="link in data.links"
                        :key="link.label"
                        :href="link.url"
                        :class="[
                            'px-3 py-2 text-sm font-medium rounded-md',
                            link.active
                                ? 'bg-propoff-blue text-white'
                                : link.url
                                    ? 'text-gray-700 hover:bg-gray-100'
                                    : 'text-gray-400 cursor-not-allowed'
                        ]"
                        :preserve-state="preserveState"
                        :preserve-scroll="preserveScroll"
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
