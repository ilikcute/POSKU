<template>
    <AuthenticatedLayout>
        <Head title="Reprint Struk Penjualan" />

        <template #header>
            <div>
                <h2 class="font-semibold text-lg text-[#1f1f1f] leading-tight">
                    Reprint Struk Penjualan
                </h2>
                <p class="text-xs text-[#555]">Cari transaksi dan cetak ulang struk.</p>
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
                            <div>
                                <label class="block text-sm font-medium text-[#1f1f1f]">No Invoice</label>
                                <input v-model="filters.search" type="text"
                                    class="mt-1 block w-full border border-[#9c9c9c] rounded bg-white text-[#1f1f1f] px-3 py-2"
                                    placeholder="INV-2025..." />
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
                        <h3 class="text-sm font-semibold mb-3 text-[#1f1f1f]">Daftar Transaksi</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-[#d0d0d0] text-xs text-[#1f1f1f]">
                                <thead class="bg-[#efefef]">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                            Invoice
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                            Tanggal
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                            Pelanggan
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                            Kasir
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider">
                                            Total
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-[#d0d0d0]">
                                    <tr v-for="sale in sales.data" :key="sale.id" class="hover:bg-[#f7f7f7]">
                                        <td class="px-4 py-3 font-semibold">{{ sale.invoice_number }}</td>
                                        <td class="px-4 py-3 text-[#555]">{{ formatDate(sale.transaction_date) }}</td>
                                        <td class="px-4 py-3 text-[#555]">{{ sale.customer?.name || 'Umum' }}</td>
                                        <td class="px-4 py-3 text-[#555]">{{ sale.user?.name || '-' }}</td>
                                        <td class="px-4 py-3 text-right">{{ formatCurrency(sale.final_amount) }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <Link :href="route('sales.print', sale.id)" target="_blank"
                                                class="inline-flex bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] px-3 py-2 text-[11px] font-semibold hover:bg-white transition-colors">
                                                Cetak
                                            </Link>
                                        </td>
                                    </tr>
                                    <tr v-if="!sales.data.length">
                                        <td colspan="6" class="px-4 py-6 text-center text-[#777]">
                                            Belum ada data.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <Pagination v-if="sales.links" :links="sales.links" class="mt-6" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { ref } from 'vue';

const props = defineProps({
    sales: Object,
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
    window.location.href = route('reports.sales-reprint', filters.value);
};
</script>
