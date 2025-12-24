<template>
    <AuthenticatedLayout>
        <Head title="Stok" />

        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="font-semibold text-lg text-[#1f1f1f]">Kelola Stok</h2>
                    <p class="text-xs text-[#555]">Pantau stok dan lakukan opname.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Link :href="route('stock.movements')"
                        class="inline-flex items-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] px-4 py-2 text-xs font-semibold shadow-sm hover:bg-white transition-colors">
                        Riwayat Stok
                    </Link>
                    <Link :href="route('stock.opname')"
                        class="inline-flex items-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] px-4 py-2 text-xs font-semibold shadow-sm hover:bg-white transition-colors">
                        Opname Stok
                    </Link>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded p-4">
                <form @submit.prevent="filterStock">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-[#1f1f1f] mb-1">Kategori</label>
                            <select v-model="filters.category_id"
                                class="w-full border border-[#9c9c9c] rounded px-3 py-2 bg-white text-[#1f1f1f]">
                                <option value="">Semua Kategori</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[#1f1f1f] mb-1">Pencarian</label>
                            <input type="text" v-model="filters.search" placeholder="Cari produk..."
                                class="w-full border border-[#9c9c9c] rounded px-3 py-2 bg-white text-[#1f1f1f]">
                        </div>
                        <div class="flex items-end gap-2">
                            <input type="text" v-model="filters.low_stock" placeholder="Ambang stok minimum"
                                class="flex-1 border border-[#9c9c9c] rounded px-3 py-2 bg-white text-[#1f1f1f]">
                            <button type="submit"
                                class="bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] px-4 py-2 text-xs font-semibold shadow-sm hover:bg-white transition-colors">
                                Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="bg-white border border-[#9c9c9c] rounded overflow-hidden">
                <table class="min-w-full divide-y divide-[#d0d0d0] text-sm text-[#1f1f1f]">
                    <thead class="bg-[#efefef]">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Produk</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Kategori</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Stok Saat Ini</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Min Stok</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#d0d0d0]">
                        <tr v-for="stock in stocks.data" :key="stock.id" class="hover:bg-[#f7f7f7]">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium">{{ stock.product.name }}</div>
                                <div class="text-xs text-[#555]">{{ stock.product.product_code }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-[#1f1f1f]">
                                {{ stock.product.category?.name || 'Tanpa Kategori' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                {{ stock.quantity }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-[#555]">
                                {{ stock.product.min_stock_alert }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    :class="stock.quantity <= stock.product.min_stock_alert ? 'bg-[#ffe1e1] text-[#8a1f1f]' : 'bg-[#e1f3e1] text-[#1f7a1f]'"
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded">
                                    {{ stock.quantity <= stock.product.min_stock_alert ? 'Low Stock' : 'In Stock' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <Link :href="route('stock.show', stock.id)"
                                    class="text-[#1f1f1f] hover:underline">
                                    Lihat
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination v-if="stocks.links" :links="stocks.links" />
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
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

</script>
