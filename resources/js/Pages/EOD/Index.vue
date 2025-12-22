<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    closings: { type: Object, required: true }, // paginator
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

const currency = (n) => {
    const num = Number(n ?? 0);
    return new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR" }).format(num);
};
</script>

<template>

    <Head title="Riwayat EOD" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="font-semibold text-lg text-[#1f1f1f] leading-tight">Riwayat Tutup Harian (EOD)</h2>
                <p class="text-xs text-[#555]">Lihat hasil finalize harian toko.</p>
            </div>
        </template>

        <div class="max-w-6xl mx-auto space-y-6">
            <div v-if="flash.success"
                class="rounded bg-[#e1f3e1] border border-[#9c9c9c] p-4 text-[#1f1f1f]">
                {{ flash.success }}
            </div>
            <div v-if="flash.warning" class="rounded bg-[#fff1d6] border border-[#9c9c9c] p-4 text-[#1f1f1f]">
                {{ flash.warning }}
            </div>

            <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div>
                        <div class="text-[#1f1f1f] text-xl font-bold">Riwayat Finalize Harian</div>
                        <div class="text-[#555] text-sm mt-1">Data berasal dari tabel daily_closings.</div>
                    </div>

                    <div class="flex gap-2">
                        <Link :href="route('eod.station-close.form')"
                            class="px-4 py-2 rounded bg-[#e9e9e9] border border-[#9c9c9c] text-[#1f1f1f] text-xs font-semibold hover:bg-white">
                            Tutup Station
                        </Link>
                        <Link :href="route('eod.finalize.form')"
                            class="px-4 py-2 rounded bg-[#e9e9e9] border border-[#9c9c9c] text-[#1f1f1f] text-xs font-semibold hover:bg-white">
                            Finalize Toko
                        </Link>
                    </div>
                </div>
            </div>

            <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-[#1f1f1f]">
                        <thead>
                            <tr class="text-[#1f1f1f] bg-[#efefef] border-b border-[#9c9c9c]">
                                <th class="text-left py-3 pr-4">Tanggal</th>
                                <th class="text-left py-3 pr-4">Total Sales</th>
                                <th class="text-left py-3 pr-4">Sales Return</th>
                                <th class="text-left py-3 pr-4">Purchase</th>
                                <th class="text-left py-3 pr-4">Cash Counted</th>
                                <th class="text-left py-3 pr-4">Expected</th>
                                <th class="text-left py-3 pr-4">Variance</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="row in closings.data" :key="row.id" class="border-b border-[#d0d0d0]">
                                <td class="py-3 pr-4">
                                    <div class="text-[#1f1f1f] font-semibold">{{ row.business_date }}</div>
                                    <div class="text-[#555] text-xs">finalized_at: {{ row.finalized_at }}</div>
                                </td>

                                <td class="py-3 pr-4">{{ currency(row.total_sales) }}</td>
                                <td class="py-3 pr-4">{{ currency(row.total_sales_return) }}</td>
                                <td class="py-3 pr-4">{{ currency(row.total_purchase) }}</td>
                                <td class="py-3 pr-4">{{ currency(row.cash_counted_total) }}</td>
                                <td class="py-3 pr-4">{{ currency(row.expected_cash_total) }}</td>
                                <td class="py-3 pr-4">
                                    <span class="font-semibold text-[#1f1f1f]">
                                        {{ currency(row.variance_total) }}
                                    </span>
                                </td>
                            </tr>

                            <tr v-if="!closings.data || closings.data.length === 0">
                                <td colspan="7" class="py-6 text-center text-[#555]">
                                    Belum ada data finalize EOD.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="closings.links" class="flex flex-wrap gap-2 mt-6">
                    <Link v-for="(link, i) in closings.links" :key="i" :href="link.url || '#'"
                        class="px-3 py-2 rounded border text-xs font-semibold" :class="[
                            link.active ? 'bg-[#e9e9e9] border-[#9c9c9c] text-[#1f1f1f]' : 'bg-white border-[#9c9c9c] text-[#555] hover:bg-[#f7f7f7]',
                            !link.url ? 'opacity-40 pointer-events-none' : ''
                        ]" v-html="link.label" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
