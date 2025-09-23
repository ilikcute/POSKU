<script setup>
import { Link } from "@inertiajs/vue3";

defineProps({
    links: Array,
});
</script>

<template>
    <div v-if="links.length > 3" class="flex flex-wrap -mb-1 mt-6 justify-center">
        <template v-for="(link, key) in links" :key="key">
            <div
                v-if="link.url === null"
                class="mr-1 mb-1 px-4 py-2 rounded-full text-sm shadow-lg bg-gradient-to-r from-gray-500 to-gray-600 text-white opacity-50 cursor-not-allowed"
                v-html="link.label"
            />
            <Link
                v-else
                class="mr-1 mb-1 px-4 py-2 rounded-full text-sm shadow-lg font-bold transition-transform duration-200"
                :class="{
                    'bg-gradient-to-r from-green-400 to-emerald-400 text-white hover:scale-105 hover:from-green-500 hover:to-emerald-500': link.active,
                    'bg-gradient-to-r from-blue-400 to-blue-500 text-white hover:scale-105 hover:from-blue-500 hover:to-blue-600': !link.active
                }"
                :href="link.url"
                v-html="link.label"
            />
        </template>
    </div>
</template>