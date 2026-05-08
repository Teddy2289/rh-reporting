<template>
  <AppLayout>
    <div class="p-8 space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-dark-900">Rapports</h1>
          <p class="text-sm text-dark-500 mt-1">Analysez les heures travaillées et les performances</p>
        </div>
        <button
          @click="exportData"
          class="inline-flex items-center gap-2 bg-secondary-500 hover:bg-secondary-600 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-all duration-200 shadow-sm hover:shadow"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
          </svg>
          Exporter les données
        </button>
      </div>

      <!-- Period Selector -->
      <div class="bg-white rounded-xl border border-dark-100 p-5">
        <div class="flex flex-wrap items-end gap-4">
          <div class="flex-1 min-w-[150px]">
            <label class="block text-xs font-medium text-dark-600 mb-1">Type de rapport</label>
            <select
              v-model="reportType"
              class="w-full px-3 py-2 border border-dark-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent transition"
            >
              <option value="dashboard">Tableau de bord</option>
              <option value="agent">Rapport par agent</option>
              <option value="team">Rapport d'équipe</option>
            </select>
          </div>

          <div v-if="reportType !== 'dashboard'" class="flex-1 min-w-[150px]">
            <label class="block text-xs font-medium text-dark-600 mb-1">Agent</label>
            <select
              v-model="selectedAgentId"
              class="w-full px-3 py-2 border border-dark-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent transition"
              :disabled="reportType === 'team'"
            >
              <option :value="null">Sélectionner un agent</option>
              <option v-for="agent in agents" :key="agent.id" :value="agent.id">
                {{ agent.full_name }}
              </option>
            </select>
          </div>

          <div v-if="reportType === 'team'" class="flex-1 min-w-[150px]">
            <label class="block text-xs font-medium text-dark-600 mb-1">Manager</label>
            <select
              v-model="selectedManagerId"
              class="w-full px-3 py-2 border border-dark-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent transition"
            >
              <option :value="null">Sélectionner un manager</option>
              <option v-for="manager in managers" :key="manager.id" :value="manager.id">
                {{ manager.full_name }}
              </option>
            </select>
          </div>

          <div class="w-40">
            <label class="block text-xs font-medium text-dark-600 mb-1">Année</label>
            <select
              v-model="selectedYear"
              class="w-full px-3 py-2 border border-dark-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent transition"
            >
              <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
            </select>
          </div>

          <div class="w-32">
            <label class="block text-xs font-medium text-dark-600 mb-1">Mois</label>
            <select
              v-model="selectedMonth"
              class="w-full px-3 py-2 border border-dark-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent transition"
            >
              <option :value="null">Annuel</option>
              <option v-for="(label, index) in months" :key="index" :value="index + 1">{{ label }}</option>
            </select>
          </div>

          <button
            @click="loadReport"
            :disabled="!canLoadReport"
            class="px-4 py-2 bg-primary-400 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Charger
          </button>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="reportsStore.loading" class="flex items-center justify-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-2 border-primary-400 border-t-transparent"></div>
      </div>

      <!-- Dashboard Stats -->
      <div v-else-if="reportType === 'dashboard' && reportsStore.dashboardStats" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <div class="bg-white rounded-xl border border-dark-100 p-5">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-dark-500 uppercase tracking-wider">Total agents</p>
                <p class="text-2xl font-bold text-dark-900 mt-1">{{ reportsStore.dashboardStats.total_agents }}</p>
              </div>
              <div class="w-10 h-10 rounded-lg bg-primary-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-xl border border-dark-100 p-5">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-dark-500 uppercase tracking-wider">Agents actifs</p>
                <p class="text-2xl font-bold text-green-600 mt-1">{{ reportsStore.dashboardStats.active_agents }}</p>
              </div>
              <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-xl border border-dark-100 p-5">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-dark-500 uppercase tracking-wider">Congés en attente</p>
                <p class="text-2xl font-bold text-amber-600 mt-1">{{ reportsStore.dashboardStats.pending_leaves }}</p>
              </div>
              <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-xl border border-dark-100 p-5">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-dark-500 uppercase tracking-wider">Heures totales</p>
                <p class="text-2xl font-bold text-secondary-600 mt-1">{{ reportsStore.dashboardStats.total_hours_month }}h</p>
              </div>
              <div class="w-10 h-10 rounded-lg bg-secondary-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-secondary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl border border-dark-100 p-6">
          <h3 class="font-semibold text-dark-900 mb-4">Performance mensuelle</h3>
          <div class="space-y-4">
            <div>
              <div class="flex justify-between text-sm text-dark-600 mb-1">
                <span>Heures travaillées</span>
                <span>{{ reportsStore.dashboardStats.total_hours_month }}h</span>
              </div>
              <div class="h-2 bg-dark-100 rounded-full overflow-hidden">
                <div class="h-full bg-primary-400 rounded-full" :style="{ width: '75%' }"></div>
              </div>
            </div>
            <div>
              <div class="flex justify-between text-sm text-dark-600 mb-1">
                <span>Heures supplémentaires</span>
                <span>{{ reportsStore.dashboardStats.overtime_hours_month }}h</span>
              </div>
              <div class="h-2 bg-dark-100 rounded-full overflow-hidden">
                <div class="h-full bg-secondary-400 rounded-full" :style="{ width: '30%' }"></div>
              </div>
            </div>
            <div>
              <div class="flex justify-between text-sm text-dark-600 mb-1">
                <span>Congés approuvés</span>
                <span>{{ reportsStore.dashboardStats.approved_leaves_month }} jours</span>
              </div>
              <div class="h-2 bg-dark-100 rounded-full overflow-hidden">
                <div class="h-full bg-green-400 rounded-full" :style="{ width: '45%' }"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Agent Report -->
      <div v-else-if="reportType === 'agent' && reportsStore.hourReport" class="space-y-6">
        <div class="bg-white rounded-xl border border-dark-100 overflow-hidden">
          <div class="p-6 border-b border-dark-100">
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 rounded-xl bg-primary-100 flex items-center justify-center">
                <span class="text-primary-600 font-semibold text-lg">
                  {{ getAgentInitials() }}
                </span>
              </div>
              <div>
                <h2 class="text-xl font-semibold text-dark-900">{{ getAgentName() }}</h2>
                <p class="text-sm text-dark-500">Rapport {{ selectedMonth ? 'mensuel' : 'annuel' }}</p>
              </div>
            </div>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
              <div class="text-center p-4 bg-primary-50 rounded-lg">
                <p class="text-2xl font-bold text-primary-600">{{ reportsStore.hourReport.worked_hours }}h</p>
                <p class="text-xs text-primary-600 mt-1">Heures travaillées</p>
              </div>
              <div class="text-center p-4 bg-dark-50 rounded-lg">
                <p class="text-2xl font-bold text-dark-600">{{ reportsStore.hourReport.expected_hours }}h</p>
                <p class="text-xs text-dark-600 mt-1">Heures attendues</p>
              </div>
              <div class="text-center p-4 bg-secondary-50 rounded-lg">
                <p class="text-2xl font-bold text-secondary-600">{{ reportsStore.hourReport.overtime_hours }}h</p>
                <p class="text-xs text-secondary-600 mt-1">Heures supplémentaires</p>
              </div>
            </div>

            <div v-if="reportsStore.hourReport.details?.length" class="mt-6">
              <h3 class="font-semibold text-dark-900 mb-3">Détail journalier</h3>
              <div class="overflow-x-auto">
                <table class="w-full text-sm">
                  <thead>
                    <tr class="bg-dark-50">
                      <th class="px-4 py-2 text-left">Date</th>
                      <th class="px-4 py-2 text-right">Travaillées</th>
                      <th class="px-4 py-2 text-right">Attendues</th>
                      <th class="px-4 py-2 text-right">Heures sup.</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-dark-100">
                    <tr v-for="detail in reportsStore.hourReport.details" :key="detail.date">
                      <td class="px-4 py-2">{{ formatDate(detail.date) }}</td>
                      <td class="px-4 py-2 text-right">{{ detail.worked_minutes / 60 }}h</td>
                      <td class="px-4 py-2 text-right">{{ detail.expected_minutes / 60 }}h</td>
                      <td class="px-4 py-2 text-right text-secondary-600">{{ detail.overtime_minutes / 60 }}h</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Team Report -->
      <div v-else-if="reportType === 'team' && reportsStore.teamReports.length" class="space-y-4">
        <div class="bg-white rounded-xl border border-dark-100 overflow-hidden">
          <div class="p-6 border-b border-dark-100">
            <h2 class="text-xl font-semibold text-dark-900">Rapport d'équipe</h2>
            <p class="text-sm text-dark-500 mt-1">Performance de l'équipe pour {{ monthName }} {{ selectedYear }}</p>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="bg-dark-50">
                  <th class="px-6 py-3 text-left">Agent</th>
                  <th class="px-6 py-3 text-right">Travaillées</th>
                  <th class="px-6 py-3 text-right">Attendues</th>
                  <th class="px-6 py-3 text-right">Heures sup.</th>
                  <th class="px-6 py-3 text-center">Performance</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-dark-100">
                <tr v-for="report in reportsStore.teamReports" :key="report.agent_id">
                  <td class="px-6 py-3 font-medium">{{ report.agent_name }}</td>
                  <td class="px-6 py-3 text-right">{{ report.worked_hours }}h</td>
                  <td class="px-6 py-3 text-right">{{ report.expected_hours }}h</td>
                  <td class="px-6 py-3 text-right text-secondary-600">{{ report.overtime_hours }}h</td>
                  <td class="px-6 py-3 text-center">
                    <div class="flex items-center gap-2">
                      <div class="flex-1 h-1.5 bg-dark-100 rounded-full overflow-hidden">
                        <div
                          class="h-full bg-primary-400 rounded-full"
                          :style="{ width: `${(report.worked_hours / report.expected_hours) * 100}%` }"
                        ></div>
                      </div>
                      <span class="text-xs text-dark-500">{{ Math.round((report.worked_hours / report.expected_hours) * 100) }}%</span>
                    </div>
                  </td>
                </tr>
              </tbody>
              <tfoot class="bg-dark-50 font-semibold">
                <tr>
                  <td class="px-6 py-3">Total équipe</td>
                  <td class="px-6 py-3 text-right">{{ totalTeamHours }}h</td>
                  <td class="px-6 py-3 text-right">{{ totalTeamExpectedHours }}h</td>
                  <td class="px-6 py-3 text-right text-secondary-600">{{ reportsStore.totalTeamOvertime }}h</td>
                  <td class="px-6 py-3 text-center">
                    <span class="text-primary-600">{{ Math.round((totalTeamHours / totalTeamExpectedHours) * 100) }}%</span>
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="!reportsStore.loading" class="text-center py-12 bg-white rounded-xl border border-dark-100">
        <svg class="w-12 h-12 mx-auto text-dark-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
        <p class="text-dark-500">Sélectionnez un rapport et cliquez sur "Charger"</p>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import AppLayout from '@/components/layout/AppLayout.vue'
import { useReportsStore } from '@/stores/reports'
import { useAgentsStore } from '@/stores/agents'
import { useAuthStore } from '@/stores/auth'
import { useApi } from '@/composables/useApi'
import { formatDate } from '@/utils'
import type { Agent } from '@/types'

const reportsStore = useReportsStore()
const agentsStore = useAgentsStore()
const authStore = useAuthStore()
const { execute } = useApi()

const reportType = ref<'dashboard' | 'agent' | 'team'>('dashboard')
const selectedAgentId = ref<number | null>(null)
const selectedManagerId = ref<number | null>(null)
const selectedYear = ref(new Date().getFullYear())
const selectedMonth = ref<number | null>(new Date().getMonth() + 1)

const agents = ref<Agent[]>([])
const managers = ref<Agent[]>([])

const years = Array.from({ length: 5 }, (_, i) => selectedYear.value - 2 + i)
const months = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre']

const monthName = computed(() => {
  if (!selectedMonth.value) return ''
  return months[selectedMonth.value - 1]
})

const canLoadReport = computed(() => {
  if (reportType.value === 'dashboard') return true
  if (reportType.value === 'agent') return !!selectedAgentId.value
  if (reportType.value === 'team') return !!selectedManagerId.value
  return false
})

const totalTeamExpectedHours = computed(() => {
  return reportsStore.teamReports.reduce((sum, report) => sum + (report.expected_hours || 0), 0)
})

const totalTeamHours = computed(() => {
  return reportsStore.teamReports.reduce((sum, report) => sum + (report.worked_hours || 0), 0)
})

function getAgentInitials(): string {
  const agent = agents.value.find(a => a.id === selectedAgentId.value)
  if (!agent) return '??'
  return `${agent.first_name?.[0]}${agent.last_name?.[0]}`.toUpperCase()
}

function getAgentName(): string {
  const agent = agents.value.find(a => a.id === selectedAgentId.value)
  return agent?.full_name || 'Agent'
}

async function loadReport() {
  if (!canLoadReport.value) return

  await execute(async () => {
    if (reportType.value === 'dashboard') {
      await reportsStore.fetchDashboardStats(selectedYear.value, selectedMonth.value || 1)
    } else if (reportType.value === 'agent' && selectedAgentId.value) {
      const params: any = {
        agent_id: selectedAgentId.value,
        year: selectedYear.value
      }
      if (selectedMonth.value) {
        params.month = selectedMonth.value
      }
      await reportsStore.fetchHourReport(params)
    } else if (reportType.value === 'team' && selectedManagerId.value && selectedMonth.value) {
      await reportsStore.fetchTeamReport(selectedManagerId.value, selectedYear.value, selectedMonth.value)
    }
  })
}

async function exportData() {
  const params: any = { year: selectedYear.value }
  if (selectedMonth.value) {
    params.month = selectedMonth.value
    params.format = 'json'
  }

  await reportsStore.exportReport(params)

  // Créer un fichier JSON à télécharger
  const dataStr = JSON.stringify(reportsStore.exportData, null, 2)
  const blob = new Blob([dataStr], { type: 'application/json' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = `rapport_${selectedYear.value}${selectedMonth.value ? `_${selectedMonth.value}` : ''}.json`
  link.click()
  URL.revokeObjectURL(url)
}

async function loadAgents() {
  await agentsStore.fetchAgents({ active_only: true, per_page: 200 })
  agents.value = agentsStore.agents
  managers.value = agentsStore.agents.filter(a => a.manager_id === null || a.manager_id === undefined)

  // Si l'utilisateur est manager, sélectionner son équipe par défaut
  if (authStore.hasRole('manager') && !authStore.hasAnyRole('admin', 'rh')) {
    const userAgent = agents.value.find(a => a.user_id === authStore.user?.id)
    if (userAgent) {
      reportType.value = 'team'
      selectedManagerId.value = userAgent.id
    }
  }
}

onMounted(() => {
  loadAgents()
})
</script>
