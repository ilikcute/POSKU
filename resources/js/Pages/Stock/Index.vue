<template>
    <div class="p-6">

        <Head title="Stock Management" />
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Stock Management</h1>
            <div class="space-x-2">
                <Link :href="route('stock.movements')"
                    class="bg-blue-500 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Stock Movements
                </Link>
                <Link :href="route('stock.opname')"
                    class="bg-green-500 hover:bg-green-700 text-white px-4 py-2 rounded">
                Stock Opname
                </Link>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form @submit.prevent="filterStock">
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
                    <div class="flex items-end space-x-2">
                        <input type="text" v-model="filters.low_stock" placeholder="Low stock threshold"
                            class="flex-1 border border-gray-300 rounded-md px-3 py-2">
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
                            Current Stock</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Min
                            Stock</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="stock in stocks.data" :key="stock.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ stock.product.name }}</div>
                            <div class="text-sm text-gray-500">{{ stock.product.sku }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ stock.product.category.name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ stock.quantity }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ stock.product.min_stock }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                :class="stock.quantity <= stock.product.min_stock ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'"
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                {{ stock.quantity <= stock.product.min_stock ? 'Low Stock' : 'In Stock' }} </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <Link :href="`/stock/${stock.id}`" class="text-indigo-600 hover:text-indigo-900 mr-4">View
                            </Link>
                            <button @click="adjustStock(stock)"
                                class="text-yellow-600 hover:text-yellow-900">Adjust</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <Pagination v-if="stocks.links" :links="stocks.links" />
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
    low_stock: props.filters.low_stock || '',
});

const filterStock = () => {
    router.get(route('stock.index'), filters.value, { preserveState: true, replace: true });
};

const adjustStock = (stock) => {
    const newQuantity = prompt(`Adjust stock for ${stock.product.name}. Current: ${stock.quantity}`, stock.quantity);
    if (newQuantity !== null) {
        router.patch(route('stock.adjust', stock.id), { quantity: newQuantity });
    }
};
</script>
