<template>
  <AppLayout>
    <div class="p-8 space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-dark-900">Planning</h1>
          <p class="text-sm text-dark-500 mt-1">Gérez les plannings des agents</p>
        </div>
        <div class="flex gap-3">
          <RouterLink
            v-if="authStore.hasAnyRole('admin', 'rh')"
            :to="{ name: 'planning.generate' }"
            class="inline-flex items-center gap-2 bg-secondary-500 hover:bg-secondary-600 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-all duration-200 shadow-sm hover:shadow"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Générer le planning
          </RouterLink>
          <button
            @click="openCreateModal"
            class="inline-flex items-center gap-2 bg-primary-400 hover:bg-primary-500 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-all duration-200 shadow-sm hover:shadow"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Ajouter un créneau
          </button>
        </div>
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
          v-model="selectedAgentId"
          class="px-3 py-2 border border-dark-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent transition bg-white min-w-[180px]"
          @change="fetchSlots"
        >
          <option :value="null">Tous les agents</option>
          <option v-for="agent in agents" :key="agent.id" :value="agent.id">
            {{ agent.full_name }}
          </option>
        </select>

        <input
          v-model="currentDate"
          type="month"
          class="px-3 py-2 border border-dark-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent transition"
          @change="fetchSlots"
        />
      </div>

      <!-- Loading -->
      <div v-if="planningStore.loading" class="flex items-center justify-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-2 border-primary-400 border-t-transparent"></div>
      </div>

      <!-- Calendar View -->
      <div v-else class="bg-white rounded-xl border border-dark-100 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="bg-dark-50 border-b border-dark-100">
                <th class="w-40 px-4 py-3 text-left text-xs font-semibold text-dark-600 uppercase tracking-wider">Agent</th>
                <th v-for="day in weekDays" :key="day.date" class="px-3 py-3 text-center min-w-[100px]">
                  <div class="text-xs font-semibold text-dark-600">{{ day.name }}</div>
                  <div class="text-lg font-semibold text-dark-800">{{ day.day }}</div>
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-dark-100">
              <tr v-for="agent in filteredAgents" :key="agent.id" class="hover:bg-dark-50/50 transition">
                <td class="px-4 py-3 sticky left-0 bg-white z-10">
                  <div class="font-medium text-dark-900">{{ agent.full_name }}</div>
                  <div class="text-xs text-dark-500">{{ agent.department?.name }}</div>
                </td>
                <td v-for="day in weekDays" :key="day.date" class="px-2 py-2 align-top">
                  <div class="space-y-1">
                    <template v-for="slot in getSlotsForAgentAndDay(agent.id, day.date)" :key="slot.id">
                      <div
                        @click="openEditModal(slot)"
                        :class="getSlotClass(slot.type)"
                        class="p-1.5 rounded-lg cursor-pointer hover:shadow-sm transition group"
                      >
                        <div class="text-xs font-medium truncate">
                          {{ slot.time_start }} - {{ slot.time_end }}
                        </div>
                        <div class="text-xs truncate opacity-75">{{ slot.mission?.name || slot.client?.name || slot.note || '—' }}</div>
                        <div class="flex justify-end mt-1 opacity-0 group-hover:opacity-100 transition">
                          <span class="text-[10px] text-dark-400">Modifier</span>
                        </div>
                      </div>
                    </template>
                    <button
                      @click="openCreateModalForDay(agent.id, day.date)"
                      class="w-full p-1.5 rounded-lg border border-dashed border-dark-200 text-dark-400 hover:border-primary-400 hover:text-primary-500 transition text-xs flex items-center justify-center gap-1"
                    >
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                      </svg>
                      Ajouter
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Stats footer -->
      <div v-if="!planningStore.loading && planningStore.slots.length > 0" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-dark-100 p-4">
          <p class="text-xs text-dark-500 uppercase tracking-wider">Total créneaux</p>
          <p class="text-2xl font-semibold text-dark-900 mt-1">{{ planningStore.slots.length }}</p>
        </div>
        <div class="bg-white rounded-xl border border-dark-100 p-4">
          <p class="text-xs text-dark-500 uppercase tracking-wider">Heures travaillées</p>
          <p class="text-2xl font-semibold text-primary-600 mt-1">{{ totalWorkedHours }}h</p>
        </div>
        <div class="bg-white rounded-xl border border-dark-100 p-4">
          <p class="text-xs text-dark-500 uppercase tracking-wider">Congés</p>
          <p class="text-2xl font-semibold text-secondary-600 mt-1">{{ totalLeaveSlots }}</p>
        </div>
        <div class="bg-white rounded-xl border border-dark-100 p-4">
          <p class="text-xs text-dark-500 uppercase tracking-wider">Agents concernés</p>
          <p class="text-2xl font-semibold text-dark-900 mt-1">{{ uniqueAgentsCount }}</p>
        </div>
      </div>
    </div>

    <!-- Modal Création/Édition -->
    <div v-if="modalOpen" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="closeModal">
      <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
        <div class="flex items-center justify-between p-5 border-b border-dark-100">
          <h2 class="text-lg font-semibold text-dark-900">{{ editingSlot ? 'Modifier le créneau' : 'Ajouter un créneau' }}</h2>
          <button @click="closeModal" class="text-dark-400 hover:text-dark-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <form @submit.prevent="saveSlot" class="p-5 space-y-4">
          <AppSelect v-model="slotForm.agent_id" label="Agent" required :disabled="!!editingSlot">
            <option v-for="agent in agents" :key="agent.id" :value="agent.id">{{ agent.full_name }}</option>
          </AppSelect>

          <AppInput v-model="slotForm.date" label="Date" type="date" required />

          <div class="grid grid-cols-2 gap-3">
            <AppInput v-model="slotForm.time_start" label="Heure début" type="time" required />
            <AppInput v-model="slotForm.time_end" label="Heure fin" type="time" required />
          </div>

          <AppSelect v-model="slotForm.type" label="Type" required>
            <option value="work">Travail</option>
            <option value="leave">Congé</option>
            <option value="training">Formation</option>
            <option value="other">Autre</option>
          </AppSelect>

          <AppSelect v-model="slotForm.client_id" label="Client" :clearable="true">
            <option :value="null">Aucun</option>
            <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.name }}</option>
          </AppSelect>

          <AppSelect v-model="slotForm.mission_id" label="Mission" :clearable="true">
            <option :value="null">Aucune</option>
            <option v-for="mission in filteredMissions" :key="mission.id" :value="mission.id">{{ mission.name }}</option>
          </AppSelect>

          <AppInput v-model="slotForm.note" label="Note" type="textarea" />

          <div class="flex justify-end gap-3 pt-3">
            <AppButton type="button" variant="secondary" @click="closeModal">Annuler</AppButton>
            <AppButton type="submit" :loading="saving">Enregistrer</AppButton>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { RouterLink } from 'vue-router'
import AppLayout from '@/components/layout/AppLayout.vue'
import AppInput from '@/components/common/AppInput.vue'
import AppSelect from '@/components/common/AppSelect.vue'
import AppButton from '@/components/common/AppButton.vue'
import { usePlanningStore } from '@/stores/planning'
import { useAgentsStore } from '@/stores/agents'
import { useAuthStore } from '@/stores/auth'
import { clientsApi, missionsApi } from '@/api'
import { useApi } from '@/composables/useApi'
import type { Agent, Client, Mission, PlanningSlotForm } from '@/types'

const planningStore = usePlanningStore()
const agentsStore = useAgentsStore()
const authStore = useAuthStore()
const { execute } = useApi()

const agents = ref<Agent[]>([])
const clients = ref<Client[]>([])
const missions = ref<Mission[]>([])
const selectedAgentId = ref<number | null>(null)
const currentDate = ref(new Date().toISOString().slice(0, 7))
const modalOpen = ref(false)
const editingSlot = ref<any>(null)
const saving = ref(false)

const filters = ref({ search: '' })

const slotForm = ref<PlanningSlotForm>({
  agent_id: 0,
  date: '',
  time_start: '09:00',
  time_end: '17:00',
  type: 'work',
  client_id: null,
  mission_id: null,
  note: ''
})

// Week days computation
const weekDays = computed(() => {
  const [year, month] = currentDate.value.split('-').map(Number)
  const firstDay = new Date(year, month - 1, 1)
  const lastDay = new Date(year, month, 0)
  const days = []

  for (let d = 1; d <= lastDay.getDate(); d++) {
    const date = new Date(year, month - 1, d)
    days.push({
      date: date.toISOString().split('T')[0],
      name: date.toLocaleDateString('fr', { weekday: 'short' }).replace('.', ''),
      day: d
    })
  }
  return days
})

const filteredAgents = computed(() => {
  if (!filters.value.search) return agents.value
  const search = filters.value.search.toLowerCase()
  return agents.value.filter(a =>
    a.full_name.toLowerCase().includes(search) ||
    a.employee_code.toLowerCase().includes(search)
  )
})

const filteredMissions = computed(() => {
  if (!slotForm.value.client_id) return missions.value
  return missions.value.filter(m => m.client_id === slotForm.value.client_id)
})

const totalWorkedHours = computed(() => {
  const workedSlots = planningStore.slots.filter(s => s.type === 'work')
  const totalMinutes = workedSlots.reduce((sum, s) => sum + s.duration_minutes, 0)
  return Math.round(totalMinutes / 60 * 10) / 10
})

const totalLeaveSlots = computed(() => {
  return planningStore.slots.filter(s => s.type === 'leave').length
})

const uniqueAgentsCount = computed(() => {
  return new Set(planningStore.slots.map(s => s.agent_id)).size
})

function getSlotsForAgentAndDay(agentId: number, date: string) {
  return planningStore.slots.filter(s => s.agent_id === agentId && s.date === date)
}

function getSlotClass(type: string) {
  const classes = {
    work: 'bg-primary-50 text-primary-700 border border-primary-100',
    leave: 'bg-secondary-50 text-secondary-700 border border-secondary-100',
    training: 'bg-amber-50 text-amber-700 border border-amber-100',
    other: 'bg-dark-50 text-dark-600 border border-dark-100'
  }
  return classes[type as keyof typeof classes] || classes.work
}

let debounceTimer: ReturnType<typeof setTimeout>
function debouncedFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => fetchSlots(), 300)
}

async function fetchSlots() {
  const [year, month] = currentDate.value.split('-').map(Number)
  await planningStore.fetchMonthPlanning(selectedAgentId.value || 0, year, month)
}

async function loadData() {
  await agentsStore.fetchAgents({ active_only: true, per_page: 200 })
  agents.value = agentsStore.agents

  const [clientsRes, missionsRes] = await Promise.all([
    clientsApi.index(),
    missionsApi.index()
  ])
  clients.value = clientsRes.data.data
  missions.value = missionsRes.data.data

  await fetchSlots()
}

function openCreateModal() {
  editingSlot.value = null
  slotForm.value = {
    agent_id: selectedAgentId.value || agents.value[0]?.id || 0,
    date: new Date().toISOString().split('T')[0],
    time_start: '09:00',
    time_end: '17:00',
    type: 'work',
    client_id: null,
    mission_id: null,
    note: ''
  }
  modalOpen.value = true
}

function openCreateModalForDay(agentId: number, date: string) {
  editingSlot.value = null
  slotForm.value = {
    agent_id: agentId,
    date: date,
    time_start: '09:00',
    time_end: '17:00',
    type: 'work',
    client_id: null,
    mission_id: null,
    note: ''
  }
  modalOpen.value = true
}

function openEditModal(slot: any) {
  editingSlot.value = slot
  slotForm.value = {
    agent_id: slot.agent_id,
    date: slot.date,
    time_start: slot.time_start,
    time_end: slot.time_end,
    type: slot.type,
    client_id: slot.client_id,
    mission_id: slot.mission_id,
    note: slot.note || ''
  }
  modalOpen.value = true
}

async function saveSlot() {
  saving.value = true
  try {
    if (editingSlot.value) {
      await planningStore.updateSlot(editingSlot.value.id, slotForm.value)
    } else {
      await planningStore.createSlot(slotForm.value)
    }
    await fetchSlots()
    closeModal()
  } catch (error) {
    console.error('Erreur lors de la sauvegarde:', error)
  } finally {
    saving.value = false
  }
}

function closeModal() {
  modalOpen.value = false
  editingSlot.value = null
}

watch(selectedAgentId, () => fetchSlots())
watch(() => currentDate.value, () => fetchSlots())

onMounted(() => {
  loadData()
})
</script>
