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
const customerCodes = ref('');
const barcodeType = ref('code128');
const rows = ref(5);
const activeTab = ref('barcode'); // 'barcode', 'price-tag', or 'customer-card'
const columns = ref(activeTab.value === 'customer-card' ? 2 : 3);
const paperSize = ref('A4');
const isGenerating = ref(false);
const generatedBarcodes = ref([]);
const generatedPriceTags = ref([]);
const generatedCustomerCards = ref([]);

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

const parsedCustomerCodes = computed(() => {
    return customerCodes.value
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
        if (error.response && error.response.status === 422) {
            // Validation errors
            const errors = error.response.data.errors;
            let errorMessage = 'Kesalahan validasi:\n';
            for (const field in errors) {
                errorMessage += `${field}: ${errors[field].join(', ')}\n`;
            }
            alert(errorMessage);
        } else {
            alert('Terjadi kesalahan saat generate barcode');
        }
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
        if (error.response && error.response.status === 422) {
            // Validation errors
            const errors = error.response.data.errors;
            let errorMessage = 'Kesalahan validasi:\n';
            for (const field in errors) {
                errorMessage += `${field}: ${errors[field].join(', ')}\n`;
            }
            alert(errorMessage);
        } else {
            alert('Terjadi kesalahan saat generate price tag');
        }
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

const generateCustomerCards = async () => {
    if (parsedCustomerCodes.value.length === 0) {
        alert('Masukkan minimal satu kode customer');
        return;
    }

    isGenerating.value = true;

    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    try {
        const response = await axios.post(route('master.barcodes.generate-customer-cards'), {
            customer_codes: parsedCustomerCodes.value,
            rows: rows.value,
            columns: columns.value,
            paper_size: paperSize.value,
        }, {
            headers: {
                'X-CSRF-TOKEN': token
            }
        });

        generatedCustomerCards.value = response.data.customer_card_data;
    } catch (error) {
        console.error('Error generating customer cards:', error);
        if (error.response && error.response.status === 422) {
            // Validation errors
            const errors = error.response.data.errors;
            let errorMessage = 'Kesalahan validasi:\n';
            for (const field in errors) {
                errorMessage += `${field}: ${errors[field].join(', ')}\n`;
            }
            alert(errorMessage);
        } else {
            alert('Terjadi kesalahan saat generate kartu customer');
        }
    } finally {
        isGenerating.value = false;
    }
};

const printCustomerCards = () => {
    if (generatedCustomerCards.value.length === 0) {
        alert('Generate kartu customer terlebih dahulu');
        return;
    }

    const printForm = document.createElement('form');
    printForm.method = 'POST';
    printForm.target = '_blank';
    printForm.action = route('master.barcodes.print-customer-cards');
    printForm.style.display = 'none';

    // CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    printForm.appendChild(csrfInput);

    // Customer codes
    const customerCodesInput = document.createElement('input');
    customerCodesInput.type = 'hidden';
    customerCodesInput.name = 'customer_codes';
    customerCodesInput.value = JSON.stringify(parsedCustomerCodes.value);
    printForm.appendChild(customerCodesInput);

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
    customerCodes.value = '';
    generatedBarcodes.value = [];
    generatedPriceTags.value = [];
    generatedCustomerCards.value = [];
    form.reset();
};
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Cetak Barcode & Label" />

        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-lg text-[#1f1f1f] leading-tight">
                        Cetak Barcode & Label
                    </h2>
                    <p class="text-xs text-[#555] mt-1">
                        Generate dan cetak barcode, price tag, dan kartu customer.
                    </p>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Tabs -->
            <div class="mb-6">
                <div class="border-b border-[#9c9c9c]">
                    <nav class="flex flex-wrap gap-2" aria-label="Tabs">
                        <button @click="activeTab = 'barcode'"
                            :class="activeTab === 'barcode' ? 'bg-white text-[#1f1f1f]' : 'bg-[#f7f7f7] text-[#555] hover:bg-white'"
                            class="whitespace-nowrap py-2 px-4 border border-[#9c9c9c] text-xs font-semibold">
                            Barcode
                        </button>
                        <button @click="activeTab = 'price-tag'"
                            :class="activeTab === 'price-tag' ? 'bg-white text-[#1f1f1f]' : 'bg-[#f7f7f7] text-[#555] hover:bg-white'"
                            class="whitespace-nowrap py-2 px-4 border border-[#9c9c9c] text-xs font-semibold">
                            Price Tag
                        </button>
                        <button @click="activeTab = 'customer-card'"
                            :class="activeTab === 'customer-card' ? 'bg-white text-[#1f1f1f]' : 'bg-[#f7f7f7] text-[#555] hover:bg-white'"
                            class="whitespace-nowrap py-2 px-4 border border-[#9c9c9c] text-xs font-semibold">
                            Kartu Customer
                        </button>
                    </nav>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Form Section -->
                <div class="space-y-6">
                    <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-sm p-6">
                        <h3 class="text-sm font-semibold text-[#1f1f1f] mb-4">
                            {{ activeTab === 'barcode' ? 'Konfigurasi Barcode' : activeTab === 'price-tag' ?
                                'Konfigurasi Price Tag' : 'Konfigurasi Kartu Customer' }}
                        </h3>

                        <form v-if="activeTab === 'barcode'" @submit.prevent="generateBarcodes" class="space-y-6">
                            <!-- Product Codes -->
                            <div>
                                <InputLabel for="product_codes" value="Kode Produk" class="text-[#1f1f1f]" />
                                <textarea id="product_codes" v-model="productCodes"
                                    class="mt-1 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500 min-h-32"
                                    placeholder="Masukkan kode produk, satu per baris&#10;Contoh:&#10;PROD001&#10;PROD002&#10;PROD003"
                                    required></textarea>
                                <p class="text-xs text-[#555] mt-1">
                                    Masukkan kode produk yang akan dicetak barcodenya, satu kode per baris.
                                    Total: {{ parsedProductCodes.length }} produk
                                </p>
                                <InputError :message="form.errors.product_codes" class="mt-2 text-red-600" />
                            </div>

                            <!-- Barcode Type -->
                            <div>
                                <InputLabel for="barcode_type" value="Tipe Barcode" class="text-[#1f1f1f]" />
                                <select id="barcode_type" v-model="barcodeType"
                                    class="mt-1 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500">
                                    <option value="code128">Barcode (Code 128)</option>
                                    <option value="qrcode">QR Code</option>
                                </select>
                                <InputError :message="form.errors.barcode_type" class="mt-2 text-red-600" />
                            </div>

                            <!-- Layout Configuration -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <InputLabel for="rows" value="Baris per Halaman" class="text-[#1f1f1f]" />
                                    <select id="rows" v-model="rows"
                                        class="mt-1 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500">
                                        <option v-for="r in 20" :key="r" :value="r">{{ r }}</option>
                                    </select>
                                    <InputError :message="form.errors.rows" class="mt-2 text-red-600" />
                                </div>

                                <div>
                                    <InputLabel for="columns" value="Kolom per Baris" class="text-[#1f1f1f]" />
                                    <select id="columns" v-model="columns"
                                        class="mt-1 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500">
                                        <option v-for="c in 10" :key="c" :value="c">{{ c }}</option>
                                    </select>
                                    <InputError :message="form.errors.columns" class="mt-2 text-red-600" />
                                </div>
                            </div>

                            <!-- Paper Size -->
                            <div>
                                <InputLabel for="paper_size" value="Ukuran Kertas" class="text-[#1f1f1f]" />
                                <select id="paper_size" v-model="paperSize"
                                    class="mt-1 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500">
                                    <option value="A4">A4</option>
                                </select>
                                <InputError :message="form.errors.paper_size" class="mt-2 text-red-600" />
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-3">
                                <button type="submit" :disabled="isGenerating || parsedProductCodes.length === 0"
                                    class="flex-1 inline-flex items-center justify-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] font-semibold py-3 px-6 rounded text-xs hover:bg-white transition-colors disabled:opacity-50">
                                    <svg v-if="isGenerating" class="animate-spin -ml-1 mr-3 h-4 w-4 text-[#1f1f1f]"
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
                                    class="inline-flex items-center justify-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] font-semibold py-3 px-6 rounded text-xs hover:bg-white transition-colors">
                                    Clear
                                </button>
                            </div>
                        </form>

                        <form v-else-if="activeTab === 'price-tag'" @submit.prevent="generatePriceTags"
                            class="space-y-6">
                            <!-- Product Codes -->
                            <div>
                                <InputLabel for="product_codes_price" value="Kode Produk" class="text-[#1f1f1f]" />
                                <textarea id="product_codes_price" v-model="productCodes"
                                    class="mt-1 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500 min-h-32"
                                    placeholder="Masukkan kode produk, satu per baris&#10;Contoh:&#10;PROD001&#10;PROD002&#10;PROD003"
                                    required></textarea>
                                <p class="text-xs text-[#555] mt-1">
                                    Masukkan kode produk yang akan dicetak price tagnya, satu kode per baris.
                                    Total: {{ parsedProductCodes.length }} produk
                                </p>
                                <InputError :message="form.errors.product_codes" class="mt-2 text-red-600" />
                            </div>

                            <!-- Layout Configuration -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <InputLabel for="rows_price" value="Baris per Halaman" class="text-[#1f1f1f]" />
                                    <select id="rows_price" v-model="rows"
                                        class="mt-1 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500">
                                        <option v-for="r in 20" :key="r" :value="r">{{ r }}</option>
                                    </select>
                                    <InputError :message="form.errors.rows" class="mt-2 text-red-600" />
                                </div>

                                <div>
                                    <InputLabel for="columns_price" value="Kolom per Baris" class="text-[#1f1f1f]" />
                                    <select id="columns_price" v-model="columns"
                                        class="mt-1 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500">
                                        <option v-for="c in 10" :key="c" :value="c">{{ c }}</option>
                                    </select>
                                    <InputError :message="form.errors.columns" class="mt-2 text-red-600" />
                                </div>
                            </div>

                            <!-- Paper Size -->
                            <div>
                                <InputLabel for="paper_size_price" value="Ukuran Kertas" class="text-[#1f1f1f]" />
                                <select id="paper_size_price" v-model="paperSize"
                                    class="mt-1 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500">
                                    <option value="A4">A4</option>
                                </select>
                                <InputError :message="form.errors.paper_size" class="mt-2 text-red-600" />
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-3">
                                <button type="submit" :disabled="isGenerating || parsedProductCodes.length === 0"
                                    class="flex-1 inline-flex items-center justify-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] font-semibold py-3 px-6 rounded text-xs hover:bg-white transition-colors disabled:opacity-50">
                                    <svg v-if="isGenerating" class="animate-spin -ml-1 mr-3 h-4 w-4 text-[#1f1f1f]"
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
                                    class="inline-flex items-center justify-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] font-semibold py-3 px-6 rounded text-xs hover:bg-white transition-colors">
                                    Clear
                                </button>
                            </div>
                        </form>

                        <form v-else @submit.prevent="generateCustomerCards" class="space-y-6">
                            <!-- Customer Codes -->
                            <div>
                                <InputLabel for="customer_codes" value="Kode Customer" class="text-[#1f1f1f]" />
                                <textarea id="customer_codes" v-model="customerCodes"
                                    class="mt-1 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500 min-h-32"
                                    placeholder="Masukkan kode customer, satu per baris&#10;Contoh:&#10;CUST001&#10;CUST002&#10;CUST003"
                                    required></textarea>
                                <p class="text-xs text-[#555] mt-1">
                                    Masukkan kode customer yang akan dicetak kartu customernya, satu kode per baris.
                                    Total: {{ parsedCustomerCodes.length }} customer
                                </p>
                                <InputError :message="form.errors.customer_codes" class="mt-2 text-red-600" />
                            </div>

                            <!-- Layout Configuration -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <InputLabel for="rows_customer" value="Baris per Halaman" class="text-[#1f1f1f]" />
                                    <select id="rows_customer" v-model="rows"
                                        class="mt-1 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500">
                                        <option v-for="r in 20" :key="r" :value="r">{{ r }}</option>
                                    </select>
                                    <InputError :message="form.errors.rows" class="mt-2 text-red-600" />
                                </div>

                                <div>
                                    <InputLabel for="columns_customer" value="Kolom per Baris" class="text-[#1f1f1f]" />
                                    <select id="columns_customer" v-model="columns"
                                        class="mt-1 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500">
                                        <option v-for="c in 10" :key="c" :value="c">{{ c }}</option>
                                    </select>
                                    <InputError :message="form.errors.columns" class="mt-2 text-red-600" />
                                </div>
                            </div>

                            <!-- Paper Size -->
                            <div>
                                <InputLabel for="paper_size_customer" value="Ukuran Kertas" class="text-[#1f1f1f]" />
                                <select id="paper_size_customer" v-model="paperSize"
                                    class="mt-1 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500">
                                    <option value="A4">A4</option>
                                </select>
                                <InputError :message="form.errors.paper_size" class="mt-2 text-red-600" />
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-3">
                                <button type="submit" :disabled="isGenerating || parsedCustomerCodes.length === 0"
                                    class="flex-1 inline-flex items-center justify-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] font-semibold py-3 px-6 rounded text-xs hover:bg-white transition-colors disabled:opacity-50">
                                    <svg v-if="isGenerating" class="animate-spin -ml-1 mr-3 h-4 w-4 text-[#1f1f1f]"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    {{ isGenerating ? 'Generating...' : 'Generate Kartu Customer' }}
                                </button>

                                <button type="button" @click="clearForm"
                                    class="inline-flex items-center justify-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] font-semibold py-3 px-6 rounded text-xs hover:bg-white transition-colors">
                                    Clear
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Preview Section -->
                <div class="space-y-6">
                    <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-sm p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-semibold text-[#1f1f1f]">
                                {{ activeTab === 'barcode' ? 'Preview Barcode' : activeTab === 'price-tag' ? 'Preview Price Tag' : 'Preview Kartu Customer' }}
                            </h3>
                            <button v-if="activeTab === 'barcode' && generatedBarcodes.length > 0"
                                @click="printBarcodes"
                                class="inline-flex items-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] font-semibold py-2 px-4 rounded text-xs hover:bg-white transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Print
                            </button>
                            <button v-else-if="activeTab === 'price-tag' && generatedPriceTags.length > 0"
                                @click="printPriceTags"
                                class="inline-flex items-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] font-semibold py-2 px-4 rounded text-xs hover:bg-white transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Print
                            </button>
                            <button v-else-if="activeTab === 'customer-card' && generatedCustomerCards.length > 0"
                                @click="printCustomerCards"
                                class="inline-flex items-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] font-semibold py-2 px-4 rounded text-xs hover:bg-white transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Print
                            </button>
                        </div>

                        <div v-if="activeTab === 'barcode'">
                            <div v-if="generatedBarcodes.length === 0" class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-[#555] mb-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <h3 class="text-sm font-semibold text-[#555] mb-2">Belum ada barcode</h3>
                                <p class="text-[#555]">Generate barcode terlebih dahulu untuk melihat preview.</p>
                            </div>

                            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div v-for="barcode in generatedBarcodes" :key="barcode.product_code"
                                    class="bg-white border border-[#9c9c9c] rounded p-4 text-center">
                                    <div class="mb-2">
                                        <img v-if="barcode.barcode_image" :src="barcode.barcode_image"
                                            :alt="`Barcode ${barcode.product_code}`"
                                            class="mx-auto max-w-full h-16 object-contain" />
                                    </div>
                                    <div class="text-xs text-[#1f1f1f] font-mono">{{ barcode.product_code }}</div>
                                    <div class="text-xs text-[#555] truncate">{{ barcode.name }}</div>
                                </div>
                            </div>

                            <div v-if="generatedBarcodes.length > 0" class="mt-4 text-center">
                                <p class="text-xs text-[#555]">
                                    Total: {{ generatedBarcodes.length }} barcode - Layout: {{ rows }} baris x {{
                                        columns }}
                                    kolom
                                </p>
                            </div>
                        </div>

                        <div v-else-if="activeTab === 'price-tag'">
                            <div v-if="generatedPriceTags.length === 0" class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-[#555] mb-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                <h3 class="text-sm font-semibold text-[#555] mb-2">Belum ada price tag</h3>
                                <p class="text-[#555]">Generate price tag terlebih dahulu untuk melihat preview.</p>
                            </div>

                            <div v-else class="grid grid-cols-1 gap-4">
                                <div v-for="priceTag in generatedPriceTags" :key="priceTag.product_code"
                                    class="bg-white border border-[#9c9c9c] rounded p-4 shadow-sm">

                                    <!-- Layout Landscape: Informasi kiri, Harga kanan -->
                                    <div class="flex items-center justify-between gap-4">

                                        <!-- Bagian Kiri: Info Produk -->
                                        <div class="flex-1 text-left">
                                            <div class="text-sm text-[#1f1f1f] font-mono mb-1 font-semibold">{{
                                                priceTag.product_code }}</div>
                                            <div class="text-sm text-[#555] mb-2 leading-tight">{{ priceTag.name }}
                                            </div>

                                            <!-- Info Promo di bagian kiri bawah -->
                                            <div v-if="priceTag.has_promotion"
                                                class="text-xs text-red-600 font-semibold mb-1 bg-[#fff0f0] px-2 py-1 border border-[#d9a0a0] inline-block">
                                                PROMO {{ priceTag.promotion_name }}
                                            </div>
                                            <div v-if="priceTag.has_promotion" class="text-xs text-red-600">
                                                Sampai: {{ priceTag.promotion_end_date }}
                                            </div>
                                        </div>

                                        <!-- Bagian Kanan: Harga -->
                                        <div class="flex-shrink-0 text-right">
                                            <!-- Harga Coret (jika ada promo) -->
                                            <div v-if="priceTag.has_promotion"
                                                class="text-lg line-through text-[#555] mb-1"
                                                style="font-family: 'Tahoma', sans-serif;">
                                                {{ formatCurrency(priceTag.original_price) }}
                                            </div>

                                            <!-- Harga Utama - Layout Landscape -->
                                            <div class="text-3xl font-black text-black leading-none bg-[#fff4c2] px-3 py-2 rounded border-2 border-[#d9b76c] shadow-inner min-w-max"
                                                style="font-family: 'Tahoma', sans-serif; font-weight: 900; text-shadow: 1px 1px 0px rgba(0,0,0,0.1);">
                                                {{ formatCurrency(priceTag.final_price) }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Footer -->
                                    <div class="text-xs text-[#555] border-t border-[#d0d0d0] pt-2 mt-2">{{ priceTag.printed_at }}
                                    </div>
                                </div>
                            </div>

                            <div v-if="generatedPriceTags.length > 0" class="mt-4 text-center">
                                <p class="text-xs text-[#555]">
                                    Total: {{ generatedPriceTags.length }} price tag - Layout: {{ rows }} baris x {{
                                        columns }}
                                    kolom
                                </p>
                            </div>
                        </div>

                        <div v-else-if="activeTab === 'customer-card'">
                            <div v-if="generatedCustomerCards.length === 0" class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-[#555] mb-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                </svg>
                                <h3 class="text-sm font-semibold text-[#555] mb-2">Belum ada kartu customer</h3>
                                <p class="text-[#555]">Generate kartu customer terlebih dahulu untuk melihat preview.
                                </p>
                            </div>

                            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div v-for="card in generatedCustomerCards" :key="card.customer_code"
                                    class="bg-white border border-[#9c9c9c] rounded p-4 text-center shadow-sm">
                                    <div class="text-center">
                                        <!-- Photo -->
                                        <div v-if="card.photo_url" class="mb-2">
                                            <img :src="`/storage/${card.photo_url}`" :alt="`Photo ${card.name}`"
                                                class="w-10 h-10 rounded-full mx-auto object-cover border border-[#9c9c9c]" />
                                        </div>

                                        <!-- Customer Code -->
                                        <div class="font-bold text-sm text-[#1f1f1f] mb-1">{{ card.customer_code }}</div>

                                        <!-- Name -->
                                        <div class="font-semibold text-base text-black mb-2">{{ card.name }}</div>

                                        <!-- Customer Type -->
                                        <div class="text-xs text-[#555] mb-1">
                                            <strong>Jenis:</strong> {{ card.customer_type }}
                                        </div>

                                        <!-- Status -->
                                        <div class="text-xs mb-1"
                                            :class="card.status === 'active' ? 'text-green-600' : 'text-red-600'">
                                            <strong>Status:</strong> {{ card.status.charAt(0).toUpperCase() +
                                                card.status.slice(1) }}
                                        </div>

                                        <!-- Joined Date -->
                                        <div class="text-xs text-[#555] mb-2">
                                            <strong>Bergabung:</strong> {{ card.joined_at }}
                                        </div>

                                        <!-- QR Code -->
                                        <div v-if="card.qr_code_image" class="mt-2">
                                            <img :src="card.qr_code_image" :alt="`QR Code ${card.customer_code}`"
                                                class="w-16 h-16 mx-auto object-contain" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="generatedCustomerCards.length > 0" class="mt-4 text-center">
                                <p class="text-xs text-[#555]">
                                    Total: {{ generatedCustomerCards.length }} kartu customer - Layout: {{ rows }} baris
                                    x {{
                                        columns }} kolom
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
