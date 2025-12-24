<template>
    <AuthenticatedLayout>
        <Head title="Rencana Pembelian" />

        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-lg text-[#1f1f1f] leading-tight">
                        Rencana Pembelian
                    </h2>
                    <p class="text-xs text-[#555] mt-1">
                        Rencana dibuat berdasarkan stok maksimal dikurangi stok saat ini.
                    </p>
                </div>
                <button @click="generatePlan"
                    class="inline-flex items-center justify-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] font-semibold py-3 px-6 rounded text-xs hover:bg-white transition-colors">
                    Generate Rencana
                </button>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs text-[#1f1f1f]">
                        <thead class="bg-[#efefef] border-b border-[#9c9c9c]">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold">
                                    Doc No
                                </th>
                                <th class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold">
                                    Tanggal
                                </th>
                                <th class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold">
                                    Supplier
                                </th>
                                <th class="px-6 py-3 text-right text-xs uppercase tracking-wider font-semibold">
                                    Total Item
                                </th>
                                <th class="px-6 py-3 text-right text-xs uppercase tracking-wider font-semibold">
                                    Total Qty
                                </th>
                                <th class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold">
                                    Dibuat Oleh
                                </th>
                                <th class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#d0d0d0] bg-[#f7f7f7]">
                            <tr v-for="plan in plans.data" :key="plan.id" class="hover:bg-white">
                                <td class="px-6 py-4 whitespace-nowrap font-semibold">
                                    {{ plan.doc_no }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-[#555]">
                                    {{ plan.plan_date }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-[#555]">
                                    {{ plan.supplier?.name || '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    {{ plan.total_items }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    {{ plan.total_qty }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-[#555]">
                                    {{ plan.creator?.name || '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs font-semibold">
                                    <Link :href="route('purchases.plans.show', plan.id)"
                                        class="inline-flex items-center px-3 py-2 rounded bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] hover:bg-white">
                                        Lihat
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="!plans.data.length">
                                <td colspan="7" class="px-6 py-6 text-center text-[#555]">
                                    Belum ada rencana pembelian.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <Pagination v-if="plans.links" :links="plans.links" />
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import Pagination from "@/Components/Pagination.vue";

const props = defineProps({
    plans: Object,
});

const generatePlan = () => {
    if (confirm("Generate rencana pembelian baru?")) {
        router.post(route("purchases.plans.generate"));
    }
};
</script>
