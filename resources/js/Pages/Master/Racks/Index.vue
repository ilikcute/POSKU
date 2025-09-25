<template>
    <AuthenticatedLayout>

        <Head title="Master Rak" />

        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-xl text-white leading-tight">
                        Master Rak
                    </h2>
                    <p class="text-sm text-gray-400 mt-1">
                        Kelola rak penyimpanan dengan tampilan konsisten dan mudah.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="flex bg-gray-800/50 rounded-lg p-1 backdrop-blur-sm">
                        <button @click="viewMode = 'table'" :class="[
                            'px-3 py-2 rounded-md text-sm font-medium transition-all duration-200',
                            viewMode === 'table'
                                ? 'bg-white/20 text-white shadow-sm'
                                : 'text-gray-300 hover:text-white hover:bg-white/10'
                        ]">

                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 10h18M3 6h18m-9 10h9" />
                            </svg>
                            Table
                        </button>
                        <button @click="viewMode = 'cards'" :class="[
                            'px-3 py-2 rounded-md text-sm font-medium transition-all duration-200',
                            viewMode === 'cards'
                                ? 'bg-white/20 text-white shadow-sm'
                                : 'text-gray-300 hover:text-white hover:bg-white/10'
                        ]">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14-7H5a2 2 0 00-2 2v12a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2z" />
                            </svg>
                            Cards
                        </button>
                    </div>

                    <button @click="openModal(false)"
                        class="inline-flex items-center justify-center bg-gradient-to-r from-green-400 to-emerald-400 text-white font-bold py-3 px-6 rounded-full text-sm shadow-xl hover:scale-105 hover:from-green-500 hover:to-emerald-500 transition-transform duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Rak
                    </button>
                </div>
            </div>
        </template>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl shadow-xl p-6 mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex w-full lg:w-auto items-center gap-3">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
                    </svg>
                    <TextInput v-model="search" type="text"
                        class="w-full lg:w-72 bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Cari nama rak..." />
                </div>
            </div>

            <div v-if="racks.data.length">
                <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl shadow-xl overflow-hidden">
                    <div v-if="viewMode === 'table'">
                        <div class="hidden lg:block overflow-x-auto">
                            <table class="min-w-full text-sm text-gray-200">
                                <thead
                                    class="bg-gradient-to-r from-purple-600/30 to-blue-600/30 backdrop-blur-sm border-b border-white/20">
                                    <tr>
                                        <th
                                            class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-white/90">
                                            Nama Rak</th>
                                        <th
                                            class="px-6 py-4 text-right text-xs uppercase tracking-wider font-semibold text-white/90">
                                            Aksi</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-700/50">
                                    <tr v-for="rack in racks.data" :key="rack.id"
                                        class="hover:bg-white/5 transition-all duration-200 group">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-sm font-bold">
                                                    {{ rack.name.charAt(0).toUpperCase() }}
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-white">{{ rack.name }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex justify-end gap-2">
                                                <button @click="openModal(true, rack)"
                                                    class="inline-flex items-center px-4 py-2 rounded-full text-xs font-bold bg-gradient-to-r from-blue-400 to-blue-500 text-white shadow-lg hover:scale-105 hover:from-blue-500 hover:to-blue-600 transition-transform duration-200">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                    Edit
                                                </button>
                                                <button @click="deleteRack(rack.id)"
                                                    class="inline-flex items-center px-4 py-2 rounded-full text-xs font-bold bg-gradient-to-r from-red-400 to-red-500 text-white shadow-lg hover:scale-105 hover:from-red-500 hover:to-red-600 transition-transform duration-200">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="lg:hidden overflow-x-auto">
                            <table class="min-w-full text-xs text-gray-200">
                                <thead
                                    class="bg-gradient-to-r from-purple-600/30 to-blue-600/30 backdrop-blur-sm border-b border-white/20">
                                    <tr>
                                        <th class="px-3 py-3 text-left font-semibold text-white/90">Rak</th>
                                        <th class="px-3 py-3 text-left font-semibold text-white/90">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-700/50">
                                    <tr v-for="rack in racks.data" :key="rack.id" class="hover:bg-white/5">
                                        <td class="px-3 py-3">
                                            <div class="font-semibold text-white text-sm">{{ rack.name }}</div>
                                        </td>
                                        <td class="px-3 py-3">
                                            <div class="flex flex-col gap-2">
                                                <button @click="openModal(true, rack)"
                                                    class="inline-flex items-center justify-center px-3 py-2 rounded-full text-[11px] font-bold bg-gradient-to-r from-blue-400 to-blue-500 text-white shadow-lg">
                                                    Edit
                                                </button>
                                                <button @click="deleteRack(rack.id)"
                                                    class="inline-flex items-center justify-center px-3 py-2 rounded-full text-[11px] font-bold bg-gradient-to-r from-red-400 to-red-500 text-white shadow-lg">
                                                    Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div v-else>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
                            <div v-for="rack in racks.data" :key="rack.id"
                                class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl p-6 shadow-xl hover:shadow-2xl hover:bg-white/10 transition-all duration-300 hover:-translate-y-1">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-lg font-bold">
                                            {{ rack.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-white">{{ rack.name }}</h3>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-3 pt-4 border-t border-gray-800/60 mt-4">
                                    <button @click="openModal(true, rack)"
                                        class="inline-flex items-center px-4 py-2 rounded-full text-xs font-bold bg-gradient-to-r from-blue-400 to-blue-500 text-white shadow-lg hover:scale-105 hover:from-blue-500 hover:to-blue-600 transition-transform duration-200">
                                        <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit Rak
                                    </button>
                                    <button @click="deleteRack(rack.id)"
                                        class="inline-flex items-center px-4 py-2 rounded-full text-xs font-bold bg-gradient-to-r from-red-400 to-red-500 text-white shadow-lg hover:scale-105 hover:from-red-500 hover:to-red-600 transition-transform duration-200">
                                        <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Hapus Rak
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-12 backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="text-lg font-medium text-gray-300 mb-2">Belum ada rak</h3>
                <p class="text-gray-400">Tambah rak baru untuk mengorganisir produk Anda.</p>
            </div>

            <Pagination v-if="racks.links" :links="racks.links" />
        </div>
        <!-- </div> -->

        <Modal :show="isModalOpen" @close="closeModal">
            <div
                class="p-8 bg-gray-900/70 backdrop-blur-md border border-white/10 rounded-2xl shadow-lg max-w-4xl mx-auto space-y-6 text-white">
                <h2 class="text-lg font-semibold">
                    {{ isEditMode ? "Edit Rak" : "Tambah Rak Baru" }}
                </h2>
                <form @submit.prevent="saveRack" class="mt-6 grid grid-cols-1 gap-6">
                    <div>
                        <InputLabel for="name" value="Nama Rak" class="text-gray-300" />
                        <TextInput id="name" v-model="form.name"
                            class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500"
                            required />
                        <InputError :message="form.errors.name" class="mt-2 text-red-400" />
                    </div>
                    <div class="flex justify-end gap-3 mt-4">
                        <button type="button" @click="closeModal"
                            class="inline-flex items-center bg-gradient-to-r from-blue-400 to-blue-500 text-white font-bold py-2 px-6 rounded-full text-sm shadow-lg hover:scale-105 hover:from-blue-500 hover:to-blue-600 transition-transform duration-200">
                            <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Batal
                        </button>
                        <button type="submit" :disabled="form.processing"
                            class="inline-flex items-center bg-gradient-to-r from-green-400 to-emerald-400 text-white font-bold py-2 px-6 rounded-full text-sm shadow-lg hover:scale-105 hover:from-green-500 hover:to-emerald-500 transition-transform duration-200"
                            :class="{ 'opacity-25': form.processing }">
                            <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            {{ isEditMode ? 'Perbarui' : 'Simpan' }}
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

import Modal from "@/Components/Modal.vue";
import Pagination from "@/Components/Pagination.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    racks: Object,
    filters: Object,
});

const viewMode = ref('table');
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
            route("master.racks.index"),
            { search: value },
            {
                preserveState: true,
                replace: true,
            }
        );
    }, 300)
);

const openModal = (editMode = false, rack = null) => {
    isEditMode.value = editMode;
    if (editMode && rack) {
        form.id = rack.id;
        form.name = rack.name;
    } else {
        form.reset();
    }
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const saveRack = () => {
    if (isEditMode.value) {
        form.patch(route("master.racks.update", form.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route("master.racks.store"), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteRack = (id) => {
    if (confirm("Apakah Anda yakin ingin menghapus rak ini?")) {
        useForm({}).delete(route("master.racks.destroy", id));
    }
};
</script>