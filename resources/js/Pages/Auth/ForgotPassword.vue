<template>
    <div
        class="relative min-h-screen flex flex-col justify-center items-center bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 text-white overflow-hidden">
        <!-- Decorative Gradient Blobs -->
        <div class="absolute -top-24 -left-24 w-72 h-72 rounded-full bg-indigo-400/30 blur-3xl"></div>
        <div class="absolute -bottom-20 -right-20 w-80 h-80 rounded-full bg-pink-400/25 blur-3xl"></div>

        <!-- Forgot Password Card -->
        <div
            class="relative z-10 w-full max-w-md bg-white/10 backdrop-blur-lg rounded-2xl shadow-2xl p-8 animate-fade-in-up">
            <!-- Logo and Title -->
            <div class="flex flex-col items-center mb-8">
                <img :src="logoUrl" alt="POSKU Logo" class="h-14 w-auto drop-shadow-lg mb-4" />
                <h1
                    class="text-3xl font-extrabold bg-gradient-to-r from-indigo-300 to-pink-300 bg-clip-text text-transparent">
                    Lupa Password
                </h1>
                <p class="text-white/80 mt-2 text-center">Masukkan email Anda untuk menerima link reset password</p>
            </div>

            <!-- Forgot Password Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-white/90">Email</label>
                    <input id="email" v-model="form.email" type="email" required autofocus
                        class="mt-1 block w-full px-4 py-3 rounded-xl border border-white/20 bg-white/20 text-white placeholder-white/70 focus:ring-2 focus:ring-pink-400 focus:outline-none"
                        placeholder="contoh@email.com" />
                </div>

                <div class="flex items-center">
                    <button type="submit"
                        class="inline-flex items-center px-6 py-3 text-sm font-bold rounded-xl bg-gradient-to-r from-indigo-500 to-pink-500 text-white shadow-lg hover:from-indigo-600 hover:to-pink-600 transition-transform duration-200 w-full justify-center h-12 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-400"
                        :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Kirim Link Reset
                    </button>
                </div>
            </form>

            <!-- Back to Login CTA -->
            <p class="mt-6 text-center text-white/80">
                Ingat password Anda?
                <Link :href="route('login')" class="font-semibold text-pink-200 hover:text-pink-100 transition">Masuk
                </Link>
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
    name: "ForgotPassword",
    components: {
        Head,
        Link,
    },
    setup() {
        const form = useForm({
            email: "",
        });

        function submit() {
            form.post(route("password.email"));
        }

        return { form, submit };
    },
    props: {
        logoUrl: {
            type: String,
            default: "/images/logo.svg",
        },
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
