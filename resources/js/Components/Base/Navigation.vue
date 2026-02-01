<template>
    <nav class='flex font-semibold uppercase w-full'
        :class="menuType == 'Mobile'
            ? 'flex-col min-h-screen bg-black text-gray-300 px-4 py-2'
            : 'hidden xl:flex xl:justify-evenly xl:text-sm text-primary'">

        <template v-for="menu in menus">
            <Link v-if="menu.title == adminMenuName ? $page.props?.auth?.user?.isAdmin == true : menu.title == orateknicianMenuName ? $page.props?.auth?.user?.hasOrateknicianAccess == true : menu.mustbeLoggedIn ? menu.mustbeLoggedIn == true && $page.props.auth?.user?.id > 0 : true"
                :href="route(menu.route)"
                :class="menuType == 'Mobile' ? 'border-b border-gray-600 p-2 hover:text-white' : 'hover:text-primary'"
                class="transition-colors">
                {{ menu.title }}
            </Link>
        </template>
    </nav>
</template>

<script setup>
    import { Link } from '@inertiajs/vue3';

    defineProps({
        menuType: { type: String, default: "MAIN" },
    });

    const adminMenuName = 'Admin';
    const orateknicianMenuName = 'Orateknician';

    const menus = [
        { title: 'Dashboard', route: 'dashboard', mustbeLoggedIn: true },
        { title: 'Analytics', route: 'analytics', mustbeLoggedIn: true },
        { title: adminMenuName, route: 'admin.index' },
        { title: orateknicianMenuName, route: 'orateknician.index' },
    ];
</script>
