<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import Pagination from '@/Components/Pagination.vue';
import { debounce } from 'lodash';

const props = defineProps({
    salesmen: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    can: {
        type: Object,
        default: () => ({}),
    },
});

const viewMode = ref('table');
const search = ref(props.filters?.search ?? '');
const isModalOpen = ref(false);
const isEditMode = ref(false);

const form = useForm({
    id: null,
    salesman_code: '',
    name: '',
    phone: '',
    email: '',
});

const salesmenList = computed(() => props.salesmen?.data ?? []);

const getSalesmanInitial = (name) => {
    if (!name) {
        return '?';
    }

    return name.charAt(0).toUpperCase();
};

const openModal = (editMode = false, salesman = null) => {
    isEditMode.value = editMode;

    if (editMode && salesman) {
        form.id = salesman.id;
        form.salesman_code = salesman.salesman_code;
        form.name = salesman.name;
        form.phone = salesman.phone;
        form.email = salesman.email;
    } else {
        form.reset();
    }

    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const saveSalesman = () => {
    if (isEditMode.value) {
        form.patch(route('master.salesmen.update', form.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('master.salesmen.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteSalesman = (salesmanId) => {
    if (confirm('Apakah Anda yakin ingin menghapus salesman ini?')) {
        useForm({}).delete(route('master.salesmen.destroy', salesmanId));
    }
};

watch(
    search,
    debounce((value) => {
        router.get(
            route('master.salesmen.index'),
            { search: value },
            {
                preserveState: true,
                replace: true,
            }
        );
    }, 300)
);
</script>

<template>

    <Head title="Master Salesman" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-xl text-white leading-tight">
                        Master Salesman
                    </h2>
                    <p class="text-sm text-gray-400 mt-1">
                        Kelola data salesman dengan tampilan konsisten dan mudah.
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
                        Tambah Salesman
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
                        placeholder="Cari kode atau nama..." />
                </div>

                <div class="flex flex-wrap gap-3 justify-end">
                    <!-- Import/Export buttons can be added here when routes are available -->
                </div>
            </div>

            <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl shadow-xl overflow-hidden">
                <!-- Table View -->
                <div v-if="viewMode === 'table'" class="hidden lg:block overflow-x-auto">
                    <table class="min-w-full text-sm text-gray-200">
                        <thead
                            class="bg-gradient-to-r from-purple-600/30 to-blue-600/30 backdrop-blur-sm border-b border-white/20">
                            <tr>
                                <th
                                    class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-white/90">
                                    Kode
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-white/90">
                                    Nama
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-white/90">
                                    Telepon
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-white/90">
                                    Email
                                </th>
                                <th
                                    class="px-6 py-4 text-right text-xs uppercase tracking-wider font-semibold text-white/90">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700/50">
                            <tr v-for="salesman in salesmenList" :key="salesman.id"
                                class="hover:bg-white/5 transition-all duration-200 group">
                                <td class="px-6 py-4 font-mono text-sm">{{ salesman.salesman_code }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-sm font-bold">
                                            {{ getSalesmanInitial(salesman.name) }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-white">{{ salesman.name }}</p>
                                            <p class="text-xs text-gray-400">Phone: {{ salesman.phone || '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-300">{{ salesman.phone || '-' }}</td>
                                <td class="px-6 py-4 text-gray-300">{{ salesman.email || '-' }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-2">
                                        <button @click="openModal(true, salesman)"
                                            class="inline-flex items-center px-4 py-2 rounded-full text-xs font-bold bg-gradient-to-r from-blue-400 to-blue-500 text-white shadow-lg hover:scale-105 hover:from-blue-500 hover:to-blue-600 transition-transform duration-200">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Edit
                                        </button>
                                        <button @click="deleteSalesman(salesman.id)"
                                            class="inline-flex items-center px-4 py-2 rounded-full text-xs font-bold bg-gradient-to-r from-red-400 to-red-500 text-white shadow-lg hover:scale-105 hover:from-red-500 hover:to-red-600 transition-transform duration-200">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
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

                <!-- Cards View -->
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
                    <!-- Salesman Cards -->
                    <div v-for="salesman in salesmenList" :key="salesman.id"
                        class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl p-6 shadow-xl hover:shadow-2xl hover:bg-white/10 transition-all duration-300 hover:-translate-y-1">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-lg font-bold">
                                    {{ getSalesmanInitial(salesman.name) }}
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white">{{ salesman.name }}</h3>
                                    <p class="text-sm text-blue-400 font-mono">{{ salesman.salesman_code }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Info -->
                        <div class="space-y-2 mb-4">
                            <div v-if="salesman.phone" class="flex items-center text-sm text-gray-300">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                {{ salesman.phone }}
                            </div>
                            <div v-if="salesman.email" class="flex items-center text-sm text-gray-300">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                {{ salesman.email }}
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-wrap gap-2 pt-4 border-t border-gray-800/60">
                            <button @click="openModal(true, salesman)"
                                class="inline-flex items-center px-3 py-2 rounded-full text-xs font-bold bg-gradient-to-r from-blue-400 to-blue-500 text-white shadow-lg hover:scale-105 hover:from-blue-500 hover:to-blue-600 transition-transform duration-200">
                                <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit
                            </button>
                            <button @click="deleteSalesman(salesman.id)"
                                class="inline-flex items-center px-3 py-2 rounded-full text-xs font-bold bg-gradient-to-r from-red-400 to-red-500 text-white shadow-lg hover:scale-105 hover:from-red-500 hover:to-red-600 transition-transform duration-200">
                                <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="props.salesmen.links && props.salesmen.links.length > 0"
                    class="px-6 py-4 border-t border-white/10">
                    <Pagination :links="props.salesmen.links" />
                </div>
            </div>

        </div>
    </AuthenticatedLayout>

    <!-- Unified Modal -->
    <Modal :show="isModalOpen" @close="closeModal">
        <div
            class="p-8 bg-gray-900/95 backdrop-blur-md border border-white/10 rounded-2xl shadow-2xl max-w-2xl mx-auto space-y-6 text-white">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-400 to-emerald-500 flex items-center justify-center"
                    v-if="!isEditMode">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </div>
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-500 flex items-center justify-center"
                    v-else>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold bg-gradient-to-r from-green-400 to-emerald-400 bg-clip-text text-transparent"
                    v-if="!isEditMode">
                    Tambah Salesman Baru
                </h2>
                <h2 class="text-2xl font-bold bg-gradient-to-r from-blue-400 to-blue-500 bg-clip-text text-transparent"
                    v-else>
                    Edit Salesman
                </h2>
            </div>

            <form @submit.prevent="saveSalesman" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <InputLabel for="salesman_code" value="Kode Salesman *" class="text-gray-300 font-semibold" />
                        <TextInput id="salesman_code" v-model="form.salesman_code"
                            class="mt-2 block w-full bg-white/5 border-white/20 rounded-lg text-white placeholder-gray-400 focus:ring-green-500 focus:border-green-500"
                            placeholder="Masukkan kode salesman" required />
                        <InputError :message="form.errors?.salesman_code" class="mt-2 text-red-400" />
                    </div>
                    <div>
                        <InputLabel for="name" value="Nama Lengkap *" class="text-gray-300 font-semibold" />
                        <TextInput id="name" v-model="form.name"
                            class="mt-2 block w-full bg-white/5 border-white/20 rounded-lg text-white placeholder-gray-400 focus:ring-green-500 focus:border-green-500"
                            placeholder="Masukkan nama lengkap" required />
                        <InputError :message="form.errors?.name" class="mt-2 text-red-400" />
                    </div>
                    <div>
                        <InputLabel for="phone" value="Nomor Telepon" class="text-gray-300 font-semibold" />
                        <TextInput id="phone" v-model="form.phone"
                            class="mt-2 block w-full bg-white/5 border-white/20 rounded-lg text-white placeholder-gray-400 focus:ring-green-500 focus:border-green-500"
                            placeholder="Masukkan nomor telepon" />
                        <InputError :message="form.errors?.phone" class="mt-2 text-red-400" />
                    </div>
                    <div>
                        <InputLabel for="email" value="Email" class="text-gray-300 font-semibold" />
                        <TextInput id="email" v-model="form.email" type="email"
                            class="mt-2 block w-full bg-white/5 border-white/20 rounded-lg text-white placeholder-gray-400 focus:ring-green-500 focus:border-green-500"
                            placeholder="Masukkan email" />
                        <InputError :message="form.errors?.email" class="mt-2 text-red-400" />
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-6 border-t border-white/10">
                    <button type="button" @click="closeModal"
                        class="inline-flex items-center bg-gradient-to-r from-gray-600 to-gray-700 text-white font-semibold py-3 px-6 rounded-full text-sm shadow-lg hover:scale-105 hover:from-gray-700 hover:to-gray-800 transition-all duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Batal
                    </button>
                    <button type="submit" :disabled="form.processing"
                        class="inline-flex items-center bg-gradient-to-r from-green-400 to-emerald-500 text-white font-bold py-3 px-8 rounded-full text-sm shadow-xl hover:scale-105 hover:from-green-500 hover:to-emerald-600 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        v-if="!isEditMode">
                        <svg v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Salesman' }}
                    </button>
                    <button type="submit" :disabled="form.processing"
                        class="inline-flex items-center bg-gradient-to-r from-blue-400 to-blue-500 text-white font-bold py-3 px-8 rounded-full text-sm shadow-xl hover:scale-105 hover:from-blue-500 hover:to-blue-600 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        v-else>
                        <svg v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ form.processing ? 'Mengupdate...' : 'Update Salesman' }}
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>
