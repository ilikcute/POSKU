<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputError from "@/Components/InputError.vue";

const form = useForm({
    import_file: null,
});

const submit = () => {
    form.post(route("master.products.import.store"), {
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Impor Produk" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Impor Produk
            </h2>
        </template>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6"
                >
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Langkah 1: Download Template
                    </h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Download file template Excel untuk memastikan format
                        data yang Anda masukkan sudah benar. Isi data produk
                        sesuai dengan kolom yang tersedia.
                    </p>
                    <a
                        :href="route('master.products.import.template')"
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:outline-none transition"
                    >
                        Download Template
                    </a>
                </div>

                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6"
                >
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Langkah 2: Upload File
                    </h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Pilih file Excel yang sudah Anda isi datanya. Sistem
                        akan secara otomatis menambahkan produk baru ke dalam
                        database.
                    </p>
                    <form @submit.prevent="submit">
                        <input
                            type="file"
                            @input="form.import_file = $event.target.files[0]"
                            class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none"
                        />
                        <progress
                            v-if="form.progress"
                            :value="form.progress.percentage"
                            max="100"
                            class="w-full mt-2"
                        >
                            {{ form.progress.percentage }}%
                        </progress>
                        <InputError
                            class="mt-2"
                            :message="form.errors.import_file"
                        />

                        <div class="mt-4">
                            <PrimaryButton :disabled="form.processing">
                                Upload dan Impor
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
