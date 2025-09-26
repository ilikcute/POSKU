<template>
    <div class="p-6">

        <Head title="Stock Movements" />

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Stock Movements</h1>
            <Link :href="route('stock.index')" class="bg-gray-500 hover:bg-gray-700 text-white px-4 py-2 rounded">
            Back to Stock
            </Link>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form @submit.prevent="filterMovements">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input type="date" v-model="filters.start_date"
                            class="w-full border border-gray-300 rounded-md px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <input type="date" v-model="filters.end_date"
                            class="w-full border border-gray-300 rounded-md px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input type="text" v-model="filters.search" placeholder="Search products..."
                            class="w-full border border-gray-300 rounded-md px-3 py-2">
                    </div>
                    <div class="flex items-end">
                        <button type="submit"
                            class="bg-blue-500 hover:bg-blue-700 text-white px-4 py-2 rounded-md">Filter</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Movements Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Reason</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            User</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="movement in movements.data" :key="movement.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ new Date(movement.created_at).toLocaleDateString() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ movement.product?.name || '' }}</div>
                            <div class="text-sm text-gray-500">{{ movement.product?.product_code || '' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                :class="movement.type === 'in' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                {{ movement.type === 'in' ? 'Stock In' : 'Stock Out' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ movement.quantity }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ movement.reason }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ movement.user?.name || 'System' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <Pagination v-if="movements.links" :links="movements.links" />
    </div>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    movements: Object,
    filters: Object,
});

const filters = ref({
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
    search: props.filters.search || '',
});

const filterMovements = () => {
    router.get(route('stock.movements'), filters.value, { preserveState: true, replace: true });
};
</script>
