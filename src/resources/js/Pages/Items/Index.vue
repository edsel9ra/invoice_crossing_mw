<script setup>
import { ref, onMounted, Transition } from 'vue';
import axios from 'axios';

const items = ref([]);
const form = ref({ code: '', name: '', tickets_per_unit: 1, is_active: true });
const message = ref('');

async function loadItems() {
    try {
        const res = await axios.get('/api/items');
        items.value = res.data.items;
    } catch (e) {
        console.error(e);
    }
}

async function saveItem() {
    message.value = '';
    try {
        const res = await axios.post('/api/items', form.value);
        message.value = res.data.message;
        form.value = { code: '', name: '', tickets_per_unit: 1, is_active: true };
        await loadItems();
    } catch (e) {
        message.value = e.response?.data?.message || 'Error al guardar';
    }
}

onMounted(loadItems);
</script>

<template>
    <div class="animate-fade-in">
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-slate-800">Maestra de Items</h2>
            <p class="mt-1 text-sm text-slate-500">Administre los items que se cruzan con las facturas</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-1">
                <div class="card p-6">
                    <h3 class="text-base font-semibold text-slate-800 mb-5">Nuevo Item</h3>
                    <form @submit.prevent="saveItem" class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Código</label>
                            <input v-model="form.code" type="text" maxlength="10" required placeholder="Ej: A001"
                                class="input-field font-mono" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Nombre</label>
                            <input v-model="form.name" type="text" maxlength="160" required placeholder="Nombre del item"
                                class="input-field" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Boletas por Unidad</label>
                            <input v-model.number="form.tickets_per_unit" type="number" min="0" required
                                class="input-field" />
                        </div>
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input v-model="form.is_active" type="checkbox"
                                class="h-4 w-4 rounded border-slate-300 text-amber-500 focus:ring-amber-400/20 focus:ring-2" />
                            <span class="text-sm text-slate-700">Activo</span>
                        </label>
                        <button type="submit" class="btn-primary w-full">
                            Guardar Item
                        </button>
                    </form>
                    <Transition name="alert">
                        <div v-if="message" class="mt-5 rounded-lg bg-emerald-50 border border-emerald-100 p-3 text-sm text-emerald-700">
                            {{ message }}
                        </div>
                    </Transition>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="card card-hover overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-[400px]">
                            <thead>
                                <tr class="bg-slate-50">
                                    <th class="table-header">Código</th>
                                    <th class="table-header">Nombre</th>
                                    <th class="table-header text-center">Boletas x Unid</th>
                                    <th class="table-header text-center">Activo</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="(item, i) in items" :key="item.id"
                                    class="animate-row-in transition-colors hover:bg-slate-50"
                                    :class="!item.isActive ? 'opacity-50' : ''"
                                    :style="{ animationDelay: `${i * 0.04}s` }">
                                    <td class="table-cell font-mono font-medium">{{ item.code }}</td>
                                    <td class="table-cell">{{ item.name }}</td>
                                    <td class="table-cell text-center font-medium">{{ item.ticketsPerUnit }}</td>
                                    <td class="table-cell text-center">
                                        <span :class="item.isActive ? 'badge-success' : 'badge-danger'">
                                            {{ item.isActive ? 'SI' : 'NO' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="items.length === 0">
                                    <td colspan="4" class="px-4 py-10 text-center text-sm text-slate-400">
                                        <div class="animate-fade-in flex flex-col items-center gap-2">
                                            <span class="text-2xl animate-empty-float">⊡</span>
                                            <span>No hay items registrados.</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
