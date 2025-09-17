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
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Profil Toko</h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">Informasi Toko</h2>
                            <p class="mt-1 text-sm text-gray-600">
                                Perbarui nama, alamat, dan informasi kontak toko Anda.
                            </p>
                        </header>

                        <form @submit.prevent="submit" class="mt-6 space-y-6">
                            <div>
                                <InputLabel for="name" value="Nama Toko" />
                                <TextInput
                                    id="name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.name"
                                    required
                                    autofocus
                                />
                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>

                            <div>
                                <InputLabel for="address" value="Alamat Toko" />
                                <textarea
                                    id="address"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    rows="3"
                                    v-model="form.address"
                                    placeholder="Masukkan alamat lengkap toko"
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.address" />
                            </div>

                            <div>
                                <InputLabel for="phone" value="Nomor Telepon" />
                                <TextInput
                                    id="phone"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.phone"
                                    placeholder="Contoh: 081234567890"
                                />
                                <InputError class="mt-2" :message="form.errors.phone" />
                            </div>

                            <div>
                                <InputLabel for="logo" value="Logo Toko" />
                                <input
                                    id="logo"
                                    type="file"
                                    accept="image/*"
                                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                    @change="handleLogoChange"
                                />
                                <InputError class="mt-2" :message="form.errors.logo" />
                                <p class="mt-2 text-sm text-gray-500">
                                    Pilih gambar logo toko Anda (format: JPG, PNG, GIF. Maksimal 2MB).
                                </p>
                                
                                <!-- Logo Preview -->
                                <div class="mt-4">
                                    <p class="text-sm font-medium text-gray-700 mb-2">Preview Logo:</p>
                                    <div class="flex items-center space-x-4">
                                        <!-- Current Logo (if exists and no new file selected) -->
                                        <div v-if="!logoPreview && props.store.logo_path" class="text-center">
                                            <p class="text-xs text-gray-500 mb-1">Logo Saat Ini</p>
                                            <img
                                                :src="`/storage/${props.store.logo_path}`"
                                                alt="Logo Toko Saat Ini"
                                                class="h-20 w-auto object-contain border rounded shadow-sm"
                                                @error="$event.target.style.display='none'"
                                            />
                                        </div>
                                        
                                        <!-- New Logo Preview -->
                                        <div v-if="logoPreview" class="text-center">
                                            <p class="text-xs text-gray-500 mb-1">Logo Baru</p>
                                            <img
                                                :src="logoPreview"
                                                alt="Preview Logo Baru"
                                                class="h-20 w-auto object-contain border rounded shadow-sm"
                                            />
                                        </div>
                                        
                                        <!-- No Logo State -->
                                        <div v-if="!logoPreview && !props.store.logo_path" class="text-center">
                                            <div class="h-20 w-20 bg-gray-100 border rounded flex items-center justify-center">
                                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-1">Belum ada logo</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <button @click="router.visit(route('dashboard'))"
                                    class="inline-flex items-center px-2 py-1 border border-gray-300 text-xs font-medium rounded text-indigo-600 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Batal
                                </button>
                                <button type="submit" :disabled="form.processing"
                                    class="ms-3 inline-flex items-center px-2 py-1 border border-indigo-300 text-xs font-medium rounded text-indigo-600 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                                </button>
                                <Transition
                                    enter-active-class="transition ease-in-out"
                                    enter-from-class="opacity-0"
                                    leave-active-class="transition ease-in-out"
                                    leave-to-class="opacity-0"
                                >
                                    <p v-if="form.recentlySuccessful" class="text-sm text-green-600 font-medium">
                                        ✓ Berhasil disimpan!
                                    </p>
                                </Transition>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>