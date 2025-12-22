<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-sm font-semibold text-[#1f1f1f]">
                Informasi Profil
            </h2>

            <p class="mt-1 text-xs text-[#555]">
                Perbarui informasi profil dan alamat email akun Anda.
            </p>
        </header>

        <form @submit.prevent="form.patch(route('profile.update'))" class="mt-6 space-y-6">
            <div>
                <InputLabel for="name" value="Nama" class="text-[#1f1f1f]" />

                <TextInput id="name" type="text" class="mt-1 block w-full bg-white border-[#9c9c9c] text-[#1f1f1f]"
                    v-model="form.name" required autofocus autocomplete="name" />

                <InputError class="mt-2 text-red-600" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Email" class="text-[#1f1f1f]" />

                <TextInput id="email" type="email" class="mt-1 block w-full bg-white border-[#9c9c9c] text-[#1f1f1f]"
                    v-model="form.email" required autocomplete="username" />

                <InputError class="mt-2 text-red-600" :message="form.errors.email" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-xs text-[#555]">
                    Your email address is unverified.
                    <Link :href="route('verification.send')" method="post" as="button"
                        class="underline text-xs text-[#1f1f1f] hover:text-black rounded focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Click here to re-send the verification email.
                    </Link>
                </p>

                <div v-show="status === 'verification-link-sent'" class="mt-2 font-semibold text-xs text-green-600">
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <button type="submit"
                    class="inline-flex items-center gap-2 bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] font-semibold py-2 px-4 rounded text-xs hover:bg-white transition-colors"
                    :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Simpan
                </button>
                <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                    <p v-if="form.recentlySuccessful" class="text-xs text-[#555]">
                        Tersimpan.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
