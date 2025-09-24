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
                    <h2 class="font-semibold text-xl text-white leading-tight">
                        Impor Produk
                    </h2>
                    <p class="text-sm text-gray-400 mt-1">
                        Gunakan template resmi, lalu unggah untuk menambahkan produk secara massal.
                    </p>
                </div>

                <Link :href="route('master.products.index')" class="inline-flex">
                    <button
                        class="inline-flex items-center bg-gradient-to-r from-blue-400 to-blue-500 text-white font-bold py-3 px-5 rounded-full text-sm shadow-lg hover:scale-105 hover:from-blue-500 hover:to-blue-600 transition-transform duration-200"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Kembali ke Daftar
                    </button>
                </Link>
            </div>
        </template>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl p-6 shadow-xl space-y-4">
                    <h3 class="text-lg font-semibold text-white">
                        Langkah 1: Download Template
                    </h3>
                    <p class="text-sm text-gray-300">
                        Unduh file Excel template untuk memastikan struktur kolom sesuai. Isi data produk sesuai petunjuk
                        sebelum melakukan impor.
                    </p>
                    <a
                        :href="route('master.products.import.template')"
                        class="inline-flex items-center bg-gradient-to-r from-green-400 to-emerald-400 text-white font-bold py-3 px-5 rounded-full text-sm shadow-lg hover:scale-105 hover:from-green-500 hover:to-emerald-500 transition-transform duration-200"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M4 12l4 4m0 0l4-4m-4 4V4" />
                        </svg>
                        Download Template
                    </a>
                    <p class="text-xs text-gray-500">
                        Template tersedia dalam format .xlsx dan mendukung hingga ribuan baris data.
                    </p>
                </div>

                <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl p-6 shadow-xl space-y-5">
                    <h3 class="text-lg font-semibold text-white">
                        Langkah 2: Unggah File
                    </h3>
                    <p class="text-sm text-gray-300">
                        Pilih file Excel yang sudah Anda lengkapi. Sistem akan menambahkan atau memperbarui produk secara otomatis.
                    </p>

                    <form @submit.prevent="submit" class="space-y-5">
                        <label class="block">
                            <span class="text-sm text-gray-300">Pilih File Template</span>
                            <input
                                type="file"
                                accept=".xls,.xlsx"
                                @change="handleFileChange"
                                class="mt-2 w-full text-sm text-gray-200 bg-white/5 border border-white/20 rounded-lg cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded-l-lg file:border-0 file:bg-blue-500/80 file:text-white hover:file:bg-blue-500"
                            />
                        </label>

                        <p v-if="form.import_file" class="text-xs text-gray-400">
                            File terpilih: <span class="text-gray-200">{{ form.import_file.name }}</span>
                        </p>

                        <progress
                            v-if="form.progress"
                            :value="form.progress.percentage"
                            max="100"
                            class="w-full h-2 rounded-full overflow-hidden bg-gray-800"
                        >
                            {{ form.progress.percentage }}%
                        </progress>

                        <InputError class="text-red-400" :message="form.errors.import_file" />

                        <div class="flex justify-end gap-3">
                            <button
                                type="submit"
                                :disabled="form.processing || !form.import_file"
                                class="inline-flex items-center bg-gradient-to-r from-purple-400 to-pink-400 text-white font-bold py-3 px-6 rounded-full text-sm shadow-lg hover:scale-105 hover:from-purple-500 hover:to-pink-500 transition-transform duration-200"
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
