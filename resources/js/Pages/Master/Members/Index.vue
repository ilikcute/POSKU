<template>
    <AuthenticatedLayout>

        <Head title="Master Customer" />
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">
                Master Customer
            </h2>
        </template>
        
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl shadow-lg overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-4 border-b border-gray-600/60">
                        <h3 class="text-lg font-medium text-white">Daftar Customer</h3>
                        <button @click="openModal(false)"
                            class="inline-block bg-gradient-to-r from-green-400 to-emerald-400 text-white font-bold py-3 px-10 rounded-full text-sm md:text-base shadow-xl hover:scale-105 hover:from-green-500 hover:to-emerald-500 transition-transform duration-200">
                            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            Tambah Customer
                        </button>
                    </div>
                    <div class="flex items-center justify-between px-4 py-4 border-b border-gray-600/60 gap-4">
                        <TextInput v-model="search" type="text" placeholder="Cari nama atau kode..."
                            class="w-full max-w-xs bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500" />
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm text-gray-200 divide-y divide-gray-600">
                            <thead class="bg-gray-800/70">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Kode
                                        Customer
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Nama</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Telepon
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Alamat
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                <tr v-for="customer in customers.data" :key="customer.id"
                                    class="hover:bg-gray-700/50 transition-colors duration-150">
                                    <td class="px-4 py-3 whitespace-nowrap font-mono text-sm">{{ customer.member_code }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-gray-200">{{ customer.name
                                        }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-400">{{ customer.phone || '-' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap truncate max-w-xs text-gray-400"
                                        :title="customer.address">{{ customer.address || '-' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end space-x-2">
                                            <button title="Cetak Kartu Customer" @click="printCard(customer)"
                                                class="inline-block bg-gradient-to-r from-blue-400 to-sky-400 text-white font-bold py-2 px-4 rounded-full text-xs shadow-lg hover:scale-105 hover:from-blue-500 hover:to-sky-500 transition-transform duration-200">
                                                <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z">
                                                    </path>
                                                </svg>
                                                Kartu
                                            </button>
                                            <button @click="openModal(true, customer)"
                                                class="inline-block bg-gradient-to-r from-blue-400 to-blue-500 text-white font-bold py-2 px-4 rounded-full text-xs shadow-lg hover:scale-105 hover:from-blue-500 hover:to-blue-600 transition-transform duration-200">
                                                <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </button>
                                            <button @click="deleteCustomer(customer.id)"
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
                                <tr v-if="customers.data.length === 0">
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-12 h-12 text-gray-500 mb-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                                </path>
                                            </svg>
                                            <p class="text-lg font-medium text-gray-300">Belum ada data customer</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Pagination :links="customers.links" />
                </div>
            </div>
        </div>
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
                            {{ isEditMode ? 'Update' : 'Simpan' }}
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
        visibility: hidden !important;
        box-shadow: none !important;
    }

    .customer-card-container,
    .customer-card-container * {
        visibility: visible !important;
    }

    #customer-card,
    #customer-card * {
        visibility: visible !important;
    }

    .fixed,
    .inset-0,
    .overflow-y-auto,
    .min-h-screen,
    .pt-4,
    .px-4,
    .pb-20,
    .text-center,
    .sm\:block,
    .sm\:p-0 {
        position: static !important;
        padding: 0 !important;
    }
}
</style>