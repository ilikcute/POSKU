<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const page = usePage();
const user = page.props.auth.user;

const props = defineProps({
    shifts: {
        type: Object,
        required: true
    },
});

const viewMode = ref('table'); // 'table' or 'cards'

const formatDateTime = (dateTime) => {
    const options = {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    };
    return new Date(dateTime).toLocaleDateString(undefined, options);
};

const formatDateTimeShort = (dateTime) => {
    const options = {
        day: '2-digit',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    };
    return new Date(dateTime).toLocaleDateString('id-ID', options);
};

const formatCurrency = (value) => {
    if (!value) return '-';
    return new Intl.NumberFormat(
        'id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const getStatusBadge = (status) => {
    const badges = {
        'open': 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
        'closed': 'bg-slate-500/20 text-slate-300 border-slate-500/30',
        'completed': 'bg-blue-500/20 text-blue-300 border-blue-500/30'
    };
    return badges[status] || 'bg-gray-500/20 text-gray-300 border-gray-500/30';
};

const getVarianceColor = (variance) => {
    if (!variance) return 'text-gray-400';
    return variance >= 0 ? 'text-emerald-400' : 'text-red-400';
};
</script>

<template>

    <Head title="Data Shift" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                <div>
                    <h2 class="font-semibold text-lg text-[#1f1f1f] leading-tight">
                        Riwayat Shift
                    </h2>
                    <p class="text-xs text-[#555]">Pantau aktivitas shift kasir.</p>
                </div>

                <!-- View Toggle -->
                <div class="flex bg-[#f7f7f7] border border-[#9c9c9c] rounded p-1">
                    <button @click="viewMode = 'table'" :class="[
                        'px-3 py-2 rounded text-xs font-semibold transition-colors',
                        viewMode === 'table'
                            ? 'bg-white text-[#1f1f1f]'
                            : 'text-[#1f1f1f] hover:bg-white'
                    ]">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 10h18M3 6h18m-9 10h9" />
                        </svg>
                        Table
                    </button>
                    <button @click="viewMode = 'cards'" :class="[
                        'px-3 py-2 rounded text-xs font-semibold transition-colors',
                        viewMode === 'cards'
                            ? 'bg-white text-[#1f1f1f]'
                            : 'text-[#1f1f1f] hover:bg-white'
                    ]">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14-7H5a2 2 0 00-2 2v12a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2z" />
                        </svg>
                        Cards
                    </button>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Table View -->
            <div v-if="viewMode === 'table'"
                class="bg-white border border-[#9c9c9c] rounded overflow-hidden">
                <!-- Desktop Table -->
                <div class="hidden lg:block overflow-x-auto">
                    <table class="min-w-full text-sm text-[#1f1f1f]">
                        <thead class="bg-[#efefef] border-b border-[#9c9c9c]">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold">
                                    Station</th>
                                <th
                                    class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold">
                                    Device</th>
                                <th
                                    class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold">
                                    User</th>
                                <th
                                    class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold">
                                    Kasir</th>
                                <th
                                    class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold">
                                    Waktu</th>
                                <th
                                    class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold">
                                    Modal</th>
                                <th
                                    class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold">
                                    Penjualan</th>
                                <th
                                    class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold">
                                    Selisih</th>
                                <th
                                    class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold">
                                    Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#d0d0d0]">
                            <tr v-for="shift in props.shifts.data" :key="shift.id"
                                class="hover:bg-[#f7f7f7] transition-colors group">
                                <td class="px-6 py-4">{{ shift.station?.name ?? '-' }}</td>
                                <td class="px-6 py-4">{{ shift.device_id ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div
                                            class="w-8 h-8 rounded bg-[#dbe7ff] border border-[#9c9c9c] flex items-center justify-center text-xs font-bold mr-3">
                                            {{ shift.user.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <span class="font-medium">{{ shift.user.name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-medium">{{ shift.name }}</td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        <div class="text-xs text-[#555]">Mulai</div>
                                        <div>{{ formatDateTimeShort(shift.start_time) }}</div>
                                        <div v-if="shift.end_time" class="text-xs text-[#555]">Selesai</div>
                                        <div v-if="shift.end_time">{{ formatDateTimeShort(shift.end_time) }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        <div class="text-[#1f1f1f]">{{ formatCurrency(shift.initial_cash) }}</div>
                                        <div v-if="shift.final_cash" class="text-[#1f1f1f]">{{
                                            formatCurrency(shift.final_cash)
                                        }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-medium text-[#1f1f1f]">
                                    {{ shift.total_sales ? formatCurrency(shift.total_sales) : '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="getVarianceColor(shift.variance)" class="font-semibold">
                                        {{ shift.variance ? formatCurrency(shift.variance) : '-' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="getStatusBadge(shift.status)"
                                        class="inline-flex px-3 py-1 rounded text-xs font-medium border">
                                        {{ shift.status }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Compact Table -->
                <div class="lg:hidden overflow-x-auto">
                    <table class="min-w-full text-xs text-[#1f1f1f]">
                        <thead class="bg-[#efefef] border-b border-[#9c9c9c]">
                            <tr>
                                <th class="px-3 py-3 text-left font-semibold">Shift Info</th>
                                <th class="px-3 py-3 text-left font-semibold">Finansial</th>
                                <th class="px-3 py-3 text-left font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#d0d0d0]">
                            <tr v-for="shift in props.shifts.data" :key="shift.id" class="hover:bg-[#f7f7f7]">
                                <td class="px-3 py-3">
                                    <div class="space-y-1">
                                        <div class="font-medium text-[#1f1f1f]">{{ shift.user.name }}</div>
                                        <div class="text-[#555]">{{ shift.name }}</div>
                                        <div class="text-[#555] text-xs">{{ formatDateTimeShort(shift.start_time) }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3">
                                    <div class="space-y-1">
                                        <div class="text-[#1f1f1f] text-xs">{{ formatCurrency(shift.initial_cash) }}
                                        </div>
                                        <div class="text-[#1f1f1f] text-xs">{{ shift.total_sales ?
                                            formatCurrency(shift.total_sales) : '-' }}</div>
                                        <div :class="getVarianceColor(shift.variance)" class="text-xs font-medium">
                                            {{ shift.variance ? formatCurrency(shift.variance) : '-' }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3">
                                    <span :class="getStatusBadge(shift.status)"
                                        class="inline-flex px-2 py-1 rounded text-xs font-medium border">
                                        {{ shift.status }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Card View -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="shift in props.shifts.data" :key="shift.id"
                    class="bg-white border border-[#9c9c9c] rounded p-4 shadow-sm hover:bg-[#f7f7f7] transition-colors">

                    <!-- Header -->
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div
                                class="w-12 h-12 rounded bg-[#dbe7ff] border border-[#9c9c9c] flex items-center justify-center text-lg font-bold mr-4">
                                {{ shift.user.name.charAt(0).toUpperCase() }}
                            </div>
                            <div>
                                <h3 class="font-semibold text-[#1f1f1f]">{{ shift.user.name }}</h3>
                                <p class="text-sm text-[#555]">{{ shift.name }}</p>
                            </div>
                        </div>
                        <span :class="getStatusBadge(shift.status)"
                            class="inline-flex px-3 py-1 rounded text-xs font-medium border">
                            {{ shift.status }}
                        </span>
                    </div>

                    <!-- Time Info -->
                    <div class="mb-4 p-3 bg-[#f7f7f7] border border-[#9c9c9c] rounded">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-[#555]">Mulai:</span>
                            <span class="text-[#1f1f1f]">{{ formatDateTimeShort(shift.start_time) }}</span>
                        </div>
                        <div v-if="shift.end_time" class="flex justify-between items-center text-sm mt-1">
                            <span class="text-[#555]">Selesai:</span>
                            <span class="text-[#1f1f1f]">{{ formatDateTimeShort(shift.end_time) }}</span>
                        </div>
                    </div>

                    <!-- Financial Info -->
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-[#555]">Modal Awal</span>
                            <span class="text-[#1f1f1f] font-medium">{{ formatCurrency(shift.initial_cash) }}</span>
                        </div>
                        <div v-if="shift.final_cash" class="flex justify-between items-center">
                            <span class="text-sm text-[#555]">Modal Akhir</span>
                            <span class="text-[#1f1f1f] font-medium">{{ formatCurrency(shift.final_cash) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-[#555]">Total Penjualan</span>
                            <span class="text-[#1f1f1f] font-medium">{{ shift.total_sales ?
                                formatCurrency(shift.total_sales) :
                                '-' }}</span>
                        </div>
                        <hr class="border-[#9c9c9c]">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-[#555]">Selisih</span>
                            <span :class="getVarianceColor(shift.variance)" class="font-bold text-lg">
                                {{ shift.variance ? formatCurrency(shift.variance) : '-' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <Pagination :links="props.shifts.links" />

            <!-- Empty State -->
            <div v-if="!props.shifts.data.length"
                class="text-center py-12 bg-[#f7f7f7] border border-[#9c9c9c] rounded">
                <svg class="mx-auto h-12 w-12 text-[#555] mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="text-lg font-medium text-[#1f1f1f] mb-2">Belum ada data shift</h3>
                <p class="text-[#555]">Data shift akan muncul di sini setelah ada aktivitas kasir.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
