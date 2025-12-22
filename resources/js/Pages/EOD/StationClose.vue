<script setup>
import { computed } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, usePage, Link } from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    businessDate: { type: String, required: true },
    station: { type: Object, required: true },
    summary: { type: Object, required: true },
    alreadyClosed: { type: Boolean, default: false },
    existing: { type: [Object, null], default: null },
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

const currency = (n) => {
    const num = Number(n ?? 0);
    return new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR" }).format(num);
};

const expectedCash = computed(() => Number(props.summary?.expectedCash ?? 0));
const varianceLive = computed(() => Number(form.cash_counted ?? 0) - expectedCash.value);

const form = useForm({
    business_date: props.businessDate,
    cash_counted: 0,
    authorization_password: "",
    notes: "", // controller Anda belum validate notes, tapi aman untuk future
});

const submit = () => {
    form.post(route("eod.station-close.store"), { preserveScroll: true });
};
</script>

<template>

    <Head title="Tutup Kasir Harian" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="font-semibold text-lg text-[#1f1f1f] leading-tight">Tutup Kasir Harian</h2>
                <p class="text-xs text-[#555]">Tutup harian untuk station yang sedang aktif.</p>
            </div>
        </template>

        <div class="max-w-5xl mx-auto space-y-6">
            <div v-if="flash.success"
                class="rounded border border-[#9c9c9c] bg-[#e1f3e1] p-4 text-[#1f1f1f]">
                {{ flash.success }}
            </div>
            <div v-if="flash.warning" class="rounded border border-[#9c9c9c] bg-[#fff1d6] p-4 text-[#1f1f1f]">
                {{ flash.warning }}
            </div>

            <!-- Header -->
            <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div>
                        <div class="text-[#1f1f1f] text-xl font-bold">Tutup Kasir Harian (Station)</div>
                        <div class="text-[#555] mt-1">
                            Station: <span class="text-[#1f1f1f] font-semibold">{{ station.name }}</span>
                            <span class="mx-2 text-[#555]">•</span>
                            Device: <span class="text-[#1f1f1f] font-semibold">{{ station.device_identifier }}</span>
                        </div>
                        <div class="text-[#555] mt-1">
                            Business date: <span class="text-[#1f1f1f] font-semibold">{{ businessDate }}</span>
                        </div>
                    </div>

                    <div v-if="alreadyClosed"
                        class="px-4 py-2 rounded bg-[#e1f3e1] border border-[#9c9c9c] text-[#1f1f1f] text-sm">
                        Sudah ditutup
                        <div v-if="existing?.closed_at" class="text-xs text-[#555] mt-1">
                            closed_at: {{ existing.closed_at }}
                        </div>
                    </div>
                    <div v-else class="px-4 py-2 rounded bg-[#f0f0f0] border border-[#9c9c9c] text-[#1f1f1f] text-sm">
                        Belum ditutup
                    </div>
                </div>
            </div>

            <!-- Ringkasan -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded p-5">
                    <div class="text-[#555] text-sm">Total Sales</div>
                    <div class="text-[#1f1f1f] text-xl font-bold mt-1">{{ currency(summary.totalSales) }}</div>
                    <div class="text-[#555] text-sm mt-2">
                        Sales count: <span class="text-[#1f1f1f] font-semibold">{{ summary.salesCount }}</span>
                    </div>
                </div>

                <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded p-5">
                    <div class="text-[#555] text-sm">Total Sales Return</div>
                    <div class="text-[#1f1f1f] text-xl font-bold mt-1">{{ currency(summary.totalSalesReturn) }}</div>
                    <div class="text-[#555] text-sm mt-2">
                        Discount: <span class="text-[#1f1f1f] font-semibold">{{ currency(summary.totalDiscount) }}</span>
                    </div>
                </div>

                <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded p-5">
                    <div class="text-[#555] text-sm">Total Tax</div>
                    <div class="text-[#1f1f1f] text-xl font-bold mt-1">{{ currency(summary.totalTax) }}</div>
                    <div class="text-[#555] text-sm mt-2">
                        Shift closed: <span class="text-[#1f1f1f] font-semibold">{{ summary.shiftCount }}</span>
                    </div>
                </div>
            </div>

            <!-- Cash drawer -->
            <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded p-6 space-y-4">
                <div class="text-[#1f1f1f] text-lg font-semibold">Cash Drawer</div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white border border-[#9c9c9c] rounded p-4">
                        <div class="text-[#555] text-sm">Initial Cash Sum</div>
                        <div class="text-[#1f1f1f] font-bold mt-1">{{ currency(summary.initialCashSum) }}</div>
                    </div>

                    <div class="bg-white border border-[#9c9c9c] rounded p-4">
                        <div class="text-[#555] text-sm">Cash Sales</div>
                        <div class="text-[#1f1f1f] font-bold mt-1">{{ currency(summary.cashSales) }}</div>
                    </div>

                    <div class="bg-white border border-[#9c9c9c] rounded p-4 md:col-span-2">
                        <div class="text-[#555] text-sm">Expected Cash (Sistem)</div>
                        <div class="text-[#1f1f1f] text-xl font-bold mt-1">{{ currency(expectedCash) }}</div>
                        <div class="text-[#555] text-xs mt-1">
                            expectedCash = initialCashSum + cashSales - salesReturn - cashPurchase + purchaseReturn
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-[#9c9c9c] rounded p-4">
                    <div class="text-[#555] text-sm">Payments Breakdown</div>
                    <div class="mt-2 space-y-1">
                        <div v-for="p in summary.payments" :key="p.method" class="flex justify-between text-sm">
                            <span class="text-[#555]">{{ p.method }}</span>
                            <span class="text-[#1f1f1f] font-semibold">{{ currency(p.total) }}</span>
                        </div>
                        <div v-if="!summary.payments || summary.payments.length === 0" class="text-[#555] text-sm">
                            Tidak ada transaksi.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded p-6">
                <form @submit.prevent="submit" class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="cash_counted" value="Cash Counted" class="text-[#1f1f1f]" />
                            <TextInput id="cash_counted" type="number"
                                class="mt-1 block w-full bg-white border-[#9c9c9c] focus:ring-blue-500 focus:border-blue-500 rounded"
                                v-model="form.cash_counted" :disabled="alreadyClosed" min="0" step="0.01" required />
                            <InputError class="mt-2 text-red-600" :message="form.errors.cash_counted" />
                            <div class="text-[#555] text-xs mt-2">
                                Variance (live):
                                <span class="font-semibold text-[#1f1f1f]">
                                    {{ currency(varianceLive) }}
                                </span>
                            </div>
                        </div>

                        <div>
                            <InputLabel for="authorization_password"
                                value="Password Otorisasi (Authorization: 'Tutup Harian')" class="text-[#1f1f1f]" />
                            <TextInput id="authorization_password" type="password"
                                class="mt-1 block w-full bg-white border-[#9c9c9c] focus:ring-blue-500 focus:border-blue-500 rounded"
                                v-model="form.authorization_password" :disabled="alreadyClosed" required />
                            <InputError class="mt-2 text-red-600" :message="form.errors.authorization_password" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <Link :href="route('eod.index')"
                            class="px-4 py-2 rounded bg-[#e9e9e9] border border-[#9c9c9c] text-[#1f1f1f] text-xs font-semibold hover:bg-white">
                            Riwayat EOD
                        </Link>

                        <button type="submit" :disabled="form.processing || alreadyClosed"
                            class="inline-flex items-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] px-4 py-2 text-xs font-semibold shadow-sm hover:bg-white transition-colors">
                            {{ alreadyClosed ? "Sudah Ditutup" : "Simpan Tutup Harian Station" }}
                        </button>
                    </div>

                    <div v-if="alreadyClosed" class="text-[#555] text-sm">
                        Data tidak bisa diubah karena station daily closing sudah tersimpan untuk tanggal ini.
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
