<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    products: Array,
    customers: Array,
    activeShift: Object,
});

const form = useForm({
    items: [],
    customer_id: null,
    payment_method: 'cash',
    amount_paid: 0,
    notes: '',
});

const cart = ref([]);
const selectedCustomer = ref(null);
const searchTerm = ref('');
// Removed activeTab - only products now
const showPaymentModal = ref(false);
const showReceiptModal = ref(false);
const selectedPaymentMethod = ref('cash');
const receiptData = ref(null);
const currentTime = ref('');
const currentDate = ref('');
const currentShift = ref('');
const cashierName = ref('');
const barcodeInput = ref('');
const barcodeRef = ref(null);

const addProductToCart = (product) => {
    // Check if product has stock
    const stockQuantity = product.stocks?.[0]?.quantity ?? 0;
    if (stockQuantity < 1) {
        // Show notification for insufficient stock
        alert(`Produk "${product.name}" tidak dapat dijual karena stok kurang dari 1. Stok tersedia: ${stockQuantity}`);
        return;
    }

    const existing = cart.value.find(item => item.id === product.id);
    if (existing) {
        // Check if adding one more would exceed stock
        if (existing.quantity + 1 > stockQuantity) {
            alert(`Tidak dapat menambah jumlah produk "${product.name}". Stok maksimal: ${stockQuantity}`);
            return;
        }
        existing.quantity++;
    } else {
        // Calculate promotion discount for this product
        const basePrice = product.selling_price;
        let finalPrice = basePrice;
        let discountAmount = 0;
        let promotionName = null;
        let bundledProducts = [];

        if (product.active_promotion) {
            const promo = product.active_promotion;
            promotionName = promo.name;

            // Handle different promotion types
            if (promo.promotion_type === 'tiered_pricing') {
                // Tiered pricing logic will be handled in getPriceWithPromotion
                const pricing = getPriceWithPromotion(product, 1);
                finalPrice = pricing.final_price;
                discountAmount = pricing.discount_amount;
                bundledProducts = pricing.bundled_products || [];
            } else if (promo.promotion_type === 'bundling') {
                // Bundling logic will be handled in getPriceWithPromotion
                const pricing = getPriceWithPromotion(product, 1);
                finalPrice = pricing.final_price;
                discountAmount = pricing.discount_amount;
                bundledProducts = pricing.bundled_products || [];
            } else {
                // Standard discount
                if (promo.discount_type === 'percentage') {
                    discountAmount = basePrice * (promo.discount_value / 100);
                } else {
                    discountAmount = promo.discount_value;
                }

                if (promo.max_discount_amount && discountAmount > promo.max_discount_amount) {
                    discountAmount = promo.max_discount_amount;
                }

                finalPrice = basePrice - discountAmount;
            }
        }

        cart.value.push({
            ...product,
            quantity: 1,
            type: 'product',
            original_price: basePrice,
            final_price: finalPrice,
            discount_amount: discountAmount,
            promotion_name: promotionName,
            bundled_products: bundledProducts
        });

        // Add bundled products to cart
        bundledProducts.forEach(bundled => {
            cart.value.push({
                ...bundled,
                type: 'bundled',
                quantity: bundled.quantity,
                original_price: bundled.price,
                final_price: bundled.price,
                discount_amount: 0,
                promotion_name: `${promotionName} (Bundled)`,
                bundled_from: product.id
            });
        });
    }
    updateFormItems();
};

// Removed manual promotion addition - discounts are now applied automatically

const removeFromCart = (id) => {
    // Remove the item and all its bundled products
    cart.value = cart.value.filter(item => item.id !== id && item.bundled_from !== id);
    updateFormItems();
};

const updateQuantity = (id, quantity) => {
    const item = cart.value.find(item => item.id === id);
    if (item) {
        const newQuantity = Math.max(1, parseInt(quantity));
        const stockQuantity = item.stocks?.[0]?.quantity ?? 0;

        // Check stock limit
        if (newQuantity > stockQuantity) {
            alert(`Jumlah tidak boleh melebihi stok tersedia. Stok maksimal: ${stockQuantity}`);
            return;
        }

        // If quantity changed, recalculate promotion
        if (newQuantity !== item.quantity) {
            item.quantity = newQuantity;

            // Recalculate promotion for this item
            if (item.active_promotion) {
                const pricing = getPriceWithPromotion(item, newQuantity);
                item.final_price = pricing.final_price;
                item.discount_amount = pricing.discount_amount;

                // Update bundled products
                const existingBundled = cart.value.filter(cartItem => cartItem.bundled_from === id);
                cart.value = cart.value.filter(cartItem => cartItem.bundled_from !== id);

                pricing.bundled_products.forEach(bundled => {
                    cart.value.push({
                        ...bundled,
                        type: 'bundled',
                        quantity: bundled.quantity,
                        original_price: bundled.price,
                        final_price: bundled.price,
                        discount_amount: 0,
                        promotion_name: `${item.promotion_name} (Bundled)`,
                        bundled_from: id
                    });
                });
            }
        }
    }
    updateFormItems();
};

const updateFormItems = () => {
    form.items = cart.value.filter(item => item.type === 'product').map(item => ({
        product_id: item.id,
        quantity: item.quantity,
        price: item.selling_price,
    }));
};

const calculateSubtotal = computed(() => {
    return cart.value.filter(item => item.type === 'product').reduce((total, item) => total + (item.selling_price * item.quantity), 0);
});

const calculateDiscount = computed(() => {
    let discount = 0;
    cart.value.filter(item => item.type === 'product').forEach(item => {
        // Calculate discount per item based on quantity and active promotions
        const itemDiscount = (item.original_price - item.final_price) * item.quantity;
        discount += itemDiscount;
    });
    return discount;
});

const calculateTotal = computed(() => {
    return Math.max(0, calculateSubtotal.value - calculateDiscount.value);
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};

const getProductPrice = (product) => {
    if (product.active_promotion) {
        const pricing = getPriceWithPromotion(product, 1);
        return formatCurrency(pricing.final_price);
    }

    return formatCurrency(product.selling_price);
};

const getPriceWithPromotion = (product, quantity = 1) => {
    const basePrice = product.selling_price;
    let finalPrice = basePrice;
    let discountAmount = 0;
    let bundledProducts = [];

    if (product.active_promotion) {
        const promo = product.active_promotion;

        if (promo.promotion_type === 'tiered_pricing') {
            // Find the highest applicable tier
            const applicableTiers = promo.tiers?.filter(tier => quantity >= tier.min_quantity) || [];
            if (applicableTiers.length > 0) {
                const bestTier = applicableTiers.reduce((prev, current) =>
                    (prev.min_quantity > current.min_quantity) ? prev : current
                );

                if (bestTier.discount_type === 'percentage') {
                    discountAmount = basePrice * (bestTier.discount_value / 100);
                } else {
                    discountAmount = bestTier.discount_value;
                }

                finalPrice = basePrice - discountAmount;
            }
        } else if (promo.promotion_type === 'bundling') {
            // Calculate how many bundles can be made
            const bundles = promo.bundles || [];
            let totalBundledProducts = [];

            bundles.forEach(bundle => {
                const bundleCount = Math.floor(quantity / bundle.buy_quantity);
                if (bundleCount > 0) {
                    const bundledProduct = {
                        id: bundle.get_product_id,
                        name: bundle.get_product_name || `Product ${bundle.get_product_id}`,
                        price: bundle.get_price || 0,
                        quantity: bundleCount * bundle.get_quantity
                    };
                    totalBundledProducts.push(bundledProduct);
                }
            });

            bundledProducts = totalBundledProducts;
            // For bundling, the main product price might be discounted
            finalPrice = basePrice; // Or apply additional discount if specified
        } else {
            // Standard discount
            if (promo.discount_type === 'percentage') {
                discountAmount = basePrice * (promo.discount_value / 100);
            } else {
                discountAmount = promo.discount_value;
            }

            if (promo.max_discount_amount && discountAmount > promo.max_discount_amount) {
                discountAmount = promo.max_discount_amount;
            }

            finalPrice = basePrice - discountAmount;
        }
    }

    return {
        original_price: basePrice,
        final_price: finalPrice,
        discount_amount: discountAmount,
        bundled_products: bundledProducts
    };
};

const openPaymentModal = () => {
    if (cart.value.length === 0) return;
    selectedPaymentMethod.value = 'cash';
    showPaymentModal.value = true;
};

const confirmPayment = () => {
    form.payment_method = selectedPaymentMethod.value;
    form.amount_paid = form.amount_paid || calculateTotal.value;
    form.customer_id = selectedCustomer.value?.id || null;

    form.post(route('sales.store'), {
        onSuccess: (response) => {
            // Assume response includes sale data for receipt
            receiptData.value = {
                sale: response.sale || {},
                items: cart.value,
                subtotal: calculateSubtotal.value,
                discount: calculateDiscount.value,
                total: calculateTotal.value,
                amount_paid: form.amount_paid,
                change: Math.max(0, form.amount_paid - calculateTotal.value),
                payment_method: selectedPaymentMethod.value,
                customer: selectedCustomer.value,
                date: new Date().toLocaleString('id-ID')
            };
            showPaymentModal.value = false;
            showReceiptModal.value = true;
            cart.value = [];
            selectedCustomer.value = null;
            form.reset();
        },
        onError: (errors) => {
            console.error('Form submission errors:', errors);
        },
    });
};

const printReceipt = () => {
    window.print();
};

const closeReceiptModal = () => {
    showReceiptModal.value = false;
    receiptData.value = null;
};

// Update time and date
const updateDateTime = () => {
    const now = new Date();
    currentTime.value = now.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
    currentDate.value = now.toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });

    // Use shift data from database if available
    if (props.activeShift) {
        currentShift.value = props.activeShift.shift_code;
        cashierName.value = props.activeShift.name;
    } else {
        // Fallback to time-based shift if no active shift
        const hour = now.getHours();
        if (hour >= 6 && hour < 14) {
            currentShift.value = 'Pagi';
        } else if (hour >= 14 && hour < 22) {
            currentShift.value = 'Siang';
        } else {
            currentShift.value = 'Malam';
        }
        cashierName.value = props.auth?.user?.name || 'Kasir';
    }
};

// Initialize date/time and cashier info
onMounted(() => {
    updateDateTime();
    setInterval(updateDateTime, 1000); // Update every second

    // Get cashier name from props or auth
    cashierName.value = props.auth?.user?.name || 'Kasir';
});

// Get cashier name from auth
if (typeof window !== 'undefined' && window.auth) {
    cashierName.value = window.auth.user?.name || 'Kasir';
}

// Scan barcode function
const scanBarcode = () => {
    const barcode = barcodeInput.value.trim();
    if (!barcode) return;

    // Find product by barcode
    const product = props.products?.find(p => p.barcode === barcode);
    if (product) {
        addProductToCart(product);
        barcodeInput.value = '';
        return;
    }

    // If not found, show error or just clear
    barcodeInput.value = '';
    // Could add a toast notification here for invalid barcode
};

// Check if customer exists in customers table
const customerExists = (customer) => {
    return props.customers?.some(c => c.id === customer.id) || false;
};
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Transaksi Penjualan (POS)" />

        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-xl text-white leading-tight">
                        Transaksi Penjualan (POS)
                    </h2>
                    <p class="text-sm text-gray-400 mt-1">
                        Kelola transaksi penjualan dengan tampilan modern dan efisien.
                    </p>
                </div>
                <div class="flex items-center gap-6 text-white">
                    <div class="text-right">
                        <div class="text-sm text-gray-300">{{ currentDate }}</div>
                        <div class="text-lg font-semibold">{{ currentTime }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-300">Shift</div>
                        <div class="text-lg font-semibold">{{ currentShift }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-300">Kasir</div>
                        <div class="text-lg font-semibold">{{ cashierName }}</div>
                    </div>
                </div>
            </div>
        </template>

        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="flex h-screen bg-gray-900/50 backdrop-blur-md border border-white/10 rounded-2xl shadow-xl overflow-hidden">
                <!-- Kolom Kiri: Produk & Promosi -->
                <div class="w-3/5 p-6 flex flex-col bg-white/5">
                    <!-- Header for Products Section -->
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold text-white mb-2">Produk Tersedia</h3>
                        <p class="text-sm text-gray-400">Pilih produk untuk ditambahkan ke keranjang</p>
                    </div>

                    <!-- Search & Barcode -->
                    <div class="mb-4 space-y-3">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
                            </svg>
                            <input v-model="searchTerm" type="text" placeholder="Cari produk..."
                                class="w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500 px-4 py-3">
                        </div>
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2m-9 0h10m-9 0V1m10 3V1m0 3l1 1v16a2 2 0 01-2 2H6a2 2 0 01-2-2V8l1-1z" />
                            </svg>
                            <input v-model="barcodeInput" @keydown.enter="scanBarcode" type="text"
                                placeholder="Scan barcode produk/promosi..."
                                class="w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-green-500 focus:border-green-500 px-4 py-3"
                                ref="barcodeRef">
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="flex-grow overflow-y-auto">
                        <div v-if="props.products?.length === 0" class="text-center text-gray-400 py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-500 mb-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p class="text-lg font-medium text-gray-300 mb-2">Belum ada produk</p>
                            <p class="text-gray-400">Tambah produk baru atau impor dari template.</p>
                            <a :href="route('master.products.index')"
                                class="text-blue-400 hover:text-blue-300 mt-2 inline-block">Kelola Produk</a>
                        </div>
                        <div v-else class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            <div v-for="product in props.products?.filter(p =>
                                p.name.toLowerCase().includes(searchTerm.toLowerCase()) || p.barcode?.includes(searchTerm)
                            )" :key="product.id" @click="addProductToCart(product)"
                                class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl p-4 text-center cursor-pointer hover:bg-white/10 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 relative">
                                <!-- Promotion Badge -->
                                <div v-if="product.active_promotion"
                                    class="absolute -top-2 -right-2 bg-gradient-to-r from-red-500 to-pink-500 text-white text-xs px-2 py-1 rounded-full font-semibold shadow-lg">
                                    {{ product.active_promotion.discount_type === 'percentage' ?
                                        `${product.active_promotion.discount_value}%` : 'Diskon' }}
                                </div>
                                <div class="text-xs text-gray-400 mb-2">{{ product.barcode || 'No Barcode' }}</div>
                                <h3 class="font-semibold text-white mb-2">{{ product.name }}</h3>
                                <div class="mb-2">
                                    <p v-if="product.active_promotion" class="text-sm text-gray-400 line-through">
                                        {{ formatCurrency(product.selling_price) }}
                                    </p>
                                    <p class="text-sm text-blue-400 font-medium">
                                        {{ getProductPrice(product) }}
                                    </p>
                                </div>
                                <div class="flex items-center justify-center gap-1">
                                    <svg class="w-3 h-3 text-emerald-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    <p class="text-xs text-emerald-400">Stok: {{ product.stocks?.[0]?.quantity ?? 0 }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan: Keranjang & Pembayaran -->
                <div class="w-2/5 bg-white/5 backdrop-blur-md border-l border-white/10 flex flex-col">
                    <!-- Fixed Header Section -->
                    <div class="p-6 border-b border-white/10">
                        <!-- Customer Selection -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-300 mb-2">Pelanggan</label>
                            <select v-model="selectedCustomer"
                                class="w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500 px-4 py-3">
                                <option :value="null">Pelanggan Umum</option>
                                <option v-for="customer in props.customers" :key="customer.id" :value="customer">
                                    {{ customer.name }} ({{ customer.phone || 'No Phone' }})
                                    <span v-if="!customerExists(customer)" class="text-red-400">*</span>
                                </option>
                            </select>
                            <p v-if="selectedCustomer && !customerExists(selectedCustomer)"
                                class="text-xs text-red-400 mt-1">
                                Pelanggan belum terdaftar dalam sistem
                            </p>
                        </div>
                    </div>

                    <!-- Scrollable Cart Section -->
                    <div class="flex-1 overflow-y-auto px-6">
                        <!-- Cart Items -->
                        <div class="py-4">
                            <div v-if="cart.length === 0" class="text-center text-gray-400 mt-10">
                                <svg class="mx-auto h-12 w-12 text-gray-500 mb-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.1 5H19M7 13l-1.1 5M7 13l1.1-5m8.9 5L17 8m0 0l1.1 5M9 21a2 2 0 11-4 0 2 2 0 014 0zm10 0a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <p>Keranjang kosong. Pilih produk atau promosi di kiri.</p>
                            </div>
                            <div v-for="item in cart" :key="`${item.id}-${item.type}`"
                                class="flex items-center justify-between mb-3 p-3 bg-white/5 rounded-lg border border-white/10"
                                :class="{ 'opacity-75': item.type === 'bundled' }">
                                <div class="flex-1">
                                    <p class="font-semibold text-white">
                                        {{ item.name }}
                                        <span v-if="item.type === 'bundled'"
                                            class="text-xs text-blue-300">(Bundled)</span>
                                    </p>
                                    <div class="text-sm text-gray-400">
                                        <span v-if="item.discount_amount > 0" class="line-through">{{
                                            formatCurrency(item.original_price) }}</span>
                                        <span v-if="item.discount_amount > 0" class="text-green-400 ml-2">{{
                                            formatCurrency(item.final_price) }}</span>
                                        <span v-else>{{ formatCurrency(item.final_price) }}</span>
                                        <span class="ml-2">x {{ item.quantity }}</span>
                                    </div>
                                    <div v-if="item.promotion_name" class="text-xs text-purple-300 mt-1">
                                        Promo: {{ item.promotion_name }}
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button @click="removeFromCart(item.id)" class="text-red-400 hover:text-red-300"
                                        :disabled="item.type === 'bundled'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                    <div class="flex items-center gap-1" v-if="item.type !== 'bundled'">
                                        <button @click="updateQuantity(item.id, item.quantity - 1)"
                                            class="text-gray-400 hover:text-white px-2 py-1 rounded">-</button>
                                        <input type="number" v-model.number="item.quantity"
                                            @input="updateQuantity(item.id, $event.target.value)"
                                            class="w-12 text-center bg-white/10 border-white/20 rounded text-white text-sm"
                                            min="1">
                                        <button @click="updateQuantity(item.id, item.quantity + 1)"
                                            class="text-gray-400 hover:text-white px-2 py-1 rounded">+</button>
                                    </div>
                                    <div class="text-right text-white font-semibold w-24">
                                        {{ formatCurrency(item.final_price * item.quantity) }}
                                        <div v-if="item.discount_amount > 0" class="text-xs text-green-400">
                                            Hemat: {{ formatCurrency(item.discount_amount * item.quantity) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fixed Footer Section -->
                    <div class="p-6 border-t border-white/10 bg-white/5">
                        <!-- Summary & Payment -->
                        <div class="space-y-3">
                            <div class="flex justify-between text-sm text-gray-300">
                                <span>Subtotal</span>
                                <span>{{ formatCurrency(calculateSubtotal) }}</span>
                            </div>
                            <div v-if="calculateDiscount > 0" class="flex justify-between text-sm text-green-400">
                                <span>Diskon</span>
                                <span>-{{ formatCurrency(calculateDiscount) }}</span>
                            </div>
                            <div
                                class="flex justify-between text-lg font-semibold text-white border-t border-white/20 pt-2">
                                <span>Total</span>
                                <span>{{ formatCurrency(calculateTotal) }}</span>
                            </div>

                            <textarea v-model="form.notes" placeholder="Catatan..."
                                class="w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500 px-4 py-3"
                                rows="2"></textarea>

                            <button @click="openPaymentModal" :disabled="cart.length === 0"
                                class="w-full bg-gradient-to-r from-green-400 to-emerald-400 text-white font-bold py-4 rounded-xl text-lg shadow-xl hover:scale-105 hover:from-green-500 hover:to-emerald-500 transition-transform duration-200 disabled:opacity-50">
                                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                BAYAR
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Modal -->
        <Modal :show="showPaymentModal" @close="showPaymentModal = false">
            <div
                class="p-8 bg-gray-900/70 backdrop-blur-md border border-white/10 rounded-2xl shadow-lg max-w-md mx-auto text-white">
                <h2 class="text-lg font-semibold mb-6 text-center">Pilih Metode Pembayaran</h2>

                <div class="space-y-3 mb-6">
                    <label
                        class="flex items-center p-4 bg-white/5 rounded-lg cursor-pointer hover:bg-white/10 transition-all">
                        <input type="radio" v-model="selectedPaymentMethod" value="cash" class="mr-3">
                        <div>
                            <div class="font-medium">Tunai</div>
                            <div class="text-sm text-gray-400">Pembayaran dengan uang tunai</div>
                        </div>
                    </label>
                    <label
                        class="flex items-center p-4 bg-white/5 rounded-lg cursor-pointer hover:bg-white/10 transition-all">
                        <input type="radio" v-model="selectedPaymentMethod" value="debit" class="mr-3">
                        <div>
                            <div class="font-medium">Kartu Debit</div>
                            <div class="text-sm text-gray-400">Pembayaran dengan kartu debit</div>
                        </div>
                    </label>
                    <label
                        class="flex items-center p-4 bg-white/5 rounded-lg cursor-pointer hover:bg-white/10 transition-all">
                        <input type="radio" v-model="selectedPaymentMethod" value="credit" class="mr-3">
                        <div>
                            <div class="font-medium">Kartu Kredit</div>
                            <div class="text-sm text-gray-400">Pembayaran dengan kartu kredit</div>
                        </div>
                    </label>
                    <label
                        class="flex items-center p-4 bg-white/5 rounded-lg cursor-pointer hover:bg-white/10 transition-all">
                        <input type="radio" v-model="selectedPaymentMethod" value="transfer" class="mr-3">
                        <div>
                            <div class="font-medium">Transfer Bank</div>
                            <div class="text-sm text-gray-400">Pembayaran via transfer bank</div>
                        </div>
                    </label>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Jumlah Bayar</label>
                    <input type="number" v-model="form.amount_paid"
                        @input="form.amount_paid = Math.max(0, $event.target.value)"
                        class="w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500 px-4 py-3"
                        placeholder="0">
                </div>

                <div class="flex justify-between text-sm mb-6">
                    <span>Total: {{ formatCurrency(calculateTotal) }}</span>
                    <span class="text-green-400">Kembali: {{ formatCurrency(Math.max(0, form.amount_paid -
                        calculateTotal))
                        }}</span>
                </div>

                <div class="flex gap-3">
                    <button @click="showPaymentModal = false"
                        class="flex-1 bg-gray-600 text-white font-bold py-3 rounded-lg hover:bg-gray-700 transition-all">
                        Batal
                    </button>
                    <button @click="confirmPayment" :disabled="form.processing"
                        class="flex-1 bg-gradient-to-r from-blue-400 to-blue-500 text-white font-bold py-3 rounded-lg hover:from-blue-500 hover:to-blue-600 transition-all disabled:opacity-50">
                        {{ form.processing ? 'Memproses...' : 'Konfirmasi' }}
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Receipt Modal -->
        <Modal :show="showReceiptModal" @close="closeReceiptModal">
            <div class="p-8 bg-white rounded-2xl shadow-lg max-w-sm mx-auto text-gray-900" id="receipt">
                <div class="text-center mb-6">
                    <h2 class="text-lg font-bold">STRUK PEMBELIAN</h2>
                    <p class="text-sm text-gray-600">{{ receiptData?.date }}</p>
                </div>

                <div class="border-t border-b py-4 mb-4">
                    <div v-for="item in receiptData?.items?.filter(i => i.type === 'product')" :key="item.id"
                        class="text-sm mb-2">
                        <div class="flex justify-between">
                            <span>{{ item.name }} x{{ item.quantity }}</span>
                            <span>{{ formatCurrency(item.final_price * item.quantity) }}</span>
                        </div>
                        <div v-if="item.discount_amount > 0" class="text-xs text-green-600 ml-4">
                            Diskon: -{{ formatCurrency(item.discount_amount * item.quantity) }}
                        </div>
                        <div v-if="item.promotion_name" class="text-xs text-purple-600 ml-4">
                            Promo: {{ item.promotion_name }}
                        </div>
                    </div>
                </div>

                <div class="space-y-1 text-sm mb-4">
                    <div class="flex justify-between">
                        <span>Subtotal:</span>
                        <span>{{ formatCurrency(receiptData?.subtotal) }}</span>
                    </div>
                    <div v-if="receiptData?.discount > 0" class="flex justify-between text-green-600">
                        <span>Diskon:</span>
                        <span>-{{ formatCurrency(receiptData?.discount) }}</span>
                    </div>
                    <div class="flex justify-between font-bold">
                        <span>Total:</span>
                        <span>{{ formatCurrency(receiptData?.total) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Bayar:</span>
                        <span>{{ formatCurrency(receiptData?.amount_paid) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Kembali:</span>
                        <span>{{ formatCurrency(receiptData?.change) }}</span>
                    </div>
                </div>

                <div class="text-center text-xs text-gray-500 mb-4">
                    <p>Metode: {{ receiptData?.payment_method === 'cash' ? 'Tunai' : receiptData?.payment_method ===
                        'debit' ?
                        'Kartu Debit' : receiptData?.payment_method === 'credit' ? 'Kartu Kredit' : 'Transfer' }}</p>
                    <p v-if="receiptData?.customer">Pelanggan: {{ receiptData.customer.name }}</p>
                </div>

                <div class="flex gap-3">
                    <button @click="printReceipt"
                        class="flex-1 bg-blue-600 text-white font-bold py-2 rounded-lg hover:bg-blue-700 transition-all">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak
                    </button>
                    <button @click="closeReceiptModal"
                        class="flex-1 bg-gray-600 text-white font-bold py-2 rounded-lg hover:bg-gray-700 transition-all">
                        Tutup
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
