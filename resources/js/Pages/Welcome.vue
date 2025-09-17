<script setup>
import { Head, Link } from "@inertiajs/vue3";

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

// URL Aset Gambar (Logo & Hero Ilustrasi)
const logoUrl = "/logo-posku.svg";
const heroImageUrl = "/hero-modern-pos.svg";
</script>

<template>
    <Head title="Selamat Datang di POSKU" />
    <div class="relative min-h-screen w-full overflow-hidden bg-gradient-to-br from-indigo-100 via-pink-100 to-yellow-100">
        <!-- Decorative Blobs -->
        <div class="absolute top-0 left-0 w-72 h-72 bg-indigo-300 rounded-full opacity-30 blur-3xl -z-10 animate-pulse"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-pink-300 rounded-full opacity-30 blur-3xl -z-10 animate-pulse"></div>

        <div class="relative min-h-screen flex flex-col z-10">
            <header class="w-full">
                <div class="container mx-auto px-6 py-6 flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <img :src="logoUrl" alt="POSKU Logo" class="h-12 w-auto drop-shadow-lg" />
                        <span class="font-extrabold text-2xl text-indigo-700 tracking-wide">POSKU</span>
                    </div>
                    <nav v-if="canLogin">
                        <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="text-base font-semibold text-gray-700 hover:text-indigo-700 transition">Dashboard</Link>
                        <template v-else>
                            <Link :href="route('login')" class="text-base font-semibold text-gray-700 hover:text-indigo-700 transition">Log in</Link>
                            <Link v-if="canRegister" :href="route('register')" class="ml-4 inline-block bg-indigo-600 text-white font-semibold py-2 px-6 rounded-xl shadow-md hover:bg-indigo-700 transition">Register</Link>
                        </template>
                    </nav>
                </div>
            </header>

            <main class="flex-grow flex items-center">
                <div class="container mx-auto px-6 grid md:grid-cols-2 gap-16 items-center">
                    <div class="flex flex-col justify-center items-start animate-fadeIn">
                        <h1 class="text-5xl md:text-6xl font-extrabold leading-tight text-indigo-900 mb-6 drop-shadow-lg">
                            Solusi Kasir Modern
                        </h1>
                        <p class="text-xl text-gray-700 mb-8 max-w-lg">
                            Kelola penjualan, stok, dan pelanggan dengan mudah dalam satu aplikasi web yang cepat, aman, dan intuitif. POSKU mendukung pertumbuhan bisnis retail Anda dengan teknologi terkini.
                        </p>
                        <Link :href="route('login')" class="inline-block bg-gradient-to-r from-indigo-500 to-pink-500 text-white font-bold py-3 px-10 rounded-full text-xl shadow-xl hover:scale-105 hover:from-indigo-600 hover:to-pink-600 transition-transform duration-200">
                            Mulai Sekarang
                        </Link>
                    </div>
                    <div class="flex justify-center animate-fadeIn delay-200">
                        <img :src="heroImageUrl" alt="Modern Retail POS" class="max-w-full h-auto rounded-2xl shadow-2xl border-4 border-white" />
                    </div>
                </div>
            </main>

            <footer class="w-full py-8">
                <div class="container mx-auto text-center text-base text-gray-600">
                    &copy; 2025 <span class="font-bold text-indigo-600">POSKU</span>. All Rights Reserved.
                </div>
            </footer>
        </div>
    </div>
</template>

<style scoped>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(40px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.animate-fadeIn {
    animation: fadeIn 1s cubic-bezier(.4,0,.2,1) forwards;
}
.delay-200 {
    animation-delay: 0.2s;
}
</style>
