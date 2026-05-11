<template>
    <div class="min-h-screen flex">
        <!-- Colonne gauche - Formulaire -->
        <div class="flex-1 flex items-center justify-center bg-white p-8">
            <div class="w-full max-w-md">
                <!-- Logo -->
                <div class="text-center mb-8">
                    <div
                        class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-400 to-primary-600 shadow-lg mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-dark-900">Rapport Mensuel</h1>
                    <p class="text-sm text-dark-500 mt-1">Gérez vos plannings et congés</p>
                </div>

                <!-- Formulaire -->
                <form @submit.prevent="handleLogin" class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-dark-700 mb-1">Email</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-dark-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <input v-model="form.email" type="email" required autocomplete="email"
                                placeholder="admin@rhplaning.fr"
                                class="w-full pl-10 pr-4 py-2.5 border border-dark-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent transition" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-dark-700 mb-1">Mot de passe</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-dark-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input v-model="form.password" type="password" required autocomplete="current-password"
                                placeholder="••••••••"
                                class="w-full pl-10 pr-4 py-2.5 border border-dark-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent transition" />
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input v-model="rememberMe" type="checkbox"
                                class="rounded border-dark-300 text-primary-400 focus:ring-primary-400" />
                            <span class="text-sm text-dark-600">Se souvenir de moi</span>
                        </label>
                    </div>

                    <p v-if="error" class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2 flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ error }}
                    </p>

                    <button type="submit" :disabled="loading"
                        class="w-full bg-primary-400 hover:bg-primary-500 disabled:opacity-50 text-white font-semibold rounded-lg py-2.5 transition-all duration-200 shadow-sm hover:shadow">
                        <span v-if="loading" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4" />
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                            Connexion en cours...
                        </span>
                        <span v-else>Se connecter</span>
                    </button>
                </form>

                <!-- Demo credentials -->
                <div class="mt-8 p-4 bg-dark-50 rounded-lg">
                    <p class="text-xs text-dark-500 text-center mb-2">Comptes de démonstration</p>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="text-dark-600">
                            <span class="font-medium">Admin:</span><br />
                            admin@rhplaning.fr
                        </div>
                        <div class="text-dark-600">
                            <span class="font-medium">Manager:</span><br />
                            manager@rhplaning.fr
                        </div>
                        <div class="text-dark-600 col-span-2">
                            <span class="font-medium">Agent:</span><br />
                            agent@rhplaning.fr
                        </div>
                        <div class="text-dark-500 col-span-2 text-center text-xs mt-1">
                            Mot de passe: <span class="font-mono">password</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Colonne droite - Image seule -->
        <div class="hidden lg:flex flex-1 relative overflow-hidden">
            <img src="https://images.unsplash.com/photo-1486312338219-ce68d2c6f44d?w=800&h=1200&fit=crop"
                alt="Business meeting" class="absolute inset-0 w-full h-full object-cover" />
            <!-- Overlay légèrement obscur pour améliorer la lisibilité si besoin -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useApi } from '@/composables/useApi'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const { loading, error, execute } = useApi()

const form = reactive({ email: '', password: '' })
const rememberMe = ref(false)

async function handleLogin() {
    await execute(() => authStore.login(form))
    if (!error.value) {
        if (rememberMe.value) {
            localStorage.setItem('remember_email', form.email)
        }
        const redirect = (route.query.redirect as string) || '/dashboard'
        router.push(redirect)
    }
}

// Remplir automatiquement l'email sauvegardé
const savedEmail = localStorage.getItem('remember_email')
if (savedEmail) {
    form.email = savedEmail
    rememberMe.value = true
}
</script>
