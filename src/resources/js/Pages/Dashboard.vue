<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';


const stats = ref({
    totalCrossings: 0,
    totalTickets: 0,
    totalClients: 0,
    recentCrossings: [],
});

onMounted(async () => {
    try {
        const [crossRes, statsRes] = await Promise.all([
            axios.get('/api/crossings?per_page=5'),
            axios.get('/api/stats'),
        ]);
        stats.value.totalCrossings = crossRes.data.pagination.total;
        stats.value.totalClients = statsRes.data.totalClients;
        stats.value.totalTickets = statsRes.data.totalTickets;
        stats.value.recentCrossings = crossRes.data.crossings.data ?? [];
    } catch (e) {
        console.error(e);
    }
});
</script>

<template>
    <div class="animate-fade-in">
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-slate-800">Dashboard</h2>
            <p class="mt-1 text-sm text-slate-500">Resumen de actividad del sistema</p>
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3 mb-8">
            <div class="card card-hover p-5 animate-slide-in">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                        <span class="text-lg font-bold">📋</span>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Canjes Realizados</p>
                        <p class="mt-0.5 text-2xl font-bold text-slate-800">{{ stats.totalCrossings }}</p>
                    </div>
                </div>
            </div>

            <div class="card card-hover p-5 animate-slide-in" style="animation-delay: 0.1s">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-sky-100 text-sky-600">
                        <span class="text-lg font-bold">👤</span>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Clientes Registrados</p>
                        <p class="mt-0.5 text-2xl font-bold text-slate-800">{{ stats.totalClients }}</p>
                    </div>
                </div>
            </div>

            <div class="card card-hover p-5 animate-slide-in" style="animation-delay: 0.2s">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                        <span class="text-lg font-bold">🎫</span>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Total Boletas</p>
                        <p class="mt-0.5 text-2xl font-bold text-slate-800">{{ stats.totalTickets }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card animate-fade-in overflow-hidden">
            <div class="border-b border-slate-100 px-4 sm:px-5 py-4">
                <h3 class="text-base font-semibold text-slate-800">Últimos Canjes</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-[600px]">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="table-header">Factura</th>
                            <th class="table-header">Cliente</th>
                            <th class="table-header">Sede</th>
                            <th class="table-header">Estado</th>
                            <th class="table-header text-right">Boletas</th>
                            <th class="table-header text-right">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="(c, i) in stats.recentCrossings" :key="c.id"
                            class="animate-row-in transition-colors hover:bg-slate-50"
                            :style="{ animationDelay: `${i * 0.05}s` }">
                            <td class="table-cell font-medium text-slate-800">{{ c.invoiceNumber }}</td>
                            <td class="table-cell text-slate-600">{{ c.clientName }}</td>
                            <td class="table-cell text-slate-600">{{ c.branchName }}</td>
                            <td class="table-cell">
                                <span :class="c.status === 'matched' ? 'badge-success' : 'badge-warning'">
                                    <span class="h-1.5 w-1.5 rounded-full"
                                        :class="c.status === 'matched' ? 'bg-emerald-500' : 'bg-amber-500'"></span>
                                    {{ c.status === 'matched' ? 'Match' : c.status === 'without_matches' ? 'Sin match' : 'No encontrado' }}
                                </span>
                            </td>
                            <td class="table-cell text-right font-semibold">{{ c.ticketsAdded }}</td>
                            <td class="table-cell text-right text-slate-500">{{ new Date(c.processedAt).toLocaleDateString() }}</td>
                        </tr>
                        <tr v-if="stats.recentCrossings.length === 0">
                            <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-400">
                                <div class="animate-fade-in flex flex-col items-center gap-2">
                                    <span class="text-2xl animate-empty-float">📊</span>
                                    <span>No hay cruces registrados aún.</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
