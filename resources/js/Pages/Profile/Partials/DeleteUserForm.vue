<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Hapus Akun
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Begitu akun Anda dihapus, semua sumber daya dan data yang terkait akan dihapus secara permanen. Sebelum menghapus akun Anda, silakan unduh data atau informasi apa pun yang ingin Anda simpan.
            </p>
        </header>

        <button
            type="submit" @click="confirmUserDeletion"
            class="inline-flex items-center px-4 py-2 border border-red-300 text-sm font-medium rounded text-red-600 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 w-full justify-center h-10"
            :class="{ 'opacity-25': form.processing }"
            :disabled="form.processing"
        >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <rect x="3" y="3" width="18" height="18" rx="4" stroke="currentColor" stroke-width="2" fill="#fee2e2"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 8l8 8M16 8l-8 8" stroke="#b91c1c"/>
            </svg>
            Hapus Akun
        </button>
        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6">
                <h2
                    class="text-lg font-medium text-gray-900"
                >
                    Apakah Anda yakin ingin menghapus akun Anda?
                </h2>

                <p class="mt-1 text-sm text-gray-600">
                    Begitu akun Anda dihapus, semua sumber daya dan data yang terkait akan dihapus secara permanen. Silakan masukkan kata sandi Anda untuk mengonfirmasi bahwa Anda ingin menghapus akun Anda secara permanen.
                </p>

                <div class="mt-6">
                    <InputLabel
                        for="password"
                        value="Password"
                        class="sr-only"
                    />

                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-3/4"
                        placeholder="Password"
                        @keyup.enter="deleteUser"
                    />

                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end">
                    <div class="mt-6 flex justify-end">
                        <button type="button" @click="closeModal"
                            class="inline-flex items-center px-4 py-2 border border-indigo-300 text-sm font-medium rounded text-indigo-600 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 w-full justify-center h-10"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </Modal>
    </section>
</template>
