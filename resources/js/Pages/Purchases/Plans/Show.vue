<template>
    <AuthenticatedLayout>
        <Head title="Detail Rencana Pembelian" />

        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-lg text-[#1f1f1f] leading-tight">
                        Detail Rencana Pembelian
                    </h2>
                    <p class="text-xs text-[#555] mt-1">
                        Doc No: <span class="font-semibold">{{ plan.doc_no }}</span> • Supplier:
                        <span class="font-semibold">{{ plan.supplier?.name || '-' }}</span>
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a :href="route('purchases.plans.pdf', plan.id)"
                        class="inline-flex items-center justify-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] font-semibold py-3 px-6 rounded text-xs hover:bg-white transition-colors">
                        Unduh PDF
                    </a>
                    <Link :href="route('purchases.plans.index')"
                        class="inline-flex items-center justify-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] font-semibold py-3 px-6 rounded text-xs hover:bg-white transition-colors">
                        Kembali
                    </Link>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-sm p-4 mb-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs text-[#1f1f1f]">
                    <div>
                        <div class="text-[#555]">Tanggal</div>
                        <div class="font-semibold">{{ plan.plan_date }}</div>
                    </div>
                    <div>
                        <div class="text-[#555]">Total Item</div>
                        <div class="font-semibold">{{ plan.total_items }}</div>
                    </div>
                    <div>
                        <div class="text-[#555]">Total Qty</div>
                        <div class="font-semibold">{{ plan.total_qty }}</div>
                    </div>
                </div>
            </div>

            <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs text-[#1f1f1f]">
                        <thead class="bg-[#efefef] border-b border-[#9c9c9c]">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold">
                                    Kode
                                </th>
                                <th class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold">
                                    Nama Barang
                                </th>
                                <th class="px-6 py-3 text-right text-xs uppercase tracking-wider font-semibold">
                                    Stok Saat Ini
                                </th>
                                <th class="px-6 py-3 text-right text-xs uppercase tracking-wider font-semibold">
                                    Stok Maks
                                </th>
                                <th class="px-6 py-3 text-right text-xs uppercase tracking-wider font-semibold">
                                    Kebutuhan
                                </th>
                                <th class="px-6 py-3 text-right text-xs uppercase tracking-wider font-semibold">
                                    Kelipatan
                                </th>
                                <th class="px-6 py-3 text-right text-xs uppercase tracking-wider font-semibold">
                                    Rencana Qty
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#d0d0d0] bg-[#f7f7f7]">
                            <tr v-for="item in plan.items" :key="item.id" class="hover:bg-white">
                                <td class="px-6 py-4 whitespace-nowrap font-semibold">
                                    {{ item.product?.product_code || '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-[#555]">
                                    {{ item.product?.name || '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    {{ item.current_stock }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    {{ item.max_stock }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    {{ item.needed_qty }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    {{ item.min_order_qty }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right font-semibold">
                                    {{ item.planned_qty }}
                                </td>
                            </tr>
                            <tr v-if="!plan.items.length">
                                <td colspan="7" class="px-6 py-6 text-center text-[#555]">
                                    Tidak ada item pada rencana ini.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";

const props = defineProps({
    plan: Object,
});
</script>
