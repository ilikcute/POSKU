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
                    <h2 class="font-semibold text-xl text-white leading-tight">
                        Master Promosi
                    </h2>
                    <p class="text-sm text-gray-400 mt-1">
                        Kelola kode promosi unik dengan tanggal berlaku dan jenis promosi.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="flex bg-gray-800/50 rounded-lg p-1 backdrop-blur-sm">
                        <button @click="viewMode = 'table'" :class="[
                            'px-3 py-2 rounded-md text-sm font-medium transition-all duration-200',
                            viewMode === 'table'
                                ? 'bg-white/20 text-white shadow-sm'
                                : 'text-gray-300 hover:text-white hover:bg-white/10'
                        ]">

                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 10h18M3 6h18m-9 10h9" />
                            </svg>
                            Table
                        </button>
                        <button @click="viewMode = 'cards'" :class="[
                            'px-3 py-2 rounded-md text-sm font-medium transition-all duration-200',
                            viewMode === 'cards'
                                ? 'bg-white/20 text-white shadow-sm'
                                : 'text-gray-300 hover:text-white hover:bg-white/10'
                        ]">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14-7H5a2 2 0 00-2 2v12a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2z" />
                            </svg>
                            Cards
                        </button>
                    </div>

                    <button @click="$inertia.visit(route('promotions.create'))"
                        class="inline-flex items-center justify-center bg-gradient-to-r from-green-400 to-emerald-400 text-white font-bold py-3 px-6 rounded-full text-sm shadow-xl hover:scale-105 hover:from-green-500 hover:to-emerald-500 transition-transform duration-200">
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
                class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl shadow-xl p-6 mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex w-full lg:w-auto items-center gap-3">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
                    </svg>
                    <input v-model="search" type="text"
                        class="w-full lg:w-72 bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Cari kode promosi..." />
                </div>

                <div class="flex flex-wrap gap-3 justify-end">
                    <button @click="$inertia.reload({ only: ['promotions'] })"
                        class="inline-flex items-center bg-gradient-to-r from-blue-400 to-sky-400 text-white font-bold py-3 px-5 rounded-full text-sm shadow-lg hover:scale-105 hover:from-blue-500 hover:to-sky-500 transition-transform duration-200">
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
                    class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl shadow-xl overflow-hidden">
                    <div class="hidden lg:block overflow-x-auto">
                        <table class="min-w-full text-sm text-gray-200">
                            <thead
                                class="bg-gradient-to-r from-purple-600/30 to-blue-600/30 backdrop-blur-sm border-b border-white/20">
                                <tr>
                                    <th
                                        class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-white/90">
                                        Kode Promosi</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-white/90">
                                        Tanggal Mulai</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-white/90">
                                        Tanggal Berakhir</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-white/90">
                                        Jenis Promosi</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-white/90">
                                        Produk</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs uppercase tracking-wider font-semibold text-white/90">
                                        Customer</th>
                                    <th
                                        class="px-6 py-4 text-right text-xs uppercase tracking-wider font-semibold text-white/90">
                                        Aksi</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-700/50">
                                <tr v-for="promotion in props.promotions.data" :key="promotion.id"
                                    class="hover:bg-white/5 transition-all duration-200 group">
                                    <td class="px-6 py-4 font-mono">{{ promotion.name }}</td>
                                    <td class="px-6 py-4">{{ formatDate(promotion.start_date) }}</td>
                                    <td class="px-6 py-4">{{ formatDate(promotion.end_date) }}</td>
                                    <td class="px-6 py-4">{{ promotion.promotion_type }}</td>
                                    <td class="px-6 py-4">{{ formatProductsCustomers(promotion).productsText }}</td>
                                    <td class="px-6 py-4">{{ formatProductsCustomers(promotion).customersText }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-end gap-2">
                                            <button @click="clearProductsCustomers(promotion)"
                                                class="inline-flex items-center px-4 py-2 rounded-full text-xs font-bold bg-gradient-to-r from-orange-400 to-orange-500 text-white shadow-lg hover:scale-105 hover:from-orange-500 hover:to-orange-600 transition-transform duration-200">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Kosongkan
                                            </button>
                                            <button @click="$inertia.visit(route('promotions.edit', promotion.id))"
                                                class="inline-flex items-center px-4 py-2 rounded-full text-xs font-bold bg-gradient-to-r from-blue-400 to-blue-500 text-white shadow-lg hover:scale-105 hover:from-blue-500 hover:to-blue-600 transition-transform duration-200">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </button>
                                            <button @click="deletePromotion(promotion)"
                                                class="inline-flex items-center px-4 py-2 rounded-full text-xs font-bold bg-gradient-to-r from-red-400 to-red-500 text-white shadow-lg hover:scale-105 hover:from-red-500 hover:to-red-600 transition-transform duration-200">
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
                        <table class="min-w-full text-xs text-gray-200">
                            <thead
                                class="bg-gradient-to-r from-purple-600/30 to-blue-600/30 backdrop-blur-sm border-b border-white/20">
                                <tr>
                                    <th class="px-3 py-3 text-left font-semibold text-white/90">Promosi</th>
                                    <th class="px-3 py-3 text-left font-semibold text-white/90">Tanggal</th>
                                    <th class="px-3 py-3 text-left font-semibold text-white/90">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700/50">
                                <tr v-for="promotion in props.promotions.data" :key="promotion.id"
                                    class="hover:bg-white/5">
                                    <td class="px-3 py-3">
                                        <div class="font-semibold text-white text-sm">{{ promotion.name }}</div>
                                        <div class="text-[11px] text-gray-400">{{ promotion.promotion_type }}</div>
                                    </td>
                                    <td class="px-3 py-3 text-[11px] text-gray-300">{{ formatDate(promotion.start_date)
                                    }} - {{
                                            formatDate(promotion.end_date) }}</td>
                                    <td class="px-3 py-3">
                                        <div class="flex gap-1">
                                            <button @click="clearProductsCustomers(promotion)"
                                                class="inline-flex items-center justify-center px-3 py-2 rounded-full text-[11px] font-bold bg-gradient-to-r from-orange-400 to-orange-500 text-white shadow-lg">
                                                Kosongkan
                                            </button>
                                            <button @click="$inertia.visit(route('promotions.edit', promotion.id))"
                                                class="inline-flex items-center justify-center px-3 py-2 rounded-full text-[11px] font-bold bg-gradient-to-r from-blue-400 to-blue-500 text-white shadow-lg">
                                                Edit
                                            </button>
                                            <button @click="deletePromotion(promotion)"
                                                class="inline-flex items-center justify-center px-3 py-2 rounded-full text-[11px] font-bold bg-gradient-to-r from-red-400 to-red-500 text-white shadow-lg">
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
                        class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl p-6 shadow-xl hover:shadow-2xl hover:bg-white/10 transition-all duration-300 hover:-translate-y-1">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="font-semibold text-white">{{ promotion.name }}</h3>
                                <p class="text-sm text-gray-400 mt-1">{{ promotion.description }}</p>
                            </div>
                            <span
                                class="inline-flex px-3 py-1 rounded-full text-xs font-medium border border-white/20 bg-white/10 text-gray-200">
                                {{ promotion.promotion_type }}
                            </span>
                        </div>

                        <div class="space-y-3">
                            <div class="flex justify-between text-sm text-gray-300">
                                <span>Tanggal Mulai</span>
                                <span>{{ formatDate(promotion.start_date) }}</span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-300">
                                <span>Tanggal Berakhir</span>
                                <span>{{ formatDate(promotion.end_date) }}</span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-300">
                                <span>Status</span>
                                <span class="text-green-400">{{ promotion.is_active ? 'Aktif' : 'Tidak Aktif' }}</span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-300">
                                <span>Produk</span>
                                <span>{{ formatProductsCustomers(promotion).productsText }}</span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-300">
                                <span>Customer</span>
                                <span>{{ formatProductsCustomers(promotion).customersText }}</span>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-3 pt-4 border-t border-gray-800/60 mt-4">
                            <button @click="clearProductsCustomers(promotion)"
                                class="inline-flex items-center px-4 py-2 rounded-full text-xs font-bold bg-gradient-to-r from-orange-400 to-orange-500 text-white shadow-lg hover:scale-105 hover:from-orange-500 hover:to-orange-600 transition-transform duration-200">
                                <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Kosongkan
                            </button>
                            <button @click="$inertia.visit(route('promotions.edit', promotion.id))"
                                class="inline-flex items-center px-4 py-2 rounded-full text-xs font-bold bg-gradient-to-r from-blue-400 to-blue-500 text-white shadow-lg hover:scale-105 hover:from-blue-500 hover:to-blue-600 transition-transform duration-200">
                                <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit Promosi
                            </button>
                            <button @click="deletePromotion(promotion)"
                                class="inline-flex items-center px-4 py-2 rounded-full text-xs font-bold bg-gradient-to-r from-red-400 to-red-500 text-white shadow-lg hover:scale-105 hover:from-red-500 hover:to-red-600 transition-transform duration-200">
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

            <div v-else class="text-center py-12 backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="text-lg font-medium text-gray-300 mb-2">Belum ada promosi</h3>
                <p class="text-gray-400">Tambah promosi baru untuk memulai.</p>
            </div>

            <Pagination v-if="props.promotions?.links" :links="props.promotions.links" />
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
select option {
    background-color: #374151;
    color: #f3f4f6;
}
</style>
