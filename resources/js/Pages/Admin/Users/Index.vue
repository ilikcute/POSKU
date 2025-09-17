<script setup>
import { ref, computed, watch } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import Modal from '@/Components/Modal.vue'
import TextInput from '@/Components/TextInput.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
    users: {
        type: Object,
        default: () => ({ data: [], links: [], meta: {} })
    },
    roles: {
        type: Array,
        default: () => []
    },
    permissions: {
        type: Array,
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({})
    },
    can: {
        type: Object,
        default: () => ({})
    }
})

// Reactive variables
const showCreateModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const processing = ref(false)
const search = ref(props.filters.search || '')

// Form data
const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    roles: []
})

const editingUser = ref(null)
const deletingUser = ref(null)

const page = usePage()

// Computed
const usersData = computed(() => {
    if (!props.users || !props.users.data) {
        return []
    }
    return props.users.data
})

// Methods
const openCreateModal = () => {
    resetForm()
    showCreateModal.value = true
}

const openEditModal = (user) => {
    editingUser.value = user
    form.value = {
        name: user.name || '',
        email: user.email || '',
        password: '',
        password_confirmation: '',
        store_id: user.store_id || '',
        roles: user.roles?.map(role => role.name) || []
    }
    showEditModal.value = true
}

const openDeleteModal = (user) => {
    deletingUser.value = user
    showDeleteModal.value = true
}

const closeModals = () => {
    showCreateModal.value = false
    showEditModal.value = false
    showDeleteModal.value = false
    resetForm()
    editingUser.value = null
    deletingUser.value = null
}

const resetForm = () => {
    form.value = {
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        roles: []
    }
}

const createUser = () => {
    processing.value = true
    
    router.post(route('admin.users.store'), form.value, {
        onSuccess: () => {
            closeModals()
        },
        onError: (errors) => {
            console.error('Create errors:', errors)
        },
        onFinish: () => {
            processing.value = false
        }
    })
}

const updateUser = () => {
    if (!editingUser.value) return

    processing.value = true

    const payload = { ...form.value }
    if (!payload.password) {
        delete payload.password
        delete payload.password_confirmation
    }

    router.patch(route('admin.users.update', editingUser.value.id), payload, {
        onSuccess: (page) => {
            // Cek jika ada error pada response
            if (page?.props?.errors && Object.keys(page.props.errors).length > 0) {
                // Error, jangan tutup modal
                processing.value = false
                return
            }
            closeModals()
        },
        onError: (errors) => {
            // Tampilkan error pada UI jika ada
            processing.value = false
            // Bisa tambahkan notifikasi atau flash message di sini
            console.error('Update errors:', errors)
        },
        onFinish: () => {
            processing.value = false
        }
    })
}

const deleteUser = () => {
    if (!deletingUser.value) return
    
    processing.value = true
    
    router.delete(route('admin.users.destroy', deletingUser.value.id), {
        onSuccess: () => {
            closeModals()
        },
        onError: (errors) => {
            console.error('Delete errors:', errors)
        },
        onFinish: () => {
            processing.value = false
        }
    })
}


import { debounce } from 'lodash'

const performSearch = () => {
    router.get(route('admin.users.index'), { search: search.value }, {
        preserveState: true,
        preserveScroll: true
    })
}

const debouncedSearch = debounce(performSearch, 400)

watch(search, (val) => {
    debouncedSearch()
})

const toggleRole = (roleName) => {
    const index = form.value.roles.indexOf(roleName)
    if (index > -1) {
        form.value.roles.splice(index, 1)
    } else {
        form.value.roles.push(roleName)
    }
}
</script>

<template>
    <Head title="Manajemen User" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Manajemen User
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        
                        <!-- Header Actions -->
                        <div class="flex justify-between items-center mb-6">
                            <div class="flex items-center space-x-4">
                                <button @click="openCreateModal" v-if="can.create_users" class="inline-flex items-center px-4 py-2 border border-indigo-300 text-xs font-medium rounded text-indigo-600 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Tambah User
                                </button>
                            </div>
                            <div class="relative">
                                <TextInput
                                    v-model="search"
                                    type="text"
                                    placeholder="Cari user..."
                                    class="pl-10 pr-4 py-2 w-64 px-4 border border-gray-300 rounded text-xs font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                />
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Roles</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-if="usersData.length === 0">
                                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">Tidak ada data user</td>
                                    </tr>
                                    <tr v-for="user in usersData" :key="user.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ user.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ user.email }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="flex flex-wrap gap-1">
                                                <span
                                                    v-for="role in user.roles"
                                                    :key="role.id"
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                    :class="{
                                                        'bg-red-100 text-red-800': role.name === 'Super Admin',
                                                        'bg-blue-100 text-blue-800': role.name === 'Admin',
                                                        'bg-green-100 text-green-800': role.name === 'Manager',
                                                        'bg-yellow-100 text-yellow-800': role.name === 'Cashier',
                                                        'bg-purple-100 text-purple-800': role.name === 'Inventory Staff'
                                                    }"
                                                >
                                                    {{ role.name }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end space-x-2">
                                                <button @click="openEditModal(user)" class="inline-flex items-center px-2 py-1 border border-gray-300 text-xs font-medium rounded text-indigo-600 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                    Edit
                                                </button>
                                                <button 
                                                    @click="openDeleteModal(user)"
                                                    v-if="can.delete_users && user.id !== $page.props.auth.user.id"
                                                    class="inline-flex items-center px-2 py-1 border border-red-300 text-xs font-medium rounded text-red-600 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                                >
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                    Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Modal -->
        <Modal :show="showCreateModal" @close="closeModals">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Tambah User Baru</h2>
                <form @submit.prevent="createUser" class="space-y-4">
                    <div>
                        <InputLabel for="name" value="Nama" />
                        <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required />
                        <InputError :message="page.props.errors?.name" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="email" value="Email" />
                        <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" required />
                        <InputError :message="page.props.errors?.email" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="password" value="Password" />
                        <TextInput id="password" v-model="form.password" type="password" class="mt-1 block w-full" required />
                        <InputError :message="page.props.errors?.password" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="password_confirmation" value="Konfirmasi Password" />
                        <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password" class="mt-1 block w-full" required />
                    </div>
                    <div v-if="can.manage_roles">
                        <InputLabel value="Roles" />
                        <div class="mt-2 space-y-2">
                            <label v-for="role in roles" :key="role.id" class="flex items-center">
                                <input
                                    type="checkbox"
                                    :value="role.name"
                                    :checked="form.roles.includes(role.name)"
                                    @change="toggleRole(role.name)"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                >
                                <span class="ml-2 text-sm text-gray-600">{{ role.name }}</span>
                            </label>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" @click="closeModals"
                            class="inline-flex items-center px-2 py-1 border border-gray-300 text-xs font-medium rounded text-indigo-600 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Batal
                        </button>
                        <button type="submit" :disabled="processing"
                            class="inline-flex items-center px-2 py-1 border border-indigo-300 text-xs font-medium rounded text-indigo-600 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </Modal>


        <!-- Edit Modal -->
        <Modal :show="showEditModal" @close="closeModals">
            <div class="p-4 max-w-xl mx-auto">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Edit User</h2>
                <form @submit.prevent="updateUser" class="space-y-3">
                    <div>
                        <InputLabel for="edit_store_id" value="Toko" />
                        <TextInput id="edit_store_id" v-model="form.store_id" type="text" class="mt-1 block w-full" required />
                        <InputError :message="page.props.errors?.store_id" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="edit_name" value="Nama" />
                        <TextInput id="edit_name" v-model="form.name" type="text" class="mt-1 block w-full" required />
                        <InputError :message="page.props.errors?.name" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="edit_email" value="Email" />
                        <TextInput id="edit_email" v-model="form.email" type="email" class="mt-1 block w-full" required />
                        <InputError :message="page.props.errors?.email" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="edit_password" value="Password (kosongkan jika tidak ingin mengubah)" />
                        <TextInput id="edit_password" v-model="form.password" type="password" class="mt-1 block w-full" />
                        <InputError :message="page.props.errors?.password" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="edit_password_confirmation" value="Konfirmasi Password" />
                        <TextInput id="edit_password_confirmation" v-model="form.password_confirmation" type="password" class="mt-1 block w-full" />
                    </div>
                    <div v-if="can.manage_roles">
                        <InputLabel value="Roles" />
                        <div class="mt-2 space-y-2">
                            <label v-for="role in roles" :key="role.id" class="flex items-center">
                                <input
                                    type="checkbox"
                                    :value="role.name"
                                    :checked="form.roles.includes(role.name)"
                                    @change="toggleRole(role.name)"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                >
                                <span class="ml-2 text-sm text-gray-600">{{ role.name }}</span>
                            </label>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-2 pt-4">
                        <button type="button" @click="closeModals"
                            class="inline-flex items-center px-2 py-1 border border-gray-300 text-xs font-medium rounded text-indigo-600 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Batal
                        </button>
                        <button type="submit" :disabled="processing"
                            class="inline-flex items-center px-2 py-1 border border-indigo-300 text-xs font-medium rounded text-indigo-600 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Modal -->
        <Modal :show="showDeleteModal" @close="closeModals">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Hapus User</h2>
                <p class="text-sm text-gray-600 mb-4">
                    Apakah Anda yakin ingin menghapus user <strong>{{ deletingUser?.name }}</strong>?
                    Tindakan ini tidak dapat dibatalkan.
                </p>
                <div class="flex justify-end space-x-3">
                    <SecondaryButton @click="closeModals">Batal</SecondaryButton>
                    <DangerButton @click="deleteUser" :disabled="processing">Hapus</DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>