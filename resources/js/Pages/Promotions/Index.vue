<script setup>
import { ref, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    promotions: Object,
    filters: Object,
});

const search = ref(props.filters?.search ?? '');

watch(search, (value) => {
    router.get(route('promotions.index'), { search: value }, { preserveState: true, replace: true });
});

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID');
};
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Master Promosi" />

        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-xl text-white leading-tight">Master Promosi</h2>
                    <p class="text-sm text-gray-400 mt-1">Kelola kode promosi unik dengan tanggal berlaku dan jenis
                        promosi.</p>
                </div>
                <div>
                    <button @click="$inertia.visit(route('promotions.create'))"
                        class="inline-flex items-center bg-gradient-to-r from-green-400 to-emerald-400 text-white font-bold py-3 px-6 rounded-full text-sm shadow-lg hover:scale-105 hover:from-green-500 hover:to-emerald-500 transition-transform duration-200">
                        Tambah Promosi
                    </button>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
            <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl shadow-xl overflow-hidden">
                <div class="flex items-center gap-3 p-4">
                    <input v-model="search" type="text" placeholder="Cari kode promosi..."
                        class="bg-white/5 border border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500 px-4 py-2 w-full max-w-sm" />
                    <button @click="$inertia.reload({ only: ['promotions'] })"
                        class="bg-gradient-to-r from-blue-400 to-sky-400 text-white font-bold py-2 px-4 rounded-lg hover:from-blue-500 hover:to-sky-500 transition">
                        Refresh
                    </button>
                </div>

                <div class="overflow-x-auto">
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
                                    class="px-6 py-4 text-right text-xs uppercase tracking-wider font-semibold text-white/90">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700/50">
                            <tr v-for="promotion in props.promotions.data" :key="promotion.id"
                                class="hover:bg-white/5 transition-all duration-200">
                                <td class="px-6 py-4 font-mono">{{ promotion.name }}</td>
                                <td class="px-6 py-4">{{ formatDate(promotion.start_date) }}</td>
                                <td class="px-6 py-4">{{ formatDate(promotion.end_date) }}</td>
                                <td class="px-6 py-4">{{ promotion.promotion_type }}</td>
                                <td class="px-6 py-4 text-right">
                                    <button @click="$inertia.visit(route('promotions.edit', promotion.id))"
                                        class="inline-flex items-center px-4 py-2 rounded-full text-xs font-bold bg-gradient-to-r from-blue-400 to-blue-500 text-white shadow-lg hover:scale-105 hover:from-blue-500 hover:to-blue-600 transition-transform duration-200">
                                        Edit
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <Pagination :links="props.promotions.links" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
