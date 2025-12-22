<script setup>
import { ref, computed } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
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
            <div>
                <h2 class="font-semibold text-lg text-[#1f1f1f] leading-tight">Manajemen User</h2>
                <p class="text-xs text-[#555]">Kelola akun pengguna dan hak akses.</p>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-[#f7f7f7]  border border-[#9c9c9c] rounded shadow-sm p-8 space-y-6">
                    <div class="text-[#1f1f1f]">

                        <!-- Header Actions -->
                        <div class="flex justify-between items-center mb-6">
                            <div class="flex items-center space-x-4">
                                <div class="relative">
                                    <TextInput v-model="search" type="text" placeholder="Cari user..."
                                        class="pl-10 pr-4 py-2 w-64 bg-white border-[#9c9c9c] focus:ring-blue-500 focus:border-blue-500 rounded"
                                        @keyup.enter="performSearch" />
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-[#555]" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <button @click="performSearch"
                                    class="inline-block bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] text-[#1f1f1f] font-semibold py-2 px-6 rounded text-xs shadow-sm hover:bg-white transition-colors">
                                    <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    Cari
                                </button>
                            </div>

                            <button @click="openCreateModal" v-if="can.create_users"
                                class="inline-block bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] text-[#1f1f1f] font-semibold py-3 px-10 rounded text-xs shadow-sm hover:bg-white transition-colors">
                                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Tambah User
                            </button>
                        </div>

                        <!-- Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-xs text-[#1f1f1f] divide-y divide-[#d0d0d0]">
                                <thead class="bg-[#efefef]">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs uppercase tracking-wider">Nama</th>
                                        <th class="px-4 py-2 text-left text-xs uppercase tracking-wider">Email</th>
                                        <th class="px-4 py-2 text-left text-xs uppercase tracking-wider">Roles</th>
                                        <th class="px-4 py-2 text-left text-xs uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#d0d0d0]">
                                    <tr v-if="usersData.length === 0"
                                        class="hover:bg-[#f7f7f7] transition-colors duration-150">
                                        <td colspan="4" class="px-4 py-3 text-center text-[#555]">Tidak ada data user
                                        </td>
                                    </tr>
                                    <tr v-for="user in usersData" :key="user.id"
                                        class="hover:bg-[#f7f7f7] transition-colors duration-150">
                                        <td class="px-4 py-2 whitespace-nowrap">{{ user.name }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap">{{ user.email }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            <div class="flex flex-wrap gap-1">
                                                <span v-for="role in user.roles" :key="role.id"
                                                    class="px-2 py-0.5 text-xs rounded" :class="{
                                                        'bg-[#f0f0f0] text-[#1f1f1f]': role.name === 'Super Admin',
                                                        'bg-[#f0f0f0] text-[#1f1f1f]': role.name === 'Admin',
                                                        'bg-[#f0f0f0] text-[#1f1f1f]': role.name === 'Manager',
                                                        'bg-[#f0f0f0] text-[#1f1f1f]': role.name === 'Cashier',
                                                        'bg-[#f0f0f0] text-[#1f1f1f]': role.name === 'Inventory Staff'
                                                    }">
                                                    {{ role.name }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            <div class="flex justify-end space-x-2">
                                                <button @click="openEditModal(user)" v-if="can.edit_users"
                                                    class="inline-flex items-center px-2 py-1 border border-[#9c9c9c] text-xs rounded text-[#1f1f1f] bg-[#f7f7f7] hover:bg-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
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
                                                    class="inline-flex items-center px-2 py-1 border border-[#9c9c9c] text-xs rounded text-[#1f1f1f] bg-[#f7f7f7] hover:bg-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
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
                class="p-8 bg-[#f7f7f7]  border border-[#9c9c9c] rounded shadow-sm max-w-lg mx-auto space-y-6 text-[#1f1f1f]">
                <h2 class="text-sm font-semibold">Tambah User Baru</h2>
                <form @submit.prevent="createUser" class="space-y-4">
                    <div>
                        <InputLabel for="name" value="Nama" class="text-[#1f1f1f]" />
                        <TextInput id="name" v-model="form.name" type="text"
                            class="mt-1 block w-full bg-white border-[#9c9c9c] focus:ring-blue-500 focus:border-blue-500 rounded"
                            required />
                        <InputError :message="page.props.errors?.name" class="mt-2 text-red-600" />
                    </div>
                    <div>
                        <InputLabel for="email" value="Email" class="text-[#1f1f1f]" />
                        <TextInput id="email" v-model="form.email" type="email"
                            class="mt-1 block w-full bg-white border-[#9c9c9c] focus:ring-blue-500 focus:border-blue-500 rounded"
                            required />
                        <InputError :message="page.props.errors?.email" class="mt-2 text-red-600" />
                    </div>
                    <div>
                        <InputLabel for="password" value="Password" class="text-[#1f1f1f]" />
                        <TextInput id="password" v-model="form.password" type="password"
                            class="mt-1 block w-full bg-white border-[#9c9c9c] focus:ring-blue-500 focus:border-blue-500 rounded"
                            required />
                        <InputError :message="page.props.errors?.password" class="mt-2 text-red-600" />
                    </div>
                    <div>
                        <InputLabel for="password_confirmation" value="Konfirmasi Password" class="text-[#1f1f1f]" />
                        <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password"
                            class="mt-1 block w-full bg-white border-[#9c9c9c] focus:ring-blue-500 focus:border-blue-500 rounded"
                            required />
                    </div>
                    <div v-if="can.manage_roles">
                        <InputLabel value="Roles" class="text-[#1f1f1f]" />
                        <div class="mt-2 space-y-2">
                            <label v-for="role in roles" :key="role.id" class="flex items-center">
                                <input type="checkbox" :value="role.name" :checked="form.roles.includes(role.name)"
                                    @change="toggleRole(role.name)"
                                    class="rounded border-[#9c9c9c] bg-white text-blue-500 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                                <span class="ml-2 text-xs text-[#555]">{{ role.name }}</span>
                            </label>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 pt-4">
                        <button @click="closeModals"
                            class="inline-block bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] text-[#1f1f1f] font-semibold py-2 px-6 rounded text-xs shadow-sm hover:bg-white transition-colors">
                            Batal
                        </button>
                        <button type="submit" :disabled="processing"
                            class="inline-block bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] text-[#1f1f1f] font-semibold py-2 px-6 rounded text-xs shadow-sm hover:bg-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
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
                class="p-8 bg-[#f7f7f7]  border border-[#9c9c9c] rounded shadow-sm max-w-lg mx-auto space-y-6 text-[#1f1f1f]">
                <h2 class="text-sm font-semibold">Edit User</h2>
                <form @submit.prevent="updateUser" class="space-y-4">
                    <div>
                        <InputLabel for="edit_name" value="Nama" class="text-[#1f1f1f]" />
                        <TextInput id="edit_name" v-model="form.name" type="text"
                            class="mt-1 block w-full bg-white border-[#9c9c9c] focus:ring-blue-500 focus:border-blue-500 rounded"
                            required />
                        <InputError :message="page.props.errors?.name" class="mt-2 text-red-600" />
                    </div>
                    <div>
                        <InputLabel for="edit_email" value="Email" class="text-[#1f1f1f]" />
                        <TextInput id="edit_email" v-model="form.email" type="email"
                            class="mt-1 block w-full bg-white border-[#9c9c9c] focus:ring-blue-500 focus:border-blue-500 rounded"
                            required />
                        <InputError :message="page.props.errors?.email" class="mt-2 text-red-600" />
                    </div>
                    <div>
                        <InputLabel for="edit_password" value="Password (kosongkan jika tidak ingin mengubah)"
                            class="text-[#1f1f1f]" />
                        <TextInput id="edit_password" v-model="form.password" type="password"
                            class="mt-1 block w-full bg-white border-[#9c9c9c] focus:ring-blue-500 focus:border-blue-500 rounded" />
                        <InputError :message="page.props.errors?.password" class="mt-2 text-red-600" />
                    </div>
                    <div>
                        <InputLabel for="edit_password_confirmation" value="Konfirmasi Password" class="text-[#1f1f1f]" />
                        <TextInput id="edit_password_confirmation" v-model="form.password_confirmation" type="password"
                            class="mt-1 block w-full bg-white border-[#9c9c9c] focus:ring-blue-500 focus:border-blue-500 rounded" />
                    </div>
                    <div v-if="can.manage_roles">
                        <InputLabel value="Roles" class="text-[#1f1f1f]" />
                        <div class="mt-2 space-y-2">
                            <label v-for="role in roles" :key="role.id" class="flex items-center">
                                <input type="checkbox" :value="role.name" :checked="form.roles.includes(role.name)"
                                    @change="toggleRole(role.name)"
                                    class="rounded border-[#9c9c9c] bg-white text-blue-500 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                                <span class="ml-2 text-xs text-[#555]">{{ role.name }}</span>
                            </label>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 pt-4">
                        <button @click="closeModals"
                            class="inline-block bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] text-[#1f1f1f] font-semibold py-2 px-6 rounded text-xs shadow-sm hover:bg-white transition-colors">
                            Batal
                        </button>
                        <button type="submit" :disabled="processing"
                            class="inline-block bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] text-[#1f1f1f] font-semibold py-2 px-6 rounded text-xs shadow-sm hover:bg-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
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
                class="p-8 bg-[#f7f7f7]  border border-[#9c9c9c] rounded shadow-sm max-w-lg mx-auto space-y-6 text-[#1f1f1f]">
                <h2 class="text-sm font-semibold">Hapus User</h2>
                <p class="text-xs">
                    Apakah Anda yakin ingin menghapus user <strong>{{ deletingUser?.name }}</strong>?
                    Tindakan ini tidak dapat dibatalkan.
                </p>
                <div class="flex justify-end space-x-3">
                    <button @click="closeModals"
                        class="inline-block bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] text-[#1f1f1f] font-semibold py-2 px-6 rounded text-xs shadow-sm hover:bg-white transition-colors">
                        Batal
                    </button>
                    <button @click="deleteUser" :disabled="processing"
                        class="inline-block bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] text-[#1f1f1f] font-semibold py-2 px-6 rounded text-xs shadow-sm hover:bg-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
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
