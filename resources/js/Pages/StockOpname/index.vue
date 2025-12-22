<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

// Contoh data produk (nanti akan dari props)
const products = ref([
    { id: 1, code: 'P001', name: 'Kopi Sachet', system_stock: 100 },
    { id: 2, code: 'P002', name: 'Teh Celup Box', system_stock: 50 },
    { id: 3, code: 'P003', name: 'Gula Pasir 1kg', system_stock: 75 },
]);
</script>

<template>
    <Head title="Stok Opname" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="font-semibold text-lg text-[#1f1f1f] leading-tight">Stok Opname</h2>
                <p class="text-xs text-[#555]">Sesuaikan stok fisik dengan data sistem.</p>
            </div>
        </template>
        
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">
            <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded p-4">
                 <div class="flex items-center justify-between mb-4">
                    <p class="text-[#555]">Lakukan penyesuaian jumlah stok fisik dengan data di sistem.</p>
                     <button class="px-4 py-2 bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] text-xs font-semibold shadow-sm hover:bg-white">
                        Simpan Penyesuaian
                     </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#d0d0d0] text-sm text-[#1f1f1f]">
                        <thead class="bg-[#efefef]">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Kode</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Nama Produk</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase">Stok Sistem</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase">Stok Fisik</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase">Selisih</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-[#d0d0d0]">
                            <tr v-for="product in products" :key="product.id">
                                <td class="px-6 py-4">{{ product.code }}</td>
                                <td class="px-6 py-4">{{ product.name }}</td>
                                <td class="px-6 py-4 text-center font-semibold">{{ product.system_stock }}</td>
                                <td class="px-6 py-4">
                                    <input type="number" class="w-24 text-center border border-[#9c9c9c] rounded shadow-sm">
                                </td>
                                <td class="px-6 py-4 text-center font-bold text-[#555]">
                                    -
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
