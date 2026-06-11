<script setup>
import { useForm, Head } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

function submit() {
    form.post('/login', {
        onError: () => form.reset('password'),
    });
}
</script>

<template>
    <Head title="Iniciar Sesión" />

    <div class="flex min-h-screen items-center justify-center bg-surface px-4">
        <div class="w-full max-w-sm">
            <div class="mb-8 text-center">
                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500 text-xl font-bold text-white">
                    XC
                </div>
                <h1 class="text-xl font-bold text-slate-800">Invoice Crossing</h1>
                <p class="mt-1 text-sm text-slate-500">Panel de gestión</p>
            </div>

            <div class="card p-6">
                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Correo electrónico</label>
                        <input v-model="form.email" type="email" required autofocus placeholder="admin@example.com"
                            class="input-field" :class="{ 'border-red-300': form.errors.email }" />
                        <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Contraseña</label>
                        <input v-model="form.password" type="password" required placeholder="••••••••"
                            class="input-field" :class="{ 'border-red-300': form.errors.password }" />
                    </div>

                    <label class="flex cursor-pointer items-center gap-2">
                        <input v-model="form.remember" type="checkbox"
                            class="h-4 w-4 rounded border-slate-300 text-amber-500 focus:ring-amber-400/20 focus:ring-2" />
                        <span class="text-sm text-slate-600">Recordarme</span>
                    </label>

                    <button type="submit" class="btn-primary w-full" :disabled="form.processing">
                        <span v-if="form.processing" class="flex items-center justify-center gap-2">
                            <span class="h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white"></span>
                            Ingresando...
                        </span>
                        <span v-else>Ingresar</span>
                    </button>
                </form>
            </div>

            <p class="mt-6 text-center text-xs text-slate-400">
                &copy; {{ new Date().getFullYear() }} Invoice Crossing
            </p>
        </div>
    </div>
</template>
