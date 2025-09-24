<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { ref } from 'vue';

const props = defineProps({
    store: Object,
});

// State untuk preview logo baru
const logoPreview = ref(null);

// Inisialisasi form dengan data toko dari controller
const form = useForm({
    name: props.store.name,
    address: props.store.address,
    phone: props.store.phone,
    logo: null,
});

// Handle file input change
const handleLogoChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.logo = file;
        // Create preview URL
        logoPreview.value = URL.createObjectURL(file);
    } else {
        form.logo = null;
        logoPreview.value = null;
    }
};

const submit = () => {
    form.processing = true;

    // Always use FormData for file uploads
    const formData = new FormData();
    formData.append('name', form.name);
    formData.append('address', form.address || '');
    formData.append('phone', form.phone || '');
    formData.append('_method', 'PATCH');

    if (form.logo) {
        formData.append('logo', form.logo);
    }

    // Use router.post with FormData for file uploads
    router.post(route('store.profile.update'), formData, {
        preserveScroll: true,
        onSuccess: (page) => {
            form.processing = false;
            form.clearErrors();
            form.reset('logo');
            logoPreview.value = null;

            // Set success state
            form.recentlySuccessful = true;

            // Hide success message after 3 seconds
            setTimeout(() => {
                form.recentlySuccessful = false;
            }, 3000);
        },
        onError: (errors) => {
            form.processing = false;
            form.setError(errors);
            console.error('Form errors:', errors);
        },
        onFinish: () => {
            form.processing = false;
        }
    });
};
</script>

<template>

    <Head title="Profil Toko" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">
                Profil Toko
            </h2>
        </template>


        <div
            class="mx-auto max-w-lg bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl shadow-lg p-8 space-y-6">
            <div class="text-center">
                <h1 class="text-3xl font-bold text-white">Informasi Toko</h1>
                <p class="text-sm text-gray-400 mt-2">
                    Perbarui nama, alamat, dan informasi kontak toko Anda.
                </p>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <InputLabel for="name" value="Nama Toko" class="text-white" />
                        <TextInput id="name" type="text"
                            class="mt-1 block w-full bg-white/5 border-white/20 focus:ring-blue-500 focus:border-blue-500 rounded-lg"
                            v-model="form.name" required autofocus />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div>
                        <InputLabel for="address" value="Alamat Toko" class="text-white" />
                        <textarea id="address"
                            class="mt-1 block w-full bg-white/5 border-white/20 focus:ring-blue-500 focus:border-blue-500 rounded-lg"
                            rows="3" v-model="form.address" placeholder="Masukkan alamat lengkap toko"></textarea>
                        <InputError class="mt-2" :message="form.errors.address" />
                    </div>

                    <div>
                        <InputLabel for="phone" value="Nomor Telepon" class="text-white" />
                        <TextInput id="phone" type="text"
                            class="mt-1 block w-full bg-white/5 border-white/20 focus:ring-blue-500 focus:border-blue-500 rounded-lg"
                            v-model="form.phone" placeholder="Contoh: 081234567890" />
                        <InputError class="mt-2" :message="form.errors.phone" />
                    </div>

                    <div>
                        <InputLabel for="logo" value="Logo Toko" class="text-white" />
                        <input id="logo" type="file" accept="image/*"
                            class="mt-1 block w-sm bg-white/5 border-white/20 focus:ring-blue-500 focus:border-blue-500 rounded-lg"
                            @change="handleLogoChange" />
                        <InputError class="mt-2" :message="form.errors.logo" />
                        <p class="mt-2 text-sm text-white">
                            Pilih gambar logo toko Anda (format: JPG, PNG, GIF. Maksimal 2MB).
                        </p>

                        <!-- Logo Preview -->
                        <div class="mt-2">
                            <p class="text-sm text-left font-medium text-white mb-2">Preview Logo:</p>
                            <div class="flex items-center space-x-4">
                                <!-- Current Logo (if exists and no new file selected) -->
                                <div v-if="!logoPreview && props.store.logo_path" class="text-center">
                                    <p class="text-xs text-gray-500 mb-1">Logo Saat Ini</p>
                                    <img :src="`/storage/${props.store.logo_path}`" alt="Logo Toko Saat Ini"
                                        class="h-20 w-auto object-contain border rounded shadow-sm"
                                        @error="$event.target.style.display = 'none'" />
                                </div>

                                <!-- New Logo Preview -->
                                <div v-if="logoPreview" class="text-center">
                                    <p class="text-xs text-gray-500 mb-1">Logo Baru</p>
                                    <img :src="logoPreview" alt="Preview Logo Baru"
                                        class="h-20 w-auto object-contain border rounded shadow-sm" />
                                </div>

                                <!-- No Logo State -->
                                <div v-if="!logoPreview && !props.store.logo_path" class="text-center">
                                    <div class="h-20 w-20 bg-gray-100 border rounded flex items-center justify-center">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                    </div>
                                    <p class="text-xs text-white mt-1">Belum ada logo</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-6 mt-6">
                        <button @click="router.visit(route('dashboard'))"
                            class="ms-3 inline-block bg-gradient-to-r from-indigo-400 to-pink-400 text-white font-bold py-2 px-12 rounded-full text-lg shadow-xl hover:scale-105 hover:from-indigo-500 hover:to-pink-500 transition-transform duration-200">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                            Batal
                        </button>
                        <button type="submit" :disabled="form.processing"
                            class="ms-3 inline-block bg-gradient-to-r from-indigo-400 to-pink-400 text-white font-bold py-2 px-12 rounded-full text-lg shadow-xl hover:scale-105 hover:from-indigo-500 hover:to-pink-500 transition-transform duration-200">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </button>
                        <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                            <p v-if="form.recentlySuccessful" class="text-sm text-green-600 font-medium">
                                ✓ Berhasil disimpan!
                            </p>
                        </Transition>
                    </div>
                </form>
            </div>
        </div>

    </AuthenticatedLayout>
</template>