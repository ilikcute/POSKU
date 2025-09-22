<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const user = usePage().props.auth.user;

// Placeholder data for dashboard stats
const stats = [
    { name: 'Total Penjualan Hari Ini', value: 'Rp 1.250.000', icon: 'cash' },
    { name: 'Total Transaksi', value: '15', icon: 'receipt' },
    { name: 'Produk Terlaris', value: 'Kopi Susu', icon: 'star' },
    { name: 'Stok Menipis', value: '5 Produk', icon: 'warning' },
];

const icons = {
    cash: `<svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>`,
    receipt: `<svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>`,
    star: `<svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.364 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.364-1.118L2.98 9.11c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg>`,
    warning: `<svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>`,
};

const cardColors = [
    'from-blue-500 to-blue-600',
    'from-green-500 to-green-600',
    'from-yellow-500 to-yellow-600',
    'from-red-500 to-red-600',
];

// Placeholder data for top selling products
const topProducts = [
    { name: 'Kopi Susu Gula Aren', sold: 120 },
    { name: 'Croissant Coklat', sold: 95 },
    { name: 'Teh Melati', sold: 80 },
    { name: 'Donat Gula', sold: 65 },
    { name: 'Roti Bakar Keju', sold: 50 },
];

const chartData = {
    labels: topProducts.map(p => p.name),
    datasets: [
        {
            label: 'Jumlah Terjual',
            backgroundColor: 'rgba(74, 144, 226, 0.5)',
            borderColor: 'rgba(74, 144, 226, 1)',
            borderWidth: 1,
            hoverBackgroundColor: 'rgba(74, 144, 226, 0.8)',
            hoverBorderColor: 'rgba(74, 144, 226, 1)',
            data: topProducts.map(p => p.sold),
            borderRadius: 4,
        },
    ],
};

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false,
        },
        tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.7)',
            titleColor: '#ffffff',
            bodyColor: '#ffffff',
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            grid: {
                color: 'rgba(255, 255, 255, 0.1)',
            },
            ticks: {
                color: '#cbd5e1', // slate-300
            },
        },
        x: {
            grid: {
                display: false,
            },
            ticks: {
                color: '#cbd5e1', // slate-300
            },
        },
    },
};

</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="space-y-6 animate-fade-in-up">
            <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl shadow-lg p-6">
                <h3 class="text-xl font-bold text-white">
                    Selamat Datang Kembali, {{ user.name }}!
                </h3>
                <p class="mt-2 text-sm text-gray-300">
                    Berikut adalah ringkasan aktivitas toko Anda hari ini. Semoga harimu menyenangkan!
                </p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div v-for="(stat, index) in stats" :key="stat.name"
                    :class="`bg-gradient-to-br ${cardColors[index % cardColors.length]}`"
                    class="p-6 rounded-2xl shadow-lg text-white transition-all duration-300 hover:scale-105 hover:shadow-2xl">
                    <div class="flex items-center">
                        <div
                            class="flex-shrink-0 bg-white/20 rounded-full p-3 transform group-hover:rotate-12 transition-transform duration-300">
                            <div v-html="icons[stat.icon]"></div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium truncate">{{ stat.name }}</p>
                            <p class="text-2xl font-bold">{{ stat.value }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl shadow-lg">
                <div class="p-6 border-b border-white/10">
                    <h3 class="text-lg font-bold text-white">Aksi Cepat</h3>
                </div>
                <div class="p-6 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                    <a :href="route('sales.create')"
                        class="flex flex-col items-center justify-center p-4 bg-white/5 hover:bg-blue-500/20 rounded-xl transition-all duration-300 group transform hover:-translate-y-1">
                        <div
                            class="p-3 bg-blue-500/10 rounded-full mb-2 border border-blue-500/30 group-hover:bg-blue-500/20 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 3h2.25l1.5 12h12.75l1.5-12H21M6.75 3l1.5 12m0 0l2.25 3.75M6.75 15h6.75m0 0l-2.25 3.75m2.25-3.75h3.75l2.25 3.75M9 18.75h6" />
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-gray-200 group-hover:text-white">POS Baru</span>
                    </a>
                    <a :href="route('master.products.create')"
                        class="flex flex-col items-center justify-center p-4 bg-white/5 hover:bg-green-500/20 rounded-xl transition-all duration-300 group transform hover:-translate-y-1">
                        <div
                            class="p-3 bg-green-500/10 rounded-full mb-2 border border-green-500/30 group-hover:bg-green-500/20 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-gray-200 group-hover:text-white">Tambah Produk</span>
                    </a>
                    <a :href="route('purchases.create')"
                        class="flex flex-col items-center justify-center p-4 bg-white/5 hover:bg-yellow-500/20 rounded-xl transition-all duration-300 group transform hover:-translate-y-1">
                        <div
                            class="p-3 bg-yellow-500/10 rounded-full mb-2 border border-yellow-500/30 group-hover:bg-yellow-500/20 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-yellow-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 17.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-gray-200 group-hover:text-white">Tambah Pembelian</span>
                    </a>
                    <a :href="route('reports.sales')"
                        class="flex flex-col items-center justify-center p-4 bg-white/5 hover:bg-purple-500/20 rounded-xl transition-all duration-300 group transform hover:-translate-y-1">
                        <div
                            class="p-3 bg-purple-500/10 rounded-full mb-2 border border-purple-500/30 group-hover:bg-purple-500/20 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m1-3l1 3m-9-3v4.5A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75v-4.5m-15 0h15" />
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-gray-200 group-hover:text-white">Laporan Penjualan</span>
                    </a>
                </div>
            </div>

            <!-- Top Selling Products -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Chart -->
                <div
                    class="lg:col-span-2 bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl shadow-lg">
                    <div class="p-6 border-b border-white/10">
                        <h3 class="text-lg font-bold text-white">5 Produk Terlaris Bulan Ini</h3>
                    </div>
                    <div class="p-6 h-96">
                        <Bar :data="chartData" :options="chartOptions" />
                    </div>
                </div>

                <!-- Table -->
                <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl shadow-lg">
                    <div class="p-6 border-b border-white/10">
                        <h3 class="text-lg font-bold text-white">Peringkat Produk</h3>
                    </div>
                    <div class="p-6">
                        <table class="w-full text-white">
                            <thead>
                                <tr class="text-left text-sm font-semibold text-gray-300 border-b border-white/10">
                                    <th class="py-3">#</th>
                                    <th class="py-3">Nama Produk</th>
                                    <th class="py-3 text-right">Terjual</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(product, index) in topProducts" :key="product.name"
                                    class="border-t border-white/10">
                                    <td class="py-3 text-gray-300">{{ index + 1 }}</td>
                                    <td class="py-3 font-medium">{{ product.name }}</td>
                                    <td class="py-3 text-right font-semibold">{{ product.sold }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
