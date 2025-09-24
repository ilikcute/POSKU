<template>

    <AuthenticatedLayout>

        <Head title="Master Produk" />
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">
                Master Produk
            </h2>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl shadow-lg overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-4 border-b border-gray-600/60">
                        <h3 class="text-lg font-medium text-white">Daftar Produk</h3>
                        <button @click="openModal(false)"
                            class="inline-block bg-gradient-to-r from-green-400 to-emerald-400 text-white font-bold py-3 px-10 rounded-full text-sm md:text-base shadow-xl hover:scale-105 hover:from-green-500 hover:to-emerald-500 transition-transform duration-200">
                            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            Tambah Produk
                        </button>
                    </div>
                    <div class="flex items-center justify-between px-4 py-4 border-b border-gray-600/60 gap-4">
                        <TextInput v-model="search" type="text" placeholder="Cari kode atau nama..."
                            class="w-full max-w-xs bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500" />
                        <div class="flex gap-2">
                            <Link :href="route('master.products.import.form')">
                            <button
                                class="inline-block bg-gradient-to-r from-blue-400 to-sky-400 text-white font-bold py-3 px-10 rounded-full text-sm md:text-base shadow-xl hover:scale-105 hover:from-blue-500 hover:to-sky-500 transition-transform duration-200">
                                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M4 12l4 4m0 0l4-4m-4 4V4" />
                                </svg>
                                Impor
                            </button>
                            </Link>
                            <Link :href="route('master.products.export')">
                            <button
                                class="inline-block bg-gradient-to-r from-purple-400 to-pink-400 text-white font-bold py-3 px-10 rounded-full text-sm md:text-base shadow-xl hover:scale-105 hover:from-purple-500 hover:to-pink-500 transition-transform duration-200">
                                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v2a2 2 0 002 2h12a2 2 0 002-2V4M4 12l4-4m0 0l4 4m-4-4v12" />
                                </svg>
                                Eksport
                            </button>
                            </Link>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm text-gray-200 divide-y divide-gray-600">
                            <thead class="bg-gray-800/70">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Kode</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Nama
                                        Produk</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Kategori
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Divisi
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Unit</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Harga
                                        Beli</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Harga
                                        Jual</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Stok</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                <tr v-for="product in products.data" :key="product.id"
                                    class="hover:bg-gray-700/50 transition-colors duration-150">
                                    <td class="px-4 py-3 whitespace-nowrap">{{ product.product_code }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">{{ product.name }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">{{ product.category?.name ?? '-' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">{{ product.division?.name ?? '-' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">{{ product.unit }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">{{ formatCurrency(product.purchase_price) }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">{{ formatCurrency(product.selling_price) }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">{{ product.stock }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end space-x-2">
                                            <button @click="openModal(true, product)"
                                                class="inline-block bg-gradient-to-r from-blue-400 to-blue-500 text-white font-bold py-2 px-4 rounded-full text-xs shadow-lg hover:scale-105 hover:from-blue-500 hover:to-blue-600 transition-transform duration-200">
                                                <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </button>
                                            <button @click="deleteProduct(product.id)"
                                                class="inline-block bg-gradient-to-r from-red-400 to-red-500 text-white font-bold py-2 px-4 rounded-full text-xs shadow-lg hover:scale-105 hover:from-red-500 hover:to-red-600 transition-transform duration-200">
                                                <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="products.data.length === 0">
                                    <td colspan="9" class="px-6 py-12 text-center text-gray-400">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-12 h-12 text-gray-500 mb-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4">
                                                </path>
                                            </svg>
                                            <p class="text-lg font-medium text-gray-300">Belum ada data produk</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Pagination :links="products.links" />
                </div>
            </div>
        </div>


        <Modal :show="isModalOpen" @close="closeModal">
            <div
                class="p-8 bg-gray-900/70 backdrop-blur-md border border-white/10 rounded-2xl shadow-lg max-w-4xl mx-auto space-y-6 text-white">
                <h2 class="text-lg font-semibold">
                    {{ isEditMode ? "Edit Produk" : "Tambah Produk Baru" }}
                </h2>
                <form @submit.prevent="saveProduct" class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">

                    <div class="md:col-span-1">
                        <InputLabel for="product_code" value="Kode Produk" class="text-gray-300" />
                        <TextInput id="product_code" v-model="form.product_code"
                            class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500"
                            required />
                        <InputError :message="form.errors.product_code" class="mt-2 text-red-400" />
                    </div>
                    <div class="md:col-span-1">
                        <InputLabel for="barcode" value="Barcode" class="text-gray-300" />
                        <TextInput id="barcode" v-model="form.barcode"
                            class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500" />
                        <InputError :message="form.errors.barcode" class="mt-2 text-red-400" />
                    </div>
                    <div class="md:col-span-1">
                        <InputLabel for="name" value="Nama Produk" class="text-gray-300" />
                        <TextInput id="name" v-model="form.name"
                            class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500"
                            required />
                        <InputError :message="form.errors.name" class="mt-2 text-red-400" />
                    </div>
                    <div class="md:col-span-3">
                        <InputLabel for="description" value="Deskripsi" class="text-gray-300" />
                        <TextInput id="description" v-model="form.description"
                            class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500" />
                        <InputError :message="form.errors.description" class="mt-2 text-red-400" />
                    </div>

                    <div>
                        <InputLabel for="category_id" value="Kategori" class="text-gray-300" />
                        <select id="category_id" v-model="form.category_id"
                            class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Pilih Kategori</option>
                            <option v-for="cat in props.categories" :key="cat.id" :value="cat.id">{{ cat.name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.category_id" class="mt-2 text-red-400" />
                    </div>
                    <div>
                        <InputLabel for="division_id" value="Divisi" class="text-gray-300" />
                        <select id="division_id" v-model="form.division_id"
                            class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Pilih Divisi</option>
                            <option v-for="div in props.divisions" :key="div.id" :value="div.id">{{ div.name }}</option>
                        </select>
                        <InputError :message="form.errors.division_id" class="mt-2 text-red-400" />
                    </div>
                    <div>
                        <InputLabel for="rack_id" value="Rak" class="text-gray-300" />
                        <select id="rack_id" v-model="form.rack_id"
                            class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Pilih Rak</option>
                            <option v-for="rack in props.racks" :key="rack.id" :value="rack.id">{{ rack.name }}</option>
                        </select>
                        <InputError :message="form.errors.rack_id" class="mt-2 text-red-400" />
                    </div>
                    <div>
                        <InputLabel for="supplier_id" value="Supplier" class="text-gray-300" />
                        <select id="supplier_id" v-model="form.supplier_id"
                            class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Pilih Supplier</option>
                            <option v-for="sup in props.suppliers" :key="sup.id" :value="sup.id">{{ sup.name }}</option>
                        </select>
                        <InputError :message="form.errors.supplier_id" class="mt-2 text-red-400" />
                    </div>
                    <div>
                        <InputLabel for="unit" value="Satuan" class="text-gray-300" />
                        <select id="unit" v-model="form.unit"
                            class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500">
                            <option value="Pcs">Pcs</option>
                            <option value="Box">Box</option>
                            <option value="Kg">Kg</option>
                            <option value="Liter">Liter</option>
                            <option value="Pack">Pack</option>
                        </select>
                        <InputError :message="form.errors.unit" class="mt-2 text-red-400" />
                    </div>
                    <div>
                        <InputLabel for="purchase_price" value="Harga Beli" class="text-gray-300" />
                        <TextInput id="purchase_price" type="number" v-model="form.purchase_price"
                            class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500"
                            required />
                        <InputError :message="form.errors.purchase_price" class="mt-2 text-red-400" />
                    </div>
                    <div>
                        <InputLabel for="selling_price" value="Harga Jual" class="text-gray-300" />
                        <TextInput id="selling_price" type="number" v-model="form.selling_price"
                            class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500"
                            required />
                        <InputError :message="form.errors.selling_price" class="mt-2 text-red-400" />
                    </div>
                    <div>
                        <InputLabel for="member_price" value="Harga Member" class="text-gray-300" />
                        <TextInput id="member_price" type="number" v-model="form.member_price"
                            class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500" />
                        <InputError :message="form.errors.member_price" class="mt-2 text-red-400" />
                    </div>
                    <div>
                        <InputLabel for="vip_price" value="Harga VIP" class="text-gray-300" />
                        <TextInput id="vip_price" type="number" v-model="form.vip_price"
                            class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500" />
                        <InputError :message="form.errors.vip_price" class="mt-2 text-red-400" />
                    </div>
                    <div>
                        <InputLabel for="wholesale_price" value="Harga Grosir" class="text-gray-300" />
                        <TextInput id="wholesale_price" type="number" v-model="form.wholesale_price"
                            class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500" />
                        <InputError :message="form.errors.wholesale_price" class="mt-2 text-red-400" />
                    </div>
                    <div>
                        <InputLabel for="min_wholesale_qty" value="Min Qty Grosir" class="text-gray-300" />
                        <TextInput id="min_wholesale_qty" type="number" v-model="form.min_wholesale_qty"
                            class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500" />
                        <InputError :message="form.errors.min_wholesale_qty" class="mt-2 text-red-400" />
                    </div>
                    <div>
                        <InputLabel for="stock" value="Stok" class="text-gray-300" />
                        <TextInput id="stock" type="number" v-model="form.stock"
                            class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500" />
                        <InputError :message="form.errors.stock" class="mt-2 text-red-400" />
                    </div>
                    <div>
                        <InputLabel for="min_stock_alert" value="Min Stok Alert" class="text-gray-300" />
                        <TextInput id="min_stock_alert" type="number" v-model="form.min_stock_alert"
                            class="mt-1 block w-full bg-white/5 border-white/20 rounded-lg text-gray-200 focus:ring-blue-500 focus:border-blue-500" />
                        <InputError :message="form.errors.min_stock_alert" class="mt-2 text-red-400" />
                    </div>
                    <div class="md:col-span-3 flex justify-end gap-3 mt-4">
                        <button type="button" @click="closeModal"
                            class="inline-block bg-gradient-to-r from-gray-500 to-gray-600 text-white font-bold py-2 px-6 rounded-full text-sm shadow-lg hover:scale-105 hover:from-gray-600 hover:to-gray-700 transition-transform duration-200">
                            <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Batal
                        </button>
                        <button type="submit" :disabled="form.processing"
                            class="inline-block bg-gradient-to-r from-green-400 to-emerald-400 text-white font-bold py-2 px-6 rounded-full text-sm shadow-lg hover:scale-105 hover:from-green-500 hover:to-emerald-500 transition-transform duration-200"
                            :class="{ 'opacity-25': form.processing }">
                            <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import Modal from "@/Components/Modal.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import Pagination from "@/Components/Pagination.vue";
import { debounce } from "lodash";


const props = defineProps({
    products: Object,
    filters: Object,
    categories: Array,
    divisions: Array,
    suppliers: Array,
    racks: Array,
});

const isModalOpen = ref(false);
const isEditMode = ref(false);

const form = useForm({
    id: null,
    product_code: "",
    barcode: "",
    name: "",
    description: "",
    purchase_price: 0,
    selling_price: 0,
    member_price: 0,
    vip_price: 0,
    wholesale_price: 0,
    min_wholesale_qty: 0,
    category_id: null,
    division_id: null,
    rack_id: null,
    supplier_id: null,
    unit: "Pcs",
    min_stock_alert: 0,
    stock: 0,
});

const search = ref(props.filters.search);
watch(
    search,
    debounce((newValue) => {
        router.get(
            route("master.products.index"),
            { search: newValue },
            {
                preserveState: true,
                replace: true,
            }
        );
    }, 300)
);

const openModal = (editMode = false, product = null) => {
    isEditMode.value = editMode;
    if (editMode && product) {
        form.id = product.id;
        form.product_code = product.product_code;
        form.barcode = product.barcode;
        form.name = product.name;
        form.description = product.description;
        form.purchase_price = product.purchase_price;
        form.selling_price = product.selling_price;
        form.member_price = product.member_price;
        form.vip_price = product.vip_price;
        form.wholesale_price = product.wholesale_price;
        form.min_wholesale_qty = product.min_wholesale_qty;
        form.category_id = product.category_id;
        form.division_id = product.division_id;
        form.rack_id = product.rack_id;
        form.supplier_id = product.supplier_id;
        form.unit = product.unit;
        form.min_stock_alert = product.min_stock_alert;
        form.stock = product.stock;
    } else {
        form.reset();
        form.unit = "Pcs";
    }
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const saveProduct = () => {
    if (isEditMode.value) {
        form.patch(route("master.products.update", form.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route("master.products.store"), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteProduct = (productId) => {
    if (confirm("Apakah Anda yakin ingin menghapus produk ini?")) {
        useForm({}).delete(route("master.products.destroy", productId));
    }
};

const formatCurrency = (value) => {
    if (value === null || value === undefined) return '-';
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(value);
};
</script>