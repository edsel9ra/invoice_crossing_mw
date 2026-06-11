<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    id: { type: [String, Number], required: true },
});

const crossing = ref(null);

onMounted(async () => {
    try {
        const res = await axios.get(`/api/crossings/${props.id}`);
        crossing.value = res.data.crossing;
    } catch (e) {
        console.error(e);
    }
});
</script>

<template>
    <div class="animate-fade-in">
        <div class="mb-8">
            <button @click="router.visit('/cruces')" class="btn-ghost text-sm mb-3">
                ← Volver al historial
            </button>
            <h2 class="text-2xl font-bold text-slate-800">Detalle del Canje</h2>
            <p class="mt-1 text-sm text-slate-500">Información completa del canje #{{ props.id }}</p>
        </div>

        <div v-if="!crossing" class="flex items-center justify-center py-16">
            <div class="animate-fade-in flex flex-col items-center gap-4 text-slate-400">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl border border-slate-200 bg-white">
                    <span class="h-6 w-6 animate-spin rounded-full border-2 border-slate-200 border-t-amber-500"></span>
                </div>
                <div class="text-center">
                    <p class="text-sm font-medium text-slate-500">Cargando detalle</p>
                    <p class="text-xs text-slate-400 mt-0.5">Espere un momento...</p>
                </div>
            </div>
        </div>

        <template v-if="crossing">
            <div class="card card-hover p-6 mb-6 animate-slide-in">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">Factura</p>
                        <p class="text-sm font-semibold text-slate-800 font-mono">{{ crossing.invoiceNumber }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">Sede</p>
                        <p class="text-sm text-slate-800">{{ crossing.branchName }} <span class="text-slate-400 font-mono">({{ crossing.seriesNumber }})</span></p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">Cliente</p>
                        <p class="text-sm text-slate-800">{{ crossing.clientName }} <span class="text-slate-400">—</span> <span class="text-slate-500 font-mono text-xs">{{ crossing.clientDocNum }}</span></p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">Estado</p>
                        <span :class="crossing.status === 'matched' ? 'badge-success' : 'badge-warning'">
                            <span class="h-1.5 w-1.5 rounded-full"
                                :class="crossing.status === 'matched' ? 'bg-emerald-500 animate-pulse-dot' : 'bg-amber-500'"></span>
                            {{ crossing.status === 'matched' ? 'Match' : 'Sin match' }}
                        </span>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">Boletas Generadas</p>
                        <p class="text-xl font-bold text-amber-600">{{ crossing.ticketsAdded }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">Procesado</p>
                        <p class="text-sm text-slate-800">{{ new Date(crossing.processedAt).toLocaleString() }}</p>
                    </div>
                </div>
            </div>

            <div class="card card-hover mb-6 overflow-hidden animate-slide-in stagger-2">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h3 class="text-base font-semibold text-slate-800">Items Matcheados</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-[500px]">
                        <thead>
                            <tr class="bg-slate-50">
                                <th class="table-header">Código</th>
                                <th class="table-header">Nombre</th>
                                <th class="table-header text-right">Cantidad</th>
                                <th class="table-header text-right">Boletas/Unidad</th>
                                <th class="table-header text-right">Total Boletas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="d in crossing.details" :key="d.id" class="transition-colors hover:bg-slate-50">
                                <td class="table-cell font-mono">{{ d.itemCode }}</td>
                                <td class="table-cell">{{ d.itemName }}</td>
                                <td class="table-cell text-right">{{ d.quantity }}</td>
                                <td class="table-cell text-right">{{ d.ticketsPerUnit }}</td>
                                <td class="table-cell text-right font-semibold text-amber-600">{{ d.ticketsGenerated }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card p-6 animate-slide-in stagger-4">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-base font-semibold text-slate-800">Boletas Generadas</h3>
                    <span class="text-xs font-medium text-slate-400">{{ crossing.tickets.length }} boleta(s)</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-2">
                    <div v-for="(t, i) in crossing.tickets" :key="t.id"
                        class="animate-ticket-in group relative rounded-lg border border-amber-200 bg-amber-50/50 p-2 text-center transition-all duration-200 hover:border-amber-400 hover:bg-amber-50 hover:shadow-sm hover:-translate-y-0.5"
                        :style="{ animationDelay: `${i * 0.025}s` }">
                        <p class="text-xs font-bold font-mono text-amber-800 truncate" :title="t.ticketCode">{{ t.ticketCode }}</p>
                        <p class="text-[10px] text-amber-500 font-mono">{{ t.itemCode }}</p>
                        <a :href="`/tickets/${t.id}/descargar`" target="_blank"
                            class="mt-1 inline-flex items-center gap-0.5 rounded bg-amber-200/60 px-1.5 py-0.5 text-[10px] text-amber-800 transition-all duration-200 hover:bg-amber-300/60 hover:shadow-sm">
                            ⬇
                        </a>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>
