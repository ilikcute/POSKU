<template>
    <div class="min-h-screen bg-[#e6e6e6] flex items-center justify-center p-4 font-[Tahoma] text-[#1f1f1f]">
        <div class="w-full max-w-md bg-[#f7f7f7] border border-[#9c9c9c] rounded p-6 shadow-sm">
            <div class="flex flex-col items-center mb-6">
                <img :src="logoUrl" alt="POSKU Logo" class="h-12 w-auto mb-3" />
                <h1 class="text-xl font-bold">Verifikasi Email</h1>
                <p class="text-xs text-[#555] mt-1 text-center">
                    Silakan verifikasi email Anda dengan mengklik link yang sudah kami kirimkan.
                </p>
            </div>

            <transition name="fade">
                <div v-if="toast.message"
                    :class="['mb-3 p-3 rounded text-center text-sm font-medium border', toast.type === 'success' ? 'bg-[#e1f3e1] border-[#9c9c9c] text-[#1f1f1f]' : 'bg-[#ffe1e1] border-[#9c9c9c] text-[#1f1f1f]']">
                    {{ toast.message }}
                </div>
            </transition>

            <div v-if="status === 'verification-link-sent'" class="mb-3 text-xs font-semibold text-[#1f1f1f] text-center">
                Link verifikasi baru telah dikirim ke email Anda.
            </div>

            <div class="space-y-3">
                <form @submit.prevent="resend" class="flex items-center">
                    <button type="submit"
                        class="w-full bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] py-2 text-xs font-semibold shadow-sm hover:bg-white transition-colors"
                        :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Kirim Ulang Email Verifikasi
                    </button>
                </form>

                <form method="POST" @submit.prevent="logout">
                    <button type="submit"
                        class="w-full bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] py-2 text-xs font-semibold shadow-sm hover:bg-white transition-colors">
                        Logout
                    </button>
                </form>
            </div>
        </div>
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
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
