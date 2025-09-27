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

<style>
@media print {
    @page {
        size: A4 landscape;
        margin: 0.5in;
    }

    body {
        margin: 0;
        padding: 0;
        font-family: 'Tahoma', sans-serif;
    }

    .print-container {
        width: 100%;
        height: 100vh;
        background: white;
        page-break-inside: avoid;
    }

    .price-tag-item {
        page-break-inside: avoid;
        box-sizing: border-box;
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

<template>
    <div class="print-container">
        <div class="no-print text-center py-4 bg-gray-100">
            <button onclick="window.print()" class="bg-green-500 text-white px-4 py-2 rounded">
                Print Price Tag
            </button>
        </div>

        <div class="print-container" :style="{
            display: 'grid',
            'grid-template-columns': `repeat(${layout.columns}, 1fr)`,
            'grid-template-rows': `repeat(${layout.rows}, 1fr)`,
            gap: '2px'
        }">
            <div v-for="(position, index) in (layout.rows * layout.columns)" :key="index" class="price-tag-item" :style="{
                display: 'flex',
                'align-items': 'center',
                'justify-content': 'space-between',
                padding: '8px',
                border: '1px solid #000',
                'background': 'white',
                'box-sizing': 'border-box'
            }">
                <template
                    v-if="getProductForPosition(1, Math.floor(index / layout.columns) + 1, (index % layout.columns) + 1)">
                    <div class="product-info" style="flex: 1; text-align: left;">
                        <div class="product-code"
                            style="font-family: 'Tahoma', sans-serif; font-size: 10px; font-weight: bold; margin-bottom: 2px;">
                            {{ getProductForPosition(1, Math.floor(index / layout.columns) + 1, (index % layout.columns)
                            + 1).product_code }}
                        </div>
                        <div class="product-name"
                            style="font-family: 'Tahoma', sans-serif; font-size: 9px; line-height: 1.1; margin-bottom: 4px; max-height: 20px; overflow: hidden;">
                            {{ getProductForPosition(1, Math.floor(index / layout.columns) + 1, (index % layout.columns)
                            + 1).name }}
                        </div>
                        <div v-if="getProductForPosition(1, Math.floor(index / layout.columns) + 1, (index % layout.columns) + 1).has_promotion"
                            class="promotion"
                            style="font-family: 'Tahoma', sans-serif; font-size: 8px; color: red; margin-bottom: 1px;">
                            PROMO: {{ getProductForPosition(1, Math.floor(index / layout.columns) + 1, (index %
                                layout.columns) + 1).promotion_name }}
                        </div>
                        <div v-if="getProductForPosition(1, Math.floor(index / layout.columns) + 1, (index % layout.columns) + 1).has_promotion"
                            class="promotion-end"
                            style="font-family: 'Tahoma', sans-serif; font-size: 8px; color: red;">
                            Sampai: {{ getProductForPosition(1, Math.floor(index / layout.columns) + 1, (index %
                                layout.columns) + 1).promotion_end_date }}
                        </div>
                    </div>
                    <div class="price-section" style="text-align: right; flex-shrink: 0;">
                        <div v-if="getProductForPosition(1, Math.floor(index / layout.columns) + 1, (index % layout.columns) + 1).has_promotion"
                            class="original-price"
                            style="font-family: 'Tahoma', sans-serif; font-size: 10px; text-decoration: line-through; color: gray; margin-bottom: 2px;">
                            {{ formatCurrency(getProductForPosition(1, Math.floor(index / layout.columns) + 1, (index %
                                layout.columns) + 1).original_price) }}
                        </div>
                        <div class="final-price"
                            style="font-family: 'Tahoma', sans-serif; font-size: 16px; font-weight: bold; color: blue;">
                            {{ formatCurrency(getProductForPosition(1, Math.floor(index / layout.columns) + 1, (index %
                                layout.columns) + 1).final_price) }}
                        </div>
                    </div>
                    <div class="printed-at"
                        style="font-family: 'Tahoma', sans-serif; font-size: 8px; color: gray; margin-top: 2px; text-align: center; width: 100%;">
                        {{ getProductForPosition(1, Math.floor(index / layout.columns) + 1, (index % layout.columns) +
                        1).printed_at }}
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>
