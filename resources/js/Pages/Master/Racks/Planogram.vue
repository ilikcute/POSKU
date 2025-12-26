<template>
    <AuthenticatedLayout>
        <Head title="Planogram Rak" />

        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-lg text-[#1f1f1f] leading-tight">
                        Planogram Rak
                    </h2>
                    <p class="text-xs text-[#555] mt-1">
                        Susun rak dan isi shelf berdasarkan produk untuk panduan penataan.
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Link :href="route('master.racks.planogram.report')"
                        class="inline-flex items-center justify-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] font-semibold py-3 px-6 rounded text-xs hover:bg-white transition-colors">
                        Laporan Planogram
                    </Link>
                    <Link :href="route('master.racks.index')"
                        class="inline-flex items-center justify-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] font-semibold py-3 px-6 rounded text-xs hover:bg-white transition-colors">
                        Kembali ke Master Rak
                    </Link>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-[260px_1fr] gap-6">
                <aside class="bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-sm p-4">
                    <div class="text-xs font-semibold text-[#555] uppercase mb-3">
                        Pilih Rak
                    </div>
                    <div class="space-y-2 max-h-[520px] overflow-y-auto pr-1">
                        <button v-for="rack in racks" :key="rack.id" type="button"
                            @click="selectRack(rack)"
                            class="w-full text-left px-3 py-2 rounded border text-xs font-semibold transition-colors"
                            :class="selectedRack?.id === rack.id ? 'bg-white border-[#1f1f1f] text-[#1f1f1f]' : 'bg-[#e9e9e9] border-[#9c9c9c] text-[#555] hover:bg-white'">
                            <div class="font-semibold text-[#1f1f1f]">{{ rack.rack_code || '-' }} - {{ rack.name }}</div>
                            <div class="text-[11px] text-[#777]">
                                {{ rack.rack_type || '-' }} | Shelf {{ rack.shelf_count || 0 }}
                            </div>
                        </button>
                    </div>
                </aside>

                <section class="bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-sm p-6">
                    <div v-if="!selectedRack" class="text-center text-[#555] py-10">
                        Pilih rak di sebelah kiri untuk melihat planogram.
                    </div>

                    <div v-else>
                        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                            <div>
                                <h3 class="text-sm font-semibold text-[#1f1f1f]">
                                    {{ selectedRack.rack_code || '-' }} - {{ selectedRack.name }}
                                </h3>
                                <p class="text-[11px] text-[#555]">
                                    {{ selectedRack.rack_type || '-' }} | Shelf {{ selectedRack.shelf_count || 0 }}
                                </p>
                            </div>
                            <button @click="savePlanogram"
                                class="inline-flex items-center justify-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] font-semibold py-2 px-5 rounded text-xs hover:bg-white transition-colors">
                                Simpan Planogram
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div v-for="shelf in shelves" :key="shelf.id"
                                class="border border-[#9c9c9c] bg-white rounded p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="text-xs font-semibold text-[#1f1f1f]">
                                        Shelf {{ shelf.shelf_no }}
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <select v-model="shelf.selectedProductId"
                                            class="bg-white border border-[#9c9c9c] rounded px-2 py-1 text-xs text-[#1f1f1f]">
                                            <option value="">Pilih produk</option>
                                            <option v-for="product in products" :key="product.id" :value="product.id">
                                                {{ product.product_code }} - {{ product.name }}
                                            </option>
                                        </select>
                                        <button type="button" @click="addProductToShelf(shelf)"
                                            class="px-3 py-1 bg-[#e9e9e9] border border-[#9c9c9c] text-xs font-semibold rounded hover:bg-white">
                                            Tambah
                                        </button>
                                    </div>
                                </div>

                                <div v-if="shelf.product_ids.length" class="space-y-2">
                                    <div v-for="productId in shelf.product_ids" :key="productId"
                                        class="flex items-center justify-between text-xs border border-[#d0d0d0] rounded px-3 py-2 bg-[#f7f7f7]">
                                        <div class="text-[#1f1f1f]">
                                            {{ productLabel(productId) }}
                                        </div>
                                        <button type="button" @click="removeProductFromShelf(shelf, productId)"
                                            class="text-red-700 hover:text-red-800">
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                                <div v-else class="text-[11px] text-[#777]">
                                    Belum ada produk di shelf ini.
                                </div>
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
import { Head, Link, router } from "@inertiajs/vue3";
import { computed, ref } from "vue";

const props = defineProps({
    racks: Array,
    products: Array,
});

const selectedRack = ref(null);
const shelves = ref([]);

const productMap = computed(() => {
    const map = {};
    (props.products || []).forEach((product) => {
        map[product.id] = product;
    });
    return map;
});

const selectRack = (rack) => {
    selectedRack.value = rack;
    const sourceShelves = Array.isArray(rack.shelves) ? rack.shelves : [];
    const next = sourceShelves
        .slice()
        .sort((a, b) => Number(a.shelf_no || 0) - Number(b.shelf_no || 0))
        .map((shelf) => ({
            id: shelf.id,
            shelf_no: shelf.shelf_no,
            product_ids: (shelf.products || []).map((product) => product.id),
            selectedProductId: "",
        }));
    shelves.value = next;
};

const productLabel = (productId) => {
    const product = productMap.value[productId];
    if (!product) return `#${productId}`;
    return product.product_code ? `${product.product_code} - ${product.name}` : product.name;
};

const addProductToShelf = (shelf) => {
    if (!shelf.selectedProductId) return;
    const id = Number(shelf.selectedProductId);
    if (!shelf.product_ids.includes(id)) {
        shelf.product_ids.push(id);
    }
    shelf.selectedProductId = "";
};

const removeProductFromShelf = (shelf, productId) => {
    shelf.product_ids = shelf.product_ids.filter((id) => id !== productId);
};

const savePlanogram = () => {
    if (!selectedRack.value) return;
    const payload = shelves.value.map((shelf) => ({
        shelf_id: shelf.id,
        product_ids: shelf.product_ids,
    }));
    router.patch(route("master.racks.planogram.update", selectedRack.value.id), {
        shelf_plan: payload,
    }, {
        preserveScroll: true,
    });
};
</script>
