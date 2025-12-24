<template>
    <AuthenticatedLayout>
        <Head title="Setting Station" />
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-lg text-[#1f1f1f] leading-tight">
                        Setting Station
                    </h2>
                    <p class="text-xs text-[#555] mt-1">
                        Kelola komputer/terminal kasir yang terhubung ke toko.
                    </p>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="flex bg-[#f7f7f7] border border-[#9c9c9c] rounded p-1">
                        <button @click="viewMode = 'table'" :class="[
                            'px-3 py-2 rounded text-xs font-semibold transition-colors',
                            viewMode === 'table'
                                ? 'bg-white text-[#1f1f1f]'
                                : 'text-[#555] hover:text-[#1f1f1f] hover:bg-[#f0f0f0]'
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
                                : 'text-[#555] hover:text-[#1f1f1f] hover:bg-[#f0f0f0]'
                        ]">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14-7H5a2 2 0 00-2 2v12a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2z" />
                            </svg>
                            Cards
                        </button>
                    </div>
                    <button @click="openModal(false)"
                        class="inline-flex items-center justify-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] font-semibold py-3 px-6 rounded text-xs hover:bg-white transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Station
                    </button>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-sm p-6 mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex w-full lg:w-auto items-center gap-3">
                    <svg class="w-5 h-5 text-[#555]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
                    </svg>
                    <TextInput v-model="search" type="text"
                        class="w-full lg:w-72 bg-white border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Cari nama atau device..." />
                </div>
            </div>

            <div v-if="stations.data.length">
                <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-sm overflow-hidden">
                    <div v-if="viewMode === 'table'">
                        <div class="hidden lg:block overflow-x-auto">
                            <table class="min-w-full text-xs text-[#1f1f1f]">
                                <thead class="bg-[#efefef] border-b border-[#9c9c9c]">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-[#1f1f1f]">
                                            Station
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-[#1f1f1f]">
                                            Komputer
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-[#1f1f1f]">
                                            Status
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-[#1f1f1f]">
                                            Terakhir Aktif
                                        </th>
                                        <th class="px-6 py-4 text-right text-xs uppercase tracking-wider font-semibold text-[#1f1f1f]">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#d0d0d0]">
                                    <tr v-for="station in stations.data" :key="station.id"
                                        class="hover:bg-white transition-colors duration-150">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="w-10 h-10 rounded bg-[#dbe7ff] flex items-center justify-center text-xs font-bold">
                                                    {{ station.name.charAt(0).toUpperCase() }}
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-[#1f1f1f]">{{ station.name }}</p>
                                                    <p class="text-[11px] text-[#555]">{{ station.description || '-' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 font-mono text-xs text-[#555]">
                                            {{ station.device_identifier }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-2 py-1 text-[10px] font-semibold border border-[#9c9c9c]"
                                                :class="station.is_active ? 'bg-[#e1f3e1]' : 'bg-[#f0f0f0]'">
                                                {{ station.is_active ? 'AKTIF' : 'NONAKTIF' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-xs text-[#555]">
                                            {{ formatDate(station.last_seen_at) }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex justify-end gap-2">
                                                <button @click="openModal(true, station)"
                                                    class="inline-flex items-center px-4 py-2 rounded text-xs font-bold bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] hover:bg-white transition-colors">
                                                    Edit
                                                </button>
                                                <button @click="deleteStation(station.id)"
                                                    class="inline-flex items-center px-4 py-2 rounded text-xs font-bold bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] hover:bg-white transition-colors">
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
                                <thead class="bg-[#efefef] border-b border-[#9c9c9c]">
                                    <tr>
                                        <th class="px-3 py-3 text-left font-semibold text-[#1f1f1f]">Station</th>
                                        <th class="px-3 py-3 text-left font-semibold text-[#1f1f1f]">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#d0d0d0]">
                                    <tr v-for="station in stations.data" :key="station.id" class="hover:bg-white">
                                        <td class="px-3 py-3">
                                            <div class="font-semibold text-[#1f1f1f] text-xs">{{ station.name }}</div>
                                            <div class="text-[11px] text-[#555] font-mono">{{ station.device_identifier }}</div>
                                            <div class="text-[10px] text-[#555] mt-1">
                                                {{ station.is_active ? 'Aktif' : 'Nonaktif' }}
                                            </div>
                                        </td>
                                        <td class="px-3 py-3">
                                            <div class="flex flex-col gap-2">
                                                <button @click="openModal(true, station)"
                                                    class="inline-flex items-center justify-center px-3 py-2 rounded text-[11px] font-bold bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c]">
                                                    Edit
                                                </button>
                                                <button @click="deleteStation(station.id)"
                                                    class="inline-flex items-center justify-center px-3 py-2 rounded text-[11px] font-bold bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c]">
                                                    Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div v-else>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
                            <div v-for="station in stations.data" :key="station.id"
                                class="bg-[#f7f7f7] border border-[#9c9c9c] rounded p-6 shadow-sm hover:shadow-2xl hover:bg-[#f0f0f0] transition-all duration-300 hover:-translate-y-1">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded bg-[#dbe7ff] flex items-center justify-center text-sm font-bold">
                                            {{ station.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-[#1f1f1f]">{{ station.name }}</h3>
                                            <p class="text-[11px] text-[#555] font-mono">{{ station.device_identifier }}</p>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-1 text-[10px] font-semibold border border-[#9c9c9c]"
                                        :class="station.is_active ? 'bg-[#e1f3e1]' : 'bg-[#f0f0f0]'">
                                        {{ station.is_active ? 'AKTIF' : 'NONAKTIF' }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-[#555] min-h-[32px]">{{ station.description || '-' }}</p>
                                <div class="mt-4 text-[11px] text-[#555]">
                                    Terakhir aktif: {{ formatDate(station.last_seen_at) }}
                                </div>
                                <div class="flex flex-wrap gap-3 pt-4 border-t border-[#d0d0d0] mt-4">
                                    <button @click="openModal(true, station)"
                                        class="inline-flex items-center px-4 py-2 rounded text-xs font-bold bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] hover:bg-white transition-colors">
                                        Edit
                                    </button>
                                    <button @click="deleteStation(station.id)"
                                        class="inline-flex items-center px-4 py-2 rounded text-xs font-bold bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] hover:bg-white transition-colors">
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-12 bg-[#f7f7f7] border border-[#9c9c9c] rounded">
                <svg class="mx-auto h-12 w-12 text-[#555] mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <h3 class="text-sm font-semibold text-[#555] mb-2">Belum ada station</h3>
                <p class="text-[#555]">Tambahkan station untuk menghubungkan komputer kasir.</p>
            </div>

            <Pagination v-if="stations.links" :links="stations.links" />
        </div>

        <Modal :show="isModalOpen" @close="closeModal">
            <div class="p-8 bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-sm max-w-4xl mx-auto space-y-6 text-[#1f1f1f]">
                <h2 class="text-sm font-semibold">
                    {{ isEditMode ? "Edit Station" : "Tambah Station Baru" }}
                </h2>
                <form @submit.prevent="saveStation" class="mt-6 grid grid-cols-1 gap-6">
                    <div>
                        <InputLabel for="name" value="Nama Station" class="text-[#555]" />
                        <TextInput id="name" v-model="form.name"
                            class="mt-1 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500"
                            required />
                        <InputError :message="form.errors.name" class="mt-2 text-red-400" />
                    </div>
                    <div>
                        <InputLabel for="device_identifier" value="Nama Komputer/Device" class="text-[#555]" />
                        <TextInput id="device_identifier" v-model="form.device_identifier"
                            class="mt-1 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500"
                            required />
                        <InputError :message="form.errors.device_identifier" class="mt-2 text-red-400" />
                    </div>
                    <div>
                        <InputLabel for="description" value="Catatan" class="text-[#555]" />
                        <textarea id="description" v-model="form.description" rows="3"
                            class="mt-1 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500"></textarea>
                        <InputError :message="form.errors.description" class="mt-2 text-red-400" />
                    </div>
                    <div>
                        <InputLabel for="is_active" value="Status" class="text-[#555]" />
                        <select id="is_active" v-model="form.is_active"
                            class="mt-1 block w-full bg-white border-[#9c9c9c] rounded text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500">
                            <option :value="true">Aktif</option>
                            <option :value="false">Nonaktif</option>
                        </select>
                        <InputError :message="form.errors.is_active" class="mt-2 text-red-400" />
                    </div>
                    <div class="flex justify-end gap-3 mt-4">
                        <button type="button" @click="closeModal"
                            class="inline-block bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] font-semibold py-2 px-6 rounded text-xs hover:bg-white transition-colors">
                            Batal
                        </button>
                        <button type="submit" :disabled="form.processing"
                            class="inline-block bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] font-semibold py-2 px-6 rounded text-xs hover:bg-white transition-colors"
                            :class="{ 'opacity-25': form.processing }">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, router } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import { debounce } from "lodash";

import Modal from "@/Components/Modal.vue";
import Pagination from "@/Components/Pagination.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    stations: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const viewMode = ref('table');
const isModalOpen = ref(false);
const isEditMode = ref(false);
const search = ref(props.filters?.search ?? '');

const form = useForm({
    id: null,
    name: "",
    device_identifier: "",
    description: "",
    is_active: true,
});

const onSearch = () => {
    router.get(
        route("stations.index"),
        { search: search.value },
        {
            preserveState: true,
            replace: true,
        }
    );
};

watch(
    search,
    debounce(() => {
        onSearch();
    }, 300)
);

const openModal = (editMode = false, station = null) => {
    isEditMode.value = editMode;
    if (editMode && station) {
        form.id = station.id;
        form.name = station.name;
        form.device_identifier = station.device_identifier;
        form.description = station.description || "";
        form.is_active = station.is_active;
    } else {
        form.reset();
        form.is_active = true;
    }
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const saveStation = () => {
    if (isEditMode.value) {
        form.patch(route("stations.update", form.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route("stations.store"), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteStation = (id) => {
    if (confirm("Apakah Anda yakin ingin menghapus station ini?")) {
        useForm({}).delete(route("stations.destroy", id));
    }
};

const formatDate = (value) => {
    if (!value) return '-';
    return new Date(value).toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>
