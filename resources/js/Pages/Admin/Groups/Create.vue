<script setup>
import GroupForm from './GroupForm.vue';

defineProps({
    events: Array,
});
</script>

<template>
    <GroupForm :events="events" />
</template>
