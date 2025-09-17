<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, useForm } from "@inertiajs/vue3";

const props = defineProps({
    email: String,
    token: String,
});

const logoUrl = "http://googleusercontent.com/image_generation_content/0";

const form = useForm({
    token: props.token,
    email: props.email,
    password: "",
    password_confirmation: "",
});

const submit = () => {
    form.post(route("password.store"), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <Head title="Reset Password" />

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

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <InputLabel for="email" value="Email" />
                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full bg-gray-200"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                        readonly
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div>
                    <InputLabel for="password" value="Password Baru" />
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
                        value="Konfirmasi Password Baru"
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

                <div class="flex items-center justify-end pt-2">
                    <button
                        type="submit"
                        class="inline-flex items-center px-4 py-2 border border-indigo-300 text-sm font-medium rounded text-indigo-600 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 w-full justify-center h-10"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v-3m0 0V9m0 3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Reset Password
                    </button>
                    <!-- <PrimaryButton
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Reset Password
                    </PrimaryButton> -->
                </div>
            </form>
        </div>
    </div>
</template>
