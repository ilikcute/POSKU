<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>

    <Head title="Profile" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-lg text-[#1f1f1f] leading-tight">
                Profile
            </h2>
        </template>

        <!-- <div class="py-12"> -->
            <div
                class="mx-auto max-w-lg bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-sm p-8 space-y-6">
                <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-sm p-6 sm:p-8">
                    <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status"
                        class="max-w-xl" />
                </div>

                <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-sm p-6 sm:p-8">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-sm p-6 sm:p-8">
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        <!-- </div> -->
    </AuthenticatedLayout>
</template>
