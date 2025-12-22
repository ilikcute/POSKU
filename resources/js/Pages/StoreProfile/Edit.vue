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

// State untuk preview gambar baru
const logoPreview = ref(null);
const heroimagePreview = ref(null);
const faviconPreview = ref(null);

// Inisialisasi form dengan data toko dari controller
const form = useForm({
    name: props.store.name,
    address: props.store.address,
    phone: props.store.phone,
    email: props.store.email,
    website: props.store.website,
    tax: props.store.tax,
    logo: null,
    heroimage: null,
    favicon: null,
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

const handleHeroimageChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.heroimage = file;
        // Create preview URL
        heroimagePreview.value = URL.createObjectURL(file);
    } else {
        form.heroimage = null;
        heroimagePreview.value = null;
    }
};

const handleFaviconChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.favicon = file;
        // Create preview URL
        faviconPreview.value = URL.createObjectURL(file);
    } else {
        form.favicon = null;
        faviconPreview.value = null;
    }
};

const openModal = () => {
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset('logo');
    form.reset('heroimage');
    form.reset('favicon');
    logoPreview.value = null;
    heroimagePreview.value = null;
    faviconPreview.value = null;
};

const saveStoreProfile = () => {
    form.processing = true;

    // Always use FormData for file uploads
    const formData = new FormData();
    formData.append('name', form.name);
    formData.append('address', form.address || '');
    formData.append('phone', form.phone || '');
    formData.append('email', form.email || '');
    formData.append('website', form.website || '');
    formData.append('tax', form.tax || '');
    formData.append('_method', 'PATCH');

    if (form.logo) {
        formData.append('logo', form.logo);
    }

    if (form.heroimage) {
        formData.append('heroimage', form.heroimage);
    }

    if (form.favicon) {
        formData.append('favicon', form.favicon);
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
                    <h2 class="font-semibold text-lg text-[#1f1f1f] leading-tight">
                        Profil Toko
                    </h2>
                    <p class="text-xs text-[#555] mt-1">
                        Kelola informasi dan pengaturan toko dengan tampilan konsisten.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="flex bg-[#f7f7f7] border border-[#9c9c9c] rounded p-1">
                        <button @click="viewMode = 'form'" :class="[
                            'px-3 py-2 rounded text-xs font-semibold transition-colors',
                            viewMode === 'form'
                                ? 'bg-white text-[#1f1f1f]'
                                : 'text-[#555] hover:text-[#1f1f1f] hover:bg-[#f0f0f0]'
                        ]">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Form
                        </button>
                        <button @click="viewMode = 'preview'" :class="[
                            'px-3 py-2 rounded text-xs font-semibold transition-colors',
                            viewMode === 'preview'
                                ? 'bg-white text-[#1f1f1f]'
                                : 'text-[#555] hover:text-[#1f1f1f] hover:bg-[#f0f0f0]'
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
                        class="inline-flex items-center justify-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] font-semibold py-3 px-6 rounded text-xs hover:bg-white transition-colors">
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
                class="mb-6 p-4 bg-[#f7f7f7] border border-[#9c9c9c] rounded ">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-green-600 font-medium">Profil toko berhasil diperbarui!</p>
                </div>
            </div>

            <!-- Store Profile Display -->
            <div v-if="viewMode === 'preview'"
                class="bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-sm p-8">
                <div class="text-center mb-8">
                    <div
                        class="w-24 h-24 mx-auto mb-4 rounded bg-[#dbe7ff] border border-[#9c9c9c] flex items-center justify-center">
                        <svg v-if="!props.store.logo_path" class="w-12 h-12 text-[#1f1f1f]" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <img v-else :src="`/storage/${props.store.logo_path}`" alt="Logo Toko"
                            class="w-full h-full object-cover rounded" />
                    </div>
                    <h1 class="text-3xl font-bold text-[#1f1f1f] mb-2">{{ props.store.name }}</h1>
                    <div class="space-y-2 text-[#555]">
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
            <div v-else class="text-center py-12  bg-white border border-[#9c9c9c] rounded">
                <svg class="mx-auto h-12 w-12 text-[#555] mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <h3 class="text-sm font-semibold text-[#555] mb-2">Kelola Profil Toko</h3>
                <p class="text-[#555] mb-6">Klik tombol "Edit Profil" untuk memperbarui informasi toko Anda.</p>
                <button @click="openModal"
                    class="inline-flex items-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] font-semibold py-3 px-6 rounded text-xs hover:bg-white transition-colors">
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
                class="p-8 bg-[#f7f7f7]  border border-[#9c9c9c] rounded shadow-sm max-w-2xl mx-auto space-y-6 text-[#1f1f1f]">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded bg-[#dbe7ff] border border-[#9c9c9c] flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <h2
                        class="text-sm font-semibold text-[#1f1f1f]">
                        Edit Profil Toko
                    </h2>
                </div>

                <form @submit.prevent="saveStoreProfile" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <InputLabel for="name" value="Nama Toko *" class="text-[#555] font-semibold" />
                            <TextInput id="name" v-model="form.name"
                                class="mt-2 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] placeholder-gray-400 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Masukkan nama toko" required />
                            <InputError :message="form.errors?.name" class="mt-2 text-red-400" />
                        </div>

                        <div>
                            <InputLabel for="address" value="Alamat Toko" class="text-[#555] font-semibold" />
                            <textarea id="address" v-model="form.address" rows="3"
                                class="mt-2 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] placeholder-gray-400 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Masukkan alamat lengkap toko"></textarea>
                            <InputError :message="form.errors?.address" class="mt-2 text-red-400" />
                        </div>

                        <div>
                            <InputLabel for="phone" value="Nomor Telepon" class="text-[#555] font-semibold" />
                            <TextInput id="phone" v-model="form.phone"
                                class="mt-2 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] placeholder-gray-400 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Contoh: 081234567890" />
                            <InputError :message="form.errors?.phone" class="mt-2 text-red-400" />
                        </div>

                        <div>
                            <InputLabel for="email" value="Email Toko" class="text-[#555] font-semibold" />
                            <TextInput id="email" v-model="form.email" type="email"
                                class="mt-2 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] placeholder-gray-400 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="contoh@email.com" />
                            <InputError :message="form.errors?.email" class="mt-2 text-red-400" />
                        </div>

                        <div>
                            <InputLabel for="website" value="Website Toko" class="text-[#555] font-semibold" />
                            <TextInput id="website" v-model="form.website"
                                class="mt-2 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] placeholder-gray-400 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="https://www.tokoanda.com" />
                            <InputError :message="form.errors?.website" class="mt-2 text-red-400" />
                        </div>

                        <div>
                            <InputLabel for="tax" value="Nomor Pajak" class="text-[#555] font-semibold" />
                            <TextInput id="tax" v-model="form.tax"
                                class="mt-2 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] placeholder-gray-400 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Contoh: 1234567890" />
                            <InputError :message="form.errors?.tax" class="mt-2 text-red-400" />
                        </div>

                        <div>
                            <InputLabel for="logo" value="Logo Toko" class="text-[#555] font-semibold" />
                            <input id="logo" type="file" accept="image/*"
                                class="mt-2 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-blue-500 file:text-[#1f1f1f] hover:file:bg-blue-600"
                                @change="handleLogoChange" />
                            <InputError :message="form.errors?.logo" class="mt-2 text-red-400" />
                            <p class="mt-2 text-xs text-[#555]">
                                Pilih gambar logo toko Anda (format: JPG, PNG, GIF. Maksimal 2MB).
                            </p>

                            <!-- Logo Preview -->
                            <div class="mt-4">
                                <p class="text-xs font-medium text-[#555] mb-3">Preview Logo:</p>
                                <div class="flex items-center space-x-6">
                                    <!-- Current Logo (if exists and no new file selected) -->
                                    <div v-if="!logoPreview && props.store.logo_path" class="text-center">
                                        <p class="text-xs text-[#777] mb-2">Logo Saat Ini</p>
                                        <img :src="`/storage/${props.store.logo_path}`" alt="Logo Toko Saat Ini"
                                            class="h-20 w-auto object-contain border border-[#9c9c9c] rounded shadow-sm"
                                            @error="$event.target.style.display = 'none'" />
                                    </div>

                                    <!-- New Logo Preview -->
                                    <div v-if="logoPreview" class="text-center">
                                        <p class="text-xs text-[#777] mb-2">Logo Baru</p>
                                        <img :src="logoPreview" alt="Preview Logo Baru"
                                            class="h-20 w-auto object-contain border border-[#9c9c9c] rounded shadow-sm" />
                                    </div>

                                    <!-- No Logo State -->
                                    <div v-if="!logoPreview && !props.store.logo_path" class="text-center">
                                        <div
                                            class="h-20 w-20 bg-gray-700 border border-[#9c9c9c] rounded flex items-center justify-center">
                                            <svg class="w-8 h-8 text-[#555]" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <p class="text-xs text-[#555] mt-2">Belum ada logo</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <InputLabel for="heroimage" value="Gambar Hero Toko" class="text-[#555] font-semibold" />
                            <input id="heroimage" type="file" accept="image/*"
                                class="mt-2 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-green-500 file:text-[#1f1f1f] hover:file:bg-green-600"
                                @change="handleHeroimageChange" />
                            <InputError :message="form.errors?.heroimage" class="mt-2 text-red-400" />
                            <p class="mt-2 text-xs text-[#555]">
                                Pilih gambar hero untuk halaman welcome (format: JPG, PNG, GIF. Maksimal 2MB).
                            </p>

                            <!-- Hero Image Preview -->
                            <div class="mt-4">
                                <p class="text-xs font-medium text-[#555] mb-3">Preview Gambar Hero:</p>
                                <div class="flex items-center space-x-6">
                                    <!-- Current Hero Image (if exists and no new file selected) -->
                                    <div v-if="!heroimagePreview && props.store.heroimage_path" class="text-center">
                                        <p class="text-xs text-[#777] mb-2">Gambar Hero Saat Ini</p>
                                        <img :src="`/storage/${props.store.heroimage_path}`"
                                            alt="Gambar Hero Toko Saat Ini"
                                            class="h-20 w-auto object-contain border border-[#9c9c9c] rounded shadow-sm"
                                            @error="$event.target.style.display = 'none'" />
                                    </div>

                                    <!-- New Hero Image Preview -->
                                    <div v-if="heroimagePreview" class="text-center">
                                        <p class="text-xs text-[#777] mb-2">Gambar Hero Baru</p>
                                        <img :src="heroimagePreview" alt="Preview Gambar Hero Baru"
                                            class="h-20 w-auto object-contain border border-[#9c9c9c] rounded shadow-sm" />
                                    </div>

                                    <!-- No Hero Image State -->
                                    <div v-if="!heroimagePreview && !props.store.heroimage_path" class="text-center">
                                        <div
                                            class="h-20 w-20 bg-gray-700 border border-[#9c9c9c] rounded flex items-center justify-center">
                                            <svg class="w-8 h-8 text-[#555]" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <p class="text-xs text-[#555] mt-2">Belum ada gambar hero</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <InputLabel for="favicon" value="Favicon Toko" class="text-[#555] font-semibold" />
                            <input id="favicon" type="file" accept="image/*"
                                class="mt-2 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-purple-500 file:text-[#1f1f1f] hover:file:bg-purple-600"
                                @change="handleFaviconChange" />
                            <InputError :message="form.errors?.favicon" class="mt-2 text-red-400" />
                            <p class="mt-2 text-xs text-[#555]">
                                Pilih gambar favicon untuk browser tab (format: ICO, PNG. Maksimal 512KB).
                            </p>

                            <!-- Favicon Preview -->
                            <div class="mt-4">
                                <p class="text-xs font-medium text-[#555] mb-3">Preview Favicon:</p>
                                <div class="flex items-center space-x-6">
                                    <!-- Current Favicon (if exists and no new file selected) -->
                                    <div v-if="!faviconPreview && props.store.favicon_path" class="text-center">
                                        <p class="text-xs text-[#777] mb-2">Favicon Saat Ini</p>
                                        <img :src="`/storage/${props.store.favicon_path}`" alt="Favicon Toko Saat Ini"
                                            class="h-8 w-8 object-contain border border-[#9c9c9c] rounded shadow-sm"
                                            @error="$event.target.style.display = 'none'" />
                                    </div>

                                    <!-- New Favicon Preview -->
                                    <div v-if="faviconPreview" class="text-center">
                                        <p class="text-xs text-[#777] mb-2">Favicon Baru</p>
                                        <img :src="faviconPreview" alt="Preview Favicon Baru"
                                            class="h-8 w-8 object-contain border border-[#9c9c9c] rounded shadow-sm" />
                                    </div>

                                    <!-- No Favicon State -->
                                    <div v-if="!faviconPreview && !props.store.favicon_path" class="text-center">
                                        <div
                                            class="h-8 w-8 bg-gray-700 border border-[#9c9c9c] rounded flex items-center justify-center">
                                            <svg class="w-4 h-4 text-[#555]" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <p class="text-xs text-[#555] mt-2">Belum ada favicon</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-6 border-t border-[#9c9c9c]">
                        <button type="button" @click="closeModal"
                            class="inline-flex items-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] font-semibold py-3 px-6 rounded text-xs hover:bg-white transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Batal
                        </button>
                        <button type="submit" :disabled="form.processing"
                            class="inline-flex items-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] font-semibold py-3 px-8 rounded text-xs hover:bg-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
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
