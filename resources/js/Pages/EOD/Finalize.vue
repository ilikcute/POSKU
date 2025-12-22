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
    alreadyFinalized: { type: Boolean, default: false },
    final: { type: [Object, null], default: null },
    stations: { type: Array, required: true }, // [{id,name,device_identifier,is_closed,closing}]
    canFinalize: { type: Boolean, required: true },
    storeSummary: { type: Object, required: true },
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

const currency = (n) => {
    const num = Number(n ?? 0);
    return new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR" }).format(num);
};

const form = useForm({
    business_date: props.businessDate,
    authorization_password: "",
    notes: "",
});

const submit = () => {
    form.post(route("eod.finalize.store"), { preserveScroll: true });
};
</script>

<template>

    <Head title="Finalize EOD" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">Finalize Tutup Harian Toko</h2>
        </template>

        <div class="max-w-6xl mx-auto space-y-6">
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
                        <div class="text-white text-2xl font-bold">Finalize EOD Toko</div>
                        <div class="text-gray-300 mt-1">
                            Business date: <span class="text-white font-semibold">{{ businessDate }}</span>
                        </div>
                        <div class="text-gray-400 text-sm mt-1">
                            Syarat finalize: semua station yang terlibat hari itu harus sudah melakukan Station Close.
                        </div>
                    </div>

                    <div v-if="alreadyFinalized"
                        class="px-4 py-2 rounded-xl bg-blue-500/15 border border-blue-400/30 text-blue-100">
                        Sudah finalize
                        <div v-if="final?.finalized_at" class="text-xs text-blue-200 mt-1">
                            finalized_at: {{ final.finalized_at }}
                        </div>
                    </div>

                    <div v-else class="px-4 py-2 rounded-xl border"
                        :class="canFinalize ? 'bg-emerald-500/15 border-emerald-400/30 text-emerald-100' : 'bg-amber-500/15 border-amber-400/30 text-amber-100'">
                        {{ canFinalize ? "Siap Finalize" : "Belum bisa finalize" }}
                    </div>
                </div>
            </div>

            <!-- Store Summary -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white/10 border border-white/10 rounded-2xl p-5">
                    <div class="text-gray-300 text-sm">Total Sales</div>
                    <div class="text-white text-2xl font-bold mt-1">{{ currency(storeSummary.totalSales) }}</div>
                    <div class="text-gray-400 text-sm mt-2">
                        Sales count: <span class="text-white font-semibold">{{ storeSummary.salesCount }}</span>
                    </div>
                </div>

                <div class="bg-white/10 border border-white/10 rounded-2xl p-5">
                    <div class="text-gray-300 text-sm">Total Sales Return</div>
                    <div class="text-white text-2xl font-bold mt-1">{{ currency(storeSummary.totalSalesReturn) }}</div>
                    <div class="text-gray-400 text-sm mt-2">
                        Station count: <span class="text-white font-semibold">{{ storeSummary.stationCount }}</span>
                    </div>
                </div>

                <div class="bg-white/10 border border-white/10 rounded-2xl p-5">
                    <div class="text-gray-300 text-sm">Discount & Tax</div>
                    <div class="text-white font-bold mt-2">Discount: {{ currency(storeSummary.totalDiscount) }}</div>
                    <div class="text-white font-bold mt-1">Tax: {{ currency(storeSummary.totalTax) }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white/10 border border-white/10 rounded-2xl p-5">
                    <div class="text-gray-300 text-sm">Cash Counted Total</div>
                    <div class="text-white text-2xl font-bold mt-1">{{ currency(storeSummary.cashCountedTotal) }}</div>
                </div>

                <div class="bg-white/10 border border-white/10 rounded-2xl p-5">
                    <div class="text-gray-300 text-sm">Expected Cash Total</div>
                    <div class="text-white text-2xl font-bold mt-1">{{ currency(storeSummary.expectedCashTotal) }}</div>
                </div>

                <div class="bg-white/10 border border-white/10 rounded-2xl p-5">
                    <div class="text-gray-300 text-sm">Variance Total</div>
                    <div class="text-2xl font-bold mt-1"
                        :class="Number(storeSummary.varianceTotal ?? 0) === 0 ? 'text-white' : Number(storeSummary.varianceTotal ?? 0) > 0 ? 'text-emerald-200' : 'text-rose-200'">
                        {{ currency(storeSummary.varianceTotal) }}
                    </div>
                </div>
            </div>

            <!-- Stations table -->
            <div class="bg-white/10 border border-white/10 rounded-2xl p-6">
                <div class="text-white text-lg font-semibold mb-4">Station Terlibat</div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-gray-300 border-b border-white/10">
                                <th class="text-left py-3 pr-4">Station</th>
                                <th class="text-left py-3 pr-4">Status</th>
                                <th class="text-left py-3 pr-4">Cash Counted</th>
                                <th class="text-left py-3 pr-4">Expected</th>
                                <th class="text-left py-3 pr-4">Variance</th>
                                <th class="text-left py-3 pr-4">Closed By</th>
                            </tr>
                        </thead>

                        <tbody class="text-gray-200">
                            <tr v-for="st in stations" :key="st.id" class="border-b border-white/5">
                                <td class="py-3 pr-4">
                                    <div class="text-white font-semibold">{{ st.name }}</div>
                                    <div class="text-gray-400 text-xs">{{ st.device_identifier }}</div>
                                </td>

                                <td class="py-3 pr-4">
                                    <span class="px-2 py-1 rounded-lg text-xs border"
                                        :class="st.is_closed ? 'bg-emerald-500/15 border-emerald-400/30 text-emerald-100' : 'bg-amber-500/15 border-amber-400/30 text-amber-100'">
                                        {{ st.is_closed ? "Closed" : "Not Closed" }}
                                    </span>
                                </td>

                                <td class="py-3 pr-4">
                                    <span v-if="st.closing">{{ currency(st.closing.cash_counted) }}</span>
                                    <span v-else class="text-gray-500">-</span>
                                </td>

                                <td class="py-3 pr-4">
                                    <span v-if="st.closing">{{ currency(st.closing.expected_cash) }}</span>
                                    <span v-else class="text-gray-500">-</span>
                                </td>

                                <td class="py-3 pr-4">
                                    <span v-if="st.closing">{{ currency(st.closing.variance) }}</span>
                                    <span v-else class="text-gray-500">-</span>
                                </td>

                                <td class="py-3 pr-4">
                                    <span v-if="st.closing">{{ st.closing.closed_by }}</span>
                                    <span v-else class="text-gray-500">-</span>
                                </td>
                            </tr>

                            <tr v-if="stations.length === 0">
                                <td colspan="6" class="py-6 text-center text-gray-400">
                                    Tidak ada station yang terlibat pada tanggal ini.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="!canFinalize" class="mt-4 text-amber-100 text-sm">
                    Finalize tidak bisa dilakukan: pastikan semua station terlibat sudah Station Close dan tidak ada
                    shift open.
                </div>
            </div>

            <!-- Finalize form -->
            <div class="bg-white/10 border border-white/10 rounded-2xl p-6">
                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <InputLabel for="authorization_password"
                            value="Password Otorisasi (Authorization: 'Tutup Harian')" class="text-white" />
                        <TextInput id="authorization_password" type="password"
                            class="mt-1 block w-full bg-white/5 border-white/20 focus:ring-blue-500 focus:border-blue-500 rounded-lg"
                            v-model="form.authorization_password" :disabled="alreadyFinalized || !canFinalize"
                            required />
                        <InputError class="mt-2" :message="form.errors.authorization_password" />
                    </div>

                    <div>
                        <InputLabel for="notes" value="Catatan (opsional)" class="text-white" />
                        <textarea id="notes" v-model="form.notes" :disabled="alreadyFinalized || !canFinalize"
                            class="mt-1 block w-full bg-white/5 border border-white/20 focus:ring-blue-500 focus:border-blue-500 rounded-lg text-white"
                            rows="3" placeholder="Catatan finalize..." />
                        <InputError class="mt-2" :message="form.errors.notes" />
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <Link :href="route('eod.index')"
                            class="px-4 py-2 rounded-xl bg-white/10 border border-white/20 text-white hover:bg-white/15">
                            Riwayat EOD
                        </Link>

                        <PrimaryButton type="submit" class="rounded-xl"
                            :disabled="form.processing || alreadyFinalized || !canFinalize">
                            {{ alreadyFinalized ? "Sudah Finalize" : "Finalize EOD" }}
                        </PrimaryButton>
                    </div>

                    <div v-if="alreadyFinalized" class="text-gray-300 text-sm">
                        Data finalize sudah tersimpan untuk tanggal ini.
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
