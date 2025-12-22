<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';

const form = useForm({
    import_file: null,
});

const handleFileChange = (event) => {
    form.import_file = event.target.files[0];
};

const submit = () => {
    form.post(route('master.products.import.store'), {
        onSuccess: () => form.reset('import_file'),
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Impor Produk" />

        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-lg text-[#1f1f1f] leading-tight">
                        Impor Produk
                    </h2>
                    <p class="text-xs text-[#555] mt-1">
                        Gunakan template resmi, lalu unggah untuk menambahkan produk secara massal.
                    </p>
                </div>

                <Link :href="route('master.products.index')" class="inline-flex">
                    <button
                        class="inline-flex items-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] px-4 py-2 text-xs font-semibold shadow-sm hover:bg-white transition-colors"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Kembali ke Daftar
                    </button>
                </Link>
            </div>
        </template>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded p-4 shadow-sm space-y-4">
                    <h3 class="text-sm font-semibold text-[#1f1f1f]">
                        Langkah 1: Download Template
                    </h3>
                    <p class="text-sm text-[#555]">
                        Unduh file Excel template untuk memastikan struktur kolom sesuai. Isi data produk sesuai petunjuk
                        sebelum melakukan impor.
                    </p>
                    <a
                        :href="route('master.products.import.template')"
                        class="inline-flex items-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] px-4 py-2 text-xs font-semibold shadow-sm hover:bg-white transition-colors"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M4 12l4 4m0 0l4-4m-4 4V4" />
                        </svg>
                        Download Template
                    </a>
                    <p class="text-xs text-[#555]">
                        Template tersedia dalam format .xlsx dan mendukung hingga ribuan baris data.
                    </p>
                </div>

                <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded p-4 shadow-sm space-y-5">
                    <h3 class="text-sm font-semibold text-[#1f1f1f]">
                        Langkah 2: Unggah File
                    </h3>
                    <p class="text-sm text-[#555]">
                        Pilih file Excel yang sudah Anda lengkapi. Sistem akan menambahkan atau memperbarui produk secara otomatis.
                    </p>

                    <form @submit.prevent="submit" class="space-y-5">
                        <label class="block">
                            <span class="text-sm text-[#1f1f1f]">Pilih File Template</span>
                            <input
                                type="file"
                                accept=".xls,.xlsx"
                                @change="handleFileChange"
                                class="mt-2 w-full text-sm text-[#1f1f1f] bg-white border border-[#9c9c9c] rounded cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded-l file:border-0 file:bg-[#e9e9e9] file:text-[#1f1f1f] hover:file:bg-white"
                            />
                        </label>

                        <p v-if="form.import_file" class="text-xs text-[#555]">
                            File terpilih: <span class="text-[#1f1f1f]">{{ form.import_file.name }}</span>
                        </p>

                        <progress
                            v-if="form.progress"
                            :value="form.progress.percentage"
                            max="100"
                            class="w-full h-2 rounded-full overflow-hidden bg-[#d0d0d0]"
                        >
                            {{ form.progress.percentage }}%
                        </progress>

                        <InputError class="text-red-600" :message="form.errors.import_file" />

                        <div class="flex justify-end gap-3">
                            <button
                                type="submit"
                                :disabled="form.processing || !form.import_file"
                                class="inline-flex items-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] px-4 py-2 text-xs font-semibold shadow-sm hover:bg-white transition-colors"
                                :class="{ 'opacity-25': form.processing || !form.import_file }"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v16h16V8.828A2 2 0 0019.414 7L15 2.586A2 2 0 0013.586 2H6a2 2 0 00-2 2z" />
                                </svg>
                                Upload dan Impor
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
