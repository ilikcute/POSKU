<script setup>
import { computed } from "vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

const props = defineProps({
    status: String,
});

const logoUrl = "http://googleusercontent.com/image_generation_content/0";

const form = useForm({});

const submit = () => {
    form.post(route("verification.send"));
};

const verificationLinkHasBeenSent = computed(
    () => props.status === "verification-link-sent"
);
</script>

<template>
    <Head title="Verifikasi Email" />

    <div
        class="relative min-h-screen w-full flex items-center justify-center p-6 overflow-hidden"
    >
        <div
            class="absolute inset-0 bg-gradient-to-br from-sky-200 via-rose-200 to-amber-200 -z-20"
        ></div>
        <div
            class="absolute -top-1/4 -right-1/4 w-1/2 h-1/2 bg-white/20 rounded-full filter blur-3xl opacity-50 -z-10"
        ></div>
        <div
            class="absolute -bottom-1/4 -left-1/4 w-1/2 h-1/2 bg-white/20 rounded-full filter blur-3xl opacity-50 -z-10"
        ></div>

        <div
            class="w-full max-w-md bg-white/70 backdrop-blur-xl rounded-2xl shadow-xl p-8 space-y-6 text-center"
        >
            <div>
                <div class="flex justify-center">
                <Link href="/">
                    <div class="flex items-center">
                            <img 
                                v-if="store && store.logo_path" 
                                :src="`/storage/${store.logo_path}`" 
                                :alt="store.name"
                                class="h-8 w-8 rounded object-contain mr-2"/>
                            <span class="font-semibold text-lg">
                                {{ store && store.name ? store.name : 'POSKU' }}
                            </span>
                        </div>
                </Link>
            </div>
                <h2 class="text-2xl font-bold text-gray-800">
                    Verifikasi Email Anda
                </h2>
            </div>

            <div class="mb-4 text-sm text-gray-600">
                Terima kasih telah mendaftar! Sebelum memulai, silakan
                verifikasi alamat email Anda dengan mengklik link yang baru saja
                kami kirimkan. Jika Anda tidak menerima email, kami akan
                mengirimkannya kembali.
            </div>

            <div
                v-if="verificationLinkHasBeenSent"
                class="mb-4 font-medium text-sm text-green-600 bg-green-100 p-3 rounded-md"
            >
                Link verifikasi baru telah dikirim ke alamat email yang Anda
                berikan saat pendaftaran.
            </div>

            <form @submit.prevent="submit">
                <div class="mt-4 flex items-center justify-between">
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition disabled:opacity-25"
                        :disabled="form.processing"
                        :class="{ 'opacity-25': form.processing }"
                        title="Kirim Ulang Email Verifikasi"
                    >
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 12H8m8 0l-4-4m4 4l-4 4M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>
                    <!-- <PrimaryButton
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Kirim Ulang Email Verifikasi
                    </PrimaryButton> -->

                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >Log Out</Link
                    >
                </div>
            </form>
        </div>
    </div>
</template>
