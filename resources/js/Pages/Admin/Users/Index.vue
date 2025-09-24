<script setup>
import { ref, computed, watch } from 'vue'
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
            <h2 class="font-semibold text-xl text-white leading-tight">
                Manajemen User
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header dengan tombol Create User -->
                <div class="mb-6 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-white">Daftar User</h3>
                    <button v-if="can.create_users" @click="openCreateModal"
                        class="inline-block bg-gradient-to-r from-green-400 to-emerald-400 text-white font-bold py-3 px-10 rounded-full text-lg shadow-xl hover:scale-105 hover:from-green-500 hover:to-emerald-500 transition-transform duration-200">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Tambah User
                    </button>
                </div>

                <!-- Container untuk efek blur dan border -->
                <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl shadow-lg overflow-hidden">
                    <!-- Table wrapper agar bisa di-scroll horizontal pada layar kecil -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm text-gray-200 divide-y divide-gray-600">
                            <!-- Header -->
                            <thead class="bg-gray-800/70">
                                <tr>
                                    <th class="px-4 py-2 text-left uppercase tracking-wider">Nama</th>
                                    <th class="px-4 py-2 text-left uppercase tracking-wider">Email</th>
                                    <th class="px-4 py-2 text-left uppercase tracking-wider">Roles</th>
                                    <th class="px-4 py-2 text-left uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <!-- Isi tabel -->
                            <tbody class="divide-y divide-gray-700">
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
                                            <button @click="openEditModal(user)"
                                                class="inline-block bg-gradient-to-r from-blue-400 to-blue-500 text-white font-bold py-2 px-4 rounded-full text-xs shadow-lg hover:scale-105 hover:from-blue-500 hover:to-blue-600 transition-transform duration-200">
                                                <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                    </path>
                                                </svg>
                                                Edit
                                            </button>
                                            <button v-if="can.delete_users && user.id !== $page.props.auth.user.id"
                                                @click="openDeleteModal(user)"
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
                            </tbody>
                        </table>
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
                        <InputLabel for="name" value="Nama" class="text-gray-300" />
                        <TextInput id="name" v-model="form.name" type="text"
                            class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500"
                            required />
                        <InputError :message="page.props.errors?.name" class="mt-2 text-red-400" />
                    </div>

                    <div>
                        <InputLabel for="email" value="Email" class="text-gray-300" />
                        <TextInput id="email" v-model="form.email" type="email"
                            class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500"
                            required />
                        <InputError :message="page.props.errors?.email" class="mt-2 text-red-400" />
                    </div>

                    <div>
                        <InputLabel for="password" value="Password" class="text-gray-300" />
                        <TextInput id="password" v-model="form.password" type="password"
                            class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500"
                            required />
                        <InputError :message="page.props.errors?.password" class="mt-2 text-red-400" />
                    </div>

                    <div>
                        <InputLabel for="password_confirmation" value="Konfirmasi Password" class="text-gray-300" />
                        <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password"
                            class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500"
                            required />
                    </div>

                    <div v-if="can.manage_roles">
                        <InputLabel value="Roles" class="text-gray-300" />
                        <div class="mt-2 space-y-2">
                            <label v-for="role in roles" :key="role.id" class="flex items-center">
                                <input type="checkbox" :value="role.name" :checked="form.roles.includes(role.name)"
                                    @change="toggleRole(role.name)"
                                    class="rounded border-gray-600 text-blue-400 bg-gray-800 focus:border-blue-400 focus:ring-blue-200 focus:ring-opacity-50" />
                                <span class="ml-2 text-sm text-gray-300">{{ role.name }}</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" @click="closeModals"
                            class="inline-block bg-gradient-to-r from-blue-400 to-blue-500 text-white font-bold py-2 px-6 rounded-full text-sm shadow-lg hover:scale-105 hover:from-blue-500 hover:to-blue-600 transition-transform duration-200">
                            <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Batal
                        </button>
                        <button type="submit" :disabled="processing"
                            class="inline-block bg-gradient-to-r from-green-400 to-emerald-400 text-white font-bold py-2 px-6 rounded-full text-sm shadow-lg hover:scale-105 hover:from-green-500 hover:to-emerald-500 transition-transform duration-200 disabled:opacity-25">
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
                    <!-- Field examples; ulangi pola input seperti modal create -->
                    <div>
                        <InputLabel for="edit_store_id" value="Toko" class="text-gray-300" />
                        <TextInput id="edit_store_id" v-model="form.store_id" type="text"
                            class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500"
                            required />
                        <InputError :message="page.props.errors?.store_id" class="mt-2 text-red-400" />
                    </div>
                    <div>
                        <InputLabel for="edit_name" value="Nama" class="text-gray-300" />
                        <TextInput id="edit_name" v-model="form.name" type="text"
                            class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500"
                            required />
                        <InputError :message="page.props.errors?.name" class="mt-2 text-red-400" />
                    </div>
                    <div>
                        <InputLabel for="edit_email" value="Email" class="text-gray-300" />
                        <TextInput id="edit_email" v-model="form.email" type="email"
                            class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500"
                            required />
                        <InputError :message="page.props.errors?.email" class="mt-2 text-red-400" />
                    </div>
                    <div>
                        <InputLabel for="edit_password" value="Password (kosongkan jika tidak ingin mengubah)"
                            class="text-gray-300" />
                        <TextInput id="edit_password" v-model="form.password" type="password"
                            class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500" />
                        <InputError :message="page.props.errors?.password" class="mt-2 text-red-400" />
                    </div>
                    <div>
                        <InputLabel for="edit_password_confirmation" value="Konfirmasi Password"
                            class="text-gray-300" />
                        <TextInput id="edit_password_confirmation" v-model="form.password_confirmation" type="password"
                            class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500" />
                    </div>
                    <div v-if="can.manage_roles">
                        <InputLabel value="Roles" class="text-gray-300" />
                        <div class="mt-2 space-y-2">
                            <label v-for="role in roles" :key="role.id" class="flex items-center">
                                <input type="checkbox" :value="role.name" :checked="form.roles.includes(role.name)"
                                    @change="toggleRole(role.name)"
                                    class="rounded border-gray-600 text-blue-400 bg-gray-800 focus:border-blue-400 focus:ring-blue-200 focus:ring-opacity-50" />
                                <span class="ml-2 text-sm text-gray-300">{{ role.name }}</span>
                            </label>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" @click="closeModals"
                            class="inline-block bg-gradient-to-r from-blue-400 to-blue-500 text-white font-bold py-2 px-6 rounded-full text-sm shadow-lg hover:scale-105 hover:from-blue-500 hover:to-blue-600 transition-transform duration-200">
                            <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Batal
                        </button>
                        <button type="submit" :disabled="processing"
                            class="inline-block bg-gradient-to-r from-green-400 to-emerald-400 text-white font-bold py-2 px-6 rounded-full text-sm shadow-lg hover:scale-105 hover:from-green-500 hover:to-emerald-500 transition-transform duration-200 disabled:opacity-25">
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
                <p class="text-sm text-gray-300 mb-4">
                    Apakah Anda yakin ingin menghapus user <strong>{{ deletingUser?.name }}</strong>? Tindakan ini
                    tidak
                    dapat dibatalkan.
                </p>
                <div class="flex justify-end space-x-3">
                    <button type="button" @click="closeModals"
                        class="inline-block bg-gradient-to-r from-blue-400 to-blue-500 text-white font-bold py-2 px-6 rounded-full text-sm shadow-lg hover:scale-105 hover:from-blue-500 hover:to-blue-600 transition-transform duration-200">
                        Batal
                    </button>
                    <button @click="deleteUser" :disabled="processing"
                        class="inline-block bg-gradient-to-r from-red-400 to-red-500 text-white font-bold py-2 px-6 rounded-full text-sm shadow-lg hover:scale-105 hover:from-red-500 hover:to-red-600 transition-transform duration-200 disabled:opacity-25">
                        Hapus
                    </button>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>