<template>
    <div class="relative min-h-screen flex flex-col bg-slate-900 text-white overflow-hidden">
        <!-- Background -->
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900">
        </div>
        <div
            class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-purple-800/20 via-slate-900/50 to-slate-900">
        </div>

        <Head title="POSKU - Solusi Kasir Modern" />

        <!-- Header -->
        <header
            class="w-full backdrop-blur-md bg-white/5 border-b border-white/10 sticky top-0 z-50 animate-fade-in-down">
            <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 py-4 flex justify-between items-center">
                <div class="flex items-center gap-3 min-w-0">
                    <div
                        class="w-10 h-10 bg-gradient-to-br from-blue-400 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                        <img :src="logoUrl" alt="POSKU Logo" class="h-7 w-7 rounded-lg object-contain" />
                    </div>
                    <span
                        class="font-extrabold text-xl sm:text-2xl bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent tracking-wide truncate">
                        POSKU
                    </span>
                </div>

                <nav v-if="canLogin" class="flex items-center gap-3 sm:gap-4" aria-label="Primary">
                    <Link v-if="$page.props.auth?.user" :href="route('dashboard')"
                        class="text-sm sm:text-base font-semibold text-white/90 hover:text-white transition">
                    Dashboard
                    </Link>

                    <template v-else>
                        <Link :href="route('login')"
                            class="inline-flex items-center justify-center bg-gradient-to-r from-blue-500 to-purple-600 text-white font-bold py-2 px-4 sm:px-6 rounded-xl text-sm sm:text-base shadow-xl hover:scale-[1.02] hover:from-blue-600 hover:to-purple-700 transition-all duration-200 border border-white/20">
                        Log in
                        </Link>

                        <Link v-if="canRegister" :href="route('register')"
                            class="inline-flex items-center justify-center bg-white/10 backdrop-blur-sm text-white font-bold py-2 px-4 sm:px-6 rounded-xl text-sm sm:text-base border border-white/20 hover:bg-white/20 transition-all duration-200">
                        Register
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <!-- Hero -->
        <main class="relative flex-grow">
            <div
                class="mx-auto w-full max-w-7xl px-4 sm:px-6 py-12 sm:py-16 grid lg:grid-cols-2 gap-10 lg:gap-12 items-center relative z-10">
                <!-- Text -->
                <div class="animate-fade-in-up space-y-6 text-center lg:text-left">
                    <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold leading-tight drop-shadow-2xl">
                        Solusi Kasir
                        <span
                            class="bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">Modern</span>
                    </h1>

                    <p class="text-base sm:text-lg md:text-xl text-gray-300 max-w-xl mx-auto lg:mx-0 leading-relaxed">
                        Kelola penjualan, stok, dan pelanggan dalam satu aplikasi web yang cepat, aman, dan intuitif.
                        POSKU mendukung pertumbuhan bisnis retail Anda dengan teknologi terkini.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center lg:justify-start">
                        <Link :href="route('login')"
                            class="inline-flex items-center justify-center bg-gradient-to-r from-blue-500 to-purple-600 text-white font-bold py-3 px-6 sm:px-8 rounded-xl text-base sm:text-lg shadow-xl hover:scale-[1.02] hover:from-blue-600 hover:to-purple-700 transition-all duration-200 border border-white/20">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Mulai Sekarang
                        </Link>

                        <a href="#features"
                            class="inline-flex items-center justify-center bg-white/10 backdrop-blur-sm text-white font-bold py-3 px-6 sm:px-8 rounded-xl text-base sm:text-lg border border-white/20 hover:bg-white/20 transition-all duration-200">
                            Pelajari Lebih Lanjut
                        </a>
                    </div>
                </div>

                <!-- Image -->
                <div class="relative flex justify-center animate-fade-in-up delay-200">
                    <div
                        class="pointer-events-none absolute -top-10 -right-10 w-72 sm:w-80 h-72 sm:h-80 bg-purple-600/30 rounded-full blur-3xl animate-pulse">
                    </div>
                    <div
                        class="pointer-events-none absolute -bottom-10 -left-10 w-72 sm:w-80 h-72 sm:h-80 bg-blue-600/20 rounded-full blur-3xl animate-pulse delay-700">
                    </div>

                    <div
                        class="relative w-full max-w-md sm:max-w-lg lg:max-w-xl backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl p-3 sm:p-4 shadow-2xl">
                        <img :src="heroImageUrl" alt="Modern Retail POS" class="w-full h-auto rounded-xl" loading="lazy"
                            decoding="async" />
                    </div>
                </div>
            </div>

            <!-- Features (biar link #features ada tujuan) -->
            <section id="features" class="relative z-10 mx-auto w-full max-w-7xl px-4 sm:px-6 pb-16">
                <div class="grid md:grid-cols-3 gap-4 sm:gap-6">
                    <div class="rounded-2xl border border-white/10 bg-white/5 backdrop-blur-md p-6">
                        <h3 class="font-bold text-lg">Penjualan Cepat</h3>
                        <p class="text-gray-300 mt-2 text-sm sm:text-base">Proses transaksi lebih ringkas dan minim
                            klik, cocok
                            untuk jam ramai.</p>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-white/5 backdrop-blur-md p-6">
                        <h3 class="font-bold text-lg">Kontrol Stok</h3>
                        <p class="text-gray-300 mt-2 text-sm sm:text-base">Pantau pergerakan stok, alert barang menipis,
                            dan
                            stok opname lebih terstruktur.</p>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-white/5 backdrop-blur-md p-6">
                        <h3 class="font-bold text-lg">Laporan Real-time</h3>
                        <p class="text-gray-300 mt-2 text-sm sm:text-base">Lihat performa harian, profit/loss, dan tren
                            penjualan untuk keputusan cepat.</p>
                    </div>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer
            class="w-full py-6 bg-white/5 backdrop-blur-md border-t border-white/10 relative z-10 animate-fade-in-up delay-500">
            <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 text-center text-xs sm:text-sm text-gray-400">
                &copy; 2025 <span class="font-bold text-white">POSKU</span>. All Rights Reserved.
            </div>
        </footer>
    </div>
</template>

<script>
import { Head, Link } from "@inertiajs/vue3";

export default {
    name: "Welcome",
    components: { Head, Link },
    props: {
        canLogin: Boolean,
        canRegister: Boolean,
        logoUrl: String,
        heroImageUrl: String,
    },
};
</script>

<style>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.8s ease forwards;
}

.animate-fade-in-down {
    animation: fadeInDown 0.8s ease forwards;
}

.delay-200 {
    animation-delay: 0.2s;
}

.delay-500 {
    animation-delay: 0.5s;
}

/* aksesibilitas: hormati preferensi reduce motion */
@media (prefers-reduced-motion: reduce) {

    .animate-fade-in-up,
    .animate-fade-in-down,
    .animate-pulse {
        animation: none !important;
    }
}
</style>
