<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";


const user = usePage().props.auth.user;

const form = useForm({
    initial_cash: 0,
    name: "",
    authorization_password: "",
});

const submit = () => {
    form.post(route("shifts.open.store"));
};
</script>

<template>

    <Head title="Buka Shift" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="font-semibold text-lg text-[#1f1f1f] leading-tight">Buka Shift</h2>
                <p class="text-xs text-[#555]">Mulai shift baru dengan modal awal.</p>
            </div>
        </template>

        <div
            class="mx-auto max-w-lg bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-lg p-6 space-y-6">
            <div class="text-center">
                <h1 class="text-2xl font-bold text-[#1f1f1f]">Buka Shift Baru</h1>
                <p class="mt-2 text-[#555]">Selamat datang, {{ user.name }}!</p>
                <p class="text-sm text-[#555] mt-2">
                    Tidak ada shift yang sedang aktif. Silakan masukkan modal awal untuk memulai.
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <InputLabel for="name" value="Nama Kasir" class="text-[#1f1f1f]" />
                    <TextInput id="name" type="text"
                        class="mt-1 block w-full bg-white border-[#9c9c9c] focus:ring-blue-500 focus:border-blue-500 rounded"
                        v-model="form.name" required autofocus />
                    <InputError class="mt-2 text-red-600" :message="form.errors.name" />
                </div>

                <div>
                    <InputLabel for="initial_cash" value="Modal Awal (Rp)" class="text-[#1f1f1f]" />
                    <TextInput id="initial_cash" type="number"
                        class="mt-1 block w-full bg-white border-[#9c9c9c] focus:ring-blue-500 focus:border-blue-500 rounded"
                        v-model="form.initial_cash" required />
                    <InputError class="mt-2 text-red-600" :message="form.errors.initial_cash" />
                </div>

                <div>
                    <InputLabel for="authorization_password" value="Password Verifikasi" class="text-[#1f1f1f]" />
                    <TextInput id="authorization_password" type="password"
                        class="mt-1 block w-full bg-white border-[#9c9c9c] focus:ring-blue-500 focus:border-blue-500 rounded"
                        v-model="form.authorization_password" required autofocus />
                    <InputError class="mt-2 text-red-600" :message="form.errors.authorization_password" />
                </div>

                <div class="flex items-center justify-between mt-6">
                    <Link :href="route('logout')" method="post" as="button"
                        class="inline-flex items-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] py-2 px-5 text-xs font-semibold shadow-sm hover:bg-white transition-colors">
                    Log Out
                    </Link>
                    <button type="submit" :disabled="form.processing"
                        class="inline-flex items-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] py-2 px-5 text-xs font-semibold shadow-sm hover:bg-white transition-colors">
                        Buka Shift
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
