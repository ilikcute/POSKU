<template>
    <div class="relative min-h-screen flex flex-col justify-center items-center bg-slate-900 text-white overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900"></div>
        <div
            class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-purple-800/20 via-slate-900/50 to-slate-900">
        </div>

        <!-- Decorative Gradient Blobs -->
        <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-purple-600/20 blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-20 -right-20 w-96 h-96 rounded-full bg-blue-600/15 blur-3xl animate-pulse delay-700"></div>

        <!-- Login Card -->
        <div
            class="relative z-10 w-full max-w-md bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl shadow-2xl p-8 animate-fade-in-up">
            <!-- Logo and Title -->
            <div class="flex flex-col items-center mb-8">
                <div
                    class="w-16 h-16 bg-gradient-to-br from-blue-400 to-purple-600 rounded-xl flex items-center justify-center shadow-lg mb-4">
                    <img :src="logoUrl" alt="POSKU Logo" class="h-10 w-10 rounded-lg object-contain" />
                </div>
                <h1
                    class="text-3xl font-extrabold bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">
                    Selamat Datang Kembali
                </h1>
                <p class="text-gray-300 mt-2 text-center">Masuk untuk mengakses dashboard kasir modern Anda</p>
            </div>

            <!-- Login Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-200 mb-2">Email</label>
                    <input id="email" v-model="form.email" type="email" required autofocus
                        class="block w-full px-4 py-3 rounded-xl border border-white/20 bg-white/5 text-white placeholder-gray-400 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:outline-none transition-all"
                        placeholder="contoh@email.com" />
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-200 mb-2">Password</label>
                    <input id="password" v-model="form.password" type="password" required
                        class="block w-full px-4 py-3 rounded-xl border border-white/20 bg-white/5 text-white placeholder-gray-400 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:outline-none transition-all"
                        placeholder="********" />
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center text-sm text-gray-300">
                        <input type="checkbox" v-model="form.remember"
                            class="rounded border-white/30 bg-white/10 text-purple-500 focus:ring-purple-500" />
                        <span class="ml-2">Ingat saya</span>
                    </label>
                    <Link :href="route('password.request')"
                        class="text-sm font-medium text-purple-400 hover:text-purple-300 transition">
                    Lupa Password?
                    </Link>
                </div>

                <button type="submit"
                    class="w-full bg-gradient-to-r from-blue-500 to-purple-600 text-white font-bold py-3 px-6 rounded-xl shadow-lg hover:scale-105 hover:from-blue-600 hover:to-purple-700 transition-all duration-200 border border-white/20">
                    Masuk
                </button>
            </form>

            <!-- Register CTA -->
            <p class="mt-6 text-center text-gray-300">
                Belum punya akun?
                <Link :href="route('register')" class="font-semibold text-purple-400 hover:text-purple-300 transition">
                Daftar Sekarang</Link>
            </p>
        </div>

        <!-- Footer -->
        <footer class="relative z-10 mt-8 text-center text-sm text-gray-400 animate-fade-in-up delay-300">
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
