<template>
    <div class="p-6">

        <Head title="Purchases" />
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Purchases</h1>
            <Link :href="route('purchases.create')"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            New Purchase
            </Link>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form @submit.prevent="filterPurchases">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                    <div class="flex items-end space-x-2">
                        <input type="text" v-model="filters.search" placeholder="Search invoice..."
                            class="flex-1 border border-gray-300 rounded-md px-3 py-2">
                        <button type="submit"
                            class="bg-blue-500 hover:bg-blue-700 text-white px-4 py-2 rounded-md">Filter</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Purchases Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Invoice</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Supplier</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="purchase in purchases.data" :key="purchase.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ purchase.invoice_number }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ purchase.transaction_date }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ purchase.supplier ? purchase.supplier.name : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            Rp {{ purchase.final_amount.toLocaleString() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ purchase.status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ purchase.status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <Link :href="`/purchases/${purchase.id}`"
                                class="text-indigo-600 hover:text-indigo-900 mr-4">View</Link>
                            <Link :href="`/purchases/${purchase.id}/edit`"
                                class="text-indigo-600 hover:text-indigo-900 mr-4">Edit</Link>
                            <button @click="deletePurchase(purchase.id)"
                                class="text-red-600 hover:text-red-900">Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <Pagination v-if="purchases.links" :links="purchases.links" />
    </div>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    purchases: Object,
    filters: Object,
});

const filters = ref({
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
    search: props.filters.search || '',
});

const filterPurchases = () => {
    router.get(route('purchases.index'), filters.value, { preserveState: true, replace: true });
};

const deletePurchase = (id) => {
    if (confirm('Are you sure you want to delete this purchase?')) {
        router.delete(route('purchases.destroy', id));
    }
};
</script>
