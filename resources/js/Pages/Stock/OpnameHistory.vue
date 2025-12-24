<template>
    <AuthenticatedLayout>
        <Head title="Riwayat Opname" />

        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="font-semibold text-lg text-[#1f1f1f]">Riwayat Opname</h2>
                    <p class="text-xs text-[#555]">Daftar dokumen opname per toko.</p>
                </div>
                <Link :href="route('stock.opname')"
                    class="inline-flex items-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] px-4 py-2 text-xs font-semibold shadow-sm hover:bg-white transition-colors">
                    Kembali ke Opname
                </Link>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white border border-[#9c9c9c] rounded overflow-hidden">
                <table class="min-w-full divide-y divide-[#d0d0d0] text-sm text-[#1f1f1f]">
                    <thead class="bg-[#efefef]">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Doc No</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Items</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Dibuat</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Adjusted</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#d0d0d0]">
                        <tr v-for="row in opnames.data" :key="row.id" class="hover:bg-[#f7f7f7]">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-[#1f1f1f]">
                                {{ row.docno }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span
                                    :class="row.status === 'A' ? 'bg-[#e1f3e1] text-[#1f7a1f]' : row.status === 'E' ? 'bg-[#fff2cc] text-[#8a6d3b]' : 'bg-[#dbe7ff] text-[#1f1f1f]'"
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded">
                                    {{ row.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ row.items_count }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-[#555]">
                                {{ formatDate(row.created_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-[#555]">
                                {{ row.adjusted_at ? formatDate(row.adjusted_at) : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <Link :href="route('stock.opname', { docno: row.docno })"
                                    class="text-[#1f1f1f] hover:underline">
                                    Buka
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="!opnames.data.length">
                            <td colspan="6" class="px-6 py-6 text-center text-[#777]">
                                Belum ada dokumen opname.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination v-if="opnames.links" :links="opnames.links" />
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    opnames: Object,
});

const formatDate = (value) => {
    if (!value) return '-';
    return new Date(value).toLocaleString('id-ID');
};
</script>
