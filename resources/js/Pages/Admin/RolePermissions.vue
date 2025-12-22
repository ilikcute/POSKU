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
                    <h2 class="font-semibold text-lg text-[#1f1f1f] leading-tight">
                        Manajemen Role & Permission
                    </h2>
                    <p class="text-xs text-[#555] mt-1">
                        Atur peran tim dan hak akses dengan tampilan yang konsisten.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="flex bg-[#f7f7f7] border border-[#9c9c9c] rounded p-1">
                        <button @click="viewMode = 'table'" :class="[
                            'px-3 py-2 rounded text-xs font-semibold transition-colors',
                            viewMode === 'table'
                                ? 'bg-white text-[#1f1f1f]'
                                : 'text-[#1f1f1f] hover:text-[#1f1f1f] hover:bg-[#f7f7f7]'
                        ]">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 10h18M3 6h18m-9 10h9" />
                            </svg>
                            Table
                        </button>
                        <button @click="viewMode = 'cards'" :class="[
                            'px-3 py-2 rounded text-xs font-semibold transition-colors',
                            viewMode === 'cards'
                                ? 'bg-white text-[#1f1f1f]'
                                : 'text-[#1f1f1f] hover:text-[#1f1f1f] hover:bg-[#f7f7f7]'
                        ]">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14-7H5a2 2 0 00-2 2v12a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2z" />
                            </svg>
                            Cards
                        </button>
                    </div>

                    <button @click="openModal(false)"
                        class="inline-flex items-center justify-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] text-xs font-semibold py-3 px-6 rounded text-xs shadow-sm hover:bg-white transition-colors">
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
                    class=" bg-white border border-[#9c9c9c] rounded shadow-sm overflow-hidden">
                    <div class="hidden lg:block overflow-x-auto">
                        <table class="min-w-full text-xs text-[#1f1f1f]">
                            <thead
                                class="bg-[#efefef]  border-b border-[#9c9c9c]">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold text-[#1f1f1f]">
                                        Role
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold text-[#1f1f1f]">
                                        Permissions
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs uppercase tracking-wider font-semibold text-[#1f1f1f]">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#d0d0d0]">
                                <tr v-for="role in roles" :key="role.id"
                                    class="hover:bg-[#f7f7f7] transition-colors group">
                                    <td class="px-6 py-3">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-10 h-10 rounded bg-[#dbe7ff] border border-[#9c9c9c] flex items-center justify-center text-xs font-bold">
                                                {{ getRoleInitial(role.name) }}
                                            </div>
                                            <div>
                                                <p class="font-semibold text-[#1f1f1f]">{{ role.name }}</p>
                                                <p class="text-xs text-[#555]">
                                                    {{ role.permissions.length }} hak akses
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3">
                                        <div class="flex flex-wrap gap-2">
                                            <span v-if="!role.permissions.length"
                                                class="px-3 py-1 text-xs font-medium rounded border border-dashed border-[#9c9c9c] text-[#555] bg-[#f7f7f7]">
                                                Belum ada permission
                                            </span>
                                            <span v-for="perm in role.permissions" :key="perm.id"
                                                class="px-3 py-1 text-xs font-medium rounded bg-white border border-[#9c9c9c] text-[#1f1f1f] group-hover:border-[#9c9c9c]">
                                                {{ perm.name }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3">
                                        <div class="flex justify-end gap-2">
                                            <button @click="openModal(true, role)"
                                                class="inline-flex items-center px-4 py-2 rounded text-xs font-bold bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] shadow-sm hover:bg-white transition-colors">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </button>
                                            <button @click="deleteRole(role.id)"
                                                class="inline-flex items-center px-4 py-2 rounded text-xs font-bold bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] shadow-sm hover:bg-white transition-colors">
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
                        <table class="min-w-full text-xs text-[#1f1f1f]">
                            <thead
                                class="bg-[#efefef]  border-b border-[#9c9c9c]">
                                <tr>
                                    <th class="px-3 py-3 text-left font-semibold text-[#1f1f1f]">Role</th>
                                    <th class="px-3 py-3 text-left font-semibold text-[#1f1f1f]">Permissions</th>
                                    <th class="px-3 py-3 text-left font-semibold text-[#1f1f1f]">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#d0d0d0]">
                                <tr v-for="role in roles" :key="role.id" class="hover:bg-[#f7f7f7]">
                                    <td class="px-3 py-3 align-top">
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="w-8 h-8 rounded bg-[#dbe7ff] border border-[#9c9c9c] flex items-center justify-center text-xs font-bold">
                                                {{ getRoleInitial(role.name) }}
                                            </div>
                                            <div>
                                                <p class="font-medium text-[#1f1f1f] text-xs">{{ role.name }}</p>
                                                <p class="text-[11px] text-[#555]">
                                                    {{ role.permissions.length }} hak akses
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="flex flex-wrap gap-1">
                                            <span v-if="!role.permissions.length"
                                                class="px-2 py-0.5 text-[11px] font-medium rounded border border-dashed border-[#9c9c9c] text-[#555] bg-[#f7f7f7]">
                                                Belum ada
                                            </span>
                                            <span v-for="perm in role.permissions" :key="perm.id"
                                                class="px-2 py-0.5 text-[11px] font-medium rounded bg-white border border-[#9c9c9c] text-[#1f1f1f]">
                                                {{ perm.name }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="flex flex-col gap-2">
                                            <button @click="openModal(true, role)"
                                                class="inline-flex items-center justify-center px-3 py-2 rounded text-[11px] font-bold bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] shadow-sm">
                                                Edit
                                            </button>
                                            <button @click="deleteRole(role.id)"
                                                class="inline-flex items-center justify-center px-3 py-2 rounded text-[11px] font-bold bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] shadow-sm">
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
                        class=" bg-white border border-[#9c9c9c] rounded p-6 shadow-sm hover:shadow-sm hover:bg-[#f7f7f7] transition-all duration-300 hover:-translate-y-1">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 rounded bg-[#dbe7ff] border border-[#9c9c9c] flex items-center justify-center text-lg font-bold">
                                    {{ getRoleInitial(role.name) }}
                                </div>
                                <div>
                                    <h3 class="font-semibold text-[#1f1f1f]">{{ role.name }}</h3>
                                    <p class="text-xs text-[#555]">
                                        {{ role.permissions.length }} hak akses terhubung
                                    </p>
                                </div>
                            </div>
                            <span
                                class="inline-flex px-3 py-1 rounded text-xs font-medium border border-[#9c9c9c] bg-[#f7f7f7] text-[#1f1f1f]">
                                Role
                            </span>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <p class="text-xs uppercase tracking-wider text-[#555] mb-2">
                                    Permissions
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    <span v-if="!role.permissions.length"
                                        class="px-3 py-1 text-xs font-medium rounded border border-dashed border-[#9c9c9c] text-[#555] bg-[#f7f7f7]">
                                        Belum ada permission ditetapkan
                                    </span>
                                    <span v-for="perm in role.permissions" :key="perm.id"
                                        class="px-3 py-1 text-xs font-medium rounded bg-white border border-[#9c9c9c] text-[#1f1f1f]">
                                        {{ perm.name }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-3 pt-4 border-t border-[#9c9c9c]">
                                <button @click="openModal(true, role)"
                                    class="inline-flex items-center px-4 py-2 rounded text-xs font-bold bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] shadow-sm hover:bg-white transition-colors">
                                    <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit Role
                                </button>
                                <button @click="deleteRole(role.id)"
                                    class="inline-flex items-center px-4 py-2 rounded text-xs font-bold bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] shadow-sm hover:bg-white transition-colors">
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

            <div v-else class="text-center py-12  bg-white border border-[#9c9c9c] rounded">
                <svg class="mx-auto h-12 w-12 text-[#555] mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="text-lg font-medium text-[#1f1f1f] mb-2">Belum ada data role</h3>
                <p class="text-[#555]">Tambahkan role baru untuk mulai mengatur permission.</p>
            </div>
        </div>

        <Modal :show="isModalOpen" @close="closeModal">
            <div
                class="p-8 bg-[#f7f7f7]  border border-[#9c9c9c] rounded shadow-sm max-w-3xl mx-auto space-y-6 text-[#1f1f1f]">
                <h2 class="text-lg font-semibold">
                    {{ isEditMode ? 'Edit Role' : 'Tambah Role' }}
                </h2>

                <form @submit.prevent="saveRole" class="space-y-6">
                    <div>
                        <InputLabel for="name" value="Nama Role" class="text-[#1f1f1f]" />
                        <TextInput id="name" v-model="form.name"
                            class="mt-1 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Nama role" required />
                        <InputError :message="form.errors.name" class="mt-2 text-red-600" />
                    </div>

                    <div>
                        <InputLabel value="Permissions" class="text-[#1f1f1f]" />
                        <div class="mt-2 max-h-64 overflow-y-auto p-3 bg-white rounded border border-[#9c9c9c]">
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 text-[#1f1f1f]">
                                <label v-for="permission in allPermissions" :key="permission.id"
                                    class="flex items-center">
                                    <input type="checkbox" :value="permission.id" v-model="form.permissions"
                                        class="rounded border-[#9c9c9c] text-[#1f1f1f] bg-white focus:border-blue-400 focus:ring-blue-200 focus:ring-opacity-50" />
                                    <span class="ml-2 text-xs">{{ permission.name }}</span>
                                </label>
                            </div>
                        </div>
                        <InputError :message="form.errors.permissions" class="mt-2 text-red-600" />
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="closeModal"
                            class="inline-flex items-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] text-xs font-semibold py-2 px-6 rounded text-xs shadow-sm hover:bg-white transition-colors">
                            <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Batal
                        </button>
                        <button
                            class="inline-flex items-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] text-xs font-semibold py-2 px-6 rounded text-xs shadow-sm hover:bg-white transition-colors"
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
