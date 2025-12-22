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
            <h2 class="font-semibold text-xl text-white leading-tight">Riwayat Tutup Harian (EOD)</h2>
        </template>

        <div class="max-w-6xl mx-auto space-y-6">
            <div v-if="flash.success"
                class="rounded-xl bg-emerald-500/15 border border-emerald-400/30 p-4 text-emerald-100">
                {{ flash.success }}
            </div>
            <div v-if="flash.warning" class="rounded-xl bg-amber-500/15 border border-amber-400/30 p-4 text-amber-100">
                {{ flash.warning }}
            </div>

            <div class="bg-white/10 border border-white/10 rounded-2xl p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div>
                        <div class="text-white text-2xl font-bold">Riwayat Finalize Harian</div>
                        <div class="text-gray-400 text-sm mt-1">Data berasal dari tabel daily_closings.</div>
                    </div>

                    <div class="flex gap-2">
                        <Link :href="route('eod.station-close.form')"
                            class="px-4 py-2 rounded-xl bg-white/10 border border-white/20 text-white hover:bg-white/15">
                            Tutup Station
                        </Link>
                        <Link :href="route('eod.finalize.form')"
                            class="px-4 py-2 rounded-xl bg-white/10 border border-white/20 text-white hover:bg-white/15">
                            Finalize Toko
                        </Link>
                    </div>
                </div>
            </div>

            <div class="bg-white/10 border border-white/10 rounded-2xl p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-gray-300 border-b border-white/10">
                                <th class="text-left py-3 pr-4">Tanggal</th>
                                <th class="text-left py-3 pr-4">Total Sales</th>
                                <th class="text-left py-3 pr-4">Sales Return</th>
                                <th class="text-left py-3 pr-4">Purchase</th>
                                <th class="text-left py-3 pr-4">Cash Counted</th>
                                <th class="text-left py-3 pr-4">Expected</th>
                                <th class="text-left py-3 pr-4">Variance</th>
                            </tr>
                        </thead>

                        <tbody class="text-gray-200">
                            <tr v-for="row in closings.data" :key="row.id" class="border-b border-white/5">
                                <td class="py-3 pr-4">
                                    <div class="text-white font-semibold">{{ row.business_date }}</div>
                                    <div class="text-gray-400 text-xs">finalized_at: {{ row.finalized_at }}</div>
                                </td>

                                <td class="py-3 pr-4">{{ currency(row.total_sales) }}</td>
                                <td class="py-3 pr-4">{{ currency(row.total_sales_return) }}</td>
                                <td class="py-3 pr-4">{{ currency(row.total_purchase) }}</td>
                                <td class="py-3 pr-4">{{ currency(row.cash_counted_total) }}</td>
                                <td class="py-3 pr-4">{{ currency(row.expected_cash_total) }}</td>
                                <td class="py-3 pr-4">
                                    <span class="font-semibold"
                                        :class="Number(row.variance_total ?? 0) === 0 ? 'text-white' : Number(row.variance_total ?? 0) > 0 ? 'text-emerald-200' : 'text-rose-200'">
                                        {{ currency(row.variance_total) }}
                                    </span>
                                </td>
                            </tr>

                            <tr v-if="!closings.data || closings.data.length === 0">
                                <td colspan="7" class="py-6 text-center text-gray-400">
                                    Belum ada data finalize EOD.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="closings.links" class="flex flex-wrap gap-2 mt-6">
                    <Link v-for="(link, i) in closings.links" :key="i" :href="link.url || '#'"
                        class="px-3 py-2 rounded-lg border text-sm" :class="[
                            link.active ? 'bg-white/15 border-white/30 text-white' : 'bg-white/5 border-white/10 text-gray-300 hover:bg-white/10',
                            !link.url ? 'opacity-40 pointer-events-none' : ''
                        ]" v-html="link.label" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
