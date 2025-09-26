<template>
    <div class="p-6">

        <Head title="Reports" />
        <div class="max-w-6xl mx-auto">
            <h1 class="text-3xl font-bold mb-8">Reports</h1>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Sales Report -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center mb-4">
                        <div class="bg-blue-100 p-3 rounded-full">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                </path>
                            </svg>
                        </div>
                        <h3 class="ml-3 text-lg font-semibold">Sales Report</h3>
                    </div>
                    <p class="text-gray-600 mb-4">View detailed sales reports with filters by date range.</p>
                    <Link :href="route('reports.sales')"
                        class="bg-blue-500 hover:bg-blue-700 text-white px-4 py-2 rounded-md inline-block">
                    View Sales Report
                    </Link>
                </div>

                <!-- Purchases Report -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center mb-4">
                        <div class="bg-green-100 p-3 rounded-full">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                        </div>
                        <h3 class="ml-3 text-lg font-semibold">Purchases Report</h3>
                    </div>
                    <p class="text-gray-600 mb-4">Analyze purchase transactions and supplier performance.</p>
                    <Link :href="route('reports.purchases')"
                        class="bg-green-500 hover:bg-green-700 text-white px-4 py-2 rounded-md inline-block">
                    View Purchases Report
                    </Link>
                </div>

                <!-- Stock Report -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center mb-4">
                        <div class="bg-yellow-100 p-3 rounded-full">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        <h3 class="ml-3 text-lg font-semibold">Stock Report</h3>
                    </div>
                    <p class="text-gray-600 mb-4">Monitor inventory levels and stock movements.</p>
                    <Link :href="route('reports.stock')"
                        class="bg-yellow-500 hover:bg-yellow-700 text-white px-4 py-2 rounded-md inline-block">
                    View Stock Report
                    </Link>
                </div>

                <!-- Stock Movements Report -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center mb-4">
                        <div class="bg-purple-100 p-3 rounded-full">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                            </svg>
                        </div>
                        <h3 class="ml-3 text-lg font-semibold">Stock Movements</h3>
                    </div>
                    <p class="text-gray-600 mb-4">Track all stock in/out movements and adjustments.</p>
                    <Link :href="route('reports.stock-movements')"
                        class="bg-purple-500 hover:bg-purple-700 text-white px-4 py-2 rounded-md inline-block">
                    View Movements
                    </Link>
                </div>

                <!-- Profit & Loss Report -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center mb-4">
                        <div class="bg-red-100 p-3 rounded-full">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                </path>
                            </svg>
                        </div>
                        <h3 class="ml-3 text-lg font-semibold">Profit & Loss</h3>
                    </div>
                    <p class="text-gray-600 mb-4">View profit and loss analysis for selected periods.</p>
                    <Link :href="route('reports.profit-loss')"
                        class="bg-red-500 hover:bg-red-700 text-white px-4 py-2 rounded-md inline-block">
                    View P&L Report
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
</script>
