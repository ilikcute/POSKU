<script setup>
import { ref, onMounted, computed } from 'vue';
import QRCode from 'qrcode';

const props = defineProps({
    customer: Object,
    store: Object,
    show: Boolean,
});

const emit = defineEmits(['close']);

const qrCodeDataUrl = ref('');

// Print configuration
const printConfig = ref({
    paperSize: 'A4', // A4 or F4
    cardsPerRow: 2,
    cardsPerColumn: 5,
    printer: '',
    orientation: 'portrait' // portrait or landscape
});

const availablePrinters = ref([]);
const showPrintDialog = ref(false);

// Available paper sizes
const paperSizes = [
    { value: 'A4', label: 'A4 (210 x 297 mm)', width: 210, height: 297 },
    { value: 'F4', label: 'F4 (210 x 330 mm)', width: 210, height: 330 }
];

// Grid options
const gridOptions = [
    { rows: 1, cols: 1, label: '1x1 (1 kartu)' },
    { rows: 2, cols: 1, label: '2x1 (2 kartu)' },
    { rows: 1, cols: 2, label: '1x2 (2 kartu)' },
    { rows: 2, cols: 2, label: '2x2 (4 kartu)' },
    { rows: 3, cols: 2, label: '3x2 (6 kartu)' },
    { rows: 4, cols: 2, label: '4x2 (8 kartu)' },
    { rows: 5, cols: 2, label: '5x2 (10 kartu)' },
    { rows: 3, cols: 3, label: '3x3 (9 kartu)' },
];

// Computed properties
const selectedPaperSize = computed(() => {
    return paperSizes.find(p => p.value === printConfig.value.paperSize);
});

const totalCards = computed(() => {
    return printConfig.value.cardsPerRow * printConfig.value.cardsPerColumn;
});

const cardArray = computed(() => {
    return Array(totalCards.value).fill(props.customer);
});

const generateQRCode = async () => {
    try {
        const dataUrl = await QRCode.toDataURL(props.customer.member_code, {
            width: 80,
            margin: 1,
            color: {
                dark: '#000000',
                light: '#FFFFFF'
            }
        });
        qrCodeDataUrl.value = dataUrl;
    } catch (error) {
        console.error('Error generating QR code:', error);
    }
};

const getAvailablePrinters = async () => {
    try {
        // Check if browser supports printer enumeration
        if ('navigator' in window && 'mediaDevices' in navigator) {
            // For modern browsers, we can only get basic printer info
            availablePrinters.value = [
                { id: 'default', name: 'Default Printer' },
                { id: 'system-dialog', name: 'Pilih dari Dialog Sistem' }
            ];
        }
    } catch (error) {
        console.log('Printer enumeration not supported');
        availablePrinters.value = [
            { id: 'default', name: 'Default Printer' },
            { id: 'system-dialog', name: 'Pilih dari Dialog Sistem' }
        ];
    }
};

const openPrintDialog = () => {
    showPrintDialog.value = true;
};

const printCard = () => {
    // Set CSS variables for print layout
    document.documentElement.style.setProperty('--cards-per-row', printConfig.value.cardsPerRow);
    document.documentElement.style.setProperty('--cards-per-column', printConfig.value.cardsPerColumn);
    document.documentElement.style.setProperty('--paper-width', `${selectedPaperSize.value.width}mm`);
    document.documentElement.style.setProperty('--paper-height', `${selectedPaperSize.value.height}mm`);
    
    // Close dialog
    showPrintDialog.value = false;
    
    // Small delay to ensure CSS variables are applied
    setTimeout(() => {
        window.print();
    }, 100);
};

const setGridOption = (option) => {
    printConfig.value.cardsPerRow = option.cols;
    printConfig.value.cardsPerColumn = option.rows;
};

onMounted(() => {
    if (props.customer?.member_code) {
        generateQRCode();
    }
    getAvailablePrinters();
});
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75" @click="emit('close')"></div>
            </div>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Preview Kartu Customer</h3>
                        <div class="flex space-x-2">
                            <button
                                @click="openPrintDialog"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                                </svg>
                                Cetak
                            </button>
                            <button
                                @click="emit('close')"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Tutup
                            </button>
                        </div>
                    </div>

                    <!-- Print Configuration Dialog -->
                    <div v-if="showPrintDialog" class="fixed inset-0 z-60 overflow-y-auto">
                        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
                            <div class="fixed inset-0 bg-black opacity-50"></div>
                            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:align-middle sm:max-w-lg sm:w-full">
                                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                    <h4 class="text-lg font-medium text-gray-900 mb-4">Pengaturan Print</h4>
                                    
                                    <!-- Paper Size Selection -->
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Ukuran Kertas</label>
                                        <select 
                                            v-model="printConfig.paperSize"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        >
                                            <option v-for="paper in paperSizes" :key="paper.value" :value="paper.value">
                                                {{ paper.label }}
                                            </option>
                                        </select>
                                    </div>

                                    <!-- Grid Layout Selection -->
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Layout Grid</label>
                                        <div class="grid grid-cols-2 gap-2">
                                            <button
                                                v-for="option in gridOptions"
                                                :key="`${option.rows}x${option.cols}`"
                                                @click="setGridOption(option)"
                                                :class="[
                                                    'px-3 py-2 text-sm border rounded-md',
                                                    printConfig.cardsPerRow === option.cols && printConfig.cardsPerColumn === option.rows
                                                        ? 'bg-indigo-600 text-white border-indigo-600'
                                                        : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                                                ]"
                                            >
                                                {{ option.label }}
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Custom Grid -->
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Custom Grid</label>
                                        <div class="flex space-x-2">
                                            <div class="flex-1">
                                                <label class="block text-xs text-gray-500">Kolom</label>
                                                <input
                                                    v-model.number="printConfig.cardsPerRow"
                                                    type="number"
                                                    min="1"
                                                    max="5"
                                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                />
                                            </div>
                                            <div class="flex-1">
                                                <label class="block text-xs text-gray-500">Baris</label>
                                                <input
                                                    v-model.number="printConfig.cardsPerColumn"
                                                    type="number"
                                                    min="1"
                                                    max="10"
                                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                />
                                            </div>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">Total: {{ totalCards }} kartu</p>
                                    </div>

                                    <!-- Printer Selection -->
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Printer</label>
                                        <select 
                                            v-model="printConfig.printer"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        >
                                            <option v-for="printer in availablePrinters" :key="printer.id" :value="printer.id">
                                                {{ printer.name }}
                                            </option>
                                        </select>
                                    </div>

                                    <!-- Orientation -->
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Orientasi</label>
                                        <div class="flex space-x-4">
                                            <label class="flex items-center">
                                                <input
                                                    v-model="printConfig.orientation"
                                                    type="radio"
                                                    value="portrait"
                                                    class="form-radio h-4 w-4 text-indigo-600"
                                                />
                                                <span class="ml-2 text-sm text-gray-700">Portrait</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input
                                                    v-model="printConfig.orientation"
                                                    type="radio"
                                                    value="landscape"
                                                    class="form-radio h-4 w-4 text-indigo-600"
                                                />
                                                <span class="ml-2 text-sm text-gray-700">Landscape</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                    <button
                                        @click="printCard"
                                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm"
                                    >
                                        Cetak Sekarang
                                    </button>
                                    <button
                                        @click="showPrintDialog = false"
                                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                                    >
                                        Batal
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Grid -->
                    <div class="mb-4 p-4 bg-gray-100 rounded-lg">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Preview Layout</h4>
                        <div 
                            class="grid gap-2 mx-auto"
                            :style="{
                                'grid-template-columns': `repeat(${printConfig.cardsPerRow}, 1fr)`,
                                'max-width': '400px'
                            }"
                        >
                            <div
                                v-for="(card, index) in cardArray"
                                :key="index"
                                class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded aspect-[1.6/1] flex items-center justify-center text-white text-xs"
                            >
                                Card {{ index + 1 }}
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2 text-center">
                            {{ printConfig.cardsPerRow }} x {{ printConfig.cardsPerColumn }} = {{ totalCards }} kartu
                        </p>
                    </div>

                    <!-- Kartu Customer untuk Print -->
                    <div id="customer-cards" class="customer-cards-container">
                        <div class="print-page">
                            <div 
                                class="cards-grid"
                                :style="{
                                    'grid-template-columns': `repeat(${printConfig.cardsPerRow}, 1fr)`,
                                    'grid-template-rows': `repeat(${printConfig.cardsPerColumn}, 1fr)`
                                }"
                            >
                                <div 
                                    v-for="(card, index) in cardArray" 
                                    :key="index"
                                    class="card-container"
                                >
                                    <!-- Customer Card -->
                                    <div class="customer-card front-card">
                                        <!-- Background Pattern -->
                                        <div class="card-background"></div>
                                        
                                        <!-- Header -->
                                        <div class="card-header">
                                            <div class="store-logo">
                                                <div class="logo-circle">
                                                    <span class="logo-text">{{ store?.name?.charAt(0) || 'S' }}</span>
                                                </div>
                                            </div>
                                            <div class="store-info">
                                                <h2 class="store-name">{{ store?.name || 'POSKU' }}</h2>
                                                <p class="card-title">KARTU CUSTOMER</p>
                                            </div>
                                        </div>

                                        <!-- Customer Info -->
                                        <div class="customer-info">
                                            <div class="customer-photo">
                                                <div v-if="customer.photo" class="photo-container">
                                                    <img :src="customer.photo" :alt="customer.name" class="customer-image" />
                                                </div>
                                                <div v-else class="photo-placeholder">
                                                    <svg class="placeholder-icon" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                                    </svg>
                                                </div>
                                            </div>
                                            
                                            <div class="customer-details">
                                                <div class="customer-name">{{ customer.name }}</div>
                                                <div class="customer-code-label">Kode Member:</div>
                                                <div class="customer-code">{{ customer.member_code }}</div>
                                                <div class="customer-phone" v-if="customer.phone">{{ customer.phone }}</div>
                                            </div>
                                        </div>

                                        <!-- QR Code -->
                                        <div class="qr-section">
                                            <div class="qr-code">
                                                <img v-if="qrCodeDataUrl" :src="qrCodeDataUrl" alt="QR Code" />
                                            </div>
                                            <div class="qr-label">Scan untuk verifikasi</div>
                                        </div>

                                        <!-- Footer -->
                                        <div class="card-footer">
                                            <div class="footer-text">Valid sampai tidak terbatas</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Print Styles */
@media print {
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body * {
        visibility: hidden;
    }
    
    #customer-cards,
    #customer-cards * {
        visibility: visible;
    }
    
    #customer-cards {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: 100vh;
        margin: 0;
        padding: 0;
        background: white !important;
        print-color-adjust: exact;
        -webkit-print-color-adjust: exact;
        color-adjust: exact;
    }
    
    .print-page {
        width: var(--paper-width, 210mm);
        height: var(--paper-height, 297mm);
        padding: 10mm;
        page-break-after: always;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .cards-grid {
        display: grid;
        gap: 5mm;
        width: 100%;
        height: 100%;
    }
    
    .card-container {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    @page {
        size: var(--paper-width, 210mm) var(--paper-height, 297mm);
        margin: 0;
    }
}

.customer-cards-container {
    display: none;
}

@media print {
    .customer-cards-container {
        display: block;
    }
}

.customer-card {
    width: 85.6mm;
    height: 53.98mm;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 8px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    color: white;
    font-family: 'Arial', sans-serif;
}

.card-background {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.05)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.05)"/><circle cx="50" cy="50" r="0.5" fill="rgba(255,255,255,0.03)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    opacity: 0.6;
}

.card-header {
    display: flex;
    align-items: center;
    padding: 4mm 3mm 2mm 3mm;
    position: relative;
    z-index: 2;
}

.store-logo {
    margin-right: 2mm;
}

.logo-circle {
    width: 8mm;
    height: 8mm;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.logo-text {
    font-size: 3mm;
    font-weight: bold;
    color: white;
}

.store-info {
    flex: 1;
}

.store-name {
    font-size: 3mm;
    font-weight: bold;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.card-title {
    font-size: 2mm;
    margin: 0;
    opacity: 0.9;
    font-weight: 500;
}

.customer-info {
    display: flex;
    padding: 1mm 3mm 2mm 3mm;
    position: relative;
    z-index: 2;
}

.customer-photo {
    width: 12mm;
    height: 12mm;
    margin-right: 2mm;
    flex-shrink: 0;
}

.photo-container, .photo-placeholder {
    width: 100%;
    height: 100%;
    border-radius: 4px;
    overflow: hidden;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
}

.customer-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.placeholder-icon {
    width: 60%;
    height: 60%;
    color: rgba(255, 255, 255, 0.6);
}

.customer-details {
    flex: 1;
    min-width: 0;
}

.customer-name {
    font-size: 3mm;
    font-weight: bold;
    margin-bottom: 1mm;
    line-height: 1.2;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.customer-code-label {
    font-size: 1.5mm;
    opacity: 0.8;
    margin-bottom: 0.5mm;
}

.customer-code {
    font-size: 2.5mm;
    font-weight: bold;
    font-family: 'Courier New', monospace;
    background: rgba(255, 255, 255, 0.1);
    padding: 0.5mm 1mm;
    border-radius: 2px;
    display: inline-block;
    margin-bottom: 1mm;
    letter-spacing: 0.5px;
}

.customer-phone {
    font-size: 1.8mm;
    opacity: 0.9;
}

.qr-section {
    position: absolute;
    bottom: 2mm;
    right: 3mm;
    display: flex;
    flex-direction: column;
    align-items: center;
    z-index: 2;
}

.qr-code {
    width: 10mm;
    height: 10mm;
    background: white;
    border-radius: 2px;
    padding: 1mm;
    margin-bottom: 1mm;
}

.qr-code img {
    width: 100%;
    height: 100%;
}

.qr-label {
    font-size: 1.2mm;
    opacity: 0.8;
    text-align: center;
    line-height: 1.2;
    max-width: 12mm;
}

.card-footer {
    position: absolute;
    bottom: 1.5mm;
    left: 3mm;
    z-index: 2;
}

.footer-text {
    font-size: 1.5mm;
    opacity: 0.7;
}

/* Decorative elements */
.customer-card::before {
    content: '';
    position: absolute;
    top: -5mm;
    right: -5mm;
    width: 20mm;
    height: 20mm;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 50%;
    z-index: 1;
}

.customer-card::after {
    content: '';
    position: absolute;
    bottom: -10mm;
    left: -10mm;
    width: 30mm;
    height: 30mm;
    background: rgba(255, 255, 255, 0.03);
    border-radius: 50%;
    z-index: 1;
}
</style>