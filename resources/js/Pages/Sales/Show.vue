<template>
    <div class="p-6">

        <Head title="Sale Details" />
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h1 class="text-2xl font-bold">Sale Details</h1>
                        <div class="space-x-2">
                            <Link :href="`/sales/${sale.id}/pdf`"
                                class="bg-blue-500 hover:bg-blue-700 text-white px-4 py-2 rounded">
                            Download PDF
                            </Link>
                            <Link :href="`/sales/${sale.id}/print`"
                                class="bg-green-500 hover:bg-green-700 text-white px-4 py-2 rounded">
                            Print
                            </Link>
                            <Link :href="route('sales.edit', sale.id)"
                                class="bg-yellow-500 hover:bg-yellow-700 text-white px-4 py-2 rounded">
                            Edit
                            </Link>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <h3 class="text-lg font-semibold mb-2">Sale Information</h3>
                            <div class="space-y-2">
                                <p><strong>Invoice:</strong> {{ sale.invoice_number }}</p>
                                <p><strong>Date:</strong> {{ sale.transaction_date }}</p>
                                <p><strong>Cashier:</strong> {{ sale.user.name }}</p>
                                <p><strong>Store:</strong> {{ sale.store.name }}</p>
                                <p v-if="sale.member"><strong>Customer:</strong> {{ sale.member.name }}</p>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold mb-2">Payment Information</h3>
                            <div class="space-y-2">
                                <p><strong>Payment Method:</strong> {{ sale.payment_method }}</p>
                                <p><strong>Total Amount:</strong> Rp {{ sale.total_amount.toLocaleString() }}</p>
                                <p v-if="sale.discount > 0"><strong>Discount:</strong> Rp {{
                                    sale.discount.toLocaleString() }}</p>
                                <p><strong>Tax:</strong> Rp {{ sale.tax.toLocaleString() }}</p>
                                <p><strong>Final Amount:</strong> Rp {{ sale.final_amount.toLocaleString() }}</p>
                                <p><strong>Amount Paid:</strong> Rp {{ sale.amount_paid.toLocaleString() }}</p>
                                <p><strong>Change Due:</strong> Rp {{ sale.change_due.toLocaleString() }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2">Items</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Product</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Qty</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Price</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="detail in sale.sale_details" :key="detail.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ detail.product.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ detail.quantity }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            Rp {{ detail.price_at_sale.toLocaleString() }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            Rp {{ detail.subtotal.toLocaleString() }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div v-if="sale.notes" class="mb-6">
                        <h3 class="text-lg font-semibold mb-2">Notes</h3>
                        <p class="text-gray-700">{{ sale.notes }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    sale: Object,
});
</script>
