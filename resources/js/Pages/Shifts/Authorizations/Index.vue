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
    if (name === 'Tutup Harian') return { label: 'EOD', cls: 'bg-[#e1f3e1] text-[#1f1f1f] border-[#9c9c9c]' };
    if (name === 'Buka Shift' || name === 'Tutup Shift') return { label: 'SHIFT', cls: 'bg-[#dbe7ff] text-[#1f1f1f] border-[#9c9c9c]' };
    return { label: 'LAINNYA', cls: 'bg-[#f0f0f0] text-[#1f1f1f] border-[#9c9c9c]' };
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
                <div>
                    <h2 class="font-semibold text-lg text-[#1f1f1f] leading-tight">Kelola Authorization</h2>
                    <p class="text-xs text-[#555]">Atur password verifikasi untuk shift dan EOD.</p>
                </div>

                <Link :href="route('shifts.authorizations.create')"
                    class="inline-flex items-center px-4 py-2 bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] rounded text-xs font-semibold shadow-sm hover:bg-white transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Tambah Authorization
                </Link>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white border border-[#9c9c9c] rounded overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-[#1f1f1f]">
                        <thead class="bg-[#efefef] border-b border-[#9c9c9c]">
                            <tr>
                                <!-- Tambah kolom kategori -->
                                <th
                                    class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold">
                                    Kategori
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold">
                                    Nama
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold">
                                    Dibuat Pada
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold">
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-[#d0d0d0]">
                            <tr v-for="authorization in props.authorizations.data" :key="authorization.id"
                                class="hover:bg-[#f7f7f7] transition-colors group">
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold border"
                                        :class="categorizeAuth(authorization.name).cls">
                                        {{ categorizeAuth(authorization.name).label }}
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="font-medium text-[#1f1f1f]">{{ authorization.name }}</div>

                                    <!-- Info khusus EOD biar user paham -->
                                    <div v-if="authorization.name === 'Tutup Harian'"
                                        class="text-xs text-[#555] mt-1">
                                        Dipakai untuk Station Close & Finalize EOD
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-[#555]">
                                    {{ formatDateTime(authorization.created_at) }}
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex space-x-2">
                                        <Link :href="route('shifts.authorizations.edit', authorization.id)"
                                            class="inline-flex items-center px-3 py-1 bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] rounded text-xs font-medium hover:bg-white transition-colors">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Edit
                                        </Link>

                                        <button @click="deleteAuthorization(authorization)"
                                            class="inline-flex items-center px-3 py-1 bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] rounded text-xs font-medium hover:bg-white transition-colors">
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
                class="text-center py-12 bg-[#f7f7f7] border border-[#9c9c9c] rounded">
                <svg class="mx-auto h-12 w-12 text-[#555] mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <h3 class="text-lg font-medium text-[#1f1f1f] mb-2">Belum ada authorization</h3>
                <p class="text-[#555]">Tambahkan authorization untuk mengelola password verifikasi.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
