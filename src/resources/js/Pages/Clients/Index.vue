<script setup>
import { ref, onMounted, Transition } from 'vue';
import axios from 'axios';

const form = ref({
    name: '',
    doc_num: '',
    phone_number: '',
});

const clients = ref([]);
const clientResult = ref(null);
const error = ref('');

function waUrl(phone) {
    const digits = phone.replace(/\D/g, '');
    const number = digits.startsWith('57') ? digits : '57' + digits;
    return `https://wa.me/${number}`;
}

async function loadClients() {
    try {
        const res = await axios.get('/api/clients');
        clients.value = res.data.clients;
    } catch (e) {
        console.error(e);
    }
}

async function registerClient() {
    error.value = '';
    clientResult.value = null;

    try {
        const res = await axios.post('/api/clients', form.value);
        clientResult.value = res.data;
        form.value = { name: '', doc_num: '', phone_number: '' };
        await loadClients();
    } catch (e) {
        error.value = e.response?.data?.message || 'Error al registrar cliente';
    }
}

onMounted(loadClients);
</script>

<template>
    <div class="animate-fade-in">
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-slate-800">Clientes</h2>
            <p class="mt-1 text-sm text-slate-500">Registro y consulta de clientes</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-1">
                <div class="card p-6">
                    <h3 class="text-base font-semibold text-slate-800 mb-5">Registrar Cliente</h3>
                    <form @submit.prevent="registerClient" class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Nombre</label>
                            <input v-model="form.name" type="text" required placeholder="Nombre completo"
                                class="input-field" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Nro. Documento</label>
                            <input v-model="form.doc_num" type="text" required placeholder="CC / NIT"
                                class="input-field" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Teléfono</label>
                            <input v-model="form.phone_number" type="text" required placeholder="310 123 4567"
                                class="input-field" />
                        </div>
                        <button type="submit" class="btn-primary w-full">
                            Registrar Cliente
                        </button>
                    </form>

                    <Transition name="alert">
                        <div v-if="error" class="mt-5 rounded-lg bg-red-50 border border-red-100 p-4 text-sm text-red-700">
                            {{ error }}
                        </div>
                    </Transition>

                    <Transition name="alert">
                        <div v-if="clientResult" class="mt-5 rounded-lg bg-emerald-50 border border-emerald-100 p-4">
                            <p class="text-sm font-medium text-emerald-800">{{ clientResult.message }}</p>
                            <p class="mt-1 text-xs text-emerald-600 font-mono">
                                ID: {{ clientResult.client.id }} · Doc: {{ clientResult.client.documentNumber }}
                            </p>
                        </div>
                    </Transition>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="card card-hover overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-slate-50">
                                    <th class="table-header">Nombre</th>
                                    <th class="table-header">Documento</th>
                                    <th class="table-header">Teléfono</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="(c, i) in clients" :key="c.id"
                                    class="animate-row-in transition-colors hover:bg-slate-50"
                                    :style="{ animationDelay: `${i * 0.04}s` }">
                                    <td class="table-cell font-medium text-slate-800">{{ c.name }}</td>
                                    <td class="table-cell text-slate-600 font-mono">{{ c.documentNumber }}</td>
                                    <td class="table-cell">
                                        <div class="flex items-center gap-2">
                                            <span class="text-slate-600">{{ c.phoneNumber }}</span>
                                            <a :href="waUrl(c.phoneNumber)" target="_blank" rel="noopener"
                                                class="inline-flex items-center gap-1 rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 transition-all duration-200 hover:bg-emerald-100 hover:shadow-sm active:scale-95"
                                                title="Enviar WhatsApp">
                                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                                </svg>
                                                WhatsApp
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="clients.length === 0">
                                    <td colspan="3" class="px-4 py-10 text-center text-sm text-slate-400">
                                        <div class="animate-fade-in flex flex-col items-center gap-2">
                                            <span class="text-2xl animate-empty-float">👤</span>
                                            <span>No hay clientes registrados.</span>
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
