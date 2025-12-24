<template>
    <AuthenticatedLayout>
        <Head title="Opname Stok" />

        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="font-semibold text-lg text-[#1f1f1f]">Opname Stok</h2>
                    <p class="text-xs text-[#555]">Input kode barang atau upload file untuk opname.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Link :href="route('stock.index')"
                        class="inline-flex items-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] px-4 py-2 text-xs font-semibold shadow-sm hover:bg-white transition-colors">
                        Kembali ke Stok
                    </Link>
                    <Link :href="route('stock.opname.history')"
                        class="inline-flex items-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] px-4 py-2 text-xs font-semibold shadow-sm hover:bg-white transition-colors">
                        Riwayat Opname
                    </Link>
                    <button @click="createDoc"
                        class="inline-flex items-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] px-4 py-2 text-xs font-semibold shadow-sm hover:bg-white transition-colors">
                        Buat Dokumen Opname
                    </button>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded p-4">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="text-xs text-[#555]">
                        <div>Doc No: <span class="font-semibold text-[#1f1f1f]">{{ docno || '-' }}</span></div>
                        <div>Status: <span class="font-semibold text-[#1f1f1f]">{{ opname?.status || '-' }}</span></div>
                    </div>
                    <form @submit.prevent="addItem" class="flex flex-wrap gap-3 items-end">
                        <div>
                            <label class="block text-xs font-semibold text-[#555]">Input Product Code</label>
                            <input v-model="productCode" type="text"
                                class="w-48 border border-[#9c9c9c] rounded px-3 py-2 bg-white text-[#1f1f1f]">
                        </div>
                        <button type="submit" :disabled="!docno"
                            class="bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] px-4 py-2 text-xs font-semibold shadow-sm hover:bg-white transition-colors disabled:opacity-50">
                            Tambah Item
                        </button>
                    </form>
                    <form @submit.prevent="uploadItems" class="flex flex-wrap gap-3 items-end">
                        <div>
                            <label class="block text-xs font-semibold text-[#555]">Upload Excel</label>
                            <input ref="uploadRef" type="file" @change="onFileChange"
                                class="w-56 border border-[#9c9c9c] rounded px-2 py-1 bg-white text-xs text-[#1f1f1f]"
                                accept=".xlsx,.csv,.txt">
                        </div>
                        <Link :href="route('stock.opname.template')"
                            class="bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] px-3 py-2 text-xs font-semibold shadow-sm hover:bg-white transition-colors">
                            Download Template
                        </Link>
                        <button type="submit" :disabled="!docno || !uploadFile"
                            class="bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] px-4 py-2 text-xs font-semibold shadow-sm hover:bg-white transition-colors disabled:opacity-50">
                            Upload
                        </button>
                    </form>
                </div>
            </div>

            <div class="bg-white border border-[#9c9c9c] rounded overflow-hidden">
                <table class="min-w-full divide-y divide-[#d0d0d0] text-sm text-[#1f1f1f]">
                    <thead class="bg-[#efefef]">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Produk</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Stok Sistem</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Stok Fisik</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Selisih</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#d0d0d0]">
                        <tr v-for="item in items" :key="item.id" class="hover:bg-[#f7f7f7]">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium">{{ item.product?.name || 'Unknown Product' }}</div>
                                <div class="text-xs text-[#555]">{{ item.product?.product_code || '' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                {{ item.system_qty }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="number" v-model.number="item.physical_qty"
                                    :disabled="item.status === 'A'"
                                    @change="updateItem(item)"
                                    class="w-24 border border-[#9c9c9c] rounded px-2 py-1 text-sm bg-white text-[#1f1f1f] disabled:bg-[#f0f0f0]">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                {{ (item.physical_qty || 0) - (item.system_qty || 0) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    :class="item.status === 'A' ? 'bg-[#e1f3e1] text-[#1f7a1f]' : item.status === 'E' ? 'bg-[#fff2cc] text-[#8a6d3b]' : 'bg-[#dbe7ff] text-[#1f1f1f]'"
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded">
                                    {{ item.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <button v-if="item.status !== 'A'" @click="deleteItem(item)"
                                    class="text-[#1f1f1f] hover:underline">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!items.length">
                            <td colspan="6" class="px-6 py-6 text-center text-[#777]">
                                Belum ada item. Tambahkan product_code atau upload file.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end">
                <button @click="finalizeOpname" :disabled="!docno || opname?.status === 'A'"
                    class="bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] px-5 py-2 text-xs font-semibold shadow-sm hover:bg-white transition-colors">
                    Finalize Opname
                </button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    docno: String,
    opname: Object,
    items: Array,
});

const productCode = ref('');
const uploadFile = ref(null);
const uploadRef = ref(null);

const createDoc = () => {
    router.post(route('stock.opname.create'));
};

const addItem = () => {
    if (!props.docno || !productCode.value) return;
    router.post(route('stock.opname.add-item'), {
        docno: props.docno,
        product_code: productCode.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            productCode.value = '';
        },
    });
};

const updateItem = (item) => {
    if (!item?.id || item.status === 'A') return;
    router.patch(route('stock.opname.item.update', item.id), {
        physical_qty: item.physical_qty ?? 0,
    }, { preserveScroll: true });
};

const deleteItem = (item) => {
    if (!item?.id || item.status === 'A') return;
    if (!confirm('Hapus item ini dari opname?')) return;
    router.delete(route('stock.opname.item.delete', item.id), { preserveScroll: true });
};

const finalizeOpname = () => {
    if (!props.docno) return;
    router.post(route('stock.opname.process'), { docno: props.docno });
};

const onFileChange = (event) => {
    uploadFile.value = event.target.files[0] || null;
};

const uploadItems = () => {
    if (!props.docno || !uploadFile.value) return;
    const formData = new FormData();
    formData.append('docno', props.docno);
    formData.append('file', uploadFile.value);
    router.post(route('stock.opname.upload'), formData, {
        preserveScroll: true,
        onSuccess: () => {
            uploadFile.value = null;
            if (uploadRef.value) {
                uploadRef.value.value = '';
            }
        },
    });
};
</script>
