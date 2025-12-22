<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    sales: Object,
    filters: Object,
});

const search = ref(props.filters?.search ?? '');

watch(search, (value) => {
    router.get(route('sales.index'), { search: value }, { preserveState: true, replace: true });
});

const formatCurrency = (value) => {
    if (value === null || value === undefined) {
        return '-';
    }
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Daftar Penjualan" />

        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-lg text-[#1f1f1f] leading-tight">Daftar Penjualan</h2>
                    <p class="text-xs text-[#555] mt-1">Kelola data transaksi penjualan dengan tampilan konsisten.</p>
                </div>
                <div class="flex items-center gap-3">
                    <input v-model="search" type="text" placeholder="Cari nomor invoice..."
                        class="bg-white border border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500 px-4 py-2 text-xs" />
                    <button @click="$inertia.reload({ only: ['sales'] })"
                        class="bg-[#e9e9e9] text-[#1f1f1f] font-semibold py-2 px-4 rounded border border-[#9c9c9c] hover:bg-white transition text-xs">
                        Refresh
                    </button>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
            <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs text-[#1f1f1f]">
                        <thead class="bg-[#efefef] border-b border-[#9c9c9c]">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold text-[#1f1f1f]">
                                    No
                                    Invoice</th>
                                <th
                                    class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold text-[#1f1f1f]">
                                    Tanggal</th>
                                <th
                                    class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold text-[#1f1f1f]">
                                    Kasir</th>
                                <th
                                    class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold text-[#1f1f1f]">
                                    Pelanggan</th>
                                <th
                                    class="px-6 py-3 text-right text-xs uppercase tracking-wider font-semibold text-[#1f1f1f]">
                                    Total</th>
                                <th
                                    class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold text-[#1f1f1f]">
                                    Metode Bayar</th>
                                <th
                                    class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold text-[#1f1f1f]">
                                    Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#d0d0d0]">
                            <tr v-for="sale in props.sales.data" :key="sale.id"
                                class="hover:bg-white transition-all duration-200">
                                <td class="px-6 py-4 font-mono">{{ sale.invoice_number }}</td>
                                <td class="px-6 py-4">{{ new Date(sale.transaction_date).toLocaleDateString('id-ID') }}
                                </td>
                                <td class="px-6 py-4">{{ sale.user?.name || '-' }}</td>
                                <td class="px-6 py-4">{{ sale.member?.name || 'Umum' }}</td>
                                <td class="px-6 py-4 text-right font-semibold">{{ formatCurrency(sale.final_amount) }}
                                </td>
                                <td class="px-6 py-4">{{ sale.payment_method }}</td>
                                <td class="px-6 py-4">{{ sale.status || 'Selesai' }}</td>
                            </tr>
                            <tr v-if="!props.sales.data.length">
                                <td colspan="7" class="px-6 py-6 text-center text-[#555]">
                                    Belum ada data penjualan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination :links="props.sales.links" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
