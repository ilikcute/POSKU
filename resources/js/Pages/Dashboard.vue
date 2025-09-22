<template>
    <div class="min-h-screen flex flex-col bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 text-white">
        <!-- Header -->
        <header class="w-full backdrop-blur-md bg-white/10 sticky top-0 z-50 shadow-lg animate-fade-in-down">
            <div class="container mx-auto px-6 py-4 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <img :src="logoUrl" alt="POSKU Logo" class="h-10 w-auto drop-shadow-lg" />
                    <span
                        class="font-extrabold text-2xl bg-gradient-to-r from-indigo-300 to-pink-300 bg-clip-text text-transparent tracking-wide">
                        POSKU
                    </span>
                </div>
                <nav class="flex items-center space-x-4">
                    <Link :href="route('dashboard')"
                        class="text-sm font-semibold text-white/80 hover:text-white transition">Dashboard</Link>
                    <Link :href="route('profile.edit')"
                        class="text-sm font-semibold text-white/80 hover:text-white transition">Profile</Link>
                    <form @submit.prevent="logout" method="post">
                        <button type="submit"
                            class="text-sm font-semibold text-white/80 hover:text-white transition">Logout</button>
                    </form>
                </nav>
            </div>
        </header>

        <!-- Dashboard Content -->
        <main class="flex-grow container mx-auto px-6 py-10 animate-fade-in-up">
            <h1 class="text-3xl font-bold mb-8 drop-shadow-lg">Dashboard</h1>

            <!-- Example Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                <div
                    class="p-6 bg-white/10 backdrop-blur-lg rounded-2xl shadow-xl hover:scale-105 transform transition">
                    <h2 class="text-lg font-semibold mb-2">Total Penjualan</h2>
                    <p class="text-3xl font-extrabold">{{ stats.sales }}</p>
                </div>
                <div
                    class="p-6 bg-white/10 backdrop-blur-lg rounded-2xl shadow-xl hover:scale-105 transform transition">
                    <h2 class="text-lg font-semibold mb-2">Produk</h2>
                    <p class="text-3xl font-extrabold">{{ stats.products }}</p>
                </div>
                <div
                    class="p-6 bg-white/10 backdrop-blur-lg rounded-2xl shadow-xl hover:scale-105 transform transition">
                    <h2 class="text-lg font-semibold mb-2">Pelanggan</h2>
                    <p class="text-3xl font-extrabold">{{ stats.customers }}</p>
                </div>
            </div>

            <!-- Slot for additional dashboard content -->
            <div class="bg-white/10 backdrop-blur-lg rounded-2xl shadow-xl p-6">
                <slot />
            </div>
        </main>

        <!-- Footer -->
        <footer class="w-full py-6 bg-white/10 backdrop-blur-md text-center text-sm text-white/70 animate-fade-in">
            &copy; 2025 <span class="font-bold text-white">POSKU</span>. All Rights Reserved.
        </footer>
    </div>
</template>

<script>
import { Head, Link, useForm } from "@inertiajs/vue3";

export default {
    name: "Dashboard",
    components: {
        Head,
        Link,
    },
    props: {
        stats: Object,
        logoUrl: {
            type: String,
            default: "/images/logo.svg",
        },
    },
    setup() {
        const form = useForm({});

        function logout() {
            form.post(route("logout"));
        }

        return { form, logout };
    },
};
</script>

<style scoped>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }

    to {
        opacity: 1;
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.8s ease forwards;
}

.animate-fade-in-down {
    animation: fadeInDown 0.8s ease forwards;
}

.animate-fade-in {
    animation: fadeIn 1s ease forwards;
}
</style>
