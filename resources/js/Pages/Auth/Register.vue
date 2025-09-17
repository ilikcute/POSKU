<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

// Gunakan URL logo yang sama
const logoUrl = "http://googleusercontent.com/image_generation_content/0";

const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
});

const submit = () => {
    form.post(route("register"), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <Head title="Register" />

    <div
        class="relative min-h-screen w-full flex items-center justify-center p-6 overflow-hidden"
    >
        <pre
            v-if="Object.keys($page.props.errors).length > 0"
            class="bg-red-100 text-red-700 p-4 m-4 rounded"
            >{{ $page.props.errors }}</pre
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
                <h2 class="text-2xl font-bold text-gray-800">Buat Akun Baru</h2>
                <p class="text-sm text-gray-600">
                    Mulai kelola bisnis Anda bersama POSKU
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <InputLabel for="name" value="Nama" />
                    <TextInput
                        id="name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.name"
                        required
                        autofocus
                        autocomplete="name"
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div>
                    <InputLabel for="email" value="Email" />
                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full"
                        v-model="form.email"
                        required
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
                        autocomplete="new-password"
                    />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <div>
                    <InputLabel
                        for="password_confirmation"
                        value="Konfirmasi Password"
                    />
                    <TextInput
                        id="password_confirmation"
                        type="password"
                        class="mt-1 block w-full"
                        v-model="form.password_confirmation"
                        required
                        autocomplete="new-password"
                    />
                    <InputError
                        class="mt-2"
                        :message="form.errors.password_confirmation"
                    />
                </div>

                <div class="pt-2">
                    <button
                        type="submit"
                        class="inline-flex items-center px-4 py-2 border border-indigo-300 text-sm font-medium rounded text-indigo-600 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 w-full justify-center h-10"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        <!-- Sparkle icon for a more attractive look -->
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.364-6.364l-1.414 1.414M6.05 17.95l-1.414 1.414m12.728 0l-1.414-1.414M6.05 6.05L4.636 4.636M12 8a4 4 0 100 8 4 4 0 000-8z"/>
                        </svg>
                        Register
                    </button>
                    <!-- <PrimaryButton
                        class="w-full justify-center"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Register
                    </PrimaryButton> -->
                </div>

                <div class="text-center text-sm text-gray-600">
                    Sudah punya akun?
                    <Link
                        :href="route('login')"
                        class="font-medium text-indigo-600 hover:text-indigo-800"
                    >
                        Masuk di sini
                    </Link>
                </div>
            </form>
        </div>
    </div>
</template>
