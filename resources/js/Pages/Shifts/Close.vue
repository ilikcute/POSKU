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
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Tutup Shift
            </h2>
        </template>

        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 py-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3
                    class="text-lg font-medium text-gray-900 mb-4 border-b pb-4"
                >
                    Ringkasan Shift Aktif
                </h3>

                <div class="grid grid-cols-2 gap-4 text-sm mb-6">
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

                <form @submit.prevent="submit">
                    <div class="space-y-4">
                        <div>
                            <InputLabel
                                for="final_cash"
                                value="Uang Tunai Akhir di Laci (Rp)"
                            />
                            <TextInput
                                id="final_cash"
                                type="number"
                                class="mt-1 block w-full"
                                v-model="form.final_cash"
                                required
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.final_cash"
                            />
                        </div>

                        <div
                            class="p-4 rounded-md"
                            :class="
                                variance === 0
                                    ? 'bg-gray-100'
                                    : variance > 0
                                    ? 'bg-green-100'
                                    : 'bg-red-100'
                            "
                        >
                            <div
                                class="flex justify-between items-center font-bold"
                            >
                                <span
                                    >Selisih (Uang di Laci - (Modal +
                                    Penjualan))</span
                                >
                                <span
                                    :class="
                                        variance === 0
                                            ? 'text-gray-800'
                                            : variance > 0
                                            ? 'text-green-600'
                                            : 'text-red-600'
                                    "
                                >
                                    {{ formatCurrency(variance) }}
                                </span>
                            </div>
                        </div>

                        <div>
                            <InputLabel
                                for="authorization_password"
                                value="Password Verifikasi (Default: 123456)"
                            />
                            <TextInput
                                id="authorization_password"
                                type="password"
                                class="mt-1 block w-full"
                                v-model="form.authorization_password"
                                required
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.authorization_password"
                            />
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <PrimaryButton
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            Verifikasi dan Tutup Shift
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
