<template>
    <AuthenticatedLayout>
        <Head title="Laporan Detail Penjualan" />

        <template #header>
            <div>
                <h2 class="font-semibold text-lg text-[#1f1f1f] leading-tight">
                    Laporan Detail Penjualan
                </h2>
                <p class="text-xs text-[#555]">Detail item penjualan per periode.</p>
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
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                            <div class="bg-[#f7f7f7] border border-[#9c9c9c] p-3 rounded">
                                <div class="text-lg font-bold text-[#1f1f1f]">{{ summary.total_qty }}</div>
                                <div class="text-xs text-[#555]">Total Qty</div>
                            </div>
                            <div class="bg-[#f7f7f7] border border-[#9c9c9c] p-3 rounded">
                                <div class="text-lg font-bold text-[#1f1f1f]">
                                    {{ formatCurrency(summary.total_subtotal) }}
                                </div>
                                <div class="text-xs text-[#555]">Subtotal</div>
                            </div>
                            <div class="bg-[#f7f7f7] border border-[#9c9c9c] p-3 rounded">
                                <div class="text-lg font-bold text-[#1f1f1f]">
                                    {{ formatCurrency(summary.total_discount) }}
                                </div>
                                <div class="text-xs text-[#555]">Diskon</div>
                            </div>
                            <div class="bg-[#f7f7f7] border border-[#9c9c9c] p-3 rounded">
                                <div class="text-lg font-bold text-[#1f1f1f]">
                                    {{ formatCurrency(summary.total_tax) }}
                                </div>
                                <div class="text-xs text-[#555]">PPN</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-[#9c9c9c] rounded">
                    <div class="p-4">
                        <h3 class="text-sm font-semibold mb-3 text-[#1f1f1f]">Detail Item</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-[#d0d0d0] text-xs text-[#1f1f1f]">
                                <thead class="bg-[#efefef]">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                            Nota
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                            Tanggal
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                            Produk
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider">
                                            Qty
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider">
                                            Harga
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider">
                                            Diskon
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider">
                                            PPN
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider">
                                            Subtotal
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                            Kasir
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-[#d0d0d0]">
                                    <tr v-for="detail in details.data" :key="detail.id" class="hover:bg-[#f7f7f7]">
                                        <td class="px-4 py-3 font-semibold">
                                            {{ detail.sale?.invoice_number || '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-[#555]">
                                            {{ formatDate(detail.sale?.transaction_date) }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-semibold">{{ detail.product?.name || '-' }}</div>
                                            <div class="text-[10px] text-[#777] font-mono">
                                                {{ detail.product?.product_code || detail.product?.barcode || '-' }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-right">{{ detail.quantity }}</td>
                                        <td class="px-4 py-3 text-right">{{ formatCurrency(detail.price_at_sale) }}</td>
                                        <td class="px-4 py-3 text-right">
                                            {{ formatCurrency(detail.discount_per_item * detail.quantity) }}
                                        </td>
                                        <td class="px-4 py-3 text-right">{{ formatCurrency(detail.tax_amount) }}</td>
                                        <td class="px-4 py-3 text-right">{{ formatCurrency(detail.subtotal) }}</td>
                                        <td class="px-4 py-3 text-[#555]">{{ detail.sale?.user?.name || '-' }}</td>
                                    </tr>
                                    <tr v-if="!details.data.length">
                                        <td colspan="9" class="px-4 py-6 text-center text-[#777]">
                                            Belum ada data.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <Pagination v-if="details.links" :links="details.links" class="mt-6" />
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
    details: Object,
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
    window.location.href = route('reports.sales-details', filters.value);
};
</script>
