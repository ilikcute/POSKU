<template>
    <AuthenticatedLayout>
        <Head title="Laporan Retur Penjualan" />

        <template #header>
            <div>
                <h2 class="font-semibold text-lg text-[#1f1f1f] leading-tight">
                    Laporan Retur Penjualan
                </h2>
                <p class="text-xs text-[#555]">Pantau transaksi retur penjualan.</p>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
                <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded">
                    <div class="p-4">
                        <form @submit.prevent="fetchReport" class="flex flex-wrap gap-4">
                            <div>
                                <label class="block text-sm font-medium text-[#1f1f1f]">Tanggal Mulai</label>
                                <input v-model="filters.start_date" type="date"
                                    class="mt-1 block w-full border border-[#9c9c9c] rounded bg-white text-[#1f1f1f] px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-[#1f1f1f]">Tanggal Akhir</label>
                                <input v-model="filters.end_date" type="date"
                                    class="mt-1 block w-full border border-[#9c9c9c] rounded bg-white text-[#1f1f1f] px-3 py-2">
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
                        <h3 class="text-sm font-semibold mb-3 text-[#1f1f1f]">Ringkasan</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div class="bg-[#f7f7f7] border border-[#9c9c9c] p-3 rounded">
                                <div class="text-lg font-bold text-[#1f1f1f]">{{ summary.total_count }}</div>
                                <div class="text-xs text-[#555]">Jumlah Retur</div>
                            </div>
                            <div class="bg-[#f7f7f7] border border-[#9c9c9c] p-3 rounded">
                                <div class="text-lg font-bold text-[#1f1f1f]">{{ formatCurrency(summary.total_returns) }}</div>
                                <div class="text-xs text-[#555]">Total Retur</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-[#9c9c9c] rounded">
                    <div class="p-4">
                        <h3 class="text-sm font-semibold mb-3 text-[#1f1f1f]">Detail Retur</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-[#d0d0d0] text-xs text-[#1f1f1f]">
                                <thead class="bg-[#efefef]">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                            Kode Retur
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                            Nota Asal
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                            Tanggal
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                            Kasir
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                            Station
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider">
                                            Total
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-[#d0d0d0]">
                                    <tr v-for="row in returns.data" :key="row.id" class="hover:bg-[#f7f7f7]">
                                        <td class="px-4 py-3 font-semibold">RET-{{ row.id }}</td>
                                        <td class="px-4 py-3">{{ row.sale?.invoice_number || '-' }}</td>
                                        <td class="px-4 py-3 text-[#555]">{{ formatDate(row.return_date) }}</td>
                                        <td class="px-4 py-3 text-[#555]">{{ row.user?.name || '-' }}</td>
                                        <td class="px-4 py-3 text-[#555]">{{ row.station?.name || '-' }}</td>
                                        <td class="px-4 py-3 text-right">{{ formatCurrency(row.final_amount) }}</td>
                                    </tr>
                                    <tr v-if="!returns.data.length">
                                        <td colspan="6" class="px-4 py-6 text-center text-[#777]">
                                            Belum ada data.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <Pagination v-if="returns.links" :links="returns.links" class="mt-6" />
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
    returns: Object,
    summary: Object,
    filters: Object,
});

const filters = ref({ ...props.filters });

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value || 0);
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID');
};

const fetchReport = () => {
    window.location.href = route('reports.sales-returns', filters.value);
};
</script>
