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

const formatCurrency = (value) => {
    if (value === null || value === undefined) {
        return '-';
    }
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(Number(value));
};

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
                    size: A4 portrait;
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

                .price-tag {
                    page-break-inside: avoid;
                }
            }

            @media screen {
                .print-container {
                    background: #f5f5f5;
                    padding: 20px;
                }
            }

            .price-tag {
                background: white;
                border: 2px solid #000;
                border-radius: 4px;
                padding: 10px;
                box-sizing: border-box;
                height: 100%;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                position: relative;
            }

            .price-tag-header {
                border-bottom: 1px dashed #999;
                padding-bottom: 6px;
                margin-bottom: 6px;
            }

            .product-code {
                font-family: 'Courier New', monospace;
                font-size: 10px;
                font-weight: bold;
                color: #333;
                letter-spacing: 0.5px;
            }

            .product-name {
                font-family: Arial, sans-serif;
                font-size: 11px;
                font-weight: 600;
                color: #000;
                line-height: 1.3;
                margin-top: 4px;
                max-height: 32px;
                overflow: hidden;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
            }

            .promo-badge {
                background: linear-gradient(135deg, #ff0000, #cc0000);
                color: white;
                font-family: Arial, sans-serif;
                font-size: 9px;
                font-weight: bold;
                padding: 3px 8px;
                border-radius: 3px;
                display: inline-block;
                margin-top: 4px;
                text-transform: uppercase;
                box-shadow: 0 2px 4px rgba(255, 0, 0, 0.3);
            }

            .promo-date {
                font-family: Arial, sans-serif;
                font-size: 8px;
                color: #d00;
                margin-top: 2px;
                font-weight: 600;
            }

            .price-section {
                margin-top: 8px;
                text-align: center;
            }

            .original-price {
                font-family: Arial, sans-serif;
                font-size: 14px;
                color: #999;
                text-decoration: line-through;
                margin-bottom: 4px;
            }

            .final-price-wrapper {
                background: linear-gradient(135deg, #ffeb3b, #ffc107);
                border: 3px solid #f57c00;
                border-radius: 6px;
                padding: 8px 12px;
                box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
                margin: 6px 0;
            }

            .final-price {
                font-family: 'Arial Black', Arial, sans-serif;
                font-size: 24px;
                font-weight: 900;
                color: #000;
                letter-spacing: 0.5px;
                text-shadow: 1px 1px 0px rgba(255, 255, 255, 0.5);
            }

            .price-tag-footer {
                border-top: 1px dashed #999;
                padding-top: 4px;
                margin-top: 6px;
                text-align: center;
            }

            .printed-date {
                font-family: Arial, sans-serif;
                font-size: 7px;
                color: #999;
            }
        </style>

        <div class="no-print text-center py-4 bg-gray-100">
            <button onclick="window.print()" class="bg-green-500 text-white px-4 py-2 rounded">
                Print Price Tag
            </button>
        </div>

        <div class="print-container">
            <div v-for="page in Math.ceil(products.length / (layout.rows * layout.columns))" :key="page" :style="{
                pageBreakAfter: 'always',
                width: '100%',
                minHeight: '100vh',
                display: 'grid',
                gridTemplateRows: `repeat(${layout.rows}, 1fr)`,
                gap: '0.4cm',
                padding: '0.2cm',
                boxSizing: 'border-box'
            }">
                <div v-for="row in layout.rows" :key="row" :style="{
                    display: 'grid',
                    gridTemplateColumns: `repeat(${layout.columns}, 1fr)`,
                    gap: '0.4cm',
                    height: '100%'
                }">
                    <div v-for="col in layout.columns" :key="col" class="price-tag">
                        <template v-if="getProductForPosition(page, row, col)">
                            <div class="price-tag-header">
                                <div class="product-code">
                                    {{ getProductForPosition(page, row, col).product_code }}
                                </div>
                                <div class="product-name">
                                    {{ getProductForPosition(page, row, col).name }}
                                </div>
                                <div v-if="getProductForPosition(page, row, col).has_promotion">
                                    <span class="promo-badge">
                                        🏷️ {{ getProductForPosition(page, row, col).promotion_name }}
                                    </span>
                                    <div class="promo-date">
                                        S/d: {{ getProductForPosition(page, row, col).promotion_end_date }}
                                    </div>
                                </div>
                            </div>

                            <div class="price-section">
                                <div v-if="getProductForPosition(page, row, col).has_promotion" class="original-price">
                                    {{ formatCurrency(getProductForPosition(page, row, col).original_price) }}
                                </div>
                                <div class="final-price-wrapper">
                                    <div class="final-price">
                                        {{ formatCurrency(getProductForPosition(page, row, col).final_price) }}
                                    </div>
                                </div>
                            </div>

                            <div class="price-tag-footer">
                                <div class="printed-date">
                                    Dicetak: {{ getProductForPosition(page, row, col).printed_at }}
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>