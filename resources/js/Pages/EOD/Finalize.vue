<script setup>
import { computed } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, usePage, Link } from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";

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
            <div>
                <h2 class="font-semibold text-lg text-[#1f1f1f] leading-tight">Finalize Tutup Harian Toko</h2>
                <p class="text-xs text-[#555]">Ringkas dan kunci tutup harian toko.</p>
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

            <!-- Header -->
            <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div>
                        <div class="text-[#1f1f1f] text-xl font-bold">Finalize EOD Toko</div>
                        <div class="text-[#555] mt-1">
                            Business date: <span class="text-[#1f1f1f] font-semibold">{{ businessDate }}</span>
                        </div>
                        <div class="text-[#555] text-sm mt-1">
                            Syarat finalize: semua station yang terlibat hari itu harus sudah melakukan Station Close.
                        </div>
                    </div>

                    <div v-if="alreadyFinalized"
                        class="px-4 py-2 rounded bg-[#f0f0f0] border border-[#9c9c9c] text-[#1f1f1f] text-sm">
                        Sudah finalize
                        <div v-if="final?.finalized_at" class="text-xs text-[#555] mt-1">
                            finalized_at: {{ final.finalized_at }}
                        </div>
                    </div>

                    <div v-else class="px-4 py-2 rounded border"
                        :class="canFinalize ? 'bg-[#e1f3e1] border-[#9c9c9c] text-[#1f1f1f]' : 'bg-[#fff1d6] border-[#9c9c9c] text-[#1f1f1f]'">
                        {{ canFinalize ? "Siap Finalize" : "Belum bisa finalize" }}
                    </div>
                </div>
            </div>

            <!-- Store Summary -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded p-5">
                    <div class="text-[#555] text-sm">Total Sales</div>
                    <div class="text-[#1f1f1f] text-2xl font-bold mt-1">{{ currency(storeSummary.totalSales) }}</div>
                    <div class="text-[#555] text-sm mt-2">
                        Sales count: <span class="text-[#1f1f1f] font-semibold">{{ storeSummary.salesCount }}</span>
                    </div>
                </div>

                <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded p-5">
                    <div class="text-[#555] text-sm">Total Sales Return</div>
                    <div class="text-[#1f1f1f] text-2xl font-bold mt-1">{{ currency(storeSummary.totalSalesReturn) }}</div>
                    <div class="text-[#555] text-sm mt-2">
                        Station count: <span class="text-[#1f1f1f] font-semibold">{{ storeSummary.stationCount }}</span>
                    </div>
                </div>

                <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded p-5">
                    <div class="text-[#555] text-sm">Discount & Tax</div>
                    <div class="text-[#1f1f1f] font-bold mt-2">Discount: {{ currency(storeSummary.totalDiscount) }}</div>
                    <div class="text-[#1f1f1f] font-bold mt-1">Tax: {{ currency(storeSummary.totalTax) }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded p-5">
                    <div class="text-[#555] text-sm">Cash Counted Total</div>
                    <div class="text-[#1f1f1f] text-2xl font-bold mt-1">{{ currency(storeSummary.cashCountedTotal) }}</div>
                </div>

                <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded p-5">
                    <div class="text-[#555] text-sm">Expected Cash Total</div>
                    <div class="text-[#1f1f1f] text-2xl font-bold mt-1">{{ currency(storeSummary.expectedCashTotal) }}</div>
                </div>

                <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded p-5">
                    <div class="text-[#555] text-sm">Variance Total</div>
                    <div class="text-xl font-bold mt-1 text-[#1f1f1f]">
                        {{ currency(storeSummary.varianceTotal) }}
                    </div>
                </div>
            </div>

            <!-- Stations table -->
            <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded p-6">
                <div class="text-[#1f1f1f] text-lg font-semibold mb-4">Station Terlibat</div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-[#555] border-b border-[#9c9c9c]">
                                <th class="text-left py-3 pr-4">Station</th>
                                <th class="text-left py-3 pr-4">Status</th>
                                <th class="text-left py-3 pr-4">Cash Counted</th>
                                <th class="text-left py-3 pr-4">Expected</th>
                                <th class="text-left py-3 pr-4">Variance</th>
                                <th class="text-left py-3 pr-4">Closed By</th>
                            </tr>
                        </thead>

                        <tbody class="text-[#1f1f1f]">
                            <tr v-for="st in stations" :key="st.id" class="border-b border-[#d0d0d0]">
                                <td class="py-3 pr-4">
                                    <div class="text-[#1f1f1f] font-semibold">{{ st.name }}</div>
                                    <div class="text-[#555] text-xs">{{ st.device_identifier }}</div>
                                </td>

                                <td class="py-3 pr-4">
                                    <span class="px-2 py-1 rounded text-xs border"
                                        :class="st.is_closed ? 'bg-[#e1f3e1] border-[#9c9c9c] text-[#1f1f1f]' : 'bg-[#fff1d6] border-[#9c9c9c] text-[#1f1f1f]'">
                                        {{ st.is_closed ? "Closed" : "Not Closed" }}
                                    </span>
                                </td>

                                <td class="py-3 pr-4">
                                    <span v-if="st.closing">{{ currency(st.closing.cash_counted) }}</span>
                                    <span v-else class="text-[#555]">-</span>
                                </td>

                                <td class="py-3 pr-4">
                                    <span v-if="st.closing">{{ currency(st.closing.expected_cash) }}</span>
                                    <span v-else class="text-[#555]">-</span>
                                </td>

                                <td class="py-3 pr-4">
                                    <span v-if="st.closing">{{ currency(st.closing.variance) }}</span>
                                    <span v-else class="text-[#555]">-</span>
                                </td>

                                <td class="py-3 pr-4">
                                    <span v-if="st.closing">{{ st.closing.closed_by }}</span>
                                    <span v-else class="text-[#555]">-</span>
                                </td>
                            </tr>

                            <tr v-if="stations.length === 0">
                                <td colspan="6" class="py-6 text-center text-[#555]">
                                    Tidak ada station yang terlibat pada tanggal ini.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="!canFinalize" class="mt-4 text-[#555] text-sm">
                    Finalize tidak bisa dilakukan: pastikan semua station terlibat sudah Station Close dan tidak ada
                    shift open.
                </div>
            </div>

            <!-- Finalize form -->
            <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded p-6">
                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <InputLabel for="authorization_password"
                            value="Password Otorisasi (Authorization: 'Tutup Harian')" class="text-[#1f1f1f]" />
                        <TextInput id="authorization_password" type="password"
                            class="mt-1 block w-full bg-white border-[#9c9c9c] focus:ring-blue-500 focus:border-blue-500 rounded"
                            v-model="form.authorization_password" :disabled="alreadyFinalized || !canFinalize"
                            required />
                        <InputError class="mt-2 text-red-600" :message="form.errors.authorization_password" />
                    </div>

                    <div>
                        <InputLabel for="notes" value="Catatan (opsional)" class="text-[#1f1f1f]" />
                        <textarea id="notes" v-model="form.notes" :disabled="alreadyFinalized || !canFinalize"
                            class="mt-1 block w-full bg-white border border-[#9c9c9c] focus:ring-blue-500 focus:border-blue-500 rounded text-[#1f1f1f]"
                            rows="3" placeholder="Catatan finalize..." />
                        <InputError class="mt-2 text-red-600" :message="form.errors.notes" />
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <Link :href="route('eod.index')"
                            class="px-4 py-2 rounded bg-[#e9e9e9] border border-[#9c9c9c] text-[#1f1f1f] text-xs font-semibold hover:bg-white">
                            Riwayat EOD
                        </Link>

                        <button type="submit"
                            class="inline-flex items-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] px-4 py-2 text-xs font-semibold shadow-sm hover:bg-white transition-colors"
                            :disabled="form.processing || alreadyFinalized || !canFinalize">
                            {{ alreadyFinalized ? "Sudah Finalize" : "Finalize EOD" }}
                        </button>
                    </div>

                    <div v-if="alreadyFinalized" class="text-[#555] text-sm">
                        Data finalize sudah tersimpan untuk tanggal ini.
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
