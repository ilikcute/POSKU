<template>
    <AuthenticatedLayout>
        <Head title="Role & Permission" />
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Manajemen Role & Permission
            </h2>
        </template>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <button @click="openModal(false)" class="inline-flex items-center px-4 py-2 border border-indigo-300 text-xs font-medium rounded text-indigo-600 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Role
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-bold text-gray-700 uppercase tracking-wide">Role</th>
                                <th class="px-6 py-3 text-left text-sm font-bold text-gray-700 uppercase tracking-wide">Permissions</th>
                                <th class="px-6 py-3 text-center text-sm font-bold text-gray-700 uppercase tracking-wide">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="role in roles" :key="role.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ role.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span v-for="perm in role.permissions" :key="perm.id" class="inline-block bg-gray-100 text-gray-700 rounded px-2 py-1 text-xs mr-1 mb-1">{{ perm.name }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-2">
                                        <button @click="openModal(true, role)" class="inline-flex items-center px-2 py-1 border border-gray-300 text-xs font-medium rounded text-indigo-600 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Edit
                                        </button>
                                        <button @click="deleteRole(role.id)" class="inline-flex items-center px-2 py-1 border border-red-300 text-xs font-medium rounded text-red-600 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="roles.length === 0">
                                <td colspan="3" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        <p class="text-lg font-medium text-gray-900">Belum ada data role</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <Modal :show="isModalOpen" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    {{ isEditMode ? "Edit Role" : "Tambah Role" }}
                </h2>
                <form @submit.prevent="saveRole" class="mt-6 space-y-4">
                    <div>
                        <InputLabel for="name" value="Nama Role" />
                        <TextInput id="name" v-model="form.name" class="mt-1 block w-full" placeholder="Nama role" required />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel value="Permissions" />
                        <div class="grid grid-cols-2 gap-2">
                            <label v-for="perm in allPermissions" :key="perm.id" class="flex items-center space-x-2">
                                <input type="checkbox" :value="perm.id" v-model="form.permissions" />
                                <span>{{ perm.name }}</span>
                            </label>
                        </div>
                        <InputError :message="form.errors.permissions" class="mt-2" />
                    </div>
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" @click="closeModal" class="inline-flex items-center px-2 py-1 border border-gray-300 text-xs font-medium rounded text-indigo-600 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Batal
                        </button>
                        <button class="ms-3 inline-flex items-center px-2 py-1 border border-indigo-300 text-xs font-medium rounded text-indigo-600 bg-white hover:bg-indigo-50" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
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
