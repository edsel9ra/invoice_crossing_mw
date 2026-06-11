<script setup>
import { ref, Transition } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';

const page = usePage();
const auth = page.props.auth;
const mobileMenuOpen = ref(false);

const navItems = [
    { component: 'Dashboard', href: '/dashboard', label: 'Dashboard', icon: '◈' },
    { component: 'Crossings/Index', href: '/cruces', label: 'Canjes', icon: '⊞' },
    { component: 'Clients/Index', href: '/clientes', label: 'Clientes', icon: '◉' },
    { component: 'Items/Index', href: '/items', label: 'Items', icon: '⊡' },
    { component: 'Branches/Index', href: '/sedes', label: 'Sedes', icon: '⌗' },
];

function logout() {
    router.post('/logout');
}

function closeMobileMenu() {
    mobileMenuOpen.value = false;
}
</script>

<template>
    <div class="flex h-screen overflow-hidden bg-surface">
        <!-- Mobile overlay -->
        <Transition name="page">
            <div v-if="mobileMenuOpen" class="fixed inset-0 z-20 bg-slate-900/50 backdrop-blur-sm lg:hidden" @click="mobileMenuOpen = false"></div>
        </Transition>

        <!-- Mobile hamburger -->
        <button @click="mobileMenuOpen = !mobileMenuOpen"
            class="fixed left-4 top-4 z-40 flex h-9 w-9 items-center justify-center rounded-lg bg-white shadow-md border border-slate-200 text-slate-600 transition-all duration-200 hover:bg-slate-50 lg:hidden"
            :class="{ 'left-[260px]': mobileMenuOpen }">
            <span v-if="!mobileMenuOpen" class="text-lg leading-none">☰</span>
            <span v-else class="text-lg leading-none">✕</span>
        </button>

        <!-- Sidebar -->
        <aside
            class="fixed left-0 top-0 z-30 flex h-full w-60 flex-col bg-sidebar transition-transform duration-300 ease-in-out lg:translate-x-0"
            :class="mobileMenuOpen ? 'translate-x-0' : '-translate-x-full'">
            <div class="flex h-16 items-center gap-2.5 border-b border-slate-700/50 px-5">
                <img src="/logo_mw.png" alt="Logo" class="h-8 w-8 rounded-full bg-amber-500/20 p-1 text-amber-400" />
                <div>
                    <h1 class="text-sm font-semibold leading-tight text-white">Canje de Facturas</h1>
                    <p class="text-[11px] leading-tight text-slate-500">Panel de Gestión</p>
                </div>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                <Link
                    v-for="item in navItems"
                    :key="item.component"
                    :href="item.href"
                    @click="closeMobileMenu"
                    class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200"
                    :class="page.component === item.component
                        ? 'bg-sidebar-active text-sidebar-text-active shadow-sm'
                        : 'text-sidebar-text hover:bg-sidebar-hover hover:text-white'"
                >
                    <span class="flex w-5 items-center justify-center text-base transition-transform duration-300 group-hover:scale-110" :class="page.component === item.component ? 'text-amber-400' : 'text-slate-500 group-hover:text-slate-300'">
                        {{ item.icon }}
                    </span>
                    <span class="truncate">{{ item.label }}</span>
                    <span v-if="page.component === item.component" class="ml-auto h-1.5 w-1.5 shrink-0 rounded-full bg-amber-400 animate-pulse"></span>
                </Link>
            </nav>

            <div v-if="auth?.user" class="border-t border-slate-700/50 px-5 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-500/20 text-xs font-semibold text-amber-400">
                        {{ auth.user.name.charAt(0).toUpperCase() }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-white">{{ auth.user.name }}</p>
                        <p class="truncate text-[11px] text-slate-500">{{ auth.user.email }}</p>
                    </div>
                    <button @click="logout" class="shrink-0 rounded-lg p-1.5 text-slate-500 transition-colors hover:bg-slate-700/50 hover:text-white" title="Cerrar sesión">
                        <span class="text-sm">⏻</span>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main content -->
        <main class="min-h-screen flex-1 overflow-y-auto lg:ml-60">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-6 lg:py-8">
                <Transition name="page" mode="out-in">
                    <slot />
                </Transition>
            </div>
        </main>
    </div>
</template>
