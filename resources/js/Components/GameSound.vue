<script setup>
import { watch } from 'vue';

const props = defineProps({
    trigger: {
        type: [Number, Boolean, String],
        default: 0
    },
    soundType: {
        type: String,
        default: 'success', // 'success' or 'buzzer'
        validator: (value) => ['success', 'buzzer'].includes(value)
    }
});

// Sound configuration presets
const soundConfigs = {
    success: {
        type: 'sine',
        startFreq: 800,
        endFreq: 600,
        gain: 0.3,
        duration: 0.3,
        rampDuration: 0.1,
    },
    buzzer: {
        type: 'sawtooth',
        startFreq: 150,
        endFreq: 150, // No frequency change for buzzer
        gain: 0.4,
        duration: 0.5,
        rampDuration: null, // No frequency ramp
    }
};

const playSound = () => {
    const config = soundConfigs[props.soundType];
    if (!config) return;

    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    const oscillator = audioContext.createOscillator();
    const gainNode = audioContext.createGain();

    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);

    // Configure oscillator
    oscillator.type = config.type;
    oscillator.frequency.setValueAtTime(config.startFreq, audioContext.currentTime);

    // Add frequency ramp if configured
    if (config.rampDuration) {
        oscillator.frequency.exponentialRampToValueAtTime(
            config.endFreq,
            audioContext.currentTime + config.rampDuration
        );
    }

    // Configure gain envelope
    gainNode.gain.setValueAtTime(config.gain, audioContext.currentTime);
    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + config.duration);

    // Play sound
    oscillator.start(audioContext.currentTime);
    oscillator.stop(audioContext.currentTime + config.duration);
};

// Watch the trigger prop and play sound when it changes
watch(() => props.trigger, (newVal, oldVal) => {
    if (newVal !== oldVal && newVal) {
        playSound();
    }
});
</script>

<template>
    <!-- This component doesn't render anything, it just plays sounds -->
</template>
