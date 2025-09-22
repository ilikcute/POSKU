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

// Menu states
const isMasterMenuActive = computed(() => route().current("master.*"));
const isMasterMenuOpen = ref(isMasterMenuActive.value);

const isUserManagementMenuActive = computed(() => route().current("admin.*"));
const isUserManagementMenuOpen = ref(isUserManagementMenuActive.value);

const isTransactionMenuActive = computed(() => 
    route().current('sales.*|purchases.*|sales-returns.*|purchase-returns.*')
);
const isTransactionMenuOpen = ref(isTransactionMenuActive.value);

const isInventoryMenuActive = computed(() => route().current('stock.*|inventory.*'));
const isInventoryMenuOpen = ref(isInventoryMenuActive.value);

const isReportMenuActive = computed(() => route().current('reports.*'));
const isReportMenuOpen = ref(isReportMenuActive.value);

const isShiftsMenuActive = computed(() => route().current('shifts.*'));
const isShiftsMenuOpen = ref(isShiftsMenuActive.value);

// Permissions
const hasPermission = (permissions) => {
    const userPermissions = page.props.auth.user.permissions || [];
    const userRoles = page.props.auth.user.roles || [];
    if (userRoles.includes('Super Admin')) return true;
    if (Array.isArray(permissions)) {
        return permissions.some(p => userPermissions.includes(p));
    }
    return userPermissions.includes(permissions);
};

const canAccessMasterMenu = computed(() => hasPermission(['view_products', 'view_promotions', 'view_categories', 'view_divisions', 'view_racks', 'view_suppliers', 'view_members', 'view_salesmen']));
const canAccessUserManagement = computed(() => hasPermission(['view_users', 'manage_roles']));
const canAccessTransactionMenu = computed(() => hasPermission(['create_sales', 'view_purchases', 'create_sales_returns', 'create_purchase_returns']));
const canAccessInventoryMenu = computed(() => hasPermission(['view_stock', 'stock_opname']));
const canAccessReportMenu = computed(() => hasPermission(['view_reports']));
const canAccessShiftsMenu = computed(() => hasPermission(['view_shifts', 'open_shifts', 'close_shifts']));

const menuItems = [
    { can: canAccessShiftsMenu, isOpen: isShiftsMenuOpen, isActive: isShiftsMenuActive, toggle: () => isShiftsMenuOpen.value = !isShiftsMenuOpen.value, name: 'Shifts', icon: '<svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-12v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2h12a2 2 0 012 2z" /></svg>', sub: [
        { route: 'shifts.open.form', label: 'Buka Shift' },
        { route: 'shifts.close.form', label: 'Tutup Shift' },
    ]},
    { can: canAccessUserManagement, isOpen: isUserManagementMenuOpen, isActive: isUserManagementMenuActive, toggle: () => isUserManagementMenuOpen.value = !isUserManagementMenuOpen.value, name: 'User Management', icon: '<svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>', sub: [
        { route: 'admin.users.index', label: 'Manajemen User' },
        { route: 'admin.roles.index', label: 'Role & Permission', can: hasPermission('manage_roles') },
    ]},
    { can: canAccessMasterMenu, isOpen: isMasterMenuOpen, isActive: isMasterMenuActive, toggle: () => isMasterMenuOpen.value = !isMasterMenuOpen.value, name: 'Setting Master', icon: '<svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12a7.5 7.5 0 0015 0m-15 0a7.5 7.5 0 1115 0m-15 0H3m16.5 0H21m-1.5 0H12m-8.457 3.077l1.41-.513m14.095-5.13l-1.41-.513M5.106 17.785l1.153-.964M16.745 7.86l1.154-.964m-14.09 5.13l1.41.513M18.894 6.215l-1.153.964m-5.13-1.41l-.513 1.41m5.643 5.643l-.513 1.41M12 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>', sub: [
        { route: 'master.products.index', label: 'Produk', can: hasPermission('view_products') },
        { route: 'promotions.index', label: 'Promosi', can: hasPermission('view_promotions') },
        { route: 'master.categories.index', label: 'Kategori', can: hasPermission('view_categories') },
        { route: 'master.divisions.index', label: 'Divisi', can: hasPermission('view_divisions') },
        { route: 'master.racks.index', label: 'Rak', can: hasPermission('view_racks') },
        { route: 'master.suppliers.index', label: 'Supplier', can: hasPermission('view_suppliers') },
        { route: 'master.members.index', label: 'Master Member', can: hasPermission('view_members') },
        { route: 'master.salesmen.index', label: 'Master Salesman', can: hasPermission('view_salesmen') },
        { route: 'store.profile.edit', label: 'Profil Toko' },
    ]},
    { can: canAccessTransactionMenu, isOpen: isTransactionMenuOpen, isActive: isTransactionMenuActive, toggle: () => isTransactionMenuOpen.value = !isTransactionMenuOpen.value, name: 'Transaksi', icon: '<svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2.25l1.5 12h12.75l1.5-12H21M6.75 3l1.5 12m0 0l2.25 3.75M6.75 15h6.75m0 0l-2.25 3.75m2.25-3.75h3.75l2.25 3.75M9 18.75h6" /></svg>', sub: [
        { route: 'sales.create', label: 'Penjualan (POS)', can: hasPermission('create_sales') },
        { route: 'purchases.create', label: 'Pembelian', can: hasPermission('create_purchases') },
        { route: 'sales.index', label: 'Daftar Penjualan', can: hasPermission('view_sales') },
        { route: 'purchases.index', label: 'Daftar Pembelian', can: hasPermission('view_purchases') },
        { route: 'sales-returns.index', label: 'Retur Penjualan', can: hasPermission('view_sales_returns') },
        { route: 'purchase-returns.index', label: 'Retur Pembelian', can: hasPermission('view_purchase_returns') },
    ]},
    { can: canAccessInventoryMenu, isOpen: isInventoryMenuOpen, isActive: isInventoryMenuActive, toggle: () => isInventoryMenuOpen.value = !isInventoryMenuOpen.value, name: 'Inventory', icon: '<svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>', sub: [
        { route: 'stock-entries.index', label: 'Stok Masuk', can: hasPermission('view_stock_entries') },
        { route: 'stock-adjustments.index', label: 'Penyesuaian Stok', can: hasPermission('view_stock_adjustments') },
        { route: 'stock.opname', label: 'Opname Stok', can: hasPermission('view_stock_opnames') },
    ]},
    { can: canAccessReportMenu, isOpen: isReportMenuOpen, isActive: isReportMenuActive, toggle: () => isReportMenuOpen.value = !isReportMenuOpen.value, name: 'Laporan', icon: '<svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m1-3l1 3m-9-3v4.5A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75v-4.5m-15 0h15" /></svg>', sub: [
        { route: 'reports.sales', label: 'Laporan Penjualan', can: hasPermission('view_sales_report') },
        { route: 'reports.purchases', label: 'Laporan Pembelian', can: hasPermission('view_purchases_report') },
        { route: 'reports.stock', label: 'Laporan Inventori', can: hasPermission('view_inventory_report') },
    ]},
];

</script>

<template>
    <div class="h-screen flex overflow-hidden bg-gray-50 font-sans text-gray-700">
        <!-- Sidebar -->
        <aside :class="[
            sidebarOpen ? 'translate-x-0' : '-translate-x-full',
            isSidebarMinimized ? 'w-20' : 'w-64',
        ]"
            class="fixed inset-y-0 left-0 z-50 bg-white transition-all duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 flex-shrink-0 border-r border-gray-200/80">
            <div class="flex flex-col h-full">
                <!-- Sidebar Header -->
                <div class="flex items-center justify-center h-16 border-b border-gray-200/80 flex-shrink-0 px-4">
                    <Link :href="route('dashboard')" class="flex items-center overflow-hidden">
                        <img v-if="store && store.logo_path" :src="`/storage/${store.logo_path}`" :alt="store.name"
                            class="h-9 w-9 rounded-full object-contain" />
                        <span :class="{ 'lg:hidden': isSidebarMinimized }"
                            class="ml-3 font-bold text-lg text-gray-800 whitespace-nowrap transition-opacity duration-200">
                            {{ store && store.name ? store.name : 'POSKU' }}
                        </span>
                    </Link>
                </div>

                <!-- Navigation Menu -->
                <nav class="mt-4 px-2 space-y-1 flex-grow overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100">
                    
                    <NavLink :href="route('dashboard')" :active="route().current('dashboard')" :is-sidebar-link="true">
                        <template #icon>
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h7.5" /></svg>
                        </template>
                        <span :class="{ 'lg:hidden': isSidebarMinimized }" class="ml-3">Dashboard</span>
                    </NavLink>

                    <!-- Menu Groups -->
                    <div v-for="menu in menuItems" :key="menu.name">
                        <div v-if="menu.can">
                            <button @click="menu.toggle"
                                class="w-full flex items-center p-2 text-gray-500 hover:bg-primary/10 hover:text-primary rounded-md transition-colors duration-200"
                                :class="{ 'bg-primary/10 text-primary font-semibold': menu.isActive }">
                                <span v-html="menu.icon"></span>
                                <span :class="{ 'lg:hidden': isSidebarMinimized }" class="ml-3 whitespace-nowrap">{{ menu.name }}</span>
                                <svg :class="{ 'rotate-90': menu.isOpen.value, 'lg:hidden': isSidebarMinimized }" class="h-5 w-5 ml-auto transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg>
                            </button>
                            <div v-if="menu.isOpen.value" :class="{ 'lg:pl-6': !isSidebarMinimized }" class="mt-1 space-y-1">
                                <template v-for="item in menu.sub" :key="item.route">
                                    <NavLink v-if="item.can !== false" :href="route(item.route)" :active="route().current(item.route)" :is-sidebar-link="true">
                                        <span :class="{ 'lg:hidden': isSidebarMinimized }" class="ml-9 whitespace-nowrap transition-opacity duration-200">{{ item.label }}</span>
                                    </NavLink>
                                </template>
                            </div>
                        </div>
                    </div>
                </nav>

                <!-- Sidebar Toggle -->
                <div class="flex-shrink-0 border-t border-gray-200/80">
                    <button @click="isSidebarMinimized = !isSidebarMinimized"
                        class="hidden lg:flex items-center justify-center w-full h-12 text-gray-500 hover:bg-gray-100">
                        <svg v-if="!isSidebarMinimized" class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" /></svg>
                        <svg v-else class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7" /></svg>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 flex flex-col">
            <!-- Header -->
            <header class="flex justify-between items-center h-16 bg-white/50 backdrop-blur-sm border-b border-gray-200/80 px-4 sm:px-6 lg:px-8 flex-shrink-0 z-10">
                <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 focus:outline-none lg:hidden">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                </button>
                <div class="flex-1">
                    <div v-if="$slots.header" class="font-semibold text-xl text-gray-800 leading-tight">
                        <slot name="header" />
                    </div>
                </div>
                <div class="hidden sm:ms-6 sm:flex sm:items-center">
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <span class="inline-flex rounded-md">
                                <button type="button"
                                    class="inline-flex items-center rounded-md border border-transparent bg-transparent px-3 py-2 text-sm font-medium leading-4 text-gray-500 hover:text-gray-900 focus:outline-none transition duration-150 ease-in-out">
                                    {{ $page.props.auth.user.name }}
                                    <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                </button>
                            </span>
                        </template>
                        <template #content>
                            <DropdownLink :href="route('profile.edit')"> Profile </DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button"> Log Out </DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </header>
            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6">
                <slot />
            </main>
        </div>
    </div>
</template>