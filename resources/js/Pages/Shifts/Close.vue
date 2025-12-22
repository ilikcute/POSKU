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
            <h2 class="font-semibold text-xl text-white leading-tight">Tutup Shift</h2>
        </template>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- LIST SHIFT OPEN KEMARIN -->
            <div v-if="mode === 'force_previous'"
                class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl p-5">
                <h3 class="text-white font-semibold mb-3">
                    Shift Hari Sebelumnya yang Belum Ditutup
                </h3>

                <div v-if="unclosedShifts.length" class="overflow-x-auto">
                    <table class="min-w-full text-sm text-gray-200">
                        <thead class="border-b border-white/10 text-xs uppercase text-white/80">
                            <tr>
                                <th class="py-3 px-3 text-left">Pemilik Shift</th>
                                <th class="py-3 px-3 text-left">Station/Device</th>
                                <th class="py-3 px-3 text-left">Mulai</th>
                                <th class="py-3 px-3 text-left">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10">
                            <tr v-for="s in unclosedShifts" :key="s.id" class="hover:bg-white/5 transition"
                                :class="s.id === activeShift.id ? 'bg-white/5' : ''">
                                <td class="py-3 px-3">
                                    <div class="font-medium text-white">
                                        {{ s.user?.name ?? `User#${s.user_id}` }}
                                    </div>
                                    <div class="text-xs text-gray-400">
                                        Shift Code: {{ s.shift_code ?? '-' }}
                                    </div>
                                </td>

                                <td class="py-3 px-3">
                                    <div class="text-white">
                                        {{ s.station?.name ?? `Station#${s.station_id ?? '-'}` }}
                                    </div>
                                    <div class="text-xs text-gray-400">
                                        Device: {{ s.device_id ?? s.station?.device_identifier ?? '-' }}
                                    </div>
                                </td>

                                <td class="py-3 px-3 text-gray-300">
                                    {{ formatDateTime(s.start_time) }}
                                </td>

                                <td class="py-3 px-3">
                                    <button type="button"
                                        class="px-3 py-2 rounded-lg bg-white/10 hover:bg-white/20 border border-white/10 text-white text-xs font-semibold transition"
                                        @click="selectShift(s.id)">
                                        Pilih
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="text-gray-300 text-sm">
                    Tidak ada shift hari sebelumnya yang masih open.
                </div>
            </div>

            <!-- FORM CLOSE SHIFT TERPILIH -->
            <div
                class="mx-auto max-w-xl bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl shadow-lg p-8 space-y-6">
                <div class="text-center">
                    <h1 class="text-3xl font-bold text-white">Tutup Shift</h1>
                    <p class="text-sm text-gray-300 mt-2">
                        Anda akan menutup shift milik:
                        <span class="font-semibold text-white">
                            {{ activeShift.user?.name ?? `User#${activeShift.user_id}` }}
                        </span>
                    </p>
                </div>

                <!-- ringkasan -->
                <div class="rounded-xl border border-white/10 bg-white/5 p-4 text-sm text-gray-200 space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Waktu Mulai</span>
                        <span class="text-white">{{ formatDateTime(activeShift.start_time) }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-400">Modal Awal</span>
                        <span class="text-emerald-300 font-semibold">{{ formatCurrency(activeShift.initial_cash)
                        }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-400">Total Penjualan Sistem</span>
                        <span class="text-blue-300 font-semibold">{{ formatCurrency(summary.totalSales) }}</span>
                    </div>

                    <div class="flex justify-between border-t border-white/10 pt-2 mt-2">
                        <span class="text-gray-400">Expected Cash (Backend)</span>
                        <span class="text-white font-semibold">{{ formatCurrency(summary.expectedCash) }}</span>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <!-- hidden shift_id (tetap dikirim) -->
                    <input type="hidden" :value="form.shift_id" />

                    <div>
                        <InputLabel for="final_cash" value="Uang Tunai Akhir di Laci (Rp)" class="text-white" />
                        <TextInput id="final_cash" type="number"
                            class="mt-1 block w-full bg-white/5 border-white/20 focus:ring-blue-500 focus:border-blue-500 rounded-lg"
                            v-model="form.final_cash" required />
                        <InputError class="mt-2" :message="form.errors.final_cash" />
                    </div>

                    <!-- Variance -->
                    <div class="rounded-xl border border-white/10 p-3 text-center font-semibold"
                        :class="variance === 0 ? 'bg-white/10' : variance > 0 ? 'bg-emerald-500/10' : 'bg-red-500/10'">
                        <div class="text-gray-300 text-sm">
                            Selisih (Uang di Laci - Expected Cash)
                        </div>
                        <div
                            :class="variance === 0 ? 'text-white' : variance > 0 ? 'text-emerald-300' : 'text-red-300'">
                            {{ formatCurrency(variance) }}
                        </div>
                    </div>

                    <div>
                        <InputLabel for="authorization_password" value="Password Verifikasi" class="text-white" />
                        <TextInput id="authorization_password" type="password"
                            class="mt-1 block w-full bg-white/5 border-white/20 focus:ring-blue-500 focus:border-blue-500 rounded-lg"
                            v-model="form.authorization_password" required />
                        <InputError class="mt-2" :message="form.errors.authorization_password" />
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" :disabled="form.processing"
                            class="inline-block bg-gradient-to-r from-indigo-400 to-pink-400 text-white font-bold py-3 px-8 rounded-full text-base shadow-xl hover:scale-105 hover:from-indigo-500 hover:to-pink-500 transition-transform duration-200 disabled:opacity-60 disabled:hover:scale-100">
                            Verifikasi dan Tutup Shift
                        </button>
                    </div>

                    <InputError class="mt-2" :message="form.errors.shift_id" />
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
