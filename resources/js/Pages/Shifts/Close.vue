<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { computed } from "vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    activeShift: Object,
    totalSales: Number,
});

const form = useForm({
    final_cash: 0,
    authorization_password: "",
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(value);
};

// Hitung selisih secara real-time
const variance = computed(() => {
    const expectedCash = props.activeShift.initial_cash + props.totalSales;
    return form.final_cash - expectedCash;
});

const submit = () => {
    if (
        confirm(
            "Apakah Anda yakin ingin menutup shift ini? Aksi ini tidak dapat dibatalkan."
        )
    ) {
        form.post(route("shifts.close.store"));
    }
};
</script>

<template>

    <Head title="Tutup Shift" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">
                Tutup Shift
            </h2>
        </template>

        <div
            class="mx-auto max-w-2xl bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl shadow-lg p-8 space-y-6">
            <div class="backdrop-blur-md border border-white/10 rounded-2xl shadow-lg p-8 space-y-6">
                <h3 class="font-medium text-l text-white leading-tight mb-4 border-b pb-4">
                    Ringkasan Shift Aktif
                </h3>

                <div class="grid grid-cols-2 gap-4 text-sm text-white mb-6">
                    <div>
                        <strong>Kasir:</strong> {{ $page.props.auth.user.name }}
                    </div>
                    <div>
                        <strong>Waktu Mulai:</strong>
                        {{
                            new Date(activeShift.start_time).toLocaleString(
                                "id-ID"
                            )
                        }}
                    </div>
                    <div>
                        <strong>Modal Awal:</strong>
                        {{ formatCurrency(activeShift.initial_cash) }}
                    </div>
                    <div class="font-bold">
                        <strong>Total Penjualan Sistem:</strong>
                        {{ formatCurrency(totalSales) }}
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="space-y-4 text-wht">
                        <div>
                            <InputLabel for="final_cash" value="Uang Tunai Akhir di Laci (Rp)" class="text-white" />
                            <TextInput id="final_cash" type="number"
                                class="mt-1 block w-full bg-white/5 border-white/20 focus:ring-blue-500 focus:border-blue-500 rounded-lg"
                                v-model="form.final_cash" required />
                            <InputError class="mt-2" :message="form.errors.final_cash" />
                        </div>

                        <div class="flex justity-between items-center font-bold border-white/10 focus:border-blue-500 focus-ring-blue-500 rounded-xl text-center p-2"
                            :class="variance === 0
                                ? 'bg-gray-100'
                                : variance > 0
                                    ? 'bg-green-100'
                                    : 'bg-red-100'
                                ">
                            <div
                                class="mt-1 block w-full bg-white/5 border-white/20 focus:ring-blue-500 focus:border-blue-500 rounded-lg">
                                <span>Selisih (Uang di Laci - (Modal +
                                    Penjualan)) </span>
                                <span :class="variance === 0
                                    ? 'text-gray-800'
                                    : variance > 0
                                        ? 'text-green-600'
                                        : 'text-red-600'
                                    ">
                                    {{ formatCurrency(variance) }}
                                </span>
                            </div>
                        </div>

                        <div>
                            <InputLabel for="authorization_password" value="Password Verifikasi (Default: 123456)"
                                class="text-white" />
                            <TextInput id="authorization_password" type="password"
                                class="mt-1 block w-full bg-white/5 border-white/20 focus:ring-blue-500 focus:border-blue-500 rounded-lg"
                                v-model="form.authorization_password" required />
                            <InputError class="mt-2" :message="form.errors.authorization_password" />
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button type="submit" :disabled="form.processing"
                            class="inline-block bg-gradient-to-r from-indigo-400 to-pink-400 text-white font-bold py-3 px-10 rounded-full text-lg shadow-xl hover:scale-105 hover:from-indigo-500 hover:to-pink-500 transition-transform duration-200">
                            Verifikasi dan Tutup Shift
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
