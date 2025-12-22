<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const user = page.props.auth.user;

const props = defineProps({
    authorizations: { type: Object, required: true },
});

const formatDateTime = (dateTime) => {
    const options = {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    };
    return new Date(dateTime).toLocaleDateString(undefined, options);
};

// === Tambahkan ini ===
const categorizeAuth = (name) => {
    if (name === 'Tutup Harian') return { label: 'EOD', cls: 'bg-emerald-500/20 text-emerald-200 border-emerald-500/30' };
    if (name === 'Buka Shift' || name === 'Tutup Shift') return { label: 'SHIFT', cls: 'bg-blue-500/20 text-blue-200 border-blue-500/30' };
    return { label: 'LAINNYA', cls: 'bg-gray-500/20 text-gray-200 border-gray-500/30' };
};

const deleteAuthorization = (authorization) => {
    if (confirm(`Apakah Anda yakin ingin menghapus authorization "${authorization.name}"?`)) {
        router.delete(route('shifts.authorizations.destroy', authorization.id));
    }
};
</script>

<template>

    <Head title="Kelola Authorization" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                <h2 class="font-semibold text-xl text-white leading-tight">Kelola Authorization</h2>

                <Link :href="route('shifts.authorizations.create')"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-purple-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:from-blue-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-gray-800 transition-all duration-200 shadow-lg hover:shadow-xl">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Tambah Authorization
                </Link>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl shadow-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-gray-200">
                        <thead
                            class="bg-gradient-to-r from-purple-600/30 to-blue-600/30 backdrop-blur-sm border-b border-white/20">
                            <tr>
                                <!-- Tambah kolom kategori -->
                                <th
                                    class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-white/90">
                                    Kategori
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-white/90">
                                    Nama
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-white/90">
                                    Dibuat Pada
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-white/90">
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-700/50">
                            <tr v-for="authorization in props.authorizations.data" :key="authorization.id"
                                class="hover:bg-white/5 transition-all duration-200 group">
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-semibold border"
                                        :class="categorizeAuth(authorization.name).cls">
                                        {{ categorizeAuth(authorization.name).label }}
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="font-medium text-white">{{ authorization.name }}</div>

                                    <!-- Info khusus EOD biar user paham -->
                                    <div v-if="authorization.name === 'Tutup Harian'"
                                        class="text-xs text-gray-400 mt-1">
                                        Dipakai untuk Station Close & Finalize EOD
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-gray-400">
                                    {{ formatDateTime(authorization.created_at) }}
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex space-x-2">
                                        <Link :href="route('shifts.authorizations.edit', authorization.id)"
                                            class="inline-flex items-center px-3 py-1 bg-blue-500/20 text-blue-300 border border-blue-500/30 rounded-lg text-xs font-medium hover:bg-blue-500/30 transition-all duration-200">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Edit
                                        </Link>

                                        <button @click="deleteAuthorization(authorization)"
                                            class="inline-flex items-center px-3 py-1 bg-red-500/20 text-red-300 border border-red-500/30 rounded-lg text-xs font-medium hover:bg-red-500/30 transition-all duration-200">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
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
            </div>

            <Pagination :links="props.authorizations.links" />

            <div v-if="!props.authorizations.data.length"
                class="text-center py-12 backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <h3 class="text-lg font-medium text-gray-300 mb-2">Belum ada authorization</h3>
                <p class="text-gray-400">Tambahkan authorization untuk mengelola password verifikasi.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
