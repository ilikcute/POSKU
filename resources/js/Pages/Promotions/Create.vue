<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    products: Array,
    customerTypes: Array,
});

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

const showAdvancedOptions = ref(false);
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

const isTieredPricing = computed(() => form.promotion_type === 'tiered_pricing');
const isBundling = computed(() => form.promotion_type === 'bundling');
const isBuyGet = computed(() => form.promotion_type === 'buy_get');
const isBuyGetOrBundling = computed(() => isBuyGet.value || isBundling.value);
const requiresDiscountFields = computed(() => !isTieredPricing.value && !isBuyGetOrBundling.value);

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

const addBundle = () => {
    const newBundle = {
        buy_product_id: '',
        buy_quantity: 1,
        get_product_id: '',
        get_quantity: 1,
        get_price: isBuyGet.value ? 0 : '',
    };
    form.bundles.push(newBundle);
};

watch(() => form.promotion_type, (newType) => {
    if (newType === 'buy_get') {
        form.bundles.forEach(bundle => {
            bundle.get_price = 0;
        });
    }
});

const removeBundle = (index) => {
    form.bundles.splice(index, 1);
};

const submit = () => {
    form.post(route('promotions.store'), {
        onSuccess: () => {
            // Redirect to index or show success message
        },
        onError: (errors) => {
            console.error('Form errors:', errors);
        },
    });
};

onMounted(() => {
    // Load products and customer types if not provided
    if (!products.value.length) {
        // You might want to fetch products here
    }
    if (!customerTypes.value.length) {
        // You might want to fetch customer types here
    }
});
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Buat Promosi" />

        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-lg text-[#1f1f1f] leading-tight">Buat Promosi Baru</h2>
                    <p class="text-xs text-[#555] mt-1">Buat promosi baru dengan berbagai jenis dan pengaturan.</p>
                </div>
                <div>
                    <button @click="$inertia.visit(route('promotions.index'))"
                        class="inline-flex items-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] font-semibold py-2 px-4 rounded text-xs hover:bg-white transition-colors">
                        Kembali
                    </button>
                </div>
            </div>
        </template>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
            <form @submit.prevent="submit"
                class="bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-sm p-6">
                <div class="space-y-6">
                    <!-- Basic Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-medium text-[#1f1f1f] mb-2">Nama Promosi</label>
                            <input v-model="form.name" type="text" required
                                class="w-full bg-white border border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500 px-4 py-3">
                            <div v-if="form.errors.name" class="text-red-600 text-xs mt-1">{{ form.errors.name }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-[#1f1f1f] mb-2">Jenis Promosi</label>
                            <select v-model="form.promotion_type" required
                                class="w-full bg-white border border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500 px-4 py-3">
                                <option v-for="type in promotionTypes" :key="type.value" :value="type.value">
                                    {{ type.label }}
                                </option>
                            </select>
                            <div v-if="form.errors.promotion_type" class="text-red-600 text-xs mt-1">{{
                                form.errors.promotion_type }}</div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[#1f1f1f] mb-2">Deskripsi</label>
                        <textarea v-model="form.description" rows="3"
                            class="w-full bg-white border border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500 px-4 py-3"></textarea>
                        <div v-if="form.errors.description" class="text-red-600 text-xs mt-1">{{ form.errors.description
                        }}
                        </div>
                    </div>

                    <!-- Discount Fields (for non-tiered/bundling promotions) -->
                    <div v-if="requiresDiscountFields" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-xs font-medium text-[#1f1f1f] mb-2">Tipe Diskon</label>
                            <select v-model="form.discount_type" required
                                class="w-full bg-white border border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500 px-4 py-3">
                                <option v-for="type in discountTypes" :key="type.value" :value="type.value">
                                    {{ type.label }}
                                </option>
                            </select>
                            <div v-if="form.errors.discount_type" class="text-red-600 text-xs mt-1">{{
                                form.errors.discount_type
                            }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-[#1f1f1f] mb-2">Nilai Diskon</label>
                            <input v-model.number="form.discount_value" type="number" step="0.01" min="0" required
                                class="w-full bg-white border border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500 px-4 py-3">
                            <div v-if="form.errors.discount_value" class="text-red-600 text-xs mt-1">{{
                                form.errors.discount_value }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-[#1f1f1f] mb-2">Maksimal Diskon (Rp)</label>
                            <input v-model.number="form.max_discount_amount" type="number" step="0.01" min="0"
                                class="w-full bg-white border border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500 px-4 py-3">
                            <div v-if="form.errors.max_discount_amount" class="text-red-600 text-xs mt-1">{{
                                form.errors.max_discount_amount }}</div>
                        </div>
                    </div>

                    <!-- Tiered Pricing -->
                    <div v-if="isTieredPricing" class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-[#1f1f1f]">Tingkatan Harga</h3>
                            <button type="button" @click="addTier"
                                class="bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] px-4 py-2 rounded text-xs hover:bg-white transition-colors">
                                Tambah Tingkatan
                            </button>
                        </div>

                        <div v-for="(tier, index) in form.tiers" :key="index"
                            class="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-[#f7f7f7] border border-[#9c9c9c] rounded">
                            <div>
                                <label class="block text-xs font-medium text-[#1f1f1f] mb-2">Min Quantity</label>
                                <input v-model.number="tier.min_quantity" type="number" min="1" required
                                    class="w-full bg-white border border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-[#1f1f1f] mb-2">Tipe Diskon</label>
                                <select v-model="tier.discount_type" required
                                    class="w-full bg-white border border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                                    <option v-for="type in discountTypes" :key="type.value" :value="type.value">
                                        {{ type.label }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-[#1f1f1f] mb-2">Nilai Diskon</label>
                                <input v-model.number="tier.discount_value" type="number" step="0.01" min="0" required
                                    class="w-full bg-white border border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                            </div>
                            <div class="flex items-end">
                                <button type="button" @click="removeTier(index)"
                                    class="bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] px-3 py-2 rounded text-xs hover:bg-white transition-colors">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Buy Get or Bundling -->
                    <div v-if="isBuyGetOrBundling" class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-[#1f1f1f]">{{ isBuyGet ? 'Aturan Beli X Gratis Y' :
                                'Bundling Rules'
                            }}</h3>
                            <button type="button" @click="addBundle"
                                class="bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] px-4 py-2 rounded text-xs hover:bg-white transition-colors">
                                {{ isBuyGet ? 'Tambah Aturan' : 'Tambah Bundle' }}
                            </button>
                        </div>

                        <div v-for="(bundle, index) in form.bundles" :key="index"
                            class="grid grid-cols-1 md:grid-cols-6 gap-4 p-4 bg-[#f7f7f7] border border-[#9c9c9c] rounded">
                            <div>
                                <label class="block text-xs font-medium text-[#1f1f1f] mb-2">Beli Produk</label>
                                <select v-model="bundle.buy_product_id" required
                                    class="w-full bg-white border border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                                    <option value="">Pilih Produk</option>
                                    <option v-for="product in products" :key="product.id" :value="product.id">
                                        {{ product.name }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-[#1f1f1f] mb-2">Qty Beli</label>
                                <input v-model.number="bundle.buy_quantity" type="number" min="1" required
                                    class="w-full bg-white border border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-[#1f1f1f] mb-2">
                                    {{ isBuyGet ? 'Produk Hadiah' : 'Gratis Produk' }}
                                </label>
                                <select v-model="bundle.get_product_id" required
                                    class="w-full bg-white border border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                                    <option value="">Pilih Produk</option>
                                    <option v-for="product in products" :key="product.id" :value="product.id"
                                        v-if="product && product.id && product.name">
                                        {{ product.name }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-[#1f1f1f] mb-2">
                                    {{ isBuyGet ? 'Qty Hadiah' : 'Qty Gratis' }}
                                </label>
                                <input v-model.number="bundle.get_quantity" type="number" min="1" required
                                    :value="bundle?.get_quantity || 1"
                                    class="w-full bg-white border border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                            </div>

                            <div v-if="!isBuyGet">
                                <label class="block text-xs font-medium text-[#1f1f1f] mb-2">Harga Gratis</label>
                                <input v-model.number="bundle.get_price" type="number" step="0.01" min="0"
                                    :value="bundle?.get_price || 0"
                                    class="w-full bg-white border border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                            </div>
                            <div class="flex items-end">
                                <button type="button" @click="removeBundle(index)"
                                    class="bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] px-3 py-2 rounded text-xs hover:bg-white transition-colors">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Applicable Products -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-[#1f1f1f]">Produk yang Berlaku</h3>
                            <span class="text-xs text-[#555]">Kosongkan untuk semua produk</span>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-[#1f1f1f] mb-2">Pilih Produk</label>
                            <select multiple v-model="form.product_ids"
                                class="w-full bg-white border border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500 px-4 py-3 min-h-[120px]">
                                <option v-for="product in products" :key="product.id" :value="product.id">
                                    {{ product.name }}
                                </option>
                            </select>
                            <div class="text-xs text-[#555] mt-1">Tekan Ctrl/Cmd untuk memilih multiple produk</div>
                            <div v-if="form.errors.product_ids" class="text-red-600 text-xs mt-1">{{
                                form.errors.product_ids }}
                            </div>
                        </div>
                    </div>

                    <!-- Applicable Customer Types -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-[#1f1f1f]">Tipe Customer yang Berlaku</h3>
                            <span class="text-xs text-[#555]">Kosongkan untuk semua tipe customer</span>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-[#1f1f1f] mb-2">Pilih Tipe Customer</label>
                            <select multiple v-model="form.customer_type_ids"
                                class="w-full bg-white border border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500 px-4 py-3 min-h-[120px]">
                                <option v-for="customerType in customerTypes" :key="customerType.id"
                                    :value="customerType.id">
                                    {{ customerType.name }}
                                </option>
                            </select>
                            <div class="text-xs text-[#555] mt-1">Tekan Ctrl/Cmd untuk memilih multiple tipe customer
                            </div>
                            <div v-if="form.errors.customer_type_ids" class="text-red-600 text-xs mt-1">{{
                                form.errors.customer_type_ids }}</div>
                        </div>
                    </div>

                    <!-- Date Range -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-medium text-[#1f1f1f] mb-2">Tanggal Mulai</label>
                            <input v-model="form.start_date" type="date" required
                                class="w-full bg-white border border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500 px-4 py-3">
                            <div v-if="form.errors.start_date" class="text-red-600 text-xs mt-1">{{
                                form.errors.start_date }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-[#1f1f1f] mb-2">Tanggal Berakhir</label>
                            <input v-model="form.end_date" type="date" required
                                class="w-full bg-white border border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500 px-4 py-3">
                            <div v-if="form.errors.end_date" class="text-red-600 text-xs mt-1">{{ form.errors.end_date
                            }}</div>
                        </div>
                    </div>

                    <!-- Advanced Options -->
                    <div>
                        <button type="button" @click="showAdvancedOptions = !showAdvancedOptions"
                            class="text-[#1f1f1f] hover:text-black text-xs font-medium">
                            {{ showAdvancedOptions ? 'Sembunyikan' : 'Tampilkan' }} Opsi Lanjutan
                        </button>
                    </div>

                    <div v-if="showAdvancedOptions" class="space-y-6 p-4 bg-[#f7f7f7] border border-[#9c9c9c] rounded">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-xs font-medium text-[#1f1f1f] mb-2">Minimal Pembelian
                                    (Rp)</label>
                                <input v-model.number="form.min_purchase_amount" type="number" step="0.01" min="0"
                                    class="w-full bg-white border border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500 px-4 py-3">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-[#1f1f1f] mb-2">Minimal Quantity</label>
                                <input v-model.number="form.min_quantity" type="number" min="1"
                                    class="w-full bg-white border border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500 px-4 py-3">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-[#1f1f1f] mb-2">Batas Penggunaan</label>
                                <input v-model.number="form.usage_limit" type="number" min="1"
                                    class="w-full bg-white border border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500 px-4 py-3">
                            </div>
                        </div>

                        <div class="flex items-center space-x-6">
                            <label class="flex items-center">
                                <input v-model="form.is_active" type="checkbox"
                                    class="rounded border-[#9c9c9c] text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-xs text-[#1f1f1f]">Aktif</span>
                            </label>

                            <label class="flex items-center">
                                <input v-model="form.is_stackable" type="checkbox"
                                    class="rounded border-[#9c9c9c] text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-xs text-[#1f1f1f]">Dapat Ditumpuk</span>
                            </label>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex justify-end space-x-4">
                        <button type="button" @click="$inertia.visit(route('promotions.index'))"
                            class="bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] font-semibold py-3 px-6 rounded text-xs hover:bg-white transition-colors">
                            Batal
                        </button>
                        <button type="submit" :disabled="form.processing"
                            class="bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] font-semibold py-3 px-6 rounded text-xs hover:bg-white transition-colors disabled:opacity-50">
                            {{ form.processing ? 'Menyimpan...' : 'Buat Promosi' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
select option {
    background-color: #ffffff;
    color: #1f1f1f;
}
</style>
