<script setup>
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";

const user = usePage().props.auth.user;

const form = useForm({
    initial_cash: 0,
});

const submit = () => {
    form.post(route("shifts.open.store"));
};
</script>

<template>
    <Head title="Buka Shift" />

    <div class="min-h-screen flex flex-col justify-center items-center p-4 bg-gray-900 text-white">
        <div class="max-w-md w-full bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl shadow-lg p-8 space-y-6">
            <div class="text-center">
                <h1 class="text-3xl font-bold text-white">
                    Buka Shift Baru
                </h1>
                <p class="mt-2 text-gray-300">Selamat datang, {{ user.name }}!</p>
                <p class="text-sm text-gray-400 mt-2">
                    Tidak ada shift yang sedang aktif. Silakan masukkan modal
                    awal untuk memulai.
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <InputLabel for="initial_cash" value="Modal Awal (Rp)" class="text-gray-300" />
                    <TextInput
                        id="initial_cash"
                        type="number"
                        class="mt-1 block w-full bg-white/5 border-white/20 focus:ring-blue-500 focus:border-blue-500 rounded-lg"
                        v-model="form.initial_cash"
                        required
                        autofocus
                    />
                    <InputError
                        class="mt-2"
                        :message="form.errors.initial_cash"
                    />
                </div>

                <div class="flex items-center justify-between mt-6">
                     <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="text-sm text-gray-400 hover:text-white transition-colors duration-300"
                    >
                        Log Out
                    </Link>
                    <PrimaryButton
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Buka Shift
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </div>
</template>
