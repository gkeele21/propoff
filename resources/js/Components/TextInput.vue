<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="bg-surface-elevated border-border text-body placeholder-subtle focus:border-primary focus:ring-primary/50 rounded-md shadow-sm"
        v-model="model"
        ref="input"
    />
</template>
