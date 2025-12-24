<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import { ref, computed, onMounted, nextTick, watch } from "vue";
import RawLayout from "@/Layouts/RawLayout.vue";
import Modal from "@/Components/Modal.vue";

const props = defineProps({
    products: Array,
    customers: Array,
    activeShift: Object,
    promotions: Array,
    store: Object, // Added store prop
    shift_id: [Number, String],
    station_id: [Number, String],
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
const receiptFooter = ref("Terima kasih atas kunjungan Anda.");

const barcodeInputRef = ref(null);
const searchInputRef = ref(null);
const paymentInputRef = ref(null);


// Computed values
const receiptLogo = computed(() => {
    if (receiptData.value?.store_logo) return receiptData.value.store_logo;
    if (props.store?.logo_path) return `/storage/${props.store.logo_path}`;
    return null;
});

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


const getItemBasePrice = (item) =>
    Number(item.original_price ?? item.selling_price ?? item.price ?? 0);

const getItemNetPrice = (item) =>
    Number(item.selling_price ?? item.price ?? 0);

const getItemTaxPerItem = (item) => {
    if (item.tax_type !== "Y") return 0;
    const rate = Number(item.tax_rate || 0);
    return getItemNetPrice(item) * (rate / 100);
};

const calculateSubtotal = computed(() => {
    return cart.value
        .filter((item) => item.type === "product")
        .reduce((total, item) => total + getItemBasePrice(item) * item.quantity, 0);
});

const calculateDiscount = computed(() => {
    return cart.value
        .filter((item) => item.type === "product")
        .reduce(
            (total, item) =>
                total +
                Math.max(0, getItemBasePrice(item) - getItemNetPrice(item)) *
                    item.quantity,
            0
        );
});

const netSubtotal = computed(() =>
    Math.max(0, calculateSubtotal.value - calculateDiscount.value)
);

const calculateTotal = computed(() =>
    Math.max(0, netSubtotal.value + calculateTax.value)
);

const calculateTax = computed(() => {
    return cart.value
        .filter((item) => item.type === "product")
        .reduce(
            (total, item) => total + getItemTaxPerItem(item) * item.quantity,
            0
        );
});

const calculateGrossSubtotal = computed(() => {
    return cart.value
        .filter((item) => item.type === "product")
        .reduce(
            (total, item) => total + getItemUnitPrice(item) * item.quantity,
            0
        );
});

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

const roundReceiptValue = (value) => {
    const numeric = Number(value || 0);
    const base = Math.floor(numeric);
    const decimals = Math.round((numeric - base) * 100);
    if (decimals > 50) {
        return base + 1;
    }
    return base;
};

const formatNumber = (value) => {
    return new Intl.NumberFormat("id-ID", {
        minimumFractionDigits: 0,
    }).format(value || 0);
};

const formatReceiptNumber = (value) => formatNumber(roundReceiptValue(value));

const formatReceiptDate = (date) => {
    const value = date ? new Date(date) : new Date();
    const dd = String(value.getDate()).padStart(2, "0");
    const mm = String(value.getMonth() + 1).padStart(2, "0");
    const yy = String(value.getFullYear()).slice(-2);
    const hh = String(value.getHours()).padStart(2, "0");
    const mi = String(value.getMinutes()).padStart(2, "0");
    return `${dd}.${mm}.${yy}-${hh}:${mi}`;
};

const formatInvoiceShort = (value) => {
    if (!value) return "-";
    return String(value).replace(/^INV-?/i, "");
};

const receiptPaperSize = computed(() => props.store?.receipt_paper_size || "58");
const receiptWidth = computed(() => (receiptPaperSize.value === "80" ? "80mm" : "58mm"));
const receiptWidthChars = computed(() => (receiptPaperSize.value === "80" ? 48 : 42));
const repeatChar = (char, count) => new Array(count + 1).join(char);
const padRight = (value, length) => {
    const text = String(value ?? "");
    return text.length >= length ? text.slice(0, length) : text + repeatChar(" ", length - text.length);
};
const padLeft = (value, length) => {
    const text = String(value ?? "");
    return text.length >= length ? text.slice(0, length) : repeatChar(" ", length - text.length) + text;
};

const formatPlainNumber = (value) => String(roundReceiptValue(value) || 0);

const formatMetaLine = (data) => {
    const date = data?.date || formatReceiptDate(new Date());
    const station = data?.station_id || "-";
    const shift = data?.shift_code || "-";
    const cashier = data?.cashier || "-";
    const invoice = formatInvoiceShort(data?.invoice_number || "-");
    const line = `${date}/ST:${station}/SF:${shift}/${cashier}/${invoice}`;
    const width = receiptWidthChars.value;
    return line.length > width ? line.slice(0, width) : line;
};

const formatItemLine = (item) => {
    const name = padRight(item.name || "-", 24);
    const qty = padLeft(item.quantity || 0, 3);
    const price = padLeft(formatReceiptNumber(getItemUnitPrice(item)), 7);
    const total = padLeft(formatReceiptNumber(getItemGross(item)), 8);
    return `${name}${qty}${price}${total}`;
};

const formatRightLine = (label, value) => {
    const val = String(value ?? "");
    const space = receiptWidthChars.value - label.length - val.length;
    const gap = space > 0 ? repeatChar(" ", space) : " ";
    return `${label}${gap}${val}`;
};


const getItemUnitPrice = (item) => {
    if (item.final_price !== undefined && item.final_price !== null) {
        return Number(item.final_price || 0);
    }
    return getItemNetPrice(item) + getItemTaxPerItem(item);
};

const getItemDiscount = (item) => {
    if (item.discount_amount !== undefined) {
        return Number(item.discount_amount || 0);
    }
    return Math.max(0, getItemBasePrice(item) - getItemNetPrice(item));
};

const getItemGross = (item) => {
    return getItemUnitPrice(item) * Number(item.quantity || 0);
};

const showNotification = (message, type = "info") => {
    notification.value = { show: true, message, type };
    setTimeout(() => {
        notification.value.show = false;
    }, 3000);
};

const getPriceWithPromotion = (product, quantity = 1) => {
    // Logic copied from previous efficient implementation
    const basePrice = Number(product.selling_price || 0);
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
        original_price: Number(basePrice || 0),
        final_price: Number(finalPrice || 0),
        discount_amount: Number(discountAmount || 0),
        bundled_products: bundledProducts,
    };
};

const autoPrint = ref(true);
const ensurePrintStyle = () => {
    const styleId = "receipt-print-style";
    let styleEl = document.getElementById(styleId);
    if (!styleEl) {
        styleEl = document.createElement("style");
        styleEl.id = styleId;
        document.head.appendChild(styleEl);
    }
    styleEl.textContent = `@page { size: ${receiptWidth.value} auto; margin: 0; }`;
};
const pendingSales = ref([]);
const showPendingModal = ref(false);
const pendingKey = "posku.sales.pending";

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
        const sourceProduct = (props.products || []).find(
            (item) => item.id === product.id
        );
        const pricing = getPriceWithPromotion(sourceProduct || product, newQty);
        const netPrice = Number(pricing.final_price || 0);
        const taxPerItem =
            (existing.tax_type ?? "N") === "Y"
                ? netPrice * (Number(existing.tax_rate || 0) / 100)
                : 0;

        existing.quantity = newQty;
        existing.selling_price = netPrice;
        existing.final_price = netPrice + taxPerItem;
        existing.discount_amount = pricing.discount_amount;

        lastAddedItem.value = existing;
    } else {
        const pricing = getPriceWithPromotion(product, qtyToAdd);
        const netPrice = Number(pricing.final_price || 0);
        const taxPerItem =
            (product.tax_type ?? "N") === "Y"
                ? netPrice * (Number(product.tax_rate || 0) / 100)
                : 0;
        const finalPrice = Number(netPrice + taxPerItem);

        const newItem = {
            ...product,
            quantity: qtyToAdd,
            type: "product",
            original_price: pricing.original_price,
            selling_price: netPrice,
            final_price: finalPrice,
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
        onSuccess: (page) => {
            const invoiceNumber = page?.props?.flash?.invoice_number || "-";
            receiptData.value = {
                store_name: props.store?.name || "POSKU STORE",
                store_address: props.store?.address || "-",
                store_phone: props.store?.phone || "-",
                store_logo: props.store?.logo_path ? `/storage/${props.store.logo_path}` : null,
                station_id: props.station_id || "-",
                shift_code: props.activeShift?.shift_code || "-",
                cashier: cashierName.value || props.auth?.user?.name || "Kasir",
                invoice_number: invoiceNumber,
                subtotal: calculateGrossSubtotal.value,
                dpp_subtotal: netSubtotal.value,
                harga_jual: calculateSubtotal.value,
                total: calculateTotal.value,
                tax: calculateTax.value,
                discount: calculateDiscount.value,
                amount_paid: form.amount_paid,
                change: changeAmount.value,
                payment_method: "cash",
                date: formatReceiptDate(new Date()),
                footer: receiptFooter.value,
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
    } else if (e.key === "F4") {
        e.preventDefault();
        savePendingSale();
    } else if (e.key === "F6") {
        e.preventDefault();
        showPendingModal.value = true;
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
    loadPendingSales();
    ensurePrintStyle();
});

watch(receiptWidth, () => {
    ensurePrintStyle();
});

const loadPendingSales = () => {
    try {
        const raw = localStorage.getItem(pendingKey);
        pendingSales.value = raw ? JSON.parse(raw) : [];
    } catch (error) {
        pendingSales.value = [];
    }
};

const persistPendingSales = () => {
    localStorage.setItem(pendingKey, JSON.stringify(pendingSales.value));
};

const clearCurrentSale = () => {
    cart.value = [];
    selectedCustomer.value = null;
    form.reset();
    updateFormItems();
    barcodeInput.value = "";
    qtyInput.value = 1;
    nextTick(() => {
        barcodeInputRef.value?.focus();
    });
};

const savePendingSale = () => {
    if (!cart.value.length) {
        showNotification("Tidak ada item untuk pending.", "warning");
        return;
    }
    const pendingItem = {
        id: Date.now(),
        created_at: new Date().toISOString(),
        items: JSON.parse(JSON.stringify(cart.value)),
        customer: selectedCustomer.value ? { ...selectedCustomer.value } : null,
        notes: form.notes || "",
    };
    pendingSales.value.unshift(pendingItem);
    persistPendingSales();
    clearCurrentSale();
    showNotification("Transaksi disimpan ke pending.", "success");
};

const resumePendingSale = (pendingItem) => {
    cart.value = JSON.parse(JSON.stringify(pendingItem.items || []));
    selectedCustomer.value = pendingItem.customer || null;
    form.notes = pendingItem.notes || "";
    updateFormItems();
    pendingSales.value = pendingSales.value.filter((item) => item.id !== pendingItem.id);
    persistPendingSales();
    showPendingModal.value = false;
    showNotification("Transaksi pending dipulihkan.", "success");
    nextTick(() => {
        barcodeInputRef.value?.focus();
    });
};

const deletePendingSale = (pendingItem) => {
    pendingSales.value = pendingSales.value.filter((item) => item.id !== pendingItem.id);
    persistPendingSales();
};

const voidSale = () => {
    if (!cart.value.length) {
        showNotification("Keranjang sudah kosong.", "warning");
        return;
    }
    if (confirm("Batalkan transaksi ini?")) {
        clearCurrentSale();
        showNotification("Transaksi dibatalkan.", "success");
    }
};
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
                        <div class="flex justify-between"><span>[F4]</span> <span>Pending</span></div>
                        <div class="flex justify-between"><span>[F6]</span> <span>Panggil Pending</span></div>
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
                                            formatCurrency(getItemUnitPrice(item)) }}</td>
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
                        <div class="grid grid-cols-2 gap-2">
                            <button @click="savePendingSale"
                                class="bg-gray-700 hover:bg-gray-800 text-white font-bold py-3 rounded text-sm flex items-center justify-center gap-2 transition-all active:scale-[0.98]">
                                <span>PENDING</span>
                                <span class="bg-black/30 px-2 py-0.5 rounded text-xs">[F4]</span>
                            </button>
                            <button @click="showPendingModal = true"
                                class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-3 rounded text-sm flex items-center justify-center gap-2 transition-all active:scale-[0.98]">
                                <span>PANGGIL</span>
                                <span class="bg-black/30 px-2 py-0.5 rounded text-xs">[F6]</span>
                            </button>
                        </div>
                        <button @click="voidSale"
                            class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded text-sm flex items-center justify-center gap-2 transition-all active:scale-[0.98]">
                            VOID
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
                                        formatCurrency(p.final_price ?? p.selling_price)
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

                            <div class="mb-4">
                                <label class="block text-xs font-bold text-gray-700 mb-2 uppercase">Ekor Struk</label>
                                <textarea v-model="receiptFooter" rows="2"
                                    class="w-full border border-gray-300 rounded p-2 text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Tulis informasi tambahan struk..."></textarea>
                            </div>

                            <button @click="confirmPayment"
                                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-4 rounded-lg text-lg shadow-lg transform active:scale-[0.98] transition-all">
                                PROSES SELESAI [ENTER]
                            </button>
                        </div>
                    </div>
                </Modal>

                <!-- Pending List Modal -->
                <Modal :show="showPendingModal" @close="showPendingModal = false">
                    <div class="bg-white text-gray-900 shadow-2xl max-w-2xl mx-auto overflow-hidden rounded-lg border border-gray-200">
                        <div class="bg-gray-800 text-white px-5 py-4 font-bold flex justify-between items-center">
                            <span>DAFTAR PENDING</span>
                            <button @click="showPendingModal = false"
                                class="text-xs bg-gray-900 hover:bg-black py-1.5 px-3 rounded text-gray-100 transition-colors">[ESC]
                                TUTUP</button>
                        </div>
                        <div class="p-4">
                            <table class="w-full text-sm border border-gray-200">
                                <thead class="bg-gray-100 text-gray-600 text-xs uppercase">
                                    <tr>
                                        <th class="p-2 text-left">Waktu</th>
                                        <th class="p-2 text-left">Pelanggan</th>
                                        <th class="p-2 text-right">Item</th>
                                        <th class="p-2 text-left">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in pendingSales" :key="item.id" class="border-t border-gray-200">
                                        <td class="p-2">{{ new Date(item.created_at).toLocaleString('id-ID') }}</td>
                                        <td class="p-2">{{ item.customer?.name || 'Umum' }}</td>
                                        <td class="p-2 text-right">{{ item.items?.length || 0 }}</td>
                                        <td class="p-2">
                                            <div class="flex gap-2">
                                                <button @click="resumePendingSale(item)"
                                                    class="px-3 py-1 bg-emerald-600 text-white text-xs font-bold rounded hover:bg-emerald-700">
                                                    Lanjutkan
                                                </button>
                                                <button @click="deletePendingSale(item)"
                                                    class="px-3 py-1 bg-red-600 text-white text-xs font-bold rounded hover:bg-red-700">
                                                    Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="!pendingSales.length">
                                        <td colspan="4" class="p-4 text-center text-gray-500">
                                            Tidak ada transaksi pending.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
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
            <Teleport to="body">
                <!-- Receipt Container with dynamic width -->
                <div id="receipt" class="hidden print:block bg-white text-black font-mono leading-tight text-left"
                    :style="{
                        width: receiptWidth,
                        padding: '4mm',
                        fontFamily: 'Courier New, monospace',
                        fontSize: '9pt'
                    }">

                    <!-- Header -->
                    <div class="mb-2 text-center" style="font-size: 9pt;">
                        <div v-if="receiptLogo" class="text-center mb-2">
                            <img :src="receiptLogo" alt="Logo"
                                class="block mx-auto h-10 w-auto object-contain"
                                style="border-radius: 0;" />
                        </div>
                        <h2 class="font-bold uppercase tracking-wide mb-1" style="font-size: 9pt;">
                            {{ receiptData?.store_name || 'POSKU STORE' }}
                        </h2>
                        <p class="mb-0.5">{{ receiptData?.store_address || '-' }}</p>
                        <p v-if="receiptData?.store_phone && receiptData?.store_phone !== '-'">Telp: {{
                            receiptData.store_phone }}</p>
                    </div>

                    <!-- Meta Info -->
                    <pre class="receipt-line text-center">{{ repeatChar("-", receiptWidthChars) }}</pre>
                    <pre class="receipt-line">{{ formatMetaLine(receiptData) }}</pre>
                    <pre class="receipt-line text-center">{{ repeatChar("-", receiptWidthChars) }}</pre>

                    <!-- Items -->
                    <div class="space-y-1 mb-2">
                        <div v-for="item in receiptData?.items" :key="item.id">
                            <pre class="receipt-line">{{ formatItemLine(item) }}</pre>
                            <pre v-if="getItemDiscount(item) > 0" class="receipt-line text-right italic">{{
                                formatRightLine("DISKON :", `(${formatReceiptNumber(getItemDiscount(item) * item.quantity)})`)
                            }}</pre>
                        </div>
                    </div>

                    <!-- Totals -->
                    <pre class="receipt-line text-center">{{ repeatChar("-", receiptWidthChars) }}</pre>
                    <pre class="receipt-line text-right">SUBTOTAL : {{ formatReceiptNumber(receiptData?.subtotal) }}</pre>
                    <pre class="receipt-line text-right">DISKON : {{ formatReceiptNumber(receiptData?.discount || 0) }}</pre>
                    <pre class="receipt-line text-right font-bold">TOTAL BELANJA : {{ formatReceiptNumber(receiptData?.total) }}</pre>
                    <pre class="receipt-line text-right">{{ receiptData?.payment_method?.toUpperCase() || "TUNAI" }} : {{ formatReceiptNumber(receiptData?.amount_paid) }}</pre>
                    <pre class="receipt-line text-right">ANDA HEMAT : {{ formatReceiptNumber(receiptData?.discount || 0) }}</pre>
                    <pre class="receipt-line text-left">PPN : DPP={{ formatReceiptNumber(receiptData?.dpp_subtotal || 0) }} PPN={{ formatReceiptNumber(receiptData?.tax || 0) }}</pre>
                    <pre class="receipt-line text-left">HARGA JUAL : {{ formatReceiptNumber(receiptData?.harga_jual || 0) }}</pre>
                    <pre class="receipt-line text-center">{{ repeatChar("-", receiptWidthChars) }}</pre>

                    <!-- Footer -->
                    <div class="mt-4 space-y-1 text-center">
                        <p class="font-bold">TERIMA KASIH</p>
                        <p>Barang yang sudah dibeli tidak dapat ditukar/dikembalikan</p>
                        <p class="mt-2 text-[10px] uppercase">-- {{ receiptData?.payment_method || 'CASH' }} --</p>
                        <p v-if="receiptData?.footer" class="mt-2 text-[10px] whitespace-pre-line">
                            {{ receiptData.footer }}
                        </p>
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
        margin: 0 !important;
        padding: 0 !important;
    }
}

.receipt-line {
    margin: 0;
    white-space: pre;
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
