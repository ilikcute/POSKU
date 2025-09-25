<template>
    <AuthenticatedLayout>

        <Head title="Master Customer" />

        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-xl text-white leading-tight">
                        Master Customer
                    </h2>
                    <p class="text-sm text-gray-400 mt-1">
                        Kelola customer dengan tampilan konsisten dan mudah.
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
                        Tambah Customer
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
                        placeholder="Cari nama atau kode customer..." />
                </div>
            </div>

            <div v-if="customers.data.length">
                <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl shadow-xl overflow-hidden">
                    <div v-if="viewMode === 'table'">
                        <div class="hidden lg:block overflow-x-auto">
                            <table class="min-w-full text-sm text-gray-200">
                                <thead
                                    class="bg-gradient-to-r from-purple-600/30 to-blue-600/30 backdrop-blur-sm border-b border-white/20">
                                    <tr>
                                        <th
                                            class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-white/90">
                                            Kode Customer</th>
                                        <th
                                            class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-white/90">
                                            Nama</th>
                                        <th
                                            class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-white/90">
                                            Telepon</th>
                                        <th
                                            class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-white/90">
                                            Alamat</th>
                                        <th
                                            class="px-6 py-4 text-right text-xs uppercase tracking-wider font-semibold text-white/90">
                                            Aksi</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-700/50">
                                    <tr v-for="customer in customers.data" :key="customer.id"
                                        class="hover:bg-white/5 transition-all duration-200 group">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-sm font-bold">
                                                    {{ (customer.member_code || 'C').charAt(0).toUpperCase() }}
                                                </div>
                                                <div>
                                                    <p class="font-mono font-semibold text-white">{{
                                                        customer.member_code || '-' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <p class="font-semibold text-white">{{ customer.name }}</p>
                                        </td>
                                        <td class="px-6 py-4">
                                            <p class="text-gray-400">{{ customer.phone || '-' }}</p>
                                        </td>
                                        <td class="px-6 py-4">
                                            <p class="text-gray-400 truncate max-w-xs" :title="customer.address">{{
                                                customer.address || '-' }}</p>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex justify-end gap-2">
                                                <button title="Cetak Kartu Customer" @click="printCard(customer)"
                                                    class="inline-flex items-center px-4 py-2 rounded-full text-xs font-bold bg-gradient-to-r from-blue-400 to-sky-400 text-white shadow-lg hover:scale-105 hover:from-blue-500 hover:to-sky-500 transition-transform duration-200">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z">
                                                        </path>
                                                    </svg>
                                                    Kartu
                                                </button>
                                                <button @click="openModal(true, customer)"
                                                    class="inline-flex items-center px-4 py-2 rounded-full text-xs font-bold bg-gradient-to-r from-blue-400 to-blue-500 text-white shadow-lg hover:scale-105 hover:from-blue-500 hover:to-blue-600 transition-transform duration-200">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                    Edit
                                                </button>
                                                <button @click="deleteCustomer(customer.id)"
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
                                        <th class="px-3 py-3 text-left font-semibold text-white/90">Customer</th>
                                        <th class="px-3 py-3 text-left font-semibold text-white/90">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-700/50">
                                    <tr v-for="customer in customers.data" :key="customer.id" class="hover:bg-white/5">
                                        <td class="px-3 py-3">
                                            <div class="font-semibold text-white text-sm">{{ customer.name }}</div>
                                            <div class="font-mono text-xs text-gray-400">{{ customer.member_code || '-'
                                            }}
                                            </div>
                                            <div class="text-xs text-gray-500">{{ customer.phone || '-' }}</div>
                                        </td>
                                        <td class="px-3 py-3">
                                            <div class="flex flex-col gap-2">
                                                <button title="Cetak Kartu Customer" @click="printCard(customer)"
                                                    class="inline-flex items-center justify-center px-3 py-2 rounded-full text-[11px] font-bold bg-gradient-to-r from-blue-400 to-sky-400 text-white shadow-lg">
                                                    Kartu
                                                </button>
                                                <button @click="openModal(true, customer)"
                                                    class="inline-flex items-center justify-center px-3 py-2 rounded-full text-[11px] font-bold bg-gradient-to-r from-blue-400 to-blue-500 text-white shadow-lg">
                                                    Edit
                                                </button>
                                                <button @click="deleteCustomer(customer.id)"
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
                            <div v-for="customer in customers.data" :key="customer.id"
                                class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl p-6 shadow-xl hover:shadow-2xl hover:bg-white/10 transition-all duration-300 hover:-translate-y-1">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-lg font-bold">
                                            {{ (customer.member_code || 'C').charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-white">{{ customer.name }}</h3>
                                            <p class="font-mono text-sm text-gray-400">{{ customer.member_code || '-' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-2 mb-4">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                        </svg>
                                        <p class="text-sm text-gray-300">{{ customer.phone || '-' }}</p>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <svg class="w-4 h-4 text-gray-400 mt-0.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <p class="text-sm text-gray-300">{{ customer.address || '-' }}</p>
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-3 pt-4 border-t border-gray-800/60 mt-4">
                                    <button title="Cetak Kartu Customer" @click="printCard(customer)"
                                        class="inline-flex items-center px-4 py-2 rounded-full text-xs font-bold bg-gradient-to-r from-blue-400 to-sky-400 text-white shadow-lg hover:scale-105 hover:from-blue-500 hover:to-sky-500 transition-transform duration-200">
                                        <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                        </svg>
                                        Kartu
                                    </button>
                                    <button @click="openModal(true, customer)"
                                        class="inline-flex items-center px-4 py-2 rounded-full text-xs font-bold bg-gradient-to-r from-blue-400 to-blue-500 text-white shadow-lg hover:scale-105 hover:from-blue-500 hover:to-blue-600 transition-transform duration-200">
                                        <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit Customer
                                    </button>
                                    <button @click="deleteCustomer(customer.id)"
                                        class="inline-flex items-center px-4 py-2 rounded-full text-xs font-bold bg-gradient-to-r from-red-400 to-red-500 text-white shadow-lg hover:scale-105 hover:from-red-500 hover:to-red-600 transition-transform duration-200">
                                        <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Hapus Customer
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
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <h3 class="text-lg font-medium text-gray-300 mb-2">Belum ada customer</h3>
                <p class="text-gray-400">Tambah customer baru untuk mengorganisir data pelanggan Anda.</p>
            </div>

            <Pagination v-if="customers.links" :links="customers.links" />
        </div>
        <!-- </div> -->
        <Modal :show="isModalOpen" @close="closeModal">
            <div
                class="p-8 bg-gray-900/70 backdrop-blur-md border border-white/10 rounded-2xl shadow-lg max-w-4xl mx-auto space-y-6 text-white">
                <h2 class="text-lg font-semibold">
                    {{ isEditMode ? "Edit Customer" : "Tambah Customer Baru" }}
                </h2>
                <form @submit.prevent="saveCustomer" class="mt-6 grid grid-cols-1 gap-6">
                    <div>
                        <InputLabel for="member_code" value="Kode Customer" class="text-gray-300" />
                        <TextInput id="member_code" v-model="form.member_code"
                            class="mt-1 block w-full font-mono bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Contoh: CUST001" required />
                        <InputError :message="form.errors.member_code" class="mt-2 text-red-400" />
                    </div>
                    <div>
                        <InputLabel for="name" value="Nama Customer" class="text-gray-300" />
                        <TextInput id="name" v-model="form.name"
                            class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Nama lengkap customer" required />
                        <InputError :message="form.errors.name" class="mt-2 text-red-400" />
                    </div>
                    <div>
                        <InputLabel for="phone" value="Telepon" class="text-gray-300" />
                        <TextInput id="phone" v-model="form.phone"
                            class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Nomor telepon" />
                        <InputError :message="form.errors.phone" class="mt-2 text-red-400" />
                    </div>
                    <div>
                        <InputLabel for="email" value="Email" class="text-gray-300" />
                        <TextInput id="email" v-model="form.email" type="email"
                            class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Alamat email" />
                        <InputError :message="form.errors.email" class="mt-2 text-red-400" />
                    </div>
                    <div>
                        <InputLabel for="address" value="Alamat" class="text-gray-300" />
                        <textarea id="address" v-model="form.address"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500"
                            rows="3" placeholder="Alamat lengkap"></textarea>
                        <InputError :message="form.errors.address" class="mt-2 text-red-400" />
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
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
        <CustomerCard :show="showCustomerCard" :customer="selectedCustomerForCard" :store="store"
            @close="closeCustomerCard" />
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { Head, usePage, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Pagination from '@/Components/Pagination.vue';
import CustomerCard from '@/Components/CustomerCard.vue';
import { debounce } from "lodash";

const props = defineProps({
    customers: Object,
    filters: Object,
    store: Object, // Added store prop for CustomerCard
});

const page = usePage();

const viewMode = ref('table');
const search = ref(props.filters.search || ''); // Initialize search with filter value
const isModalOpen = ref(false);
const isEditMode = ref(false);
const showCustomerCard = ref(false);
const selectedCustomerForCard = ref(null);

const form = useForm({
    id: null,
    member_code: '',
    name: '',
    phone: '',
    email: '',
    address: '',
});

watch(
    search,
    debounce((value) => {
        router.get(
            route("master.members.index"),
            { search: value },
            {
                preserveState: true,
                replace: true,
            }
        );
    }, 300)
);

const openModal = (editMode = false, customer = null) => {
    isEditMode.value = editMode;
    if (editMode && customer) {
        form.id = customer.id;
        form.member_code = customer.member_code;
        form.name = customer.name;
        form.phone = customer.phone;
        form.email = customer.email;
        form.address = customer.address;
    } else {
        form.reset();
    }
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const saveCustomer = () => {
    if (isEditMode.value) {
        form.patch(route('master.members.update', form.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('master.members.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteCustomer = (id) => {
    if (confirm("Apakah Anda yakin ingin menghapus customer ini?")) {
        useForm({}).delete(route("master.members.destroy", id), {
            onSuccess: () => closeModal(),
        });
    }
};
function printCard(customer) {
    selectedCustomerForCard.value = customer;
    showCustomerCard.value = true;
}

function closeCustomerCard() {
    showCustomerCard.value = false;
    selectedCustomerForCard.value = null;
}
</script>

<style>
@media print {
    body * {
        visibility: hidden;
    }

    #customer-card, #customer-card * {
        visibility: visible;
    }

    #customer-card {
        position: absolute;
        left: 0;
        top: 0;
    }

    .customer-card-container {
        page-break-after: always;
    }
}
</style>
