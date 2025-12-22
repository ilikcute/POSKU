<template>
    <AuthenticatedLayout>
        <Head title="Pembelian" />

        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-lg text-[#1f1f1f] leading-tight">
                        Daftar Pembelian
                    </h2>
                    <p class="text-xs text-[#555] mt-1">
                        Kelola transaksi pembelian dengan tampilan desktop style.
                    </p>
                </div>
                <Link :href="route('purchases.create')"
                    class="inline-flex items-center justify-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] font-semibold py-3 px-6 rounded text-xs hover:bg-white transition-colors">
                Tambah Pembelian
                </Link>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-sm p-4 mb-6">
                <form @submit.prevent="filterPurchases">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#555] mb-1">Start Date</label>
                            <input type="date" v-model="filters.start_date"
                                class="w-full border border-[#9c9c9c] bg-white rounded px-3 py-2 text-sm text-[#1f1f1f]">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#555] mb-1">End Date</label>
                            <input type="date" v-model="filters.end_date"
                                class="w-full border border-[#9c9c9c] bg-white rounded px-3 py-2 text-sm text-[#1f1f1f]">
                        </div>
                        <div class="flex items-end gap-2">
                            <input type="text" v-model="filters.search" placeholder="Cari nomor nota..."
                                class="flex-1 border border-[#9c9c9c] bg-white rounded px-3 py-2 text-sm text-[#1f1f1f]">
                            <button type="submit"
                                class="bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] px-4 py-2 rounded text-xs font-semibold hover:bg-white">
                                Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Purchases Table -->
            <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs text-[#1f1f1f]">
                        <thead class="bg-[#efefef] border-b border-[#9c9c9c]">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold">
                                    Invoice</th>
                                <th class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold">Date
                                </th>
                                <th class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold">
                                    Supplier</th>
                                <th class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold">Total
                                </th>
                                <th class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold">
                                    Status</th>
                                <th class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#d0d0d0] bg-[#f7f7f7]">
                            <tr v-for="purchase in purchases.data" :key="purchase.id" class="hover:bg-white">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-xs font-semibold text-[#1f1f1f]">{{ purchase.invoice_number }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-[#555]">
                                    {{ purchase.transaction_date }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-[#555]">
                                    {{ purchase.supplier ? purchase.supplier.name : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-[#1f1f1f]">
                                    {{ formatCurrency(purchase.final_amount) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs">
                                    <span :class="[
                                        'px-2 inline-flex text-[11px] leading-5 font-semibold border border-[#9c9c9c]',
                                        purchase.status === 'completed'
                                            ? 'bg-[#d7f2d7] text-[#1f1f1f]'
                                            : 'bg-[#f4e6bf] text-[#1f1f1f]'
                                    ]">
                                        {{ purchase.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs font-semibold">
                                    <Link :href="`/purchases/${purchase.id}`"
                                        class="inline-flex items-center px-3 py-2 rounded bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] hover:bg-white mr-2">
                                    Lihat
                                    </Link>
                                    <Link :href="`/purchases/${purchase.id}/edit`"
                                        class="inline-flex items-center px-3 py-2 rounded bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] hover:bg-white mr-2">
                                    Edit
                                    </Link>
                                    <button @click="deletePurchase(purchase.id)"
                                        class="inline-flex items-center px-3 py-2 rounded bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] hover:bg-white">
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!purchases.data.length">
                                <td colspan="6" class="px-6 py-6 text-center text-[#555]">
                                    Belum ada data pembelian.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <Pagination v-if="purchases.links" :links="purchases.links" />
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
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

const formatCurrency = (value) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value || 0);
</script>
