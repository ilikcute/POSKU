<template>
    <AuthenticatedLayout>
        <Head title="Laporan Stok Menipis" />

        <template #header>
            <div>
                <h2 class="font-semibold text-lg text-[#1f1f1f] leading-tight">
                    Laporan Stok Menipis
                </h2>
                <p class="text-xs text-[#555]">Pantau barang yang mendekati stok minimum.</p>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
                <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded">
                    <div class="p-4">
                        <form @submit.prevent="fetchReport" class="flex flex-wrap gap-4">
                            <div>
                                <label class="block text-sm font-medium text-[#1f1f1f]">Pencarian</label>
                                <input v-model="filters.search" type="text"
                                    class="mt-1 block w-full border border-[#9c9c9c] rounded bg-white text-[#1f1f1f] px-3 py-2"
                                    placeholder="Nama / Kode / Barcode" />
                            </div>
                            <div class="flex items-end">
                                <button type="submit"
                                    class="bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] px-4 py-2 text-xs font-semibold shadow-sm hover:bg-white transition-colors">
                                    Filter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="bg-white border border-[#9c9c9c] rounded">
                    <div class="p-4">
                        <h3 class="text-sm font-semibold mb-3 text-[#1f1f1f]">Daftar Stok Menipis</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-[#d0d0d0] text-xs text-[#1f1f1f]">
                                <thead class="bg-[#efefef]">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                            Produk
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                            Kategori
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                            Supplier
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider">
                                            Stok
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider">
                                            Min. Alert
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-[#d0d0d0]">
                                    <tr v-for="row in stocks.data" :key="row.id" class="hover:bg-[#f7f7f7]">
                                        <td class="px-4 py-3">
                                            <div class="font-semibold">{{ row.product?.name || '-' }}</div>
                                            <div class="text-[10px] text-[#777] font-mono">
                                                {{ row.product?.product_code || row.product?.barcode || '-' }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-[#555]">{{ row.product?.category?.name || '-' }}</td>
                                        <td class="px-4 py-3 text-[#555]">{{ row.product?.supplier?.name || '-' }}</td>
                                        <td class="px-4 py-3 text-right font-semibold text-[#c53030]">
                                            {{ row.quantity }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-[#555]">
                                            {{ row.product?.min_stock_alert ?? '-' }}
                                        </td>
                                    </tr>
                                    <tr v-if="!stocks.data.length">
                                        <td colspan="5" class="px-4 py-6 text-center text-[#777]">
                                            Tidak ada data stok menipis.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <Pagination v-if="stocks.links" :links="stocks.links" class="mt-6" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { ref } from 'vue';

const props = defineProps({
    stocks: Object,
    filters: Object,
});

const filters = ref({ ...props.filters });

const fetchReport = () => {
    window.location.href = route('reports.low-stock', filters.value);
};
</script>
