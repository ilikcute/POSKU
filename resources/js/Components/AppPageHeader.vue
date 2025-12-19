<template>
    <div
        class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
    >
        <div>
            <h1 class="text-2xl font-bold text-white">
                {{ title }}
            </h1>
            <p v-if="description" class="text-sm text-gray-300 mt-1">
                {{ description }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <slot name="filters" />
            <slot name="actions" />
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        default: '',
    },
});

defineOptions({
    name: 'AppPageHeader',
});
</script>
