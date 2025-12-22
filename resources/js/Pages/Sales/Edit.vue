<template>
    <AuthenticatedLayout>
        <Head title="Edit Penjualan" />

        <template #header>
            <div class="flex flex-col gap-2">
                <h2 class="font-semibold text-lg text-[#1f1f1f] leading-tight">Edit Penjualan</h2>
                <p class="text-xs text-[#555]">Perbarui data transaksi penjualan.</p>
            </div>
        </template>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <form @submit.prevent="updateSale" class="bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-sm p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Customer Selection -->
                    <div>
                        <label class="block text-xs font-semibold text-[#555] mb-2">Customer</label>
                        <select v-model="form.customer_id"
                            class="w-full border-[#9c9c9c] bg-white rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Walk-in Customer</option>
                            <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                                {{ customer.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.customer_id" class="mt-1 text-xs text-red-600">{{ form.errors.customer_id
                            }}</p>
                    </div>

                    <!-- Salesman Selection -->
                    <div>
                        <label class="block text-xs font-semibold text-[#555] mb-2">Salesman</label>
                        <select v-model="form.salesman_id"
                            class="w-full border-[#9c9c9c] bg-white rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Salesman</option>
                            <option v-for="salesman in salesmen" :key="salesman.id" :value="salesman.id">
                                {{ salesman.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.salesman_id" class="mt-1 text-xs text-red-600">{{ form.errors.salesman_id
                            }}</p>
                    </div>
                </div>

                <!-- Products Section -->
                <div class="mt-8">
                    <h3 class="text-sm font-semibold mb-4 text-[#1f1f1f]">Products</h3>
                    <div class="space-y-4">
                        <div v-for="(item, index) in form.items" :key="index"
                            class="flex flex-col lg:flex-row lg:items-center gap-3 p-4 border border-[#9c9c9c] bg-white rounded">
                            <div class="flex-1">
                                <select v-model="item.product_id" @change="updateProductPrice(index)"
                                    class="w-full border-[#9c9c9c] bg-white rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select Product</option>
                                    <option v-for="product in products" :key="product.id" :value="product.id">
                                        {{ product.name }} - {{ formatCurrency(product.selling_price) }}
                                    </option>
                                </select>
                            </div>
                            <div class="w-full lg:w-24">
                                <input v-model.number="item.quantity" @input="updateItemTotal(index)" type="number"
                                    min="1"
                                    class="w-full border-[#9c9c9c] bg-white rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Qty">
                            </div>
                            <div class="w-full lg:w-32">
                                <input v-model.number="item.price" @input="updateItemTotal(index)" type="number"
                                    step="0.01"
                                    class="w-full border-[#9c9c9c] bg-white rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Price">
                            </div>
                            <div class="w-full lg:w-32 text-right font-semibold text-[#1f1f1f]">
                                {{ formatCurrency(item.total || 0) }}
                            </div>
                            <button type="button" @click="removeItem(index)"
                                class="inline-flex items-center justify-center bg-[#e9e9e9] border border-[#9c9c9c] rounded px-3 py-2 text-xs font-semibold text-[#1f1f1f] hover:bg-white">
                                Hapus
                            </button>
                        </div>
                    </div>
                    <button type="button" @click="addItem"
                        class="mt-4 bg-[#e9e9e9] border border-[#9c9c9c] text-[#1f1f1f] px-4 py-2 rounded text-xs font-semibold hover:bg-white">
                        Tambah Produk
                    </button>
                </div>

                <!-- Totals Section -->
                <div class="mt-8 bg-white border border-[#9c9c9c] p-4 rounded">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-semibold text-[#1f1f1f]">Total:</span>
                        <span class="text-lg font-bold text-[#1f1f1f]">{{ formatCurrency(total) }}</span>
                    </div>
                </div>

                <!-- Payment Section -->
                <div class="mt-8">
                    <h3 class="text-sm font-semibold mb-4 text-[#1f1f1f]">Payment</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-semibold text-[#555] mb-2">Payment Method</label>
                            <select v-model="form.payment_method"
                                class="w-full border-[#9c9c9c] bg-white rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500">
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="transfer">Bank Transfer</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#555] mb-2">Amount Paid</label>
                            <input v-model.number="form.amount_paid" type="number" step="0.01"
                                class="w-full border-[#9c9c9c] bg-white rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-8 flex justify-end gap-3">
                    <Link :href="route('sales.index')"
                        class="bg-[#e9e9e9] border border-[#9c9c9c] text-[#1f1f1f] px-4 py-2 rounded text-xs font-semibold hover:bg-white">
                    Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing"
                        class="bg-[#e9e9e9] border border-[#9c9c9c] text-[#1f1f1f] px-4 py-2 rounded text-xs font-semibold hover:bg-white disabled:opacity-50">
                        Update Sale
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';

const props = defineProps({
    sale: Object,
    customers: Array,
    salesmen: Array,
    products: Array,
});

const form = useForm({
    customer_id: props.sale.customer_id || '',
    salesman_id: props.sale.salesman_id || '',
    items: props.sale.items || [{ product_id: '', quantity: 1, price: 0, total: 0 }],
    payment_method: props.sale.payment_method || 'cash',
    amount_paid: props.sale.amount_paid || 0,
});

const total = computed(() => {
    return form.items.reduce((sum, item) => sum + (item.total || 0), 0);
});

const addItem = () => {
    form.items.push({ product_id: '', quantity: 1, price: 0, total: 0 });
};

const removeItem = (index) => {
    form.items.splice(index, 1);
};

const updateProductPrice = (index) => {
    const productId = form.items[index].product_id;
    const product = props.products.find(p => p.id == productId);
    if (product) {
        form.items[index].price = product.selling_price;
        updateItemTotal(index);
    }
};

const updateItemTotal = (index) => {
    const item = form.items[index];
    item.total = item.quantity * item.price;
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(amount);
};

const updateSale = () => {
    form.put(route('sales.update', props.sale.id), {
        onSuccess: () => {
            // Handle success
        },
    });
};

onMounted(() => {
    form.items.forEach((item, index) => {
        updateItemTotal(index);
    });
});
</script>
