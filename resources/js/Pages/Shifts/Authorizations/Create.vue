<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
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
            <div>
                <h2 class="font-semibold text-lg text-[#1f1f1f] leading-tight">Tambah Authorization</h2>
                <p class="text-xs text-[#555]">Buat password verifikasi untuk Shift/EOD.</p>
            </div>
        </template>

        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded p-6 shadow-sm">
                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <InputLabel for="name" value="Nama Authorization" class="text-[#1f1f1f]" />

                        <!-- Ubah TextInput menjadi Select -->
                        <select id="name" v-model="form.name" required
                            class="mt-1 block w-full bg-white border border-[#9c9c9c] text-[#1f1f1f] rounded focus:ring-blue-500 focus:border-blue-500">
                            <option value="" disabled>Pilih jenis authorization...</option>
                            <option v-for="p in AUTH_PRESETS" :key="p.value" :value="p.value">
                                {{ p.value }}
                            </option>
                        </select>

                        <div v-if="form.name" class="text-xs text-[#555] mt-2">
                            {{AUTH_PRESETS.find(x => x.value === form.name)?.hint}}
                        </div>

                        <InputError :message="form.errors.name" class="mt-2 text-red-600" />
                    </div>

                    <div>
                        <InputLabel for="password" value="Password" class="text-[#1f1f1f]" />
                        <TextInput id="password" v-model="form.password" type="password"
                            class="mt-1 block w-full bg-white border border-[#9c9c9c] rounded text-[#1f1f1f]"
                            placeholder="Masukkan password" required />
                        <InputError :message="form.errors.password" class="mt-2 text-red-600" />
                    </div>

                    <div class="flex items-center justify-end">
                        <button type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing"
                            class="inline-flex items-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] px-4 py-2 text-xs font-semibold shadow-sm hover:bg-white transition-colors">
                            Simpan
                        </button>
                    </div>
                </form>

                <!-- Tips penting -->
                <div class="mt-6 text-xs text-[#555]">
                    EOD memakai authorization bernama <span class="text-[#1f1f1f] font-semibold">"Tutup Harian"</span>.
                    Pastikan Anda membuatnya agar halaman Station Close dan Finalize bisa jalan.
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
