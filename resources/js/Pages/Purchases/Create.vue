<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    products: Array,
    suppliers: Array,
    customers: Array,
    activeShift: Object,
    invoice_number: String,
    current_user: Object,
    current_date: String,
});

const form = useForm({
    items: [],
    supplier_id: null,
    payment_method: 'cash',
    payment_term_days: null,
    amount_paid: 0,
    notes: '',
});

const cart = ref([]);
const selectedSupplier = ref(null);
const barcodeInput = ref('');
const formNotice = ref({ show: false, message: '', type: 'error' });
const selectedProductId = ref(null);
const inputQty = ref(1);
const inputPrice = ref(0);
const inputTax = ref(0);
const inputDiscount = ref(0);
const showReceiptModal = ref(false);
const receiptData = ref(null);

const currentTime = ref('');
const currentDate = ref('');
const currentShift = ref('');
const cashierName = ref('');

const isCredit = computed(() => form.payment_method === 'credit');

const formattedDate = computed(() => {
    const raw = props.current_date || new Date().toISOString().slice(0, 10);
    return new Date(raw).toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
});

const dueDate = computed(() => {
    if (!isCredit.value || !form.payment_term_days) return '-';
    const base = new Date(props.current_date || new Date().toISOString().slice(0, 10));
    base.setDate(base.getDate() + Number(form.payment_term_days));
    return base.toLocaleDateString('id-ID');
});

const filteredProducts = computed(() => {
    const products = props.products || [];
    if (!selectedSupplier.value?.id) {
        return products;
    }
    return products.filter(
        (product) =>
            Number(product.supplier_id) === Number(selectedSupplier.value.id)
    );
});

const addProductToCart = (product, overrides = {}) => {
    const basePrice =
        overrides.price ?? product.purchase_price ?? product.selling_price ?? 0;
    const defaultTaxPerItem =
        product.tax_type === 'Y'
            ? (basePrice * (product.tax_rate || 0)) / 100
            : 0;
    const existing = cart.value.find((item) => item.id === product.id);
    if (existing) {
        if (overrides.replace) {
            existing.quantity = overrides.quantity ?? 1;
            existing.price = overrides.price ?? existing.price;
            existing.tax_per_item = overrides.tax_per_item ?? existing.tax_per_item;
            existing.discount_per_item = overrides.discount_per_item ?? existing.discount_per_item;
        } else {
            existing.quantity += overrides.quantity ?? 1;
        }
    } else {
        cart.value.push({
            ...product,
            quantity: overrides.quantity ?? 1,
            price: basePrice,
            tax_per_item: overrides.tax_per_item ?? defaultTaxPerItem,
            discount_per_item: overrides.discount_per_item ?? 0,
        });
    }
    updateFormItems();
};

const showNotice = (message, type = 'error') => {
    formNotice.value = { show: true, message, type };
    setTimeout(() => {
        formNotice.value.show = false;
    }, 3000);
};

const canAddProduct = (product) => {
    if (!selectedSupplier.value) {
        showNotice('Pilih supplier terlebih dahulu sebelum menambahkan barang.');
        return false;
    }
    if (
        product?.supplier_id &&
        selectedSupplier.value?.id &&
        Number(product.supplier_id) !== Number(selectedSupplier.value.id)
    ) {
        showNotice('Barang ini bukan milik supplier yang dipilih.');
        return false;
    }
    return true;
};

const removeFromCart = (id) => {
    cart.value = cart.value.filter((item) => item.id !== id);
    updateFormItems();
};

const updateQuantity = (id, quantity) => {
    const item = cart.value.find((item) => item.id === id);
    if (!item) return;
    item.quantity = Math.max(1, parseInt(quantity || 1, 10));
    updateFormItems();
};

const updatePrice = (id, price) => {
    const item = cart.value.find((item) => item.id === id);
    if (!item) return;
    const nextPrice = Number.isNaN(parseFloat(price)) ? 0 : parseFloat(price);
    item.price = Math.max(0, nextPrice);
    updateFormItems();
};

const updateTax = (id, tax) => {
    const item = cart.value.find((item) => item.id === id);
    if (!item) return;
    const nextTax = Number.isNaN(parseFloat(tax)) ? 0 : parseFloat(tax);
    item.tax_per_item = Math.max(0, nextTax);
    updateFormItems();
};

const updateDiscount = (id, discount) => {
    const item = cart.value.find((item) => item.id === id);
    if (!item) return;
    const nextDiscount = Number.isNaN(parseFloat(discount)) ? 0 : parseFloat(discount);
    item.discount_per_item = Math.max(0, nextDiscount);
    updateFormItems();
};

const updateFormItems = () => {
    form.items = cart.value.map((item) => ({
        product_id: item.id,
        quantity: item.quantity,
        price: item.price,
        tax_per_item: item.tax_per_item,
        discount_per_item: item.discount_per_item,
    }));
};

const subtotalAmount = computed(() =>
    cart.value.reduce((total, item) => total + item.price * item.quantity, 0)
);

const totalTax = computed(() =>
    cart.value.reduce((total, item) => total + (item.tax_per_item || 0) * item.quantity, 0)
);

const totalDiscount = computed(() =>
    cart.value.reduce((total, item) => total + (item.discount_per_item || 0) * item.quantity, 0)
);

const grossTotal = computed(() => subtotalAmount.value - totalDiscount.value + totalTax.value);

const changeAmount = computed(() => Math.max(0, form.amount_paid - grossTotal.value));
const amountPaidTouched = ref(false);

const lineGross = (item) => {
    const price = Number(item.price || 0);
    const tax = Number(item.tax_per_item || 0);
    const discount = Number(item.discount_per_item || 0);
    const qty = Number(item.quantity || 0);
    return (price + tax - discount) * qty;
};

const formatCurrency = (value) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value || 0);

const scanBarcode = () => {
    const barcode = barcodeInput.value.trim();
    if (!barcode) return;
    const product = props.products?.find(
        (p) => p.barcode === barcode || p.product_code === barcode
    );
    if (product && canAddProduct(product)) {
        const basePrice = product.purchase_price ?? product.selling_price ?? 0;
        const taxPerItem =
            product.tax_type === 'Y'
                ? (basePrice * (product.tax_rate || 0)) / 100
                : 0;
        addProductToCart(product, {
            quantity: inputQty.value,
            price: basePrice,
            tax_per_item: taxPerItem,
            discount_per_item: 0,
            replace: true,
        });
    }
    barcodeInput.value = '';
};

const addSelectedProduct = () => {
    if (selectedProductId.value === null || selectedProductId.value === undefined || selectedProductId.value === '') {
        return;
    }
    const product = props.products?.find(
        (p) => String(p.id) === String(selectedProductId.value)
    );
    if (!product) return;
    if (!canAddProduct(product)) return;
    addProductToCart(product, {
        quantity: inputQty.value,
        price: inputPrice.value,
        tax_per_item: inputTax.value,
        discount_per_item: inputDiscount.value,
        replace: true,
    });
    selectedProductId.value = null;
    inputQty.value = 1;
    inputPrice.value = 0;
    inputTax.value = 0;
    inputDiscount.value = 0;
};

const submitPurchase = (printAfterSave) => {
    form.supplier_id = selectedSupplier.value?.id || null;
    form.post(route('purchases.store'), {
        onSuccess: () => {
            if (printAfterSave) {
                receiptData.value = {
                    items: cart.value,
                    total: grossTotal.value,
                    subtotal: subtotalAmount.value,
                    tax: totalTax.value,
                    discount: totalDiscount.value,
                    amount_paid: form.amount_paid,
                    change: changeAmount.value,
                    payment_method: form.payment_method,
                    supplier: selectedSupplier.value,
                    date: new Date().toLocaleString('id-ID'),
                };
                showReceiptModal.value = true;
                setTimeout(() => window.print(), 200);
            }
            cart.value = [];
            selectedSupplier.value = null;
            form.reset();
        },
    });
};

const updateDateTime = () => {
    const now = new Date();
    currentTime.value = now.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });
    currentDate.value = now.toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });

    if (props.activeShift) {
        currentShift.value = props.activeShift.shift_code;
        cashierName.value = props.activeShift.name;
    } else {
        cashierName.value = props.current_user?.name || 'Petugas';
    }
};

watch(
    () => form.payment_method,
    (value) => {
        amountPaidTouched.value = false;
        if (value === 'credit') {
            form.amount_paid = 0;
            return;
        }
        form.amount_paid = grossTotal.value;
    }
);

watch(grossTotal, (value) => {
    if (!isCredit.value && !amountPaidTouched.value) {
        form.amount_paid = value;
    }
});

const onAmountPaidInput = (event) => {
    amountPaidTouched.value = true;
    const nextValue = Number(event.target.value || 0);
    form.amount_paid = Math.max(0, nextValue);
};

watch(selectedProductId, (value) => {
    const product = props.products?.find((p) => p.id === Number(value));
    if (!product) return;
    inputPrice.value = product.purchase_price ?? product.selling_price ?? 0;
    inputTax.value =
        product.tax_type === 'Y'
            ? (inputPrice.value * (product.tax_rate || 0)) / 100
            : 0;
    inputQty.value = 1;
    inputDiscount.value = 0;
});

onMounted(() => {
    updateDateTime();
    setInterval(updateDateTime, 1000);
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Transaksi Pembelian" />

        <template #header>
            <div class="flex flex-col gap-2">
                <h2 class="font-semibold text-lg text-[#1f1f1f] leading-tight">
                    Proses Penerimaan Barang
                </h2>
                <p class="text-xs text-[#555]">
                    Input pembelian harian berdasarkan supplier dan detail barang.
                </p>
            </div>
        </template>

        <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-sm p-5 text-[#1f1f1f] font-['Tahoma']">
                <div class="grid gap-3 lg:grid-cols-3">
                    <div class="space-y-1">
                        <div class="text-[11px] uppercase tracking-wide text-[#555]">No Transaksi / Nota</div>
                        <div class="bg-white border border-[#9c9c9c] rounded px-2 py-1 text-sm">
                            {{ props.invoice_number || '-' }}
                        </div>
                    </div>
                    <div class="space-y-1">
                        <div class="text-[11px] uppercase tracking-wide text-[#555]">Tanggal</div>
                        <div class="bg-white border border-[#9c9c9c] rounded px-2 py-1 text-sm">
                            {{ formattedDate }}
                        </div>
                    </div>
                    <div class="space-y-1">
                        <div class="text-[11px] uppercase tracking-wide text-[#555]">User</div>
                        <div class="bg-white border border-[#9c9c9c] rounded px-2 py-1 text-sm">
                            {{ props.current_user?.name || cashierName }}
                        </div>
                    </div>
                </div>

                <div class="mt-4 grid gap-3 lg:grid-cols-3">
                    <div>
                        <label class="block text-[11px] uppercase tracking-wide mb-1 text-[#555]">Supplier</label>
                        <select v-model="selectedSupplier"
                            class="w-full bg-white border border-[#9c9c9c] rounded px-2 py-1 text-sm text-[#1f1f1f]">
                            <option :value="null">Pilih Supplier</option>
                            <option v-for="supplier in props.suppliers" :key="supplier.id" :value="supplier">
                                {{ supplier.name }}
                            </option>
                        </select>
                        <div v-if="formNotice.show" class="mt-2 text-[11px] text-red-700">
                            {{ formNotice.message }}
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] uppercase tracking-wide mb-1 text-[#555]">Metode Pembayaran</label>
                        <select v-model="form.payment_method"
                            class="w-full bg-white border border-[#9c9c9c] rounded px-2 py-1 text-sm text-[#1f1f1f]">
                            <option value="cash">Tunai</option>
                            <option value="card">Kartu</option>
                            <option value="transfer">Transfer</option>
                            <option value="credit">Kredit</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] uppercase tracking-wide mb-1 text-[#555]">Tempo (Hari)</label>
                        <input v-model.number="form.payment_term_days" type="number" min="1"
                            :disabled="!isCredit"
                            class="w-full bg-white border border-[#9c9c9c] rounded px-2 py-1 text-sm text-[#1f1f1f] disabled:opacity-50" />
                        <div class="text-[11px] text-[#555] mt-1">Jatuh tempo: {{ dueDate }}</div>
                    </div>
                </div>

                <div class="mt-4 grid gap-3 lg:grid-cols-4">
                    <div>
                        <label class="block text-[11px] uppercase tracking-wide mb-1 text-[#555]">Scan Barcode / PLU</label>
                        <input v-model="barcodeInput" @keydown.enter.prevent="scanBarcode" type="text"
                            class="w-full bg-white border border-[#9c9c9c] rounded px-2 py-1 text-sm text-[#1f1f1f]"
                            placeholder="Scan barcode" />
                    </div>
                    <div>
                        <label class="block text-[11px] uppercase tracking-wide mb-1 text-[#555]">Pilih Barang</label>
                        <select v-model="selectedProductId"
                            class="w-full bg-white border border-[#9c9c9c] rounded px-2 py-1 text-sm text-[#1f1f1f]">
            <option :value="null">Pilih Produk</option>
            <option v-for="product in filteredProducts" :key="product.id" :value="product.id">
                {{ product.product_code ? `${product.product_code} - ${product.name}` : product.name }}
            </option>
        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] uppercase tracking-wide mb-1 text-[#555]">Qty</label>
                        <input v-model.number="inputQty" type="number" min="1"
                            class="w-full bg-white border border-[#9c9c9c] rounded px-2 py-1 text-sm text-[#1f1f1f]" />
                    </div>
                    <div class="flex items-end">
                        <button type="button" @click="addSelectedProduct"
                            class="w-full bg-[#e9e9e9] hover:bg-white text-[#1f1f1f] text-sm font-semibold py-2 rounded border border-[#9c9c9c]">
                            Tambah
                        </button>
                    </div>
                </div>

                <div class="mt-4 overflow-x-auto border border-[#9c9c9c] bg-[#f7f7f7]">
                    <table class="min-w-full text-[12px] text-[#1f1f1f]">
                        <thead class="text-[11px] uppercase tracking-wide text-[#555] border-b border-[#9c9c9c] bg-[#efefef]">
                            <tr>
                                <th class="py-2 px-2 text-left">No</th>
                                <th class="py-2 px-2 text-left">Kode</th>
                                <th class="py-2 px-2 text-left">Nama / Deskripsi</th>
                                <th class="py-2 px-2 text-right">Qty</th>
                                <th class="py-2 px-2 text-right">Price</th>
                                <th class="py-2 px-2 text-right">PPN</th>
                                <th class="py-2 px-2 text-right">Diskon</th>
                                <th class="py-2 px-2 text-right">Gross</th>
                                <th class="py-2 px-2 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="cart.length === 0">
                                <td colspan="9" class="py-6 text-center text-[#555]">
                                    Belum ada item pembelian.
                                </td>
                            </tr>
                            <tr v-for="(item, index) in cart" :key="item.id" class="border-b border-[#d0d0d0]">
                                <td class="py-2 px-2">{{ index + 1 }}</td>
                                <td class="py-2 px-2">{{ item.product_code || item.barcode || '-' }}</td>
                                <td class="py-2 px-2">{{ item.name }}</td>
                                <td class="py-2 px-2 text-right">
                                    <input v-model.number="item.quantity" type="number" min="1"
                                        @input="updateQuantity(item.id, item.quantity)"
                                        class="w-20 bg-white border border-[#9c9c9c] rounded px-2 py-1 text-right text-[#1f1f1f]" />
                                </td>
                                <td class="py-2 px-2 text-right">
                                    <input type="number" min="0" :value="item.price"
                                        @input="updatePrice(item.id, $event.target.value)"
                                        class="w-24 bg-white border border-[#9c9c9c] rounded px-2 py-1 text-right text-[#1f1f1f]" />
                                </td>
                                <td class="py-2 px-2 text-right">
                                    <input type="number" min="0" :value="item.tax_per_item"
                                        @input="updateTax(item.id, $event.target.value)"
                                        class="w-20 bg-white border border-[#9c9c9c] rounded px-2 py-1 text-right text-[#1f1f1f]" />
                                </td>
                                <td class="py-2 px-2 text-right">
                                    <input type="number" min="0" :value="item.discount_per_item"
                                        @input="updateDiscount(item.id, $event.target.value)"
                                        class="w-20 bg-white border border-[#9c9c9c] rounded px-2 py-1 text-right text-[#1f1f1f]" />
                                </td>
                                <td class="py-2 px-2 text-right">{{ formatCurrency(lineGross(item)) }}</td>
                                <td class="py-2 px-2 text-center">
                                    <button @click="removeFromCart(item.id)" class="text-red-700 hover:text-red-800">
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 grid gap-4 lg:grid-cols-3">
                    <div class="lg:col-span-2">
                        <label class="block text-[11px] uppercase tracking-wide mb-1 text-[#555]">Catatan</label>
                        <textarea v-model="form.notes" rows="2"
                            class="w-full bg-white border border-[#9c9c9c] rounded px-2 py-1 text-sm text-[#1f1f1f]"
                            placeholder="Catatan tambahan"></textarea>
                    </div>
                    <div class="space-y-2 bg-white border border-[#9c9c9c] rounded p-3">
                        <div class="flex justify-between text-sm text-[#555]">
                            <span>Subtotal</span>
                            <span>{{ formatCurrency(subtotalAmount) }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-[#555]">
                            <span>Total PPN</span>
                            <span>{{ formatCurrency(totalTax) }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-[#555]">
                            <span>Total Diskon</span>
                            <span>{{ formatCurrency(totalDiscount) }}</span>
                        </div>
                        <div class="flex justify-between text-base font-semibold text-[#1f1f1f]">
                            <span>Gross Total</span>
                            <span>{{ formatCurrency(grossTotal) }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-[#555]">
                            <span>Bayar</span>
                            <input :value="form.amount_paid" type="number" min="0" @input="onAmountPaidInput"
                                class="w-32 bg-white border border-[#9c9c9c] rounded px-2 py-1 text-right text-[#1f1f1f]" />
                        </div>
                        <div class="flex justify-between text-sm text-[#555]">
                            <span>Kembalian</span>
                            <span>{{ formatCurrency(changeAmount) }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap gap-3 justify-end">
                    <Link :href="route('purchases.index')"
                        class="px-4 py-2 rounded border border-[#9c9c9c] text-[#1f1f1f] bg-[#e9e9e9] hover:bg-white">
                        Cancel
                    </Link>
                    <button @click="submitPurchase(false)" :disabled="cart.length === 0 || !selectedSupplier"
                        class="px-4 py-2 rounded border border-[#9c9c9c] bg-[#e9e9e9] hover:bg-white text-[#1f1f1f] disabled:opacity-50">
                        Simpan
                    </button>
                    <button @click="submitPurchase(true)" :disabled="cart.length === 0 || !selectedSupplier"
                        class="px-4 py-2 rounded border border-[#9c9c9c] bg-[#e9e9e9] hover:bg-white text-[#1f1f1f] disabled:opacity-50">
                        Cetak
                    </button>
                </div>
            </div>
        </div>

        <Modal :show="showReceiptModal" @close="showReceiptModal = false">
            <div class="p-6 bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-sm max-w-sm mx-auto text-[#1f1f1f]" id="receipt">
                <div class="text-center mb-6">
                    <h2 class="text-lg font-bold">STRUK PEMBELIAN</h2>
                    <p class="text-xs text-[#555]">{{ receiptData?.date }}</p>
                </div>

                <div class="border-t border-b border-[#9c9c9c] py-4 mb-4">
                    <div v-for="item in receiptData?.items" :key="item.id" class="text-sm mb-2">
                        <div class="flex justify-between">
                            <span>{{ item.name }} x{{ item.quantity }}</span>
                            <span>{{ formatCurrency(lineGross(item)) }}</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-1 text-sm mb-4 text-[#1f1f1f]">
                    <div class="flex justify-between">
                        <span>Subtotal:</span>
                        <span>{{ formatCurrency(receiptData?.subtotal) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>PPN:</span>
                        <span>{{ formatCurrency(receiptData?.tax) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Diskon:</span>
                        <span>{{ formatCurrency(receiptData?.discount) }}</span>
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

                <div class="text-center text-xs text-[#555] mb-4">
                    <p>Metode: {{ receiptData?.payment_method === 'cash' ? 'Tunai' : receiptData?.payment_method ===
                        'card' ? 'Kartu' : receiptData?.payment_method === 'credit' ? 'Kredit' : 'Transfer' }}</p>
                    <p v-if="receiptData?.supplier">Supplier: {{ receiptData.supplier.name }}</p>
                </div>

                <div class="flex gap-3">
                    <button @click="() => window.print()"
                        class="flex-1 bg-[#e9e9e9] text-[#1f1f1f] font-bold py-2 rounded border border-[#9c9c9c] hover:bg-white transition-all">
                        Cetak
                    </button>
                    <button @click="showReceiptModal = false"
                        class="flex-1 bg-[#e9e9e9] text-[#1f1f1f] font-bold py-2 rounded border border-[#9c9c9c] hover:bg-white transition-all">
                        Tutup
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
