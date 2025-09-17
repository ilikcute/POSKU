<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, router } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import { debounce } from "lodash";

// Import komponen yang sudah kita buat sebelumnya
import Modal from "@/Components/Modal.vue";
import Pagination from "@/Components/Pagination.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    categories: Object,
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

const saveItem = () => {
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

const deleteItem = (id) => {
    if (confirm("Apakah Anda yakin ingin menghapus item ini?")) {
        router.delete(route("master.categories.destroy", id));
    }
};
</script>

<template>
    <Head title="Master Kategori" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Master Kategori
            </h2>
        </template>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <button
                        @click="openModal(false)"
                        class="inline-flex items-center px-4 py-2 border border-indigo-300 text-xs font-medium rounded text-indigo-600 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Kategori
                    </button>
                    <TextInput
                        v-model="search"
                        type="text"
                        placeholder="Cari nama..."
                        class="w-80 px-4 py-2 border border-gray-300 rounded text-xs font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    />
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-bold text-gray-700 uppercase tracking-wide" style="font-family: 'Poppins', sans-serif;">Nama Kategori</th>
                                <th class="px-6 py-3 text-center text-sm font-bold text-gray-700 uppercase tracking-wide" style="font-family: 'Poppins', sans-serif;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="category in categories.data" :key="category.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ category.name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-2">
                                        <!-- Tombol Edit -->
                                        <button 
                                            @click="openModal(true, category)" 
                                            class="inline-flex items-center px-2 py-1 border border-gray-300 text-xs font-medium rounded text-indigo-600 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                        >
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                            Edit
                                        </button>
                                        <!-- Tombol Hapus -->
                                        <button 
                                            @click="deleteItem(category.id)" 
                                            class="inline-flex items-center px-2 py-1 border border-red-300 text-xs font-medium rounded text-red-600 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                        >
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="categories.data.length === 0">
                                <td colspan="2" class="px-6 py-4 text-center text-gray-500">
                                    Tidak ada data.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination :links="categories.links" />
            </div>
        </div>

        <Modal :show="isModalOpen" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    {{ isEditMode ? "Edit Kategori" : "Tambah Kategori" }}
                </h2>
                <form @submit.prevent="saveItem" class="mt-6 space-y-4">
                    <div>
                        <InputLabel for="name" value="Nama Kategori" />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button type="button" @click="closeModal"
                            class="inline-flex items-center px-2 py-1 border border-gray-300 text-xs font-medium rounded text-indigo-600 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Batal
                        </button>
                        <button type="submit" :disabled="form.processing"
                            class="ms-3 inline-flex items-center px-2 py-1 border border-indigo-300 text-xs font-medium rounded text-indigo-600 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
