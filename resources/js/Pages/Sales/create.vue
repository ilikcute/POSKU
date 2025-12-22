<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import { ref, computed, onMounted, nextTick } from "vue";
import RawLayout from "@/Layouts/RawLayout.vue";
import Modal from "@/Components/Modal.vue";

const props = defineProps({
    products: Array,
    customers: Array,
    activeShift: Object,
    promotions: Array,
    store: Object, // Added store prop
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
const showSearchModal = ref(false); // New modal for search
const receiptData = ref(null);
const currentTime = ref("");
const currentDate = ref("");
const currentShift = ref("");
const cashierName = ref("");
const barcodeInput = ref("");
const qtyInput = ref(1); // Default Quantity
const notification = ref({ show: false, message: "", type: "info" });
const lastAddedItem = ref(null);
const showLastItemToast = ref(false);

const barcodeInputRef = ref(null);
const searchInputRef = ref(null);
const paymentInputRef = ref(null);


// Computed values
const filteredProducts = computed(() => {
    if (!searchTerm.value) return [];

    const search = searchTerm.value.toLowerCase();
    // Return all matches
    return (props.products || []).filter(
        (p) =>
            p.name.toLowerCase().includes(search) ||
            p.barcode?.includes(search) ||
            p.product_code?.toLowerCase().includes(search)
    ).slice(0, 50); // Warning: limit search results for perf
});


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
    // Logic copied from previous efficient implementation
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

const autoPrint = ref(true);
const receiptWidth = ref("80mm"); // "58mm" or "80mm"

// Cart operations
const addProductToCart = (product) => {
    console.log("Adding product to cart:", product);
    if (!props.activeShift) {
        console.error("Shift not open");
        alert("SHIFT BELUM DIBUKA! Harap kembali ke Dashboard/Buka Shift.");
        showNotification("Shift belum dibuka!", "error");
        return;
    }

    const stockQuantity = product.stocks?.[0]?.quantity ?? 0;
    const qtyToAdd = parseInt(qtyInput.value) || 1;

    if (stockQuantity < qtyToAdd) {
        showNotification(`Stok ${product.name} tidak cukup`, "error");
        return;
    }

    const existing = cart.value.find(
        (item) => item.id === product.id && item.type === "product"
    );

    let currentQty = existing ? existing.quantity : 0;
    let newQty = currentQty + qtyToAdd;

    if (newQty > stockQuantity) {
        showNotification(`Stok maksimal: ${stockQuantity}`, "warning");
        return;
    }

    if (existing) {
        existing.quantity = newQty;
        if (existing.active_promotion) {
            const pricing = getPriceWithPromotion(existing, existing.quantity);
            existing.final_price = pricing.final_price;
            existing.discount_amount = pricing.discount_amount;
        }

        lastAddedItem.value = existing;
    } else {
        const pricing = getPriceWithPromotion(product, qtyToAdd);

        const newItem = {
            ...product,
            quantity: qtyToAdd,
            type: "product",
            original_price: pricing.original_price,
            final_price: pricing.final_price,
            discount_amount: pricing.discount_amount,
            promotion_name: product.active_promotion?.name || null,
            bundled_products: pricing.bundled_products,
        };

        cart.value.push(newItem);

        pricing.bundled_products.forEach((bundled) => {
            cart.value.push({
                ...bundled,
                type: "bundled",
                bundled_from: product.id,
            });
        });

        lastAddedItem.value = newItem;
    }

    // Reset inputs
    qtyInput.value = 1;
    barcodeInput.value = "";

    nextTick(() => {
        barcodeInputRef.value?.focus();
    });

    // Show toast for last item
    showLastItemToast.value = true;
    setTimeout(() => showLastItemToast.value = false, 2000);

    updateFormItems();
};

const removeFromCart = (id) => {
    cart.value = cart.value.filter(
        (item) => item.id !== id && item.bundled_from !== id
    );
    updateFormItems();
    barcodeInputRef.value?.focus();
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
        showNotification("Keranjang kosong", "warning");
        return;
    }

    form.amount_paid = 0; // Reset or smart default
    showPaymentModal.value = true;

    nextTick(() => {
        paymentInputRef.value?.focus();
    });
};

const confirmPayment = () => {
    if (form.amount_paid < calculateTotal.value) {
        showNotification("Pembayaran kurang!", "error");
        return;
    }

    form.payment_method = "cash";
    form.customer_id = selectedCustomer.value?.id || null;

    form.post(route("sales.store"), {
        onSuccess: (response) => {
            receiptData.value = {
                // ... populate receipt data similar to before ...
                total: calculateTotal.value,
                amount_paid: form.amount_paid,
                change: changeAmount.value,
                payment_method: "cash",
                date: new Date().toLocaleString("id-ID"),
                items: JSON.parse(JSON.stringify(cart.value)) // Snapshot
            };

            showPaymentModal.value = false;
            showReceiptModal.value = true;

            if (autoPrint.value) {
                setTimeout(() => {
                    window.print();
                    closeReceiptModal(); // Auto-close after print dialog finishes
                }, 500);
            }

            cart.value = [];
            selectedCustomer.value = null;
            form.reset();
        },
        onError: () => {
            showNotification("Gagal memproses transaksi", "error");
        },
    });
};

const printReceipt = () => {
    window.print();
};

const closeReceiptModal = () => {
    showReceiptModal.value = false;
    receiptData.value = null;
    barcodeInputRef.value?.focus();
};

// Barcode scanner logic
const scanBarcode = () => {
    const code = barcodeInput.value?.trim();
    if (!code) return;

    console.log("Searching for:", code);

    const product = (props.products || []).find((p) => {
        const barcodeMatch = p.barcode && String(p.barcode).toLowerCase() === String(code).toLowerCase();
        const codeMatch = p.product_code && String(p.product_code).toLowerCase() === String(code).toLowerCase();
        return barcodeMatch || codeMatch;
    });

    if (product) {
        addProductToCart(product);
        showNotification("Item ditambahkan", "success");
    } else {
        console.warn("Product not found");
        showNotification(`Barang tidak ditemukan: ${code}`, "error");
        barcodeInput.value = "";
    }

    // Ensure focus remains on input
    nextTick(() => {
        barcodeInputRef.value?.focus();
    });
};

const selectProductFromSearch = (product) => {
    addProductToCart(product);
    showSearchModal.value = false;
    searchTerm.value = "";
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
        weekday: "short",
        year: "numeric",
        month: "short",
        day: "numeric",
    });

    if (props.activeShift) {
        currentShift.value = props.activeShift.shift_code;
        cashierName.value = props.activeShift.name || props.activeShift.user?.name || "Kasir";
    }
};

const goBack = () => {
    window.history.back();
};

// Keyboard shortcuts
const handleKeyPress = (e) => {
    if (e.key === "F1") {
        e.preventDefault();
        showSearchModal.value = true;
        nextTick(() => searchInputRef.value?.focus());
    } else if (e.key === "F2") {
        e.preventDefault();
        openPaymentModal();
    } else if (e.key === "Escape") {
        if (showSearchModal.value) showSearchModal.value = false;
        else if (showPaymentModal.value) showPaymentModal.value = false;
        else if (showReceiptModal.value) showReceiptModal.value = false;
        else barcodeInputRef.value?.focus();
    }
};

// Lifecycle hooks
onMounted(() => {
    updateDateTime();
    setInterval(updateDateTime, 1000);
    window.addEventListener("keydown", handleKeyPress);
    barcodeInputRef.value?.focus();
});
</script>

<template>
    <RawLayout>
        <div class="flex flex-col h-screen overflow-hidden bg-gray-200">

            <Head title="Point of Sale" />

            <!-- Top Header Info Bar -->
            <div
                class="bg-blue-900 text-white p-2 text-sm font-mono border-b border-white/20 flex justify-between items-center select-none shadow-md z-10 relative shrink-0 h-16">

                <!-- Left: Logo & Station -->
                    <div class="flex items-center gap-4">
                        <button type="button" @click="goBack"
                            class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded font-bold transition flex items-center gap-2"
                            title="Keluar / Kembali ke halaman sebelumnya">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            EXIT
                        </button>
                    <div class="flex flex-col">
                        <span class="font-bold text-lg leading-tight tracking-wider">POS SYSTEM v1.0</span>
                        <span class="text-xs text-blue-200 uppercase">{{ store?.name || 'STORE NAME' }} - STATION
                            01</span>
                    </div>
                </div>

                <!-- Center: Clock -->
                <div class="flex flex-col items-center">
                    <span class="text-xl font-bold bg-black/30 px-4 py-1 rounded border border-white/10">{{
                        currentTime
                    }}</span>
                    <span class="text-xs text-blue-200">{{ currentDate }}</span>
                </div>

                <!-- Right: Status & Shift -->
                <div class="text-right flex flex-col items-end">
                    <div class="flex items-center gap-2">
                        <span class="bg-green-600 px-2 rounded text-xs font-bold">ONLINE</span>
                        <span class="font-bold">{{ cashierName }}</span>
                    </div>
                    <span class="text-xs text-blue-200">Shift: {{ currentShift || '-' }}</span>
                </div>
            </div>

            <!-- Main Workspace -->
            <div class="flex-1 flex overflow-hidden font-sans text-sm relative">

                <!-- LEFT PANEL: Promotions & Shortcuts -->
                <div class="w-64 bg-gray-100 flex flex-col border-r border-gray-300 shadow-inner hidden md:flex">
                    <!-- Promo Header -->
                    <div
                        class="bg-emerald-700 text-white px-3 py-2 font-bold text-xs uppercase tracking-wide flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                        </svg>
                        Info Promosi
                    </div>

                    <!-- Promo List -->
                    <div class="flex-1 overflow-y-auto p-2 space-y-2 bg-gray-200/50">
                        <div v-for="promo in promotions || []" :key="promo.id"
                            class="bg-white border border-gray-300 p-2 rounded shadow-sm text-xs">
                            <div class="font-bold text-emerald-700 mb-1 border-b border-gray-200 pb-1">{{ promo.name
                            }}
                            </div>
                            <p class="text-gray-600 leading-snug">{{ promo.description }}</p>
                            <div class="mt-1 text-[10px] text-gray-500 italic">Berlaku sampai: {{ new
                                Date(promo.end_date).toLocaleDateString() }}</div>
                        </div>
                        <div v-if="(!promotions || promotions.length === 0)"
                            class="text-center text-gray-400 italic py-4">
                            Tidak ada promosi aktif
                        </div>
                    </div>

                    <!-- Shortcuts Legend -->
                    <div
                        class="bg-gray-800 text-gray-300 p-3 text-[10px] space-y-1.5 font-mono border-t border-gray-600">
                        <div class="flex justify-between"><span>[F1]</span> <span>Cari Barang</span></div>
                        <div class="flex justify-between"><span>[F2]</span> <span>Bayar</span></div>
                        <!-- F4 Pending not implemented yet but shown as placeholder -->
                        <!-- <div class="flex justify-between"><span>[F4]</span> <span>Pending</span></div> -->
                        <div class="flex justify-between"><span>[ESC]</span> <span>Batal / Kembali</span></div>
                    </div>
                </div>

                <!-- CENTER PANEL: Transaction Table -->
                <div class="flex-1 flex flex-col bg-white overflow-hidden relative">
                    <!-- Main Input Area -->
                    <div class="p-4 bg-gray-100 border-b border-gray-300 shadow-sm z-10">
                        <div class="flex gap-2">
                            <div class="flex-1 relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                    </svg>
                                </div>
                                <input ref="barcodeInputRef" v-model="barcodeInput" @keydown.enter="scanBarcode"
                                    type="text"
                                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg font-bold font-mono tracking-wide shadow-inner"
                                    placeholder="Scan Barcode / Input Kode" autofocus>
                            </div>
                            <div class="w-24 flex items-center relative">
                                <span class="absolute left-2 text-xs text-gray-500 font-bold z-10">QTY</span>
                                <input v-model="qtyInput" type="number" min="1"
                                    class="block w-full pl-8 pr-2 py-3 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 font-bold text-lg text-center">
                            </div>
                        </div>
                    </div>

                    <!-- Table Header -->
                    <div
                        class="flex bg-gray-200 border-b border-gray-300 text-gray-700 font-bold text-xs uppercase px-1 py-2 select-none">
                        <div class="w-10 text-center">No.</div>
                        <div class="w-32 pl-2">Kode Item</div>
                        <div class="flex-1 pl-2">Deskripsi Item</div>
                        <div class="w-16 text-center">Qty</div>
                        <div class="w-24 text-right pr-2">Harga</div>
                        <div class="w-24 text-right pr-2">Diskon</div>
                        <div class="w-28 text-right pr-4">Total</div>
                        <div class="w-10 text-center">Act</div>
                    </div>

                    <!-- Table Body -->
                    <div class="flex-1 overflow-y-auto bg-white custom-scrollbar">
                        <table class="w-full text-sm border-collapse">
                            <tbody>
                                <template v-for="(item, index) in cart" :key="item.id + index">
                                    <tr class="border-b border-gray-100 last:border-0 hover:bg-blue-50 transition-colors cursor-default"
                                        :class="{ 'bg-gray-50/50': index % 2 === 0, 'bg-yellow-50': item.type === 'bundled' }">
                                        <td class="py-2 w-10 text-center text-gray-400 text-xs">{{ index + 1 }}</td>
                                        <td class="py-2 w-32 pl-2 font-mono text-xs text-gray-500 truncate">{{
                                            item.barcode
                                            || item.product_code || '-' }}</td>
                                        <td class="py-2 pl-2">
                                            <div class="font-semibold text-gray-800">{{ item.name }}</div>
                                            <div v-if="item.type === 'bundled'"
                                                class="text-[10px] text-blue-600 italic">
                                                Included in Bundle</div>
                                        </td>
                                        <td class="py-2 w-16 text-center">
                                            <span class="font-bold bg-gray-200 rounded px-1.5 py-0.5">{{
                                                item.quantity
                                            }}</span>
                                        </td>
                                        <td class="py-2 w-24 text-right pr-2 font-mono text-gray-600">{{
                                            formatCurrency(item.original_price) }}</td>
                                        <td class="py-2 w-24 text-right pr-2 font-mono text-red-500 text-xs">
                                            {{ item.discount_amount > 0 ? formatCurrency(item.discount_amount *
                                                item.quantity) : '-' }}
                                        </td>
                                        <td class="py-2 w-28 text-right pr-4 font-bold font-mono text-gray-900">
                                            {{ formatCurrency(item.final_price * item.quantity) }}
                                        </td>
                                        <td class="py-2 w-10 text-center">
                                            <button v-if="item.type === 'product'" @click="removeFromCart(item.id)"
                                                class="text-gray-400 hover:text-red-500 focus:outline-none p-1 rounded hover:bg-red-50 transition-colors"
                                                title="Hapus [Del]">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                <tr v-if="cart.length === 0">
                                    <td colspan="8" class="h-64 text-center text-gray-300 select-none">
                                        <div class="flex flex-col items-center justify-center h-full">
                                            <svg class="w-16 h-16 opacity-20 mb-2" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                            <span class="text-2xl font-bold opacity-20 tracking-widest">TRANSAKSI
                                                KOSONG</span>
                                            <span class="text-sm mt-1 opacity-50">Silakan scan produk atau tekan F1
                                                untuk
                                                cari</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Last Item Toast (Bottom Overlay) -->
                    <div v-if="lastAddedItem"
                        class="absolute bottom-4 left-4 right-4 bg-gray-900/90 text-white p-3 rounded-lg shadow-xl backdrop-blur flex justify-between items-center transition-all duration-300 transform"
                        :class="showLastItemToast ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0 pointer-events-none'">
                        <div class="flex flex-col">
                            <span class="text-[10px] text-gray-400 uppercase tracking-widest">Terakhir
                                Ditambahkan</span>
                            <span class="font-bold text-lg text-emerald-400 line-clamp-1">{{ lastAddedItem.name }}
                                (x{{
                                    lastAddedItem.quantity }})</span>
                        </div>
                        <span class="text-2xl font-bold font-mono">{{ formatCurrency(lastAddedItem.final_price *
                            lastAddedItem.quantity)
                        }}</span>
                    </div>
                </div>

                <!-- RIGHT PANEL: Summary & Big Totals -->
                <div class="w-80 bg-gray-100 border-l border-gray-300 flex flex-col shadow-xl z-20">
                    <!-- Big Total Display -->
                    <div
                        class="bg-gray-800 text-white p-5 text-right flex flex-col justify-end h-36 border-b-4 border-emerald-500 shadow-md">
                        <span class="text-xs text-gray-400 uppercase tracking-widest mb-1">Total Bayar</span>
                        <span class="text-4xl font-mono font-bold text-emerald-400 tracking-tighter leading-none">{{
                            formatCurrency(calculateTotal) }}</span>
                    </div>

                    <!-- Customer Select -->
                    <div class="p-4 bg-white border-b border-gray-200">
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Pelanggan</label>
                        <div class="relative">
                            <select v-model="selectedCustomer"
                                class="w-full text-sm border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500 bg-gray-50 py-2 pl-2 pr-8">
                                <option :value="null">-- Pelanggan Umum --</option>
                                <option v-for="c in customers" :key="c.id" :value="c">{{ c.name }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Summary Details -->
                    <div class="p-4 space-y-3 bg-gray-50 flex-1 overflow-y-auto">
                        <div
                            class="flex justify-between text-sm text-gray-600 border-b border-gray-200 pb-2 border-dashed">
                            <span>Total Item</span>
                            <span class="font-bold text-gray-800">{{cart.reduce((acc, item) => acc + (item.type ===
                                'product'
                                ?
                                item.quantity : 0), 0)}}</span>
                        </div>
                        <div
                            class="flex justify-between text-sm text-gray-600 border-b border-gray-200 pb-2 border-dashed">
                            <span>Subtotal</span>
                            <span class="font-mono text-gray-800">{{ formatCurrency(calculateSubtotal) }}</span>
                        </div>
                        <div
                            class="flex justify-between text-sm text-red-500 border-b border-gray-200 pb-2 border-dashed">
                            <span>Potongan</span>
                            <span class="font-mono">{{ formatCurrency(calculateDiscount) }}</span>
                        </div>

                        <div class="border border-blue-100 bg-blue-50 rounded p-2 text-center mt-4">
                            <span class="text-xs text-blue-500 font-bold block mb-1">SHIFT STATUS</span>
                            <span v-if="!activeShift"
                                class="text-xs text-red-500 bg-red-100 px-2 py-0.5 rounded">TUTUP</span>
                            <span v-else class="text-xs text-blue-800 font-mono font-bold">{{ activeShift.shift_code
                            }}</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="p-4 bg-white border-t border-gray-300 space-y-3">
                        <button @click="openPaymentModal"
                            class="w-full bg-blue-700 hover:bg-blue-800 text-white font-bold py-4 rounded shadow-lg text-lg flex items-center justify-center gap-2 transition-all active:scale-[0.98] outline-none focus:ring-4 focus:ring-blue-300"
                            :disabled="!cart.length || !activeShift"
                            :class="{ 'opacity-50 cursor-not-allowed grayscale': !cart.length || !activeShift }">
                            <span>BAYAR</span>
                            <span
                                class="bg-blue-900/50 px-2 py-0.5 rounded text-xs text-blue-100 border border-blue-500/30">[F2]</span>
                        </button>
                    </div>
                </div>

            </div>

            <!-- Hidden Search Functionality (triggered by keyboard or fallback if needed) -->
            <div v-if="showSearchModal"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
                @click.self="showSearchModal = false">
                <div
                    class="bg-white rounded-lg shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[80vh] animate-scale-in">
                    <div class="p-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                        <h3 class="font-bold text-lg text-gray-800 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Cari Produk
                        </h3>
                        <button @click="showSearchModal = false"
                            class="text-gray-400 hover:text-gray-700 font-bold px-2 py-1 rounded hover:bg-gray-200 transition-colors">ESC</button>
                    </div>
                    <div class="p-4 border-b border-gray-200 bg-white">
                        <input ref="searchInputRef" v-model="searchTerm" type="text"
                            class="w-full text-lg border-gray-300 rounded p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-inner"
                            placeholder="Ketik nama / kode barang..." autocomplete="off">
                    </div>
                    <div class="flex-1 overflow-y-auto p-0 bg-white">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-gray-500 border-b font-xs uppercase sticky top-0">
                                <tr>
                                    <th class="p-3 text-left w-32">Kode</th>
                                    <th class="p-3 text-left">Nama Produk</th>
                                    <th class="p-3 text-right w-20">Stok</th>
                                    <th class="p-3 text-right w-32">Harga</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="(p, idx) in filteredProducts" :key="p.id" @click="selectProductFromSearch(p)"
                                    class="hover:bg-blue-50 cursor-pointer transition-colors"
                                    :class="{ 'bg-red-50 hover:bg-red-100': p.stocks?.[0]?.quantity <= 0, 'bg-gray-50/30': idx % 2 !== 0 }">
                                    <td class="p-3 font-mono text-xs text-gray-500">{{ p.product_code || p.barcode
                                        || '-' }}
                                    </td>
                                    <td class="p-3 font-semibold text-gray-800">{{ p.name }}</td>
                                    <td class="p-3 text-right"
                                        :class="p.stocks?.[0]?.quantity <= 0 ? 'text-red-600 font-bold' : 'text-gray-600'">
                                        {{
                                            p.stocks?.[0]?.quantity || 0 }}</td>
                                    <td class="p-3 text-right font-mono text-emerald-600">{{
                                        formatCurrency(p.selling_price)
                                    }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <div v-if="filteredProducts.length === 0" class="p-8 text-center text-gray-400">
                            <p>Tidak ada produk ditemukan untuk "{{ searchTerm }}"</p>
                        </div>
                    </div>
                </div>
            </div>

            <Teleport to="body">
                <!-- Payment Modal -->
                <Modal :show="showPaymentModal" @close="showPaymentModal = false">
                    <div
                        class="bg-white text-gray-900 shadow-2xl max-w-lg mx-auto overflow-hidden rounded-lg font-sans border border-gray-200">
                        <div
                            class="bg-blue-800 text-white px-5 py-4 font-bold flex justify-between items-center text-lg">
                            <span>PEMBAYARAN</span>
                            <button @click="showPaymentModal = false"
                                class="text-xs bg-blue-900 hover:bg-blue-950 py-1.5 px-3 rounded text-blue-100 transition-colors">[ESC]
                                BATAL</button>
                        </div>
                        <div class="p-6">
                            <div class="flex justify-between items-end mb-6 border-b border-gray-200 pb-4">
                                <span class="text-gray-500 font-medium">Total Tagihan</span>
                                <span class="text-4xl font-bold font-mono text-gray-900 tracking-tight">{{
                                    formatCurrency(calculateTotal) }}</span>
                            </div>

                            <label class="block text-sm font-bold text-gray-700 mb-2 uppercase">Uang Tunai
                                (Cash)</label>
                            <div class="relative mb-6">
                                <span
                                    class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg font-bold">Rp</span>
                                <input ref="paymentInputRef" type="number" v-model="form.amount_paid"
                                    @keyup.enter="confirmPayment"
                                    class="w-full text-3xl font-bold font-mono pl-12 pr-4 py-3 border-2 border-blue-500 rounded-lg focus:outline-none focus:ring-4 focus:ring-blue-100 text-right shadow-inner"
                                    placeholder="0">
                            </div>

                            <!-- Quick Amount Buttons -->
                            <div class="grid grid-cols-4 gap-2 mb-6">
                                <button @click="form.amount_paid = calculateTotal; $refs.paymentInputRef.focus()"
                                    class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-2 rounded text-xs">PAS</button>
                                <button @click="form.amount_paid = 50000; $refs.paymentInputRef.focus()"
                                    class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-2 rounded text-xs">50k</button>
                                <button @click="form.amount_paid = 100000; $refs.paymentInputRef.focus()"
                                    class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-2 rounded text-xs">100k</button>
                                <button @click="form.amount_paid = 200000; $refs.paymentInputRef.focus()"
                                    class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-2 rounded text-xs">200k</button>
                            </div>

                            <div
                                class="flex justify-between items-center bg-gray-100 p-4 rounded-lg mb-6 border border-gray-200">
                                <span class="font-bold text-gray-600">KEMBALIAN</span>
                                <span class="font-bold text-2xl font-mono transition-colors"
                                    :class="changeAmount >= 0 ? 'text-blue-700' : 'text-red-500'">{{
                                        formatCurrency(Math.max(0,
                                            changeAmount)) }}</span>
                            </div>

                            <!-- Auto Print Checkbox -->
                            <div class="flex items-center mb-4">
                                <input id="autoPrint" type="checkbox" v-model="autoPrint"
                                    class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                <label for="autoPrint" class="ml-2 text-sm font-bold text-gray-700">Cetak Struk
                                    Otomatis
                                    (Langsung)</label>
                            </div>

                            <button @click="confirmPayment"
                                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-4 rounded-lg text-lg shadow-lg transform active:scale-[0.98] transition-all">
                                PROSES SELESAI [ENTER]
                            </button>
                        </div>
                    </div>
                </Modal>

                <!-- Receipt Info Modal -->
                <Modal :show="showReceiptModal" @close="closeReceiptModal">
                    <div class="bg-white p-8 text-center max-w-sm mx-auto rounded-xl">
                        <div
                            class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4 animate-bounce-short">
                            <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold mb-2 text-gray-800">Transaksi Berhasil!</h2>
                        <p class="text-gray-500 mb-6 text-sm">Pembayaran telah dikonfirmasi.</p>

                        <div class="bg-gray-50 border border-gray-100 rounded-lg p-4 mb-6">
                            <div class="flex justify-between text-sm mb-2">
                                <span class="text-gray-500">Total</span>
                                <span class="font-bold">{{ formatCurrency(receiptData?.total) }}</span>
                            </div>
                            <div class="flex justify-between text-sm mb-2">
                                <span class="text-gray-500">Bayar</span>
                                <span class="font-bold">{{ formatCurrency(receiptData?.amount_paid) }}</span>
                            </div>
                            <div class="border-t border-dashed border-gray-200 my-2"></div>
                            <div class="flex justify-between text-base">
                                <span class="text-gray-800 font-bold">Kembalian</span>
                                <span class="text-emerald-600 font-bold">{{ formatCurrency(receiptData?.change)
                                }}</span>
                            </div>
                        </div>

                        <div class="flex flex-col gap-3">
                            <button @click="printReceipt"
                                class="w-full bg-gray-900 hover:bg-black text-white py-3 rounded-lg font-bold shadow transition-colors flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                CETAK STRUK
                            </button>
                            <button @click="closeReceiptModal"
                                class="w-full bg-white border-2 border-gray-200 text-gray-700 py-3 rounded-lg font-bold hover:bg-gray-50 transition-colors">
                                TRANSAKSI BARU
                            </button>
                        </div>
                    </div>
                </Modal>
            </Teleport>

            <!-- Notification Toast -->
            <div v-if="notification.show" class="fixed top-16 right-4 z-50 animate-slide-in">
                <div class="bg-gray-800 text-white px-4 py-3 rounded shadow-xl flex items-center gap-3 border-l-4"
                    :class="notification.type === 'error' ? 'border-red-500' : 'border-emerald-500'">
                    <span v-if="notification.type === 'error'" class="text-red-400 font-bold">!</span>
                    <span v-else class="text-emerald-400 font-bold">✓</span>
                    <span class="font-medium text-sm">{{ notification.message }}</span>
                </div>
            </div>

            <!-- Hidden Receipt Template -->
            <!-- Hidden Receipt Template -->
            <Teleport to="body">
                <!-- Receipt Container with dynamic width -->
                <div id="receipt" class="hidden print:block bg-white text-black font-mono leading-tight"
                    :style="{ width: receiptWidth, padding: '5mm', fontSize: receiptWidth === '58mm' ? '10px' : '12px' }">

                    <!-- Header -->
                    <div class="text-center mb-2">
                        <h2 class="font-bold uppercase tracking-wide mb-1"
                            :class="receiptWidth === '58mm' ? 'text-sm' : 'text-base'">
                            {{ props.auth?.user?.store?.name || 'POSKU STORE' }}
                        </h2>
                        <p class="mb-0.5">{{ props.auth?.user?.store?.address || 'Jl. Contoh Alamat No. 123' }}</p>
                        <p>Telp: 0812-3456-7890</p>
                    </div>

                    <!-- Meta Info -->
                    <div class="border-b border-dashed border-black my-2"></div>
                    <div class="flex justify-between mb-1">
                        <span>{{ new Date().toLocaleString('id-ID', {
                            day: 'numeric', month: 'numeric', year: '2-digit',
                            hour:
                                '2-digit', minute: '2-digit'
                        }) }}</span>
                        <span>Kasir: {{ props.auth?.user?.name?.split(' ')[0] || 'Admin' }}</span>
                    </div>
                    <div class="flex justify-between mb-1">
                        <span>Inv: {{ receiptData?.invoice_number }}</span>
                    </div>
                    <div class="border-b border-dashed border-black my-2"></div>

                    <!-- Items -->
                    <div class="space-y-1 mb-2">
                        <div v-for="item in receiptData?.items" :key="item.id">
                            <!-- Format: Name on top, Qty x Price = Total on bottom (or same line if 80mm) -->
                            <div class="font-bold mb-0.5">{{ item.name }}</div>
                            <div class="flex justify-between pl-2">
                                <span>{{ item.quantity }} x {{ formatCurrency(item.final_price).replace('Rp', '')
                                }}</span>
                                <span>{{ formatCurrency(item.final_price * item.quantity).replace('Rp', '')
                                }}</span>
                            </div>
                            <div v-if="item.discount_amount > 0" class="text-xs text-right italic">
                                (Disc: -{{ formatCurrency(item.discount_amount * item.quantity).replace('Rp', '')
                                }})
                            </div>
                        </div>
                    </div>

                    <!-- Totals -->
                    <div class="border-top border-dashed border-black my-2 pt-2"></div>
                    <div class="flex justify-between font-bold mb-1">
                        <span>TOTAL</span>
                        <span>{{ formatCurrency(receiptData?.total) }}</span>
                    </div>
                    <div class="flex justify-between mb-1">
                        <span>TUNAI</span>
                        <span>{{ formatCurrency(receiptData?.amount_paid) }}</span>
                    </div>
                    <div class="flex justify-between mb-1">
                        <span>KEMBALI</span>
                        <span>{{ formatCurrency(receiptData?.change) }}</span>
                    </div>
                    <div class="border-b border-dashed border-black my-2"></div>

                    <!-- Footer -->
                    <div class="text-center mt-4 space-y-1">
                        <p class="font-bold">TERIMA KASIH</p>
                        <p>Barang yang sudah dibeli tidak dapat ditukar/dikembalikan</p>
                        <p class="mt-2 text-[10px] uppercase">-- {{ receiptData?.payment_method || 'CASH' }} --</p>
                    </div>
                </div>
            </Teleport>

        </div>
    </RawLayout>
</template>

<style>
/* Global Print Styles to override everything */
@media print {

    /* Hide everything by default */
    body>* {
        display: none !important;
    }

    /* Only show the receipt */
    #receipt,
    #receipt * {
        display: block !important;
        visibility: visible !important;
    }

    /* Ensure receipt layout */
    #receipt {
        display: block !important;
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }
}
</style>

<style scoped>
/* Ensure custom scrollbars for specific sections */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

@keyframes slide-in {
    0% {
        transform: translateX(100%);
        opacity: 0;
    }

    100% {
        transform: translateX(0);
        opacity: 1;
    }
}

.animate-slide-in {
    animation: slide-in 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes scale-in {
    0% {
        transform: scale(0.95);
        opacity: 0;
    }

    100% {
        transform: scale(1);
        opacity: 1;
    }
}

.animate-scale-in {
    animation: scale-in 0.2s ease-out;
}

@keyframes bounce-short {

    0%,
    100% {
        transform: translateY(0);
    }

    50% {
        transform: translateY(-10px);
    }
}

.animate-bounce-short {
    animation: bounce-short 0.5s ease-in-out;
}

/* Typography Tweaks for POS numeric readiness */
.font-mono {
    font-variant-numeric: tabular-nums;
    letter-spacing: -0.02em;
}

/* Hide number input spinners */
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type=number] {
    -moz-appearance: textfield;
}

/* Print Specific */
@media print {
    /* handled in global style above */
}
</style>
