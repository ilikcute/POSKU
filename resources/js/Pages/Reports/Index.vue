<template>
    <AuthenticatedLayout>
        <Head title="Laporan" />

        <template #header>
            <div>
                <h2 class="font-semibold text-lg text-[#1f1f1f]">Laporan</h2>
                <p class="text-xs text-[#555]">Pilih jenis laporan yang ingin ditampilkan.</p>
            </div>
        </template>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="bg-white border border-[#9c9c9c] rounded p-4 shadow-sm">
                    <div class="flex items-center mb-3">
                        <div class="bg-[#dbe7ff] border border-[#9c9c9c] p-2 rounded">
                            <svg class="w-5 h-5 text-[#1f1f1f]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                </path>
                            </svg>
                        </div>
                        <h3 class="ml-3 text-sm font-semibold text-[#1f1f1f]">Laporan Penjualan</h3>
                    </div>
                    <p class="text-sm text-[#555] mb-3">Detail penjualan dengan filter rentang tanggal.</p>
                    <Link :href="route('reports.sales')"
                        class="inline-flex bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] px-3 py-2 text-xs font-semibold shadow-sm hover:bg-white transition-colors">
                        Buka Laporan
                    </Link>
                </div>

                <div class="bg-white border border-[#9c9c9c] rounded p-4 shadow-sm">
                    <div class="flex items-center mb-3">
                        <div class="bg-[#dbe7ff] border border-[#9c9c9c] p-2 rounded">
                            <svg class="w-5 h-5 text-[#1f1f1f]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                        </div>
                        <h3 class="ml-3 text-sm font-semibold text-[#1f1f1f]">Laporan Pembelian</h3>
                    </div>
                    <p class="text-sm text-[#555] mb-3">Analisis transaksi pembelian per periode.</p>
                    <Link :href="route('reports.purchases')"
                        class="inline-flex bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] px-3 py-2 text-xs font-semibold shadow-sm hover:bg-white transition-colors">
                        Buka Laporan
                    </Link>
                </div>

                <div class="bg-white border border-[#9c9c9c] rounded p-4 shadow-sm">
                    <div class="flex items-center mb-3">
                        <div class="bg-[#dbe7ff] border border-[#9c9c9c] p-2 rounded">
                            <svg class="w-5 h-5 text-[#1f1f1f]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        <h3 class="ml-3 text-sm font-semibold text-[#1f1f1f]">Laporan Inventori</h3>
                    </div>
                    <p class="text-sm text-[#555] mb-3">Pantau stok dan pergerakan inventori.</p>
                    <Link :href="route('reports.stock')"
                        class="inline-flex bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] px-3 py-2 text-xs font-semibold shadow-sm hover:bg-white transition-colors">
                        Buka Laporan
                    </Link>
                </div>

                <div class="bg-white border border-[#9c9c9c] rounded p-4 shadow-sm">
                    <div class="flex items-center mb-3">
                        <div class="bg-[#dbe7ff] border border-[#9c9c9c] p-2 rounded">
                            <svg class="w-5 h-5 text-[#1f1f1f]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                            </svg>
                        </div>
                        <h3 class="ml-3 text-sm font-semibold text-[#1f1f1f]">Pergerakan Stok</h3>
                    </div>
                    <p class="text-sm text-[#555] mb-3">Riwayat stok masuk dan keluar.</p>
                    <Link :href="route('reports.stock-movements')"
                        class="inline-flex bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] px-3 py-2 text-xs font-semibold shadow-sm hover:bg-white transition-colors">
                        Buka Laporan
                    </Link>
                </div>

                <div class="bg-white border border-[#9c9c9c] rounded p-4 shadow-sm">
                    <div class="flex items-center mb-3">
                        <div class="bg-[#dbe7ff] border border-[#9c9c9c] p-2 rounded">
                            <svg class="w-5 h-5 text-[#1f1f1f]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                </path>
                            </svg>
                        </div>
                        <h3 class="ml-3 text-sm font-semibold text-[#1f1f1f]">Laba Rugi</h3>
                    </div>
                    <p class="text-sm text-[#555] mb-3">Analisis laba rugi per periode.</p>
                    <Link :href="route('reports.profit-loss')"
                        class="inline-flex bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] px-3 py-2 text-xs font-semibold shadow-sm hover:bg-white transition-colors">
                        Buka Laporan
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>
