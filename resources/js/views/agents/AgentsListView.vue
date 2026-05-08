<template>
  <AppLayout>
    <div class="p-8 space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-dark-900">Agents</h1>
          <p class="text-sm text-dark-500 mt-1">Gérez l'ensemble des agents et leurs informations</p>
        </div>
        <RouterLink
          v-if="authStore.hasAnyRole('admin', 'rh')"
          :to="{ name: 'agents.create' }"
          class="inline-flex items-center gap-2 bg-primary-400 hover:bg-primary-500 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-all duration-200 shadow-sm hover:shadow"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Nouvel agent
        </RouterLink>
      </div>

      <!-- Filters -->
      <div class="flex flex-wrap items-center gap-3 p-4 bg-white rounded-xl border border-dark-100">
        <div class="relative flex-1 min-w-[200px]">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            v-model="filters.search"
            type="search"
            placeholder="Rechercher un agent..."
            class="w-full pl-9 pr-3 py-2 border border-dark-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent transition"
            @input="debouncedFetch"
          />
        </div>

        <select
          v-model="filters.department_id"
          class="px-3 py-2 border border-dark-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent transition bg-white"
          @change="fetchAgents"
        >
          <option :value="undefined">Tous les départements</option>
          <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
        </select>

        <label class="flex items-center gap-2 px-3 py-2 text-sm text-dark-700 bg-dark-50 rounded-lg cursor-pointer hover:bg-dark-100 transition">
          <input v-model="filters.active_only" type="checkbox" class="rounded border-dark-300 text-primary-400 focus:ring-primary-400" @change="fetchAgents" />
          <span>Actifs uniquement</span>
        </label>
      </div>

      <!-- Table -->
      <div v-if="loading" class="flex items-center justify-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-2 border-primary-400 border-t-transparent"></div>
      </div>

      <div v-else-if="agentsStore.agents.length === 0" class="text-center py-12 bg-white rounded-xl border border-dark-100">
        <svg class="w-12 h-12 mx-auto text-dark-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <p class="text-dark-500">Aucun agent trouvé</p>
      </div>

      <div v-else class="bg-white rounded-xl border border-dark-100 overflow-hidden">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-dark-50 border-b border-dark-100">
              <th class="px-6 py-4 text-left text-xs font-semibold text-dark-600 uppercase tracking-wider">Agent</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-dark-600 uppercase tracking-wider">Département</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-dark-600 uppercase tracking-wider">Contrat</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-dark-600 uppercase tracking-wider">Statut</th>
              <th class="px-6 py-4 text-right text-xs font-semibold text-dark-600 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-dark-100">
            <tr
              v-for="agent in agentsStore.agents"
              :key="agent.id"
              class="hover:bg-dark-50/50 transition-colors duration-150 group"
            >
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center">
                    <span class="text-primary-600 text-xs font-medium">
                      {{ agent.first_name?.charAt(0) }}{{ agent.last_name?.charAt(0) }}
                    </span>
                  </div>
                  <div>
                    <div class="font-medium text-dark-900">{{ agent.full_name }}</div>
                    <div class="text-xs text-dark-500">{{ agent.employee_code }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <span class="text-dark-700">{{ agent.department?.name ?? '—' }}</span>
              </td>
              <td class="px-6 py-4">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-secondary-50 text-secondary-700">
                  {{ getContractTypeLabel(agent.contract_type) }}
                </span>
              </td>
              <td class="px-6 py-4">
                <span
                  :class="agent.is_active
                    ? 'bg-green-50 text-green-700'
                    : 'bg-dark-100 text-dark-600'"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                >
                  <span class="w-1.5 h-1.5 rounded-full mr-1.5" :class="agent.is_active ? 'bg-green-500' : 'bg-dark-400'"></span>
                  {{ agent.is_active ? 'Actif' : 'Inactif' }}
                </span>
              </td>
              <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <RouterLink
                    :to="{ name: 'agents.show', params: { id: agent.id } }"
                    class="text-dark-500 hover:text-primary-500 transition p-1"
                    title="Voir"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </RouterLink>
                  <RouterLink
                    v-if="authStore.hasAnyRole('admin', 'rh')"
                    :to="{ name: 'agents.edit', params: { id: agent.id } }"
                    class="text-dark-500 hover:text-primary-500 transition p-1"
                    title="Modifier"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </RouterLink>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="agentsStore.pagination.total > 0" class="flex items-center justify-between">
        <div class="text-sm text-dark-500">
          Affichage de <span class="font-medium text-dark-700">{{ agentsStore.pagination.from }}</span> à
          <span class="font-medium text-dark-700">{{ agentsStore.pagination.to }}</span> sur
          <span class="font-medium text-dark-700">{{ agentsStore.pagination.total }}</span> agents
        </div>

        <div class="flex items-center gap-2">
          <button
            :disabled="currentPage === 1"
            class="p-2 border border-dark-200 rounded-lg text-dark-500 hover:bg-dark-50 hover:text-dark-700 disabled:opacity-40 disabled:cursor-not-allowed transition"
            @click="goToPage(currentPage - 1)"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>

          <div class="flex items-center gap-1">
            <button
              v-for="page in visiblePages"
              :key="page"
              @click="goToPage(Number(page))"
              :class="[
                'min-w-[36px] h-9 px-3 rounded-lg text-sm font-medium transition',
                currentPage === page
                  ? 'bg-primary-400 text-white shadow-sm'
                  : 'text-dark-600 hover:bg-dark-100'
              ]"
            >
              {{ page }}
            </button>
          </div>

          <button
            :disabled="currentPage >= agentsStore.pagination.lastPage"
            class="p-2 border border-dark-200 rounded-lg text-dark-500 hover:bg-dark-50 hover:text-dark-700 disabled:opacity-40 disabled:cursor-not-allowed transition"
            @click="goToPage(currentPage + 1)"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { reactive, onMounted, ref, computed } from 'vue'
import { RouterLink } from 'vue-router'
import AppLayout from '@/components/layout/AppLayout.vue'
import { useAgentsStore } from '@/stores/agents'
import { useAuthStore } from '@/stores/auth'
import { departmentsApi } from '@/api'
import { useApi } from '@/composables/useApi'
import { CONTRACT_TYPE_LABELS } from '@/utils'
import type { Department, AgentFilters } from '@/types'

const agentsStore = useAgentsStore()
const authStore = useAuthStore()
const { loading, execute } = useApi()
const departments = ref<Department[]>([])
const currentPage = ref(1)

const filters = reactive<AgentFilters>({
  active_only: true,
  search: '',
  department_id: undefined,
  page: 1,
  per_page: 15
})

// Pagination visible pages
const visiblePages = computed(() => {
  const total = agentsStore.pagination.lastPage
  const current = currentPage.value
  const delta = 2
  const range: number[] = []
  const rangeWithDots: (number | string)[] = []
  let l: number | undefined

  for (let i = 1; i <= total; i++) {
    if (i === 1 || i === total || (i >= current - delta && i <= current + delta)) {
      range.push(i)
    }
  }

  range.forEach((i) => {
    if (l) {
      if (i - l === 2) {
        rangeWithDots.push(l + 1)
      } else if (i - l !== 1) {
        rangeWithDots.push('...')
      }
    }
    rangeWithDots.push(i)
    l = i
  })

  return rangeWithDots
})

function getContractTypeLabel(contractType: any): string {
  if (!contractType) return '—'
  if (typeof contractType === 'object' && contractType.label) return contractType.label
  if (typeof contractType === 'string') return CONTRACT_TYPE_LABELS[contractType as keyof typeof CONTRACT_TYPE_LABELS] || contractType
  return '—'
}

let debounceTimer: ReturnType<typeof setTimeout>
function debouncedFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    currentPage.value = 1
    filters.page = 1
    fetchAgents()
  }, 300)
}

async function fetchAgents() {
  filters.page = currentPage.value
  await execute(async () => {
    await agentsStore.fetchAgents(filters)
  })
}

async function goToPage(page: number) {
  if (page === currentPage.value) return
  currentPage.value = page
  filters.page = page
  await fetchAgents()
}

onMounted(async () => {
  await fetchAgents()
  try {
    const res = await departmentsApi.index()
    departments.value = res.data.data
  } catch (error) {
    console.error('Erreur chargement départements:', error)
    departments.value = []
  }
})
</script>
