<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    authorization: { type: Object, required: true },
});

const AUTH_PRESETS = [
    { value: 'Buka Shift', hint: 'Dipakai saat buka shift' },
    { value: 'Tutup Shift', hint: 'Dipakai saat tutup shift' },
    { value: 'Tutup Harian', hint: 'Dipakai untuk EOD: Station Close & Finalize' },
];

const nameOptions = computed(() => {
    const set = new Set(AUTH_PRESETS.map(x => x.value));
    // Pastikan name lama selalu ada di dropdown
    set.add(props.authorization.name);
    return Array.from(set);
});

const hint = computed(() => AUTH_PRESETS.find(x => x.value === form.name)?.hint);

const form = useForm({
    name: props.authorization.name,
    password: '',
});

const submit = () => {
    form.patch(route('shifts.authorizations.update', props.authorization.id), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>

    <Head title="Edit Authorization" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="font-semibold text-lg text-[#1f1f1f] leading-tight">Edit Authorization</h2>
                <p class="text-xs text-[#555]">Perbarui nama atau password authorization.</p>
            </div>
        </template>

        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-[#f7f7f7] border border-[#9c9c9c] rounded p-6 shadow-sm">
                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <InputLabel for="name" value="Nama Authorization" class="text-[#1f1f1f]" />

                        <select id="name" v-model="form.name" required
                            class="mt-1 block w-full bg-white border border-[#9c9c9c] text-[#1f1f1f] rounded focus:ring-blue-500 focus:border-blue-500">
                            <option v-for="n in nameOptions" :key="n" :value="n">
                                {{ n }}
                            </option>
                        </select>

                        <div v-if="hint" class="text-xs text-[#555] mt-2">{{ hint }}</div>
                        <InputError :message="form.errors.name" class="mt-2 text-red-600" />
                    </div>

                    <div>
                        <InputLabel for="password" value="Password Baru (Opsional)" class="text-[#1f1f1f]" />
                        <TextInput id="password" v-model="form.password" type="password"
                            class="mt-1 block w-full bg-white border border-[#9c9c9c] rounded text-[#1f1f1f]"
                            placeholder="Biarkan kosong jika tidak ingin mengubah password" />
                        <InputError :message="form.errors.password" class="mt-2 text-red-600" />
                    </div>

                    <div class="flex items-center justify-end">
                        <button type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing"
                            class="inline-flex items-center bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c] px-4 py-2 text-xs font-semibold shadow-sm hover:bg-white transition-colors">
                            Update
                        </button>
                    </div>
                </form>

                <div v-if="form.name === 'Tutup Harian'" class="mt-6 text-xs text-[#555]">
                    Authorization ini dipakai oleh EOD Station Close & Finalize. Pastikan password diketahui user yang
                    berwenang.
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
