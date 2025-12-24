<template>
    <AuthenticatedLayout>
        <Head title="Mutasi Stok Bulanan" />

        <template #header>
            <div>
                <h2 class="font-semibold text-lg text-[#1f1f1f] leading-tight">
                    Mutasi Stok Bulanan
                </h2>
                <p class="text-xs text-[#555]">Rekap saldo awal, mutasi, dan saldo akhir per produk.</p>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
                <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded">
                    <div class="p-4 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <form @submit.prevent="fetchReport" class="flex flex-wrap gap-4">
                            <div>
                                <label class="block text-sm font-medium text-[#1f1f1f]">Tahun</label>
                                <input v-model="filters.year" type="number" min="2000" max="2100"
                                    class="mt-1 block w-28 border border-[#9c9c9c] rounded bg-white text-[#1f1f1f] px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-[#1f1f1f]">Bulan</label>
                                <select v-model="filters.month"
                                    class="mt-1 block w-32 border border-[#9c9c9c] rounded bg-white text-[#1f1f1f] px-3 py-2">
                                    <option v-for="m in 12" :key="m" :value="m">{{ m }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-[#1f1f1f]">Kategori</label>
                                <select v-model="filters.category_id"
                                    class="mt-1 block w-56 border border-[#9c9c9c] rounded bg-white text-[#1f1f1f] px-3 py-2">
                                    <option value="">Semua</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                        {{ cat.name }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-[#1f1f1f]">Pencarian</label>
                                <input v-model="filters.search" type="text"
                                    class="mt-1 block w-56 border border-[#9c9c9c] rounded bg-white text-[#1f1f1f] px-3 py-2"
                                    placeholder="Nama / Kode" />
                            </div>
                            <div class="flex items-end">
                                <button type="submit"
                                    class="bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] px-4 py-2 text-xs font-semibold shadow-sm hover:bg-white transition-colors">
                                    Filter
                                </button>
                            </div>
                        </form>

                        <form @submit.prevent="doClose" class="flex items-end gap-3">
                            <input v-model="closeNotes" type="text"
                                class="border border-[#9c9c9c] rounded bg-white text-[#1f1f1f] px-3 py-2 text-xs w-64"
                                placeholder="Catatan closing (opsional)" />
                            <button type="submit" :disabled="alreadyClosed"
                                class="bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] px-4 py-2 text-xs font-semibold shadow-sm hover:bg-white transition-colors disabled:opacity-50">
                                {{ alreadyClosed ? 'Sudah Closing' : 'Proses Closing Bulanan' }}
                            </button>
                        </form>
                    </div>
                </div>

                <div class="bg-white border border-[#9c9c9c] rounded">
                    <div class="p-4">
                        <h3 class="text-sm font-semibold mb-3 text-[#1f1f1f]">Ringkasan Qty</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                            <div class="bg-[#f7f7f7] border border-[#9c9c9c] p-3 rounded">
                                <div class="text-lg font-bold text-[#1f1f1f]">{{ summary.opening_qty }}</div>
                                <div class="text-xs text-[#555]">Saldo Awal</div>
                            </div>
                            <div class="bg-[#f7f7f7] border border-[#9c9c9c] p-3 rounded">
                                <div class="text-lg font-bold text-[#1f1f1f]">{{ summary.in_qty_purchase }}</div>
                                <div class="text-xs text-[#555]">Pembelian</div>
                            </div>
                            <div class="bg-[#f7f7f7] border border-[#9c9c9c] p-3 rounded">
                                <div class="text-lg font-bold text-[#1f1f1f]">{{ summary.out_qty_sale }}</div>
                                <div class="text-xs text-[#555]">Penjualan</div>
                            </div>
                            <div class="bg-[#f7f7f7] border border-[#9c9c9c] p-3 rounded">
                                <div class="text-lg font-bold text-[#1f1f1f]">{{ summary.closing_qty }}</div>
                                <div class="text-xs text-[#555]">Saldo Akhir</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-[#9c9c9c] rounded">
                    <div class="p-4">
                        <h3 class="text-sm font-semibold mb-3 text-[#1f1f1f]">Ringkasan Nilai</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                            <div class="bg-[#f7f7f7] border border-[#9c9c9c] p-3 rounded">
                                <div class="text-lg font-bold text-[#1f1f1f]">{{ formatCurrency(summary.opening_value_dpp) }}</div>
                                <div class="text-xs text-[#555]">Saldo Awal (DPP)</div>
                            </div>
                            <div class="bg-[#f7f7f7] border border-[#9c9c9c] p-3 rounded">
                                <div class="text-lg font-bold text-[#1f1f1f]">{{ formatCurrency(summary.opening_value_final) }}</div>
                                <div class="text-xs text-[#555]">Saldo Awal (Final)</div>
                            </div>
                            <div class="bg-[#f7f7f7] border border-[#9c9c9c] p-3 rounded">
                                <div class="text-lg font-bold text-[#1f1f1f]">{{ formatCurrency(summary.closing_value_dpp) }}</div>
                                <div class="text-xs text-[#555]">Saldo Akhir (DPP)</div>
                            </div>
                            <div class="bg-[#f7f7f7] border border-[#9c9c9c] p-3 rounded">
                                <div class="text-lg font-bold text-[#1f1f1f]">{{ formatCurrency(summary.closing_value_final) }}</div>
                                <div class="text-xs text-[#555]">Saldo Akhir (Final)</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-[#9c9c9c] rounded">
                    <div class="p-4">
                        <h3 class="text-sm font-semibold mb-3 text-[#1f1f1f]">Detail Mutasi</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-[#d0d0d0] text-xs text-[#1f1f1f]">
                                <thead class="bg-[#efefef]">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                            Produk
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider">
                                            Saldo Awal
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider">
                                            Pembelian
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider">
                                            Retur Jual
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider">
                                            Penjualan
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider">
                                            Retur Beli
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider">
                                            Opname
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider">
                                            Saldo Akhir
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider">
                                            Nilai DPP
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider">
                                            Nilai Final
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-[#d0d0d0]">
                                    <tr v-for="row in closings.data" :key="row.id" class="hover:bg-[#f7f7f7]">
                                        <td class="px-4 py-3">
                                            <div class="font-semibold">{{ row.product?.name || '-' }}</div>
                                            <div class="text-[10px] text-[#777] font-mono">
                                                {{ row.product?.product_code || row.product?.barcode || '-' }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-right">{{ row.opening_qty }}</td>
                                        <td class="px-4 py-3 text-right">{{ row.in_qty_purchase }}</td>
                                        <td class="px-4 py-3 text-right">{{ row.in_qty_sales_return }}</td>
                                        <td class="px-4 py-3 text-right">{{ row.out_qty_sale }}</td>
                                        <td class="px-4 py-3 text-right">{{ row.out_qty_purchase_return }}</td>
                                        <td class="px-4 py-3 text-right">{{ row.adjustment_qty }}</td>
                                        <td class="px-4 py-3 text-right font-semibold">{{ row.closing_qty }}</td>
                                        <td class="px-4 py-3 text-right">{{ formatCurrency(row.closing_value_dpp) }}</td>
                                        <td class="px-4 py-3 text-right">{{ formatCurrency(row.closing_value_final) }}</td>
                                    </tr>
                                    <tr v-if="!closings.data.length">
                                        <td colspan="10" class="px-4 py-6 text-center text-[#777]">
                                            Data belum ada. Silakan lakukan closing bulanan.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <Pagination v-if="closings.links" :links="closings.links" class="mt-6" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { ref } from 'vue';

const props = defineProps({
    closings: Object,
    summary: Object,
    filters: Object,
    categories: Array,
    alreadyClosed: Boolean,
});

const filters = ref({ ...props.filters });
const closeNotes = ref('');

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value || 0);
};

const fetchReport = () => {
    router.get(route('reports.stock-mutation'), filters.value, {
        preserveState: true,
        replace: true,
    });
};

const doClose = () => {
    router.post(route('reports.stock-mutation.close'), {
        year: Number(filters.value.year),
        month: Number(filters.value.month),
        notes: closeNotes.value || null,
    });
};
</script>
