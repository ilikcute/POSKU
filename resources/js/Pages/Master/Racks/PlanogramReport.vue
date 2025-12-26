<template>
    <AuthenticatedLayout>
        <Head title="Laporan Planogram" />

        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-lg text-[#1f1f1f] leading-tight">
                        Laporan Planogram
                    </h2>
                    <p class="text-xs text-[#555] mt-1">
                        Ilustrasi susunan rak dan produk untuk panduan penataan.
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button @click="printReport" :disabled="!selectedRackId"
                        class="inline-flex items-center justify-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] font-semibold py-3 px-6 rounded text-xs hover:bg-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        Cetak
                    </button>
                    <Link :href="route('master.racks.planogram')"
                        class="inline-flex items-center justify-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] font-semibold py-3 px-6 rounded text-xs hover:bg-white transition-colors">
                        Kembali
                    </Link>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div v-if="!racks.length" class="bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-sm p-8 text-center text-[#555]">
                Belum ada data planogram.
            </div>

            <div v-else class="space-y-6">
                <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-sm p-4">
                    <label class="text-xs font-semibold text-[#555] uppercase block mb-2">Pilih Rak</label>
                    <select v-model="selectedRackId"
                        class="w-full bg-white border border-[#9c9c9c] rounded px-3 py-2 text-sm text-[#1f1f1f]">
                        <option value="">Pilih rak untuk dicetak</option>
                        <option v-for="rack in racks" :key="rack.id" :value="String(rack.id)">
                            {{ rack.rack_code || '-' }} - {{ rack.name }}
                        </option>
                    </select>
                    <div class="text-[11px] text-[#777] mt-2">
                        Cetak hanya untuk rak yang dipilih.
                    </div>
                </div>

                <div v-if="!filteredRacks.length" class="bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-sm p-6 text-center text-[#555]">
                    Pilih rak untuk menampilkan planogram.
                </div>

                <section v-for="rack in filteredRacks" :key="rack.id"
                    class="bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-sm p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                        <div>
                            <h3 class="text-sm font-semibold text-[#1f1f1f]">
                                {{ rack.rack_code || '-' }} - {{ rack.name }}
                            </h3>
                            <p class="text-[11px] text-[#555]">
                                {{ rack.rack_type || '-' }} | Shelf {{ rack.shelf_count || rack.shelves?.length || 0 }}
                            </p>
                        </div>
                        <div class="text-[11px] text-[#555]">
                            Total Shelf: {{ rack.shelves?.length || 0 }}
                        </div>
                    </div>

                    <div class="grid gap-3">
                        <div v-for="shelf in sortedShelves(rack.shelves)" :key="shelf.id"
                            class="border border-[#9c9c9c] bg-white rounded p-4">
                            <div class="text-[11px] font-semibold text-[#1f1f1f] mb-3">
                                Shelf {{ shelf.shelf_no }}
                            </div>

                            <div v-if="shelf.products?.length" class="flex gap-3 overflow-x-auto pb-2">
                                <div v-for="product in shelf.products" :key="product.id"
                                    class="border border-[#d0d0d0] rounded p-2 bg-[#f7f7f7] flex flex-col items-center text-center min-w-[110px]">
                                    <div class="w-16 h-16 bg-white border border-[#cfcfcf] rounded flex items-center justify-center overflow-hidden">
                                        <img v-if="product.image" :src="imageUrl(product.image)" :alt="product.name"
                                            class="w-full h-full object-contain" />
                                        <div v-else class="text-[10px] text-[#777]">
                                            {{ productInitials(product.name) }}
                                        </div>
                                    </div>
                                    <div class="mt-2 text-[11px] font-semibold text-[#1f1f1f]">
                                        {{ product.product_code || '-' }}
                                    </div>
                                    <div class="text-[10px] text-[#555] leading-tight">
                                        {{ product.name }}
                                    </div>
                                </div>
                            </div>

                            <div v-else class="text-[11px] text-[#777]">
                                Tidak ada produk di shelf ini.
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { computed, ref } from "vue";

const props = defineProps({
    racks: Array,
});

const selectedRackId = ref("");

const filteredRacks = computed(() => {
    if (!selectedRackId.value) {
        return [];
    }
    return props.racks.filter((rack) => String(rack.id) === String(selectedRackId.value));
});

const sortedShelves = (shelves = []) => {
    return [...shelves].sort((a, b) => Number(a.shelf_no || 0) - Number(b.shelf_no || 0));
};

const imageUrl = (path) => {
    if (!path) return null;
    if (path.startsWith('http')) return path;
    return `/storage/${path}`;
};

const productInitials = (name = '') => {
    return name
        .split(' ')
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('');
};

const printReport = () => {
    if (!selectedRackId.value) {
        return;
    }
    window.print();
};
</script>

<style scoped>
@media print {
    button,
    a {
        display: none !important;
    }
}
</style>
