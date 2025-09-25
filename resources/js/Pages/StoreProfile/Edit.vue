<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    store: Object,
});

const viewMode = ref('form');
const isModalOpen = ref(false);

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

const openModal = () => {
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset('logo');
    logoPreview.value = null;
};

const saveStoreProfile = () => {
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
            closeModal();

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
    <AuthenticatedLayout>

        <Head title="Profil Toko" />

        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-xl text-white leading-tight">
                        Profil Toko
                    </h2>
                    <p class="text-sm text-gray-400 mt-1">
                        Kelola informasi dan pengaturan toko dengan tampilan konsisten.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="flex bg-gray-800/50 rounded-lg p-1 backdrop-blur-sm">
                        <button @click="viewMode = 'form'" :class="[
                            'px-3 py-2 rounded-md text-sm font-medium transition-all duration-200',
                            viewMode === 'form'
                                ? 'bg-white/20 text-white shadow-sm'
                                : 'text-gray-300 hover:text-white hover:bg-white/10'
                        ]">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Form
                        </button>
                        <button @click="viewMode = 'preview'" :class="[
                            'px-3 py-2 rounded-md text-sm font-medium transition-all duration-200',
                            viewMode === 'preview'
                                ? 'bg-white/20 text-white shadow-sm'
                                : 'text-gray-300 hover:text-white hover:bg-white/10'
                        ]">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Preview
                        </button>
                    </div>

                    <button @click="openModal"
                        class="inline-flex items-center justify-center bg-gradient-to-r from-blue-400 to-blue-500 text-white font-bold py-3 px-6 rounded-full text-sm shadow-xl hover:scale-105 hover:from-blue-500 hover:to-blue-600 transition-transform duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit Profil
                    </button>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Success Message -->
            <div v-if="form.recentlySuccessful"
                class="mb-6 p-4 bg-green-500/20 border border-green-500/30 rounded-lg backdrop-blur-sm">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-green-400 font-medium">Profil toko berhasil diperbarui!</p>
                </div>
            </div>

            <!-- Store Profile Display -->
            <div v-if="viewMode === 'preview'"
                class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl shadow-xl p-8">
                <div class="text-center mb-8">
                    <div
                        class="w-24 h-24 mx-auto mb-4 rounded-full bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center">
                        <svg v-if="!props.store.logo_path" class="w-12 h-12 text-white" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <img v-else :src="`/storage/${props.store.logo_path}`" alt="Logo Toko"
                            class="w-full h-full object-cover rounded-full" />
                    </div>
                    <h1 class="text-3xl font-bold text-white mb-2">{{ props.store.name }}</h1>
                    <div class="space-y-2 text-gray-300">
                        <p v-if="props.store.address" class="flex items-center justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            {{ props.store.address }}
                        </p>
                        <p v-if="props.store.phone" class="flex items-center justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            {{ props.store.phone }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Empty state for form view -->
            <div v-else class="text-center py-12 backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <h3 class="text-lg font-medium text-gray-300 mb-2">Kelola Profil Toko</h3>
                <p class="text-gray-400 mb-6">Klik tombol "Edit Profil" untuk memperbarui informasi toko Anda.</p>
                <button @click="openModal"
                    class="inline-flex items-center bg-gradient-to-r from-blue-400 to-blue-500 text-white font-bold py-3 px-6 rounded-full text-sm shadow-xl hover:scale-105 hover:from-blue-500 hover:to-blue-600 transition-transform duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Profil
                </button>
            </div>
        </div>

        <!-- Edit Modal -->
        <Modal :show="isModalOpen" @close="closeModal">
            <div
                class="p-8 bg-gray-900/95 backdrop-blur-md border border-white/10 rounded-2xl shadow-2xl max-w-2xl mx-auto space-y-6 text-white">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-500 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <h2
                        class="text-2xl font-bold bg-gradient-to-r from-blue-400 to-blue-500 bg-clip-text text-transparent">
                        Edit Profil Toko
                    </h2>
                </div>

                <form @submit.prevent="saveStoreProfile" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <InputLabel for="name" value="Nama Toko *" class="text-gray-300 font-semibold" />
                            <TextInput id="name" v-model="form.name"
                                class="mt-2 block w-full bg-white/5 border-white/20 rounded-lg text-white placeholder-gray-400 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Masukkan nama toko" required />
                            <InputError :message="form.errors?.name" class="mt-2 text-red-400" />
                        </div>

                        <div>
                            <InputLabel for="address" value="Alamat Toko" class="text-gray-300 font-semibold" />
                            <textarea id="address" v-model="form.address" rows="3"
                                class="mt-2 block w-full bg-white/5 border-white/20 rounded-lg text-white placeholder-gray-400 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Masukkan alamat lengkap toko"></textarea>
                            <InputError :message="form.errors?.address" class="mt-2 text-red-400" />
                        </div>

                        <div>
                            <InputLabel for="phone" value="Nomor Telepon" class="text-gray-300 font-semibold" />
                            <TextInput id="phone" v-model="form.phone"
                                class="mt-2 block w-full bg-white/5 border-white/20 rounded-lg text-white placeholder-gray-400 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Contoh: 081234567890" />
                            <InputError :message="form.errors?.phone" class="mt-2 text-red-400" />
                        </div>

                        <div>
                            <InputLabel for="logo" value="Logo Toko" class="text-gray-300 font-semibold" />
                            <input id="logo" type="file" accept="image/*"
                                class="mt-2 block w-full bg-white/5 border-white/20 rounded-lg text-white file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-500 file:text-white hover:file:bg-blue-600"
                                @change="handleLogoChange" />
                            <InputError :message="form.errors?.logo" class="mt-2 text-red-400" />
                            <p class="mt-2 text-sm text-gray-400">
                                Pilih gambar logo toko Anda (format: JPG, PNG, GIF. Maksimal 2MB).
                            </p>

                            <!-- Logo Preview -->
                            <div class="mt-4">
                                <p class="text-sm font-medium text-gray-300 mb-3">Preview Logo:</p>
                                <div class="flex items-center space-x-6">
                                    <!-- Current Logo (if exists and no new file selected) -->
                                    <div v-if="!logoPreview && props.store.logo_path" class="text-center">
                                        <p class="text-xs text-gray-500 mb-2">Logo Saat Ini</p>
                                        <img :src="`/storage/${props.store.logo_path}`" alt="Logo Toko Saat Ini"
                                            class="h-20 w-auto object-contain border border-white/20 rounded-lg shadow-sm"
                                            @error="$event.target.style.display = 'none'" />
                                    </div>

                                    <!-- New Logo Preview -->
                                    <div v-if="logoPreview" class="text-center">
                                        <p class="text-xs text-gray-500 mb-2">Logo Baru</p>
                                        <img :src="logoPreview" alt="Preview Logo Baru"
                                            class="h-20 w-auto object-contain border border-white/20 rounded-lg shadow-sm" />
                                    </div>

                                    <!-- No Logo State -->
                                    <div v-if="!logoPreview && !props.store.logo_path" class="text-center">
                                        <div
                                            class="h-20 w-20 bg-gray-700 border border-white/20 rounded-lg flex items-center justify-center">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <p class="text-xs text-gray-400 mt-2">Belum ada logo</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-6 border-t border-white/10">
                        <button type="button" @click="closeModal"
                            class="inline-flex items-center bg-gradient-to-r from-gray-600 to-gray-700 text-white font-semibold py-3 px-6 rounded-full text-sm shadow-lg hover:scale-105 hover:from-gray-700 hover:to-gray-800 transition-all duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Batal
                        </button>
                        <button type="submit" :disabled="form.processing"
                            class="inline-flex items-center bg-gradient-to-r from-blue-400 to-blue-500 text-white font-bold py-3 px-8 rounded-full text-sm shadow-xl hover:scale-105 hover:from-blue-500 hover:to-blue-600 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
