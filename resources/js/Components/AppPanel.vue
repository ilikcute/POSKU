<template>
    <section
        class="bg-[#f7f7f7] border border-[#9c9c9c] rounded shadow-sm"
    >
        <header
            v-if="$slots.headline"
            class="px-6 py-4 border-b border-[#d0d0d0] flex items-center justify-between gap-4"
        >
            <slot name="headline" />
        </header>
        <div class="px-6 py-5">
            <slot />
        </div>
        <footer
            v-if="$slots.footer"
            class="px-6 py-4 border-t border-[#d0d0d0]"
        >
            <slot name="footer" />
        </footer>
    </section>
</template>

<script setup>
defineOptions({
    name: 'AppPanel',
});
</script>
