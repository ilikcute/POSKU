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
id-ID, {
        style: 'currency',
        currency: 'IDR',
    }).format(value);
};
</script>

<template>
    <Head title="Shift History" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Shift History</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        User</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Store</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Start Time</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        End Time</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Initial Cash</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Final Cash</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Total Sales</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="shift in props.shifts.data" :key="shift.id">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ shift.user.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ shift.store.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ formatDateTime(shift.start_time) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ shift.end_time ?
                                        formatDateTime(shift.end_time) :
                                        "-" }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ formatCurrency(shift.initial_cash) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ shift.final_cash ?
                                        formatCurrency(shift.final_cash) : "-" }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ shift.total_sales ?
                                        formatCurrency(shift.total_sales) : "-" }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ shift.status }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
