<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";

const props = defineProps({
    closings: Object,
});

const formatCurrency = (v) =>
    new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 })
        .format(Number(v ?? 0));
</script>

<template>

    <Head title="Riwayat EOD" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">Riwayat EOD</h2>
        </template>

        <div class="max-w-5xl mx-auto bg-white/10 border border-white/10 rounded-2xl p-6 text-gray-200">
            <div class="flex items-center justify-between mb-4">
                <div class="text-lg font-bold text-white">EOD per Tanggal</div>
                <Link :href="route('eod.station-close.form')" class="text-sm text-white underline">
                    Tutup Harian Station
                </Link>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="border-b border-white/10 text-xs uppercase text-white/80">
                        <tr>
                            <th class="py-3 px-3 text-left">Tanggal</th>
                            <th class="py-3 px-3 text-right">Total Sales</th>
                            <th class="py-3 px-3 text-right">Variance</th>
                            <th class="py-3 px-3 text-right">Stations</th>
                            <th class="py-3 px-3 text-left">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        <tr v-for="c in closings.data" :key="c.id" class="hover:bg-white/5">
                            <td class="py-3 px-3 text-white font-medium">{{ c.business_date }}</td>
                            <td class="py-3 px-3 text-right">{{ formatCurrency(c.total_sales) }}</td>
                            <td class="py-3 px-3 text-right"
                                :class="Number(c.variance_total) === 0 ? 'text-white' : Number(c.variance_total) > 0 ? 'text-emerald-300' : 'text-red-300'">
                                {{ formatCurrency(c.variance_total) }}
                            </td>
                            <td class="py-3 px-3 text-right">{{ c.station_count }}</td>
                            <td class="py-3 px-3">
                                <Link :href="route('eod.finalize.form', { date: c.business_date })"
                                    class="text-white underline">
                                    Detail
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex gap-2">
                <Link v-if="closings.prev_page_url" :href="closings.prev_page_url" class="text-white underline">Prev
                </Link>
                <Link v-if="closings.next_page_url" :href="closings.next_page_url" class="text-white underline">Next
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
