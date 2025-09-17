<script setup>
import { ref, computed } from "vue";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import NavLink from "@/Components/NavLink.vue";
import { Link, usePage } from "@inertiajs/vue3";

const page = usePage();
const store = page.props.store;
const sidebarOpen = ref(false);
const isSidebarMinimized = ref(false);
const isMasterMenuActive = computed(() => route().current("master.*"));
const isMasterMenuOpen = ref(isMasterMenuActive.value);

// User Management menu state
const isUserManagementMenuActive = computed(() => route().current("admin.*"));
const isUserManagementMenuOpen = ref(isUserManagementMenuActive.value);

// Helper function untuk cek permission
const hasPermission = (permissions) => {
    const userPermissions = page.props.auth.user.permissions || [];
    const userRoles = page.props.auth.user.roles || [];
    
    // Super Admin has access to everything
    if (userRoles.includes('Super Admin')) {
        return true;
    }
    
    // Check if user has any of the required permissions
    if (Array.isArray(permissions)) {
        return permissions.some(permission => userPermissions.includes(permission));
    }
    
    return userPermissions.includes(permissions);
};

// Check if user can access any master menu
const canAccessMasterMenu = computed(() => {
    return hasPermission([
        'view_products', 'view_categories', 'view_divisions', 
        'view_racks', 'view_suppliers', 'view_members', 'view_salesmen'
    ]);
});

// Check if user can access user management
const canAccessUserManagement = computed(() => {
    return hasPermission(['view_users', 'manage_roles']);
});
</script>

<template>
    <div class="h-screen flex overflow-hidden bg-gray-100">
        <aside
            :class="[
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
                isSidebarMinimized ? 'w-20' : 'w-64',
            ]"
            class="fixed inset-y-0 left-0 z-50 bg-gray-800 text-white transition-all duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 flex-shrink-0"
        >
            <div class="flex flex-col h-full">
                <div
                    class="flex items-center justify-center h-16 border-b border-gray-700 flex-shrink-0"
                >
                    <Link
                        :href="route('dashboard')"
                        class="flex items-center overflow-hidden"
                    >
                        <!-- Di bagian header/navbar -->
                        <div class="flex items-center">
                            <img 
                                v-if="store && store.logo_path" 
                                :src="`/storage/${store.logo_path}`" 
                                :alt="store.name"
                                class="h-8 w-8 rounded object-contain mr-2"
                            />
                            <span class="font-semibold text-lg">
                                {{ store && store.name ? store.name : 'POSKU' }}
                            </span>
                        </div>
                    </Link>
                </div>
                <nav class="mt-4 px-2 space-y-2 flex-grow">
                    <!-- Dashboard Menu -->
                    <NavLink
                        v-if="hasPermission('view_dashboard')"
                        :href="route('dashboard')"
                        :active="route().current('dashboard')"
                        :is-sidebar-link="true"
                    >
                        <template #icon>
                            <svg
                                class="h-6 w-6 flex-shrink-0"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h7.5"
                                />
                            </svg>
                        </template>
                        <span
                            :class="{ 'lg:hidden': isSidebarMinimized }"
                            class="ml-3 whitespace-nowrap transition-opacity duration-200"
                            >Dashboard</span
                        >
                    </NavLink>

                    <!-- User Management Menu -->
                    <div v-if="canAccessUserManagement">
                        <button
                            @click="isUserManagementMenuOpen = !isUserManagementMenuOpen"
                            class="w-full flex items-center p-2 text-sm rounded-md transition-colors duration-200"
                            :class="
                                isUserManagementMenuActive
                                    ? 'text-white bg-gray-700'
                                    : 'text-gray-300 hover:bg-gray-700 hover:text-white'
                            "
                        >
                            <svg
                                class="h-6 w-6 flex-shrink-0"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"
                                />
                            </svg>

                            <span
                                :class="{ 'lg:hidden': isSidebarMinimized }"
                                class="ml-3 whitespace-nowrap transition-opacity duration-200"
                                >User Management</span
                            >

                            <svg
                                :class="{
                                    'rotate-90': isUserManagementMenuOpen,
                                    'lg:hidden': isSidebarMinimized,
                                }"
                                class="h-5 w-5 ml-auto transition-transform duration-200 flex-shrink-0"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </button>

                        <div
                            v-if="isUserManagementMenuOpen"
                            :class="{ 'lg:pl-6': !isSidebarMinimized }"
                            class="mt-2 space-y-2 transition-all duration-300"
                        >
                            <NavLink
                                :href="route('admin.users.index')"
                                :active="route().current('admin.users.*')"
                                :is-sidebar-link="true"
                            >
                                <template #icon>
                                    <svg
                                        class="h-6 w-6 flex-shrink-0"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke-width="1.5"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766z"
                                        />
                                    </svg>
                                </template>
                                <span
                                    :class="{ 'lg:hidden': isSidebarMinimized }"
                                    class="ml-3 whitespace-nowrap transition-opacity duration-200"
                                    >Manajemen User</span
                                >
                            </NavLink>
                            <NavLink
                                v-if="hasPermission('manage_roles')"
                                :href="route('admin.roles.index')"
                                :active="route().current('admin.roles.*')"
                                :is-sidebar-link="true"
                            >
                                <template #icon>
                                    <svg class="h-6 w-6 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </template>
                                <span :class="{ 'lg:hidden': isSidebarMinimized }" class="ml-3 whitespace-nowrap transition-opacity duration-200">Role & Permission</span>
                            </NavLink>
                        </div>
                    </div>

                    <!-- Master Data Menu -->
                    <div v-if="canAccessMasterMenu">
                        <button
                            @click="isMasterMenuOpen = !isMasterMenuOpen"
                            class="w-full flex items-center p-2 text-sm rounded-md transition-colors duration-200"
                            :class="
                                isMasterMenuActive
                                    ? 'text-white bg-gray-700'
                                    : 'text-gray-300 hover:bg-gray-700 hover:text-white'
                            "
                        >
                            <svg
                                class="h-6 w-6 flex-shrink-0"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M4.5 12a7.5 7.5 0 0015 0m-15 0a7.5 7.5 0 1115 0m-15 0H3m16.5 0H21m-1.5 0H12m-8.457 3.077l1.41-.513m14.095-5.13l-1.41-.513M5.106 17.785l1.153-.964M16.745 7.86l1.154-.964m-14.09 5.13l1.41.513M18.894 6.215l-1.153.964m-5.13-1.41l-.513 1.41m5.643 5.643l-.513 1.41M12 21.75c-2.676 0-5.216-.584-7.499-1.632z"
                                />
                            </svg>

                            <span
                                :class="{ 'lg:hidden': isSidebarMinimized }"
                                class="ml-3 whitespace-nowrap transition-opacity duration-200"
                                >Setting Master</span
                            >

                            <svg
                                :class="{
                                    'rotate-90': isMasterMenuOpen,
                                    'lg:hidden': isSidebarMinimized,
                                }"
                                class="h-5 w-5 ml-auto transition-transform duration-200 flex-shrink-0"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </button>

                        <div
                            v-if="isMasterMenuOpen"
                            :class="{ 'lg:pl-6': !isSidebarMinimized }"
                            class="mt-2 space-y-2 transition-all duration-300"
                        >
                            <NavLink
                                v-if="hasPermission('view_products')"
                                :href="route('master.products.index')"
                                :active="route().current('master.products.index')"
                                :is-sidebar-link="true"
                            >
                                <template #icon>
                                    <svg class="h-6 w-6 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                </template>
                                <span :class="{ 'lg:hidden': isSidebarMinimized }" class="ml-3 whitespace-nowrap transition-opacity duration-200">Produk</span>
                            </NavLink>
                            <NavLink
                                v-if="hasPermission('view_products')"
                                :href="route('promotions.index')"
                                :active="route().current('promotions.index')"
                                :is-sidebar-link="true"
                            >
                                <template #icon>
                                    <svg class="h-6 w-6 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zm0 10c-4.418 0-8-3.582-8-8s3.582-8 8-8 8 3.582 8 8-3.582 8-8 8z" />
                                    </svg>
                                </template>
                                <span :class="{ 'lg:hidden': isSidebarMinimized }" class="ml-3 whitespace-nowrap transition-opacity duration-200">Promosi</span>
                            </NavLink>
                            <NavLink
                                v-if="hasPermission('view_categories')"
                                :href="route('master.categories.index')"
                                :active="
                                    route().current('master.categories.index')
                                "
                                :is-sidebar-link="true"
                            >
                                <template #icon>
                                    <svg
                                        class="h-6 w-6 flex-shrink-0"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke-width="1.5"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M6 6h.008v.008H6V6z"
                                        />
                                    </svg>
                                </template>
                                <span
                                    :class="{ 'lg:hidden': isSidebarMinimized }"
                                    class="ml-3 whitespace-nowrap transition-opacity duration-200"
                                    >Kategori</span
                                >
                            </NavLink>
                            <NavLink
                                v-if="hasPermission('view_divisions')"
                                :href="route('master.divisions.index')"
                                :active="
                                    route().current('master.divisions.index')
                                "
                                :is-sidebar-link="true"
                            >
                                <template #icon>
                                    <svg
                                        class="h-6 w-6 flex-shrink-0"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke-width="1.5"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"
                                        />
                                    </svg>
                                </template>
                                <span
                                    :class="{ 'lg:hidden': isSidebarMinimized }"
                                    class="ml-3 whitespace-nowrap transition-opacity duration-200"
                                    >Divisi</span
                                >
                            </NavLink>
                            <NavLink
                                v-if="hasPermission('view_racks')"
                                :href="route('master.racks.index')"
                                :active="route().current('master.racks.index')"
                                :is-sidebar-link="true"
                            >
                                <template #icon>
                                    <svg
                                        class="h-6 w-6 flex-shrink-0"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke-width="1.5"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"
                                        />
                                    </svg>
                                </template>
                                <span
                                    :class="{ 'lg:hidden': isSidebarMinimized }"
                                    class="ml-3 whitespace-nowrap transition-opacity duration-200"
                                    >Rak</span
                                >
                            </NavLink>
                            <NavLink
                                v-if="hasPermission('view_suppliers')"
                                :href="route('master.suppliers.index')"
                                :active="
                                    route().current('master.suppliers.index')
                                "
                                :is-sidebar-link="true"
                            >
                                <template #icon>
                                    <svg
                                        class="h-6 w-6 flex-shrink-0"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke-width="1.5"
                                        stroke="currentColor"
                                    >
                                        <path
                                            d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 2h8a1 1 0 001-1z"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M13 16h2m-2 2h2m-2-2v2m2-2h-2m-2-2h2m-2-2h2"
                                        />
                                    </svg>
                                </template>
                                <span
                                    :class="{ 'lg:hidden': isSidebarMinimized }"
                                    class="ml-3 whitespace-nowrap transition-opacity duration-200"
                                    >Supplier</span
                                >
                            </NavLink>
                            <NavLink
                                v-if="hasPermission('view_members')"
                                :href="route('master.members.index')"
                                :active="
                                    route().current('master.members.index')
                                "
                                :is-sidebar-link="true"
                            >
                                <template #icon>
                                    <svg
                                        class="h-6 w-6 flex-shrink-0"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke-width="1.5"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m-7.5-2.962a3.75 3.75 0 100-7.5 3.75 3.75 0 000 7.5zM3.75 18.72v-.507c0-1.279.8-2.45 1.9-2.882A4.5 4.5 0 019 15.75v.507c0 1.279-.8 2.45-1.9 2.882A4.5 4.5 0 013 18.72z"
                                        />
                                    </svg>
                                </template>
                                <span
                                    :class="{ 'lg:hidden': isSidebarMinimized }"
                                    class="ml-3 whitespace-nowrap transition-opacity duration-200"
                                    >Master Member</span
                                >
                            </NavLink>
                            <NavLink
                                v-if="hasPermission('view_salesmen')"
                                :href="route('master.salesmen.index')"
                                :active="
                                    route().current('master.salesmen.index')
                                "
                                :is-sidebar-link="true"
                            >
                                <template #icon>
                                    <svg
                                        class="h-6 w-6 flex-shrink-0"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke-width="1.5"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"
                                        />
                                    </svg>
                                </template>
                                <span
                                    :class="{ 'lg:hidden': isSidebarMinimized }"
                                    class="ml-3 whitespace-nowrap transition-opacity duration-200"
                                    >Master Salesman</span
                                >
                            </NavLink>
                            <NavLink :href="route('store.profile.edit')" :active="route().current('store.profile.edit')" :is-sidebar-link="true">
                                <template #icon>
                                    <svg class="h-6 w-6 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.25m11.25 0h8.25a2.25 2.25 0 002.25-2.25V5.25A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25v13.5A2.25 2.25 0 005.25 21h8.25" /></svg>
                                </template>
                                <span :class="{'lg:hidden': isSidebarMinimized}" class="ml-3 whitespace-nowrap transition-opacity duration-200">Profil Toko</span>
                            </NavLink>
                        </div>
                    </div>
                </nav>
                <div class="flex-shrink-0 border-t border-gray-700">
                    <button
                        @click="isSidebarMinimized = !isSidebarMinimized"
                        class="hidden lg:flex items-center justify-center w-full h-12 text-gray-300 hover:bg-gray-700 hover:text-white"
                    >
                        <svg
                            v-if="!isSidebarMinimized"
                            class="h-6 w-6"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M11 19l-7-7 7-7m8 14l-7-7 7-7"
                            />
                        </svg>
                        <svg
                            v-else
                            class="h-6 w-6"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M13 5l7 7-7 7M5 5l7 7-7 7"
                            />
                        </svg>
                    </button>
                </div>
            </div>
        </aside>
        <div class="flex-1 flex flex-col">
            <header
                class="flex justify-between items-center h-16 bg-white shadow-sm px-4 sm:px-6 lg:px-8 flex-shrink-0 z-10"
            >
                <button
                    @click="sidebarOpen = !sidebarOpen"
                    class="text-gray-500 focus:outline-none lg:hidden"
                >
                    <svg
                        class="h-6 w-6"
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M4 6h16M4 12h16M4 18h16"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        ></path>
                    </svg>
                </button>
                <div class="flex-1">
                    <div
                        v-if="$slots.header"
                        class="font-semibold text-xl text-gray-800 leading-tight"
                    >
                        <slot name="header" />
                    </div>
                </div>
                <div class="hidden sm:ms-6 sm:flex sm:items-center">
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <span class="inline-flex rounded-md">
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none"
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
                            <DropdownLink :href="route('profile.edit')">
                                Profile
                            </DropdownLink>
                            <DropdownLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Log Out
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </header>
            <main class="flex-1 overflow-y-auto p-6">
                <slot />
            </main>
        </div>
    </div>
</template>