<template>
    <div class="min-h-screen flex bg-dark-50">
          <AppToast />
        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r border-dark-100 flex flex-col">
            <div class="h-16 flex items-center px-6 border-b border-dark-100">
                <div class="flex items-center gap-2">
                    <div
                        class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <span class="text-lg font-semibold text-dark-800">RH Planning</span>
                </div>
            </div>

            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <NavItem :to="{ name: 'dashboard' }" icon="dashboard">Tableau de bord</NavItem>
                <NavItem :to="{ name: 'planning' }" icon="planning">Planning</NavItem>
                <NavItem :to="{ name: 'leaves' }" icon="leaves">Congés</NavItem>

                <template v-if="authStore.hasAnyRole('admin', 'rh', 'manager')">
                    <NavItem :to="{ name: 'agents' }" icon="agents">Agents</NavItem>
                </template>

                <template v-if="authStore.hasAnyRole('admin', 'rh')">
                    <NavItem :to="{ name: 'departments' }" icon="departments">Départements</NavItem>
                    <NavItem :to="{ name: 'clients' }" icon="clients">Clients</NavItem>
                    <NavItem :to="{ name: 'missions' }" icon="missions">Missions</NavItem>
                </template>

                <template v-if="authStore.hasAnyRole('admin', 'rh', 'manager')">
                    <NavItem :to="{ name: 'reports' }" icon="reports">Rapports</NavItem>
                </template>
            </nav>

            <!-- User info -->
            <div class="border-t border-dark-100 p-4">
                <div class="flex items-center gap-3">
                    <div
                        class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white font-semibold text-sm shadow-sm">
                        {{ userInitials }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-dark-900 truncate">{{ authStore.user?.name }}</p>
                        <p class="text-xs text-dark-500 truncate capitalize">{{
                            getUserRoleLabel(authStore.user?.roles?.[0]) }}</p>
                    </div>
                    <button title="Déconnexion"
                        class="text-dark-400 hover:text-primary-500 transition-colors p-1 rounded-lg hover:bg-dark-50"
                        @click="handleLogout">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main content -->
        <main class="flex-1 min-w-0 overflow-y-auto bg-dark-50">

            <slot />
        </main>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import NavItem from './NavItem.vue'
import AppToast from '../common/AppToast.vue'

const authStore = useAuthStore()
const router = useRouter()

const userInitials = computed(() => {
    const name = authStore.user?.name ?? ''
    return name.split(' ').map((n) => n[0]).slice(0, 2).join('').toUpperCase()
})

function getUserRoleLabel(role: string | undefined): string {
    if (!role) return ''
    const labels: Record<string, string> = {
        admin: 'Administrateur',
        rh: 'Ressources Humaines',
        manager: 'Manager',
        agent: 'Agent'
    }
    return labels[role] || role
}

async function handleLogout() {
    await authStore.logout()
    router.push({ name: 'login' })
}
</script>
