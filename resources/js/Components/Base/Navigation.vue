<template>
    <nav class="bg-surface border-b border-warning/30 shadow-[0_4px_12px_-2px_rgb(var(--color-warning)/0.3)] sticky top-0 z-50">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo (Left) -->
                <div class="shrink-0 flex items-center">
                    <Logo size="sm" link-to="/" />
                </div>

                <!-- Desktop Navigation Links -->
                <div class="hidden sm:flex items-center space-x-8">
                    <template v-for="item in visibleItems" :key="item.route">
                        <Link
                            :href="route(item.route)"
                            :class="isActive(item) ? desktopActiveClass : desktopInactiveClass"
                        >
                            {{ item.label }}
                        </Link>
                    </template>
                </div>

                <!-- Right Side (User Name - handled by parent via slot) -->
                <div class="hidden sm:flex sm:items-center">
                    <slot name="user" />
                </div>

                <!-- Hamburger (Mobile) -->
                <div class="-me-2 flex items-center sm:hidden">
                    <button
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        class="inline-flex items-center justify-center p-2 rounded-md text-body hover:text-primary hover:bg-surface-overlay focus:outline-none focus:bg-surface-overlay focus:text-primary transition duration-150 ease-in-out"
                    >
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path
                                :class="{ hidden: mobileMenuOpen, 'inline-flex': !mobileMenuOpen }"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"
                            />
                            <path
                                :class="{ hidden: !mobileMenuOpen, 'inline-flex': mobileMenuOpen }"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation Menu -->
        <div :class="{ block: mobileMenuOpen, hidden: !mobileMenuOpen }" class="sm:hidden">
            <div class="pt-2 pb-3 space-y-1">
                <template v-for="item in visibleItems" :key="item.route">
                    <Link
                        :href="route(item.route)"
                        :class="isActive(item) ? mobileActiveClass : mobileInactiveClass"
                        @click="mobileMenuOpen = false"
                    >
                        {{ item.label }}
                    </Link>
                </template>
            </div>

            <!-- Mobile User Section (handled by parent via slot) -->
            <div v-if="$slots.mobileUser" class="pt-4 pb-1 border-t border-border">
                <slot name="mobileUser" :close="() => mobileMenuOpen = false" />
            </div>
        </div>
    </nav>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import Logo from '@/Components/Domain/Logo.vue';

const props = defineProps({
    user: { type: Object, default: null },
});

const mobileMenuOpen = ref(false);

// Role checks
const isManager = computed(() => props.user?.role === 'manager');
const isAdmin = computed(() => ['admin', 'manager'].includes(props.user?.role));

// Menu items configuration
// Order: Home, Admin links, History
const menuItems = [
    { label: 'Home', route: 'home', activeMatch: ['home', 'play.*', 'selector', 'groups.questions', 'groups.invitation', 'groups.members.*'] },
    { label: 'Events', route: 'admin.events.index', requiresAdmin: true },
    { label: 'Templates', route: 'admin.question-templates.index', requiresManager: true },
    { label: 'Users', route: 'admin.users.index', requiresManager: true },
    { label: 'Groups', route: 'admin.groups.index', requiresManager: true },
    { label: 'History', route: 'history' },
];

// Filter items based on user role
const visibleItems = computed(() => {
    return menuItems.filter(item => {
        if (item.requiresManager && !isManager.value) return false;
        if (item.requiresAdmin && !isAdmin.value) return false;
        if (item.requiresAuth && !props.user) return false;
        return true;
    });
});

// Check if a menu item is active
function isActive(item) {
    const matches = item.activeMatch || [item.route, `${item.route}.*`];
    return matches.some(pattern => route().current(pattern));
}

// Desktop link classes
const desktopActiveClass = 'inline-flex items-center px-1 pt-1 border-b-2 border-primary text-sm font-medium leading-5 text-body focus:outline-none focus:border-primary transition duration-150 ease-in-out';
const desktopInactiveClass = 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-muted hover:text-body hover:border-primary/50 focus:outline-none focus:text-body focus:border-primary/50 transition duration-150 ease-in-out';

// Mobile link classes
const mobileActiveClass = 'block w-full ps-3 pe-4 py-2 border-l-4 border-primary text-start text-base font-medium text-body bg-surface-overlay focus:outline-none focus:text-body focus:bg-surface-overlay focus:border-primary transition duration-150 ease-in-out';
const mobileInactiveClass = 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-muted hover:text-body hover:bg-surface-elevated hover:border-primary/50 focus:outline-none focus:text-body focus:bg-surface-elevated focus:border-primary/50 transition duration-150 ease-in-out';
</script>
