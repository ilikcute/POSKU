<template>
    <AuthenticatedLayout>
        <Head title="Retur Pembelian" />

        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-lg text-[#1f1f1f] leading-tight">
                        Retur Pembelian
                    </h2>
                    <p class="text-xs text-[#555] mt-1">
                        Kelola transaksi retur pembelian.
                    </p>
                </div>
                <Link :href="route('purchase-returns.create')"
                    class="inline-flex items-center justify-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] font-semibold py-3 px-6 rounded text-xs hover:bg-white transition-colors">
                Tambah Retur
                </Link>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-sm p-4 mb-6">
                <form @submit.prevent="applyFilters">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#555] mb-1">Start Date</label>
                            <input type="date" v-model="filters.start_date"
                                class="w-full border border-[#9c9c9c] bg-white rounded px-3 py-2 text-xs text-[#1f1f1f]">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#555] mb-1">End Date</label>
                            <input type="date" v-model="filters.end_date"
                                class="w-full border border-[#9c9c9c] bg-white rounded px-3 py-2 text-xs text-[#1f1f1f]">
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit"
                                class="bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] px-4 py-2 rounded text-xs font-semibold hover:bg-white">
                                Filter
                            </button>
                            <button type="button" @click="resetFilters"
                                class="bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] px-4 py-2 rounded text-xs font-semibold hover:bg-white">
                                Reset
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs text-[#1f1f1f]">
                        <thead class="bg-[#efefef] border-b border-[#9c9c9c]">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold">
                                    Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold">
                                    Invoice</th>
                                <th class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold">
                                    Supplier</th>
                                <th class="px-6 py-3 text-right text-xs uppercase tracking-wider font-semibold">
                                    Total</th>
                                <th class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#d0d0d0]">
                            <tr v-for="item in props.returns.data" :key="item.id" class="hover:bg-white">
                                <td class="px-6 py-4 text-xs">
                                    {{ formatDate(item.return_date) }}
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    {{ item.purchase?.invoice_number || "-" }}
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    {{ item.purchase?.supplier?.name || "-" }}
                                </td>
                                <td class="px-6 py-4 text-xs text-right font-semibold">
                                    {{ formatCurrency(item.final_amount) }}
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    <div class="flex flex-wrap gap-2">
                                        <Link :href="route('purchase-returns.show', item.id)"
                                            class="inline-flex items-center px-3 py-2 rounded bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] hover:bg-white">
                                        Lihat
                                        </Link>
                                        <Link :href="route('purchase-returns.edit', item.id)"
                                            class="inline-flex items-center px-3 py-2 rounded bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] hover:bg-white">
                                        Edit
                                        </Link>
                                        <button type="button" @click="deleteReturn(item.id)"
                                            class="inline-flex items-center px-3 py-2 rounded bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] hover:bg-white">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!props.returns.data.length">
                                <td colspan="5" class="px-6 py-6 text-center text-[#555]">
                                    Belum ada data retur pembelian.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <Pagination v-if="props.returns.links" :links="props.returns.links" />
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref } from "vue";
import Pagination from "@/Components/Pagination.vue";

const props = defineProps({
    returns: Object,
    filters: Object,
});

const filters = ref({
    start_date: props.filters?.start_date || "",
    end_date: props.filters?.end_date || "",
});

const applyFilters = () => {
    router.get(route("purchase-returns.index"), filters.value, {
        preserveState: true,
        replace: true,
    });
};

const resetFilters = () => {
    filters.value = { start_date: "", end_date: "" };
    applyFilters();
};

const deleteReturn = (id) => {
    if (confirm("Yakin ingin menghapus retur ini?")) {
        router.delete(route("purchase-returns.destroy", id));
    }
};

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
