<script setup>
import GroupForm from './GroupForm.vue';

defineProps({
    group: Object,
});
</script>

<template>
    <GroupForm :group="group" />
</template>
