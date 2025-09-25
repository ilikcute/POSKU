<template>
    <Head title="Master Promosi" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Master Promosi
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
                        Tambah Promosi
                    </button>
                    <TextInput
                        v-model="search"
                        type="text"
                        placeholder="Cari nama promosi..."
                        class="w-80 px-4 py-2 border border-gray-300 rounded text-xs font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    />
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wide">Nama Promo</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wide">Tipe Diskon</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wide">Nilai Diskon</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wide">Tanggal Mulai</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wide">Tanggal Selesai</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wide">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="promotion in promotions.data" :key="promotion.id" class="hover:bg-gray-50">
                                <td class="px-4 py-2 whitespace-nowrap font-medium text-gray-900">{{ promotion.name }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ formatDiscountType(promotion.discount_type) }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ formatDiscountValue(promotion.discount_value, promotion.discount_type) }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ formatDate(promotion.start_date) }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ formatDate(promotion.end_date) }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-2">
                                        <button
                                            @click="openModal(true, promotion)"
                                            class="inline-flex items-center px-2 py-1 border border-gray-300 text-xs font-medium rounded text-indigo-600 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                        >
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Edit
                                        </button>
                                        <button
                                            @click="deleteItem(promotion.id)"
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
                            <tr v-if="promotions.data.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        <p class="text-lg font-medium text-gray-900">Belum ada data promosi</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination :links="promotions.links" />
            </div>
        </div>
        <Modal :show="isModalOpen" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    {{ isEditMode ? "Edit Promosi" : "Tambah Promosi Baru" }}
                </h2>
                <form @submit.prevent="saveItem" class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="name" value="Nama Promo" />
                        <TextInput id="name" v-model="form.name" class="mt-1 block w-full" placeholder="Nama promosi" required />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="discount_type" value="Tipe Diskon" />
                        <select id="discount_type" v-model="form.discount_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="percentage">Persentase (%)</option>
                            <option value="fixed_amount">Nominal (Rp)</option>
                        </select>
                        <InputError :message="form.errors.discount_type" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="discount_value" value="Nilai Diskon" />
                        <TextInput id="discount_value" v-model="form.discount_value" type="number" class="mt-1 block w-full" placeholder="Nilai diskon" required />
                        <InputError :message="form.errors.discount_value" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="start_date" value="Tanggal Mulai" />
                        <TextInput id="start_date" v-model="form.start_date" type="date" class="mt-1 block w-full" required />
                        <InputError :message="form.errors.start_date" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="end_date" value="Tanggal Selesai" />
                        <TextInput id="end_date" v-model="form.end_date" type="date" class="mt-1 block w-full" required />
                        <InputError :message="form.errors.end_date" class="mt-2" />
                    </div>
                    <div class="md:col-span-2 flex justify-end gap-2 mt-4">
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
                            {{ isEditMode ? 'Update' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Pagination from '@/Components/Pagination.vue';
import { debounce } from 'lodash';

const props = defineProps({
    promotions: Object,
    filters: Object,
});

const search = ref(props.filters.search);
const isModalOpen = ref(false);
const isEditMode = ref(false);

const form = useForm({
    id: null,
    name: '',
    description: '',
    promotion_type: 'product_discount',
    discount_type: 'percentage',
    discount_value: '',
    start_date: '',
    end_date: '',
});

// Search functionality dengan debounce seperti di products
watch(
    search,
    debounce((newValue) => {
        router.get(
            route("promotions.index"),
            { search: newValue },
            {
                preserveState: true,
                replace: true,
            }
        );
    }, 300)
);

function openModal(editMode = false, promotion = null) {
    isEditMode.value = editMode;
    if (editMode && promotion) {
        form.id = promotion.id;
        form.name = promotion.name;
        form.description = promotion.description || '';
        form.promotion_type = promotion.promotion_type || 'product_discount';
        form.discount_type = promotion.discount_type;
        form.discount_value = promotion.discount_value;
        form.start_date = promotion.start_date;
        form.end_date = promotion.end_date;
    } else {
        form.reset();
        form.promotion_type = 'product_discount';
        form.discount_type = 'percentage';
    }
    isModalOpen.value = true;
}

function closeModal() {
    isModalOpen.value = false;
    form.reset();
}

function saveItem() {
    if (isEditMode.value) {
        form.patch(route('promotions.update', form.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('promotions.store'), {
            onSuccess: () => closeModal(),
        });
    }
}

function deleteItem(id) {
    if (confirm('Yakin ingin menghapus promo ini?')) {
        useForm({}).delete(route('promotions.destroy', id));
    }
}

// Helper functions untuk formatting
function formatDiscountType(type) {
    return type === 'percentage' ? 'Persentase' : 'Nominal';
}

function formatDiscountValue(value, type) {
    if (type === 'percentage') {
        return `${value}%`;
    } else {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        }).format(value);
    }
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('id-ID');
}
</script>