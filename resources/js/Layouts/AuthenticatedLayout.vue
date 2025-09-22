<template>
    <div class="min-h-screen flex bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 text-white">
        <!-- Sidebar -->
        <transition name="slide">
            <aside v-if="sidebarOpen"
                class="w-64 bg-white/10 backdrop-blur-lg shadow-xl flex flex-col justify-between fixed inset-y-0 left-0 z-40 animate-fade-in-down">
                <div>
                    <div class="flex items-center gap-3 px-6 py-6">
                        <img :src="logoUrl" alt="POSKU Logo" class="h-10 w-auto drop-shadow-lg" />
                        <span
                            class="font-extrabold text-2xl bg-gradient-to-r from-indigo-300 to-pink-300 bg-clip-text text-transparent">POSKU</span>
                    </div>
                    <nav class="mt-6 space-y-2 px-4">
                        <Link :href="route('dashboard')"
                            class="block px-4 py-3 rounded-xl font-semibold hover:bg-white/20 transition"
                            :class="{ 'bg-white/20': $page.url.startsWith('/dashboard') }">
                        Dashboard
                        </Link>
                        <Link :href="route('profile.edit')"
                            class="block px-4 py-3 rounded-xl font-semibold hover:bg-white/20 transition"
                            :class="{ 'bg-white/20': $page.url.startsWith('/profile') }">
                        Profile
                        </Link>
                        <form method="POST" @submit.prevent="logout">
                            <button type="submit"
                                class="w-full text-left px-4 py-3 rounded-xl font-semibold hover:bg-white/20 transition">
                                Logout
                            </button>
                        </form>
                    </nav>
                </div>
                <footer class="px-6 py-4 text-xs text-white/70">
                    &copy; 2025 <span class="font-bold text-white">POSKU</span>
                </footer>
            </aside>
        </transition>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-h-screen" :class="{ 'ml-64': sidebarOpen }">
            <!-- Topbar -->
            <header class="backdrop-blur-md bg-white/10 shadow-md sticky top-0 z-30 animate-fade-in-down">
                <div class="container mx-auto px-6 py-4 flex justify-between items-center">
                    <div class="flex items-center gap-4">
                        <button @click="toggleSidebar" class="p-2 rounded-lg bg-white/10 hover:bg-white/20 transition">
                            <svg v-if="!sidebarOpen" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                        <h1 class="text-lg font-bold">{{ title }}</h1>
                    </div>
                    <div>
                        <slot name="actions" />
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 container mx-auto px-6 py-8 animate-fade-in-up">
                <slot />
            </main>
        </div>
    </div>
</template>

<script>
import { Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

export default {
    name: "AuthenticatedLayout",
    components: { Link },
    props: {
        logoUrl: {
            type: String,
            default: "/images/logo.svg",
        },
        title: {
            type: String,
            default: "Dashboard",
        },
    },
    setup() {
        const form = useForm({});
        const sidebarOpen = ref(true);

        function logout() {
            form.post(route("logout"));
        }

        function toggleSidebar() {
            sidebarOpen.value = !sidebarOpen.value;
        }

        return { form, logout, sidebarOpen, toggleSidebar };
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

.animate-fade-in-up {
    animation: fadeInUp 0.8s ease forwards;
}

.animate-fade-in-down {
    animation: fadeInDown 0.8s ease forwards;
}

.slide-enter-active,
.slide-leave-active {
    transition: transform 0.3s ease, opacity 0.3s ease;
}

.slide-enter-from {
    transform: translateX(-100%);
    opacity: 0;
}

.slide-enter-to {
    transform: translateX(0);
    opacity: 1;
}

.slide-leave-from {
    transform: translateX(0);
    opacity: 1;
}

.slide-leave-to {
    transform: translateX(-100%);
    opacity: 0;
}
</style>