<template>
    <div
        :class="[
            'flex items-start gap-3 rounded px-4 py-3 text-xs',
            variantClasses[variant],
        ]"
    >
        <span
            :class="[
                'inline-flex h-6 w-6 items-center justify-center rounded-full text-xs font-semibold',
                badgeClasses[variant],
            ]"
        >
            <slot name="icon">!</slot>
        </span>
        <div class="text-[#1f1f1f]">
            <slot />
        </div>
    </div>
</template>

<script setup>
const variantClasses = {
    info: 'bg-[#f7f7f7] border border-[#9c9c9c]',
    success: 'bg-[#f7f7f7] border border-[#9c9c9c]',
    warning: 'bg-[#f7f7f7] border border-[#9c9c9c]',
    danger: 'bg-[#f7f7f7] border border-[#9c9c9c]',
};

const badgeClasses = {
    info: 'bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c]',
    success: 'bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c]',
    warning: 'bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c]',
    danger: 'bg-[#e9e9e9] text-[#1f1f1f] border border-[#9c9c9c]',
};

defineProps({
    variant: {
        type: String,
        default: 'info',
    },
});

defineOptions({
    name: 'InlineNotice',
});
</script>
