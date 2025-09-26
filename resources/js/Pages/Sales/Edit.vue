<template>
    <div class="p-6">

        <Head title="Edit Sale" />
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold mb-8">Edit Sale</h1>

            <form @submit.prevent="updateSale" class="bg-white rounded-lg shadow p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Customer Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Customer</label>
                        <select v-model="form.customer_id"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Walk-in Customer</option>
                            <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                                {{ customer.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.customer_id" class="mt-1 text-sm text-red-600">{{ form.errors.customer_id
                            }}</p>
                    </div>

                    <!-- Salesman Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Salesman</label>
                        <select v-model="form.salesman_id"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Salesman</option>
                            <option v-for="salesman in salesmen" :key="salesman.id" :value="salesman.id">
                                {{ salesman.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.salesman_id" class="mt-1 text-sm text-red-600">{{ form.errors.salesman_id
                            }}</p>
                    </div>
                </div>

                <!-- Products Section -->
                <div class="mt-8">
                    <h3 class="text-lg font-semibold mb-4">Products</h3>
                    <div class="space-y-4">
                        <div v-for="(item, index) in form.items" :key="index"
                            class="flex items-center space-x-4 p-4 border rounded-lg">
                            <div class="flex-1">
                                <select v-model="item.product_id" @change="updateProductPrice(index)"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select Product</option>
                                    <option v-for="product in products" :key="product.id" :value="product.id">
                                        {{ product.name }} - {{ formatCurrency(product.selling_price) }}
                                    </option>
                                </select>
                            </div>
                            <div class="w-24">
                                <input v-model.number="item.quantity" @input="updateItemTotal(index)" type="number"
                                    min="1"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Qty">
                            </div>
                            <div class="w-32">
                                <input v-model.number="item.price" @input="updateItemTotal(index)" type="number"
                                    step="0.01"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Price">
                            </div>
                            <div class="w-32 text-right font-semibold">
                                {{ formatCurrency(item.total || 0) }}
                            </div>
                            <button type="button" @click="removeItem(index)" class="text-red-600 hover:text-red-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                    </path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <button type="button" @click="addItem"
                        class="mt-4 bg-blue-500 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                        Add Product
                    </button>
                </div>

                <!-- Totals Section -->
                <div class="mt-8 bg-gray-50 p-4 rounded-lg">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-semibold">Total:</span>
                        <span class="text-2xl font-bold text-blue-600">{{ formatCurrency(total) }}</span>
                    </div>
                </div>

                <!-- Payment Section -->
                <div class="mt-8">
                    <h3 class="text-lg font-semibold mb-4">Payment</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                            <select v-model="form.payment_method"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="transfer">Bank Transfer</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Amount Paid</label>
                            <input v-model.number="form.amount_paid" type="number" step="0.01"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-8 flex justify-end space-x-4">
                    <Link :href="route('sales.index')"
                        class="bg-gray-500 hover:bg-gray-700 text-white px-4 py-2 rounded-md">
                    Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing"
                        class="bg-blue-500 hover:bg-blue-700 text-white px-4 py-2 rounded-md disabled:opacity-50">
                        Update Sale
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

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
