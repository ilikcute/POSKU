<template>
    <AuthenticatedLayout>

        <Head title="Role & Permission" />
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">
                Manajemen Role & Permission
            </h2>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl shadow-lg overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-4 border-b border-gray-600/60">
                        <h3 class="text-lg font-medium text-white">Daftar Role</h3>
                        <button @click="openModal(false)"
                            class="inline-block bg-gradient-to-r from-green-400 to-emerald-400 text-white font-bold py-3 px-10 rounded-full text-sm md:text-base shadow-xl hover:scale-105 hover:from-green-500 hover:to-emerald-500 transition-transform duration-200">
                            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Tambah Role
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm text-gray-200 divide-y divide-gray-600">
                            <thead class="bg-gray-800/70">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wider">Role</th>
                                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wider">Permissions</th>
                                    <th class="px-4 py-3 text-center text-xs uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                <tr v-for="role in roles" :key="role.id"
                                    class="hover:bg-gray-700/50 transition-colors duration-150">
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-gray-100">{{ role.name }}
                                    </td>
                                    <td class="px-4 py-3 align-top">
                                        <div class="flex flex-wrap gap-1">
                                            <span v-for="perm in role.permissions" :key="perm.id"
                                                class="px-2 py-0.5 text-xs rounded bg-gray-800/60 text-gray-200 border border-white/10">{{
                                                perm.name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end space-x-2">
                                            <button @click="openModal(true, role)"
                                                class="inline-block bg-gradient-to-r from-blue-400 to-blue-500 text-white font-bold py-2 px-4 rounded-full text-xs shadow-lg hover:scale-105 hover:from-blue-500 hover:to-blue-600 transition-transform duration-200">
                                                <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </button>
                                            <button @click="deleteRole(role.id)"
                                                class="inline-block bg-gradient-to-r from-red-400 to-red-500 text-white font-bold py-2 px-4 rounded-full text-xs shadow-lg hover:scale-105 hover:from-red-500 hover:to-red-600 transition-transform duration-200">
                                                <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="roles.length === 0">
                                    <td colspan="3" class="px-6 py-12 text-center text-gray-400">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-12 h-12 text-gray-500 mb-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                                </path>
                                            </svg>
                                            <p class="text-lg font-medium text-gray-300">Belum ada data role</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <Modal :show="isModalOpen" @close="closeModal">
            <div
                class="p-8 bg-gray-900/70 backdrop-blur-md border border-white/10 rounded-2xl shadow-lg max-w-3xl mx-auto space-y-6 text-white">
                <h2 class="text-lg font-semibold">{{ isEditMode ? "Edit Role" : "Tambah Role" }}</h2>
                <form @submit.prevent="saveRole" class="space-y-6">
                    <div>
                        <InputLabel for="name" value="Nama Role" class="text-gray-300" />
                        <TextInput id="name" v-model="form.name"
                            class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Nama role" required />
                        <InputError :message="form.errors.name" class="mt-2 text-red-400" />
                    </div>
                    <div>
                        <InputLabel value="Permissions" class="text-gray-300" />
                        <div class="mt-2 max-h-64 overflow-y-auto p-2 bg-white/5 rounded-lg border border-white/10">
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 text-gray-200">
                                <label v-for="perm in allPermissions" :key="perm.id" class="flex items-center">
                                    <input type="checkbox" :value="perm.id" v-model="form.permissions"
                                        class="rounded border-gray-600 text-blue-400 bg-gray-800 focus:border-blue-400 focus:ring-blue-200 focus:ring-opacity-50" />
                                    <span class="ml-2 text-sm">{{ perm.name }}</span>
                                </label>
                            </div>
                        </div>
                        <InputError :message="form.errors.permissions" class="mt-2 text-red-400" />
                    </div>
                    <div class="flex justify-end space-x-3 pt-2">
                        <button type="button" @click="closeModal"
                            class="inline-block bg-gradient-to-r from-blue-400 to-blue-500 text-white font-bold py-2 px-6 rounded-full text-sm shadow-lg hover:scale-105 hover:from-blue-500 hover:to-blue-600 transition-transform duration-200">
                            <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Batal
                        </button>
                        <button
                            class="inline-block bg-gradient-to-r from-green-400 to-emerald-400 text-white font-bold py-2 px-6 rounded-full text-sm shadow-lg hover:scale-105 hover:from-green-500 hover:to-emerald-500 transition-transform duration-200"
                            :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            {{ isEditMode ? 'Update' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    roles: Array,
    allPermissions: Array,
});

const isModalOpen = ref(false);
const isEditMode = ref(false);

const form = useForm({
    id: null,
    name: '',
    permissions: [],
});

function openModal(editMode = false, role = null) {
    isEditMode.value = editMode;
    if (editMode && role) {
        form.id = role.id;
        form.name = role.name;
        form.permissions = role.permissions.map(p => p.id);
    } else {
        form.reset();
    }
    isModalOpen.value = true;
}

function closeModal() {
    isModalOpen.value = false;
    form.reset();
}

function saveRole() {
    if (isEditMode.value) {
        form.patch(route('admin.roles.update', form.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.roles.store'), {
            onSuccess: () => closeModal(),
        });
    }
}

function deleteRole(id) {
    if (confirm('Yakin ingin menghapus role ini?')) {
        useForm({}).delete(route('admin.roles.destroy', id), {
            onSuccess: () => closeModal(),
        });
    }
}
</script>
