<template>
    <AuthenticatedLayout>

        <Head title="Laporan Penjualan" />

        <template #header>
            <div>
                <h2 class="font-semibold text-lg text-[#1f1f1f] leading-tight">
                    Laporan Penjualan
                </h2>
                <p class="text-xs text-[#555]">Pantau ringkasan dan detail transaksi penjualan.</p>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
                <!-- Filters -->
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

                <!-- Summary -->
                <div class="bg-white border border-[#9c9c9c] rounded">
                    <div class="p-4">
                        <h3 class="text-sm font-semibold mb-3 text-[#1f1f1f]">Ringkasan</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div class="bg-[#f7f7f7] border border-[#9c9c9c] p-3 rounded">
                                <div class="text-lg font-bold text-[#1f1f1f]">{{ formatCurrency(summary.total_sales) }}
                                </div>
                                <div class="text-xs text-[#555]">Total Penjualan</div>
                            </div>
                            <div class="bg-[#f7f7f7] border border-[#9c9c9c] p-3 rounded">
                                <div class="text-lg font-bold text-[#1f1f1f]">{{ formatCurrency(summary.total_discount)
                                    }}</div>
                                <div class="text-xs text-[#555]">Total Diskon</div>
                            </div>
                            <div class="bg-[#f7f7f7] border border-[#9c9c9c] p-3 rounded">
                                <div class="text-lg font-bold text-[#1f1f1f]">{{ formatCurrency(summary.net_sales) }}
                                </div>
                                <div class="text-xs text-[#555]">Penjualan Bersih</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sales Table -->
                <div class="bg-white border border-[#9c9c9c] rounded">
                    <div class="p-4">
                        <h3 class="text-sm font-semibold mb-3 text-[#1f1f1f]">Detail Penjualan</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-[#d0d0d0] text-sm text-[#1f1f1f]">
                                <thead class="bg-[#efefef]">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                            No Faktur</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                            Tanggal</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                            Pelanggan</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                            Kasir</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                            Total</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                            Diskon</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                            Final</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-[#d0d0d0]">
                                    <tr v-for="sale in sales" :key="sale.id" class="hover:bg-[#f7f7f7]">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-[#1f1f1f]">{{
                                            sale.invoice_number }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-[#555]">{{
                                            formatDate(sale.transaction_date) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-[#555]">{{
                                            sale.member?.name ||
                                            'Umum' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-[#555]">{{ sale.user?.name
                                            }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-[#555]">{{
                                            formatCurrency(sale.subtotal) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-[#555]">{{
                                            formatCurrency(sale.discount) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-[#555]">{{
                                            formatCurrency(sale.final_amount) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    sales: Array,
    summary: Object,
    filters: Object,
});

const filters = ref(props.filters);

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID');
};

const fetchReport = () => {
    // Implement filtering logic if needed
    // For now, just reload with filters
    window.location.href = route('reports.sales', filters.value);
};
</script>
