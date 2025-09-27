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
    products: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    categories: {
        type: Array,
        default: () => [],
    },
    divisions: {
        type: Array,
        default: () => [],
    },
    suppliers: {
        type: Array,
        default: () => [],
    },
    racks: {
        type: Array,
        default: () => [],
    },
});

const viewMode = ref('table');
const search = ref(props.filters?.search ?? '');
const isModalOpen = ref(false);
const isEditMode = ref(false);

const form = useForm({
    id: null,
    product_code: '',
    barcode: '',
    name: '',
    description: '',
    image: '',
    purchase_price: 0,
    selling_price: 0,
    member_price: 0,
    vip_price: 0,
    wholesale_price: 0,
    tax_rate: 0,
    tax_type: 'Y',
    min_wholesale_qty: 0,
    category_id: null,
    division_id: null,
    rack_id: null,
    supplier_id: null,
    unit: 'Pcs',
    weight: 0,
    min_stock_alert: 0,
    max_stock_alert: 0,
    reorder: '',
});

const productsList = computed(() => props.products?.data ?? []);

const margin = computed(() => {
    const purchase = Number(form.purchase_price) || 0;
    const selling = Number(form.selling_price) || 0;
    const taxRate = Number(form.tax_rate) || 0;
    const taxAmount = (purchase * taxRate) / 100;
    return selling - purchase - taxAmount;
});

const marginPercentage = computed(() => {
    const purchase = Number(form.purchase_price) || 0;
    if (purchase === 0) return 0;
    return ((margin.value / purchase) * 100).toFixed(2);
});

watch(
    () => search.value,
    debounce((value) => {
        router.get(
            route('master.products.index'),
            { search: value },
            {
                preserveState: true,
                replace: true,
            },
        );
    }, 300),
);

const formatCurrency = (value) => {
    if (value === null || value === undefined) {
        return '-';
    }

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(Number(value));
};

const getStockStatusClass = (stock, threshold) => {
    if (stock === null || stock === undefined) {
        return 'text-gray-300';
    }

    if (threshold !== null && threshold !== undefined && Number(stock) <= Number(threshold)) {
        return 'text-amber-400';
    }

    return 'text-emerald-400';
};

const getProductInitial = (name) => {
    if (!name) {
        return '?';
    }

    return name.charAt(0).toUpperCase();
};

const openModal = (editMode = false, product = null) => {
    isEditMode.value = editMode;

    if (editMode && product) {
        form.id = product.id;
        form.product_code = product.product_code;
        form.barcode = product.barcode;
        form.name = product.name;
        form.description = product.description;
        form.image = product.image;
        form.purchase_price = product.purchase_price;
        form.selling_price = product.selling_price;
        form.member_price = product.member_price;
        form.vip_price = product.vip_price;
        form.wholesale_price = product.wholesale_price;
        form.tax_rate = product.tax_rate;
        form.tax_type = product.tax_type;
        form.min_wholesale_qty = product.min_wholesale_qty;
        form.category_id = product.category_id;
        form.division_id = product.division_id;
        form.rack_id = product.rack_id;
        form.supplier_id = product.supplier_id;
        form.unit = product.unit ?? 'Pcs';
        form.weight = product.weight;
        form.min_stock_alert = product.min_stock_alert;
        form.max_stock_alert = product.max_stock_alert;
        form.reorder = product.reorder;
    } else {
        form.reset();
        form.unit = 'Pcs';
        form.tax_type = 'Y';
    }

    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
    form.unit = 'Pcs';
};

const saveProduct = () => {
    if (isEditMode.value) {
        form.patch(route('master.products.update', form.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('master.products.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteProduct = (productId) => {
    if (confirm('Apakah Anda yakin ingin menghapus produk ini?')) {
        useForm({}).delete(route('master.products.destroy', productId));
    }
};
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Master Produk" />

        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-xl text-white leading-tight">
                        Master Produk
                    </h2>
                    <p class="text-sm text-gray-400 mt-1">
                        Kelola katalog produk dan pantau stok dengan tampilan konsisten.
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
                        Tambah Produk
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
                    <Link :href="route('master.products.import.form')" class="inline-flex">
                    <button
                        class="inline-flex items-center bg-gradient-to-r from-blue-400 to-sky-400 text-white font-bold py-3 px-5 rounded-full text-sm shadow-lg hover:scale-105 hover:from-blue-500 hover:to-sky-500 transition-transform duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M4 12l4 4m0 0l4-4m-4 4V4" />
                        </svg>
                        Impor
                    </button>
                    </Link>
                    <a :href="route('master.products.export')" class="inline-flex">
                        <button
                            class="inline-flex items-center bg-gradient-to-r from-purple-400 to-pink-400 text-white font-bold py-3 px-5 rounded-full text-sm shadow-lg hover:scale-105 hover:from-purple-500 hover:to-pink-500 transition-transform duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v2a2 2 0 002 2h12a2 2 0 002-2V4M4 12l4-4m0 0l4 4m-4-4v12" />
                            </svg>
                            Ekspor
                        </button>
                    </a>
                </div>
            </div>

            <div v-if="productsList.length">
                <div v-if="viewMode === 'table'"
                    class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl shadow-xl overflow-hidden">
                    <div class="hidden lg:block overflow-x-auto">
                        <table class="min-w-full text-sm text-gray-200">
                            <thead
                                class="bg-gradient-to-r from-purple-600/30 to-blue-600/30 backdrop-blur-sm border-b border-white/20">
                                <tr>
                                    <th
                                        class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-white/90">
                                        Kode</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-white/90">
                                        Nama Produk</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-white/90">
                                        Kategori</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-white/90">
                                        Divisi</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-white/90">
                                        Rak</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-white/90">
                                        Harga Beli</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-white/90">
                                        Harga Jual</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-white/90">
                                        Stok</th>
                                    <th
                                        class="px-6 py-4 text-right text-xs uppercase tracking-wider font-semibold text-white/90">
                                        Aksi</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-700/50">
                                <tr v-for="product in productsList" :key="product.id"
                                    class="hover:bg-white/5 transition-all duration-200 group">
                                    <td class="px-6 py-4 font-mono text-sm">{{ product.product_code }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-sm font-bold">
                                                {{ getProductInitial(product.name) }}
                                            </div>
                                            <div>
                                                <p class="font-semibold text-white">{{ product.name }}</p>
                                                <p class="text-xs text-gray-400">Barcode: {{ product.barcode || '-' }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-300">{{ product.category?.name || '-' }}</td>
                                    <td class="px-6 py-4 text-gray-300">{{ product.division?.name || '-' }}</td>
                                    <td class="px-6 py-4 text-gray-300">{{ product.rack?.name || '-' }}</td>
                                    <td class="px-6 py-4 text-emerald-400 font-medium">{{
                                        formatCurrency(product.purchase_price)
                                        }}</td>
                                    <td class="px-6 py-4 text-blue-400 font-medium">{{
                                        formatCurrency(product.selling_price) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span :class="getStockStatusClass(product.stock, product.min_stock_alert)"
                                            class="font-semibold">
                                            {{ product.stock ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-end gap-2">
                                            <button @click="openModal(true, product)"
                                                class="inline-flex items-center px-4 py-2 rounded-full text-xs font-bold bg-gradient-to-r from-blue-400 to-blue-500 text-white shadow-lg hover:scale-105 hover:from-blue-500 hover:to-blue-600 transition-transform duration-200">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </button>
                                            <button @click="deleteProduct(product.id)"
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
                                    <th class="px-3 py-3 text-left font-semibold text-white/90">Produk</th>
                                    <th class="px-3 py-3 text-left font-semibold text-white/90">Harga</th>
                                    <th class="px-3 py-3 text-left font-semibold text-white/90">Stok</th>
                                    <th class="px-3 py-3 text-left font-semibold text-white/90">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700/50">
                                <tr v-for="product in productsList" :key="product.id" class="hover:bg-white/5">
                                    <td class="px-3 py-3">
                                        <div class="font-semibold text-white text-sm">{{ product.name }}</div>
                                        <div class="text-[11px] text-gray-400">{{ product.product_code }}</div>
                                        <div class="text-[11px] text-gray-500">{{ product.category?.name || '-' }}</div>
                                    </td>
                                    <td class="px-3 py-3 text-[11px] text-blue-400 font-semibold">{{
                                        formatCurrency(product.selling_price) }}</td>
                                    <td class="px-3 py-3 text-[11px]">
                                        <span :class="getStockStatusClass(product.stock, product.min_stock_alert)">
                                            {{ product.stock ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="flex flex-col gap-2">
                                            <button @click="openModal(true, product)"
                                                class="inline-flex items-center justify-center px-3 py-2 rounded-full text-[11px] font-bold bg-gradient-to-r from-blue-400 to-blue-500 text-white shadow-lg">
                                                Edit
                                            </button>
                                            <button @click="deleteProduct(product.id)"
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

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                    <div v-for="product in productsList" :key="product.id"
                        class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl p-6 shadow-xl hover:shadow-2xl hover:bg-white/10 transition-all duration-300 hover:-translate-y-1">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-lg font-bold">
                                    {{ getProductInitial(product.name) }}
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white">{{ product.name }}</h3>
                                    <p class="text-sm text-gray-400">{{ product.product_code }}</p>
                                </div>
                            </div>
                            <span
                                class="inline-flex px-3 py-1 rounded-full text-xs font-medium border border-white/20 bg-white/10 text-gray-200">
                                {{ product.category?.name || 'Tanpa Kategori' }}
                            </span>
                        </div>

                        <div class="space-y-3">
                            <div class="flex justify-between text-sm text-gray-300">
                                <span>Divisi</span>
                                <span>{{ product.division?.name || '-' }}</span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-300">
                                <span>Rak</span>
                                <span>{{ product.rack?.name || '-' }}</span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-300">
                                <span>Supplier</span>
                                <span>{{ product.supplier?.name || '-' }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-300">Harga Beli</span>
                                <span class="text-emerald-400 font-medium">{{ formatCurrency(product.purchase_price)
                                    }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-300">Harga Jual</span>
                                <span class="text-blue-400 font-medium">{{ formatCurrency(product.selling_price)
                                    }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-300">Harga Member</span>
                                <span class="text-purple-300 font-medium">{{ formatCurrency(product.member_price)
                                    }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-300">Stok</span>
                                <span :class="getStockStatusClass(product.stock, product.min_stock_alert)"
                                    class="font-semibold">
                                    {{ product.stock ?? '-' }} {{ product.unit || 'Pcs' }}
                                </span>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-3 pt-4 border-t border-gray-800/60 mt-4">
                            <button @click="openModal(true, product)"
                                class="inline-flex items-center px-4 py-2 rounded-full text-xs font-bold bg-gradient-to-r from-blue-400 to-blue-500 text-white shadow-lg hover:scale-105 hover:from-blue-500 hover:to-blue-600 transition-transform duration-200">
                                <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit Produk
                            </button>
                            <button @click="deleteProduct(product.id)"
                                class="inline-flex items-center px-4 py-2 rounded-full text-xs font-bold bg-gradient-to-r from-red-400 to-red-500 text-white shadow-lg hover:scale-105 hover:from-red-500 hover:to-red-600 transition-transform duration-200">
                                <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Hapus Produk
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-12 backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="text-lg font-medium text-gray-300 mb-2">Belum ada produk</h3>
                <p class="text-gray-400">Tambah produk baru atau impor dari template untuk memulai.</p>
            </div>

            <Pagination v-if="props.products?.links" :links="props.products.links" />
        </div>

        <Modal :show="isModalOpen" @close="closeModal">
            <div
                class="p-8 bg-gray-900/70 backdrop-blur-md border border-white/10 rounded-2xl shadow-lg max-w-4xl mx-auto space-y-6 text-white">
                <h2 class="text-lg font-semibold">
                    {{ isEditMode ? 'Edit Produk' : 'Tambah Produk Baru' }}
                </h2>

                <form @submit.prevent="saveProduct" class="mt-6 space-y-8">

                    <!-- Basic Information Section -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-white border-b border-white/20 pb-2">Informasi Dasar</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <InputLabel for="product_code" value="Kode Produk" class="text-gray-300" />
                                <TextInput id="product_code" v-model="form.product_code"
                                    class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500"
                                    required />
                                <InputError :message="form.errors.product_code" class="mt-2 text-red-400" />
                            </div>

                            <div>
                                <InputLabel for="barcode" value="Barcode" class="text-gray-300" />
                                <TextInput id="barcode" v-model="form.barcode"
                                    class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500" />
                                <InputError :message="form.errors.barcode" class="mt-2 text-red-400" />
                            </div>

                            <div>
                                <InputLabel for="name" value="Nama Produk" class="text-gray-300" />
                                <TextInput id="name" v-model="form.name"
                                    class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500"
                                    required />
                                <InputError :message="form.errors.name" class="mt-2 text-red-400" />
                            </div>

                            <div class="md:col-span-3">
                                <InputLabel for="description" value="Deskripsi" class="text-gray-300" />
                                <TextInput id="description" v-model="form.description"
                                    class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500" />
                                <InputError :message="form.errors.description" class="mt-2 text-red-400" />
                            </div>

                            <div>
                                <InputLabel for="image" value="URL Gambar" class="text-gray-300" />
                                <TextInput id="image" v-model="form.image"
                                    class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500" />
                                <InputError :message="form.errors.image" class="mt-2 text-red-400" />
                            </div>

                            <div>
                                <InputLabel for="category_id" value="Kategori" class="text-gray-300" />
                                <select id="category_id" v-model="form.category_id"
                                    class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Pilih Kategori</option>
                                    <option v-for="cat in props.categories" :key="cat.id" :value="cat.id">{{ cat.name }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.category_id" class="mt-2 text-red-400" />
                            </div>

                            <div>
                                <InputLabel for="division_id" value="Divisi" class="text-gray-300" />
                                <select id="division_id" v-model="form.division_id"
                                    class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Pilih Divisi</option>
                                    <option v-for="div in props.divisions" :key="div.id" :value="div.id">{{ div.name }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.division_id" class="mt-2 text-red-400" />
                            </div>

                            <div>
                                <InputLabel for="rack_id" value="Rak" class="text-gray-300" />
                                <select id="rack_id" v-model="form.rack_id"
                                    class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Pilih Rak</option>
                                    <option v-for="rack in props.racks" :key="rack.id" :value="rack.id">{{ rack.name }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.rack_id" class="mt-2 text-red-400" />
                            </div>

                            <div>
                                <InputLabel for="supplier_id" value="Supplier" class="text-gray-300" />
                                <select id="supplier_id" v-model="form.supplier_id"
                                    class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Pilih Supplier</option>
                                    <option v-for="sup in props.suppliers" :key="sup.id" :value="sup.id">{{ sup.name }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.supplier_id" class="mt-2 text-red-400" />
                            </div>

                            <div>
                                <InputLabel for="unit" value="Satuan" class="text-gray-300" />
                                <select id="unit" v-model="form.unit"
                                    class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="Pcs">Pcs</option>
                                    <option value="Box">Box</option>
                                    <option value="Kg">Kg</option>
                                    <option value="Liter">Liter</option>
                                    <option value="Pack">Pack</option>
                                </select>
                                <InputError :message="form.errors.unit" class="mt-2 text-red-400" />
                            </div>

                            <div>
                                <InputLabel for="weight" value="Berat (kg)" class="text-gray-300" />
                                <TextInput id="weight" type="number" step="0.01" v-model="form.weight"
                                    class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500" />
                                <InputError :message="form.errors.weight" class="mt-2 text-red-400" />
                            </div>
                        </div>
                    </div>

                    <!-- Pricing Section -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-white border-b border-white/20 pb-2">Harga & Pajak</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <InputLabel for="purchase_price" value="Harga Beli" class="text-gray-300" />
                                <TextInput id="purchase_price" type="number" v-model="form.purchase_price"
                                    class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500"
                                    required />
                                <InputError :message="form.errors.purchase_price" class="mt-2 text-red-400" />
                            </div>

                            <div>
                                <InputLabel for="selling_price" value="Harga Jual" class="text-gray-300" />
                                <TextInput id="selling_price" type="number" v-model="form.selling_price"
                                    class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500"
                                    required />
                                <InputError :message="form.errors.selling_price" class="mt-2 text-red-400" />
                            </div>

                            <div>
                                <InputLabel for="tax_rate" value="Tarif Pajak (%)" class="text-gray-300" />
                                <TextInput id="tax_rate" type="number" step="0.01" v-model="form.tax_rate"
                                    class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500" />
                                <InputError :message="form.errors.tax_rate" class="mt-2 text-red-400" />
                            </div>

                            <div>
                                <InputLabel for="tax_type" value="Tipe Pajak" class="text-gray-300" />
                                <select id="tax_type" v-model="form.tax_type"
                                    class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="Y">Ya</option>
                                    <option value="N">Tidak</option>
                                </select>
                                <InputError :message="form.errors.tax_type" class="mt-2 text-red-400" />
                            </div>

                            <div>
                                <InputLabel for="member_price" value="Harga Member" class="text-gray-300" />
                                <TextInput id="member_price" type="number" v-model="form.member_price"
                                    class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500" />
                                <InputError :message="form.errors.member_price" class="mt-2 text-red-400" />
                            </div>

                            <div>
                                <InputLabel for="vip_price" value="Harga VIP" class="text-gray-300" />
                                <TextInput id="vip_price" type="number" v-model="form.vip_price"
                                    class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500" />
                                <InputError :message="form.errors.vip_price" class="mt-2 text-red-400" />
                            </div>

                            <div>
                                <InputLabel for="wholesale_price" value="Harga Grosir" class="text-gray-300" />
                                <TextInput id="wholesale_price" type="number" v-model="form.wholesale_price"
                                    class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500" />
                                <InputError :message="form.errors.wholesale_price" class="mt-2 text-red-400" />
                            </div>

                            <div>
                                <InputLabel for="min_wholesale_qty" value="Min Qty Grosir" class="text-gray-300" />
                                <TextInput id="min_wholesale_qty" type="number" v-model="form.min_wholesale_qty"
                                    class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500" />
                                <InputError :message="form.errors.min_wholesale_qty" class="mt-2 text-red-400" />
                            </div>

                            <div class="md:col-span-3">
                                <div class="bg-white/5 rounded-lg p-4 border border-white/20">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <span class="text-gray-300">Margin:</span>
                                            <span class="text-green-400 font-semibold ml-2">{{ formatCurrency(margin)
                                                }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-300">Margin %:</span>
                                            <span class="text-blue-400 font-semibold ml-2">{{ marginPercentage
                                                }}%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Inventory Section -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-white border-b border-white/20 pb-2">Stok & Inventory</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <InputLabel for="stock" value="Stok" class="text-gray-300" />
                                <TextInput id="stock" type="number" v-model="form.stock"
                                    class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500" />
                                <InputError :message="form.errors.stock" class="mt-2 text-red-400" />
                            </div>

                            <div>
                                <InputLabel for="min_stock_alert" value="Min Stok Alert" class="text-gray-300" />
                                <TextInput id="min_stock_alert" type="number" v-model="form.min_stock_alert"
                                    class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500" />
                                <InputError :message="form.errors.min_stock_alert" class="mt-2 text-red-400" />
                            </div>

                            <div>
                                <InputLabel for="max_stock_alert" value="Max Stok Alert" class="text-gray-300" />
                                <TextInput id="max_stock_alert" type="number" v-model="form.max_stock_alert"
                                    class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500" />
                                <InputError :message="form.errors.max_stock_alert" class="mt-2 text-red-400" />
                            </div>

                            <div>
                                <InputLabel for="reorder" value="Reorder Point" class="text-gray-300" />
                                <TextInput id="reorder" v-model="form.reorder"
                                    class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500" />
                                <InputError :message="form.errors.reorder" class="mt-2 text-red-400" />
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-3 pt-6 border-t border-white/20">
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
