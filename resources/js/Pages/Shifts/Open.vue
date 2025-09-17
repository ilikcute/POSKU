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

    <div
        class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100"
    >
        <div
            class="w-full sm:max-w-md mt-6 px-6 py-8 bg-white shadow-md overflow-hidden sm:rounded-lg"
        >
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">
                    Buka Shift Baru
                </h1>
                <p class="text-gray-600">Selamat datang, {{ user.name }}!</p>
                <p class="text-sm text-gray-500 mt-2">
                    Tidak ada shift yang sedang aktif. Silakan masukkan modal
                    awal untuk memulai.
                </p>
            </div>

            <form @submit.prevent="submit">
                <div>
                    <InputLabel for="initial_cash" value="Modal Awal (Rp)" />
                    <TextInput
                        id="initial_cash"
                        type="number"
                        class="mt-1 block w-full"
                        v-model="form.initial_cash"
                        required
                        autofocus
                    />
                    <InputError
                        class="mt-2"
                        :message="form.errors.initial_cash"
                    />
                </div>

                <div class="flex items-center justify-end mt-6">
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="text-sm text-gray-600 hover:text-gray-900"
                    >
                        Log Out
                    </Link>

                    <PrimaryButton
                        class="ms-4"
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
