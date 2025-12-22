<template>
    <AuthenticatedLayout>
        <Head title="Retur Pembelian Baru" />

        <template #header>
            <div class="flex flex-col gap-2">
                <h2 class="font-semibold text-lg text-[#1f1f1f] leading-tight">
                    Retur Pembelian
                </h2>
                <p class="text-xs text-[#555]">Pilih invoice pembelian dan tentukan item retur.</p>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-sm p-6 space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <div class="lg:col-span-2">
                        <label class="block text-xs font-semibold text-[#555] mb-2">Pilih Pembelian</label>
                        <select v-model="selectedPurchaseId"
                            class="w-full border border-[#9c9c9c] bg-white rounded text-[#1f1f1f] px-3 py-2 text-xs">
                            <option value="">Pilih invoice...</option>
                            <option v-for="item in purchaseOptions" :key="item.id" :value="item.id">
                                {{ item.invoice_number }} - {{ formatDate(item.transaction_date) }}
                            </option>
                        </select>
                        <p v-if="formError" class="text-xs text-red-600 mt-2">{{ formError }}</p>
                    </div>
                    <div class="bg-white border border-[#9c9c9c] rounded p-3 text-xs">
                        <div class="text-[#555] mb-1">Invoice</div>
                        <div class="font-semibold">{{ selectedPurchase?.invoice_number || "-" }}</div>
                        <div class="text-[#555] mt-3 mb-1">Supplier</div>
                        <div class="font-semibold">{{ selectedPurchase?.supplier?.name || "-" }}</div>
                        <div class="text-[#555] mt-3 mb-1">User</div>
                        <div class="font-semibold">{{ selectedPurchase?.user?.name || "-" }}</div>
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
                                    <th class="px-4 py-2 text-right">Qty Beli</th>
                                    <th class="px-4 py-2 text-right">Sudah Retur</th>
                                    <th class="px-4 py-2 text-right">Sisa Retur</th>
                                    <th class="px-4 py-2 text-right">Qty Retur</th>
                                    <th class="px-4 py-2 text-right">Harga</th>
                                    <th class="px-4 py-2 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#d0d0d0]">
                                <tr v-if="!returnableItems.length && !loadingReturnables">
                                    <td colspan="7" class="px-4 py-6 text-center text-[#555]">
                                        Pilih invoice untuk melihat item yang bisa diretur.
                                    </td>
                                </tr>
                                <tr v-if="loadingReturnables">
                                    <td colspan="7" class="px-4 py-6 text-center text-[#555]">
                                        Memuat data retur...
                                    </td>
                                </tr>
                                <tr v-for="item in returnableItems" :key="item.product_id">
                                    <td class="px-4 py-3">
                                        <div class="font-semibold">{{ item.product?.name || "-" }}</div>
                                        <div class="text-[11px] text-[#555]">{{ item.product?.product_code || "-" }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-right">{{ item.original_quantity }}</td>
                                    <td class="px-4 py-3 text-right">{{ item.returned_quantity }}</td>
                                    <td class="px-4 py-3 text-right">{{ item.returnable_quantity }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <input type="number" min="0" :max="item.returnable_quantity"
                                            v-model.number="item.quantity"
                                            @input="enforceReturnLimit(item)"
                                            class="w-20 border border-[#9c9c9c] bg-white rounded text-right px-2 py-1">
                                    </td>
                                    <td class="px-4 py-3 text-right">{{ formatCurrency(item.price_at_purchase) }}</td>
                                    <td class="px-4 py-3 text-right font-semibold">
                                        {{ formatCurrency(item.quantity * item.price_at_purchase) }}
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
                    <Link :href="route('purchase-returns.index')"
                        class="px-4 py-2 rounded border border-[#9c9c9c] bg-[#e9e9e9] text-[#1f1f1f] text-xs font-semibold hover:bg-white">
                    Cancel
                    </Link>
                    <button type="button" @click="submitReturn" :disabled="form.processing"
                        class="px-4 py-2 rounded border border-[#9c9c9c] bg-[#e9e9e9] text-[#1f1f1f] text-xs font-semibold hover:bg-white disabled:opacity-50">
                        Simpan Retur
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { computed, onMounted, ref, watch } from "vue";

const props = defineProps({
    purchase: Object,
    purchases: Array,
    shift_id: [Number, String],
    station_id: [Number, String],
});

const form = useForm({
    purchase_id: "",
    items: [],
    notes: "",
});

const selectedPurchaseId = ref(props.purchase?.id ? String(props.purchase.id) : "");
const returnableItems = ref([]);
const loadingReturnables = ref(false);
const formError = ref("");

const purchaseOptions = computed(() => {
    const list = props.purchases ? [...props.purchases] : [];
    if (props.purchase && !list.find((item) => item.id === props.purchase.id)) {
        list.unshift(props.purchase);
    }
    return list;
});

const selectedPurchase = computed(() =>
    purchaseOptions.value.find((item) => String(item.id) === String(selectedPurchaseId.value)) || null
);

const totalAmount = computed(() =>
    returnableItems.value.reduce(
        (sum, item) => sum + (item.quantity || 0) * (item.price_at_purchase || 0),
        0
    )
);

const fetchReturnables = async (purchaseId) => {
    if (!purchaseId) {
        returnableItems.value = [];
        return;
    }
    loadingReturnables.value = true;
    try {
        const response = await fetch(route("purchases.returnable-items", purchaseId));
        const data = await response.json();
        returnableItems.value = (data || []).map((item) => ({
            ...item,
            quantity: 0,
        }));
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
    formError.value = "";
    if (!selectedPurchaseId.value) {
        formError.value = "Pilih invoice terlebih dahulu.";
        return;
    }
    const items = returnableItems.value
        .filter((item) => Number(item.quantity) > 0)
        .map((item) => ({
            product_id: item.product_id,
            quantity: Number(item.quantity),
            price: item.price_at_purchase,
        }));

    if (!items.length) {
        formError.value = "Minimal pilih satu item untuk diretur.";
        return;
    }

    form.purchase_id = selectedPurchaseId.value;
    form.items = items;
    form.post(route("purchase-returns.store"));
};

watch(selectedPurchaseId, (value) => {
    formError.value = "";
    fetchReturnables(value);
});

onMounted(() => {
    if (selectedPurchaseId.value) {
        fetchReturnables(selectedPurchaseId.value);
    }
});

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
