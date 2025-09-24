<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const user = page.props.auth.user;

const props = defineProps({
    shifts: {
        type: Object,
        required: true
    },
});

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

const formatCurrency = (value) => {
    return new Intl.NumberFormat(
        'id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 2,
    }).format(value);
};
</script>

<template>

    <Head title="Data Shift" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">
                Shift History
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Container untuk efek blur dan border -->
                <div class="backdrop-blur-md bg-white/10 border border-white/10 rounded-2xl shadow-lg overflow-hidden">
                    <!-- Table wrapper agar bisa di-scroll horizontal pada layar kecil -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm text-gray-200 divide-y divide-gray-600">
                            <!-- Header -->
                            <thead class="bg-gray-800/70">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wider">User</th>
                                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wider">Kasir</th>
                                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wider">Mulai</th>
                                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wider">Selesai</th>
                                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wider">Modal Awal</th>
                                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wider">Modal Akhir</th>
                                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wider">Total Jual</th>
                                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wider">Selisih</th>
                                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <!-- Isi tabel -->
                            <tbody class="divide-y divide-gray-700">
                                <tr v-for="shift in props.shifts.data" :key="shift.id"
                                    class="hover:bg-gray-700/50 transition-colors duration-150">
                                    <td class="px-2 py-2 text-xs">{{ shift.user.name }}</td>
                                    <td class="px-2 py-2 text-xs">{{ shift.name }}</td>
                                    <td class="px-2 py-2 text-xs">{{ formatDateTime(shift.start_time) }}</td>
                                    <td class="px-2 py-2 text-xs">
                                        {{ shift.end_time ? formatDateTime(shift.end_time) : '-' }}
                                    </td>
                                    <td class="px-2 py-2 text-xs">{{ formatCurrency(shift.initial_cash) }}
                                    </td>
                                    <td class="px-2 py-2 text-xs">
                                        {{ shift.final_cash ? formatCurrency(shift.final_cash) : '-' }}
                                    </td>
                                    <td class="px-2 py-2 text-xs">
                                        {{ shift.total_sales ? formatCurrency(shift.total_sales) : '-' }}
                                    </td>
                                    <td class="px-2 py-2 text-xs">
                                        {{ shift.variance ? formatCurrency(shift.variance) : '-' }}
                                    </td>
                                    <td class="px-2 py-2 text-xs">{{ shift.status }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
