<template>
    <div class="p-6">

        <Head title="Create Purchase" />
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold mb-8">Create Purchase</h1>

            <form @submit.prevent="createPurchase" class="bg-white rounded-lg shadow p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Supplier Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Supplier</label>
                        <select v-model="form.supplier_id"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Supplier</option>
                            <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                                {{ supplier.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.supplier_id" class="mt-1 text-sm text-red-600">{{ form.errors.supplier_id
                            }}</p>
                    </div>

                    <!-- Purchase Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Purchase Date</label>
                        <input v-model="form.purchase_date" type="date"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <p v-if="form.errors.purchase_date" class="mt-1 text-sm text-red-600">{{
                            form.errors.purchase_date }}</p>
                    </div>
                </div>

                <!-- Products Section -->
                <div class="mt-8">
                    <h3 class="text-lg font-semibold mb-4">Products</h3>
                    <div class="space-y-4">
                        <div v-for="(item, index) in form.items" :key="index"
                            class="flex items-center space-x-4 p-4 border rounded-lg">
                            <div class="flex-1">
                                <select v-model="item.product_id" @change="updateProductInfo(index)"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select Product</option>
                                    <option v-for="product in products" :key="product.id" :value="product.id">
                                        {{ product.name }}
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
                                <input v-model.number="item.purchase_price" @input="updateItemTotal(index)"
                                    type="number" step="0.01"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Purchase Price">
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

                <!-- Notes -->
                <div class="mt-8">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <textarea v-model="form.notes" rows="3"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>

                <!-- Action Buttons -->
                <div class="mt-8 flex justify-end space-x-4">
                    <Link :href="route('purchases.index')"
                        class="bg-gray-500 hover:bg-gray-700 text-white px-4 py-2 rounded-md">
                    Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing"
                        class="bg-blue-500 hover:bg-blue-700 text-white px-4 py-2 rounded-md disabled:opacity-50">
                        Create Purchase
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
    suppliers: Array,
    products: Array,
});

const form = useForm({
    supplier_id: '',
    purchase_date: new Date().toISOString().split('T')[0],
    items: [{ product_id: '', quantity: 1, purchase_price: 0, total: 0 }],
    notes: '',
});

const total = computed(() => {
    return form.items.reduce((sum, item) => sum + (item.total || 0), 0);
});

const addItem = () => {
    form.items.push({ product_id: '', quantity: 1, purchase_price: 0, total: 0 });
};

const removeItem = (index) => {
    form.items.splice(index, 1);
};

const updateProductInfo = (index) => {
    const productId = form.items[index].product_id;
    const product = props.products.find(p => p.id == productId);
    if (product) {
        form.items[index].purchase_price = product.purchase_price || 0;
        updateItemTotal(index);
    }
};

const updateItemTotal = (index) => {
    const item = form.items[index];
    item.total = item.quantity * item.purchase_price;
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(amount);
};

const createPurchase = () => {
    form.post(route('purchases.store'), {
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
