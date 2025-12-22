<script setup>
import { ref, watch } from 'vue';
import { debounce } from 'lodash';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    promotions: Object,
    filters: Object,
});

const viewMode = ref('cards');
const search = ref(props.filters?.search ?? '');

watch(
    () => search.value,
    debounce((value) => {
        router.get(route('promotions.index'), { search: value }, { preserveState: true, replace: true });
    }, 300),
);

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID');
};

const deletePromotion = (promotion) => {
    if (confirm('Apakah Anda yakin ingin menghapus promosi ini?')) {
        router.delete(route('promotions.destroy', promotion.id));
    }
};

const clearProductsCustomers = (promotion) => {
    if (confirm('Apakah Anda yakin ingin mengosongkan produk dan tipe customer untuk promosi ini?')) {
        router.patch(route('promotions.clear', promotion.id));
    }
};

const formatProductsCustomers = (promotion) => {
    const productsText = promotion.products_count > 0 ? `${promotion.products_count} produk dipilih` : 'Semua produk';
    const customersText = promotion.customer_types_count > 0 ? `${promotion.customer_types_count} tipe customer dipilih` : 'Semua tipe customer';
    return { productsText, customersText };
};
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Master Promosi" />

        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-lg text-[#1f1f1f] leading-tight">
                        Master Promosi
                    </h2>
                    <p class="text-xs text-[#555] mt-1">
                        Kelola kode promosi unik dengan tanggal berlaku dan jenis promosi.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
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

                    <button @click="$inertia.visit(route('promotions.create'))"
                        class="inline-flex items-center justify-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] font-semibold py-3 px-6 rounded text-xs hover:bg-white transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Promosi
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
                    <input v-model="search" type="text"
                        class="w-full lg:w-72 bg-white border-[#9c9c9c] rounded-lg text-[#1f1f1f] focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Cari kode promosi..." />
                </div>

                <div class="flex flex-wrap gap-3 justify-end">
                    <button @click="$inertia.reload({ only: ['promotions'] })"
                        class="inline-flex items-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] font-semibold py-3 px-5 rounded text-xs hover:bg-white transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v2a2 2 0 002 2h12a2 2 0 002-2V4M4 12l4-4m0 0l4 4m-4-4v12" />
                        </svg>
                        Refresh
                    </button>
                </div>
            </div>

            <div v-if="props.promotions.data.length">
                <div v-if="viewMode === 'table'"
                    class="bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-sm overflow-hidden">
                    <div class="hidden lg:block overflow-x-auto">
                        <table class="min-w-full text-xs text-[#1f1f1f]">
                            <thead
                                class="bg-[#efefef] border-b border-[#9c9c9c]">
                                <tr>
                                    <th
                                        class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-[#1f1f1f]">
                                        Kode Promosi</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-[#1f1f1f]">
                                        Tanggal Mulai</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-[#1f1f1f]">
                                        Tanggal Berakhir</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-[#1f1f1f]">
                                        Jenis Promosi</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-[#1f1f1f]">
                                        Produk</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-[#1f1f1f]">
                                        Customer</th>
                                    <th
                                        class="px-6 py-4 text-right text-xs uppercase tracking-wider font-semibold text-[#1f1f1f]">
                                        Aksi</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-[#d0d0d0]">
                                <tr v-for="promotion in props.promotions.data" :key="promotion.id"
                                    class="hover:bg-white transition-colors duration-150">
                                    <td class="px-6 py-4 font-mono">{{ promotion.name }}</td>
                                    <td class="px-6 py-4">{{ formatDate(promotion.start_date) }}</td>
                                    <td class="px-6 py-4">{{ formatDate(promotion.end_date) }}</td>
                                    <td class="px-6 py-4">{{ promotion.promotion_type }}</td>
                                    <td class="px-6 py-4">{{ formatProductsCustomers(promotion).productsText }}</td>
                                    <td class="px-6 py-4">{{ formatProductsCustomers(promotion).customersText }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-end gap-2">
                                            <button @click="clearProductsCustomers(promotion)"
                                                class="inline-flex items-center px-4 py-2 rounded text-xs font-bold bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] hover:bg-white transition-colors">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Kosongkan
                                            </button>
                                            <button @click="$inertia.visit(route('promotions.edit', promotion.id))"
                                                class="inline-flex items-center px-4 py-2 rounded text-xs font-bold bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] hover:bg-white transition-colors">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </button>
                                            <button @click="deletePromotion(promotion)"
                                                class="inline-flex items-center px-4 py-2 rounded text-xs font-bold bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] hover:bg-white transition-colors">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
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

                    <div class="lg:hidden overflow-x-auto">
                        <table class="min-w-full text-xs text-[#1f1f1f]">
                            <thead
                                class="bg-[#efefef] border-b border-[#9c9c9c]">
                                <tr>
                                    <th class="px-3 py-3 text-left font-semibold text-[#1f1f1f]">Promosi</th>
                                    <th class="px-3 py-3 text-left font-semibold text-[#1f1f1f]">Tanggal</th>
                                    <th class="px-3 py-3 text-left font-semibold text-[#1f1f1f]">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#d0d0d0]">
                                <tr v-for="promotion in props.promotions.data" :key="promotion.id"
                                    class="hover:bg-white">
                                    <td class="px-3 py-3">
                                        <div class="font-semibold text-[#1f1f1f] text-xs">{{ promotion.name }}</div>
                                        <div class="text-[11px] text-[#555]">{{ promotion.promotion_type }}</div>
                                    </td>
                                    <td class="px-3 py-3 text-[11px] text-[#555]">{{ formatDate(promotion.start_date)
                                    }} - {{
                                            formatDate(promotion.end_date) }}</td>
                                    <td class="px-3 py-3">
                                        <div class="flex gap-1">
                                            <button @click="clearProductsCustomers(promotion)"
                                                class="inline-flex items-center justify-center px-3 py-2 rounded text-[11px] font-bold bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c]">
                                                Kosongkan
                                            </button>
                                            <button @click="$inertia.visit(route('promotions.edit', promotion.id))"
                                                class="inline-flex items-center justify-center px-3 py-2 rounded text-[11px] font-bold bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c]">
                                                Edit
                                            </button>
                                            <button @click="deletePromotion(promotion)"
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

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                    <div v-for="promotion in props.promotions.data" :key="promotion.id"
                        class="bg-[#f7f7f7] border border-[#9c9c9c] rounded p-6 shadow-sm hover:shadow-2xl hover:bg-[#f0f0f0] transition-all duration-300 hover:-translate-y-1">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="font-semibold text-[#1f1f1f] text-xs">{{ promotion.name }}</h3>
                                <p class="text-xs text-[#555] mt-1">{{ promotion.description }}</p>
                            </div>
                            <span
                                class="inline-flex px-3 py-1 rounded text-xs font-medium border border-[#9c9c9c] bg-[#f0f0f0] text-[#1f1f1f]">
                                {{ promotion.promotion_type }}
                            </span>
                        </div>

                        <div class="space-y-3">
                            <div class="flex justify-between text-xs text-[#555]">
                                <span>Tanggal Mulai</span>
                                <span>{{ formatDate(promotion.start_date) }}</span>
                            </div>
                            <div class="flex justify-between text-xs text-[#555]">
                                <span>Tanggal Berakhir</span>
                                <span>{{ formatDate(promotion.end_date) }}</span>
                            </div>
                            <div class="flex justify-between text-xs text-[#555]">
                                <span>Status</span>
                                <span class="text-green-400">{{ promotion.is_active ? 'Aktif' : 'Tidak Aktif' }}</span>
                            </div>
                            <div class="flex justify-between text-xs text-[#555]">
                                <span>Produk</span>
                                <span>{{ formatProductsCustomers(promotion).productsText }}</span>
                            </div>
                            <div class="flex justify-between text-xs text-[#555]">
                                <span>Customer</span>
                                <span>{{ formatProductsCustomers(promotion).customersText }}</span>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-3 pt-4 border-t border-[#d0d0d0] mt-4">
                            <button @click="clearProductsCustomers(promotion)"
                                class="inline-flex items-center px-4 py-2 rounded text-xs font-bold bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] hover:bg-white transition-colors">
                                <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Kosongkan
                            </button>
                            <button @click="$inertia.visit(route('promotions.edit', promotion.id))"
                                class="inline-flex items-center px-4 py-2 rounded text-xs font-bold bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] hover:bg-white transition-colors">
                                <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit Promosi
                            </button>
                            <button @click="deletePromotion(promotion)"
                                class="inline-flex items-center px-4 py-2 rounded text-xs font-bold bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] hover:bg-white transition-colors">
                                <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Hapus Promosi
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-12 bg-[#f7f7f7] border border-[#9c9c9c] rounded">
                <svg class="mx-auto h-12 w-12 text-[#555] mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="text-sm font-semibold text-[#555] mb-2">Belum ada promosi</h3>
                <p class="text-xs text-[#555]">Tambah promosi baru untuk memulai.</p>
            </div>

            <Pagination v-if="props.promotions?.links" :links="props.promotions.links" />
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
select option {
    background-color: #ffffff;
    color: #1f1f1f;
}
</style>
