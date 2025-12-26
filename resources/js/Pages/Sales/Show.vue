<template>
    <AuthenticatedLayout>
        <Head title="Detail Penjualan" />

        <template #header>
            <div class="flex flex-col gap-2">
                <h2 class="font-semibold text-lg text-[#1f1f1f] leading-tight">Detail Penjualan</h2>
                <p class="text-xs text-[#555]">Informasi lengkap transaksi penjualan.</p>
            </div>
        </template>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-[#9c9c9c] flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                    <h1 class="text-sm font-semibold text-[#1f1f1f]">Ringkasan Penjualan</h1>
                    <div class="flex flex-wrap gap-2">
                        <Link :href="`/sales/${sale.id}/pdf`"
                            class="bg-[#e9e9e9] border border-[#9c9c9c] text-[#1f1f1f] px-4 py-2 rounded text-xs font-semibold hover:bg-white">
                        Download PDF
                        </Link>
                        <Link :href="`/sales/${sale.id}/print`"
                            class="bg-[#e9e9e9] border border-[#9c9c9c] text-[#1f1f1f] px-4 py-2 rounded text-xs font-semibold hover:bg-white">
                        Print
                        </Link>
                        <Link :href="route('sales.edit', sale.id)"
                            class="bg-[#e9e9e9] border border-[#9c9c9c] text-[#1f1f1f] px-4 py-2 rounded text-xs font-semibold hover:bg-white">
                        Edit
                        </Link>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-white border border-[#9c9c9c] rounded p-4">
                            <h3 class="text-sm font-semibold mb-3 text-[#1f1f1f]">Informasi Penjualan</h3>
                            <div class="space-y-2 text-xs text-[#1f1f1f]">
                                <p><strong>Invoice:</strong> {{ sale.invoice_number }}</p>
                                <p><strong>Tanggal:</strong> {{ sale.transaction_date }}</p>
                                <p><strong>Kasir:</strong> {{ sale.user.name }}</p>
                                <p v-if="sale.member"><strong>Pelanggan:</strong> {{ sale.member.name }}</p>
                            </div>
                        </div>
                        <div class="bg-white border border-[#9c9c9c] rounded p-4">
                            <h3 class="text-sm font-semibold mb-3 text-[#1f1f1f]">Pembayaran</h3>
                            <div class="space-y-2 text-xs text-[#1f1f1f]">
                                <p><strong>Metode Bayar:</strong> {{ sale.payment_method }}</p>
                                <p><strong>Total:</strong> Rp {{ sale.total_amount.toLocaleString() }}</p>
                                <p v-if="sale.discount > 0"><strong>Diskon:</strong> Rp {{
                                    sale.discount.toLocaleString() }}</p>
                                <p><strong>Pajak:</strong> Rp {{ sale.tax.toLocaleString() }}</p>
                                <p><strong>Total Akhir:</strong> Rp {{ sale.final_amount.toLocaleString() }}</p>
                                <p><strong>Bayar:</strong> Rp {{ sale.amount_paid.toLocaleString() }}</p>
                                <p><strong>Kembalian:</strong> Rp {{ sale.change_due.toLocaleString() }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-sm font-semibold mb-3 text-[#1f1f1f]">Item</h3>
                        <div class="overflow-x-auto border border-[#9c9c9c]">
                            <table class="min-w-full text-xs text-[#1f1f1f]">
                                <thead class="bg-[#efefef] border-b border-[#9c9c9c]">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold">
                                            Product</th>
                                        <th class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold">
                                            Qty</th>
                                        <th class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold">
                                            Price</th>
                                        <th class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold">
                                            Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-[#d0d0d0]">
                                    <tr v-for="detail in sale.sale_details" :key="detail.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-xs font-semibold text-[#1f1f1f]">
                                            {{ detail.product.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-[#555]">
                                            {{ detail.quantity }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-[#555]">
                                            Rp {{ detail.price_at_sale.toLocaleString() }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-[#555]">
                                            Rp {{ detail.subtotal.toLocaleString() }}
                                        </td>
                                    </tr>
                                    <tr v-if="!sale.sale_details?.length">
                                        <td colspan="4" class="px-6 py-6 text-center text-[#555]">
                                            Tidak ada item.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div v-if="sale.notes" class="mb-6 bg-white border border-[#9c9c9c] rounded p-4">
                        <h3 class="text-sm font-semibold mb-2 text-[#1f1f1f]">Catatan</h3>
                        <p class="text-xs text-[#1f1f1f]">{{ sale.notes }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    sale: Object,
});
</script>
