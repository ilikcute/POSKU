<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import { ref, computed, onMounted, watch } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Modal from "@/Components/Modal.vue";
import AppPanel from "@/Components/AppPanel.vue";
import AppPageHeader from "@/Components/AppPageHeader.vue";
import InlineNotice from "@/Components/InlineNotice.vue";

const props = defineProps({
    products: Array,
    customers: Array,
    activeShift: Object,
    promotions: Array,
    stocks: Array,
});

// Form state
const form = useForm({
    items: [],
    customer_id: null,
    payment_method: "cash",
    amount_paid: 0,
    notes: "",
});

// Reactive state
const cart = ref([]);
const selectedCustomer = ref(null);
const searchTerm = ref("");
const showPaymentModal = ref(false);
const showReceiptModal = ref(false);
const selectedPaymentMethod = ref("cash");
const receiptData = ref(null);
const currentTime = ref("");
const currentDate = ref("");
const currentShift = ref("");
const cashierName = ref("");
const barcodeInput = ref("");
const categoryFilter = ref("all");
const notification = ref({ show: false, message: "", type: "info" });

// Computed values
const categoryOptions = computed(() => {
    const unique = new Set();
    (props.products || []).forEach((product) => {
        if (product.category?.name) {
            unique.add(product.category.name);
        }
    });
    return Array.from(unique);
});

const filteredProducts = computed(() => {
    let filtered = props.products || [];

    if (searchTerm.value) {
        const search = searchTerm.value.toLowerCase();
        filtered = filtered.filter(
            (p) =>
                p.name.toLowerCase().includes(search) ||
                p.barcode?.includes(search)
        );
    }

    if (categoryFilter.value !== "all") {
        filtered = filtered.filter((product) => {
            const categoryName = product.category?.name ?? "Tanpa Kategori";
            return categoryName === categoryFilter.value;
        });
    }

    return filtered;
});

const limitedProducts = computed(() => filteredProducts.value.slice(0, 7));

const calculateSubtotal = computed(() => {
    return cart.value
        .filter((item) => item.type === "product")
        .reduce((total, item) => total + item.selling_price * item.quantity, 0);
});

const calculateDiscount = computed(() => {
    return cart.value
        .filter((item) => item.type === "product")
        .reduce(
            (total, item) =>
                total +
                (item.original_price - item.final_price) * item.quantity,
            0
        );
});

const calculateTotal = computed(() =>
    Math.max(0, calculateSubtotal.value - calculateDiscount.value)
);

const changeAmount = computed(() =>
    Math.max(0, form.amount_paid - calculateTotal.value)
);

// Helper functions
const formatCurrency = (value) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(value);
};

const showNotification = (message, type = "info") => {
    notification.value = { show: true, message, type };
    setTimeout(() => {
        notification.value.show = false;
    }, 3000);
};

const getPriceWithPromotion = (product, quantity = 1) => {
    const basePrice = product.selling_price;
    let finalPrice = basePrice;
    let discountAmount = 0;
    let bundledProducts = [];

    if (product.active_promotion) {
        const promo = product.active_promotion;

        if (promo.promotion_type === "tiered_pricing") {
            const applicableTiers =
                promo.tiers?.filter((tier) => quantity >= tier.min_quantity) ||
                [];
            if (applicableTiers.length > 0) {
                const bestTier = applicableTiers.reduce((prev, current) =>
                    prev.min_quantity > current.min_quantity ? prev : current
                );

                discountAmount =
                    bestTier.discount_type === "percentage"
                        ? basePrice * (bestTier.discount_value / 100)
                        : bestTier.discount_value;

                finalPrice = basePrice - discountAmount;
            }
        } else if (promo.promotion_type === "bundling") {
            const bundles = promo.bundles || [];
            bundles.forEach((bundle) => {
                const bundleCount = Math.floor(quantity / bundle.buy_quantity);
                if (bundleCount > 0) {
                    bundledProducts.push({
                        id: bundle.get_product_id,
                        name:
                            bundle.get_product_name ||
                            `Product ${bundle.get_product_id}`,
                        price: bundle.get_price || 0,
                        quantity: bundleCount * bundle.get_quantity,
                    });
                }
            });
            finalPrice = basePrice;
        } else {
            discountAmount =
                promo.discount_type === "percentage"
                    ? basePrice * (promo.discount_value / 100)
                    : promo.discount_value;

            if (
                promo.max_discount_amount &&
                discountAmount > promo.max_discount_amount
            ) {
                discountAmount = promo.max_discount_amount;
            }

            finalPrice = basePrice - discountAmount;
        }
    }

    return {
        original_price: basePrice,
        final_price: finalPrice,
        discount_amount: discountAmount,
        bundled_products: bundledProducts,
    };
};

// Cart operations
const addProductToCart = (product) => {
    const stockQuantity = product.stocks?.[0]?.quantity ?? 0;

    if (stockQuantity < 1) {
        showNotification(`Stok ${product.name} tidak tersedia`, "error");
        return;
    }

    const existing = cart.value.find(
        (item) => item.id === product.id && item.type === "product"
    );

    if (existing) {
        if (existing.quantity + 1 > stockQuantity) {
            showNotification(`Stok maksimal: ${stockQuantity}`, "warning");
            return;
        }
        existing.quantity++;

        if (existing.active_promotion) {
            const pricing = getPriceWithPromotion(existing, existing.quantity);
            existing.final_price = pricing.final_price;
            existing.discount_amount = pricing.discount_amount;
        }
    } else {
        const pricing = getPriceWithPromotion(product, 1);

        cart.value.push({
            ...product,
            quantity: 1,
            type: "product",
            original_price: pricing.original_price,
            final_price: pricing.final_price,
            discount_amount: pricing.discount_amount,
            promotion_name: product.active_promotion?.name || null,
            bundled_products: pricing.bundled_products,
        });

        pricing.bundled_products.forEach((bundled) => {
            cart.value.push({
                ...bundled,
                type: "bundled",
                bundled_from: product.id,
            });
        });

        showNotification(`${product.name} ditambahkan`, "success");
    }

    updateFormItems();
};

const removeFromCart = (id) => {
    const item = cart.value.find((i) => i.id === id);
    cart.value = cart.value.filter(
        (item) => item.id !== id && item.bundled_from !== id
    );
    if (item) showNotification(`${item.name} dihapus`, "info");
    updateFormItems();
};

const updateQuantity = (id, quantity) => {
    const item = cart.value.find((item) => item.id === id);
    if (!item) return;

    const newQuantity = Math.max(1, parseInt(quantity));
    const stockQuantity = item.stocks?.[0]?.quantity ?? 0;

    if (newQuantity > stockQuantity) {
        showNotification(`Stok maksimal: ${stockQuantity}`, "warning");
        return;
    }

    item.quantity = newQuantity;

    if (item.active_promotion) {
        const pricing = getPriceWithPromotion(item, newQuantity);
        item.final_price = pricing.final_price;
        item.discount_amount = pricing.discount_amount;

        cart.value = cart.value.filter(
            (cartItem) => cartItem.bundled_from !== id
        );
        pricing.bundled_products.forEach((bundled) => {
            cart.value.push({
                ...bundled,
                type: "bundled",
                bundled_from: id,
            });
        });
    }

    updateFormItems();
};

const updateFormItems = () => {
    form.items = cart.value
        .filter((item) => item.type === "product")
        .map((item) => ({
            product_id: item.id,
            quantity: item.quantity,
            price: item.selling_price,
        }));
};

// Payment operations
const openPaymentModal = () => {
    if (cart.value.length === 0) {
        showNotification("Keranjang masih kosong", "warning");
        return;
    }
    selectedPaymentMethod.value = "cash";
    form.amount_paid = calculateTotal.value;
    showPaymentModal.value = true;
};

const confirmPayment = () => {
    if (form.amount_paid < calculateTotal.value) {
        showNotification("Jumlah pembayaran kurang", "error");
        return;
    }

    form.payment_method = selectedPaymentMethod.value;
    form.customer_id = selectedCustomer.value?.id || null;

    form.post(route("sales.store"), {
        onSuccess: (response) => {
            receiptData.value = {
                sale: response.sale || {},
                items: cart.value,
                subtotal: calculateSubtotal.value,
                discount: calculateDiscount.value,
                total: calculateTotal.value,
                amount_paid: form.amount_paid,
                change: changeAmount.value,
                payment_method: selectedPaymentMethod.value,
                customer: selectedCustomer.value,
                date: new Date().toLocaleString("id-ID"),
            };

            showPaymentModal.value = false;
            showReceiptModal.value = true;
            cart.value = [];
            selectedCustomer.value = null;
            form.reset();
            showNotification("Transaksi berhasil", "success");
        },
        onError: (errors) => {
            showNotification("Transaksi gagal", "error");
            console.error("Form submission errors:", errors);
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

// Barcode scanner
const scanBarcode = () => {
    const barcode = barcodeInput.value.trim();
    if (!barcode) return;

    const product = props.products?.find((p) => p.barcode === barcode);
    if (product) {
        addProductToCart(product);
        barcodeInput.value = "";
        showNotification("Produk ditemukan", "success");
    } else {
        barcodeInput.value = "";
        showNotification("Barcode tidak ditemukan", "error");
    }
};

// Time and date updates
const updateDateTime = () => {
    const now = new Date();
    currentTime.value = now.toLocaleTimeString("id-ID", {
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
    });
    currentDate.value = now.toLocaleDateString("id-ID", {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
    });

    if (props.activeShift) {
        currentShift.value = props.activeShift.shift_code;
        cashierName.value = props.activeShift.name;
    } else {
        const hour = now.getHours();
        currentShift.value =
            hour >= 6 && hour < 14
                ? "Pagi"
                : hour >= 14 && hour < 22
                    ? "Siang"
                    : "Malam";
        cashierName.value = "Kasir";
    }
};

// Keyboard shortcuts
const handleKeyPress = (e) => {
    if (e.key === "F1") {
        e.preventDefault();
        document.querySelector('input[type="text"]')?.focus();
    } else if (e.key === "F2") {
        e.preventDefault();
        openPaymentModal();
    } else if (e.key === "Escape") {
        showPaymentModal.value = false;
    }
};

// Lifecycle hooks
onMounted(() => {
    updateDateTime();
    setInterval(updateDateTime, 1000);
    window.addEventListener("keydown", handleKeyPress);

    window.addEventListener("beforeunload", (e) => {
        if (cart.value.length > 0) {
            e.preventDefault();
            e.returnValue = "";
        }
    });
});
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Point of Sale" />

        <template #header>
            <AppPageHeader title="Point of Sale" description="Pantau inventory dan transaksi dari satu layar.">
                <template #actions>
                    <div class="w-full max-w-3xl ml-auto grid grid-cols-1 gap-2 text-white sm:grid-cols-3">
                        <div
                            class="bg-white/10 px-3 py-2 rounded-xl backdrop-blur border border-white/10 shadow-sm flex flex-col gap-0.5">
                            <p class="text-[10px] uppercase tracking-wide text-gray-300">
                                Waktu
                            </p>
                            <p class="text-xs font-semibold truncate">
                                {{ currentDate }}
                            </p>
                            <p class="text-sm font-semibold">{{ currentTime }}</p>
                        </div>
                        <div
                            class="bg-white/10 px-3 py-2 rounded-xl backdrop-blur border border-white/10 shadow-sm flex flex-col gap-0.5">
                            <p class="text-[10px] uppercase tracking-wide text-gray-300">
                                Shift
                            </p>
                            <p class="text-lg font-bold text-emerald-300 leading-tight">
                                {{ currentShift }}
                            </p>
                            <p class="text-[11px] text-gray-300 truncate">
                                {{ props.activeShift?.name || "Belum Aktif" }}
                            </p>
                        </div>
                        <div
                            class="bg-white/10 px-3 py-2 rounded-xl backdrop-blur border border-white/10 shadow-sm flex flex-col gap-0.5">
                            <p class="text-[10px] uppercase tracking-wide text-gray-300">
                                Kasir
                            </p>
                            <p class="text-sm font-semibold truncate">
                                {{ cashierName }}
                            </p>
                        </div>
                    </div>
                </template>
            </AppPageHeader>
        </template>

        <!-- Notification Toast -->
        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 translate-y-2"
            enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="notification.show" class="fixed top-20 right-4 z-50 max-w-sm">
                <div class="bg-white rounded-lg shadow-lg p-4 flex items-center gap-3" :class="{
                    'border-l-4 border-green-500':
                        notification.type === 'success',
                    'border-l-4 border-red-500':
                        notification.type === 'error',
                    'border-l-4 border-yellow-500':
                        notification.type === 'warning',
                    'border-l-4 border-blue-500':
                        notification.type === 'info',
                }">
                    <div class="flex-shrink-0">
                        <svg v-if="notification.type === 'success'" class="w-5 h-5 text-green-500" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        <svg v-else-if="notification.type === 'error'" class="w-5 h-5 text-red-500" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                        <svg v-else-if="notification.type === 'warning'" class="w-5 h-5 text-yellow-500"
                            fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd" />
                        </svg>
                        <svg v-else class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-900">
                        {{ notification.message }}
                    </p>
                </div>
            </div>
        </Transition>

        <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <InlineNotice v-if="!props.activeShift" variant="warning">
                <template #icon>!</template>
                Shift belum dibuka. Silakan buka shift terlebih dahulu agar
                transaksi tercatat pada perangkat ini.
            </InlineNotice>

            <div class="grid gap-6 lg:grid-cols-[2fr_1fr] min-h-[calc(100vh-220px)]">
                <!-- Left Panel -->
                <AppPanel class="flex flex-col min-h-[60vh] space-y-4">
                    <div class="grid gap-3 md:grid-cols-2">
                        <label class="relative">
                            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
                            </svg>
                            <input v-model="searchTerm" type="text" placeholder="Cari produk... (F1)"
                                class="w-full pl-12 pr-4 py-3 bg-white/10 border border-white/20 rounded-2xl text-gray-200 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
                        </label>
                        <label class="relative">
                            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                            <input v-model="barcodeInput" @keydown.enter="scanBarcode" type="text"
                                placeholder="Scan barcode..."
                                class="w-full pl-12 pr-4 py-3 bg-white/10 border border-white/20 rounded-2xl text-gray-200 placeholder-gray-400 focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all" />
                        </label>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 text-sm text-white/80">
                        <button @click="categoryFilter = 'all'" :class="[
                            'px-3 py-1 rounded-full border transition-all',
                            categoryFilter === 'all'
                                ? 'bg-emerald-500/20 border-emerald-400 text-white'
                                : 'border-white/10 hover:border-emerald-200/50',
                        ]">
                            Semua
                        </button>
                        <button v-for="category in categoryOptions" :key="category" @click="categoryFilter = category"
                            :class="[
                                'px-3 py-1 rounded-full border transition-all capitalize',
                                categoryFilter === category
                                    ? 'bg-emerald-500/20 border-emerald-400 text-white'
                                    : 'border-white/10 hover:border-emerald-200/50',
                            ]">
                            {{ category }}
                        </button>
                    </div>

                    <div class="flex items-center justify-between text-xs text-gray-400">
                        <span>
                            Menampilkan {{ filteredProducts.length }} produk
                        </span>
                        <span>Stok terakhir diperbarui realtime</span>
                    </div>

                    <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar">
                        <div v-if="limitedProducts.length === 0" class="text-center text-gray-400 py-20">
                            <svg class="mx-auto h-16 w-16 text-gray-500 mb-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            <p class="text-lg font-medium mb-1">
                                Tidak ada produk
                            </p>
                            <p class="text-sm text-gray-500">
                                Coba kata kunci pencarian lain
                            </p>
                        </div>
                        <div v-else class="space-y-3">
                            <div class="overflow-hidden rounded-2xl border border-white/10 bg-white/5">
                                <table class="min-w-full text-sm text-white/80">
                                    <thead class="bg-white/10 text-xs">
                                        <tr>
                                            <th class="px-4 py-3 text-left">
                                                Produk
                                            </th>
                                            <th class="px-4 py-3 text-left">
                                                Harga
                                            </th>
                                            <th class="px-4 py-3 text-center">
                                                Stok
                                            </th>
                                            <th class="px-4 py-3 text-right">
                                                Aksi
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-white/5">
                                        <tr v-for="product in limitedProducts" :key="product.id"
                                            class="hover:bg-white/5">
                                            <td class="px-4 py-3">
                                                <p class="font-semibold text-white">
                                                    {{ product.name }}
                                                </p>
                                                <p class="text-xs text-gray-400">
                                                    {{
                                                        product.category?.name ||
                                                        "Tanpa Kategori"
                                                    }}
                                                </p>
                                                <span v-if="
                                                    product.active_promotion
                                                "
                                                    class="inline-flex items-center mt-1 text-[10px] uppercase px-2 py-0.5 rounded-full bg-pink-500/20 text-pink-300">
                                                    Promo
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <p
                                                    class="text-sm font-bold bg-gradient-to-r from-blue-400 to-emerald-400 bg-clip-text text-transparent">
                                                    {{
                                                        formatCurrency(
                                                            getPriceWithPromotion(
                                                                product,
                                                                1
                                                            ).final_price
                                                        )
                                                    }}
                                                </p>
                                                <p v-if="
                                                    product.active_promotion
                                                " class="text-xs text-gray-500 line-through">
                                                    {{
                                                        formatCurrency(
                                                            product.selling_price
                                                        )
                                                    }}
                                                </p>
                                            </td>
                                            <td class="px-4 py-3 text-center text-xs text-gray-300">
                                                {{
                                                    product.stocks?.[0]
                                                        ?.quantity ?? 0
                                                }}
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <button @click="
                                                    addProductToCart(
                                                        product
                                                    )
                                                    "
                                                    class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-emerald-500/20 text-emerald-200 hover:bg-emerald-500/30 transition-colors">
                                                    Tambah
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <InlineNotice v-if="
                                filteredProducts.length >
                                limitedProducts.length
                            " variant="info">
                                Menampilkan 7 produk teratas. Gunakan pencarian
                                atau filter kategori untuk menemukan produk
                                lainnya.
                            </InlineNotice>
                        </div>
                    </div>
                </AppPanel>

                <!-- Right Panel -->
                <AppPanel class="flex flex-col min-h-[60vh] space-y-0">
                    <div class="p-4 border-b border-white/10 flex-shrink-0">
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Pelanggan
                        </label>
                        <select v-model="selectedCustomer"
                            class="w-full bg-white/10 border border-white/20 rounded-xl text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent px-4 py-3 transition-all">
                            <option :value="null">Pelanggan Umum</option>
                            <option v-for="customer in props.customers" :key="customer.id" :value="customer">
                                {{ customer.name }}
                                {{ customer.phone ? `(${customer.phone})` : "" }}
                            </option>
                        </select>
                        <div class="mt-3 text-xs text-gray-400" v-if="props.activeShift">
                            Shift aktif: {{ props.activeShift.shift_code }} •
                            Awal kas:
                            {{ formatCurrency(props.activeShift.initial_cash) }}
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto px-4 py-4 custom-scrollbar min-h-0">
                        <div v-if="cart.length === 0" class="text-center text-gray-400 mt-20">
                            <svg class="mx-auto h-20 w-20 text-gray-600 mb-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 100-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <p class="text-lg font-semibold text-gray-300 mb-1">
                                Keranjang Kosong
                            </p>
                            <p class="text-sm text-gray-500">
                                Pilih produk untuk memulai transaksi
                            </p>
                        </div>

                        <div v-else class="space-y-3">
                            <div v-for="item in cart" :key="`${item.id}-${item.type}`"
                                class="group relative bg-white/5 border border-white/10 rounded-xl p-4 hover:bg-white/10 transition-all"
                                :class="{ 'opacity-60 border-dashed': item.type === 'bundled' }">
                                <div class="flex items-start gap-3">
                                    <!-- Product Icon -->
                                    <div
                                        class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-blue-500/20 to-purple-500/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white/70" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                    </div>

                                    <!-- Item Details -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="flex-1 min-w-0">
                                                <p class="font-semibold text-white text-sm truncate">
                                                    {{ item.name }}
                                                    <span v-if="item.type === 'bundled'"
                                                        class="text-xs text-blue-300 ml-1">(Bundled)</span>
                                                </p>
                                                <div class="text-xs text-gray-400 mt-1">
                                                    <span v-if="item.discount_amount > 0" class="line-through">
                                                        {{ formatCurrency(item.original_price) }}
                                                    </span>
                                                    <span class="ml-1"
                                                        :class="item.discount_amount > 0 ? 'text-green-400 font-semibold' : ''">
                                                        {{ formatCurrency(item.final_price) }}
                                                    </span>
                                                </div>
                                                <div v-if="item.promotion_name"
                                                    class="inline-block mt-1 px-2 py-0.5 bg-purple-500/20 text-purple-300 text-xs rounded-full">
                                                    {{ item.promotion_name }}
                                                </div>
                                            </div>

                                            <!-- Remove Button -->
                                            <button v-if="item.type === 'product'" @click="removeFromCart(item.id)"
                                                class="text-red-400 hover:text-red-300 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>

                                        <!-- Quantity Controls -->
                                        <div class="flex items-center justify-between mt-3">
                                            <div v-if="item.type !== 'bundled'"
                                                class="flex items-center gap-2 bg-white/10 rounded-lg p-1">
                                                <button @click="updateQuantity(item.id, item.quantity - 1)"
                                                    class="w-7 h-7 flex items-center justify-center text-gray-300 hover:text-white hover:bg-white/10 rounded transition-all">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M20 12H4" />
                                                    </svg>
                                                </button>
                                                <input type="number" :value="item.quantity"
                                                    @input="updateQuantity(item.id, $event.target.value)"
                                                    class="w-14 text-center bg-transparent border-0 text-white font-semibold focus:ring-0"
                                                    min="1" />
                                                <button @click="updateQuantity(item.id, item.quantity + 1)"
                                                    class="w-7 h-7 flex items-center justify-center text-gray-300 hover:text-white hover:bg-white/10 rounded transition-all">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M12 4v16m8-8H4" />
                                                    </svg>
                                                </button>
                                            </div>
                                            <div v-else class="text-xs text-gray-400">
                                                Qty: {{ item.quantity }}
                                            </div>

                                            <!-- Subtotal -->
                                            <div class="text-right">
                                                <p class="text-white font-bold">
                                                    {{ formatCurrency(item.final_price * item.quantity) }}
                                                </p>
                                                <p v-if="item.discount_amount > 0" class="text-xs text-green-400">
                                                    Hemat
                                                    {{ formatCurrency(item.discount_amount * item.quantity) }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="p-4 border-t border-white/10 bg-gradient-to-b from-transparent to-white/5 flex-shrink-0">
                        <!-- Summary -->
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between text-sm text-gray-300">
                                <span>Subtotal</span>
                                <span class="font-semibold">{{ formatCurrency(calculateSubtotal) }}</span>
                            </div>
                            <div v-if="calculateDiscount > 0" class="flex justify-between text-sm">
                                <span class="text-green-400">Diskon Total</span>
                                <span class="text-green-400 font-semibold">-{{ formatCurrency(calculateDiscount)
                                }}</span>
                            </div>
                            <div class="flex justify-between items-center border-t border-white/20 pt-3 mt-3">
                                <span class="text-base font-semibold text-white">Total Bayar</span>
                                <span
                                    class="text-2xl font-bold bg-gradient-to-r from-blue-400 to-emerald-400 bg-clip-text text-transparent">
                                    {{ formatCurrency(calculateTotal) }}
                                </span>
                            </div>
                        </div>

                        <!-- Notes -->
                        <textarea v-model="form.notes" placeholder="Catatan transaksi (opsional)..."
                            class="w-full bg-white/5 border border-white/20 rounded-xl text-gray-200 placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent px-4 py-2.5 mb-4 transition-all resize-none text-sm"
                            rows="2"></textarea>

                        <!-- Payment Button -->
                        <button @click="openPaymentModal" :disabled="cart.length === 0"
                            class="w-full bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 disabled:from-gray-600 disabled:to-gray-700 text-white font-bold py-4 rounded-xl text-base shadow-lg hover:shadow-xl disabled:shadow-none transition-all duration-300 disabled:cursor-not-allowed group">
                            <div class="flex items-center justify-center gap-2">
                                <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span>PROSES PEMBAYARAN (F2)</span>
                            </div>
                        </button>
                    </div>
                </AppPanel>

            </div>
        </div>

        <!-- Payment Modal -->
        <Modal :show="showPaymentModal" @close="showPaymentModal = false">
            <div
                class="bg-gradient-to-br from-gray-900 to-gray-800 border border-white/20 rounded-3xl shadow-2xl max-w-lg mx-auto text-white overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-6 py-4">
                    <h2 class="text-xl font-bold text-center">Pembayaran</h2>
                </div>

                <div class="p-6 space-y-6">
                    <!-- Payment Methods -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-3">Metode Pembayaran</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="relative cursor-pointer">
                                <input type="radio" v-model="selectedPaymentMethod" value="cash" class="peer sr-only" />
                                <div
                                    class="bg-white/5 border-2 border-white/10 peer-checked:border-green-500 peer-checked:bg-green-500/10 rounded-xl p-4 transition-all hover:bg-white/10">
                                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-400 peer-checked:text-green-400"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <p class="text-center font-semibold">
                                        Tunai
                                    </p>
                                </div>
                            </label>
                            <label class="relative cursor-pointer">
                                <input type="radio" v-model="selectedPaymentMethod" value="debit"
                                    class="peer sr-only" />
                                <div
                                    class="bg-white/5 border-2 border-white/10 peer-checked:border-blue-500 peer-checked:bg-blue-500/10 rounded-xl p-4 transition-all hover:bg-white/10">
                                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-400 peer-checked:text-blue-400"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                    <p class="text-center font-semibold">
                                        Debit
                                    </p>
                                </div>
                            </label>
                            <label class="relative cursor-pointer">
                                <input type="radio" v-model="selectedPaymentMethod" value="credit"
                                    class="peer sr-only" />
                                <div
                                    class="bg-white/5 border-2 border-white/10 peer-checked:border-purple-500 peer-checked:bg-purple-500/10 rounded-xl p-4 transition-all hover:bg-white/10">
                                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-400 peer-checked:text-purple-400"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                    <p class="text-center font-semibold">
                                        Kredit
                                    </p>
                                </div>
                            </label>
                            <label class="relative cursor-pointer">
                                <input type="radio" v-model="selectedPaymentMethod" value="transfer"
                                    class="peer sr-only" />
                                <div
                                    class="bg-white/5 border-2 border-white/10 peer-checked:border-yellow-500 peer-checked:bg-yellow-500/10 rounded-xl p-4 transition-all hover:bg-white/10">
                                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-400 peer-checked:text-yellow-400"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                    </svg>
                                    <p class="text-center font-semibold">
                                        Transfer
                                    </p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Amount Input -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Jumlah Bayar</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400">Rp</span>
                            <input type="number" v-model.number="form.amount_paid"
                                class="w-full pl-12 pr-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white text-lg font-semibold focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                :placeholder="calculateTotal.toString()" />
                        </div>

                        <!-- Quick Amount Buttons -->
                        <div class="grid grid-cols-4 gap-2 mt-3">
                            <button v-for="amount in [
                                50000, 100000, 200000, 500000,
                            ]" :key="amount" @click="
                                form.amount_paid = calculateTotal + amount
                                "
                                class="bg-white/5 hover:bg-white/10 border border-white/20 rounded-lg py-2 text-xs font-semibold transition-all">
                                +{{ (amount / 1000).toFixed(0) }}k
                            </button>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div class="bg-white/5 rounded-xl p-4 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-400">Total Belanja</span>
                            <span class="font-semibold">{{
                                formatCurrency(calculateTotal)
                                }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-400">Jumlah Bayar</span>
                            <span class="font-semibold">{{
                                formatCurrency(form.amount_paid || 0)
                                }}</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold border-t border-white/20 pt-2">
                            <span>Kembalian</span>
                            <span class="text-green-400">{{
                                formatCurrency(changeAmount)
                                }}</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3">
                        <button @click="showPaymentModal = false"
                            class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 rounded-xl transition-all">
                            Batal
                        </button>
                        <button @click="confirmPayment" :disabled="form.processing ||
                            form.amount_paid < calculateTotal
                            "
                            class="flex-1 bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 disabled:from-gray-600 disabled:to-gray-700 text-white font-bold py-3 rounded-xl transition-all disabled:cursor-not-allowed">
                            {{
                                form.processing ? "Memproses..." : "Konfirmasi"
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </Modal>

        <!-- Receipt Modal -->
        <Modal :show="showReceiptModal" @close="closeReceiptModal">
            <div class="bg-white rounded-3xl shadow-2xl max-w-md mx-auto text-gray-900 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-6 py-8 text-center">
                    <svg class="w-16 h-16 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h2 class="text-2xl font-bold mb-1">Transaksi Berhasil!</h2>
                    <p class="text-blue-100 text-sm">{{ receiptData?.date }}</p>
                </div>

                <div class="p-6">
                    <!-- Items -->
                    <div class="mb-4 max-h-60 overflow-y-auto">
                        <div v-for="item in receiptData?.items?.filter(
                            (i) => i.type === 'product'
                        )" :key="item.id" class="mb-3 pb-3 border-b border-gray-200">
                            <div class="flex justify-between items-start mb-1">
                                <span class="font-semibold flex-1">{{
                                    item.name
                                    }}</span>
                                <span class="font-bold ml-2">{{
                                    formatCurrency(
                                        item.final_price * item.quantity
                                    )
                                }}</span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>{{ item.quantity }} ×
                                    {{ formatCurrency(item.final_price) }}</span>
                                <span v-if="item.discount_amount > 0" class="text-green-600">
                                    Hemat
                                    {{
                                        formatCurrency(
                                            item.discount_amount * item.quantity
                                        )
                                    }}
                                </span>
                            </div>
                            <div v-if="item.promotion_name" class="text-xs text-purple-600 mt-1">
                                🎁 {{ item.promotion_name }}
                            </div>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div class="bg-gray-50 rounded-xl p-4 mb-4 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="font-semibold">{{
                                formatCurrency(receiptData?.subtotal)
                                }}</span>
                        </div>
                        <div v-if="receiptData?.discount > 0" class="flex justify-between text-sm text-green-600">
                            <span>Diskon</span>
                            <span class="font-semibold">-{{
                                formatCurrency(receiptData?.discount)
                            }}</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold border-t border-gray-300 pt-2">
                            <span>Total</span>
                            <span>{{
                                formatCurrency(receiptData?.total)
                                }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Bayar ({{
                                receiptData?.payment_method === "cash"
                                    ? "Tunai"
                                    : receiptData?.payment_method
                            }})</span>
                            <span class="font-semibold">{{
                                formatCurrency(receiptData?.amount_paid)
                                }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-green-600">
                            <span>Kembalian</span>
                            <span class="font-semibold">{{
                                formatCurrency(receiptData?.change)
                                }}</span>
                        </div>
                    </div>

                    <!-- Customer Info -->
                    <div v-if="receiptData?.customer" class="text-center text-sm text-gray-600 mb-4">
                        Pelanggan:
                        <span class="font-semibold">{{
                            receiptData.customer.name
                            }}</span>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3">
                        <button @click="printReceipt"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl transition-all flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            <span>Cetak</span>
                        </button>
                        <button @click="closeReceiptModal"
                            class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 rounded-xl transition-all">
                            Selesai
                        </button>
                    </div>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.3);
}

@media print {
    body * {
        visibility: hidden;
    }

    #receipt,
    #receipt * {
        visibility: visible;
    }

    #receipt {
        position: absolute;
        left: 0;
        top: 0;
    }
}
</style>
