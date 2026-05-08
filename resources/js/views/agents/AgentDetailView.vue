<template>
  <AppLayout>
    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-2 border-primary-400 border-t-transparent"></div>
    </div>

    <div v-else-if="agent" class="p-8 space-y-8">
      <!-- Header -->
      <div class="flex items-start justify-between">
        <div class="flex items-center gap-5">
          <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-2xl font-semibold shadow-sm">
            {{ getInitials(agent.full_name) }}
          </div>
          <div>
            <h1 class="text-2xl font-semibold text-dark-900">{{ agent.full_name }}</h1>
            <div class="flex items-center gap-2 mt-1">
              <span class="text-sm text-dark-500">{{ agent.employee_code }}</span>
              <span class="w-1 h-1 rounded-full bg-dark-300"></span>
              <span class="text-sm text-dark-500">{{ agent.department?.name || '—' }}</span>
            </div>
            <div class="mt-2">
              <span
                :class="agent.is_active
                  ? 'bg-green-50 text-green-700'
                  : 'bg-dark-100 text-dark-600'"
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
              >
                <span class="w-1.5 h-1.5 rounded-full mr-1.5" :class="agent.is_active ? 'bg-green-500' : 'bg-dark-400'"></span>
                {{ agent.is_active ? 'Actif' : 'Inactif' }}
              </span>
            </div>
          </div>
        </div>

        <RouterLink
          v-if="authStore.hasAnyRole('admin', 'rh')"
          :to="{ name: 'agents.edit', params: { id: agent.id } }"
        >
          <AppButton variant="secondary" size="sm" class="gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Modifier
          </AppButton>
        </RouterLink>
      </div>

      <!-- Info cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-dark-100 p-4 hover:shadow-sm transition">
          <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-medium text-dark-500 uppercase tracking-wider">Email</p>
            <svg class="w-4 h-4 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
          </div>
          <p class="text-sm font-medium text-dark-900 truncate">{{ agent.user?.email ?? '—' }}</p>
        </div>

        <div class="bg-white rounded-xl border border-dark-100 p-4 hover:shadow-sm transition">
          <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-medium text-dark-500 uppercase tracking-wider">Téléphone</p>
            <svg class="w-4 h-4 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
            </svg>
          </div>
          <p class="text-sm font-medium text-dark-900">{{ agent.phone ?? '—' }}</p>
        </div>

        <div class="bg-white rounded-xl border border-dark-100 p-4 hover:shadow-sm transition">
          <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-medium text-dark-500 uppercase tracking-wider">Contrat</p>
            <svg class="w-4 h-4 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <p class="text-sm font-medium text-dark-900">{{ getContractTypeLabel(agent.contract_type) }}</p>
        </div>

        <div class="bg-white rounded-xl border border-dark-100 p-4 hover:shadow-sm transition">
          <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-medium text-dark-500 uppercase tracking-wider">Manager</p>
            <svg class="w-4 h-4 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
          </div>
          <p class="text-sm font-medium text-dark-900">{{ agent.manager?.full_name ?? 'Aucun' }}</p>
        </div>
      </div>

      <!-- Second row of info cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-dark-100 p-4">
          <p class="text-xs font-medium text-dark-500 uppercase tracking-wider mb-2">Date d'embauche</p>
          <div class="flex items-center gap-2">
            <span class="text-sm font-medium text-dark-900">{{ formatDate(agent.hire_date) }}</span>
            <span class="text-xs text-dark-400">{{ getYearsSince(agent.hire_date) }}</span>
          </div>
        </div>

        <div v-if="agent.contract_end_date" class="bg-white rounded-xl border border-dark-100 p-4">
          <p class="text-xs font-medium text-dark-500 uppercase tracking-wider mb-2">Fin de contrat</p>
          <p class="text-sm font-medium text-dark-900">{{ formatDate(agent.contract_end_date) }}</p>
        </div>

        <div class="bg-white rounded-xl border border-dark-100 p-4">
          <p class="text-xs font-medium text-dark-500 uppercase tracking-wider mb-2">Heures / semaine</p>
          <p class="text-sm font-medium text-dark-900">{{ agent.weekly_hours }}h</p>
        </div>

        <div class="bg-white rounded-xl border border-dark-100 p-4">
          <p class="text-xs font-medium text-dark-500 uppercase tracking-wider mb-2">Congés annuels</p>
          <p class="text-sm font-medium text-dark-900">{{ agent.annual_leave_days }} jours</p>
        </div>
      </div>

      <!-- Solde congés -->
      <div class="bg-white rounded-xl border border-dark-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-dark-100">
          <h2 class="font-semibold text-dark-900">Solde de congés {{ currentYear }}</h2>
          <p class="text-xs text-dark-500 mt-0.5">Récapitulatif des droits et utilisations</p>
        </div>

        <div v-if="balance" class="p-6">
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="text-center">
              <div class="w-full bg-primary-50 rounded-xl p-4">
                <p class="text-2xl font-bold text-primary-600">{{ balance.allocated_days }}</p>
                <p class="text-xs text-primary-600 mt-1">Alloués</p>
              </div>
            </div>
            <div class="text-center">
              <div class="w-full bg-secondary-50 rounded-xl p-4">
                <p class="text-2xl font-bold text-secondary-600">{{ balance.remaining_days }}</p>
                <p class="text-xs text-secondary-600 mt-1">Restants</p>
              </div>
            </div>
            <div class="text-center">
              <div class="w-full bg-amber-50 rounded-xl p-4">
                <p class="text-2xl font-bold text-amber-600">{{ balance.pending_days }}</p>
                <p class="text-xs text-amber-600 mt-1">En attente</p>
              </div>
            </div>
            <div class="text-center">
              <div class="w-full bg-dark-50 rounded-xl p-4">
                <p class="text-2xl font-bold text-dark-600">{{ balance.used_days }}</p>
                <p class="text-xs text-dark-600 mt-1">Utilisés</p>
              </div>
            </div>
          </div>

          <!-- Progress bar -->
          <div class="mt-6">
            <div class="flex justify-between text-xs text-dark-500 mb-1">
              <span>Utilisation</span>
              <span>{{ balance.used_days }} / {{ balance.allocated_days }} jours</span>
            </div>
            <div class="h-2 bg-dark-100 rounded-full overflow-hidden">
              <div
                class="h-full bg-primary-400 rounded-full transition-all duration-500"
                :style="{ width: `${(balance.used_days / balance.allocated_days) * 100}%` }"
              ></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import AppLayout from '@/components/layout/AppLayout.vue'
import AppButton from '@/components/common/AppButton.vue'
import { useAgentsStore } from '@/stores/agents'
import { useLeavesStore } from '@/stores/leaves'
import { useAuthStore } from '@/stores/auth'
import { useApi } from '@/composables/useApi'
import { CONTRACT_TYPE_LABELS, formatDate } from '@/utils'
import type { Agent, LeaveBalance } from '@/types'

const route = useRoute()
const agentsStore = useAgentsStore()
const leavesStore = useLeavesStore()
const authStore = useAuthStore()
const { loading, execute } = useApi()

const agent = ref<Agent | null>(null)
const balance = ref<LeaveBalance | null>(null)
const currentYear = new Date().getFullYear()

function getInitials(fullName: string): string {
  if (!fullName) return ''
  const parts = fullName.split(' ')
  if (parts.length >= 2) {
    return `${parts[0][0]}${parts[1][0]}`.toUpperCase()
  }
  return fullName.substring(0, 2).toUpperCase()
}

function getContractTypeLabel(contractType: any): string {
  if (!contractType) return '—'
  if (typeof contractType === 'object' && contractType.label) return contractType.label
  if (typeof contractType === 'string') return CONTRACT_TYPE_LABELS[contractType as keyof typeof CONTRACT_TYPE_LABELS] || contractType
  return '—'
}

function getYearsSince(date: string): string {
  const hireDate = new Date(date)
  const today = new Date()
  let years = today.getFullYear() - hireDate.getFullYear()
  const monthDiff = today.getMonth() - hireDate.getMonth()
  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < hireDate.getDate())) {
    years--
  }
  if (years === 0) return '(moins d\'un an)'
  return `(${years} ${years > 1 ? 'ans' : 'an'})`
}

onMounted(async () => {
  const id = Number(route.params.id)
  await execute(async () => {
    agent.value = await agentsStore.fetchAgent(id)
    balance.value = await leavesStore.fetchBalance(id, currentYear)
  })
})
</script>
