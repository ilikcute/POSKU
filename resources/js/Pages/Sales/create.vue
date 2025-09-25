<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
    products: Array,
    customers: Array,
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

const addProductToCart = (product) => {
    const existing = cart.value.find(item => item.id === product.id);
    if (existing) {
        existing.quantity++;
    } else {
        cart.value.push({ ...product, quantity: 1 });
    }
    form.items = cart.value.map(item => ({
        product_id: item.id,
        quantity: item.quantity,
        price: item.selling_price,
    }));
};

const removeFromCart = (id) => {
    cart.value = cart.value.filter(item => item.id !== id);
    form.items = cart.value.map(item => ({
        product_id: item.id,
        quantity: item.quantity,
        price: item.selling_price,
    }));
};

const updateQuantity = (id, quantity) => {
    const item = cart.value.find(item => item.id === id);
    if (item) {
        item.quantity = Math.max(1, parseInt(quantity));
    }
    form.items = cart.value.map(item => ({
        product_id: item.id,
        quantity: item.quantity,
        price: item.selling_price,
    }));
};

const calculateTotal = computed(() => {
    return cart.value.reduce((total, item) => total + (item.selling_price * item.quantity), 0);
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};

const submitSale = () => {
    form.customer_id = selectedCustomer.value?.id || null;
    form.amount_paid = form.amount_paid || calculateTotal.value;
    form.post(route('sales.store'), {
        onSuccess: () => {
            cart.value = [];
            selectedCustomer.value = null;
            form.reset();
        },
        onError: (errors) => {
            console.error('Form submission errors:', errors);
        },
    });
};
</script>

<template>

    <Head title="Transaksi Penjualan (POS)" />

    <div class="flex h-screen bg-gray-200 font-sans">
        <!-- Kolom Kiri: Produk -->
        <div class="w-3/5 p-4 flex flex-col">
            <div class="mb-4">
                <input v-model="searchTerm" type="text" placeholder="Cari produk atau scan barcode..."
                    class="w-full p-3 border rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="flex-grow bg-white p-4 rounded-lg shadow-inner overflow-y-auto">
                <div v-if="props.products?.length === 0" class="text-center text-gray-500 py-8">
                    <p>Tidak ada produk tersedia. <a :href="route('master.products.index')"
                            class="text-indigo-600">Kelola Produk</a></p>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <div v-for="product in props.products?.filter(p =>
                        p.name.toLowerCase().includes(searchTerm.toLowerCase()) || p.barcode?.includes(searchTerm)
                    )" :key="product.id" @click="addProductToCart(product)"
                        class="border rounded-lg p-4 text-center cursor-pointer hover:bg-indigo-100 hover:shadow-lg transition-all">
                        <div class="text-xs text-gray-500 mb-1">{{ product.barcode || 'No Barcode' }}</div>
                        <h3 class="font-semibold">{{ product.name }}</h3>
                        <p class="text-sm text-gray-600">{{ formatCurrency(product.selling_price) }}</p>
                        <p class="text-xs text-green-600">Stok: {{ product.stocks?.[0]?.quantity ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Keranjang & Pembayaran -->
        <div class="w-2/5 bg-white p-4 flex flex-col shadow-lg">
            <div class="mb-4">
                <select v-model="selectedCustomer" class="w-full p-3 border rounded-lg">
                    <option :value="null">Pelanggan Umum</option>
                    <option v-for="customer in props.customers" :key="customer.id" :value="customer">
                        {{ customer.name }} ({{ customer.phone || 'No Phone' }})
                    </option>
                </select>
            </div>
            <div class="flex-grow overflow-y-auto border-t border-b py-4">
                <div v-if="cart.length === 0" class="text-center text-gray-500 mt-10">
                    <p>Keranjang kosong. Pilih produk di kiri.</p>
                </div>
                <div v-for="item in cart" :key="item.id"
                    class="flex items-center justify-between mb-3 p-2 bg-gray-50 rounded">
                    <div class="flex-1">
                        <p class="font-semibold">{{ item.name }}</p>
                        <p class="text-sm text-gray-500">{{ formatCurrency(item.selling_price) }}</p>
                    </div>
                    <div class="flex items-center">
                        <button @click="removeFromCart(item.id)" class="text-red-500 mr-2">-</button>
                        <input type="number" v-model.number="item.quantity"
                            @input="updateQuantity(item.id, $event.target.value)"
                            class="w-16 text-center border rounded-md mx-2" min="1">
                        <button @click="updateQuantity(item.id, item.quantity + 1)"
                            class="text-green-500 ml-2">+</button>
                        <p class="w-24 font-semibold text-right ml-4">{{ formatCurrency(item.selling_price *
                            item.quantity) }}</p>
                    </div>
                </div>
            </div>
            <div class="mt-4 space-y-2">
                <div class="flex justify-between text-lg">
                    <span>Total Belanja</span>
                    <span>{{ formatCurrency(calculateTotal) }}</span>
                </div>
                <div class="flex justify-between">
                    <label>Bayar:</label>
                    <input type="number" v-model="form.amount_paid"
                        @input="form.amount_paid = Math.max(0, $event.target.value)"
                        class="w-32 text-right border rounded p-1" placeholder="0">
                </div>
                <div class="flex justify-between text-green-600 font-bold">
                    <span>Kembali:</span>
                    <span>{{ formatCurrency(Math.max(0, form.amount_paid - calculateTotal)) }}</span>
                </div>
                <textarea v-model="form.notes" placeholder="Catatan..." class="w-full p-2 border rounded"
                    rows="2"></textarea>
                <button @click="submitSale" :disabled="form.processing || cart.length === 0"
                    class="w-full bg-indigo-600 text-white font-bold py-3 rounded-lg text-lg hover:bg-indigo-700 disabled:opacity-50 transition-all">
                    {{ form.processing ? 'Memproses...' : 'BAYAR' }}
                </button>
            </div>
        </div>
    </div>
</template>
