<template>
    <div
        class="relative min-h-screen flex flex-col justify-center items-center bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 text-white overflow-hidden">
        <!-- Decorative Gradient Blobs -->
        <div class="absolute -top-24 -left-24 w-72 h-72 rounded-full bg-indigo-400/30 blur-3xl"></div>
        <div class="absolute -bottom-20 -right-20 w-80 h-80 rounded-full bg-pink-400/25 blur-3xl"></div>

        <!-- Login Card -->
        <div
            class="relative z-10 w-full max-w-md bg-white/10 backdrop-blur-lg rounded-2xl shadow-2xl p-8 animate-fade-in-up">
            <!-- Logo and Title -->
            <div class="flex flex-col items-center mb-8">
                <img :src="logoUrl" alt="POSKU Logo" class="h-14 w-auto drop-shadow-lg mb-4" />
                <h1
                    class="text-3xl font-extrabold bg-gradient-to-r from-indigo-300 to-pink-300 bg-clip-text text-transparent">
                    Selamat Datang Kembali
                </h1>
                <p class="text-white/80 mt-2 text-center">Masuk untuk mengakses dashboard kasir modern Anda</p>
            </div>

            <!-- Login Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-white/90">Email</label>
                    <input id="email" v-model="form.email" type="email" required autofocus
                        class="mt-1 block w-full px-4 py-3 rounded-xl border border-white/20 bg-white/20 text-white placeholder-white/70 focus:ring-2 focus:ring-pink-400 focus:outline-none"
                        placeholder="contoh@email.com" />
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-white/90">Password</label>
                    <input id="password" v-model="form.password" type="password" required
                        class="mt-1 block w-full px-4 py-3 rounded-xl border border-white/20 bg-white/20 text-white placeholder-white/70 focus:ring-2 focus:ring-pink-400 focus:outline-none"
                        placeholder="********" />
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center text-sm text-white/80">
                        <input type="checkbox" v-model="form.remember"
                            class="rounded border-white/30 bg-white/10 text-pink-500 focus:ring-pink-400" />
                        <span class="ml-2">Ingat saya</span>
                    </label>
                    <Link :href="route('password.request')"
                        class="text-sm font-medium text-pink-200 hover:text-pink-100 transition">
                    Lupa Password?
                    </Link>
                </div>

                <button type="submit"
                    class="w-full bg-gradient-to-r from-indigo-500 to-pink-500 text-white font-bold py-3 px-6 rounded-xl shadow-lg hover:scale-105 hover:from-indigo-600 hover:to-pink-600 transition-transform duration-200">
                    Masuk
                </button>
            </form>

            <!-- Register CTA -->
            <p class="mt-6 text-center text-white/80">
                Belum punya akun?
                <Link :href="route('register')" class="font-semibold text-pink-200 hover:text-pink-100 transition">
                Daftar Sekarang</Link>
            </p>
        </div>

        <!-- Footer -->
        <footer class="relative z-10 mt-8 text-center text-sm text-white/70 animate-fade-in-up delay-300">
            &copy; 2025 <span class="font-bold text-white">POSKU</span>. All Rights Reserved.
        </footer>
    </div>
</template>

<script>
import { Head, Link, useForm } from "@inertiajs/vue3";

export default {
    name: "Login",
    components: {
        Head,
        Link,
    },
    setup() {
        const form = useForm({
            email: "",
            password: "",
            remember: false,
        });

        function submit() {
            form.post(route("login"));
        }

        return { form, submit };
    },
    props: {
        logoUrl: String,
    },
};
</script>

<style scoped>
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

.animate-fade-in-up {
    animation: fadeInUp 0.8s ease forwards;
}

.delay-300 {
    animation-delay: 0.3s;
}
</style>
