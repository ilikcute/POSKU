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
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Stok Opname</h2>
        </template>
        
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                 <div class="flex items-center justify-between mb-6">
                    <p class="text-gray-600">Lakukan penyesuaian jumlah stok fisik dengan data di sistem.</p>
                     <button class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Simpan Penyesuaian</button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Produk</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Stok Sistem</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Stok Fisik</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Selisih</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="product in products" :key="product.id">
                                <td class="px-6 py-4">{{ product.code }}</td>
                                <td class="px-6 py-4">{{ product.name }}</td>
                                <td class="px-6 py-4 text-center font-semibold">{{ product.system_stock }}</td>
                                <td class="px-6 py-4">
                                    <input type="number" class="w-24 text-center border-gray-300 rounded-md shadow-sm">
                                </td>
                                <td class="px-6 py-4 text-center font-bold text-gray-500">
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
