<script setup>
import { ref, computed, watch } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
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
const viewMode = ref('table') // 'table' or 'cards'

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
            if (page?.props?.errors && Object.keys(page.props.errors).length > 0) {
                processing.value = false
                return
            }
            closeModals()
        },
        onError: (errors) => {
            processing.value = false
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

const getRoleBadgeClass = (roleName) => {
    const badges = {
        'Super Admin': 'bg-[#f0f0f0] text-[#1f1f1f] border-[#9c9c9c]',
        'Admin': 'bg-[#f0f0f0] text-[#1f1f1f] border-[#9c9c9c]',
        'Manager': 'bg-[#f0f0f0] text-[#1f1f1f] border-[#9c9c9c]',
        'Cashier': 'bg-[#f0f0f0] text-[#1f1f1f] border-[#9c9c9c]',
        'Inventory Staff': 'bg-[#f0f0f0] text-[#1f1f1f] border-[#9c9c9c]'
    };
    return badges[roleName] || 'bg-[#f0f0f0] text-[#1f1f1f] border-[#9c9c9c]';
}
</script>

<template>

    <Head title="Manajemen User" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                <div>
                    <h2 class="font-semibold text-lg text-[#1f1f1f] leading-tight">Manajemen User</h2>
                    <p class="text-xs text-[#555]">Kelola user, role, dan izin akses.</p>
                </div>

                <!-- View Toggle -->
                <div class="flex bg-[#f7f7f7] border border-[#9c9c9c] rounded p-1">
                    <button @click="viewMode = 'table'" :class="[
                        'px-3 py-2 rounded text-xs font-semibold transition-colors',
                        viewMode === 'table'
                            ? 'bg-white text-[#1f1f1f]'
                            : 'text-[#555] hover:text-[#1f1f1f] hover:bg-white'
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
                            : 'text-[#555] hover:text-[#1f1f1f] hover:bg-white'
                    ]">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14-7H5a2 2 0 00-2 2v12a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2z" />
                        </svg>
                        Cards
                    </button>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header dengan tombol Create User -->
            <div class="mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                <!-- Search Input -->
                <div class="relative flex-1 max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-[#555]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input v-model="search" type="text" placeholder="Cari user..."
                        class="block w-full pl-10 pr-3 py-2 border border-[#9c9c9c] rounded bg-white text-[#1f1f1f] placeholder-[#888] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent ">
                </div>

                <button v-if="can.create_users" @click="openCreateModal"
                    class="inline-flex items-center px-6 py-3 bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] text-[#1f1f1f] font-medium rounded shadow-sm hover:shadow-sm  transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Tambah User
                </button>
            </div>

            <!-- Table View -->
            <div v-if="viewMode === 'table'"
                class=" bg-white border border-[#9c9c9c] rounded shadow-sm overflow-hidden">
                <!-- Desktop Table -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full text-xs text-[#1f1f1f]">
                        <thead
                            class="bg-[#efefef]  border-b border-[#9c9c9c]">
                            <tr>
                                <th
                                    class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-[#1f1f1f]/90">
                                    User</th>
                                <th
                                    class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-[#1f1f1f]/90">
                                    Email</th>
                                <th
                                    class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-[#1f1f1f]/90">
                                    Roles</th>
                                <th
                                    class="px-6 py-4 text-center text-xs uppercase tracking-wider font-semibold text-[#1f1f1f]/90">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#d0d0d0]">
                            <tr v-for="user in usersData" :key="user.id"
                                class="hover:bg-white transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div
                                            class="w-10 h-10 rounded bg-[#dbe7ff] border border-[#9c9c9c] flex items-center justify-center text-xs font-bold mr-4">
                                            {{ user.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-[#1f1f1f]">{{ user.name }}</div>
                                            <div class="text-xs text-[#555]">ID: {{ user.id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-[#555]">{{ user.email }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <span v-for="role in user.roles" :key="role.id"
                                            :class="getRoleBadgeClass(role.name)"
                                            class="inline-flex px-3 py-1 rounded text-xs font-semibold border">
                                            {{ role.name }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-center space-x-2">
                                        <button @click="openEditModal(user)"
                                            class="inline-flex items-center px-3 py-2 bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] text-[#1f1f1f] text-xs font-semibold rounded hover:shadow-sm  transition-colors">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                            Edit
                                        </button>
                                        <button v-if="can.delete_users && user.id !== $page.props.auth.user.id"
                                            @click="openDeleteModal(user)"
                                            class="inline-flex items-center px-3 py-2 bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] text-[#1f1f1f] text-xs font-semibold rounded hover:shadow-sm  transition-colors">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
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

                <!-- Mobile Table -->
                <div class="md:hidden overflow-x-auto">
                    <table class="min-w-full text-xs text-[#1f1f1f]">
                        <thead
                            class="bg-[#efefef]  border-b border-[#9c9c9c]">
                            <tr>
                                <th class="px-3 py-3 text-left font-semibold text-[#1f1f1f]/90">User Info</th>
                                <th class="px-3 py-3 text-left font-semibold text-[#1f1f1f]/90">Roles</th>
                                <th class="px-3 py-3 text-center font-semibold text-[#1f1f1f]/90">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#d0d0d0]">
                            <tr v-for="user in usersData" :key="user.id" class="hover:bg-white">
                                <td class="px-3 py-3">
                                    <div class="flex items-center">
                                        <div
                                            class="w-8 h-8 rounded bg-[#dbe7ff] border border-[#9c9c9c] flex items-center justify-center text-xs font-bold mr-3">
                                            {{ user.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-[#1f1f1f] text-xs">{{ user.name }}</div>
                                            <div class="text-xs text-[#555]">{{ user.email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        <span v-for="role in user.roles" :key="role.id"
                                            :class="getRoleBadgeClass(role.name)"
                                            class="inline-flex px-2 py-1 rounded text-xs font-semibold border">
                                            {{ role.name }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-3 py-3">
                                    <div class="flex justify-center space-x-1">
                                        <button @click="openEditModal(user)"
                                            class="p-2 bg-[#f7f7f7] text-[#1f1f1f] border border-[#9c9c9c] rounded hover:bg-white transition-colors">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                        </button>
                                        <button v-if="can.delete_users && user.id !== $page.props.auth.user.id"
                                            @click="openDeleteModal(user)"
                                            class="p-2 bg-[#f7f7f7] text-[#1f1f1f] border border-[#9c9c9c] rounded hover:bg-white transition-colors">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Card View -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="user in usersData" :key="user.id"
                    class=" bg-white border border-[#9c9c9c] rounded p-6 shadow-sm  hover:bg-white transition-colors ">

                    <!-- Header -->
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div
                                class="w-12 h-12 rounded bg-[#dbe7ff] border border-[#9c9c9c] flex items-center justify-center text-xs font-bold mr-4">
                                {{ user.name.charAt(0).toUpperCase() }}
                            </div>
                            <div>
                                <h3 class="font-semibold text-[#1f1f1f]">{{ user.name }}</h3>
                                <p class="text-xs text-[#555]">{{ user.email }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Roles -->
                    <div class="mb-4">
                        <div class="text-xs text-[#555] mb-2">Roles:</div>
                        <div class="flex flex-wrap gap-2">
                            <span v-for="role in user.roles" :key="role.id" :class="getRoleBadgeClass(role.name)"
                                class="inline-flex px-3 py-1 rounded text-xs font-semibold border">
                                {{ role.name }}
                            </span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end space-x-2 pt-4 border-t border-[#9c9c9c]">
                        <button @click="openEditModal(user)"
                            class="inline-flex items-center px-4 py-2 bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] text-[#1f1f1f] text-xs font-semibold rounded hover:shadow-sm  transition-colors">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                </path>
                            </svg>
                            Edit
                        </button>
                        <button v-if="can.delete_users && user.id !== $page.props.auth.user.id"
                            @click="openDeleteModal(user)"
                            class="inline-flex items-center px-4 py-2 bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] text-[#1f1f1f] text-xs font-semibold rounded hover:shadow-sm  transition-colors">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                </path>
                            </svg>
                            Hapus
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!usersData.length"
                class="text-center py-12  bg-white border border-[#9c9c9c] rounded">
                <svg class="mx-auto h-12 w-12 text-[#555] mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                </svg>
                <h3 class="text-xs font-semibold text-[#555] mb-2">Belum ada data user</h3>
                <p class="text-[#555]">Data user akan muncul di sini setelah ditambahkan.</p>
            </div>
        </div>
        <Pagination :links="props.users.links" />

        <!-- Create Modal -->
        <Modal :show="showCreateModal" @close="closeModals">
            <div
                class="p-8 bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-sm max-w-lg mx-auto space-y-6 text-[#1f1f1f]">
                <h2 class="text-xs font-semibold">Tambah User Baru</h2>
                <form @submit.prevent="createUser" class="space-y-4">
                    <div>
                        <InputLabel for="name" value="Nama" class="text-[#1f1f1f]" />
                        <TextInput id="name" v-model="form.name" type="text"
                            class="mt-1 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500"
                            required />
                        <InputError :message="page.props.errors?.name" class="mt-2 text-red-600" />
                    </div>

                    <div>
                        <InputLabel for="email" value="Email" class="text-[#1f1f1f]" />
                        <TextInput id="email" v-model="form.email" type="email"
                            class="mt-1 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500"
                            required />
                        <InputError :message="page.props.errors?.email" class="mt-2 text-red-600" />
                    </div>

                    <div>
                        <InputLabel for="password" value="Password" class="text-[#1f1f1f]" />
                        <TextInput id="password" v-model="form.password" type="password"
                            class="mt-1 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500"
                            required />
                        <InputError :message="page.props.errors?.password" class="mt-2 text-red-600" />
                    </div>

                    <div>
                        <InputLabel for="password_confirmation" value="Konfirmasi Password" class="text-[#1f1f1f]" />
                        <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password"
                            class="mt-1 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500"
                            required />
                    </div>

                    <div v-if="can.manage_roles">
                        <InputLabel value="Roles" class="text-[#1f1f1f]" />
                        <div class="mt-2 space-y-2">
                            <label v-for="role in roles" :key="role.id" class="flex items-center">
                                <input type="checkbox" :value="role.name" :checked="form.roles.includes(role.name)"
                                    @change="toggleRole(role.name)"
                                    class="rounded border-[#9c9c9c] text-[#1f1f1f] bg-white focus:border-blue-400 focus:ring-blue-200 focus:ring-opacity-50" />
                                <span class="ml-2 text-xs text-[#555]">{{ role.name }}</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" @click="closeModals"
                            class="inline-flex items-center px-4 py-2 bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] text-xs font-semibold rounded hover:bg-white transition-colors">
                            Batal
                        </button>
                        <button type="submit" :disabled="processing"
                            class="inline-flex items-center px-4 py-2 bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] text-[#1f1f1f] text-xs font-semibold rounded hover:shadow-sm  transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            {{ processing ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit Modal -->
        <Modal :show="showEditModal" @close="closeModals">
            <div
                class="p-8 bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-sm max-w-lg mx-auto space-y-6 text-[#1f1f1f]">
                <h2 class="text-xs font-semibold">Edit User</h2>
                <form @submit.prevent="updateUser" class="space-y-4">
                    <div>
                        <InputLabel for="edit_store_id" value="Toko" class="text-[#1f1f1f]" />
                        <TextInput id="edit_store_id" v-model="form.store_id" type="text"
                            class="mt-1 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500"
                            required />
                        <InputError :message="page.props.errors?.store_id" class="mt-2 text-red-600" />
                    </div>
                    <div>
                        <InputLabel for="edit_name" value="Nama" class="text-[#1f1f1f]" />
                        <TextInput id="edit_name" v-model="form.name" type="text"
                            class="mt-1 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500"
                            required />
                        <InputError :message="page.props.errors?.name" class="mt-2 text-red-600" />
                    </div>
                    <div>
                        <InputLabel for="edit_email" value="Email" class="text-[#1f1f1f]" />
                        <TextInput id="edit_email" v-model="form.email" type="email"
                            class="mt-1 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500"
                            required />
                        <InputError :message="page.props.errors?.email" class="mt-2 text-red-600" />
                    </div>
                    <div>
                        <InputLabel for="edit_password" value="Password (kosongkan jika tidak ingin mengubah)"
                            class="text-[#1f1f1f]" />
                        <TextInput id="edit_password" v-model="form.password" type="password"
                            class="mt-1 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500" />
                        <InputError :message="page.props.errors?.password" class="mt-2 text-red-600" />
                    </div>
                    <div>
                        <InputLabel for="edit_password_confirmation" value="Konfirmasi Password"
                            class="text-[#1f1f1f]" />
                        <TextInput id="edit_password_confirmation" v-model="form.password_confirmation" type="password"
                            class="mt-1 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500" />
                    </div>
                    <div v-if="can.manage_roles">
                        <InputLabel value="Roles" class="text-[#1f1f1f]" />
                        <div class="mt-2 space-y-2">
                            <label v-for="role in roles" :key="role.id" class="flex items-center">
                                <input type="checkbox" :value="role.name" :checked="form.roles.includes(role.name)"
                                    @change="toggleRole(role.name)"
                                    class="rounded border-[#9c9c9c] text-[#1f1f1f] bg-white focus:border-blue-400 focus:ring-blue-200 focus:ring-opacity-50" />
                                <span class="ml-2 text-xs text-[#555]">{{ role.name }}</span>
                            </label>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" @click="closeModals"
                            class="inline-flex items-center px-4 py-2 bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] text-xs font-semibold rounded hover:bg-white transition-colors">
                            Batal
                        </button>
                        <button type="submit" :disabled="processing"
                            class="inline-flex items-center px-4 py-2 bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] text-[#1f1f1f] text-xs font-semibold rounded hover:shadow-sm  transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            {{ processing ? 'Mengupdate...' : 'Update' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Modal -->
        <Modal :show="showDeleteModal" @close="closeModals">
            <div
                class="p-8 bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-sm max-w-lg mx-auto space-y-6 text-[#1f1f1f]">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-[#f7f7f7] rounded mb-4">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                <div class="text-center">
                    <h2 class="text-xs font-semibold mb-2">Hapus User</h2>
                    <p class="text-xs text-[#555] mb-6">
                        Apakah Anda yakin ingin menghapus user <strong class="text-[#1f1f1f]">{{ deletingUser?.name
                        }}</strong>?
                        <br>Tindakan ini tidak dapat dibatalkan.
                    </p>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" @click="closeModals"
                        class="inline-flex items-center px-4 py-2 bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] text-xs font-semibold rounded hover:bg-white transition-colors">
                        Batal
                    </button>
                    <button @click="deleteUser" :disabled="processing"
                        class="inline-flex items-center px-4 py-2 bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] text-[#1f1f1f] text-xs font-semibold rounded hover:shadow-sm  transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        {{ processing ? 'Menghapus...' : 'Hapus' }}
                    </button>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
