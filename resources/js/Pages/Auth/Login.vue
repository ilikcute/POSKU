<template>
    <div class="min-h-screen bg-[#e6e6e6] flex items-center justify-center p-4 font-[Tahoma] text-[#1f1f1f]">
        <div class="w-full max-w-md bg-[#f7f7f7] border border-[#9c9c9c] rounded p-6 shadow-sm">
            <div class="flex flex-col items-center mb-6">
                <div class="w-14 h-14 bg-white border border-[#9c9c9c] rounded flex items-center justify-center mb-3">
                    <img :src="logoUrl" alt="POSKU Logo" class="h-9 w-9 object-contain" />
                </div>
                <h1 class="text-xl font-bold">Selamat Datang Kembali</h1>
                <p class="text-xs text-[#555] mt-1 text-center">Masuk untuk mengakses dashboard kasir Anda.</p>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-[#1f1f1f] mb-1">Email</label>
                    <input id="email" v-model="form.email" type="email" required autofocus
                        class="block w-full px-3 py-2 rounded border border-[#9c9c9c] bg-white text-[#1f1f1f] focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none"
                        placeholder="contoh@email.com" />
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-[#1f1f1f] mb-1">Password</label>
                    <input id="password" v-model="form.password" type="password" required
                        class="block w-full px-3 py-2 rounded border border-[#9c9c9c] bg-white text-[#1f1f1f] focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none"
                        placeholder="********" />
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center text-xs text-[#555]">
                        <input type="checkbox" v-model="form.remember"
                            class="rounded border-[#9c9c9c] bg-white text-blue-600 focus:ring-blue-500" />
                        <span class="ml-2">Ingat saya</span>
                    </label>
                    <Link :href="route('password.request')"
                        class="text-xs font-semibold text-[#1f1f1f] hover:underline">
                    Lupa Password?
                    </Link>
                </div>

                <button type="submit"
                    class="w-full bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] py-2 text-xs font-semibold shadow-sm hover:bg-white transition-colors">
                    Masuk
                </button>
            </form>

            <p class="mt-4 text-center text-xs text-[#555]">
                Belum punya akun?
                <Link :href="route('register')" class="font-semibold text-[#1f1f1f] hover:underline">
                Daftar Sekarang</Link>
            </p>
        </div>
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

<style scoped></style>
