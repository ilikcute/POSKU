<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    roles: {
        type: Array,
        default: () => [],
    },
    allPermissions: {
        type: Array,
        default: () => [],
    },
});

const viewMode = ref('table');
const isModalOpen = ref(false);
const isEditMode = ref(false);

const form = useForm({
    id: null,
    name: '',
    permissions: [],
});

const getRoleInitial = (name) => {
    if (!name) {
        return '?';
    }

    return name.charAt(0).toUpperCase();
};

function openModal(editMode = false, role = null) {
    isEditMode.value = editMode;

    if (editMode && role) {
        form.id = role.id;
        form.name = role.name;
        form.permissions = role.permissions.map((permission) => permission.id);
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

<template>
    <AuthenticatedLayout>

        <Head title="Role & Permission" />

        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-xl text-white leading-tight">
                        Manajemen Role & Permission
                    </h2>
                    <p class="text-sm text-gray-400 mt-1">
                        Atur peran tim dan hak akses dengan tampilan yang konsisten.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="flex bg-gray-800/50 rounded-lg p-1 backdrop-blur-sm">
                        <button @click="viewMode = 'table'" :class="[
                            'px-3 py-2 rounded-md text-sm font-medium transition-all duration-200',
                            viewMode === 'table'
                                ? 'bg-white/20 text-white shadow-sm'
                                : 'text-gray-300 hover:text-white hover:bg-white/10'
                        ]">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 10h18M3 6h18m-9 10h9" />
                            </svg>
                            Table
                        </button>
                        <button @click="viewMode = 'cards'" :class="[
                            'px-3 py-2 rounded-md text-sm font-medium transition-all duration-200',
                            viewMode === 'cards'
                                ? 'bg-white/20 text-white shadow-sm'
                                : 'text-gray-300 hover:text-white hover:bg-white/10'
                        ]">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14-7H5a2 2 0 00-2 2v12a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2z" />
                            </svg>
                            Cards
                        </button>
                    </div>

                    <button @click="openModal(false)"
                        class="inline-flex items-center justify-center bg-gradient-to-r from-green-400 to-emerald-400 text-white font-bold py-3 px-6 rounded-full text-sm shadow-xl hover:scale-105 hover:from-green-500 hover:to-emerald-500 transition-transform duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Role
                    </button>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div v-if="roles.length">
                <div v-if="viewMode === 'table'"
                    class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl shadow-xl overflow-hidden">
                    <div class="hidden lg:block overflow-x-auto">
                        <table class="min-w-full text-sm text-gray-200">
                            <thead
                                class="bg-gradient-to-r from-purple-600/30 to-blue-600/30 backdrop-blur-sm border-b border-white/20">
                                <tr>
                                    <th
                                        class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-white/90">
                                        Role
                                    </th>
                                    <th
                                        class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-white/90">
                                        Permissions
                                    </th>
                                    <th
                                        class="px-6 py-4 text-right text-xs uppercase tracking-wider font-semibold text-white/90">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700/50">
                                <tr v-for="role in roles" :key="role.id"
                                    class="hover:bg-white/5 transition-all duration-200 group">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-sm font-bold">
                                                {{ getRoleInitial(role.name) }}
                                            </div>
                                            <div>
                                                <p class="font-semibold text-white">{{ role.name }}</p>
                                                <p class="text-xs text-gray-400">
                                                    {{ role.permissions.length }} hak akses
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            <span v-if="!role.permissions.length"
                                                class="px-3 py-1 text-xs font-medium rounded-full border border-dashed border-white/30 text-gray-400 bg-gray-800/40">
                                                Belum ada permission
                                            </span>
                                            <span v-for="perm in role.permissions" :key="perm.id"
                                                class="px-3 py-1 text-xs font-medium rounded-full bg-white/5 border border-white/10 text-gray-200 group-hover:border-white/30">
                                                {{ perm.name }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-end gap-2">
                                            <button @click="openModal(true, role)"
                                                class="inline-flex items-center px-4 py-2 rounded-full text-xs font-bold bg-gradient-to-r from-blue-400 to-blue-500 text-white shadow-lg hover:scale-105 hover:from-blue-500 hover:to-blue-600 transition-transform duration-200">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </button>
                                            <button @click="deleteRole(role.id)"
                                                class="inline-flex items-center px-4 py-2 rounded-full text-xs font-bold bg-gradient-to-r from-red-400 to-red-500 text-white shadow-lg hover:scale-105 hover:from-red-500 hover:to-red-600 transition-transform duration-200">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="lg:hidden overflow-x-auto">
                        <table class="min-w-full text-xs text-gray-200">
                            <thead
                                class="bg-gradient-to-r from-purple-600/30 to-blue-600/30 backdrop-blur-sm border-b border-white/20">
                                <tr>
                                    <th class="px-3 py-3 text-left font-semibold text-white/90">Role</th>
                                    <th class="px-3 py-3 text-left font-semibold text-white/90">Permissions</th>
                                    <th class="px-3 py-3 text-left font-semibold text-white/90">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700/50">
                                <tr v-for="role in roles" :key="role.id" class="hover:bg-white/5">
                                    <td class="px-3 py-3 align-top">
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-xs font-bold">
                                                {{ getRoleInitial(role.name) }}
                                            </div>
                                            <div>
                                                <p class="font-medium text-white text-sm">{{ role.name }}</p>
                                                <p class="text-[11px] text-gray-400">
                                                    {{ role.permissions.length }} hak akses
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="flex flex-wrap gap-1">
                                            <span v-if="!role.permissions.length"
                                                class="px-2 py-0.5 text-[11px] font-medium rounded-full border border-dashed border-white/30 text-gray-400 bg-gray-800/40">
                                                Belum ada
                                            </span>
                                            <span v-for="perm in role.permissions" :key="perm.id"
                                                class="px-2 py-0.5 text-[11px] font-medium rounded-full bg-white/5 border border-white/10 text-gray-200">
                                                {{ perm.name }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="flex flex-col gap-2">
                                            <button @click="openModal(true, role)"
                                                class="inline-flex items-center justify-center px-3 py-2 rounded-full text-[11px] font-bold bg-gradient-to-r from-blue-400 to-blue-500 text-white shadow-lg">
                                                Edit
                                            </button>
                                            <button @click="deleteRole(role.id)"
                                                class="inline-flex items-center justify-center px-3 py-2 rounded-full text-[11px] font-bold bg-gradient-to-r from-red-400 to-red-500 text-white shadow-lg">
                                                Hapus

                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="role in roles" :key="role.id"
                        class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl p-6 shadow-xl hover:shadow-2xl hover:bg-white/10 transition-all duration-300 hover:-translate-y-1">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-lg font-bold">
                                    {{ getRoleInitial(role.name) }}
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white">{{ role.name }}</h3>
                                    <p class="text-sm text-gray-400">
                                        {{ role.permissions.length }} hak akses terhubung
                                    </p>
                                </div>
                            </div>
                            <span
                                class="inline-flex px-3 py-1 rounded-full text-xs font-medium border border-white/20 bg-white/10 text-gray-200">
                                Role
                            </span>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <p class="text-xs uppercase tracking-wider text-gray-400 mb-2">
                                    Permissions
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    <span v-if="!role.permissions.length"
                                        class="px-3 py-1 text-xs font-medium rounded-full border border-dashed border-white/30 text-gray-400 bg-gray-800/40">
                                        Belum ada permission ditetapkan
                                    </span>
                                    <span v-for="perm in role.permissions" :key="perm.id"
                                        class="px-3 py-1 text-xs font-medium rounded-full bg-white/5 border border-white/10 text-gray-200">
                                        {{ perm.name }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-3 pt-4 border-t border-gray-800/60">
                                <button @click="openModal(true, role)"
                                    class="inline-flex items-center px-4 py-2 rounded-full text-xs font-bold bg-gradient-to-r from-blue-400 to-blue-500 text-white shadow-lg hover:scale-105 hover:from-blue-500 hover:to-blue-600 transition-transform duration-200">
                                    <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit Role
                                </button>
                                <button @click="deleteRole(role.id)"
                                    class="inline-flex items-center px-4 py-2 rounded-full text-xs font-bold bg-gradient-to-r from-red-400 to-red-500 text-white shadow-lg hover:scale-105 hover:from-red-500 hover:to-red-600 transition-transform duration-200">
                                    <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Hapus Role
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-12 backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="text-lg font-medium text-gray-300 mb-2">Belum ada data role</h3>
                <p class="text-gray-400">Tambahkan role baru untuk mulai mengatur permission.</p>
            </div>
        </div>

        <Modal :show="isModalOpen" @close="closeModal">
            <div
                class="p-8 bg-gray-900/70 backdrop-blur-md border border-white/10 rounded-2xl shadow-lg max-w-3xl mx-auto space-y-6 text-white">
                <h2 class="text-lg font-semibold">
                    {{ isEditMode ? 'Edit Role' : 'Tambah Role' }}
                </h2>

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
                        <div class="mt-2 max-h-64 overflow-y-auto p-3 bg-white/5 rounded-lg border border-white/10">
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 text-gray-200">
                                <label v-for="permission in allPermissions" :key="permission.id"
                                    class="flex items-center">
                                    <input type="checkbox" :value="permission.id" v-model="form.permissions"
                                        class="rounded border-gray-600 text-blue-400 bg-gray-800 focus:border-blue-400 focus:ring-blue-200 focus:ring-opacity-50" />
                                    <span class="ml-2 text-sm">{{ permission.name }}</span>
                                </label>
                            </div>
                        </div>
                        <InputError :message="form.errors.permissions" class="mt-2 text-red-400" />
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="closeModal"
                            class="inline-flex items-center bg-gradient-to-r from-blue-400 to-blue-500 text-white font-bold py-2 px-6 rounded-full text-sm shadow-lg hover:scale-105 hover:from-blue-500 hover:to-blue-600 transition-transform duration-200">
                            <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Batal
                        </button>
                        <button
                            class="inline-flex items-center bg-gradient-to-r from-green-400 to-emerald-400 text-white font-bold py-2 px-6 rounded-full text-sm shadow-lg hover:scale-105 hover:from-green-500 hover:to-emerald-500 transition-transform duration-200"
                            :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
