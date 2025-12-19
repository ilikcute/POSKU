<template>
    <div
        :class="[
            'flex items-start gap-3 rounded-xl px-4 py-3 text-sm',
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
        <div class="text-white/90">
            <slot />
        </div>
    </div>
</template>

<script setup>
const variantClasses = {
    info: 'bg-blue-500/10 border border-blue-400/40',
    success: 'bg-green-500/10 border border-green-400/40',
    warning: 'bg-amber-500/10 border border-amber-400/40',
    danger: 'bg-rose-500/10 border border-rose-400/40',
};

const badgeClasses = {
    info: 'bg-blue-500/80 text-white',
    success: 'bg-green-500/80 text-white',
    warning: 'bg-amber-500/80 text-amber-950',
    danger: 'bg-rose-500/80 text-white',
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
