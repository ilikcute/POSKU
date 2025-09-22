<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";
import { Chart, registerables } from "chart.js";

Chart.register(...registerables);

const props = defineProps({
    totalSalesToday: {
        type: Number,
        default: 0,
    },
    transactionCountToday: {
        type: Number,
        default: 0,
    },
    newMembersThisMonth: {
        type: Number,
        default: 0,
    },
    topProduct: {
        type: String,
        default: "N/A",
    },
    salesChart: {
        type: Object,
        default: () => ({ labels: [], data: [] }), // Beri nilai default jika data tidak ada
    },
    lowStockProducts: {
        type: Array,
        default: () => [],
    },
});

const salesChartCanvas = ref(null);

// Fungsi untuk format angka ke Rupiah
const formatCurrency = (value) => {
    if (typeof value !== "number") {
        value = 0;
    }
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(value);
};

onMounted(() => {
    // Pastikan data chart dan elemen canvas sudah ada sebelum membuat grafik
    if (props.salesChart && props.salesChart.labels && salesChartCanvas.value) {
        new Chart(salesChartCanvas.value, {
            type: "bar",
            data: {
                labels: props.salesChart.labels,
                datasets: [
                    {
                        label: "Penjualan (Rp)",
                        data: props.salesChart.data,
                        backgroundColor: "rgba(79, 70, 229, 0.8)",
                        borderColor: "rgba(79, 70, 229, 1)",
                        borderWidth: 1,
                    },
                ],
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                    },
                },
            },
        });
    }
});
</script>

<template>

    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-sm font-medium text-gray-500">
                        Penjualan Hari Ini
                    </h3>
                    <p class="text-3xl font-bold text-gray-900 mt-2">
                        {{ formatCurrency(totalSalesToday) }}
                    </p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-sm font-medium text-gray-500">
                        Transaksi Hari Ini
                    </h3>
                    <p class="text-3xl font-bold text-gray-900 mt-2">
                        {{ transactionCountToday }}
                    </p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-sm font-medium text-gray-500">
                        Produk Terlaris (Bulan Ini)
                    </h3>
                    <p class="text-2xl font-bold text-gray-900 mt-2 truncate">
                        {{ topProduct }}
                    </p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-sm font-medium text-gray-500">
                        Member Baru (Bulan Ini)
                    </h3>
                    <p class="text-3xl font-bold text-gray-900 mt-2">
                        {{ newMembersThisMonth }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        Grafik Penjualan 7 Hari Terakhir
                    </h3>
                    <canvas ref="salesChartCanvas"></canvas>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        Stok Segera Habis
                    </h3>
                    <div class="space-y-4">
                        <div v-if="
                            lowStockProducts && lowStockProducts.length > 0
                        " v-for="product in lowStockProducts" :key="product.id">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-700">{{ product.name }}</span>
                                <span class="text-sm font-bold text-red-500">Sisa: {{ product.quantity }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5 mt-1">
                                <div class="bg-red-500 h-2.5 rounded-full" :style="{
                                    width:
                                        (product.quantity /
                                            (product.min_stock_alert * 2)) *
                                        100 +
                                        '%',
                                }"></div>
                            </div>
                        </div>
                        <div v-else class="text-sm text-gray-500">
                            Stok aman!
                        </div>
                    </div>
                </div>
            </div>

            <footer class="mt-8 text-center text-sm text-gray-500">
                POSKU &copy; {{ new Date().getFullYear() }}
            </footer>
        </div>
    </AuthenticatedLayout>
</template>
