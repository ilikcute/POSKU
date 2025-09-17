<script setup>
import Checkbox from "@/Components/Checkbox.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

// const store = page.props.store;

// Gunakan URL logo yang sama dari halaman Welcome
const logoUrl = "http://googleusercontent.com/image_generation_content/0";

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <Head title="Log in" />

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
            class="w-full max-w-md bg-white/70 backdrop-blur-xl rounded-2xl shadow-xl p-8 space-y-6"
        >
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
            <div class="text-center">                
                <h2 class="text-2xl font-bold text-gray-800">
                    Selamat Datang Kembali
                </h2>
                <p class="text-sm text-gray-600">
                    Masuk untuk melanjutkan ke aplikasi POSKU
                </p>
            </div>

            <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <InputLabel for="email" value="Email" />
                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div>
                    <InputLabel for="password" value="Password" />
                    <TextInput
                        id="password"
                        type="password"
                        class="mt-1 block w-full"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                    />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <Checkbox
                            name="remember"
                            v-model:checked="form.remember"
                        />
                        <span class="ms-2 text-sm text-gray-600"
                            >Ingat saya</span
                        >
                    </label>

                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-sm text-indigo-600 hover:text-indigo-800 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Lupa password?
                    </Link>
                </div>

                <div class="flex items-center">
                    <button
                        type="submit"
                        class="inline-flex items-center px-4 py-2 border border-indigo-300 text-sm font-medium rounded text-indigo-600 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 w-full justify-center h-10"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Log In
                    </button>
                </div>

                <div class="text-center text-sm text-gray-600">
                    Belum punya akun?
                    <Link
                        :href="route('register')"
                        class="font-medium text-indigo-600 hover:text-indigo-800"
                    >
                        Daftar di sini
                    </Link>
                </div>
            </form>
        </div>
    </div>
</template>
