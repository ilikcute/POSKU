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

onMounted(() => {
    setTimeout(() => {
        window.print();
    }, 500);
});

const getProductForPosition = (page, row, col) => {
    const index = (page - 1) * (props.layout.rows * props.layout.columns) + ((row - 1) * props.layout.columns) + (col - 1);
    return props.products[index] || null;
};
</script>

<template>
    <div>
        <style>
            @media print {
                @page {
                    size: A4;
                    margin: 0.5cm;
                }

                body {
                    margin: 0;
                    padding: 0;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

                .print-container {
                    display: block !important;
                }

                .no-print {
                    display: none !important;
                }

                .barcode-label {
                    page-break-inside: avoid;
                }
            }

            @media screen {
                .print-container {
                    background: #f5f5f5;
                    padding: 20px;
                }
            }

            .barcode-label {
                background: white;
                border: 1px dashed #999;
                border-radius: 3px;
                padding: 8px;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                text-align: center;
                box-sizing: border-box;
                height: 100%;
            }

            .barcode-image-wrapper {
                margin-bottom: 4px;
                width: 100%;
                display: flex;
                justify-content: center;
                align-items: center;
            }

            .barcode-image-wrapper img {
                max-width: 95%;
                height: auto;
                max-height: 40px;
            }

            .barcode-code {
                font-family: 'Courier New', monospace;
                font-size: 11px;
                font-weight: bold;
                letter-spacing: 1px;
                margin-bottom: 2px;
                color: #000;
            }

            .barcode-name {
                font-family: Arial, sans-serif;
                font-size: 8px;
                line-height: 1.2;
                color: #333;
                max-height: 24px;
                overflow: hidden;
                text-overflow: ellipsis;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                width: 100%;
            }
        </style>

        <div class="no-print text-center py-4 bg-gray-100">
            <button onclick="window.print()" class="bg-blue-500 text-white px-4 py-2 rounded">
                Print Barcode
            </button>
        </div>

        <div class="print-container">
            <div v-for="page in Math.ceil(products.length / (layout.rows * layout.columns))" :key="page" :style="{
                pageBreakAfter: 'always',
                width: '100%',
                minHeight: '100vh',
                display: 'grid',
                gridTemplateRows: `repeat(${layout.rows}, 1fr)`,
                gap: '0.3cm',
                padding: '0.2cm',
                boxSizing: 'border-box'
            }">
                <div v-for="row in layout.rows" :key="row" :style="{
                    display: 'grid',
                    gridTemplateColumns: `repeat(${layout.columns}, 1fr)`,
                    gap: '0.3cm',
                    height: '100%'
                }">
                    <div v-for="col in layout.columns" :key="col" class="barcode-label">
                        <template v-if="getProductForPosition(page, row, col)">
                            <div class="barcode-image-wrapper">
                                <img v-if="getProductForPosition(page, row, col).barcode_image"
                                    :src="getProductForPosition(page, row, col).barcode_image"
                                    :alt="`Barcode ${getProductForPosition(page, row, col).product_code}`" />
                            </div>
                            <div class="barcode-code">
                                {{ getProductForPosition(page, row, col).product_code }}
                            </div>
                            <div class="barcode-name">
                                {{ getProductForPosition(page, row, col).name }}
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>