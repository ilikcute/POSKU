<template>
    <AuthenticatedLayout>
        <Head title="Detail Retur Penjualan" />

        <template #header>
            <div class="flex flex-col gap-2">
                <h2 class="font-semibold text-lg text-[#1f1f1f] leading-tight">
                    Detail Retur Penjualan
                </h2>
                <p class="text-xs text-[#555]">Informasi lengkap retur penjualan.</p>
            </div>
        </template>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-[#9c9c9c] flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                    <h1 class="text-sm font-semibold text-[#1f1f1f]">Ringkasan Retur</h1>
                    <div class="flex flex-wrap gap-2">
                        <Link :href="route('sales-returns.edit', salesReturn.id)"
                            class="bg-[#e9e9e9] border border-[#9c9c9c] text-[#1f1f1f] px-4 py-2 rounded text-xs font-semibold hover:bg-white">
                        Edit
                        </Link>
                        <Link :href="route('sales-returns.index')"
                            class="bg-[#e9e9e9] border border-[#9c9c9c] text-[#1f1f1f] px-4 py-2 rounded text-xs font-semibold hover:bg-white">
                        Kembali
                        </Link>
                    </div>
                </div>

                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-white border border-[#9c9c9c] rounded p-4">
                            <h3 class="text-sm font-semibold mb-3 text-[#1f1f1f]">Informasi Retur</h3>
                            <div class="space-y-2 text-xs text-[#1f1f1f]">
                                <p><strong>Tanggal:</strong> {{ formatDate(salesReturn.return_date) }}</p>
                                <p><strong>Kasir:</strong> {{ salesReturn.user?.name || "-" }}</p>
                                <p><strong>Toko:</strong> {{ salesReturn.store?.name || "-" }}</p>
                            </div>
                        </div>
                        <div class="bg-white border border-[#9c9c9c] rounded p-4">
                            <h3 class="text-sm font-semibold mb-3 text-[#1f1f1f]">Referensi Penjualan</h3>
                            <div class="space-y-2 text-xs text-[#1f1f1f]">
                                <p><strong>Invoice:</strong> {{ salesReturn.sale?.invoice_number || "-" }}</p>
                                <p><strong>Pelanggan:</strong> {{ salesReturn.sale?.member?.name || "Umum" }}</p>
                                <p><strong>Total Retur:</strong> {{ formatCurrency(salesReturn.final_amount) }}</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold mb-3 text-[#1f1f1f]">Item Retur</h3>
                        <div class="overflow-x-auto border border-[#9c9c9c]">
                            <table class="min-w-full text-xs text-[#1f1f1f]">
                                <thead class="bg-[#efefef] border-b border-[#9c9c9c]">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold">
                                            Produk</th>
                                        <th class="px-6 py-3 text-right text-xs uppercase tracking-wider font-semibold">
                                            Qty</th>
                                        <th class="px-6 py-3 text-right text-xs uppercase tracking-wider font-semibold">
                                            Harga</th>
                                        <th class="px-6 py-3 text-right text-xs uppercase tracking-wider font-semibold">
                                            Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-[#d0d0d0]">
                                    <tr v-for="detail in details" :key="detail.id">
                                        <td class="px-6 py-4 text-xs font-semibold text-[#1f1f1f]">
                                            {{ detail.product?.name || "-" }}
                                        </td>
                                        <td class="px-6 py-4 text-xs text-right text-[#555]">
                                            {{ detail.quantity }}
                                        </td>
                                        <td class="px-6 py-4 text-xs text-right text-[#555]">
                                            {{ formatCurrency(detail.price_at_return) }}
                                        </td>
                                        <td class="px-6 py-4 text-xs text-right text-[#555]">
                                            {{ formatCurrency(detail.subtotal) }}
                                        </td>
                                    </tr>
                                    <tr v-if="!details.length">
                                        <td colspan="4" class="px-6 py-6 text-center text-[#555]">
                                            Tidak ada item retur.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div v-if="salesReturn.notes" class="bg-white border border-[#9c9c9c] rounded p-4">
                        <h3 class="text-sm font-semibold mb-2 text-[#1f1f1f]">Catatan</h3>
                        <p class="text-xs text-[#1f1f1f]">{{ salesReturn.notes }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    salesReturn: Object,
});

const details = computed(() =>
    props.salesReturn?.sales_return_details ||
    props.salesReturn?.salesReturnDetails ||
    []
);

const formatCurrency = (value) =>
    new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(value || 0);

const formatDate = (value) => {
    if (!value) return "-";
    return new Date(value).toLocaleDateString("id-ID");
};
</script>
