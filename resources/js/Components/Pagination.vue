<script setup>
import { Link } from "@inertiajs/vue3";

defineProps({
    links: Array,
});
</script>

<template>
    <div v-if="links.length > 3" class="flex flex-wrap gap-2 mt-6 justify-center">
        <template v-for="(link, key) in links" :key="key">
            <div
                v-if="link.url === null"
                class="px-3 py-2 border border-[#9c9c9c] bg-[#f0f0f0] text-[#888] text-xs font-semibold cursor-not-allowed"
                v-html="link.label"
            />
            <Link
                v-else
                class="px-3 py-2 border border-[#9c9c9c] text-xs font-semibold transition-colors"
                :class="{
                    'bg-[#e9e9e9] text-[#1f1f1f]': link.active,
                    'bg-white text-[#1f1f1f] hover:bg-[#f7f7f7]': !link.active
                }"
                :href="link.url"
                v-html="link.label"
            />
        </template>
    </div>
</template>
