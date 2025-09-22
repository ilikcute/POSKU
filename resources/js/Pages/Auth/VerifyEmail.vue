<template>
    <div
        class="relative min-h-screen flex flex-col justify-center items-center bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 text-white overflow-hidden">
        <!-- Decorative Gradient Blobs -->
        <div class="absolute -top-24 -left-24 w-72 h-72 rounded-full bg-indigo-400/30 blur-3xl"></div>
        <div class="absolute -bottom-20 -right-20 w-80 h-80 rounded-full bg-pink-400/25 blur-3xl"></div>

        <!-- Verify Email Card -->
        <div
            class="relative z-10 w-full max-w-md bg-white/10 backdrop-blur-lg rounded-2xl shadow-2xl p-8 animate-fade-in-up">
            <!-- Logo and Title -->
            <div class="flex flex-col items-center mb-8">
                <img :src="logoUrl" alt="POSKU Logo" class="h-14 w-auto drop-shadow-lg mb-4" />
                <h1
                    class="text-3xl font-extrabold bg-gradient-to-r from-indigo-300 to-pink-300 bg-clip-text text-transparent">
                    Verifikasi Email
                </h1>
                <p class="text-white/80 mt-2 text-center">
                    Terima kasih telah mendaftar! Sebelum memulai, silakan verifikasi email Anda dengan mengklik link
                    yang sudah kami kirimkan.
                </p>
            </div>

            <!-- Toast Notification -->
            <transition name="fade">
                <div v-if="toast.message"
                    :class="['mb-4 p-3 rounded-xl text-center font-medium', toast.type === 'success' ? 'bg-green-500/80 text-white' : 'bg-red-500/80 text-white']">
                    {{ toast.message }}
                </div>
            </transition>

            <!-- Message if resent -->
            <div v-if="status === 'verification-link-sent'" class="mb-4 text-sm font-medium text-green-300 text-center">
                Link verifikasi baru telah dikirim ke email Anda.
            </div>

            <!-- Action Buttons -->
            <div class="space-y-4">
                <form @submit.prevent="resend" class="flex items-center">
                    <button type="submit"
                        class="inline-flex items-center px-6 py-3 text-sm font-bold rounded-xl bg-gradient-to-r from-indigo-500 to-pink-500 text-white shadow-lg hover:from-indigo-600 hover:to-pink-600 transition-transform duration-200 w-full justify-center h-12 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-400"
                        :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9H4m0 0V4m0 5a8.003 8.003 0 0015.938 2H20" />
                        </svg>
                        Kirim Ulang Email Verifikasi
                    </button>
                </form>

                <form method="POST" @submit.prevent="logout">
                    <button type="submit"
                        class="inline-flex items-center px-6 py-3 text-sm font-bold rounded-xl bg-white text-indigo-600 shadow-lg hover:bg-gray-100 transition-transform duration-200 w-full justify-center h-12 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Logout
                    </button>
                </form>
            </div>
        </div>

        <!-- Footer -->
        <footer class="relative z-10 mt-8 text-center text-sm text-white/70 animate-fade-in-up delay-300">
            &copy; 2025 <span class="font-bold text-white">POSKU</span>. All Rights Reserved.
        </footer>
    </div>
</template>

<script>
import { Head, useForm } from "@inertiajs/vue3";

export default {
    name: "VerifyEmail",
    components: {
        Head,
    },
    props: {
        status: String,
        logoUrl: {
            type: String,
            default: "/images/logo.svg",
        },
    },
    setup() {
        const form = useForm({});
        const toast = Vue.reactive({ message: "", type: "success" });

        function showToast(message, type = "success") {
            toast.message = message;
            toast.type = type;
            setTimeout(() => (toast.message = ""), 4000);
        }

        function resend() {
            form.post(route("verification.send"), {
                onSuccess: () => showToast("Link verifikasi baru telah dikirim ke email Anda.", "success"),
                onError: () => showToast("Gagal mengirim ulang link verifikasi.", "error"),
            });
        }

        function logout() {
            form.post(route("logout"));
        }

        return { form, resend, logout, toast };
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

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>