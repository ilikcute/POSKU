<template>
    <div
        class="relative min-h-screen flex flex-col justify-center items-center bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 text-white overflow-hidden">
        <!-- Decorative Gradient Blobs -->
        <div class="absolute -top-24 -left-24 w-72 h-72 rounded-full bg-indigo-400/30 blur-3xl"></div>
        <div class="absolute -bottom-20 -right-20 w-80 h-80 rounded-full bg-pink-400/25 blur-3xl"></div>

        <!-- Reset Password Card -->
        <div
            class="relative z-10 w-full max-w-md bg-white/10 backdrop-blur-lg rounded-2xl shadow-2xl p-8 animate-fade-in-up">
            <!-- Logo and Title -->
            <div class="flex flex-col items-center mb-8">
                <img :src="logoUrl" alt="POSKU Logo" class="h-14 w-auto drop-shadow-lg mb-4" />
                <h1
                    class="text-3xl font-extrabold bg-gradient-to-r from-indigo-300 to-pink-300 bg-clip-text text-transparent">
                    Reset Password
                </h1>
                <p class="text-white/80 mt-2 text-center">Masukkan password baru Anda untuk akun POSKU</p>
            </div>

            <!-- Reset Password Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-white/90">Email</label>
                    <input id="email" v-model="form.email" type="email" required autofocus
                        class="mt-1 block w-full px-4 py-3 rounded-xl border border-white/20 bg-white/20 text-white placeholder-white/70 focus:ring-2 focus:ring-pink-400 focus:outline-none"
                        placeholder="contoh@email.com" />
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-white/90">Password Baru</label>
                    <input id="password" v-model="form.password" type="password" required
                        class="mt-1 block w-full px-4 py-3 rounded-xl border border-white/20 bg-white/20 text-white placeholder-white/70 focus:ring-2 focus:ring-pink-400 focus:outline-none"
                        placeholder="********" />
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-white/90">Konfirmasi
                        Password</label>
                    <input id="password_confirmation" v-model="form.password_confirmation" type="password" required
                        class="mt-1 block w-full px-4 py-3 rounded-xl border border-white/20 bg-white/20 text-white placeholder-white/70 focus:ring-2 focus:ring-pink-400 focus:outline-none"
                        placeholder="********" />
                </div>

                <button type="submit"
                    class="w-full bg-gradient-to-r from-indigo-500 to-pink-500 text-white font-bold py-3 px-6 rounded-xl shadow-lg hover:scale-105 hover:from-indigo-600 hover:to-pink-600 transition-transform duration-200">
                    Reset Password
                </button>
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
    name: "ResetPassword",
    components: {
        Head,
        Link,
    },
    props: {
        email: String,
        token: String,
        logoUrl: {
            type: String,
            default: "/images/logo.svg",
        },
    },
    setup(props) {
        const form = useForm({
            token: props.token,
            email: props.email || "",
            password: "",
            password_confirmation: "",
        });

        function submit() {
            form.post(route("password.update"));
        }

        return { form, submit };
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
