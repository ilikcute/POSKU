<template>
    <section
        class="bg-white/5 border border-white/10 rounded-2xl shadow-xl backdrop-blur-md"
    >
        <header
            v-if="$slots.headline"
            class="px-6 py-4 border-b border-white/10 flex items-center justify-between gap-4"
        >
            <slot name="headline" />
        </header>
        <div class="px-6 py-5">
            <slot />
        </div>
        <footer
            v-if="$slots.footer"
            class="px-6 py-4 border-t border-white/10"
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
