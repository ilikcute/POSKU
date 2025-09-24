<script setup>
import { computed, ref, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Pagination from '@/Components/Pagination.vue';
import { debounce } from 'lodash';

const props = defineProps({
    promotions: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const viewMode = ref('table');
const search = ref(props.filters?.search ?? '');
const isModalOpen = ref(false);
const isEditMode = ref(false);

const promotionList = computed(() => props.promotions?.data ?? []);

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

watch(
    () => search.value,
    debounce((value) => {
        router.get(
            route('promotions.index'),
            { search: value },
            {
                preserveState: true,
                replace: true,
            },
        );
    }, 300),
);

const openModal = (editMode = false, promotion = null) => {
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
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
    form.promotion_type = 'product_discount';
    form.discount_type = 'percentage';
};

const saveItem = () => {
    if (isEditMode.value) {
        form.patch(route('promotions.update', form.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('promotions.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteItem = (id) => {
    if (confirm('Yakin ingin menghapus promo ini?')) {
        useForm({}).delete(route('promotions.destroy', id));
    }
};

const formatDiscountType = (type) => (type === 'percentage' ? 'Persentase' : 'Nominal');

const formatDiscountValue = (value, type) => {
    if (type === 'percentage') {
        return `${value}%`;
    }

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const formatDate = (dateString) => new Date(dateString).toLocaleDateString('id-ID');
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Master Promosi" />

        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-xl text-white leading-tight">
                        Master Promosi
                    </h2>
                    <p class="text-sm text-gray-400 mt-1">
                        Kelola program promosi dengan tampilan yang lebih seragam.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="flex bg-gray-800/50 rounded-lg p-1 backdrop-blur-sm">
                        <button
                            @click="viewMode = 'table'"
                            :class="[
                                'px-3 py-2 rounded-md text-sm font-medium transition-all duration-200',
                                viewMode === 'table'
                                    ? 'bg-white/20 text-white shadow-sm'
                                    : 'text-gray-300 hover:text-white hover:bg-white/10'
                            ]"
                        >
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18m-9 10h9" />
                            </svg>
                            Table
                        </button>
                        <button
                            @click="viewMode = 'cards'"
                            :class="[
                                'px-3 py-2 rounded-md text-sm font-medium transition-all duration-200',
                                viewMode === 'cards'
                                    ? 'bg-white/20 text-white shadow-sm'
                                    : 'text-gray-300 hover:text-white hover:bg-white/10'
                            ]"
                        >
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14-7H5a2 2 0 00-2 2v12a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2z" />
                            </svg>
                            Cards
                        </button>
                    </div>

                    <button
                        @click="openModal(false)"
                        class="inline-flex items-center justify-center bg-gradient-to-r from-green-400 to-emerald-400 text-white font-bold py-3 px-6 rounded-full text-sm shadow-xl hover:scale-105 hover:from-green-500 hover:to-emerald-500 transition-transform duration-200"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Promosi
                    </button>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl shadow-xl p-6 mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
            >
                <div class="flex w-full lg:w-auto items-center gap-3">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
                    </svg>
                    <TextInput
                        v-model="search"
                        type="text"
                        class="w-full lg:w-80 bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Cari nama promosi..."
                    />
                </div>
            </div>

            <div v-if="promotionList.length">
                <div
                    v-if="viewMode === 'table'"
                    class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl shadow-xl overflow-hidden"
                >
                    <div class="hidden lg:block overflow-x-auto">
                        <table class="min-w-full text-sm text-gray-200">
                            <thead class="bg-gradient-to-r from-purple-600/30 to-blue-600/30 backdrop-blur-sm border-b border-white/20">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-white/90">Nama Promo</th>
                             
