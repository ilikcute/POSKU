<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    businessDate: String,
    alreadyFinalized: Boolean,
    final: Object,
    stations: Array,
    canFinalize: Boolean,
    storeSummary: Object,
});

const form = useForm({
    business_date: props.businessDate,
    authorization_password: "",
    notes: "",
});

const formatCurrency = (v) =>
    new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 })
        .format(Number(v ?? 0));

const submit = () => {
    if (!props.canFinalize) return;
    if (confirm("Finalize EOD toko? Setelah difinalize, biasanya data EOD tidak boleh diubah.")) {
        form.post(route("eod.finalize.store"));
    }
};
</script>

<template>

    <Head title="Finalize EOD Toko" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">
                Finalize EOD Toko
            </h2>
        </template>

        <div class="max-w-5xl mx-auto space-y-6">
            <div class="bg-white/10 border border-white/10 rounded-2xl p-6 text-white">
                <div class="text-sm text-gray-300">Tanggal</div>
                <div class="text-2xl font-bold">{{ businessDate }}</div>

                <div v-if="alreadyFinalized" class="mt-3 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20">
                    EOD sudah difinalize untuk tanggal ini.
                </div>
            </div>

            <!-- Stations list -->
            <div class="bg-white/10 border border-white/10 rounded-2xl p-6 text-gray-200">
                <div class="text-lg font-bold text-white mb-4">Status Tutup Harian per Station</div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="border-b border-white/10 text-xs uppercase text-white/80">
                            <tr>
                                <th class="py-3 px-3 text-left">Station</th>
                                <th class="py-3 px-3 text-left">Device</th>
                                <th class="py-3 px-3 text-left">Status</th>
                                <th class="py-3 px-3 text-right">Cash Counted</th>
                                <th class="py-3 px-3 text-right">Expected</th>
                                <th class="py-3 px-3 text-right">Variance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10">
                            <tr v-for="s in stations" :key="s.id" class="hover:bg-white/5">
                                <td class="py-3 px-3 text-white font-medium">{{ s.name }}</td>
                                <td class="py-3 px-3 text-gray-300">{{ s.device_identifier }}</td>
                                <td class="py-3 px-3">
                                    <span v-if="s.is_closed" class="text-emerald-300 font-semibold">CLOSED</span>
                                    <span v-else class="text-red-300 font-semibold">PENDING</span>
                                </td>
                                <td class="py-3 px-3 text-right">{{ formatCurrency(s.closing?.cash_counted ?? 0) }}</td>
                                <td class="py-3 px-3 text-right">{{ formatCurrency(s.closing?.expected_cash ?? 0) }}
                                </td>
                                <td class="py-3 px-3 text-right"
                                    :class="(s.closing?.variance ?? 0) === 0 ? 'text-white' : (s.closing?.variance ?? 0) > 0 ? 'text-emerald-300' : 'text-red-300'">
                                    {{ formatCurrency(s.closing?.variance ?? 0) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Store summary -->
            <div class="bg-white/10 border border-white/10 rounded-2xl p-6 text-gray-200">
                <div class="text-lg font-bold text-white mb-4">Rekap Toko (sum semua station)</div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Total Sales</span>
                        <span class="text-white font-semibold">{{ formatCurrency(storeSummary.totalSales) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Sales Return</span>
                        <span class="text-white font-semibold">{{ formatCurrency(storeSummary.totalSalesReturn)
                            }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Total Purchase</span>
                        <span class="text-white font-semibold">{{ formatCurrency(storeSummary.totalPurchase) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Purchase Return</span>
                        <span class="text-white font-semibold">{{ formatCurrency(storeSummary.totalPurchaseReturn)
                            }}</span>
                    </div>

                    <div class="flex justify-between border-t border-white/10 pt-2 mt-2">
                        <span class="text-gray-400">Cash Counted Total</span>
                        <span class="text-white font-semibold">{{ formatCurrency(storeSummary.cashCountedTotal)
                            }}</span>
                    </div>
                    <div class="flex justify-between border-t border-white/10 pt-2 mt-2">
                        <span class="text-gray-400">Expected Cash Total</span>
                        <span class="text-white font-semibold">{{ formatCurrency(storeSummary.expectedCashTotal)
                            }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Variance Total</span>
                        <span class="text-white font-semibold">{{ formatCurrency(storeSummary.varianceTotal) }}</span>
                    </div>
                </div>
            </div>

            <form v-if="!alreadyFinalized" @submit.prevent="submit"
                class="bg-white/10 border border-white/10 rounded-2xl p-6 space-y-5">
                <div>
                    <InputLabel for="authorization_password" value="Password Verifikasi (Finalize EOD)"
                        class="text-white" />
                    <TextInput id="authorization_password" type="password"
                        class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg"
                        v-model="form.authorization_password" required />
                    <InputError class="mt-2" :message="form.errors.authorization_password" />
                </div>

                <div>
                    <InputLabel for="notes" value="Catatan (opsional)" class="text-white" />
                    <TextInput id="notes" type="text" class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg"
                        v-model="form.notes" />
                    <InputError class="mt-2" :message="form.errors.notes" />
                </div>

                <div class="flex justify-end">
                    <button type="submit" :disabled="form.processing || !canFinalize"
                        class="inline-block bg-gradient-to-r from-indigo-400 to-pink-400 text-white font-bold py-3 px-8 rounded-full shadow-xl disabled:opacity-60">
                        Finalize EOD Toko
                    </button>
                </div>

                <div v-if="!canFinalize" class="text-sm text-red-300">
                    Belum semua station melakukan tutup harian. Selesaikan tutup harian station terlebih dahulu.
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
