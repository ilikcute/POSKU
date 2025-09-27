<script setup>
import { onMounted } from 'vue';

const props = defineProps({
    products: {
        type: Array,
        required: true,
    },
    layout: {
        type: Object,
        required: true,
    },
});

// Auto print when component mounts
onMounted(() => {
    setTimeout(() => {
        window.print();
    }, 500);
});
</script>

<template>
    <div class="print-container">
        <style>
            @media print {
                @page {
                    size: A4;
                    margin: 0.5cm;
                }

                body {
                    margin: 0;
                    padding: 0;
                }

                .print-container {
                    display: block !important;
                }

                .no-print {
                    display: none !important;
                }
            }

            @media screen {
                .print-container {
                    display: none;
                }
            }
        </style>

        <div class="no-print text-center py-4 bg-gray-100">
            <button onclick="window.print()" class="bg-blue-500 text-white px-4 py-2 rounded">
                Print Barcode
            </button>
        </div>

        <div class="print-container">
            <div v-for="page in Math.ceil(products.length / (layout.rows * layout.columns))" :key="page"
                class="page-break"
                style="page-break-after: always; width: 100%; height: 100vh; display: grid; grid-template-rows: repeat(auto-fit, minmax(0, 1fr)); gap: 0.5cm; padding: 0.5cm; box-sizing: border-box;">
                <div v-for="row in layout.rows" :key="row"
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(0, 1fr)); gap: 0.5cm; height: 100%;">
                    <div v-for="col in layout.columns" :key="col" class="barcode-item"
                        style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 0.5cm; border: 1px solid #ccc; border-radius: 4px; background: white; min-height: 2cm;">
                        <template v-if="getProductForPosition(page, row, col)">
                            <div style="text-align: center; margin-bottom: 0.2cm;">
                                <img v-if="getProductForPosition(page, row, col).barcode_image"
                                    :src="getProductForPosition(page, row, col).barcode_image"
                                    :alt="`Barcode ${getProductForPosition(page, row, col).product_code}`"
                                    style="max-width: 100%; height: 1.5cm; object-fit: contain;" />
                            </div>
                            <div
                                style="font-size: 8px; font-family: monospace; text-align: center; margin-bottom: 0.1cm;">
                                {{ getProductForPosition(page, row, col).product_code }}
                            </div>
                            <div
                                style="font-size: 6px; text-align: center; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%;">
                                {{ getProductForPosition(page, row, col).name }}
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    methods: {
        getProductForPosition(page, row, col) {
            const index = (page - 1) * (this.layout.rows * this.layout.columns) + ((row - 1) * this.layout.columns) + (col - 1);
            return this.products[index] || null;
        }
    }
}
</script>
