<script setup>
import { ref, computed, Transition, TransitionGroup } from 'vue';
import axios from 'axios';
import JerseysIllustration from '../../Components/JerseysIllustration.vue';

const step = ref('lookup');

const docNum = ref('');
const clientData = ref(null);
const clientError = ref('');

const registerForm = ref({ name: '', doc_num: '', phone_number: '' });
const registering = ref(false);
const registerError = ref('');

const branches = ref([]);
const selectedBranch = ref('');

const invoiceInput = ref('');
const invoiceNumbers = ref([]);

const loading = ref(false);
const crossResult = ref(null);
const crossError = ref('');

const showSuccess = ref(false);

async function lookupClient() {
    clientError.value = '';
    clientData.value = null;
    const doc = docNum.value.trim();
    if (!doc) return;

    try {
        const res = await axios.get(`/api/clients/by-doc/${encodeURIComponent(doc)}`);
        clientData.value = res.data.client;
        if (clientData.value) {
            await loadBranches();
            step.value = 'branch';
        }
    } catch (e) {
        if (e.response?.status === 404) {
            registerForm.value.doc_num = doc;
            step.value = 'register';
        } else {
            clientError.value = 'Error al buscar cliente';
        }
    }
}

async function registerClient() {
    registerError.value = '';
    registering.value = true;
    try {
        const res = await axios.post('/api/clients', registerForm.value);
        clientData.value = res.data.client;
        await loadBranches();
        step.value = 'branch';
    } catch (e) {
        registerError.value = e.response?.data?.message || 'Error al registrar cliente';
    } finally {
        registering.value = false;
    }
}

async function loadBranches() {
    try {
        const res = await axios.get('/api/branches');
        branches.value = res.data.branches;
    } catch (e) {
        console.error(e);
    }
}

function addInvoice() {
    const val = invoiceInput.value.trim();
    if (val && !invoiceNumbers.value.includes(val)) {
        invoiceNumbers.value.push(val);
    }
    invoiceInput.value = '';
}

function removeInvoice(index) {
    invoiceNumbers.value.splice(index, 1);
}

async function executeCross() {
    if (!selectedBranch.value || invoiceNumbers.value.length === 0) return;

    loading.value = true;
    crossError.value = '';
    crossResult.value = null;

    try {
        const res = await axios.post(`/api/clients/${clientData.value.id}/cross`, {
            series_number: selectedBranch.value,
            invoice_numbers: invoiceNumbers.value,
        });
        crossResult.value = res.data;
        showSuccess.value = true;
    } catch (e) {
        crossError.value = e.response?.data?.message || 'Error al procesar cruce';
    } finally {
        loading.value = false;
    }
}

function goBackToLookup() {
    step.value = 'lookup';
    docNum.value = '';
    clientData.value = null;
    selectedBranch.value = '';
    invoiceNumbers.value = [];
    registerForm.value = { name: '', doc_num: '', phone_number: '' };
}

function reset() {
    step.value = 'lookup';
    docNum.value = '';
    clientData.value = null;
    registerForm.value = { name: '', doc_num: '', phone_number: '' };
    selectedBranch.value = '';
    invoiceNumbers.value = [];
    crossResult.value = null;
    showSuccess.value = false;
    crossError.value = '';
    clientError.value = '';
    registerError.value = '';
}

function downloadTicket(ticketId) {
    window.open(`/tickets/${ticketId}/descargar`, '_blank');
}

function downloadAllTickets(results) {
    results.forEach(r => {
        if (r.tickets) {
            r.tickets.forEach(t => downloadTicket(t.id));
        }
    });
}

const selectedBranchName = computed(() => {
    const branch = branches.value.find(b => b.seriesNumber === selectedBranch.value);
    return branch ? branch.branchName : selectedBranch.value;
});

const totalTickets = computed(() => {
    if (!crossResult?.value?.results) return 0;
    return crossResult.value.results.reduce((sum, r) => sum + (r.total_tickets || 0), 0);
});

const hasAnyMatch = computed(() => {
    if (!crossResult?.value?.results) return false;
    return crossResult.value.results.some(r => r.status === 'matched');
});

const allTickets = computed(() => {
    if (!crossResult?.value?.results) return [];
    const tickets = [];
    crossResult.value.results.forEach(r => {
        if (r.tickets) {
            r.tickets.forEach(t => tickets.push(t));
        }
    });
    return tickets;
});

const isRegistering = computed(() => step.value === 'register' || registerForm.value.doc_num !== '');

const stepLabels = computed(() => {
    if (isRegistering.value) {
        return ['Documento', 'Registrar', 'Sede', 'Facturas'];
    }
    return ['Documento', 'Sede', 'Facturas'];
});

const stepsMap = computed(() => isRegistering.value
    ? ['lookup', 'register', 'branch', 'invoices']
    : ['lookup', 'branch', 'invoices']
);

const stepIndex = computed(() => stepsMap.value.indexOf(step.value));
</script>

<template>
    <div class="animate-fade-in">
        <div class="mb-8 text-center">
            <img src="/logo_mw.png" alt="Logo" class="mx-auto h-20 w-auto mb-4" />
            <h2 class="text-2xl font-bold text-slate-800">Registra tus facturas</h2>
            <p class="mt-1 text-sm text-slate-500">Participa registrando tus facturas y obtén boletas para el sorteo de una de las camisetas de la Selección Colombia</p>
        </div>

        <div v-if="!showSuccess" class="mb-8">
            <div class="flex items-center justify-center gap-1 sm:gap-2">
                <div v-for="(label, i) in stepLabels" :key="i" class="flex items-center gap-1 sm:gap-2">
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold transition-all duration-300"
                        :class="i <= stepIndex
                            ? 'bg-amber-500 text-white'
                            : 'bg-slate-100 text-slate-400'">
                        {{ i + 1 }}
                    </div>
                    <span class="hidden sm:inline text-sm" :class="i <= stepIndex ? 'font-medium text-slate-700' : 'text-slate-400'">
                        {{ label }}
                    </span>
                    <span v-if="i < stepLabels.length - 1" class="hidden sm:block mx-1 h-px w-6 sm:w-8 bg-slate-200"></span>
                </div>
            </div>
            <p class="mt-2 text-center text-xs font-medium text-slate-500 sm:hidden">{{ stepLabels[stepIndex] || '' }}</p>
        </div>

        <div class="flex flex-col lg:flex-row items-center justify-center gap-8 lg:gap-12">
            <div class="w-full max-w-lg shrink-0">
                <Transition name="step" mode="out-in">
                    <div v-if="step === 'lookup'" key="lookup" class="card card-hover p-6">
                        <h3 class="text-base font-semibold text-slate-800 mb-2">¡Participa!</h3>
                        <p class="mb-5 text-sm text-slate-500">Ingresa tu número de documento para comenzar</p>
                <form @submit.prevent="lookupClient" class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Nro. Documento</label>
                        <input v-model="docNum" type="text" required placeholder="CC/CE/PT/Pasaporte"
                            class="input-field" />
                    </div>
                    <button type="submit" class="btn-primary w-full">
                        Validar
                    </button>
                </form>
                <Transition name="alert">
                    <div v-if="clientError" class="mt-5 rounded-lg bg-red-50 border border-red-100 p-4 text-sm text-red-700">
                        {{ clientError }}
                    </div>
                </Transition>
            </div>

            <div v-else-if="step === 'register'" key="register" class="card card-hover max-w-lg p-6">
                <h3 class="text-base font-semibold text-slate-800 mb-2">Cliente no registrado</h3>
                <p class="mb-5 text-sm text-slate-500">Complete sus datos para registrarse</p>
                <form @submit.prevent="registerClient" class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Nro. Documento</label>
                        <input :value="registerForm.doc_num" type="text" disabled
                            class="input-field bg-slate-50 text-slate-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Nombre</label>
                        <input v-model="registerForm.name" type="text" required placeholder="Nombre completo"
                            class="input-field" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Teléfono</label>
                        <input v-model="registerForm.phone_number" type="text" required placeholder="310 123 4567"
                            class="input-field" />
                    </div>
                    <button type="submit" class="btn-primary w-full" :disabled="registering">
                        <span v-if="registering" class="flex items-center justify-center gap-2">
                            <span class="h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white"></span>
                            Registrando...
                        </span>
                        <span v-else>Registrarse y continuar</span>
                    </button>
                </form>
                <Transition name="alert">
                    <div v-if="registerError" class="mt-5 rounded-lg bg-red-50 border border-red-100 p-4 text-sm text-red-700">
                        {{ registerError }}
                    </div>
                </Transition>
            </div>

            <div v-else-if="step === 'branch'" key="branch" class="card card-hover max-w-lg p-6">
                <h3 class="text-base font-semibold text-slate-800 mb-5">Seleccionar Sede</h3>
                <div v-if="clientData" class="mb-5 rounded-lg bg-sky-50 border border-sky-100 p-4 text-sm text-sky-800 animate-slide-up">
                Cliente: <strong>{{ clientData.name }}</strong>
                <span class="text-sky-500">·</span>
                Número de Documento: {{ clientData.documentNumber }}
                </div>
                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Sede</label>
                        <select v-model="selectedBranch" required class="select-field">
                            <option value="">-- Seleccionar --</option>
                            <option v-for="b in branches" :key="b.seriesNumber" :value="b.seriesNumber">
                                {{ b.branchName }}
                            </option>
                        </select>
                    </div>
                    <div class="flex gap-3">
                        <button @click="goBackToLookup" type="button"
                            class="btn-secondary flex-1 transition-transform duration-200 active:scale-[0.98]">
                            Atrás
                        </button>
                        <button @click="step = 'invoices'" :disabled="!selectedBranch"
                            class="btn-primary flex-1 transition-transform duration-200 active:scale-[0.98]">
                            Siguiente
                        </button>
                    </div>
                </div>
            </div>

            <div v-else-if="step === 'invoices' && !showSuccess" key="invoices" class="card card-hover max-w-lg p-6">
                <h3 class="text-base font-semibold text-slate-800 mb-5">Facturas</h3>
                <div class="mb-5 rounded-lg bg-slate-50 border border-slate-200 p-3 text-sm text-slate-600">
                    Sede: <strong class="text-slate-800">{{ selectedBranchName }}</strong>
                </div>
                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Agregar factura</label>
                        <div class="flex gap-2">
                            <input v-model="invoiceInput" type="text" @keydown.enter.prevent="addInvoice"
                                placeholder="Nro. Factura"
                                class="input-field flex-1" />
                            <button @click="addInvoice" type="button"
                                class="btn-secondary transition-transform duration-200 active:scale-[0.95]">
                                +
                            </button>
                        </div>
                    </div>

                    <Transition name="alert">
                        <div v-if="invoiceNumbers.length > 0" key="invoice-list">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-slate-700">Facturas ingresadas</span>
                                <span class="text-xs text-slate-400">{{ invoiceNumbers.length }} factura(s)</span>
                            </div>
                            <TransitionGroup name="list" tag="ul" class="space-y-1.5">
                                <li v-for="(inv, i) in invoiceNumbers" :key="inv"
                                    class="group flex items-center justify-between rounded-lg border border-slate-200 bg-white px-3 py-2 transition-all duration-200 hover:border-slate-300 hover:shadow-sm hover:-translate-y-0.5">
                                    <span class="text-sm font-mono text-slate-700">{{ inv }}</span>
                                    <button @click="removeInvoice(i)"
                                        class="text-slate-300 transition-all duration-200 hover:text-red-500 hover:scale-110 text-lg leading-none">
                                        ×
                                    </button>
                                </li>
                            </TransitionGroup>
                        </div>
                    </Transition>

                    <div class="flex gap-3">
                        <button @click="step = 'branch'" class="btn-secondary flex-1 transition-transform duration-200 active:scale-[0.98]">
                            Atrás
                        </button>
                        <button @click="executeCross" :disabled="loading || invoiceNumbers.length === 0"
                            class="btn-primary flex-1">
                            <span v-if="loading" class="flex items-center justify-center gap-2">
                                <span class="h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white"></span>
                                Procesando...
                            </span>
                            <span v-else>Ejecutar Canje</span>
                        </button>
                    </div>
                </div>
                <Transition name="alert">
                    <div v-if="crossError" class="mt-5 rounded-lg bg-red-50 border border-red-100 p-4 text-sm text-red-700">
                        {{ crossError }}
                    </div>
                </Transition>
            </div>

            <div v-else-if="showSuccess && crossResult" key="success">
                <div class="card mb-6 overflow-hidden animate-pop-in">
                    <div class="bg-gradient-to-r from-amber-500 to-amber-600 px-4 sm:px-6 py-4 sm:py-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-lg font-bold text-white">{{ hasAnyMatch ? '¡Canje exitoso!' : 'Canje procesado' }}</p>
                                <p v-if="totalTickets > 0" class="text-sm text-amber-100">{{ totalTickets }} boletas para el sorteo</p>
                            </div>
                            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-white/20 text-2xl text-white">
                                <span v-if="hasAnyMatch">✓</span>
                                <span v-else>⚠</span>
                            </div>
                        </div>
                    </div>

                    <div class="divide-y divide-slate-100">
                        <div v-for="(result, ri) in crossResult.results" :key="result.invoice_number"
                            class="px-4 sm:px-6 py-3 sm:py-4 animate-slide-up"
                            :class="'stagger-' + (ri + 1)">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg transition-all duration-300"
                                        :class="result.status === 'matched' ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-100 text-amber-600'">
                                        <span class="text-xs font-bold">{{ result.status === 'matched' ? '✓' : '!' }}</span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800">
                                            <template v-if="result.status === 'matched'">Factura: {{ result.invoice_number }}</template>
                                            <template v-else-if="result.status === 'duplicate_no_match'">Factura Canjeada No Participante</template>
                                            <template v-else-if="result.status === 'duplicate'">Factura Canjeada</template>
                                            <template v-else-if="result.status === 'without_matches'">Factura No Participa</template>
                                            <template v-else>Factura no Participante</template>
                                        </p>
                                        <p class="text-xs text-slate-500">{{ result.message }}</p>
                                    </div>
                                </div>
                                <div v-if="result.total_tickets > 0" class="flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 animate-pop-in">
                                    <span class="text-sm font-bold text-emerald-700">+{{ result.total_tickets }}</span>
                                    <span class="text-xs text-emerald-500">boletas</span>
                                </div>
                            </div>

                            <div v-if="result.details?.length" class="ml-0 sm:ml-10">
                                <div class="overflow-x-auto rounded-lg border border-slate-200">
                                    <table class="w-full text-sm min-w-[400px]">
                                        <thead>
                                            <tr class="bg-slate-50">
                                                <th class="px-3 py-2 text-left text-xs font-medium text-slate-500">Item</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-slate-500">Código</th>
                                                <th class="px-3 py-2 text-right text-xs font-medium text-slate-500">Cant.</th>
                                                <th class="px-3 py-2 text-right text-xs font-medium text-slate-500">B/Unid</th>
                                                <th class="px-3 py-2 text-right text-xs font-medium text-slate-500">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            <tr v-for="d in result.details" :key="d.item_code" class="transition-colors hover:bg-slate-50">
                                                <td class="px-3 py-2 text-slate-700">{{ d.item_name }}</td>
                                                <td class="px-3 py-2 font-mono text-xs text-slate-500">{{ d.item_code }}</td>
                                                <td class="px-3 py-2 text-right text-slate-700">{{ d.item_quantity }}</td>
                                                <td class="px-3 py-2 text-right text-slate-700">{{ d.tickets_per_unit }}</td>
                                                <td class="px-3 py-2 text-right font-semibold text-slate-800">{{ d.tickets_generated }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div v-if="result.tickets?.length" class="mt-3 flex flex-wrap gap-2">
                                    <button v-for="t in result.tickets" :key="t.id"
                                        @click="downloadTicket(t.id)"
                                        class="inline-flex items-center gap-1 rounded-lg border border-amber-200 bg-amber-50 px-2.5 py-1.5 text-xs font-medium text-amber-700 transition-all duration-200 hover:border-amber-400 hover:bg-amber-100 hover:shadow-sm active:scale-[0.97]">
                                        <span>⬇</span>
                                        Boleta #{{ t.ticketCode }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 px-4 sm:px-6 py-4">
                        <div class="flex flex-col sm:flex-row gap-3">
                            <button @click="reset" class="btn-primary w-full sm:w-auto">
                                Nueva Participación
                            </button>
                            <button v-if="allTickets.length > 0" @click="downloadAllTickets(crossResult.results)" class="btn-secondary w-full sm:w-auto">
                                Descargar todas las boletas
                            </button>
                        </div>
                    </div>
                </div>
                    </div>
                </Transition>
            </div>

            <div class="hidden lg:block w-full max-w-xs xl:max-w-sm animate-fade-in sticky top-8">
                <JerseysIllustration />
            </div>
        </div>

        <div class="mt-8 lg:hidden animate-fade-in flex justify-center">
            <div class="max-w-sm">
                <JerseysIllustration />
            </div>
        </div>
    </div>
</template>
