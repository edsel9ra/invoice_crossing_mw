<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const branches = ref([]);

onMounted(async () => {
    try {
        const res = await axios.get('/api/branches');
        branches.value = res.data.branches;
    } catch (e) {
        console.error(e);
    }
});
</script>

<template>
    <div class="animate-fade-in">
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-slate-800">Sedes</h2>
            <p class="mt-1 text-sm text-slate-500">Sedes registradas para el cruce de facturas</p>
        </div>

        <div class="card card-hover overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="table-header">Serie</th>
                            <th class="table-header">Nombre de Sede</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="(b, i) in branches" :key="b.id"
                            class="animate-row-in transition-colors hover:bg-slate-50"
                            :style="{ animationDelay: `${i * 0.04}s` }">
                            <td class="table-cell font-mono font-semibold text-slate-800">{{ b.seriesNumber }}</td>
                            <td class="table-cell">{{ b.branchName }}</td>
                        </tr>
                        <tr v-if="branches.length === 0">
                            <td colspan="2" class="px-4 py-10 text-center text-sm text-slate-400">
                                <div class="animate-fade-in flex flex-col items-center gap-2">
                                    <span class="text-2xl animate-empty-float">⌗</span>
                                    <span>No hay sedes registradas.</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
