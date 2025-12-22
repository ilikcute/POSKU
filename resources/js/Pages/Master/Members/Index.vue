<template>
    <AuthenticatedLayout>
        <Head title="Master Customer" />

        <template #header>
            <AppPageHeader
                title="Master Customer"
                description="Kelola data pelanggan dengan tampilan yang konsisten dan nyaman."
            >
                <template #filters>
                    <div class="flex bg-[#f7f7f7] border border-[#9c9c9c] rounded p-1">
                        <button
                            @click="viewMode = 'table'"
                            :class="[
                                'flex items-center gap-2 px-4 py-2 rounded text-xs font-semibold transition-colors',
                                viewMode === 'table'
                                    ? 'bg-white text-[#1f1f1f]'
                                    : 'text-[#555] hover:text-[#1f1f1f] hover:bg-white',
                            ]"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3 10h18M3 6h18m-9 10h9"
                                />
                            </svg>
                            Table
                        </button>
                        <button
                            @click="viewMode = 'cards'"
                            :class="[
                                'flex items-center gap-2 px-4 py-2 rounded text-xs font-semibold transition-colors',
                                viewMode === 'cards'
                                    ? 'bg-white text-[#1f1f1f]'
                                    : 'text-[#555] hover:text-[#1f1f1f] hover:bg-white',
                            ]"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 11H5m14-7H5a2 2 0 00-2 2v12a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2z"
                                />
                            </svg>
                            Cards
                        </button>
                    </div>
                </template>
                <template #actions>
                    <button
                        @click="openModal(false)"
                        class="inline-flex items-center justify-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] font-semibold py-3 px-6 rounded text-xs hover:bg-white transition-colors"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Customer
                    </button>
                </template>
            </AppPageHeader>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <AppPanel>
                <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <label class="flex w-full items-center gap-3 text-[#555]">
                        <svg class="w-5 h-5 text-[#555]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
                        </svg>
                        <TextInput v-model="search" type="text"
                            class="flex-1 bg-white border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Cari nama, kode customer, atau nomor telepon..." />
                    </label>

                    <div v-if="storeProfile" class="flex items-center gap-3 text-xs text-[#555]">
                        <div class="rounded bg-[#f7f7f7] border border-[#9c9c9c] px-4 py-2">
                            <p class="text-xs uppercase tracking-wide text-[#555]">Toko aktif</p>
                            <p class="text-sm font-semibold text-[#1f1f1f]">
                                {{ storeProfile.name }}
                            </p>
                            <p class="text-xs text-[#555]" v-if="storeProfile.address">
                                {{ storeProfile.address }}
                            </p>
                        </div>
                    </div>
                </div>
            </AppPanel>

            <div v-if="customers?.data?.length">
                <AppPanel class="overflow-hidden">
                    <div v-if="viewMode === 'table'">
                        <div class="hidden lg:block overflow-x-auto">
                            <table class="min-w-full text-xs text-[#1f1f1f]">
                                <thead class="bg-[#efefef] border-b border-[#9c9c9c]">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-[#1f1f1f]">
                                            Kode Customer
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-[#1f1f1f]">
                                            Nama
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-[#1f1f1f]">
                                            Telepon
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-[#1f1f1f]">
                                            Alamat
                                        </th>
                                        <th class="px-6 py-4 text-right text-xs uppercase tracking-wider font-semibold text-[#1f1f1f]">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#d0d0d0]">
                                    <tr v-for="customer in customers.data" :key="customer.id" class="hover:bg-white transition-colors duration-150">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded bg-[#dbe7ff] flex items-center justify-center text-xs font-bold">
                                                    {{ (customer.member_code || 'C').charAt(0).toUpperCase() }}
                                                </div>
                                                <div>
                                                    <p class="font-mono font-semibold text-[#1f1f1f]">{{ customer.member_code || '-' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <p class="font-semibold text-[#1f1f1f]">{{ customer.name }}</p>
                                        </td>
                                        <td class="px-6 py-4">
                                            <p class="text-[#555]">{{ customer.phone || '-' }}</p>
                                        </td>
                                        <td class="px-6 py-4">
                                            <p class="text-[#555] truncate max-w-xs" :title="customer.address">{{ customer.address || '-' }}</p>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex justify-end gap-2">
                                                <button @click="openModal(true, customer)"
                                                    class="inline-flex items-center px-4 py-2 rounded text-xs font-bold bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] hover:bg-white transition-colors">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                    Edit
                                                </button>
                                                <button @click="deleteCustomer(customer.id)"
                                                    class="inline-flex items-center px-4 py-2 rounded text-xs font-bold bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] hover:bg-white transition-colors">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
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
                                <thead class="bg-[#efefef] border-b border-[#9c9c9c]">
                                    <tr>
                                        <th class="px-3 py-3 text-left font-semibold text-[#1f1f1f]">Customer</th>
                                        <th class="px-3 py-3 text-left font-semibold text-[#1f1f1f]">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#d0d0d0]">
                                    <tr v-for="customer in customers.data" :key="customer.id" class="hover:bg-white">
                                        <td class="px-3 py-3">
                                            <div class="font-semibold text-[#1f1f1f] text-xs">{{ customer.name }}</div>
                                            <div class="font-mono text-xs text-[#555]">{{ customer.member_code || '-' }}</div>
                                            <div class="text-xs text-[#555]">{{ customer.phone || '-' }}</div>
                                        </td>
                                        <td class="px-3 py-3">
                                            <div class="flex flex-col gap-2">
                                                <button @click="openModal(true, customer)"
                                                    class="inline-flex items-center justify-center px-3 py-2 rounded text-[11px] font-bold bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c]">
                                                    Edit
                                                </button>
                                                <button @click="deleteCustomer(customer.id)"
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
                            <div v-for="customer in customers.data" :key="customer.id"
                                class="bg-[#f7f7f7] border border-[#9c9c9c] rounded p-6 shadow-sm hover:bg-white transition-colors">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded bg-[#dbe7ff] flex items-center justify-center text-sm font-bold">
                                            {{ (customer.member_code || 'C').charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-[#1f1f1f]">{{ customer.name }}</h3>
                                            <p class="font-mono text-xs text-[#555]">{{ customer.member_code || '-' }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-2 mb-4">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-[#555]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                        </svg>
                                        <p class="text-xs text-[#555]">{{ customer.phone || '-' }}</p>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <svg class="w-4 h-4 text-[#555] mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <p class="text-xs text-[#555]">{{ customer.address || '-' }}</p>
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-3 pt-4 border-t border-[#d0d0d0] mt-4">
                                    <button @click="openModal(true, customer)"
                                        class="inline-flex items-center px-4 py-2 rounded text-xs font-bold bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] hover:bg-white transition-colors">
                                        <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit Customer
                                    </button>
                                    <button @click="deleteCustomer(customer.id)"
                                        class="inline-flex items-center px-4 py-2 rounded text-xs font-bold bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] hover:bg-white transition-colors">
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
                </AppPanel>
            </div>

            <div v-else>
                <AppPanel>
                    <InlineNotice variant="info" class="text-center">
                        <p class="font-semibold">Belum ada customer yang tercatat.</p>
                        <p class="mt-1 text-[#555]">
                            Gunakan tombol <strong>Tambah Customer</strong> untuk mengisi data pertama Anda.
                        </p>
                    </InlineNotice>
                </AppPanel>
            </div>

            <Pagination v-if="customers?.links" :links="customers.links" />
        </div>

        <Modal :show="isModalOpen" @close="closeModal">
            <div class="p-8 bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-sm max-w-4xl mx-auto space-y-6 text-[#1f1f1f]">
                <h2 class="text-sm font-semibold">
                    {{ isEditMode ? 'Edit Customer' : 'Tambah Customer Baru' }}
                </h2>
                <form @submit.prevent="saveCustomer" class="mt-6 grid grid-cols-1 gap-6">
                    <div>
                        <InputLabel for="member_code" value="Kode Customer" class="text-[#1f1f1f]" />
                        <TextInput id="member_code" v-model="form.member_code"
                            class="mt-1 block w-full font-mono bg-white border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Contoh: CUST001" required />
                        <InputError :message="form.errors.member_code" class="mt-2 text-red-600" />
                    </div>
                    <div>
                        <InputLabel for="name" value="Nama Customer" class="text-[#1f1f1f]" />
                        <TextInput id="name" v-model="form.name"
                            class="mt-1 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Nama lengkap customer" required />
                        <InputError :message="form.errors.name" class="mt-2 text-red-600" />
                    </div>
                    <div>
                        <InputLabel for="phone" value="Telepon" class="text-[#1f1f1f]" />
                        <TextInput id="phone" v-model="form.phone"
                            class="mt-1 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Nomor telepon" />
                        <InputError :message="form.errors.phone" class="mt-2 text-red-600" />
                    </div>
                    <div>
                        <InputLabel for="email" value="Email" class="text-[#1f1f1f]" />
                        <TextInput id="email" v-model="form.email" type="email"
                            class="mt-1 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Alamat email" />
                        <InputError :message="form.errors.email" class="mt-2 text-red-600" />
                    </div>
                    <div>
                        <InputLabel for="address" value="Alamat" class="text-[#1f1f1f]" />
                        <textarea id="address" v-model="form.address"
                            class="mt-1 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500"
                            rows="3" placeholder="Alamat lengkap"></textarea>
                        <InputError :message="form.errors.address" class="mt-2 text-red-600" />
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
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

        <CustomerCard :show="showCustomerCard" :customer="selectedCustomerForCard" :store="store"
            @close="closeCustomerCard" />
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Pagination from '@/Components/Pagination.vue';
import CustomerCard from '@/Components/CustomerCard.vue';
import { debounce } from 'lodash';
import AppPanel from '@/Components/AppPanel.vue';
import AppPageHeader from '@/Components/AppPageHeader.vue';
import InlineNotice from '@/Components/InlineNotice.vue';

const props = defineProps({
    customers: Object,
    filters: {
        type: Object,
        default: () => ({}),
    },
    store: Object,
});

const storeProfile = computed(() => props.store ?? null);

const viewMode = ref('table');
const search = ref(props.filters?.search ?? '');
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
    debounce(() => {
        router.get(
            route('master.members.index'),
            { search: search.value },
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
    if (confirm('Apakah Anda yakin ingin menghapus customer ini?')) {
        useForm({}).delete(route('master.members.destroy', id), {
            onSuccess: () => closeModal(),
        });
    }
};

const printCard = (customer) => {
    selectedCustomerForCard.value = customer;
    showCustomerCard.value = true;
};

const closeCustomerCard = () => {
    showCustomerCard.value = false;
    selectedCustomerForCard.value = null;
};
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
