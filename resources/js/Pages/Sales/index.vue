<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

// Ini adalah layout khusus untuk POS, bukan AuthenticatedLayout
// Untuk sementara kita buat sederhana, tanpa layout utama.

const props = defineProps({
    products: Array, // Nanti controller akan kirim data produk
    customers: Array, // Nanti controller akan kirim data customer
});

const cart = ref([]);
const selectedCustomer = ref(null);
const searchTerm = ref('');

// Fungsi placeholder, nanti akan diisi logika
const addProductToCart = (product) => {
    // Cek jika produk sudah ada di keranjang, tambah qty
    const existingProduct = cart.value.find(item => item.id === product.id);
    if (existingProduct) {
        existingProduct.quantity++;
    } else {
        cart.value.push({ ...product, quantity: 1 });
    }
};

const calculateTotal = () => {
    return cart.value.reduce((total, item) => total + (item.selling_price * item.quantity), 0);
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};

</script>

<template>
    <Head title="Transaksi Penjualan (POS)" />

    <div class="flex h-screen bg-gray-200 font-sans">
        <!-- Kolom Kiri: Produk -->
        <div class="w-3/5 p-4 flex flex-col">
            <div class="mb-4">
                <input v-model="searchTerm" type="text" placeholder="Cari produk berdasarkan nama atau scan barcode..." class="w-full p-3 border rounded-lg shadow-sm">
            </div>
            <div class="flex-grow bg-white p-4 rounded-lg shadow-inner overflow-y-auto">
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <!-- Contoh Produk Card (nanti akan diganti data asli) -->
                    <div v-for="i in 12" :key="i" @click="addProductToCart({id: i, name: 'Produk Contoh '+i, selling_price: 10000})" class="border rounded-lg p-4 text-center cursor-pointer hover:bg-indigo-100 hover:shadow-lg transition-all">
                        
                        <h3 class="font-semibold mt-2">Produk {{ i }}</h3>
                        <p class="text-sm text-gray-600">{{ formatCurrency(10000) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Keranjang & Pembayaran -->
        <div class="w-2/5 bg-white p-4 flex flex-col shadow-lg">
            <div class="mb-4">
                 <input type="text" placeholder="Cari Pelanggan (Umum)" class="w-full p-3 border rounded-lg">
            </div>
            <div class="flex-grow overflow-y-auto border-t border-b py-4">
                <div v-if="cart.length === 0" class="text-center text-gray-500 mt-10">
                    <p>Keranjang kosong</p>
                </div>
                 <!-- Item di keranjang -->
                <div v-for="item in cart" :key="item.id" class="flex items-center justify-between mb-3">
                    <div>
                        <p class="font-semibold">{{ item.name }}</p>
                        <p class="text-sm text-gray-500">{{ formatCurrency(item.selling_price) }}</p>
                    </div>
                    <div class="flex items-center">
                        <input type="number" v-model="item.quantity" class="w-16 text-center border rounded-md mx-2">
                        <p class="w-24 font-semibold text-right">{{ formatCurrency(item.selling_price * item.quantity) }}</p>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex justify-between font-bold text-xl mb-4">
                    <span>Total</span>
                    <span>{{ formatCurrency(calculateTotal()) }}</span>
                </div>
                <button class="w-full bg-indigo-600 text-white font-bold py-4 rounded-lg text-lg hover:bg-indigo-700 transition-all">
                    BAYAR
                </button>
            </div>
        </div>
    </div>
</template>
