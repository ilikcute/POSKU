<template>
    <AuthenticatedLayout>

        <Head title="Master Supplier" />

        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-lg text-[#1f1f1f] leading-tight">
                        Master Supplier
                    </h2>
                    <p class="text-xs text-[#555] mt-1">
                        Kelola supplier dengan tampilan konsisten dan mudah.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="flex bg-[#f7f7f7] border border-[#9c9c9c] rounded p-1">
                        <button @click="viewMode = 'table'" :class="[
                            'px-3 py-2 rounded text-xs font-semibold transition-colors',
                            viewMode === 'table'
                                ? 'bg-white text-[#1f1f1f]'
                                : 'text-[#555] hover:text-[#1f1f1f] hover:bg-[#f0f0f0]'
                        ]">

                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 10h18M3 6h18m-9 10h9" />
                            </svg>
                            Table
                        </button>
                        <button @click="viewMode = 'cards'" :class="[
                            'px-3 py-2 rounded text-xs font-semibold transition-colors',
                            viewMode === 'cards'
                                ? 'bg-white text-[#1f1f1f]'
                                : 'text-[#555] hover:text-[#1f1f1f] hover:bg-[#f0f0f0]'
                        ]">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14-7H5a2 2 0 00-2 2v12a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2z" />
                            </svg>
                            Cards
                        </button>
                    </div>

                    <button @click="openModal(false)"
                        class="inline-flex items-center justify-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] font-semibold py-3 px-6 rounded text-xs hover:bg-white transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Supplier
                    </button>
                </div>
            </div>
        </template>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-sm p-6 mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex w-full lg:w-auto items-center gap-3">
                    <svg class="w-5 h-5 text-[#555]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
                    </svg>
                    <TextInput v-model="search" type="text"
                        class="w-full lg:w-72 bg-white border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Cari nama supplier..." />
                </div>
            </div>

            <div v-if="suppliers.data.length">
                <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-sm overflow-hidden">
                    <div v-if="viewMode === 'table'">
                        <div class="hidden lg:block overflow-x-auto">
                            <table class="min-w-full text-xs text-[#1f1f1f]">
                                <thead
                                    class="bg-[#efefef] border-b border-[#9c9c9c]">
                                    <tr>
                                        <th
                                            class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-[#1f1f1f]">
                                            Nama Supplier</th>
                                        <th
                                            class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-[#1f1f1f]">
                                            PKP</th>
                                        <th
                                            class="px-6 py-4 text-right text-xs uppercase tracking-wider font-semibold text-[#1f1f1f]">
                                            Aksi</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-[#d0d0d0]">
                                    <tr v-for="supplier in suppliers.data" :key="supplier.id"
                                        class="hover:bg-white transition-colors duration-150">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="w-10 h-10 rounded bg-[#dbe7ff] flex items-center justify-center text-xs font-bold">
                                                    {{ supplier.name.charAt(0).toUpperCase() }}
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-[#1f1f1f]">{{ supplier.name }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-xs text-[#1f1f1f]">
                                            <span
                                                class="inline-flex items-center px-2 py-1 border border-[#9c9c9c] bg-[#efefef]">
                                                {{ supplier.is_pkp ? 'PKP' : 'Non PKP' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex justify-end gap-2">
                                                <button @click="openModal(true, supplier)"
                                                    class="inline-flex items-center px-4 py-2 rounded text-xs font-bold bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] hover:bg-white transition-colors">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                    Edit
                                                </button>
                                                <button @click="deleteSupplier(supplier.id)"
                                                    class="inline-flex items-center px-4 py-2 rounded text-xs font-bold bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] hover:bg-white transition-colors">
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
                            <table class="min-w-full text-xs text-[#1f1f1f]">
                                <thead
                                    class="bg-[#efefef] border-b border-[#9c9c9c]">
                                    <tr>
                                        <th class="px-3 py-3 text-left font-semibold text-[#1f1f1f]">Supplier</th>
                                        <th class="px-3 py-3 text-left font-semibold text-[#1f1f1f]">PKP</th>
                                        <th class="px-3 py-3 text-left font-semibold text-[#1f1f1f]">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#d0d0d0]">
                                    <tr v-for="supplier in suppliers.data" :key="supplier.id" class="hover:bg-white">
                                        <td class="px-3 py-3">
                                            <div class="font-semibold text-[#1f1f1f] text-xs">{{ supplier.name }}</div>
                                        </td>
                                        <td class="px-3 py-3 text-xs text-[#1f1f1f]">
                                            {{ supplier.is_pkp ? 'PKP' : 'Non PKP' }}
                                        </td>
                                        <td class="px-3 py-3">
                                            <div class="flex flex-col gap-2">
                                                <button @click="openModal(true, supplier)"
                                                    class="inline-flex items-center justify-center px-3 py-2 rounded text-[11px] font-bold bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c]">
                                                    Edit
                                                </button>
                                                <button @click="deleteSupplier(supplier.id)"
                                                    class="inline-flex items-center justify-center px-3 py-2 rounded text-[11px] font-bold bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c]">
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
                            <div v-for="supplier in suppliers.data" :key="supplier.id"
                                class="bg-[#f7f7f7] border border-[#9c9c9c] rounded p-6 shadow-sm hover:shadow-2xl hover:bg-[#f0f0f0] transition-all duration-300 hover:-translate-y-1">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="w-12 h-12 rounded bg-[#dbe7ff] flex items-center justify-center text-sm font-bold">
                                            {{ supplier.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-[#1f1f1f]">{{ supplier.name }}</h3>
                                            <p class="text-xs text-[#555]">{{ supplier.is_pkp ? 'PKP' : 'Non PKP' }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-3 pt-4 border-t border-[#d0d0d0] mt-4">
                                    <button @click="openModal(true, supplier)"
                                        class="inline-flex items-center px-4 py-2 rounded text-xs font-bold bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] hover:bg-white transition-colors">
                                        <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit Supplier
                                    </button>
                                    <button @click="deleteSupplier(supplier.id)"
                                        class="inline-flex items-center px-4 py-2 rounded text-xs font-bold bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] hover:bg-white transition-colors">
                                        <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Hapus Supplier
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-12 bg-[#f7f7f7] border border-[#9c9c9c] rounded">
                <svg class="mx-auto h-12 w-12 text-[#555] mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="text-sm font-semibold text-[#555] mb-2">Belum ada supplier</h3>
                <p class="text-[#555]">Tambah supplier baru untuk mengorganisir produk Anda.</p>
            </div>

            <Pagination v-if="suppliers.links" :links="suppliers.links" />
        </div>
        <!-- </div> -->

        <Modal :show="isModalOpen" @close="closeModal">
            <div
                class="p-8 bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-sm max-w-4xl mx-auto space-y-6 text-[#1f1f1f]">
                <h2 class="text-sm font-semibold">
                    {{ isEditMode ? "Edit Supplier" : "Tambah Supplier Baru" }}
                </h2>
                <form @submit.prevent="saveSupplier" class="mt-6 grid grid-cols-1 gap-6">
                    <div>
                        <InputLabel for="name" value="Nama Supplier" class="text-[#555]" />
                        <TextInput id="name" v-model="form.name"
                            class="mt-1 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500"
                            required />
                        <InputError :message="form.errors.name" class="mt-2 text-red-400" />
                    </div>
                    <div>
                        <InputLabel for="contact_person" value="Kontak" class="text-[#555]" />
                        <TextInput id="contact_person" v-model="form.contact_person"
                            class="mt-1 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500" />
                        <InputError :message="form.errors.contact_person" class="mt-2 text-red-400" />
                    </div>
                    <div>
                        <InputLabel for="phone" value="Telepon" class="text-[#555]" />
                        <TextInput id="phone" v-model="form.phone"
                            class="mt-1 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500" />
                        <InputError :message="form.errors.phone" class="mt-2 text-red-400" />
                    </div>
                    <div>
                        <InputLabel for="address" value="Alamat" class="text-[#555]" />
                        <TextInput id="address" v-model="form.address"
                            class="mt-1 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500" />
                        <InputError :message="form.errors.address" class="mt-2 text-red-400" />
                    </div>
                    <label class="flex items-center gap-2 text-xs text-[#1f1f1f]">
                        <input type="checkbox" v-model="form.is_pkp"
                            class="border border-[#9c9c9c] rounded">
                        Supplier PKP (kena PPN)
                    </label>
                    <div class="flex justify-end gap-3 mt-4">
                        <button type="button" @click="closeModal"
                            class="inline-flex items-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] font-semibold py-2 px-6 rounded text-xs hover:bg-white transition-colors">
                            <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Batal
                        </button>
                        <button type="submit" :disabled="form.processing"
                            class="inline-flex items-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] font-semibold py-2 px-6 rounded text-xs hover:bg-white transition-colors"
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
    suppliers: Object,
    filters: Object,
});

const viewMode = ref('table');
const isModalOpen = ref(false);
const isEditMode = ref(false);
const search = ref(props.filters.search);

const form = useForm({
    id: null,
    name: "",
    contact_person: "",
    phone: "",
    address: "",
    is_pkp: false,
});

watch(
    search,
    debounce((value) => {
        router.get(
            route("master.suppliers.index"),
            { search: value },
            {
                preserveState: true,
                replace: true,
            }
        );
    }, 300)
);

const openModal = (editMode = false, supplier = null) => {
    isEditMode.value = editMode;
    if (editMode && supplier) {
        form.id = supplier.id;
        form.name = supplier.name;
        form.contact_person = supplier.contact_person ?? "";
        form.phone = supplier.phone ?? "";
        form.address = supplier.address ?? "";
        form.is_pkp = !!supplier.is_pkp;
    } else {
        form.reset();
    }
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const saveSupplier = () => {
    if (isEditMode.value) {
        form.patch(route("master.suppliers.update", form.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route("master.suppliers.store"), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteSupplier = (id) => {
    if (confirm("Apakah Anda yakin ingin menghapus supplier ini?")) {
        useForm({}).delete(route("master.suppliers.destroy", id));
    }
};
</script>
