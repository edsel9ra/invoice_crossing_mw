<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const crossings = ref([]);
const branches = ref([]);
const pagination = ref({});
const filters = ref({
    series_number: '',
    status: '',
    from: '',
    to: '',
});

const matchedCrossings = computed(() =>
    crossings.value.filter(c => c.status === 'matched')
);

const unmatchedCrossings = computed(() =>
    crossings.value.filter(c => c.status !== 'matched')
);

const hasMixedGroups = computed(() =>
    matchedCrossings.value.length > 0 && unmatchedCrossings.value.length > 0
);

async function loadBranches() {
    try {
        const res = await axios.get('/api/branches');
        branches.value = res.data.branches;
    } catch (e) {
        console.error(e);
    }
}

async function loadCrossings(page = 1) {
    try {
        const params = { ...filters.value, page };
        const res = await axios.get('/api/crossings', { params });
        crossings.value = res.data.crossings.data ?? [];
        pagination.value = res.data.pagination;
    } catch (e) {
        console.error(e);
    }
}

function viewDetail(id) {
    router.visit(`/cruces/${id}`);
}

function clearFilters() {
    filters.value = { series_number: '', status: '', from: '', to: '' };
    loadCrossings();
}

function exportXlsx() {
    const params = new URLSearchParams();
    Object.entries(filters.value).forEach(([k, v]) => { if (v) params.append(k, v); });
    window.open(`/api/crossings/export?${params.toString()}`, '_blank');
}

onMounted(() => {
    loadBranches();
    loadCrossings();
});
</script>

<template>
    <div class="animate-fade-in">
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-slate-800">Historial de Canjes</h2>
            <p class="mt-1 text-sm text-slate-500">Consulta tus participaciones registradas</p>
        </div>

        <div class="card card-hover p-5 mb-6">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Sede</label>
                    <select v-model="filters.series_number" class="select-field text-sm">
                        <option value="">Todas</option>
                        <option v-for="b in branches" :key="b.seriesNumber" :value="b.seriesNumber">
                            {{ b.seriesNumber }} - {{ b.branchName }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Estado</label>
                    <select v-model="filters.status" class="select-field text-sm">
                        <option value="">Todos</option>
                        <option value="matched">Match</option>
                        <option value="without_matches">Sin match</option>
                        <option value="not_found">No encontrado</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Desde</label>
                    <input v-model="filters.from" type="date" class="input-field text-sm" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Hasta</label>
                    <input v-model="filters.to" type="date" class="input-field text-sm" />
                </div>
            </div>
            <div class="mt-4 flex flex-wrap gap-2">
                <button @click="loadCrossings()" class="btn-primary text-sm flex-1 sm:flex-none">
                    Filtrar
                </button>
                <button @click="clearFilters" class="btn-secondary text-sm flex-1 sm:flex-none">
                    Limpiar
                </button>
                <button @click="exportXlsx" class="btn-secondary text-sm flex-1 sm:flex-none">
                    ⬇ Descargar XLSX
                </button>
            </div>
        </div>

        <div class="card card-hover overflow-hidden">

            <template v-if="hasMixedGroups">
                <div class="overflow-x-auto">
                    <div class="bg-emerald-50/50 border-b border-emerald-100 px-5 py-2.5">
                        <span class="text-sm font-semibold text-emerald-700">Facturas que participan</span>
                        <span class="ml-2 text-xs text-emerald-500">({{ matchedCrossings.length }})</span>
                    </div>
                    <table class="min-w-[600px]">
                        <thead>
                            <tr class="bg-slate-50">
                                <th class="table-header">Factura</th>
                                <th class="table-header">Cliente</th>
                                <th class="table-header">Sede</th>
                                <th class="table-header">Estado</th>
                                <th class="table-header text-right">Boletas</th>
                                <th class="table-header text-right">Fecha</th>
                                <th class="table-header text-right"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(c, i) in matchedCrossings" :key="c.id"
                                class="animate-row-in transition-colors hover:bg-slate-50"
                                :style="{ animationDelay: `${i * 0.04}s` }">
                                <td class="table-cell font-medium text-slate-800">{{ c.invoiceNumber }}</td>
                                <td class="table-cell text-slate-600">{{ c.clientName }}</td>
                                <td class="table-cell text-slate-600">{{ c.branchName }}</td>
                                <td class="table-cell">
                                    <span class="badge-success">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        Match
                                    </span>
                                </td>
                                <td class="table-cell text-right">
                                    <div class="inline-flex flex-wrap gap-1 justify-end max-w-[200px]">
                                        <span v-for="code in c.ticketCodes" :key="code"
                                            class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-mono font-medium text-emerald-700">
                                            {{ code }}
                                        </span>
                                    </div>
                                </td>
                                <td class="table-cell text-right text-slate-500">{{ new Date(c.processedAt).toLocaleDateString() }}</td>
                                <td class="table-cell text-right">
                                    <button @click="viewDetail(c.id)" class="btn-ghost text-xs">
                                        Detalle →
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="bg-amber-50/50 border-y border-amber-100 px-5 py-2.5">
                        <span class="text-sm font-semibold text-amber-700">Facturas que no participan</span>
                        <span class="ml-2 text-xs text-amber-500">({{ unmatchedCrossings.length }})</span>
                    </div>
                    <table class="min-w-[600px]">
                        <thead>
                            <tr class="bg-slate-50">
                                <th class="table-header">Factura</th>
                                <th class="table-header">Cliente</th>
                                <th class="table-header">Sede</th>
                                <th class="table-header">Estado</th>
                                <th class="table-header text-right">Boletas</th>
                                <th class="table-header text-right">Fecha</th>
                                <th class="table-header text-right"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(c, i) in unmatchedCrossings" :key="c.id"
                                class="animate-row-in transition-colors hover:bg-slate-50"
                                :style="{ animationDelay: `${i * 0.04}s` }">
                                <td class="table-cell font-medium text-slate-800">{{ c.invoiceNumber }}</td>
                                <td class="table-cell text-slate-600">{{ c.clientName }}</td>
                                <td class="table-cell text-slate-600">{{ c.branchName }}</td>
                                <td class="table-cell">
                                    <span class="badge-warning">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                        {{ c.status === 'without_matches' ? 'Sin match' : 'No encontrado' }}
                                    </span>
                                </td>
                                <td class="table-cell text-right font-semibold text-slate-400">—</td>
                                <td class="table-cell text-right text-slate-500">{{ new Date(c.processedAt).toLocaleDateString() }}</td>
                                <td class="table-cell text-right">
                                    <button @click="viewDetail(c.id)" class="btn-ghost text-xs">
                                        Detalle →
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>

            <template v-else>
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
                                <th class="table-header text-right"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(c, i) in crossings" :key="c.id"
                                class="animate-row-in transition-colors hover:bg-slate-50"
                                :style="{ animationDelay: `${i * 0.04}s` }">
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
                                <td class="table-cell text-right">
                                    <template v-if="c.status === 'matched'">
                                        <div class="inline-flex flex-wrap gap-1 justify-end max-w-[200px]">
                                            <span v-for="code in c.ticketCodes" :key="code"
                                                class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-mono font-medium text-emerald-700">
                                                {{ code }}
                                            </span>
                                        </div>
                                    </template>
                                    <span v-else class="font-semibold text-slate-400">—</span>
                                </td>
                                <td class="table-cell text-right text-slate-500">{{ new Date(c.processedAt).toLocaleDateString() }}</td>
                                <td class="table-cell text-right">
                                    <button @click="viewDetail(c.id)" class="btn-ghost text-xs">
                                        Detalle →
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="crossings.length === 0">
                                <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-400">
                                    <div class="animate-fade-in flex flex-col items-center gap-2">
                                        <span class="text-2xl animate-empty-float">⊞</span>
                                        <span>No hay canjes registrados.</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>

            <div v-if="pagination.lastPage > 1" class="animate-fade-in flex items-center justify-between border-t border-slate-100 px-5 py-3">
                <button @click="loadCrossings(pagination.currentPage - 1)" :disabled="pagination.currentPage <= 1"
                    class="btn-ghost text-sm transition-all duration-200 disabled:text-slate-300 disabled:cursor-not-allowed hover:scale-105 active:scale-95">
                    ← Anterior
                </button>
                <span class="text-sm text-slate-500">
                    Pág <strong class="text-slate-700">{{ pagination.currentPage }}</strong> de {{ pagination.lastPage }}
                </span>
                <button @click="loadCrossings(pagination.currentPage + 1)" :disabled="pagination.currentPage >= pagination.lastPage"
                    class="btn-ghost text-sm transition-all duration-200 disabled:text-slate-300 disabled:cursor-not-allowed hover:scale-105 active:scale-95">
                    Siguiente →
                </button>
            </div>
        </div>
    </div>
</template>
