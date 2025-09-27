<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    products: {
        type: Array,
        default: () => [],
    },
});

const productCodes = ref('');
const barcodeType = ref('code128');
const rows = ref(5);
const columns = ref(3);
const paperSize = ref('A4');
const isGenerating = ref(false);
const generatedBarcodes = ref([]);
const generatedPriceTags = ref([]);
const activeTab = ref('barcode'); // 'barcode' or 'price-tag'

const form = useForm({
    product_codes: [],
    barcode_type: 'code128',
    rows: 5,
    columns: 3,
    paper_size: 'A4',
});

const parsedProductCodes = computed(() => {
    return productCodes.value
        .split('\n')
        .map(code => code.trim())
        .filter(code => code.length > 0);
});

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

const generateBarcodes = async () => {
    if (parsedProductCodes.value.length === 0) {
        alert('Masukkan minimal satu kode produk');
        return;
    }

    isGenerating.value = true;

    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    try {
        const response = await axios.post(route('master.barcodes.generate'), {
            product_codes: parsedProductCodes.value,
            barcode_type: barcodeType.value,
            rows: rows.value,
            columns: columns.value,
            paper_size: paperSize.value,
        }, {
            headers: {
                'X-CSRF-TOKEN': token
            }
        });

        generatedBarcodes.value = response.data.barcode_data;
    } catch (error) {
        console.error('Error generating barcodes:', error);
        alert('Terjadi kesalahan saat generate barcode');
    } finally {
        isGenerating.value = false;
    }
};

const printBarcodes = () => {
    if (generatedBarcodes.value.length === 0) {
        alert('Generate barcode terlebih dahulu');
        return;
    }

    const printForm = document.createElement('form');
    printForm.method = 'POST';
    printForm.target = '_blank';
    printForm.action = route('master.barcodes.print');
    printForm.style.display = 'none';

    // CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    printForm.appendChild(csrfInput);

    // Product codes
    const productCodesInput = document.createElement('input');
    productCodesInput.type = 'hidden';
    productCodesInput.name = 'product_codes';
    productCodesInput.value = JSON.stringify(parsedProductCodes.value);
    printForm.appendChild(productCodesInput);

    // Barcode type
    const barcodeTypeInput = document.createElement('input');
    barcodeTypeInput.type = 'hidden';
    barcodeTypeInput.name = 'barcode_type';
    barcodeTypeInput.value = barcodeType.value;
    printForm.appendChild(barcodeTypeInput);

    // Rows
    const rowsInput = document.createElement('input');
    rowsInput.type = 'hidden';
    rowsInput.name = 'rows';
    rowsInput.value = rows.value;
    printForm.appendChild(rowsInput);

    // Columns
    const columnsInput = document.createElement('input');
    columnsInput.type = 'hidden';
    columnsInput.name = 'columns';
    columnsInput.value = columns.value;
    printForm.appendChild(columnsInput);

    // Paper size
    const paperSizeInput = document.createElement('input');
    paperSizeInput.type = 'hidden';
    paperSizeInput.name = 'paper_size';
    paperSizeInput.value = paperSize.value;
    printForm.appendChild(paperSizeInput);

    document.body.appendChild(printForm);
    printForm.submit();
    document.body.removeChild(printForm);

    // Redirect to dashboard after opening print window
    router.visit(route('dashboard'));
};

const generatePriceTags = async () => {
    if (parsedProductCodes.value.length === 0) {
        alert('Masukkan minimal satu kode produk');
        return;
    }

    isGenerating.value = true;

    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    try {
        const response = await axios.post(route('master.barcodes.generate-price-tags'), {
            product_codes: parsedProductCodes.value,
            rows: rows.value,
            columns: columns.value,
            paper_size: paperSize.value,
        }, {
            headers: {
                'X-CSRF-TOKEN': token
            }
        });

        generatedPriceTags.value = response.data.price_tag_data;
    } catch (error) {
        console.error('Error generating price tags:', error);
        alert('Terjadi kesalahan saat generate price tag');
    } finally {
        isGenerating.value = false;
    }
};

const printPriceTags = () => {
    if (generatedPriceTags.value.length === 0) {
        alert('Generate price tag terlebih dahulu');
        return;
    }

    const printForm = document.createElement('form');
    printForm.method = 'POST';
    printForm.target = '_blank';
    printForm.action = route('master.barcodes.print-price-tags');
    printForm.style.display = 'none';

    // CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    printForm.appendChild(csrfInput);

    // Product codes
    const productCodesInput = document.createElement('input');
    productCodesInput.type = 'hidden';
    productCodesInput.name = 'product_codes';
    productCodesInput.value = JSON.stringify(parsedProductCodes.value);
    printForm.appendChild(productCodesInput);

    // Rows
    const rowsInput = document.createElement('input');
    rowsInput.type = 'hidden';
    rowsInput.name = 'rows';
    rowsInput.value = rows.value;
    printForm.appendChild(rowsInput);

    // Columns
    const columnsInput = document.createElement('input');
    columnsInput.type = 'hidden';
    columnsInput.name = 'columns';
    columnsInput.value = columns.value;
    printForm.appendChild(columnsInput);

    // Paper size
    const paperSizeInput = document.createElement('input');
    paperSizeInput.type = 'hidden';
    paperSizeInput.name = 'paper_size';
    paperSizeInput.value = paperSize.value;
    printForm.appendChild(paperSizeInput);

    document.body.appendChild(printForm);
    printForm.submit();
    document.body.removeChild(printForm);

    // Redirect to dashboard after opening print window
    router.visit(route('dashboard'));
};

const clearForm = () => {
    productCodes.value = '';
    generatedBarcodes.value = [];
    generatedPriceTags.value = [];
    form.reset();
};
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Cetak Barcode" />

        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-xl text-white leading-tight">
                        Cetak Barcode
                    </h2>
                    <p class="text-sm text-gray-400 mt-1">
                        Generate dan cetak barcode untuk produk yang tidak memiliki barcode pabrik.
                    </p>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Tabs -->
            <div class="mb-6">
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <button @click="activeTab = 'barcode'"
                            :class="activeTab === 'barcode' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                            Barcode
                        </button>
                        <button @click="activeTab = 'price-tag'"
                            :class="activeTab === 'price-tag' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                            Price Tag
                        </button>
                    </nav>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Form Section -->
                <div class="space-y-6">
                    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl shadow-xl p-6">
                        <h3 class="text-lg font-semibold text-white mb-4">
                            {{ activeTab === 'barcode' ? 'Konfigurasi Barcode' : 'Konfigurasi Price Tag' }}
                        </h3>

                        <form v-if="activeTab === 'barcode'" @submit.prevent="generateBarcodes" class="space-y-6">
                            <!-- Product Codes -->
                            <div>
                                <InputLabel for="product_codes" value="Kode Produk" class="text-gray-300" />
                                <textarea id="product_codes" v-model="productCodes"
                                    class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500 min-h-32"
                                    placeholder="Masukkan kode produk, satu per baris&#10;Contoh:&#10;PROD001&#10;PROD002&#10;PROD003"
                                    required></textarea>
                                <p class="text-xs text-gray-400 mt-1">
                                    Masukkan kode produk yang akan dicetak barcodenya, satu kode per baris.
                                    Total: {{ parsedProductCodes.length }} produk
                                </p>
                                <InputError :message="form.errors.product_codes" class="mt-2 text-red-400" />
                            </div>

                            <!-- Barcode Type -->
                            <div>
                                <InputLabel for="barcode_type" value="Tipe Barcode" class="text-gray-300" />
                                <select id="barcode_type" v-model="barcodeType"
                                    class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="code128">Barcode (Code 128)</option>
                                    <option value="qrcode">QR Code</option>
                                </select>
                                <InputError :message="form.errors.barcode_type" class="mt-2 text-red-400" />
                            </div>

                            <!-- Layout Configuration -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <InputLabel for="rows" value="Baris per Halaman" class="text-gray-300" />
                                    <select id="rows" v-model="rows"
                                        class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500">
                                        <option v-for="r in 20" :key="r" :value="r">{{ r }}</option>
                                    </select>
                                    <InputError :message="form.errors.rows" class="mt-2 text-red-400" />
                                </div>

                                <div>
                                    <InputLabel for="columns" value="Kolom per Baris" class="text-gray-300" />
                                    <select id="columns" v-model="columns"
                                        class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500">
                                        <option v-for="c in 10" :key="c" :value="c">{{ c }}</option>
                                    </select>
                                    <InputError :message="form.errors.columns" class="mt-2 text-red-400" />
                                </div>
                            </div>

                            <!-- Paper Size -->
                            <div>
                                <InputLabel for="paper_size" value="Ukuran Kertas" class="text-gray-300" />
                                <select id="paper_size" v-model="paperSize"
                                    class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="A4">A4</option>
                                </select>
                                <InputError :message="form.errors.paper_size" class="mt-2 text-red-400" />
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-3">
                                <button type="submit" :disabled="isGenerating || parsedProductCodes.length === 0"
                                    class="flex-1 inline-flex items-center justify-center bg-gradient-to-r from-blue-400 to-sky-400 text-white font-bold py-3 px-6 rounded-full text-sm shadow-lg hover:scale-105 hover:from-blue-500 hover:to-sky-500 transition-transform duration-200 disabled:opacity-50">
                                    <svg v-if="isGenerating" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    {{ isGenerating ? 'Generating...' : 'Generate Barcode' }}
                                </button>

                                <button type="button" @click="clearForm"
                                    class="inline-flex items-center justify-center bg-gradient-to-r from-gray-400 to-gray-500 text-white font-bold py-3 px-6 rounded-full text-sm shadow-lg hover:scale-105 hover:from-gray-500 hover:to-gray-600 transition-transform duration-200">
                                    Clear
                                </button>
                            </div>
                        </form>

                        <form v-else @submit.prevent="generatePriceTags" class="space-y-6">
                            <!-- Product Codes -->
                            <div>
                                <InputLabel for="product_codes_price" value="Kode Produk" class="text-gray-300" />
                                <textarea id="product_codes_price" v-model="productCodes"
                                    class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500 min-h-32"
                                    placeholder="Masukkan kode produk, satu per baris&#10;Contoh:&#10;PROD001&#10;PROD002&#10;PROD003"
                                    required></textarea>
                                <p class="text-xs text-gray-400 mt-1">
                                    Masukkan kode produk yang akan dicetak price tagnya, satu kode per baris.
                                    Total: {{ parsedProductCodes.length }} produk
                                </p>
                                <InputError :message="form.errors.product_codes" class="mt-2 text-red-400" />
                            </div>

                            <!-- Layout Configuration -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <InputLabel for="rows_price" value="Baris per Halaman" class="text-gray-300" />
                                    <select id="rows_price" v-model="rows"
                                        class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500">
                                        <option v-for="r in 20" :key="r" :value="r">{{ r }}</option>
                                    </select>
                                    <InputError :message="form.errors.rows" class="mt-2 text-red-400" />
                                </div>

                                <div>
                                    <InputLabel for="columns_price" value="Kolom per Baris" class="text-gray-300" />
                                    <select id="columns_price" v-model="columns"
                                        class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500">
                                        <option v-for="c in 10" :key="c" :value="c">{{ c }}</option>
                                    </select>
                                    <InputError :message="form.errors.columns" class="mt-2 text-red-400" />
                                </div>
                            </div>

                            <!-- Paper Size -->
                            <div>
                                <InputLabel for="paper_size_price" value="Ukuran Kertas" class="text-gray-300" />
                                <select id="paper_size_price" v-model="paperSize"
                                    class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="A4">A4</option>
                                </select>
                                <InputError :message="form.errors.paper_size" class="mt-2 text-red-400" />
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-3">
                                <button type="submit" :disabled="isGenerating || parsedProductCodes.length === 0"
                                    class="flex-1 inline-flex items-center justify-center bg-gradient-to-r from-green-400 to-emerald-400 text-white font-bold py-3 px-6 rounded-full text-sm shadow-lg hover:scale-105 hover:from-green-500 hover:to-emerald-500 transition-transform duration-200 disabled:opacity-50">
                                    <svg v-if="isGenerating" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    {{ isGenerating ? 'Generating...' : 'Generate Price Tag' }}
                                </button>

                                <button type="button" @click="clearForm"
                                    class="inline-flex items-center justify-center bg-gradient-to-r from-gray-400 to-gray-500 text-white font-bold py-3 px-6 rounded-full text-sm shadow-lg hover:scale-105 hover:from-gray-500 hover:to-gray-600 transition-transform duration-200">
                                    Clear
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Preview Section -->
                <div class="space-y-6">
                    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl shadow-xl p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-white">
                                {{ activeTab === 'barcode' ? 'Preview Barcode' : 'Preview Price Tag' }}
                            </h3>
                            <button v-if="activeTab === 'barcode' && generatedBarcodes.length > 0"
                                @click="printBarcodes"
                                class="inline-flex items-center bg-gradient-to-r from-green-400 to-emerald-400 text-white font-bold py-2 px-4 rounded-full text-sm shadow-lg hover:scale-105 hover:from-green-500 hover:to-emerald-500 transition-transform duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Print
                            </button>
                            <button v-else-if="activeTab === 'price-tag' && generatedPriceTags.length > 0"
                                @click="printPriceTags"
                                class="inline-flex items-center bg-gradient-to-r from-green-400 to-emerald-400 text-white font-bold py-2 px-4 rounded-full text-sm shadow-lg hover:scale-105 hover:from-green-500 hover:to-emerald-500 transition-transform duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Print
                            </button>
                        </div>

                        <div v-if="activeTab === 'barcode'">
                            <div v-if="generatedBarcodes.length === 0" class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <h3 class="text-lg font-medium text-gray-300 mb-2">Belum ada barcode</h3>
                                <p class="text-gray-400">Generate barcode terlebih dahulu untuk melihat preview.</p>
                            </div>

                            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div v-for="barcode in generatedBarcodes" :key="barcode.product_code"
                                    class="backdrop-blur-md bg-white/5 border border-white/10 rounded-lg p-4 text-center">
                                    <div class="mb-2">
                                        <img v-if="barcode.barcode_image" :src="barcode.barcode_image"
                                            :alt="`Barcode ${barcode.product_code}`"
                                            class="mx-auto max-w-full h-16 object-contain" />
                                    </div>
                                    <div class="text-xs text-gray-300 font-mono">{{ barcode.product_code }}</div>
                                    <div class="text-xs text-gray-400 truncate">{{ barcode.name }}</div>
                                </div>
                            </div>

                            <div v-if="generatedBarcodes.length > 0" class="mt-4 text-center">
                                <p class="text-sm text-gray-400">
                                    Total: {{ generatedBarcodes.length }} barcode • Layout: {{ rows }} baris × {{
                                        columns }}
                                    kolom
                                </p>
                            </div>
                        </div>

                        <div v-else>
                            <div v-if="generatedPriceTags.length === 0" class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                <h3 class="text-lg font-medium text-gray-300 mb-2">Belum ada price tag</h3>
                                <p class="text-gray-400">Generate price tag terlebih dahulu untuk melihat preview.</p>
                            </div>

                            <div v-else class="grid grid-cols-1 gap-4">
                                <div v-for="priceTag in generatedPriceTags" :key="priceTag.product_code"
                                    class="backdrop-blur-md bg-white border border-gray-200 rounded-lg p-4 shadow-lg">

                                    <!-- Layout Landscape: Informasi kiri, Harga kanan -->
                                    <div class="flex items-center justify-between gap-4">

                                        <!-- Bagian Kiri: Info Produk -->
                                        <div class="flex-1 text-left">
                                            <div class="text-sm text-gray-700 font-mono mb-1 font-semibold">{{
                                                priceTag.product_code }}</div>
                                            <div class="text-sm text-gray-600 mb-2 leading-tight">{{ priceTag.name }}
                                            </div>

                                            <!-- Info Promo di bagian kiri bawah -->
                                            <div v-if="priceTag.has_promotion"
                                                class="text-xs text-red-600 font-bold mb-1 bg-red-50 px-2 py-1 rounded inline-block">
                                                🏷️ {{ priceTag.promotion_name }}
                                            </div>
                                            <div v-if="priceTag.has_promotion" class="text-xs text-red-500">
                                                Sampai: {{ priceTag.promotion_end_date }}
                                            </div>
                                        </div>

                                        <!-- Bagian Kanan: Harga -->
                                        <div class="flex-shrink-0 text-right">
                                            <!-- Harga Coret (jika ada promo) -->
                                            <div v-if="priceTag.has_promotion"
                                                class="text-lg line-through text-gray-500 mb-1"
                                                style="font-family: 'Tahoma', sans-serif;">
                                                {{ formatCurrency(priceTag.original_price) }}
                                            </div>

                                            <!-- Harga Utama - Layout Landscape -->
                                            <div class="text-3xl font-black text-black leading-none bg-yellow-100 px-3 py-2 rounded-lg border-2 border-yellow-300 shadow-inner min-w-max"
                                                style="font-family: 'Tahoma', sans-serif; font-weight: 900; text-shadow: 1px 1px 0px rgba(0,0,0,0.1);">
                                                {{ formatCurrency(priceTag.final_price) }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Footer -->
                                    <div class="text-xs text-gray-400 border-t pt-2 mt-2">{{ priceTag.printed_at }}
                                    </div>
                                </div>
                            </div>

                            <div v-if="generatedPriceTags.length > 0" class="mt-4 text-center">
                                <p class="text-sm text-gray-400">
                                    Total: {{ generatedPriceTags.length }} price tag • Layout: {{ rows }} baris × {{
                                        columns }}
                                    kolom
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>