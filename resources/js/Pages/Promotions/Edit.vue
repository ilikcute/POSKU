<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    promotion: Object,
    products: Array,
    customerTypes: Array,
});

const form = useForm({
    name: props.promotion.name || '',
    description: props.promotion.description || '',
    promotion_type: props.promotion.promotion_type || 'product_discount',
    discount_type: props.promotion.discount_type || 'percentage',
    discount_value: props.promotion.discount_value || '',
    min_purchase_amount: props.promotion.min_purchase_amount || '',
    max_discount_amount: props.promotion.max_discount_amount || '',
    min_quantity: props.promotion.min_quantity || 1,
    start_date: props.promotion.start_date ? props.promotion.start_date.split('T')[0] : '',
    end_date: props.promotion.end_date ? props.promotion.end_date.split('T')[0] : '',
    is_stackable: props.promotion.is_stackable || false,
    is_active: props.promotion.is_active || true,
    usage_limit: props.promotion.usage_limit || '',
    product_ids: props.promotion.products ? props.promotion.products.map(p => p.id) : [],
    customer_type_ids: props.promotion.customer_types ? props.promotion.customer_types.map(ct => ct.id) : [],
    tiers: props.promotion.tiers || [],
    bundles: props.promotion.bundles || [],
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
const requiresDiscountFields = computed(() => !isTieredPricing.value && !isBundling.value);

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
    form.bundles.push({
        buy_product_id: '',
        buy_quantity: 1,
        get_product_id: '',
        get_quantity: 1,
        get_price: 0,
    });
};

const removeBundle = (index) => {
    form.bundles.splice(index, 1);
};

const submit = () => {
    form.put(route('promotions.update', props.promotion.id), {
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

        <Head title="Edit Promosi" />

        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-xl text-white leading-tight">Edit Promosi</h2>
                    <p class="text-sm text-gray-400 mt-1">Edit detail promosi dan pengaturannya.</p>
                </div>
                <div>
                    <button @click="$inertia.visit(route('promotions.index'))"
                        class="inline-flex items-center bg-gray-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-gray-700 transition">
                        Kembali
                    </button>
                </div>
            </div>
        </template>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
            <form @submit.prevent="submit"
                class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl shadow-xl p-6">
                <div class="space-y-6">
                    <!-- Basic Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Nama Promosi</label>
                            <input v-model="form.name" type="text" required
                                class="w-full bg-white/5 border border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500 px-4 py-3">
                            <div v-if="form.errors.name" class="text-red-400 text-sm mt-1">{{ form.errors.name }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Jenis Promosi</label>
                            <select v-model="form.promotion_type" required
                                class="w-full bg-white/5 border border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500 px-4 py-3">
                                <option v-for="type in promotionTypes" :key="type.value" :value="type.value">
                                    {{ type.label }}
                                </option>
                            </select>
                            <div v-if="form.errors.promotion_type" class="text-red-400 text-sm mt-1">{{
                                form.errors.promotion_type }}</div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Deskripsi</label>
                        <textarea v-model="form.description" rows="3"
                            class="w-full bg-white/5 border border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500 px-4 py-3"></textarea>
                        <div v-if="form.errors.description" class="text-red-400 text-sm mt-1">{{ form.errors.description
                            }}
                        </div>
                    </div>

                    <!-- Discount Fields (for non-tiered/bundling promotions) -->
                    <div v-if="requiresDiscountFields" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Tipe Diskon</label>
                            <select v-model="form.discount_type" required
                                class="w-full bg-white/5 border border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500 px-4 py-3">
                                <option v-for="type in discountTypes" :key="type.value" :value="type.value">
                                    {{ type.label }}
                                </option>
                            </select>
                            <div v-if="form.errors.discount_type" class="text-red-400 text-sm mt-1">{{
                                form.errors.discount_type
                                }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Nilai Diskon</label>
                            <input v-model.number="form.discount_value" type="number" step="0.01" min="0" required
                                class="w-full bg-white/5 border border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500 px-4 py-3">
                            <div v-if="form.errors.discount_value" class="text-red-400 text-sm mt-1">{{
                                form.errors.discount_value }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Maksimal Diskon (Rp)</label>
                            <input v-model.number="form.max_discount_amount" type="number" step="0.01" min="0"
                                class="w-full bg-white/5 border border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500 px-4 py-3">
                            <div v-if="form.errors.max_discount_amount" class="text-red-400 text-sm mt-1">{{
                                form.errors.max_discount_amount }}</div>
                        </div>
                    </div>

                    <!-- Tiered Pricing -->
                    <div v-if="isTieredPricing" class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-white">Tingkatan Harga</h3>
                            <button type="button" @click="addTier"
                                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                                Tambah Tingkatan
                            </button>
                        </div>

                        <div v-for="(tier, index) in form.tiers" :key="index"
                            class="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-white/5 rounded-lg">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Min Quantity</label>
                                <input v-model.number="tier.min_quantity" type="number" min="1" required
                                    class="w-full bg-white/10 border border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Tipe Diskon</label>
                                <select v-model="tier.discount_type" required
                                    class="w-full bg-white/10 border border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                                    <option v-for="type in discountTypes" :key="type.value" :value="type.value">
                                        {{ type.label }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Nilai Diskon</label>
                                <input v-model.number="tier.discount_value" type="number" step="0.01" min="0" required
                                    class="w-full bg-white/10 border border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                            </div>
                            <div class="flex items-end">
                                <button type="button" @click="removeTier(index)"
                                    class="bg-red-600 text-white px-3 py-2 rounded-lg hover:bg-red-700 transition">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Bundling -->
                    <div v-if="isBundling" class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-white">Bundling Rules</h3>
                            <button type="button" @click="addBundle"
                                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                                Tambah Bundle
                            </button>
                        </div>

                        <div v-for="(bundle, index) in form.bundles" :key="index"
                            class="grid grid-cols-1 md:grid-cols-6 gap-4 p-4 bg-white/5 rounded-lg">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Beli Produk</label>
                                <select v-model="bundle.buy_product_id" required
                                    class="w-full bg-white/10 border border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                                    <option value="">Pilih Produk</option>
                                    <option v-for="product in products" :key="product.id" :value="product.id">
                                        {{ product.name }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Qty Beli</label>
                                <input v-model.number="bundle.buy_quantity" type="number" min="1" required
                                    class="w-full bg-white/10 border border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Gratis Produk</label>
                                <select v-model="bundle.get_product_id" required
                                    class="w-full bg-white/10 border border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                                    <option value="">Pilih Produk</option>
                                    <option v-for="product in products" :key="product.id" :value="product.id">
                                        {{ product.name }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Qty Gratis</label>
                                <input v-model.number="bundle.get_quantity" type="number" min="1" required
                                    class="w-full bg-white/10 border border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Harga Gratis</label>
                                <input v-model.number="bundle.get_price" type="number" step="0.01" min="0"
                                    class="w-full bg-white/10 border border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                            </div>
                            <div class="flex items-end">
                                <button type="button" @click="removeBundle(index)"
                                    class="bg-red-600 text-white px-3 py-2 rounded-lg hover:bg-red-700 transition">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Date Range -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Tanggal Mulai</label>
                            <input v-model="form.start_date" type="date" required
                                class="w-full bg-white/5 border border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500 px-4 py-3">
                            <div v-if="form.errors.start_date" class="text-red-400 text-sm mt-1">{{
                                form.errors.start_date }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Tanggal Berakhir</label>
                            <input v-model="form.end_date" type="date" required
                                class="w-full bg-white/5 border border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500 px-4 py-3">
                            <div v-if="form.errors.end_date" class="text-red-400 text-sm mt-1">{{ form.errors.end_date
                                }}</div>
                        </div>
                    </div>

                    <!-- Advanced Options -->
                    <div>
                        <button type="button" @click="showAdvancedOptions = !showAdvancedOptions"
                            class="text-blue-400 hover:text-blue-300 text-sm font-medium">
                            {{ showAdvancedOptions ? 'Sembunyikan' : 'Tampilkan' }} Opsi Lanjutan
                        </button>
                    </div>

                    <div v-if="showAdvancedOptions" class="space-y-6 p-4 bg-white/5 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Minimal Pembelian
                                    (Rp)</label>
                                <input v-model.number="form.min_purchase_amount" type="number" step="0.01" min="0"
                                    class="w-full bg-white/10 border border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500 px-4 py-3">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Minimal Quantity</label>
                                <input v-model.number="form.min_quantity" type="number" min="1"
                                    class="w-full bg-white/10 border border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500 px-4 py-3">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Batas Penggunaan</label>
                                <input v-model.number="form.usage_limit" type="number" min="1"
                                    class="w-full bg-white/10 border border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500 px-4 py-3">
                            </div>
                        </div>

                        <div class="flex items-center space-x-6">
                            <label class="flex items-center">
                                <input v-model="form.is_active" type="checkbox"
                                    class="rounded border-white/20 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-300">Aktif</span>
                            </label>

                            <label class="flex items-center">
                                <input v-model="form.is_stackable" type="checkbox"
                                    class="rounded border-white/20 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-300">Dapat Ditumpuk</span>
                            </label>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex justify-end space-x-4">
                        <button type="button" @click="$inertia.visit(route('promotions.index'))"
                            class="bg-gray-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-gray-700 transition">
                            Batal
                        </button>
                        <button type="submit" :disabled="form.processing"
                            class="bg-gradient-to-r from-blue-400 to-blue-500 text-white font-bold py-3 px-6 rounded-lg hover:from-blue-500 hover:to-blue-600 transition disabled:opacity-50">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
