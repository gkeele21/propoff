<script setup>
import EventForm from './EventForm.vue';

defineProps({
    event: Object,
});
</script>

<template>
    <EventForm :event="event" />
</template>
