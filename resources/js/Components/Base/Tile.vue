<template>
    <div class="flex flex-col items-center">
        <div :class="[variantClasses[variant].container, props.class]" class="rounded-xl shadow-xl">
            <Link :href="isLocked ? '' : route(link.path, link.params)" :class="{'cursor-default':isLocked}">
                <div :class="isLocked ? 'opacity-25' : ''" class="w-32 py-4 sm:w-40 lg:w-48 h-32 sm:h-40 lg:h-48 flex flex-col justify-around items-center leading-tight">
                    <h2 :class="variantClasses[variant].heading" class="font-semibold lg:text-xl text-center">{{heading}}</h2>
                    <i v-if="icon" :class="[icon, variantClasses[variant].icon]" class="fa-6x"></i>
                    <img v-if="image" :src="image"/>
                </div>
            </Link>
        </div>
        <div v-if="isLocked" class="relative -top-6">
            <i class="fa-solid fa-lock fa-2x bg-black text-white p-2 rounded-full" :title="isLockedMsg"></i>
        </div>
    </div>
</template>

<script setup>

    import { Link } from '@inertiajs/vue3';

    const props = defineProps({
        heading: String,
        icon: String,
        image: String,
        link: Object,
        isLocked: { type: Boolean, default: false },
        isLockedMsg: { type: String, default: '' },
        class: { type: String, default: '' },
        variant: { type: String, default: 'dark' }, // 'light', 'dark'
    });

    const variantClasses = {
        light: {
            container: 'bg-white',
            heading: 'text-primary',
            icon: 'text-primary',
        },
        dark: {
            container: 'bg-black',
            heading: 'text-white',
            icon: 'text-warning',
        },
    };

</script>