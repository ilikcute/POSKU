<template>
    <div class="p-6">

        <Head title="Stock Opname" />

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Stock Opname</h1>
            <div class="space-x-2">
                <Link :href="route('stock.index')" class="bg-gray-500 hover:bg-gray-700 text-white px-4 py-2 rounded">
                Back to Stock
                </Link>
                <button @click="startOpname" class="bg-blue-500 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    Start Opname
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form @submit.prevent="filterStocks">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select v-model="filters.category_id"
                            class="w-full border border-gray-300 rounded-md px-3 py-2">
                            <option value="">All Categories</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
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

        <!-- Stock Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            System Stock</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Physical Count</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Difference</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="stock in stocks.data" :key="stock.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ stock.product?.name || 'Unknown Product'
                                }}</div>
                            <div class="text-sm text-gray-500">{{ stock.product?.product_code || '' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ stock.product?.category?.name || 'No Category' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ stock.quantity }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="number" v-model="stock.physical_count" @input="calculateDifference(stock)"
                                class="w-20 border border-gray-300 rounded-md px-2 py-1 text-sm">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ stock.difference || 0 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                :class="stock.difference === 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                {{ stock.difference === 0 ? 'Match' : 'Discrepancy' }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <Pagination v-if="stocks.links" :links="stocks.links" />

        <!-- Save Button -->
        <div class="mt-6 flex justify-end">
            <button @click="saveOpname" class="bg-green-500 hover:bg-green-700 text-white px-6 py-2 rounded">
                Save Opname Results
            </button>
        </div>
    </div>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    stocks: Object,
    categories: Array,
    filters: Object,
});

const filters = ref({
    category_id: props.filters.category_id || '',
    search: props.filters.search || '',
});

const filterStocks = () => {
    router.get(route('stock.opname'), filters.value, { preserveState: true, replace: true });
};

const calculateDifference = (stock) => {
    stock.difference = (stock.physical_count || 0) - stock.quantity;
};

const startOpname = () => {
    // Reset all physical counts
    stocks.value.data.forEach(stock => {
        stock.physical_count = null;
        stock.difference = null;
    });
};

const saveOpname = () => {
    // Here you would send the opname data to the server
    alert('Opname functionality not fully implemented yet. This would save the physical counts and adjust stock accordingly.');
};
</script>
