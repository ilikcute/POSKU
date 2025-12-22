<template>
    <AuthenticatedLayout>
        <Head title="Edit Retur Penjualan" />

        <template #header>
            <div class="flex flex-col gap-2">
                <h2 class="font-semibold text-lg text-[#1f1f1f] leading-tight">
                    Edit Retur Penjualan
                </h2>
                <p class="text-xs text-[#555]">Sesuaikan item retur sesuai kebutuhan.</p>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-sm p-6 space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <div class="lg:col-span-2">
                        <div class="text-xs text-[#555] mb-2">Invoice</div>
                        <div class="bg-white border border-[#9c9c9c] rounded px-3 py-2 text-xs">
                            {{ salesReturn.sale?.invoice_number || "-" }}
                        </div>
                        <div class="text-xs text-[#555] mt-4 mb-2">Pelanggan</div>
                        <div class="bg-white border border-[#9c9c9c] rounded px-3 py-2 text-xs">
                            {{ salesReturn.sale?.member?.name || "Umum" }}
                        </div>
                    </div>
                    <div class="bg-white border border-[#9c9c9c] rounded p-3 text-xs">
                        <div class="text-[#555] mb-1">Tanggal Retur</div>
                        <div class="font-semibold">{{ formatDate(salesReturn.return_date) }}</div>
                        <div class="text-[#555] mt-3 mb-1">Kasir</div>
                        <div class="font-semibold">{{ salesReturn.user?.name || "-" }}</div>
                    </div>
                </div>

                <div class="border border-[#9c9c9c] rounded overflow-hidden bg-white">
                    <div class="px-4 py-3 border-b border-[#9c9c9c] bg-[#efefef] text-xs font-semibold">
                        Item Retur
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-xs text-[#1f1f1f]">
                            <thead class="bg-[#f7f7f7] border-b border-[#9c9c9c]">
                                <tr>
                                    <th class="px-4 py-2 text-left">Produk</th>
                                    <th class="px-4 py-2 text-right">Sisa Retur</th>
                                    <th class="px-4 py-2 text-right">Qty Retur</th>
                                    <th class="px-4 py-2 text-right">Harga</th>
                                    <th class="px-4 py-2 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#d0d0d0]">
                                <tr v-if="!returnableItems.length && !loadingReturnables">
                                    <td colspan="5" class="px-4 py-6 text-center text-[#555]">
                                        Tidak ada item retur.
                                    </td>
                                </tr>
                                <tr v-if="loadingReturnables">
                                    <td colspan="5" class="px-4 py-6 text-center text-[#555]">
                                        Memuat data retur...
                                    </td>
                                </tr>
                                <tr v-for="item in returnableItems" :key="item.product_id">
                                    <td class="px-4 py-3">
                                        <div class="font-semibold">{{ item.product?.name || "-" }}</div>
                                        <div class="text-[11px] text-[#555]">{{ item.product?.product_code || "-" }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-right">{{ item.returnable_quantity }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <input type="number" min="0" :max="item.returnable_quantity"
                                            v-model.number="item.quantity"
                                            @input="enforceReturnLimit(item)"
                                            class="w-20 border border-[#9c9c9c] bg-white rounded text-right px-2 py-1">
                                    </td>
                                    <td class="px-4 py-3 text-right">{{ formatCurrency(item.price_at_sale) }}</td>
                                    <td class="px-4 py-3 text-right font-semibold">
                                        {{ formatCurrency(item.quantity * item.price_at_sale) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <div class="lg:col-span-2">
                        <label class="block text-xs font-semibold text-[#555] mb-2">Catatan</label>
                        <textarea v-model="form.notes" rows="2"
                            class="w-full border border-[#9c9c9c] bg-white rounded text-[#1f1f1f] px-3 py-2 text-xs"
                            placeholder="Catatan retur"></textarea>
                    </div>
                    <div class="bg-white border border-[#9c9c9c] rounded p-4">
                        <div class="flex justify-between text-xs text-[#555]">
                            <span>Total Retur</span>
                            <span class="font-semibold text-[#1f1f1f]">{{ formatCurrency(totalAmount) }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap justify-end gap-3">
                    <Link :href="route('sales-returns.show', salesReturn.id)"
                        class="px-4 py-2 rounded border border-[#9c9c9c] bg-[#e9e9e9] text-[#1f1f1f] text-xs font-semibold hover:bg-white">
                    Cancel
                    </Link>
                    <button type="button" @click="submitReturn" :disabled="form.processing"
                        class="px-4 py-2 rounded border border-[#9c9c9c] bg-[#e9e9e9] text-[#1f1f1f] text-xs font-semibold hover:bg-white disabled:opacity-50">
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { computed, onMounted, ref } from "vue";

const props = defineProps({
    salesReturn: Object,
});

const form = useForm({
    items: [],
    notes: props.salesReturn?.notes || "",
});

const returnableItems = ref([]);
const loadingReturnables = ref(false);

const existingDetails = computed(() =>
    props.salesReturn?.sales_return_details ||
    props.salesReturn?.salesReturnDetails ||
    []
);

const totalAmount = computed(() =>
    returnableItems.value.reduce(
        (sum, item) => sum + (item.quantity || 0) * (item.price_at_sale || 0),
        0
    )
);

const fetchReturnables = async () => {
    const saleId = props.salesReturn?.sale?.id;
    if (!saleId) return;

    loadingReturnables.value = true;
    try {
        const response = await fetch(route("sales.returnable-items", saleId));
        const data = await response.json();
        const map = new Map();
        (data || []).forEach((item) => {
            map.set(item.product_id, {
                ...item,
                quantity: 0,
            });
        });

        existingDetails.value.forEach((detail) => {
            const key = detail.product_id;
            const existing = map.get(key);
            const quantity = Number(detail.quantity || 0);
            if (existing) {
                existing.returnable_quantity += quantity;
                existing.quantity = quantity;
                existing.price_at_sale = existing.price_at_sale || detail.price_at_return || 0;
            } else {
                map.set(key, {
                    product_id: key,
                    product: detail.product || detail.product_id,
                    original_quantity: quantity,
                    returned_quantity: 0,
                    returnable_quantity: quantity,
                    price_at_sale: detail.price_at_return || detail.price_at_sale || 0,
                    quantity: quantity,
                });
            }
        });

        returnableItems.value = Array.from(map.values());
    } catch (error) {
        returnableItems.value = [];
    } finally {
        loadingReturnables.value = false;
    }
};

const enforceReturnLimit = (item) => {
    const maxQty = Number(item.returnable_quantity || 0);
    if (item.quantity < 0) item.quantity = 0;
    if (item.quantity > maxQty) item.quantity = maxQty;
};

const submitReturn = () => {
    const items = returnableItems.value
        .filter((item) => Number(item.quantity) > 0)
        .map((item) => ({
            product_id: item.product_id,
            quantity: Number(item.quantity),
            price: item.price_at_sale,
        }));

    if (!items.length) {
        return;
    }

    form.items = items;
    form.patch(route("sales-returns.update", props.salesReturn.id));
};

onMounted(fetchReturnables);

const formatCurrency = (value) =>
    new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(value || 0);

const formatDate = (value) => {
    if (!value) return "-";
    return new Date(value).toLocaleDateString("id-ID");
};
</script>
