<script setup>
import { ref, computed, useSlots } from 'vue';
import Logo from '@/Components/Domain/Logo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import Breadcrumbs from '@/Components/Base/Breadcrumbs.vue';
import PreferencesModal from '@/Components/PreferencesModal.vue';
import { usePage } from '@inertiajs/vue3';
import { useTheme } from '@/composables/useTheme';

const props = defineProps({
    title: { type: String, default: '' },
    breadcrumbs: { type: Array, default: () => [] },
});

// Initialize theme from localStorage on app load
useTheme();

const showingNavigationDropdown = ref(false);
const showPreferences = ref(false);
const slots = useSlots();

const page = usePage();
const isManager = computed(() => page.props.auth.user?.role === 'manager');
const isAdmin = computed(() => ['admin', 'manager'].includes(page.props.auth.user?.role));

const showHeader = computed(() => props.title || props.breadcrumbs.length > 0 || slots.actions);
</script>

<template>
    <div>
        <div class="min-h-screen bg-bg">
            <nav class="bg-surface border-b border-border shadow-lg sticky top-0 z-50">
                <!-- Primary Navigation Menu -->
                <div class="px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center h-16">
                        <!-- Logo (Left) -->
                        <div class="shrink-0 flex items-center">
                            <Logo size="sm" link-to="/" />
                        </div>

                        <!-- Navigation Links (Center) -->
                        <div class="hidden sm:flex items-center space-x-8">
                            <NavLink
                                :href="route('dashboard')"
                                :active="route().current('dashboard')"
                            >
                                My Home
                            </NavLink>
                            <NavLink
                                v-if="isAdmin"
                                :href="route('admin.events.index')"
                                :active="route().current('admin.events.*')"
                            >
                                Events
                            </NavLink>
                            <NavLink
                                v-if="isManager"
                                :href="route('admin.question-templates.index')"
                                :active="route().current('admin.question-templates.*')"
                            >
                                Templates
                            </NavLink>
                            <NavLink
                                v-if="isManager"
                                :href="route('admin.users.index')"
                                :active="route().current('admin.users.*')"
                            >
                                Users
                            </NavLink>
                            <NavLink
                                v-if="isManager"
                                :href="route('admin.groups.index')"
                                :active="route().current('admin.groups.*')"
                            >
                                Groups
                            </NavLink>
                        </div>

                        <!-- Profile (Right) -->
                        <div class="hidden sm:flex sm:items-center">
                            <div class="relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-body hover:text-primary focus:outline-none transition ease-in-out duration-150"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="ms-2 -me-0.5 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <button
                                            @click="showPreferences = true"
                                            class="block w-full px-4 py-2 text-start text-sm leading-5 text-body hover:bg-surface-overlay focus:outline-none focus:bg-surface-overlay transition duration-150 ease-in-out"
                                        >
                                            Preferences
                                        </button>
                                        <DropdownLink :href="route('profile.edit')"> Profile </DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button">
                                            Log Out
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center p-2 rounded-md text-body hover:text-primary hover:bg-surface-overlay focus:outline-none focus:bg-surface-overlay focus:text-primary transition duration-150 ease-in-out"
                            >
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex': !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex': showingNavigationDropdown,
                                        }"
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

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }"
                    class="sm:hidden"
                >
                    <div class="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                            My Home
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="isAdmin"
                            :href="route('admin.events.index')"
                            :active="route().current('admin.events.*')"
                        >
                            Events
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="isManager"
                            :href="route('admin.question-templates.index')"
                            :active="route().current('admin.question-templates.*')"
                        >
                            Templates
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="isManager"
                            :href="route('admin.users.index')"
                            :active="route().current('admin.users.*')"
                        >
                            Users
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="isManager"
                            :href="route('admin.groups.index')"
                            :active="route().current('admin.groups.*')"
                        >
                            Groups
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-border">
                        <div class="px-4">
                            <div class="font-medium text-base text-body">
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="font-medium text-sm text-muted">{{ $page.props.auth.user.email }}</div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <button
                                @click="showPreferences = true; showingNavigationDropdown = false"
                                class="block w-full ps-3 pe-4 py-2 text-start text-base font-medium text-body hover:bg-surface-overlay focus:outline-none focus:bg-surface-overlay transition duration-150 ease-in-out"
                            >
                                Preferences
                            </button>
                            <ResponsiveNavLink :href="route('profile.edit')"> Profile </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header v-if="showHeader" class="bg-surface border-b border-border shadow sticky top-16 z-40">
                <div class="py-3 px-4 sm:px-6 lg:px-8">
                    <!-- Breadcrumbs -->
                    <Breadcrumbs
                        v-if="breadcrumbs.length > 0"
                        :items="breadcrumbs"
                        variant="light"
                        class="mb-2"
                    />
                    <!-- Title + Actions -->
                    <div class="flex justify-between items-center">
                        <h1 v-if="title" class="text-xl font-bold text-body">{{ title }}</h1>
                        <div v-if="slots.actions" class="flex items-center gap-3">
                            <slot name="actions" />
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>

        <!-- Preferences Modal -->
        <PreferencesModal :show="showPreferences" @close="showPreferences = false" />
    </div>
</template>
