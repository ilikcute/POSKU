<script setup>
import { ref, computed, watch } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { debounce } from 'lodash'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import Modal from '@/Components/Modal.vue'
import TextInput from '@/Components/TextInput.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'

// Props dengan default values untuk menghindari undefined
const props = defineProps({
    salesmen: {
        type: Object,
        default: () => ({ data: [], links: [], meta: {} })
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
    salesman_code: '',
    name: '',
    phone: '',
    email: ''
})

const editingItem = ref(null)
const deletingItem = ref(null)

// Page props
const page = usePage()

// Computed
const salesmenData = computed(() => {
    // Safety check untuk memastikan data exists
    if (!props.salesmen || !props.salesmen.data) {
        return []
    }
    return props.salesmen.data
})

// Methods
const openCreateModal = () => {
    resetForm()
    showCreateModal.value = true
}

const openEditModal = (salesman) => {
    editingItem.value = salesman
    form.value = {
        salesman_code: salesman.salesman_code || '',
        name: salesman.name || '',
        phone: salesman.phone || '',
        email: salesman.email || ''
    }
    showEditModal.value = true
}

const openDeleteModal = (salesman) => {
    deletingItem.value = salesman
    showDeleteModal.value = true
}

const closeModals = () => {
    showCreateModal.value = false
    showEditModal.value = false
    showDeleteModal.value = false
    resetForm()
    editingItem.value = null
    deletingItem.value = null
}

const resetForm = () => {
    form.value = {
        salesman_code: '',
        name: '',
        phone: '',
        email: ''
    }
}

const createSalesman = () => {
    processing.value = true

    router.post(route('master.salesmen.store'), form.value, {
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

const updateSalesman = () => {
    if (!editingItem.value) return

    processing.value = true

    router.patch(route('master.salesmen.update', editingItem.value.id), form.value, {
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

const deleteSalesman = () => {
    if (!deletingItem.value) return

    processing.value = true

    router.delete(route('master.salesmen.destroy', deletingItem.value.id), {
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

watch(
    search,
    debounce((value) => {
        router.get(
            route('master.salesmen.index'),
            { search: value },
            {
                preserveState: true,
                replace: true,
            }
        );
    }, 300)
);

// Debug - untuk cek data di console
console.log('Props salesmen:', props.salesmen)
console.log('Salesmen data:', salesmenData.value)
</script>

<template>

    <Head title="Master Salesman" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">
                Master Salesman
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl shadow-lg overflow-hidden">
                    <div class="p-6 text-gray-200">

                        <!-- Header Actions -->
                        <div class="flex justify-between items-center mb-6">
                            <div class="flex items-center">
                                <button @click="openCreateModal" v-if="can.create_salesman !== false"
                                    class="inline-block bg-gradient-to-r from-green-400 to-emerald-400 text-white font-bold py-3 px-10 rounded-full text-sm md:text-base shadow-xl hover:scale-105 hover:from-green-500 hover:to-emerald-500 transition-transform duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4" />
                                    </svg>
                                    Tambah Salesman
                                </button>
                            </div>
                            <div class="flex items-center">
                                <TextInput v-model="search" type="text" placeholder="Cari salesman..."
                                    class="w-full max-w-xs bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500" />
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm text-left text-gray-200 divide-y divide-gray-700">
                                <thead class="bg-white/5">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Kode</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Nama</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Telepon</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Email</th>
                                        <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-800">
                                    <tr v-if="salesmenData.length === 0">
                                        <td colspan="5" class="px-4 py-3 text-center">
                                            Tidak ada data salesman
                                        </td>
                                    </tr>
                                    <tr v-for="salesman in salesmenData" :key="salesman.id" class="hover:bg-white/5 transition-colors duration-150">
                                        <td class="px-4 py-3 whitespace-nowrap font-mono text-sm">
                                            {{ salesman.salesman_code }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            {{ salesman.name }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            {{ salesman.phone || '-' }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            {{ salesman.email || '-' }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end space-x-2">
                                                <button @click="openEditModal(salesman)" v-if="can.edit_salesmen"
                                                    class="inline-flex items-center gap-2 bg-gradient-to-r from-blue-400 to-sky-400 text-white font-bold py-2 px-4 rounded-full text-xs shadow-lg hover:scale-105 hover:from-blue-500 hover:to-sky-500 transition-transform duration-200">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                        </path>
                                                    </svg>
                                                    <span>Edit</span>
                                                </button>
                                                <button @click="openDeleteModal(salesman)" v-if="can.delete_salesmen"
                                                    class="inline-flex items-center gap-2 bg-gradient-to-r from-red-500 to-rose-500 text-white font-bold py-2 px-4 rounded-full text-xs shadow-lg hover:scale-105 hover:from-red-600 hover:to-rose-600 transition-transform duration-200">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                        </path>
                                                    </svg>
                                                    <span>Hapus</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="salesmen.links && salesmen.links.length > 3"
                            class="mt-6 flex justify-between items-center">
                            <div class="text-sm text-gray-400">
                                Showing {{ salesmen.meta?.from || 0 }} to {{ salesmen.meta?.to || 0 }}
                                of {{ salesmen.meta?.total || 0 }} results
                            </div>
                            <div class="flex space-x-1">
                                <template v-for="(link, index) in salesmen.links" :key="index">
                                    <button @click="router.get(link.url)"
                                            v-if="link.url"
                                            :class="[
                                                'px-3 py-2 text-sm border rounded-lg transition-colors duration-150',
                                                link.active
                                                    ? 'bg-blue-500 text-white border-blue-500/80'
                                                    : 'bg-white/5 text-gray-300 border-white/10 hover:bg-white/10'
                                            ]" v-html="link.label"></button>
                                    <span v-else class="px-3 py-2 text-sm text-gray-500" v-html="link.label"></span>
                                </template>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Create Modal -->
        <Modal :show="showCreateModal" @close="closeModals">
            <div class="p-6 bg-gray-800 text-white rounded-lg">
                <h2 class="text-lg font-medium text-white mb-4">
                    Tambah Salesman Baru
                </h2>

                <form @submit.prevent="createSalesman" class="space-y-4">
                    <div>
                        <InputLabel for="salesman_code" value="Kode Salesman" class="text-gray-300"/>
                        <TextInput id="salesman_code" v-model="form.salesman_code" type="text" class="mt-1 block w-full bg-gray-700 border-gray-600 text-white"
                            required />
                        <InputError :message="page.props.errors?.salesman_code" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="name" value="Nama" class="text-gray-300"/>
                        <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full bg-gray-700 border-gray-600 text-white" required />
                        <InputError :message="page.props.errors?.name" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="phone" value="Phone" class="text-gray-300"/>
                        <TextInput id="phone" v-model="form.phone" type="text" class="mt-1 block w-full bg-gray-700 border-gray-600 text-white" />
                        <InputError :message="page.props.errors?.phone" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="email" value="Email" class="text-gray-300"/>
                        <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full bg-gray-700 border-gray-600 text-white" />
                        <InputError :message="page.props.errors?.email" class="mt-2" />
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" @click="closeModals"
                            class="inline-flex items-center gap-2 bg-white/10 border border-white/20 text-white font-bold py-2 px-4 rounded-full text-xs shadow-lg hover:scale-105 hover:bg-white/20 transition-transform duration-200">
                            Batal
                        </button>
                        <button type="submit" :disabled="processing"
                            class="inline-flex items-center gap-2 bg-gradient-to-r from-blue-400 to-sky-400 text-white font-bold py-2 px-4 rounded-full text-xs shadow-lg hover:scale-105 hover:from-blue-500 hover:to-sky-500 transition-transform duration-200">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit Modal -->
        <Modal :show="showEditModal" @close="closeModals">
            <div class="p-6 bg-gray-800 text-white rounded-lg">
                <h2 class="text-lg font-medium text-white mb-4">
                    Edit Salesman
                </h2>

                <form @submit.prevent="updateSalesman" class="space-y-4">
                    <div>
                        <InputLabel for="edit_salesman_code" value="Kode Salesman" class="text-gray-300"/>
                        <TextInput id="edit_salesman_code" v-model="form.salesman_code" type="text"
                            class="mt-1 block w-full bg-gray-700 border-gray-600 text-white" required />
                        <InputError :message="page.props.errors?.salesman_code" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="edit_name" value="Nama" class="text-gray-300"/>
                        <TextInput id="edit_name" v-model="form.name" type="text" class="mt-1 block w-full bg-gray-700 border-gray-600 text-white" required />
                        <InputError :message="page.props.errors?.name" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="edit_phone" value="Phone" class="text-gray-300"/>
                        <TextInput id="edit_phone" v-model="form.phone" type="text" class="mt-1 block w-full bg-gray-700 border-gray-600 text-white" />
                        <InputError :message="page.props.errors?.phone" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="edit_email" value="Email" class="text-gray-300"/>
                        <TextInput id="edit_email" v-model="form.email" type="email" class="mt-1 block w-full bg-gray-700 border-gray-600 text-white" />
                        <InputError :message="page.props.errors?.email" class="mt-2" />
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" @click="closeModals"
                            class="inline-flex items-center gap-2 bg-white/10 border border-white/20 text-white font-bold py-2 px-4 rounded-full text-xs shadow-lg hover:scale-105 hover:bg-white/20 transition-transform duration-200">
                            Batal
                        </button>
                        <button type="submit" :disabled="processing"
                            class="inline-flex items-center gap-2 bg-gradient-to-r from-blue-400 to-sky-400 text-white font-bold py-2 px-4 rounded-full text-xs shadow-lg hover:scale-105 hover:from-blue-500 hover:to-sky-500 transition-transform duration-200">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Modal -->
        <Modal :show="showDeleteModal" @close="closeModals">
            <div class="p-6 bg-gray-800 text-white rounded-lg">
                <h2 class="text-lg font-medium text-white mb-4">
                    Hapus Salesman
                </h2>

                <p class="text-sm text-gray-400 mb-4">
                    Apakah Anda yakin ingin menghapus salesman
                    <strong>{{ deletingItem?.name }}</strong>?
                    Tindakan ini tidak dapat dibatalkan.
                </p>

                <div class="flex justify-end space-x-3">
                    <button type="button" @click="closeModals"
                        class="inline-flex items-center gap-2 bg-white/10 border border-white/20 text-white font-bold py-2 px-4 rounded-full text-xs shadow-lg hover:scale-105 hover:bg-white/20 transition-transform duration-200">
                        Batal
                    </button>
                    <button @click="deleteSalesman" :disabled="processing"
                        class="inline-flex items-center gap-2 bg-gradient-to-r from-red-500 to-rose-500 text-white font-bold py-2 px-4 rounded-full text-xs shadow-lg hover:scale-105 hover:from-red-600 hover:to-rose-600 transition-transform duration-200">
                        Hapus
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>