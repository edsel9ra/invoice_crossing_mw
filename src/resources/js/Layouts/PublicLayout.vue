<script setup>
</script>

<template>
    <div class="min-h-screen bg-surface">
        <header class="bg-white border-b border-slate-200">
            <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4">
                <div class="flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-md bg-amber-500 text-xs font-bold text-white">MW</span>
                    <span class="text-sm font-semibold text-slate-800">Canje de Facturas - Mister Wings</span>
                </div>
                <a href="/login" class="rounded-lg px-3 py-1.5 text-sm text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-800">
                    Ingresar
                </a>
            </div>
        </header>
        <main class="mx-auto max-w-7xl px-4 py-8">
            <slot />
        </main>
    </div>
</template>
