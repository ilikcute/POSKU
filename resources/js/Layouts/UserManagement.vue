<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import SelectInput from '@/Components/SelectInput.vue';

const props = defineProps({
    users: Object,
    roles: Array,
    permissions: Array,
    stores: Array,
});

const page = usePage();
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const showRoleModal = ref(false);
const selectedUser = ref(null);

// Helper function untuk cek permission
const hasPermission = (permission) => {
    const userPermissions = page.props.auth.user.permissions || [];
    const userRoles = page.props.auth.user.roles || [];
    
    if (userRoles.includes('Super Admin')) {
        return true;
    }
    
    return userPermissions.includes(permission);
};

// Form untuk create user
const createForm = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

// Form untuk edit user
const editForm = useForm({
    name: '',
    email: '',
});

// Form untuk assign/revoke role
const roleForm = useForm({
    role: '',
});

// Form untuk delete user
const deleteForm = useForm({});

const submitCreate = () => {
    createForm.post(route('admin.users.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        },
    });
};

const openEditModal = (user) => {
    selectedUser.value = user;
    editForm.name = user.name;
    editForm.email = user.email;
    showEditModal.value = true;
};

const submitEdit = () => {
    editForm.patch(route('admin.users.update', selectedUser.value.id), {
        onSuccess: () => {
            showEditModal.value = false;
            editForm.reset();
        },
    });
};

const openDeleteModal = (user) => {
    selectedUser.value = user;
    showDeleteModal.value = true;
};

const submitDelete = () => {
    deleteForm.delete(route('admin.users.destroy', selectedUser.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
        },
    });
};

const openRoleModal = (user) => {
    selectedUser.value = user;
    roleForm.role = '';
    showRoleModal.value = true;
};

const assignRole = () => {
    roleForm.post(route('admin.users.assign-role', selectedUser.value.id), {
        onSuccess: () => {
            showRoleModal.value = false;
            roleForm.reset();
        },
    });
};

const revokeRole = (user, role) => {
    const form = useForm({ role: role });
    form.post(route('admin.users.revoke-role', user.id));
};
</script>

<template>
    <Head title="User Management" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                User Management
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <!-- Header dengan tombol tambah -->
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium text-gray-900">Daftar User</h3>
                            <PrimaryButton
                                v-if="hasPermission('create_users')"
                                @click="showCreateModal = true"
                                class="flex items-center"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Tambah User
                            </PrimaryButton>
                        </div>

                        <!-- Tabel users -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Nama
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Email
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Roles
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ user.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ user.email }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="flex flex-wrap gap-1">
                                                <span
                                                    v-for="role in user.roles"
                                                    :key="role.id"
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                                >
                                                    {{ role.name }}
                                                    <button
                                                        v-if="hasPermission('manage_roles') && role.name !== 'Super Admin'"
                                                        @click="revokeRole(user, role.name)"
                                                        class="ml-1 text-blue-600 hover:text-blue-800"
                                                    >
                                                        ×
                                                    </button>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <SecondaryButton
                                                    v-if="hasPermission('edit_users')"
                                                    @click="openEditModal(user)"
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    Edit
                                                </SecondaryButton>
                                                <SecondaryButton
                                                    v-if="hasPermission('manage_roles')"
                                                    @click="openRoleModal(user)"
                                                    class="text-green-600 hover:text-green-900"
                                                >
                                                    Role
                                                </SecondaryButton>
                                                <DangerButton
                                                    v-if="hasPermission('delete_users') && user.id !== $page.props.auth.user.id"
                                                    @click="openDeleteModal(user)"
                                                    class="text-red-600 hover:text-red-900"
                                                >
                                                    Delete
                                                </DangerButton>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination jika diperlukan -->
                        <div v-if="users.links" class="mt-4">
                            <nav class="flex items-center justify-between">
                                <div class="flex-1 flex justify-between sm:hidden">
                                    <Link
                                        v-if="users.prev_page_url"
                                        :href="users.prev_page_url"
                                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                    >
                                        Previous
                                    </Link>
                                    <Link
                                        v-if="users.next_page_url"
                                        :href="users.next_page_url"
                                        class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                    >
                                        Next
                                    </Link>
                                </div>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Create User -->
        <Modal :show="showCreateModal" @close="showCreateModal = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Tambah User Baru</h3>
                
                <form @submit.prevent="submitCreate">
                    <div class="mb-4">
                        <InputLabel for="name" value="Nama" />
                        <TextInput
                            id="name"
                            v-model="createForm.name"
                            type="text"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError :message="createForm.errors.name" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <InputLabel for="email" value="Email" />
                        <TextInput
                            id="email"
                            v-model="createForm.email"
                            type="email"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError :message="createForm.errors.email" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <InputLabel for="password" value="Password" />
                        <TextInput
                            id="password"
                            v-model="createForm.password"
                            type="password"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError :message="createForm.errors.password" class="mt-2" />
                    </div>

                    <div class="mb-6">
                        <InputLabel for="password_confirmation" value="Konfirmasi Password" />
                        <TextInput
                            id="password_confirmation"
                            v-model="createForm.password_confirmation"
                            type="password"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError :message="createForm.errors.password_confirmation" class="mt-2" />
                    </div>

                    <div class="flex justify-end space-x-3">
                        <SecondaryButton @click="showCreateModal = false" type="button">
                            Batal
                        </SecondaryButton>
                        <PrimaryButton :disabled="createForm.processing">
                            Simpan
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Modal Edit User -->
        <Modal :show="showEditModal" @close="showEditModal = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Edit User</h3>
                
                <form @submit.prevent="submitEdit">
                    <div class="mb-4">
                        <InputLabel for="edit_name" value="Nama" />
                        <TextInput
                            id="edit_name"
                            v-model="editForm.name"
                            type="text"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError :message="editForm.errors.name" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <InputLabel for="edit_email" value="Email" />
                        <TextInput
                            id="edit_email"
                            v-model="editForm.email"
                            type="email"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError :message="editForm.errors.email" class="mt-2" />
                    </div>

                    <div class="mb-6">
                    <div class="flex justify-end space-x-3">
                        <SecondaryButton @click="showEditModal = false" type="button">
                            Batal
                        </SecondaryButton>
                        <PrimaryButton :disabled="editForm.processing">
                            Update
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Modal Assign Role -->
        <Modal :show="showRoleModal" @close="showRoleModal = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Assign Role untuk {{ selectedUser?.name }}
                </h3>
                
                <form @submit.prevent="assignRole">
                    <div class="mb-6">
                        <InputLabel for="role" value="Pilih Role" />
                        <select
                            id="role"
                            v-model="roleForm.role"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            required
                        >
                            <option value="">Pilih Role</option>
                            <option v-for="role in roles" :key="role.id" :value="role.name">
                                {{ role.name }}
                            </option>
                        </select>
                        <InputError :message="roleForm.errors.role" class="mt-2" />
                    </div>

                    <div class="flex justify-end space-x-3">
                        <SecondaryButton @click="showRoleModal = false" type="button">
                            Batal
                        </SecondaryButton>
                        <PrimaryButton :disabled="roleForm.processing">
                            Assign Role
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Modal Delete User -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Hapus User</h3>
                <p class="text-gray-600 mb-6">
                    Apakah Anda yakin ingin menghapus user <strong>{{ selectedUser?.name }}</strong>? 
                    Tindakan ini tidak dapat dibatalkan.
                </p>

                <div class="flex justify-end space-x-3">
                    <SecondaryButton @click="showDeleteModal = false" type="button">
                        Batal
                    </SecondaryButton>
                    <DangerButton @click="submitDelete" :disabled="deleteForm.processing">
                        Hapus User
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
