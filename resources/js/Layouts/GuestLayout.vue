<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
        <img
            id="background"
            class="absolute -left-20 top-0 max-w-[877px]"
            src="https://laravel.com/assets/img/welcome/background.svg"
        />
        <div
            class="relative flex min-h-screen flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white"
        >
            <div class="relative w-full max-w-md px-6">
                <div>
                    <Link href="/">
                        <ApplicationLogo class="mx-auto h-16 w-auto text-gray-500" />
                    </Link>
                </div>

                <div
                    class="mt-6 w-full overflow-hidden bg-white/80 dark:bg-zinc-900/80 backdrop-blur-sm px-6 py-4 shadow-md sm:rounded-lg"
                >
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>