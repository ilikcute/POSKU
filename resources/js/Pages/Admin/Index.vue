<script setup>
import { ref, computed } from 'vue'
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

    router.patch(route('admin.users.update', editingUser.value.id), form.value, {
        onSuccess: () => {
            closeModals()
        },
        onError: (errors) => {
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

const performSearch = () => {
    router.get(route('admin.users.index'), { search: search.value }, {
        preserveState: true,
        preserveScroll: true
    })
}

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
            <h2 class="font-semibold text-xl text-white leading-tight">
                Manajemen User
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-gray-900/70 backdrop-blur-md border border-white/10 rounded-2xl shadow-lg p-8 space-y-6">
                    <div class="text-white">

                        <!-- Header Actions -->
                        <div class="flex justify-between items-center mb-6">
                            <div class="flex items-center space-x-4">
                                <div class="relative">
                                    <TextInput v-model="search" type="text" placeholder="Cari user..."
                                        class="pl-10 pr-4 py-2 w-64 bg-white/5 border-white/20 focus:ring-blue-500 focus:border-blue-500 rounded-lg"
                                        @keyup.enter="performSearch" />
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <button @click="performSearch"
                                    class="inline-block bg-gradient-to-r from-blue-400 to-blue-500 text-white font-bold py-2 px-6 rounded-full text-sm shadow-lg hover:scale-105 hover:from-blue-500 hover:to-blue-600 transition-transform duration-200">
                                    <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    Cari
                                </button>
                            </div>

                            <button @click="openCreateModal" v-if="can.create_users"
                                class="inline-block bg-gradient-to-r from-green-400 to-emerald-400 text-white font-bold py-3 px-10 rounded-full text-lg shadow-xl hover:scale-105 hover:from-green-500 hover:to-emerald-500 transition-transform duration-200">
                                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Tambah User
                            </button>
                        </div>

                        <!-- Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm text-gray-200 divide-y divide-gray-600">
                                <thead class="bg-gray-800/70">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs uppercase tracking-wider">Nama</th>
                                        <th class="px-4 py-2 text-left text-xs uppercase tracking-wider">Email</th>
                                        <th class="px-4 py-2 text-left text-xs uppercase tracking-wider">Roles</th>
                                        <th class="px-4 py-2 text-left text-xs uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-700">
                                    <tr v-if="usersData.length === 0"
                                        class="hover:bg-gray-700/50 transition-colors duration-150">
                                        <td colspan="4" class="px-4 py-3 text-center text-gray-300">Tidak ada data user
                                        </td>
                                    </tr>
                                    <tr v-for="user in usersData" :key="user.id"
                                        class="hover:bg-gray-700/50 transition-colors duration-150">
                                        <td class="px-4 py-2 whitespace-nowrap">{{ user.name }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap">{{ user.email }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            <div class="flex flex-wrap gap-1">
                                                <span v-for="role in user.roles" :key="role.id"
                                                    class="px-2 py-0.5 text-xs rounded" :class="{
                                                        'bg-red-700/30 text-red-200': role.name === 'Super Admin',
                                                        'bg-blue-700/30 text-blue-200': role.name === 'Admin',
                                                        'bg-green-700/30 text-green-200': role.name === 'Manager',
                                                        'bg-yellow-700/30 text-yellow-200': role.name === 'Cashier',
                                                        'bg-purple-700/30 text-purple-200': role.name === 'Inventory Staff'
                                                    }">
                                                    {{ role.name }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            <div class="flex justify-end space-x-2">
                                                <button @click="openEditModal(user)" v-if="can.edit_users"
                                                    class="inline-flex items-center px-2 py-1 border border-blue-500/50 text-xs rounded text-blue-200 bg-blue-500/10 hover:bg-blue-500/20 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                        </path>
                                                    </svg>
                                                    Edit
                                                </button>
                                                <button @click="openDeleteModal(user)"
                                                    v-if="can.delete_users && user.id !== $page.props.auth.user.id"
                                                    class="inline-flex items-center px-2 py-1 border border-red-500/50 text-xs rounded text-red-200 bg-red-500/10 hover:bg-red-500/20 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
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
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Modal -->
        <Modal :show="showCreateModal" @close="closeModals">
            <div
                class="p-8 bg-gray-900/70 backdrop-blur-md border border-white/10 rounded-2xl shadow-lg max-w-lg mx-auto space-y-6 text-white">
                <h2 class="text-lg font-semibold">Tambah User Baru</h2>
                <form @submit.prevent="createUser" class="space-y-4">
                    <div>
                        <InputLabel for="name" value="Nama" class="text-white" />
                        <TextInput id="name" v-model="form.name" type="text"
                            class="mt-1 block w-full bg-white/5 border-white/20 focus:ring-blue-500 focus:border-blue-500 rounded-lg"
                            required />
                        <InputError :message="page.props.errors?.name" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="email" value="Email" class="text-white" />
                        <TextInput id="email" v-model="form.email" type="email"
                            class="mt-1 block w-full bg-white/5 border-white/20 focus:ring-blue-500 focus:border-blue-500 rounded-lg"
                            required />
                        <InputError :message="page.props.errors?.email" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="password" value="Password" class="text-white" />
                        <TextInput id="password" v-model="form.password" type="password"
                            class="mt-1 block w-full bg-white/5 border-white/20 focus:ring-blue-500 focus:border-blue-500 rounded-lg"
                            required />
                        <InputError :message="page.props.errors?.password" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="password_confirmation" value="Konfirmasi Password" class="text-white" />
                        <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password"
                            class="mt-1 block w-full bg-white/5 border-white/20 focus:ring-blue-500 focus:border-blue-500 rounded-lg"
                            required />
                    </div>
                    <div v-if="can.manage_roles">
                        <InputLabel value="Roles" class="text-white" />
                        <div class="mt-2 space-y-2">
                            <label v-for="role in roles" :key="role.id" class="flex items-center">
                                <input type="checkbox" :value="role.name" :checked="form.roles.includes(role.name)"
                                    @change="toggleRole(role.name)"
                                    class="rounded border-white/20 bg-white/5 text-blue-500 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-300">{{ role.name }}</span>
                            </label>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 pt-4">
                        <button @click="closeModals"
                            class="inline-block bg-gradient-to-r from-gray-400 to-gray-500 text-white font-bold py-2 px-6 rounded-full text-sm shadow-lg hover:scale-105 hover:from-gray-500 hover:to-gray-600 transition-transform duration-200">
                            Batal
                        </button>
                        <button type="submit" :disabled="processing"
                            class="inline-block bg-gradient-to-r from-green-400 to-emerald-400 text-white font-bold py-2 px-6 rounded-full text-sm shadow-lg hover:scale-105 hover:from-green-500 hover:to-emerald-500 transition-transform duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit Modal -->
        <Modal :show="showEditModal" @close="closeModals">
            <div
                class="p-8 bg-gray-900/70 backdrop-blur-md border border-white/10 rounded-2xl shadow-lg max-w-lg mx-auto space-y-6 text-white">
                <h2 class="text-lg font-semibold">Edit User</h2>
                <form @submit.prevent="updateUser" class="space-y-4">
                    <div>
                        <InputLabel for="edit_name" value="Nama" class="text-white" />
                        <TextInput id="edit_name" v-model="form.name" type="text"
                            class="mt-1 block w-full bg-white/5 border-white/20 focus:ring-blue-500 focus:border-blue-500 rounded-lg"
                            required />
                        <InputError :message="page.props.errors?.name" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="edit_email" value="Email" class="text-white" />
                        <TextInput id="edit_email" v-model="form.email" type="email"
                            class="mt-1 block w-full bg-white/5 border-white/20 focus:ring-blue-500 focus:border-blue-500 rounded-lg"
                            required />
                        <InputError :message="page.props.errors?.email" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="edit_password" value="Password (kosongkan jika tidak ingin mengubah)"
                            class="text-white" />
                        <TextInput id="edit_password" v-model="form.password" type="password"
                            class="mt-1 block w-full bg-white/5 border-white/20 focus:ring-blue-500 focus:border-blue-500 rounded-lg" />
                        <InputError :message="page.props.errors?.password" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="edit_password_confirmation" value="Konfirmasi Password" class="text-white" />
                        <TextInput id="edit_password_confirmation" v-model="form.password_confirmation" type="password"
                            class="mt-1 block w-full bg-white/5 border-white/20 focus:ring-blue-500 focus:border-blue-500 rounded-lg" />
                    </div>
                    <div v-if="can.manage_roles">
                        <InputLabel value="Roles" class="text-white" />
                        <div class="mt-2 space-y-2">
                            <label v-for="role in roles" :key="role.id" class="flex items-center">
                                <input type="checkbox" :value="role.name" :checked="form.roles.includes(role.name)"
                                    @change="toggleRole(role.name)"
                                    class="rounded border-white/20 bg-white/5 text-blue-500 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-300">{{ role.name }}</span>
                            </label>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 pt-4">
                        <button @click="closeModals"
                            class="inline-block bg-gradient-to-r from-gray-400 to-gray-500 text-white font-bold py-2 px-6 rounded-full text-sm shadow-lg hover:scale-105 hover:from-gray-500 hover:to-gray-600 transition-transform duration-200">
                            Batal
                        </button>
                        <button type="submit" :disabled="processing"
                            class="inline-block bg-gradient-to-r from-blue-400 to-blue-500 text-white font-bold py-2 px-6 rounded-full text-sm shadow-lg hover:scale-105 hover:from-blue-500 hover:to-blue-600 transition-transform duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Modal -->
        <Modal :show="showDeleteModal" @close="closeModals">
            <div
                class="p-8 bg-gray-900/70 backdrop-blur-md border border-white/10 rounded-2xl shadow-lg max-w-lg mx-auto space-y-6 text-white">
                <h2 class="text-lg font-semibold">Hapus User</h2>
                <p class="text-sm">
                    Apakah Anda yakin ingin menghapus user <strong>{{ deletingUser?.name }}</strong>?
                    Tindakan ini tidak dapat dibatalkan.
                </p>
                <div class="flex justify-end space-x-3">
                    <button @click="closeModals"
                        class="inline-block bg-gradient-to-r from-gray-400 to-gray-500 text-white font-bold py-2 px-6 rounded-full text-sm shadow-lg hover:scale-105 hover:from-gray-500 hover:to-gray-600 transition-transform duration-200">
                        Batal
                    </button>
                    <button @click="deleteUser" :disabled="processing"
                        class="inline-block bg-gradient-to-r from-red-400 to-red-500 text-white font-bold py-2 px-6 rounded-full text-sm shadow-lg hover:scale-105 hover:from-red-500 hover:to-red-600 transition-transform duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Hapus
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>