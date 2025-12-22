<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { computed } from "vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    businessDate: String,
    station: Object,
    summary: Object,
    alreadyClosed: Boolean,
    existing: Object,
});

const form = useForm({
    business_date: props.businessDate,
    cash_counted: 0,
    authorization_password: "",
});

const formatCurrency = (v) =>
    new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 })
        .format(Number(v ?? 0));

const variance = computed(() => Number(form.cash_counted ?? 0) - Number(props.summary.expectedCash ?? 0));

const submit = () => {
    if (confirm("Tutup harian station ini? Pastikan semua shift sudah ditutup.")) {
        form.post(route("eod.station-close.store"));
    }
};
</script>

<template>

    <Head title="Tutup Harian Station" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">
                Tutup Harian Station
            </h2>
        </template>

        <div class="max-w-4xl mx-auto space-y-6">
            <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl p-6 text-white">
                <div class="flex flex-col gap-2">
                    <div class="text-lg font-bold">Station: {{ station?.name }}</div>
                    <div class="text-sm text-gray-300">Device: {{ station?.device_identifier }}</div>
                    <div class="text-sm text-gray-300">Tanggal: <span class="font-semibold text-white">{{ businessDate
                    }}</span>
                    </div>

                    <div v-if="alreadyClosed"
                        class="mt-3 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20">
                        Station ini sudah ditutup harian untuk tanggal ini.
                    </div>
                </div>
            </div>

            <!-- Summary -->
            <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl p-6 text-gray-200 space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-400">Total Sales</span>
                    <span class="text-white font-semibold">{{ formatCurrency(summary.totalSales) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Sales Return</span>
                    <span class="text-white font-semibold">{{ formatCurrency(summary.totalSalesReturn) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Total Purchase</span>
                    <span class="text-white font-semibold">{{ formatCurrency(summary.totalPurchase) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Purchase Return</span>
                    <span class="text-white font-semibold">{{ formatCurrency(summary.totalPurchaseReturn) }}</span>
                </div>

                <div class="flex justify-between border-t border-white/10 pt-2 mt-2">
                    <span class="text-gray-400">Initial Cash Sum (shift)</span>
                    <span class="text-white font-semibold">{{ formatCurrency(summary.initialCashSum) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Cash Sales</span>
                    <span class="text-white font-semibold">{{ formatCurrency(summary.cashSales) }}</span>
                </div>

                <div class="flex justify-between border-t border-white/10 pt-2 mt-2">
                    <span class="text-gray-400">Expected Cash (System)</span>
                    <span class="text-white font-semibold">{{ formatCurrency(summary.expectedCash) }}</span>
                </div>
            </div>

            <form v-if="!alreadyClosed" @submit.prevent="submit"
                class="bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl p-6 space-y-5">
                <div>
                    <InputLabel for="cash_counted" value="Cash Counted (Uang Cash di Laci)" class="text-white" />
                    <TextInput id="cash_counted" type="number"
                        class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg" v-model="form.cash_counted"
                        required />
                    <InputError class="mt-2" :message="form.errors.cash_counted" />
                </div>

                <div class="rounded-xl border border-white/10 p-3 text-center font-semibold"
                    :class="variance === 0 ? 'bg-white/10' : variance > 0 ? 'bg-emerald-500/10' : 'bg-red-500/10'">
                    <div class="text-gray-300 text-sm">Selisih (Cash Counted - Expected)</div>
                    <div :class="variance === 0 ? 'text-white' : variance > 0 ? 'text-emerald-300' : 'text-red-300'">
                        {{ formatCurrency(variance) }}
                    </div>
                </div>

                <div>
                    <InputLabel for="authorization_password" value="Password Verifikasi (Tutup Harian)"
                        class="text-white" />
                    <TextInput id="authorization_password" type="password"
                        class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg"
                        v-model="form.authorization_password" required />
                    <InputError class="mt-2" :message="form.errors.authorization_password" />
                </div>

                <div class="flex justify-end">
                    <button type="submit" :disabled="form.processing"
                        class="inline-block bg-gradient-to-r from-indigo-400 to-pink-400 text-white font-bold py-3 px-8 rounded-full shadow-xl disabled:opacity-60">
                        Tutup Harian Station
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
