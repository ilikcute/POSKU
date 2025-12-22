<script setup>
import { computed } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, usePage, Link } from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

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
            <h2 class="font-semibold text-xl text-white leading-tight">Tutup Kasir Harian</h2>
        </template>

        <div class="max-w-5xl mx-auto space-y-6">
            <div v-if="flash.success"
                class="rounded-xl bg-emerald-500/15 border border-emerald-400/30 p-4 text-emerald-100">
                {{ flash.success }}
            </div>
            <div v-if="flash.warning" class="rounded-xl bg-amber-500/15 border border-amber-400/30 p-4 text-amber-100">
                {{ flash.warning }}
            </div>

            <!-- Header -->
            <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div>
                        <div class="text-white text-2xl font-bold">Tutup Kasir Harian (Station)</div>
                        <div class="text-gray-300 mt-1">
                            Station: <span class="text-white font-semibold">{{ station.name }}</span>
                            <span class="mx-2 text-gray-500">•</span>
                            Device: <span class="text-white font-semibold">{{ station.device_identifier }}</span>
                        </div>
                        <div class="text-gray-300 mt-1">
                            Business date: <span class="text-white font-semibold">{{ businessDate }}</span>
                        </div>
                    </div>

                    <div v-if="alreadyClosed"
                        class="px-4 py-2 rounded-xl bg-emerald-500/15 border border-emerald-400/30 text-emerald-100">
                        Sudah ditutup
                        <div v-if="existing?.closed_at" class="text-xs text-emerald-200 mt-1">
                            closed_at: {{ existing.closed_at }}
                        </div>
                    </div>
                    <div v-else class="px-4 py-2 rounded-xl bg-blue-500/15 border border-blue-400/30 text-blue-100">
                        Belum ditutup
                    </div>
                </div>
            </div>

            <!-- Ringkasan -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white/10 border border-white/10 rounded-2xl p-5">
                    <div class="text-gray-300 text-sm">Total Sales</div>
                    <div class="text-white text-2xl font-bold mt-1">{{ currency(summary.totalSales) }}</div>
                    <div class="text-gray-400 text-sm mt-2">
                        Sales count: <span class="text-white font-semibold">{{ summary.salesCount }}</span>
                    </div>
                </div>

                <div class="bg-white/10 border border-white/10 rounded-2xl p-5">
                    <div class="text-gray-300 text-sm">Total Sales Return</div>
                    <div class="text-white text-2xl font-bold mt-1">{{ currency(summary.totalSalesReturn) }}</div>
                    <div class="text-gray-400 text-sm mt-2">
                        Discount: <span class="text-white font-semibold">{{ currency(summary.totalDiscount) }}</span>
                    </div>
                </div>

                <div class="bg-white/10 border border-white/10 rounded-2xl p-5">
                    <div class="text-gray-300 text-sm">Total Tax</div>
                    <div class="text-white text-2xl font-bold mt-1">{{ currency(summary.totalTax) }}</div>
                    <div class="text-gray-400 text-sm mt-2">
                        Shift closed: <span class="text-white font-semibold">{{ summary.shiftCount }}</span>
                    </div>
                </div>
            </div>

            <!-- Cash drawer -->
            <div class="bg-white/10 border border-white/10 rounded-2xl p-6 space-y-4">
                <div class="text-white text-lg font-semibold">Cash Drawer</div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white/5 border border-white/10 rounded-xl p-4">
                        <div class="text-gray-300 text-sm">Initial Cash Sum</div>
                        <div class="text-white font-bold mt-1">{{ currency(summary.initialCashSum) }}</div>
                    </div>

                    <div class="bg-white/5 border border-white/10 rounded-xl p-4">
                        <div class="text-gray-300 text-sm">Cash Sales</div>
                        <div class="text-white font-bold mt-1">{{ currency(summary.cashSales) }}</div>
                    </div>

                    <div class="bg-white/5 border border-white/10 rounded-xl p-4 md:col-span-2">
                        <div class="text-gray-300 text-sm">Expected Cash (Sistem)</div>
                        <div class="text-white text-xl font-bold mt-1">{{ currency(expectedCash) }}</div>
                        <div class="text-gray-400 text-xs mt-1">
                            expectedCash = initialCashSum + cashSales - salesReturn - cashPurchase + purchaseReturn
                        </div>
                    </div>
                </div>

                <div class="bg-white/5 border border-white/10 rounded-xl p-4">
                    <div class="text-gray-300 text-sm">Payments Breakdown</div>
                    <div class="mt-2 space-y-1">
                        <div v-for="p in summary.payments" :key="p.method" class="flex justify-between text-sm">
                            <span class="text-gray-300">{{ p.method }}</span>
                            <span class="text-white font-semibold">{{ currency(p.total) }}</span>
                        </div>
                        <div v-if="!summary.payments || summary.payments.length === 0" class="text-gray-500 text-sm">
                            Tidak ada transaksi.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl p-6">
                <form @submit.prevent="submit" class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="cash_counted" value="Cash Counted" class="text-white" />
                            <TextInput id="cash_counted" type="number"
                                class="mt-1 block w-full bg-white/5 border-white/20 focus:ring-blue-500 focus:border-blue-500 rounded-lg"
                                v-model="form.cash_counted" :disabled="alreadyClosed" min="0" step="0.01" required />
                            <InputError class="mt-2" :message="form.errors.cash_counted" />
                            <div class="text-gray-400 text-xs mt-2">
                                Variance (live):
                                <span class="font-semibold"
                                    :class="varianceLive === 0 ? 'text-white' : varianceLive > 0 ? 'text-emerald-200' : 'text-rose-200'">
                                    {{ currency(varianceLive) }}
                                </span>
                            </div>
                        </div>

                        <div>
                            <InputLabel for="authorization_password"
                                value="Password Otorisasi (Authorization: 'Tutup Harian')" class="text-white" />
                            <TextInput id="authorization_password" type="password"
                                class="mt-1 block w-full bg-white/5 border-white/20 focus:ring-blue-500 focus:border-blue-500 rounded-lg"
                                v-model="form.authorization_password" :disabled="alreadyClosed" required />
                            <InputError class="mt-2" :message="form.errors.authorization_password" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <Link :href="route('eod.index')"
                            class="px-4 py-2 rounded-xl bg-white/10 border border-white/20 text-white hover:bg-white/15">
                            Riwayat EOD
                        </Link>

                        <PrimaryButton type="submit" class="rounded-xl" :disabled="form.processing || alreadyClosed">
                            {{ alreadyClosed ? "Sudah Ditutup" : "Simpan Tutup Harian Station" }}
                        </PrimaryButton>
                    </div>

                    <div v-if="alreadyClosed" class="text-gray-300 text-sm">
                        Data tidak bisa diubah karena station daily closing sudah tersimpan untuk tanggal ini.
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
