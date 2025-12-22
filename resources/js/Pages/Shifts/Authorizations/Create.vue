<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

// === Tambahkan preset ini ===
const AUTH_PRESETS = [
    { value: 'Buka Shift', hint: 'Dipakai saat buka shift' },
    { value: 'Tutup Shift', hint: 'Dipakai saat tutup shift' },
    { value: 'Tutup Harian', hint: 'Dipakai untuk EOD: Station Close & Finalize' },
    // opsional lain kalau Anda butuh nanti:
    // { value: 'Void Penjualan', hint: 'Batalkan transaksi' },
    // { value: 'Hapus Transaksi', hint: 'Hapus data tertentu' },
];

const form = useForm({
    name: '',
    password: '',
});

const submit = () => {
    form.post(route('shifts.authorizations.store'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>

    <Head title="Tambah Authorization" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">Tambah Authorization</h2>
        </template>

        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl p-6 shadow-xl">
                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <InputLabel for="name" value="Nama Authorization" />

                        <!-- Ubah TextInput menjadi Select -->
                        <select id="name" v-model="form.name" required
                            class="mt-1 block w-full bg-white/5 border border-white/20 text-white rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            <option value="" disabled>Pilih jenis authorization...</option>
                            <option v-for="p in AUTH_PRESETS" :key="p.value" :value="p.value">
                                {{ p.value }}
                            </option>
                        </select>

                        <div v-if="form.name" class="text-xs text-gray-400 mt-2">
                            {{AUTH_PRESETS.find(x => x.value === form.name)?.hint}}
                        </div>

                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="password" value="Password" />
                        <TextInput id="password" v-model="form.password" type="password" class="mt-1 block w-full"
                            placeholder="Masukkan password" required />
                        <InputError :message="form.errors.password" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end">
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Simpan
                        </PrimaryButton>
                    </div>
                </form>

                <!-- Tips penting -->
                <div class="mt-6 text-xs text-gray-400">
                    EOD memakai authorization bernama <span class="text-white font-semibold">"Tutup Harian"</span>.
                    Pastikan Anda membuatnya agar halaman Station Close dan Finalize bisa jalan.
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
