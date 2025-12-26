<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import { Line } from "vue-chartjs";
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    LineElement,
    PointElement,
    CategoryScale,
    LinearScale,
} from "chart.js";
import { computed, onMounted, ref } from "vue";

ChartJS.register(
    Title,
    Tooltip,
    Legend,
    LineElement,
    PointElement,
    CategoryScale,
    LinearScale
);

const props = defineProps({
    totalSalesToday: { type: Number, default: 0 },
    transactionCountToday: { type: Number, default: 0 },
    newCustomersThisMonth: { type: Number, default: 0 },
    topProducts: { type: Array, default: () => [] }, // [{ name, total_quantity }]
    salesChart: { type: Object, default: () => ({}) },
    lowStockProducts: { type: Array, default: () => [] },
    topPurchasedProducts: { type: Array, default: () => [] },
    topSuppliers: { type: Array, default: () => [] },
});

const page = usePage();
const user = computed(() => page.props.auth?.user ?? { name: "-" });
const store = computed(() => page.props.store ?? {});

const nowTime = ref("");
const nowDate = ref("");

const viewTop = ref("month"); // future ready (month/today/etc)

// ===== Utils
const toNumber = (v) => Number(v ?? 0);

const formatCurrency = (amount) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(toNumber(amount));
};

// ===== Data adapters (hindari mismatch field)
const topProducts = computed(() => props.topProducts ?? []);

const topProductsTable = computed(() =>
    topProducts.value.map((p) => ({
        name: p?.name ?? "-",
        qty: toNumber(p?.total_quantity ?? p?.sold ?? 0), // kompatibel jika backend pakai total_quantity
    }))
);

const topPurchasedTable = computed(() =>
    (props.topPurchasedProducts ?? []).map((p) => ({
        name: p?.name ?? "-",
        qty: toNumber(p?.total_quantity ?? 0),
    }))
);

const topSuppliersTable = computed(() =>
    (props.topSuppliers ?? []).map((p) => ({
        name: p?.name ?? "-",
        total: toNumber(p?.total_amount ?? 0),
    }))
);

// ===== UI Cards
const icons = {
    cash: `<svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>`,
    receipt: `<svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>`,
    star: `<svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.364 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.364-1.118L2.98 9.11c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg>`,
    warning: `<svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>`,
};

const cardColors = [
    "from-blue-500 to-blue-600",
    "from-green-500 to-green-600",
    "from-yellow-500 to-yellow-600",
    "from-red-500 to-red-600",
];

const stats = computed(() => {
    const bestProduct = topProductsTable.value.length ? topProductsTable.value[0].name : "Belum ada data";

    return [
        {
            name: "Total Penjualan Hari Ini",
            value: formatCurrency(props.totalSalesToday),
            icon: "cash",
        },
        {
            name: "Total Transaksi",
            value: String(props.transactionCountToday ?? 0),
            icon: "receipt",
        },
        {
            name: "Produk Terlaris",
            value: bestProduct,
            icon: "star",
        },
        {
            name: "Stok Menipis",
            value: `${props.lowStockProducts?.length ?? 0} Produk`,
            icon: "warning",
        },
    ];
});

const updateClock = () => {
    const now = new Date();
    nowTime.value = now.toLocaleTimeString("id-ID", {
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
    });
    nowDate.value = now.toLocaleDateString("id-ID", {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};

onMounted(() => {
    updateClock();
    setInterval(updateClock, 1000);
});

const salesChartData = computed(() => ({
    labels: props.salesChart?.labels ?? [],
    datasets: [
        {
            label: "Penjualan",
            data: props.salesChart?.data ?? [],
            borderColor: "#1f1f1f",
            backgroundColor: "rgba(255,255,255,0.6)",
            tension: 0.25,
        },
    ],
}));

const salesChartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
    },
    scales: {
        y: {
            ticks: { color: "#1f1f1f", font: { size: 10 } },
            grid: { color: "rgba(0,0,0,0.1)" },
        },
        x: {
            ticks: { color: "#1f1f1f", font: { size: 9 } },
            grid: { display: false },
        },
    },
}));
</script>

<template>

    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="text-xs text-[#555] leading-4">
                    Station: {{ props.station?.name || props.station?.id || "-" }}
                    <div class="text-[11px] text-[#555]">
                        Shift: {{ props.activeShift?.shift_code || "Belum Aktif" }}
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-sm font-semibold">{{ nowDate }}</div>
                    <div class="text-lg font-bold">{{ nowTime }}</div>
                </div>
                <div class="text-right text-xs">
                    <div class="font-semibold">
                        USER: {{ props.activeShift?.name || user.name }}
                    </div>
                </div>
            </div>
        </template>

        <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-[#f0f0f0] border border-[#9c9c9c] rounded shadow-sm font-[Tahoma] text-[#1f1f1f]">
                <div class="bg-gradient-to-b from-[#9bb6e8] via-[#7a96d1] to-[#5c72bf] p-4 text-white">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 text-sm">
                        <div class="bg-white/20 border border-white/40 px-3 py-2">
                            Total Penjualan: <strong>{{ formatCurrency(props.totalSalesToday) }}</strong>
                        </div>
                        <div class="bg-white/20 border border-white/40 px-3 py-2">
                            Transaksi: <strong>{{ props.transactionCountToday }}</strong>
                        </div>
                        <div class="bg-white/20 border border-white/40 px-3 py-2">
                            Stok Menipis: <strong>{{ props.lowStockProducts?.length ?? 0 }}</strong>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 lg:grid-cols-3 gap-4">
                        <div class="bg-white/95 text-[#1f1f1f] border border-[#9c9c9c] p-3 min-h-[220px]">
                            <div class="text-xs font-semibold border-b border-[#9c9c9c] pb-1">
                                Item Terjual Terbanyak
                            </div>
                            <table class="w-full text-xs mt-2">
                                <thead class="text-left text-[#333]">
                                    <tr>
                                        <th class="py-1">Nama</th>
                                        <th class="py-1 text-right">Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="product in topProductsTable" :key="product.name">
                                        <td class="py-1">{{ product.name }}</td>
                                        <td class="py-1 text-right">{{ product.qty }}</td>
                                    </tr>
                                    <tr v-if="!topProductsTable.length">
                                        <td colspan="2" class="py-4 text-center text-[#666]">Belum ada data.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="bg-white/95 text-[#1f1f1f] border border-[#9c9c9c] p-3 min-h-[220px]">
                            <div class="text-xs font-semibold border-b border-[#9c9c9c] pb-1">
                                Item Pembelian Terbanyak
                            </div>
                            <table class="w-full text-xs mt-2">
                                <thead class="text-left text-[#333]">
                                    <tr>
                                        <th class="py-1">Nama</th>
                                        <th class="py-1 text-right">Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="product in topPurchasedTable" :key="product.name">
                                        <td class="py-1">{{ product.name }}</td>
                                        <td class="py-1 text-right">{{ product.qty }}</td>
                                    </tr>
                                    <tr v-if="!topPurchasedTable.length">
                                        <td colspan="2" class="py-4 text-center text-[#666]">Belum ada data.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="bg-white/95 text-[#1f1f1f] border border-[#9c9c9c] p-3 min-h-[220px]">
                            <div class="text-xs font-semibold border-b border-[#9c9c9c] pb-1">
                                Pembelian Supplier Terbanyak
                            </div>
                            <table class="w-full text-xs mt-2">
                                <thead class="text-left text-[#333]">
                                    <tr>
                                        <th class="py-1">Supplier</th>
                                        <th class="py-1 text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="supplier in topSuppliersTable" :key="supplier.name">
                                        <td class="py-1">{{ supplier.name }}</td>
                                        <td class="py-1 text-right">{{ formatCurrency(supplier.total) }}</td>
                                    </tr>
                                    <tr v-if="!topSuppliersTable.length">
                                        <td colspan="2" class="py-4 text-center text-[#666]">Belum ada data.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_200px] gap-4">
                        <div class="bg-white/95 text-[#1f1f1f] border border-[#9c9c9c] p-3 min-h-[260px] min-w-0">
                            <div class="text-xs font-semibold border-b border-[#9c9c9c] pb-1">
                                Grafik Penjualan 1 Bulan Terakhir
                            </div>
                            <div class="h-52 mt-2 w-full min-w-0">
                                <Line v-if="salesChartData.labels.length" :data="salesChartData"
                                    :options="salesChartOptions" />
                                <div v-else class="h-full flex items-center justify-center text-[#666] text-xs">
                                    Belum ada data grafik.
                                </div>
                            </div>
                        </div>

                        <div class="bg-white/95 text-[#1f1f1f] border border-[#9c9c9c] p-3 min-w-0">
                            <div class="text-xs font-semibold border-b border-[#9c9c9c] pb-1">Shortcut</div>
                            <div class="mt-3 space-y-2">
                                <Link :href="route('sales.create')"
                                    class="block w-full text-center py-2 bg-[#e9e9e9] border border-[#9c9c9c] text-[#1f1f1f] shadow-sm text-sm font-semibold hover:bg-white">
                                    PENJUALAN
                                </Link>
                                <Link :href="route('purchases.create')"
                                    class="block w-full text-center py-2 bg-[#e9e9e9] border border-[#9c9c9c] text-[#1f1f1f] shadow-sm text-sm font-semibold hover:bg-white">
                                    PEMBELIAN
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
