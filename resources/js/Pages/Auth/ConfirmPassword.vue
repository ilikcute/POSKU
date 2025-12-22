<template>
    <div class="min-h-screen bg-[#e6e6e6] flex items-center justify-center p-4 font-[Tahoma] text-[#1f1f1f]">
        <div class="w-full max-w-md bg-[#f7f7f7] border border-[#9c9c9c] rounded p-6 shadow-sm">
            <div class="flex flex-col items-center mb-6">
                <img :src="logoUrl" alt="POSKU Logo" class="h-12 w-auto mb-3" />
                <h1 class="text-xl font-bold">Konfirmasi Password</h1>
                <p class="text-xs text-[#555] mt-1 text-center">Masukkan ulang password Anda untuk melanjutkan.</p>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label for="password" class="block text-sm font-medium text-[#1f1f1f]">Password</label>
                    <input id="password" v-model="form.password" type="password" required autofocus
                        class="mt-1 block w-full px-3 py-2 rounded border border-[#9c9c9c] bg-white text-[#1f1f1f] focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="********" />
                </div>

                <div class="flex items-center">
                    <button type="submit"
                        class="w-full bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] py-2 text-xs font-semibold shadow-sm hover:bg-white transition-colors"
                        :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Konfirmasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import { Head, useForm } from "@inertiajs/vue3";

export default {
    name: "ConfirmPassword",
    components: {
        Head,
    },
    setup() {
        const form = useForm({
            password: "",
        });

        function submit() {
            form.post(route("password.confirm"));
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
