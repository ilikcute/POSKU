<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-sm font-semibold text-[#1f1f1f]">
                Perbarui Kata Sandi
            </h2>

            <p class="mt-1 text-xs text-[#555]">
                Pastikan akun Anda menggunakan kata sandi yang panjang dan acak agar tetap aman.
            </p>
        </header>

        <form @submit.prevent="updatePassword" class="mt-6 space-y-6">
            <div>
                <InputLabel for="current_password" value="Kata Sandi Saat Ini" class="text-[#1f1f1f]" />

                <TextInput id=" current_password" ref="currentPasswordInput" v-model="form.current_password"
                    type="password" class="mt-1 block w-full bg-white border-[#9c9c9c] text-[#1f1f1f]"
                    autocomplete="current-password" />

                <InputError :message="form.errors.current_password" class="mt-2 text-red-600" />
            </div>

            <div>
                <InputLabel for="password" value="Kata Sandi Baru" class="text-[#1f1f1f]" />

                <TextInput id=" password" ref="passwordInput" v-model="form.password" type="password"
                    class="mt-1 block w-full bg-white border-[#9c9c9c] text-[#1f1f1f]" autocomplete="new-password" />

                <InputError :message="form.errors.password" class="mt-2 text-red-600" />
            </div>

            <div>
                <InputLabel for="password_confirmation" value="Konfirmasi Kata Sandi" class="text-[#1f1f1f]" />

                <TextInput id=" password_confirmation" v-model="form.password_confirmation" type="password"
                    class="mt-1 block w-full bg-white border-[#9c9c9c] text-[#1f1f1f]" autocomplete="new-password" />

                <InputError :message="form.errors.password_confirmation" class="mt-2 text-red-600" />
            </div>

            <div class="flex items-center gap-4">
                <button type="submit"
                    class="inline-flex items-center gap-2 bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] font-semibold py-2 px-4 rounded text-xs hover:bg-white transition-colors"
                    :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Perbarui Kata Sandi
                </button>

                <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                    <p v-if="form.recentlySuccessful" class="text-xs text-[#555]">
                        Simpan.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
