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
});

const search = ref(props.filters.search);
watch(
    search,
    debounce((newValue) => {
        console.log("1. Watch terpicu dengan nilai:", newValue); // Untuk mengecek apakah watch berjalan

        router.get(
            route("master.products.index"),
            { search: newValue },
            {
                preserveState: true,
                replace: true,
                onStart: () => {
                    console.log("2. Memulai request Inertia..."); // Untuk mengecek apakah request dimulai
                },
                onFinish: () => {
                    console.log("3. Request Inertia selesai."); // Untuk mengecek apakah request selesai
                },
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
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(value);
};
</script>

<template>
    <Head title="Master Produk" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Master Produk
            </h2>
        </template>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <button
                        @click="openModal(false)"
                        class="inline-flex items-center px-4 py-2 border border-indigo-300 text-xs font-medium rounded text-indigo-600 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Produk
                    </button>
                    <div class="flex items-center space-x-2">
                        <TextInput
                            v-model="search"
                            type="text"
                            placeholder="Cari kode atau nama..."
                            class="w-80 px-4 py-2 border border-gray-300 rounded text-xs font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        />

                        <Link :href="route('master.products.import.form')">
                            <button
                                class="inline-flex items-center px-4 py-2 border border-blue-300 text-xs font-medium rounded text-blue-600 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M4 12l4 4m0 0l4-4m-4 4V4" />
                                </svg>
                                Impor
                            </button>
                        </Link>
                        <a
                            :href="route('master.products.export')"
                            class="inline-flex items-center px-4 py-2 border border-green-300 text-xs font-medium rounded text-green-600 bg-white hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v2a2 2 0 002 2h12a2 2 0 002-2V4M4 12l4-4m0 0l4 4m-4-4v12" />
                            </svg>
                            Ekspor
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wide">Kode</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wide">Barcode</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wide">Nama Produk</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wide">Kategori</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wide">Divisi</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wide">Rak</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wide">Supplier</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wide">Unit</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wide">Harga Beli</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wide">Harga Jual</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wide">Harga Member</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wide">Harga VIP</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wide">Harga Grosir</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wide">Min Qty Grosir</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wide">Stok</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wide">Min Stok Alert</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wide">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr
                                v-for="product in products.data"
                                :key="product.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="px-4 py-2 whitespace-nowrap">{{ product.product_code }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ product.barcode }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ product.name }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ product.category?.name ?? '-' }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ product.division?.name ?? '-' }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ product.rack?.name ?? '-' }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ product.supplier?.name ?? '-' }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ product.unit }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ formatCurrency(product.purchase_price) }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ formatCurrency(product.selling_price) }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ formatCurrency(product.member_price) }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ formatCurrency(product.vip_price) }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ formatCurrency(product.wholesale_price) }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ product.min_wholesale_qty }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ product.stock }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ product.min_stock_alert }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-2">
                                        <!-- Tombol Edit -->
                                        <button 
                                            @click="openModal(true, product)" 
                                            class="inline-flex items-center px-2 py-1 border border-gray-300 text-xs font-medium rounded text-indigo-600 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                        >
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                            Edit
                                        </button>
                                        <!-- Tombol Hapus -->
                                        <button 
                                            @click="deleteProduct(product.id)" 
                                            class="inline-flex items-center px-2 py-1 border border-red-300 text-xs font-medium rounded text-red-600 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                        >
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="products.data.length === 0">
                                <td
                                    colspan="5"
                                    class="px-6 py-4 text-center text-gray-500"
                                >
                                    Tidak ada data.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <Pagination :links="products.links" />
            </div>
        </div>

        <Modal :show="isModalOpen" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    {{ isEditMode ? "Edit Produk" : "Tambah Produk Baru" }}
                </h2>
                <form @submit.prevent="saveProduct" class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="product_code" value="Kode Produk" />
                        <TextInput id="product_code" v-model="form.product_code" class="mt-1 block w-full" required />
                        <InputError :message="form.errors.product_code" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="barcode" value="Barcode" />
                        <TextInput id="barcode" v-model="form.barcode" class="mt-1 block w-full" />
                        <InputError :message="form.errors.barcode" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="name" value="Nama Produk" />
                        <TextInput id="name" v-model="form.name" class="mt-1 block w-full" required />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="description" value="Deskripsi" />
                        <TextInput id="description" v-model="form.description" class="mt-1 block w-full" />
                        <InputError :message="form.errors.description" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="category_id" value="Kategori" />
                        <select id="category_id" v-model="form.category_id" class="mt-1 block w-full border-gray-300 rounded">
                            <option value="">Pilih Kategori</option>
                            <option v-for="cat in props.categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                        <InputError :message="form.errors.category_id" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="division_id" value="Divisi" />
                        <select id="division_id" v-model="form.division_id" class="mt-1 block w-full border-gray-300 rounded">
                            <option value="">Pilih Divisi</option>
                            <option v-for="div in props.divisions" :key="div.id" :value="div.id">{{ div.name }}</option>
                        </select>
                        <InputError :message="form.errors.division_id" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="rack_id" value="Rak" />
                        <select id="rack_id" v-model="form.rack_id" class="mt-1 block w-full border-gray-300 rounded">
                            <option value="">Pilih Rak</option>
                            <option v-for="rack in props.racks" :key="rack.id" :value="rack.id">{{ rack.name }}</option>
                        </select>
                        <InputError :message="form.errors.rack_id" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="supplier_id" value="Supplier" />
                        <select id="supplier_id" v-model="form.supplier_id" class="mt-1 block w-full border-gray-300 rounded">
                            <option value="">Pilih Supplier</option>
                            <option v-for="sup in props.suppliers" :key="sup.id" :value="sup.id">{{ sup.name }}</option>
                        </select>
                        <InputError :message="form.errors.supplier_id" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="unit" value="Satuan" />
                        <select id="unit" v-model="form.unit" class="mt-1 block w-full border-gray-300 rounded">
                            <option value="Pcs">Pcs</option>
                            <option value="Box">Box</option>
                            <option value="Kg">Kg</option>
                            <option value="Liter">Liter</option>
                            <option value="Pack">Pack</option>
                        </select>
                        <InputError :message="form.errors.unit" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="purchase_price" value="Harga Beli" />
                        <TextInput id="purchase_price" type="number" v-model="form.purchase_price" class="mt-1 block w-full" required />
                        <InputError :message="form.errors.purchase_price" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="selling_price" value="Harga Jual" />
                        <TextInput id="selling_price" type="number" v-model="form.selling_price" class="mt-1 block w-full" required />
                        <InputError :message="form.errors.selling_price" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="member_price" value="Harga Member" />
                        <TextInput id="member_price" type="number" v-model="form.member_price" class="mt-1 block w-full" />
                        <InputError :message="form.errors.member_price" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="vip_price" value="Harga VIP" />
                        <TextInput id="vip_price" type="number" v-model="form.vip_price" class="mt-1 block w-full" />
                        <InputError :message="form.errors.vip_price" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="wholesale_price" value="Harga Grosir" />
                        <TextInput id="wholesale_price" type="number" v-model="form.wholesale_price" class="mt-1 block w-full" />
                        <InputError :message="form.errors.wholesale_price" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="min_wholesale_qty" value="Min Qty Grosir" />
                        <TextInput id="min_wholesale_qty" type="number" v-model="form.min_wholesale_qty" class="mt-1 block w-full" />
                        <InputError :message="form.errors.min_wholesale_qty" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="stock" value="Stok" />
                        <TextInput id="stock" type="number" v-model="form.stock" class="mt-1 block w-full" />
                        <InputError :message="form.errors.stock" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="min_stock_alert" value="Min Stok Alert" />
                        <TextInput id="min_stock_alert" type="number" v-model="form.min_stock_alert" class="mt-1 block w-full" />
                        <InputError :message="form.errors.min_stock_alert" class="mt-2" />
                    </div>
                    <div class="md:col-span-2 flex justify-end gap-2 mt-4">
                        <button type="button" @click="closeModal"
                            class="inline-flex items-center px-2 py-1 border border-gray-300 text-xs font-medium rounded text-indigo-600 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Batal
                        </button>
                        <button type="submit" :disabled="form.processing"
                            class="ms-3 inline-flex items-center px-2 py-1 border border-indigo-300 text-xs font-medium rounded text-indigo-600 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
