<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, router } from "@inertiajs/vue3";
import { computed, watch } from "vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    unclosedShifts: {
        type: Array,
        default: () => [],
    },
    activeShift: {
        type: Object,
        required: true,
    },
    summary: {
        type: Object,
        default: () => ({ totalSales: 0, expectedCash: 0 }),
    },
    mode: {
        type: String,
        default: "normal_today",
    },

});

const form = useForm({
    shift_id: props.activeShift?.id ?? null,
    final_cash: 0,
    authorization_password: "",
});

watch(
    () => props.activeShift?.id,
    (newId) => {
        if (newId) form.shift_id = newId;
    },
    { immediate: true }
);

const formatCurrency = (value) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(Number(value ?? 0));
};

const formatDateTime = (value) => {
    if (!value) return "-";
    return new Date(value).toLocaleString("id-ID");
};

// variance = uang laci - expectedCash (expectedCash dari backend)
const variance = computed(() => {
    return Number(form.final_cash ?? 0) - Number(props.summary.expectedCash ?? 0);
});

// pilih shift lain (reload inertia dengan query shift_id)
const selectShift = (shiftId) => {
    router.get(
        route("shifts.close.form"),
        { shift_id: shiftId },
        { preserveState: true, preserveScroll: true, replace: true }
    );
};

const submit = () => {
    if (confirm("Apakah Anda yakin ingin menutup shift ini?")) {
        form.post(route("shifts.close.store"));
    }
};
</script>

<template>

    <Head title="Tutup Shift" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="font-semibold text-lg text-[#1f1f1f] leading-tight">Tutup Shift</h2>
                <p class="text-xs text-[#555]">Verifikasi dan tutup shift yang masih aktif.</p>
            </div>
        </template>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- LIST SHIFT OPEN KEMARIN -->
            <div v-if="mode === 'force_previous'"
                class="bg-[#f7f7f7] border border-[#9c9c9c] rounded p-4">
                <h3 class="text-[#1f1f1f] font-semibold mb-3">
                    Shift Hari Sebelumnya yang Belum Ditutup
                </h3>

                <div v-if="unclosedShifts.length" class="overflow-x-auto">
                    <table class="min-w-full text-sm text-[#1f1f1f]">
                        <thead class="border-b border-[#9c9c9c] text-xs uppercase text-[#1f1f1f] bg-[#efefef]">
                            <tr>
                                <th class="py-3 px-3 text-left">Pemilik Shift</th>
                                <th class="py-3 px-3 text-left">Station/Device</th>
                                <th class="py-3 px-3 text-left">Mulai</th>
                                <th class="py-3 px-3 text-left">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#d0d0d0]">
                            <tr v-for="s in unclosedShifts" :key="s.id" class="hover:bg-[#f7f7f7] transition"
                                :class="s.id === activeShift.id ? 'bg-[#f7f7f7]' : ''">
                                <td class="py-3 px-3">
                                    <div class="font-medium text-[#1f1f1f]">
                                        {{ s.user?.name ?? `User#${s.user_id}` }}
                                    </div>
                                    <div class="text-xs text-[#555]">
                                        Shift Code: {{ s.shift_code ?? '-' }}
                                    </div>
                                </td>

                                <td class="py-3 px-3">
                                    <div class="text-[#1f1f1f]">
                                        {{ s.station?.name ?? `Station#${s.station_id ?? '-'}` }}
                                    </div>
                                    <div class="text-xs text-[#555]">
                                        Device: {{ s.device_id ?? s.station?.device_identifier ?? '-' }}
                                    </div>
                                </td>

                                <td class="py-3 px-3 text-[#555]">
                                    {{ formatDateTime(s.start_time) }}
                                </td>

                                <td class="py-3 px-3">
                                    <button type="button"
                                        class="px-3 py-2 rounded bg-[#e9e9e9] hover:bg-white border border-[#9c9c9c] text-[#1f1f1f] text-xs font-semibold transition"
                                        @click="selectShift(s.id)">
                                        Pilih
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="text-[#555] text-sm">
                    Tidak ada shift hari sebelumnya yang masih open.
                </div>
            </div>

            <!-- FORM CLOSE SHIFT TERPILIH -->
            <div
                class="mx-auto max-w-xl bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-lg p-6 space-y-6">
                <div class="text-center">
                    <h1 class="text-2xl font-bold text-[#1f1f1f]">Tutup Shift</h1>
                    <p class="text-sm text-[#555] mt-2">
                        Anda akan menutup shift milik:
                        <span class="font-semibold text-[#1f1f1f]">
                            {{ activeShift.user?.name ?? `User#${activeShift.user_id}` }}
                        </span>
                    </p>
                </div>

                <!-- ringkasan -->
                <div class="rounded border border-[#9c9c9c] bg-white p-4 text-sm text-[#1f1f1f] space-y-2">
                    <div class="flex justify-between">
                        <span class="text-[#555]">Waktu Mulai</span>
                        <span class="text-[#1f1f1f]">{{ formatDateTime(activeShift.start_time) }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-[#555]">Modal Awal</span>
                        <span class="text-[#1f1f1f] font-semibold">{{ formatCurrency(activeShift.initial_cash)
                        }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-[#555]">Total Penjualan Sistem</span>
                        <span class="text-[#1f1f1f] font-semibold">{{ formatCurrency(summary.totalSales) }}</span>
                    </div>

                    <div class="flex justify-between border-t border-[#9c9c9c] pt-2 mt-2">
                        <span class="text-[#555]">Expected Cash (Backend)</span>
                        <span class="text-[#1f1f1f] font-semibold">{{ formatCurrency(summary.expectedCash) }}</span>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <!-- hidden shift_id (tetap dikirim) -->
                    <input type="hidden" :value="form.shift_id" />

                    <div>
                        <InputLabel for="final_cash" value="Uang Tunai Akhir di Laci (Rp)" class="text-[#1f1f1f]" />
                        <TextInput id="final_cash" type="number"
                            class="mt-1 block w-full bg-white border-[#9c9c9c] focus:ring-blue-500 focus:border-blue-500 rounded"
                            v-model="form.final_cash" required />
                        <InputError class="mt-2 text-red-600" :message="form.errors.final_cash" />
                    </div>

                    <!-- Variance -->
                    <div class="rounded border border-[#9c9c9c] p-3 text-center font-semibold"
                        :class="variance === 0 ? 'bg-white' : variance > 0 ? 'bg-[#e1f3e1]' : 'bg-[#ffe1e1]'">
                        <div class="text-[#555] text-sm">
                            Selisih (Uang di Laci - Expected Cash)
                        </div>
                        <div
                            :class="variance === 0 ? 'text-[#1f1f1f]' : variance > 0 ? 'text-[#1f1f1f]' : 'text-[#1f1f1f]'">
                            {{ formatCurrency(variance) }}
                        </div>
                    </div>

                    <div>
                        <InputLabel for="authorization_password" value="Password Verifikasi" class="text-[#1f1f1f]" />
                        <TextInput id="authorization_password" type="password"
                            class="mt-1 block w-full bg-white border-[#9c9c9c] focus:ring-blue-500 focus:border-blue-500 rounded"
                            v-model="form.authorization_password" required />
                        <InputError class="mt-2 text-red-600" :message="form.errors.authorization_password" />
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" :disabled="form.processing"
                            class="inline-flex items-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] py-2 px-5 text-xs font-semibold shadow-sm hover:bg-white transition-colors disabled:opacity-60">
                            Verifikasi dan Tutup Shift
                        </button>
                    </div>

                    <InputError class="mt-2 text-red-600" :message="form.errors.shift_id" />
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
