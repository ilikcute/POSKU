<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
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
                    <h2 class="font-semibold text-xl text-white leading-tight">Daftar Penjualan</h2>
                    <p class="text-sm text-gray-400 mt-1">Kelola data transaksi penjualan dengan tampilan konsisten.</p>
                </div>
                <div class="flex items-center gap-3">
                    <input v-model="search" type="text" placeholder="Cari nomor invoice..."
                        class="bg-white/5 border border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500 px-4 py-2" />
                    <button @click="$inertia.reload({ only: ['sales'] })"
                        class="bg-gradient-to-r from-blue-400 to-sky-400 text-white font-bold py-2 px-4 rounded-lg hover:from-blue-500 hover:to-sky-500 transition">
                        Refresh
                    </button>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
            <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl shadow-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-gray-200">
                        <thead
                            class="bg-gradient-to-r from-purple-600/30 to-blue-600/30 backdrop-blur-sm border-b border-white/20">
                            <tr>
                                <th
                                    class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-white/90">
                                    No
                                    Invoice</th>
                                <th
                                    class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-white/90">
                                    Tanggal</th>
                                <th
                                    class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-white/90">
                                    Kasir</th>
                                <th
                                    class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-white/90">
                                    Pelanggan</th>
                                <th
                                    class="px-6 py-4 text-right text-xs uppercase tracking-wider font-semibold text-white/90">
                                    Total</th>
                                <th
                                    class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-white/90">
                                    Metode Bayar</th>
                                <th
                                    class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-white/90">
                                    Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700/50">
                            <tr v-for="sale in props.sales.data" :key="sale.id"
                                class="hover:bg-white/5 transition-all duration-200">
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
                        </tbody>
                    </table>
                </div>
                <Pagination :links="props.sales.links" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
