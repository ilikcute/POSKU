<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    products: Array,
    customerTypes: Array,
});

// Wizard State
const currentStep = ref(1);
const totalSteps = 4;
const stepTitles = ['Info Dasar', 'Pengaturan Diskon', 'Target & Periode', 'Review'];

const form = useForm({
    name: '',
    description: '',
    promotion_type: 'product_discount',
    discount_type: 'percentage',
    discount_value: '',
    min_purchase_amount: '',
    max_discount_amount: '',
    min_quantity: 1,
    start_date: '',
    end_date: '',
    is_stackable: false,
    is_active: true,
    usage_limit: '',
    product_ids: [],
    customer_type_ids: [],
    tiers: [],
    bundles: [],
});

const products = ref(props.products || []);
const customerTypes = ref(props.customerTypes || []);

const promotionTypes = [
    { value: 'product_discount', label: 'Diskon Produk' },
    { value: 'buy_get', label: 'Beli X Gratis Y' },
    { value: 'cashback', label: 'Cashback' },
    { value: 'shipping_discount', label: 'Diskon Ongkir' },
    { value: 'tiered_pricing', label: 'Harga Bertingkat' },
    { value: 'bundling', label: 'Bundling' },
];

const discountTypes = [
    { value: 'percentage', label: 'Persentase (%)' },
    { value: 'fixed_amount', label: 'Jumlah Tetap (Rp)' },
];

// Computed untuk jenis promosi
const isTieredPricing = computed(() => form.promotion_type === 'tiered_pricing');
const isBundling = computed(() => form.promotion_type === 'bundling');
const isBuyGet = computed(() => form.promotion_type === 'buy_get');
const isBuyGetOrBundling = computed(() => isBuyGet.value || isBundling.value);
const requiresDiscountFields = computed(() => !isTieredPricing.value && !isBuyGetOrBundling.value);

// Wizard Navigation
const nextStep = () => {
    if (currentStep.value < totalSteps) {
        currentStep.value++;
    }
};

const prevStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
};

const goToStep = (step) => {
    if (step >= 1 && step <= totalSteps) {
        currentStep.value = step;
    }
};

// Tier Functions
const addTier = () => {
    form.tiers.push({
        min_quantity: 1,
        discount_type: 'percentage',
        discount_value: 0,
    });
};

const removeTier = (index) => {
    form.tiers.splice(index, 1);
};

// Bundle Functions
const addBundle = () => {
    form.bundles.push({
        buy_product_id: '',
        buy_quantity: 1,
        get_product_id: '',
        get_quantity: 1,
        get_price: isBuyGet.value ? 0 : '',
    });
};

const removeBundle = (index) => {
    form.bundles.splice(index, 1);
};

// Watch untuk reset bundle price saat jenis promosi berubah
watch(() => form.promotion_type, (newType) => {
    if (newType === 'buy_get') {
        form.bundles.forEach(bundle => {
            bundle.get_price = 0;
        });
    }
});

// Helper untuk mendapatkan nama produk
const getProductName = (productId) => {
    const product = products.value.find(p => p.id === productId);
    return product ? product.name : '-';
};

// Helper untuk mendapatkan nama customer type
const getCustomerTypeName = (typeId) => {
    const type = customerTypes.value.find(t => t.id === typeId);
    return type ? type.name : '-';
};

// Helper untuk label jenis promosi
const getPromotionTypeLabel = (value) => {
    const type = promotionTypes.find(t => t.value === value);
    return type ? type.label : value;
};

// Helper untuk label tipe diskon
const getDiscountTypeLabel = (value) => {
    const type = discountTypes.find(t => t.value === value);
    return type ? type.label : value;
};

// Format currency
const formatCurrency = (value) => {
    if (!value) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(Number(value));
};

// Submit
const submit = () => {
    form.post(route('promotions.store'), {
        onSuccess: () => {
            // Redirect handled by server
        },
        onError: (errors) => {
            console.error('Form errors:', errors);
            // Go to step with first error
            if (errors.name || errors.description || errors.promotion_type) {
                currentStep.value = 1;
            } else if (errors.discount_type || errors.discount_value || errors.tiers || errors.bundles) {
                currentStep.value = 2;
            } else if (errors.product_ids || errors.customer_type_ids || errors.start_date || errors.end_date) {
                currentStep.value = 3;
            }
        },
    });
};
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Buat Promosi" />

        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-lg text-[#1f1f1f] leading-tight">Buat Promosi Baru</h2>
                    <p class="text-xs text-[#555] mt-1">
                        Langkah {{ currentStep }} dari {{ totalSteps }}: {{ stepTitles[currentStep - 1] }}
                    </p>
                </div>
                <button @click="$inertia.visit(route('promotions.index'))"
                    class="inline-flex items-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] font-semibold py-2 px-4 rounded text-xs hover:bg-white transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali
                </button>
            </div>
        </template>

        <div class="py-4">
            <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-sm">

                <!-- Step Indicator -->
                <div
                    class="flex items-center justify-center gap-2 sm:gap-4 py-4 px-4 border-b border-[#e9e9e9] bg-white">
                    <template v-for="step in totalSteps" :key="step">
                        <button @click="goToStep(step)" :class="[
                            'flex items-center gap-2 px-3 py-2 text-xs font-semibold rounded transition-colors',
                            currentStep === step
                                ? 'bg-[#1f1f1f] text-white'
                                : currentStep > step
                                    ? 'bg-emerald-100 text-emerald-700 border border-emerald-300'
                                    : 'bg-[#f7f7f7] text-[#555] border border-[#9c9c9c] hover:bg-white'
                        ]">
                            <span class="w-5 h-5 flex items-center justify-center rounded-full text-[10px] font-bold"
                                :class="currentStep === step ? 'bg-white text-[#1f1f1f]' : currentStep > step ? 'bg-emerald-500 text-white' : 'bg-[#9c9c9c] text-white'">
                                <svg v-if="currentStep > step" class="w-3 h-3" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                <span v-else>{{ step }}</span>
                            </span>
                            <span class="hidden sm:inline">{{ stepTitles[step - 1] }}</span>
                        </button>
                        <div v-if="step < totalSteps" class="w-4 sm:w-8 h-px"
                            :class="currentStep > step ? 'bg-emerald-400' : 'bg-[#9c9c9c]'"></div>
                    </template>
                </div>

                <!-- Form Content -->
                <form @submit.prevent="submit" class="p-6">

                    <!-- STEP 1: Informasi Dasar -->
                    <div v-show="currentStep === 1" class="space-y-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-semibold text-[#1f1f1f] mb-1">Nama Promosi *</label>
                                <input v-model="form.name" type="text"
                                    class="w-full border border-[#9c9c9c] rounded px-3 py-2 text-sm focus:ring-1 focus:ring-[#1f1f1f] focus:border-[#1f1f1f]"
                                    placeholder="Contoh: Promo Akhir Tahun 50%" />
                                <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-[#1f1f1f] mb-1">Jenis Promosi *</label>
                                <select v-model="form.promotion_type"
                                    class="w-full border border-[#9c9c9c] rounded px-3 py-2 text-sm focus:ring-1 focus:ring-[#1f1f1f] focus:border-[#1f1f1f]">
                                    <option v-for="type in promotionTypes" :key="type.value" :value="type.value">
                                        {{ type.label }}
                                    </option>
                                </select>
                                <p v-if="form.errors.promotion_type" class="text-red-500 text-xs mt-1">{{
                                    form.errors.promotion_type }}</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#1f1f1f] mb-1">Deskripsi</label>
                            <textarea v-model="form.description" rows="3"
                                class="w-full border border-[#9c9c9c] rounded px-3 py-2 text-sm focus:ring-1 focus:ring-[#1f1f1f] focus:border-[#1f1f1f]"
                                placeholder="Deskripsi singkat tentang promosi ini..."></textarea>
                            <p v-if="form.errors.description" class="text-red-500 text-xs mt-1">{{
                                form.errors.description }}
                            </p>
                        </div>

                        <!-- Info Box -->
                        <div class="bg-blue-50 border border-blue-200 rounded p-4">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div class="text-xs text-blue-700">
                                    <p class="font-semibold mb-1">Jenis Promosi yang Dipilih: {{
                                        getPromotionTypeLabel(form.promotion_type) }}</p>
                                    <p v-if="requiresDiscountFields">Anda akan mengatur nilai diskon di langkah
                                        berikutnya.</p>
                                    <p v-else-if="isTieredPricing">Anda akan mengatur tingkatan harga di langkah
                                        berikutnya.</p>
                                    <p v-else-if="isBuyGetOrBundling">Anda akan mengatur aturan beli/gratis di langkah
                                        berikutnya.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 2: Pengaturan Diskon -->
                    <div v-show="currentStep === 2" class="space-y-6">

                        <!-- Standard Discount Fields -->
                        <div v-if="requiresDiscountFields" class="space-y-6">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-[#1f1f1f] mb-1">Tipe Diskon</label>
                                    <select v-model="form.discount_type"
                                        class="w-full border border-[#9c9c9c] rounded px-3 py-2 text-sm">
                                        <option v-for="type in discountTypes" :key="type.value" :value="type.value">
                                            {{ type.label }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-[#1f1f1f] mb-1">Nilai Diskon
                                        *</label>
                                    <input v-model="form.discount_value" type="number"
                                        class="w-full border border-[#9c9c9c] rounded px-3 py-2 text-sm"
                                        :placeholder="form.discount_type === 'percentage' ? 'Contoh: 10' : 'Contoh: 50000'" />
                                    <p v-if="form.errors.discount_value" class="text-red-500 text-xs mt-1">{{
                                        form.errors.discount_value }}</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-[#1f1f1f] mb-1">Maksimal Diskon
                                        (Rp)</label>
                                    <input v-model="form.max_discount_amount" type="number"
                                        class="w-full border border-[#9c9c9c] rounded px-3 py-2 text-sm"
                                        placeholder="Contoh: 100000" />
                                </div>
                            </div>

                            <!-- Advanced Options inline -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4 border-t border-[#e9e9e9]">
                                <div>
                                    <label class="block text-xs font-semibold text-[#1f1f1f] mb-1">Minimal Pembelian
                                        (Rp)</label>
                                    <input v-model="form.min_purchase_amount" type="number"
                                        class="w-full border border-[#9c9c9c] rounded px-3 py-2 text-sm"
                                        placeholder="0" />
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-[#1f1f1f] mb-1">Minimal
                                        Quantity</label>
                                    <input v-model="form.min_quantity" type="number"
                                        class="w-full border border-[#9c9c9c] rounded px-3 py-2 text-sm"
                                        placeholder="1" />
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-[#1f1f1f] mb-1">Batas
                                        Penggunaan</label>
                                    <input v-model="form.usage_limit" type="number"
                                        class="w-full border border-[#9c9c9c] rounded px-3 py-2 text-sm"
                                        placeholder="Kosongkan untuk unlimited" />
                                </div>
                            </div>
                        </div>

                        <!-- Tiered Pricing -->
                        <div v-else-if="isTieredPricing" class="space-y-4">
                            <div class="flex items-center justify-between">
                                <h4 class="font-semibold text-sm text-[#1f1f1f]">Tingkatan Harga</h4>
                                <button type="button" @click="addTier"
                                    class="bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] px-3 py-1.5 rounded text-xs hover:bg-white transition-colors">
                                    + Tambah Tingkatan
                                </button>
                            </div>

                            <div v-if="form.tiers.length === 0"
                                class="text-center py-8 text-[#555] text-sm border border-dashed border-[#9c9c9c] rounded">
                                Belum ada tingkatan. Klik "Tambah Tingkatan" untuk memulai.
                            </div>

                            <div v-for="(tier, index) in form.tiers" :key="index"
                                class="grid grid-cols-4 gap-3 items-end p-3 bg-white border border-[#9c9c9c] rounded">
                                <div>
                                    <label class="block text-xs text-[#555] mb-1">Min Quantity</label>
                                    <input v-model="tier.min_quantity" type="number"
                                        class="w-full border border-[#9c9c9c] rounded px-2 py-1.5 text-sm" />
                                </div>
                                <div>
                                    <label class="block text-xs text-[#555] mb-1">Tipe Diskon</label>
                                    <select v-model="tier.discount_type"
                                        class="w-full border border-[#9c9c9c] rounded px-2 py-1.5 text-sm">
                                        <option v-for="type in discountTypes" :key="type.value" :value="type.value">{{
                                            type.label }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-[#555] mb-1">Nilai Diskon</label>
                                    <input v-model="tier.discount_value" type="number"
                                        class="w-full border border-[#9c9c9c] rounded px-2 py-1.5 text-sm" />
                                </div>
                                <div>
                                    <button type="button" @click="removeTier(index)"
                                        class="w-full bg-red-50 text-red-600 border border-red-200 px-2 py-1.5 rounded text-xs hover:bg-red-100">
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Buy Get / Bundling -->
                        <div v-else-if="isBuyGetOrBundling" class="space-y-4">
                            <div class="flex items-center justify-between">
                                <h4 class="font-semibold text-sm text-[#1f1f1f]">
                                    {{ isBuyGet ? 'Aturan Beli X Gratis Y' : 'Bundling Rules' }}
                                </h4>
                                <button type="button" @click="addBundle"
                                    class="bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] px-3 py-1.5 rounded text-xs hover:bg-white transition-colors">
                                    + {{ isBuyGet ? 'Tambah Aturan' : 'Tambah Bundle' }}
                                </button>
                            </div>

                            <div v-if="form.bundles.length === 0"
                                class="text-center py-8 text-[#555] text-sm border border-dashed border-[#9c9c9c] rounded">
                                Belum ada aturan. Klik tombol di atas untuk memulai.
                            </div>

                            <div v-for="(bundle, index) in form.bundles" :key="index"
                                class="grid grid-cols-2 sm:grid-cols-6 gap-3 items-end p-3 bg-white border border-[#9c9c9c] rounded">
                                <div>
                                    <label class="block text-xs text-[#555] mb-1">Beli Produk</label>
                                    <select v-model="bundle.buy_product_id"
                                        class="w-full border border-[#9c9c9c] rounded px-2 py-1.5 text-xs">
                                        <option value="">Pilih</option>
                                        <option v-for="product in products" :key="product.id" :value="product.id">{{
                                            product.name }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-[#555] mb-1">Qty Beli</label>
                                    <input v-model="bundle.buy_quantity" type="number"
                                        class="w-full border border-[#9c9c9c] rounded px-2 py-1.5 text-sm" />
                                </div>
                                <div>
                                    <label class="block text-xs text-[#555] mb-1">{{ isBuyGet ? 'Produk Hadiah' :'Gratis Produk' }}</label>
                                    <select v-model="bundle.get_product_id"
                                        class="w-full border border-[#9c9c9c] rounded px-2 py-1.5 text-xs">
                                        <option value="">Pilih</option>
                                        <option v-for="product in products" :key="product.id" :value="product.id">{{
                                            product.name }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-[#555] mb-1">Qty Gratis</label>
                                    <input v-model="bundle.get_quantity" type="number"
                                        class="w-full border border-[#9c9c9c] rounded px-2 py-1.5 text-sm" />
                                </div>
                                <div v-if="!isBuyGet">
                                    <label class="block text-xs text-[#555] mb-1">Harga</label>
                                    <input v-model="bundle.get_price" type="number"
                                        class="w-full border border-[#9c9c9c] rounded px-2 py-1.5 text-sm" />
                                </div>
                                <div>
                                    <button type="button" @click="removeBundle(index)"
                                        class="w-full bg-red-50 text-red-600 border border-red-200 px-2 py-1.5 rounded text-xs hover:bg-red-100">
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 3: Target & Periode -->
                    <div v-show="currentStep === 3" class="space-y-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Produk -->
                            <div>
                                <label class="block text-xs font-semibold text-[#1f1f1f] mb-1">Produk yang
                                    Berlaku</label>
                                <p class="text-xs text-[#555] mb-2">Kosongkan untuk semua produk</p>
                                <select v-model="form.product_ids" multiple
                                    class="w-full border border-[#9c9c9c] rounded px-3 py-2 text-sm h-32">
                                    <option v-for="product in products" :key="product.id" :value="product.id">
                                        {{ product.name }}
                                    </option>
                                </select>
                                <p class="text-xs text-[#555] mt-1">Ctrl/Cmd + klik untuk pilih multiple</p>
                            </div>

                            <!-- Customer Types -->
                            <div>
                                <label class="block text-xs font-semibold text-[#1f1f1f] mb-1">Tipe Customer yang
                                    Berlaku</label>
                                <p class="text-xs text-[#555] mb-2">Kosongkan untuk semua tipe customer</p>
                                <select v-model="form.customer_type_ids" multiple
                                    class="w-full border border-[#9c9c9c] rounded px-3 py-2 text-sm h-32">
                                    <option v-for="type in customerTypes" :key="type.id" :value="type.id">
                                        {{ type.name }}
                                    </option>
                                </select>
                                <p class="text-xs text-[#555] mt-1">Ctrl/Cmd + klik untuk pilih multiple</p>
                            </div>
                        </div>

                        <!-- Tanggal -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-[#1f1f1f] mb-1">Tanggal Mulai *</label>
                                <input v-model="form.start_date" type="date"
                                    class="w-full border border-[#9c9c9c] rounded px-3 py-2 text-sm" />
                                <p v-if="form.errors.start_date" class="text-red-500 text-xs mt-1">{{
                                    form.errors.start_date }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-[#1f1f1f] mb-1">Tanggal Berakhir
                                    *</label>
                                <input v-model="form.end_date" type="date"
                                    class="w-full border border-[#9c9c9c] rounded px-3 py-2 text-sm" />
                                <p v-if="form.errors.end_date" class="text-red-500 text-xs mt-1">{{ form.errors.end_date
                                    }}</p>
                            </div>
                        </div>

                        <!-- Status Options -->
                        <div class="flex items-center gap-6 pt-4 border-t border-[#e9e9e9]">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" v-model="form.is_active" class="rounded border-[#9c9c9c]" />
                                <span class="text-sm text-[#1f1f1f]">Aktif</span>
                            </label>

                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" v-model="form.is_stackable" class="rounded border-[#9c9c9c]" />
                                <span class="text-sm text-[#1f1f1f]">Dapat Ditumpuk dengan promosi lain</span>
                            </label>
                        </div>
                    </div>

                    <!-- STEP 4: Review -->
                    <div v-show="currentStep === 4" class="space-y-6">
                        <div class="bg-white border border-[#9c9c9c] rounded p-4">
                            <h4 class="font-bold text-sm text-[#1f1f1f] mb-4 pb-2 border-b border-[#e9e9e9]">
                                Ringkasan Promosi
                            </h4>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-[#555]">Nama:</span>
                                    <span class="font-semibold ml-2">{{ form.name || '-' }}</span>
                                </div>
                                <div>
                                    <span class="text-[#555]">Jenis:</span>
                                    <span class="font-semibold ml-2">{{ getPromotionTypeLabel(form.promotion_type)
                                        }}</span>
                                </div>
                                <div v-if="requiresDiscountFields">
                                    <span class="text-[#555]">Diskon:</span>
                                    <span class="font-semibold ml-2">
                                        {{ form.discount_value }}{{ form.discount_type === 'percentage' ? '%' : ' Rp' }}
                                    </span>
                                </div>
                                <div v-if="requiresDiscountFields && form.max_discount_amount">
                                    <span class="text-[#555]">Maks Diskon:</span>
                                    <span class="font-semibold ml-2">{{ formatCurrency(form.max_discount_amount)
                                        }}</span>
                                </div>
                                <div>
                                    <span class="text-[#555]">Periode:</span>
                                    <span class="font-semibold ml-2">{{ form.start_date || '-' }} s/d {{ form.end_date
                                        || '-'
                                        }}</span>
                                </div>
                                <div>
                                    <span class="text-[#555]">Status:</span>
                                    <span class="font-semibold ml-2"
                                        :class="form.is_active ? 'text-emerald-600' : 'text-red-600'">
                                        {{ form.is_active ? 'Aktif' : 'Tidak Aktif' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-[#555]">Dapat Ditumpuk:</span>
                                    <span class="font-semibold ml-2">{{ form.is_stackable ? 'Ya' : 'Tidak' }}</span>
                                </div>
                                <div>
                                    <span class="text-[#555]">Produk:</span>
                                    <span class="font-semibold ml-2">{{ form.product_ids.length ?
                                        form.product_ids.length + 'produk' : 'Semua produk' }}</span>
                                </div>
                            </div>

                            <!-- Tiered Summary -->
                            <div v-if="isTieredPricing && form.tiers.length"
                                class="mt-4 pt-4 border-t border-[#e9e9e9]">
                                <h5 class="font-semibold text-xs text-[#555] mb-2">Tingkatan Harga:</h5>
                                <div class="space-y-1">
                                    <div v-for="(tier, index) in form.tiers" :key="index" class="text-xs">
                                        Min {{ tier.min_quantity }} pcs → {{ tier.discount_value }}{{ tier.discount_type
                                        ===
                                        'percentage' ? '%' : ' Rp' }}
                                    </div>
                                </div>
                            </div>

                            <!-- Bundles Summary -->
                            <div v-if="isBuyGetOrBundling && form.bundles.length"
                                class="mt-4 pt-4 border-t border-[#e9e9e9]">
                                <h5 class="font-semibold text-xs text-[#555] mb-2">{{ isBuyGet ? 'Aturan Beli Gratis:' :
                                    'Bundling:' }}</h5>
                                <div class="space-y-1">
                                    <div v-for="(bundle, index) in form.bundles" :key="index" class="text-xs">
                                        Beli {{ bundle.buy_quantity }}x {{ getProductName(bundle.buy_product_id) }}
                                        → Gratis {{ bundle.get_quantity }}x {{ getProductName(bundle.get_product_id) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div v-if="form.description" class="bg-[#f7f7f7] border border-[#e9e9e9] rounded p-4">
                            <h5 class="font-semibold text-xs text-[#555] mb-2">Deskripsi:</h5>
                            <p class="text-sm text-[#1f1f1f]">{{ form.description }}</p>
                        </div>

                        <!-- Confirmation Message -->
                        <div class="bg-emerald-50 border border-emerald-200 rounded p-4">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div class="text-xs text-emerald-700">
                                    <p class="font-semibold">Siap untuk membuat promosi?</p>
                                    <p>Pastikan semua data sudah benar sebelum menyimpan.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="flex items-center justify-between mt-8 pt-4 border-t border-[#e9e9e9]">
                        <button type="button" @click="$inertia.visit(route('promotions.index'))"
                            class="inline-flex items-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] py-2 px-5 text-xs font-semibold rounded hover:bg-white transition-colors">
                            <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Batal
                        </button>

                        <div class="flex items-center gap-2">
                            <button v-if="currentStep > 1" type="button" @click="prevStep"
                                class="inline-flex items-center bg-[#f7f7f7] text-[#1f1f1f] border border-[#9c9c9c] py-2 px-5 text-xs font-semibold rounded hover:bg-white transition-colors">
                                <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                                Sebelumnya
                            </button>

                            <button v-if="currentStep < totalSteps" type="button" @click="nextStep"
                                class="inline-flex items-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] py-2 px-5 text-xs font-semibold rounded hover:bg-white transition-colors">
                                Selanjutnya
                                <svg class="w-3 h-3 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </button>

                            <button v-if="currentStep === totalSteps" type="submit" :disabled="form.processing"
                                class="inline-flex items-center bg-[#1f1f1f] text-white border border-[#1f1f1f] py-2 px-6 text-xs font-semibold rounded hover:bg-[#333] transition-colors disabled:opacity-50">
                                <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                {{ form.processing ? 'Menyimpan...' : 'Buat Promosi' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
select option {
    background-color: #ffffff;
    color: #1f1f1f;
}
</style>
