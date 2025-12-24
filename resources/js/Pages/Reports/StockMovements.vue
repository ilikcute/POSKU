<template>
    <AuthenticatedLayout>
        <Head title="Laporan Pergerakan Stok" />

        <template #header>
            <div>
                <h2 class="font-semibold text-lg text-[#1f1f1f]">Laporan Pergerakan Stok</h2>
                <p class="text-xs text-[#555]">Riwayat mutasi stok per periode.</p>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded p-4">
                <form @submit.prevent="filterMovements">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-[#1f1f1f] mb-1">Tanggal Mulai</label>
                            <input type="date" v-model="filters.start_date"
                                class="w-full border border-[#9c9c9c] rounded px-3 py-2 bg-white text-[#1f1f1f]">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[#1f1f1f] mb-1">Tanggal Akhir</label>
                            <input type="date" v-model="filters.end_date"
                                class="w-full border border-[#9c9c9c] rounded px-3 py-2 bg-white text-[#1f1f1f]">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[#1f1f1f] mb-1">Pencarian</label>
                            <input type="text" v-model="filters.search" placeholder="Cari produk..."
                                class="w-full border border-[#9c9c9c] rounded px-3 py-2 bg-white text-[#1f1f1f]">
                        </div>
                        <div class="flex items-end">
                            <button type="submit"
                                class="bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] px-4 py-2 text-xs font-semibold shadow-sm hover:bg-white transition-colors">
                                Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="bg-white border border-[#9c9c9c] rounded overflow-hidden">
                <table class="min-w-full divide-y divide-[#d0d0d0] text-sm text-[#1f1f1f]">
                    <thead class="bg-[#efefef]">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Produk</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Tipe</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Qty</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Alasan</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">User</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#d0d0d0]">
                        <tr v-for="movement in movements.data" :key="movement.id" class="hover:bg-[#f7f7f7]">
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                {{ new Date(movement.created_at).toLocaleDateString('id-ID') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium">{{ movement.product?.name || '' }}</div>
                                <div class="text-xs text-[#555]">{{ movement.product?.product_code || '' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    :class="movement.movement_type === 'in' ? 'bg-[#e1f3e1] text-[#1f7a1f]' : 'bg-[#ffe1e1] text-[#8a1f1f]'"
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded">
                                    {{ movement.movement_type === 'in' ? 'Stock In' : 'Stock Out' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                {{ movement.quantity }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-[#555]">
                                {{ movement.reason }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-[#555]">
                                {{ movement.user?.name || 'System' }}
                            </td>
                        </tr>
                        <tr v-if="!movements.data.length">
                            <td colspan="6" class="px-6 py-6 text-center text-[#777]">
                                Belum ada data.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination v-if="movements.links" :links="movements.links" />
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    movements: Object,
    filters: Object,
});

const filters = ref({
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
    search: props.filters.search || '',
});

const filterMovements = () => {
    router.get(route('reports.stock-movements'), filters.value, { preserveState: true, replace: true });
};
</script>
