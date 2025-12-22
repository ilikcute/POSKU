<template>
    <div class="min-h-screen bg-[#e6e6e6] flex items-center justify-center p-4 font-[Tahoma] text-[#1f1f1f]">
        <div class="w-full max-w-md bg-[#f7f7f7] border border-[#9c9c9c] rounded p-6 shadow-sm">
            <div class="flex flex-col items-center mb-6">
                <img :src="logoUrl" alt="POSKU Logo" class="h-12 w-auto mb-3" />
                <h1 class="text-xl font-bold">Lupa Password</h1>
                <p class="text-xs text-[#555] mt-1 text-center">Masukkan email Anda untuk menerima link reset.</p>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-[#1f1f1f]">Email</label>
                    <input id="email" v-model="form.email" type="email" required autofocus
                        class="mt-1 block w-full px-3 py-2 rounded border border-[#9c9c9c] bg-white text-[#1f1f1f] focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="contoh@email.com" />
                </div>

                <div class="flex items-center">
                    <button type="submit"
                        class="w-full bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] py-2 text-xs font-semibold shadow-sm hover:bg-white transition-colors"
                        :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Kirim Link Reset
                    </button>
                </div>
            </form>

            <p class="mt-4 text-center text-xs text-[#555]">
                Ingat password Anda?
                <Link :href="route('login')" class="font-semibold text-[#1f1f1f] hover:underline">Masuk</Link>
            </p>
        </div>
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

<style scoped></style>
