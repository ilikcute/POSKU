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
            <h2 class="font-semibold text-xl text-white leading-tight">
                Profile
            </h2>
        </template>

        <!-- <div class="py-12"> -->
            <div
                class="mx-auto max-w-lg bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl shadow-lg p-8 space-y-6">
                <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl shadow-lg p-6 sm:p-8">
                    <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status"
                        class="max-w-xl" />
                </div>

                <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl shadow-lg p-6 sm:p-8">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl shadow-lg p-6 sm:p-8">
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        <!-- </div> -->
    </AuthenticatedLayout>
</template>
