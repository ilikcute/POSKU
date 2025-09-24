<template>
    <AuthenticatedLayout>

        <Head title="Master Kategori" />
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">
                Master Kategori
            </h2>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl shadow-lg overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-4 border-b border-gray-600/60">
                        <h3 class="text-lg font-medium text-white">Daftar Kategori</h3>
                        <button @click="openModal(false)"
                            class="inline-block bg-gradient-to-r from-green-400 to-emerald-400 text-white font-bold py-3 px-10 rounded-full text-sm md:text-base shadow-xl hover:scale-105 hover:from-green-500 hover:to-emerald-500 transition-transform duration-200">
                            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            Tambah Kategori
                        </button>
                    </div>
                    <div class="flex items-center justify-between px-4 py-4 border-b border-gray-600/60 gap-4">
                        <TextInput v-model="search" type="text" placeholder="Cari nama..."
                            class="w-full max-w-xs bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500" />
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm text-gray-200 divide-y divide-gray-600">
                            <thead class="bg-gray-800/70">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Nama
                                        Kategori
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                <tr v-for="category in categories.data" :key="category.id"
                                    class="hover:bg-gray-700/50 transition-colors duration-150">
                                    <td class="px-4 py-3 whitespace-nowrap">{{ category.name }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end space-x-2">
                                            <button @click="openModal(true, category)"
                                                class="inline-block bg-gradient-to-r from-blue-400 to-blue-500 text-white font-bold py-2 px-4 rounded-full text-xs shadow-lg hover:scale-105 hover:from-blue-500 hover:to-blue-600 transition-transform duration-200">
                                                <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </button>
                                            <button @click="deleteCategory(category.id)"
                                                class="inline-block bg-gradient-to-r from-red-400 to-red-500 text-white font-bold py-2 px-4 rounded-full text-xs shadow-lg hover:scale-105 hover:from-red-500 hover:to-red-600 transition-transform duration-200">
                                                <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="categories.data.length === 0">
                                    <td colspan="2" class="px-6 py-12 text-center text-gray-400">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-12 h-12 text-gray-500 mb-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4">
                                                </path>
                                            </svg>
                                            <p class="text-lg font-medium text-gray-300">Belum ada data kategori</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Pagination :links="categories.links" />
                </div>
            </div>
        </div>

        <Modal :show="isModalOpen" @close="closeModal">
            <div
                class="p-8 bg-gray-900/70 backdrop-blur-md border border-white/10 rounded-2xl shadow-lg max-w-4xl mx-auto space-y-6 text-white">
                <h2 class="text-lg font-semibold">
                    {{ isEditMode ? "Edit Kategori" : "Tambah Kategori Baru" }}
                </h2>
                <form @submit.prevent="saveCategory" class="mt-6 grid grid-cols-1 gap-6">
                    <div>
                        <InputLabel for="name" value="Nama Kategori" class="text-gray-300" />
                        <TextInput id="name" v-model="form.name"
                            class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500"
                            required />
                        <InputError :message="form.errors.name" class="mt-2 text-red-400" />
                    </div>
                    <div class="flex justify-end gap-3 mt-4">
                        <button type="button" @click="closeModal"
                            class="inline-block bg-gradient-to-r from-gray-500 to-gray-600 text-white font-bold py-2 px-6 rounded-full text-sm shadow-lg hover:scale-105 hover:from-gray-600 hover:to-gray-700 transition-transform duration-200">
                            <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Batal
                        </button>
                        <button type="submit" :disabled="form.processing"
                            class="inline-block bg-gradient-to-r from-green-400 to-emerald-400 text-white font-bold py-2 px-6 rounded-full text-sm shadow-lg hover:scale-105 hover:from-green-500 hover:to-emerald-500 transition-transform duration-200"
                            :class="{ 'opacity-25': form.processing }">
                            <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, router } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import { debounce } from "lodash";

// Import komponen yang sudah kita buat sebelumnya
import Modal from "@/Components/Modal.vue";
import Pagination from "@/Components/Pagination.vue";
// PrimaryButton and SecondaryButton are not used in the new template, but keeping them for now.
// import PrimaryButton from "@/Components/PrimaryButton.vue";
// import SecondaryButton from "@/Components/SecondaryButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    categories: Object, // Changed from Array to Object to match pagination structure
    filters: Object,
});

const isModalOpen = ref(false);
const isEditMode = ref(false);
const search = ref(props.filters.search);

const form = useForm({
    id: null,
    name: "",
});

watch(
    search,
    debounce((value) => {
        router.get(
            route("master.categories.index"),
            { search: value },
            {
                preserveState: true,
                replace: true,
            }
        );
    }, 300)
);

const openModal = (editMode = false, category = null) => {
    isEditMode.value = editMode;
    if (editMode && category) {
        form.id = category.id;
        form.name = category.name;
    } else {
        form.reset();
    }
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const saveCategory = () => { // Renamed from saveItem
    if (isEditMode.value) {
        form.patch(route("master.categories.update", form.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route("master.categories.store"), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteCategory = (id) => { // Renamed from deleteItem
    if (confirm("Apakah Anda yakin ingin menghapus kategori ini?")) {
        useForm({}).delete(route("master.categories.destroy", id)); // Changed to useForm({}).delete
    }
};
</script>