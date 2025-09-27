<template>
    <div
        class="relative min-h-screen flex flex-col bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 text-white">
        <!-- Header -->
        <header class="w-full animate-fade-in-down backdrop-blur-sm bg-white/10 sticky top-0 z-50">
            <div class="container mx-auto px-6 py-4 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <img :src="logoUrl" alt="POSKU Logo" class="h-10 w-auto drop-shadow-lg" />
                    <span
                        class="font-extrabold text-2xl bg-gradient-to-r from-indigo-300 to-pink-300 bg-clip-text text-transparent tracking-wide">
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
                            class="inline-block bg-gradient-to-r from-indigo-400 to-pink-400 text-white font-bold py-2 px-6 rounded-full text-base shadow-xl hover:scale-105 hover:from-indigo-500 hover:to-pink-500 transition-transform duration-200">
                        Log in
                        </Link>
                        <Link v-if="canRegister" :href="route('register')"
                            class="ml-2 inline-block bg-gradient-to-r from-indigo-400 to-pink-400 text-white font-bold py-2 px-6 rounded-full text-base shadow-xl hover:scale-105 hover:from-indigo-500 hover:to-pink-500 transition-transform duration-200">
                        Register
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <!-- Hero Section -->
        <main class="w-7xl mx-auto flex-grow flex items-center relative overflow-hidden">
            <div class="container mx-auto px-6 py-16 grid lg:grid-cols-2 gap-6 items-center">
                <!-- Text Content -->
                <div class="flex flex-col justify-center items-start animate-fade-in-up space-y-6">
                    <h1 class="text-5xl md:text-6xl font-extrabold leading-tight drop-shadow-2xl">
                        Solusi Kasir <span class="text-yellow-300">Modern</span>
                    </h1>
                    <p class="text-lg md:text-xl text-white/90 max-w-lg leading-relaxed">
                        Kelola penjualan, stok, dan pelanggan dengan mudah dalam satu aplikasi web yang cepat, aman, dan
                        intuitif. POSKU mendukung pertumbuhan bisnis retail Anda dengan teknologi terkini.
                    </p>
                    <Link :href="route('login')"
                        class="inline-block bg-gradient-to-r from-indigo-400 to-pink-400 text-white font-bold py-3 px-10 rounded-full text-lg shadow-xl hover:scale-105 hover:from-indigo-500 hover:to-pink-500 transition-transform duration-200">
                    Mulai Sekarang
                    </Link>
                </div>

                <!-- Image Content -->
                <div class="flex justify-center animate-fade-in-up delay-200 relative w-7xl">
                    <div class="absolute -top-10 -right-10 w-100 h-100 bg-pink-400/40 rounded-full blur-3xl"></div>
                    <img :src="heroImageUrl" alt="Modern Retail POS"
                        class="relative max-w-full h-auto rounded-2xl shadow-2xl border-4 border-white/50" />
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="w-full py-6 animate-fade-in-up delay-500 bg-white/10 backdrop-blur-md">
            <div class="container mx-auto text-center text-sm text-white/80">
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
