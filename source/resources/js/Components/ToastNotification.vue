<script setup>
    const props = defineProps({
        message: String,
        show: {
            type: Boolean,
            default: false,
        },
});
</script>

<template>
    <!-- Toast Notification -->
    <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0 translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-2"
    >
        <div
            v-if="props.show"
            class="fixed bottom-4 right-4 bg-propoff-green text-white px-6 py-3 rounded-lg shadow-lg flex items-center space-x-2 z-50"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ props.message }}</span>
        </div>
    </Transition>
</template>