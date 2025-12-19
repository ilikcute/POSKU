<template>
    <div class="relative min-h-screen flex flex-col bg-slate-900 text-white overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900"></div>
        <div
            class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-purple-800/20 via-slate-900/50 to-slate-900">
        </div>

        <!-- Header -->
        <header class="w-full animate-fade-in-down backdrop-blur-md bg-white/5 border-b border-white/10 sticky top-0 z-50">
            <div class="container mx-auto px-6 py-4 flex justify-between items-center relative">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 bg-gradient-to-br from-blue-400 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                        <img :src="logoUrl" alt="POSKU Logo" class="h-7 w-7 rounded-lg object-contain" />
                    </div>
                    <span
                        class="font-extrabold text-2xl bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent tracking-wide">
                        POSKU
                    </span>
                </div>
                <nav v-if="canLogin" class="flex items-center space-x-4">
                    <Link v-if="$page.props.auth.user" :href="route('dashboard')"
                        class="text-base font-semibold text-white/90 hover:text-white transition">
                    Dashboard
                    </Link>
                    <template v-else>
                        <Link :href="route('login')"
                            class="inline-block bg-gradient-to-r from-blue-500 to-purple-600 text-white font-bold py-2 px-6 rounded-xl text-base shadow-xl hover:scale-105 hover:from-blue-600 hover:to-purple-700 transition-all duration-200 border border-white/20">
                        Log in
                        </Link>
                        <Link v-if="canRegister" :href="route('register')"
                            class="ml-2 inline-block bg-white/10 backdrop-blur-sm text-white font-bold py-2 px-6 rounded-xl text-base border border-white/20 hover:bg-white/20 transition-all duration-200">
                        Register
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <!-- Hero Section -->
        <main class="w-7xl mx-auto flex-grow flex items-center relative">
            <div class="container mx-auto px-6 py-16 grid lg:grid-cols-2 gap-12 items-center relative z-10">
                <!-- Text Content -->
                <div class="flex flex-col justify-center items-start animate-fade-in-up space-y-6">
                    <h1 class="text-5xl md:text-6xl font-extrabold leading-tight drop-shadow-2xl">
                        Solusi Kasir <span
                            class="bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">Modern</span>
                    </h1>
                    <p class="text-lg md:text-xl text-gray-300 max-w-lg leading-relaxed">
                        Kelola penjualan, stok, dan pelanggan dengan mudah dalam satu aplikasi web yang cepat, aman, dan
                        intuitif. POSKU mendukung pertumbuhan bisnis retail Anda dengan teknologi terkini.
                    </p>
                    <div class="flex gap-4">
                        <Link :href="route('login')"
                            class="inline-flex items-center bg-gradient-to-r from-blue-500 to-purple-600 text-white font-bold py-3 px-8 rounded-xl text-lg shadow-xl hover:scale-105 hover:from-blue-600 hover:to-purple-700 transition-all duration-200 border border-white/20">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Mulai Sekarang
                        </Link>
                        <a href="#features"
                            class="inline-flex items-center bg-white/10 backdrop-blur-sm text-white font-bold py-3 px-8 rounded-xl text-lg border border-white/20 hover:bg-white/20 transition-all duration-200">
                            Pelajari Lebih Lanjut
                        </a>
                    </div>
                </div>

                <!-- Image Content -->
                <div class="flex justify-center animate-fade-in-up delay-200 relative">
                    <div
                        class="absolute -top-10 -right-10 w-80 h-80 bg-purple-600/30 rounded-full blur-3xl animate-pulse">
                    </div>
                    <div
                        class="absolute -bottom-10 -left-10 w-80 h-80 bg-blue-600/20 rounded-full blur-3xl animate-pulse delay-700">
                    </div>
                    <div
                        class="relative backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl p-4 shadow-2xl">
                        <img :src="heroImageUrl" alt="Modern Retail POS"
                            class="relative max-w-full h-auto rounded-xl" />
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="w-full py-6 animate-fade-in-up delay-500 bg-white/5 backdrop-blur-md border-t border-white/10 relative">
            <div class="container mx-auto text-center text-sm text-gray-400">
                &copy; 2025 <span class="font-bold text-white">POSKU</span>. All Rights Reserved.
            </div>
        </footer>
    </div>
</template>

<script>
import { Head, Link } from "@inertiajs/vue3";

export default {
    name: "Welcome",
    components: {
        Head,
        Link,
    },
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
</style>
