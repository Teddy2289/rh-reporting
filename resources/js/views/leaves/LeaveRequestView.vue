<template>
  <AppLayout>
    <div class="p-8 max-w-2xl mx-auto">
      <div class="mb-6">
        <div class="flex items-center gap-4">
          <RouterLink :to="{ name: 'leaves' }" class="text-dark-500 hover:text-primary-500 transition p-1 -ml-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
          </RouterLink>
          <div>
            <h1 class="text-2xl font-semibold text-dark-900">Nouvelle demande de congé</h1>
            <p class="text-sm text-dark-500 mt-1">Saisissez les informations pour votre demande</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl border border-dark-100 p-6">
        <!-- Solde de congés -->
        <div v-if="balance" class="mb-6 p-4 bg-primary-50 rounded-xl">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-primary-700">Solde de congés {{ currentYear }}</p>
              <p class="text-2xl font-bold text-primary-600">{{ balance.remaining_days }} jours</p>
            </div>
            <div class="text-right">
              <p class="text-xs text-primary-600">Alloués: {{ balance.allocated_days }}</p>
              <p class="text-xs text-primary-600">Utilisés: {{ balance.used_days }}</p>
            </div>
          </div>
          <div class="mt-3 h-2 bg-primary-200 rounded-full overflow-hidden">
            <div
              class="h-full bg-primary-500 rounded-full transition-all"
              :style="{ width: `${(balance.used_days / balance.allocated_days) * 100}%` }"
            ></div>
          </div>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-5">
          <AppAlert v-if="error" type="error" :message="error" />

          <!-- Agent (admin/rh seulement) -->
          <AppSelect
            v-if="showAgentSelect"
            v-model="form.agent_id"
            label="Agent"
            required
            :error="ve.agent_id?.[0]"
          >
            <option v-for="a in agents" :key="a.id" :value="a.id">{{ a.full_name }}</option>
          </AppSelect>

          <AppSelect
            v-model="form.type"
            label="Type de congé"
            required
            :error="ve.type?.[0]"
          >
            <option v-for="(label, key) in LEAVE_TYPE_LABELS" :key="key" :value="key">{{ label }}</option>
          </AppSelect>

          <div class="grid grid-cols-2 gap-4">
            <AppInput
              v-model="form.date_start"
              label="Date de début"
              type="date"
              required
              :error="ve.date_start?.[0]"
              @update:model-value="handleDateChange"
            />
            <AppInput
              v-model="form.date_end"
              label="Date de fin"
              type="date"
              required
              :min="form.date_start"
              :error="ve.date_end?.[0]"
              @update:model-value="handleDateChange"
            />
          </div>

          <!-- Aperçu des jours -->
          <div v-if="workingDays > 0" class="p-3 bg-dark-50 rounded-lg">
            <div class="flex items-center justify-between text-sm">
              <span class="text-dark-600">Jours ouvrés</span>
              <span class="font-semibold text-primary-600">{{ workingDays }} jour(s)</span>
            </div>
            <div class="flex items-center justify-between text-sm mt-1">
              <span class="text-dark-600">Dont week-ends</span>
              <span class="text-dark-500">{{ weekendsCount }} jour(s)</span>
            </div>
          </div>

          <AppInput
            v-model="form.reason"
            label="Motif (optionnel)"
            type="textarea"
            placeholder="Précisez si nécessaire…"
          />

          <div class="flex justify-end gap-3 pt-4 border-t border-dark-100">
            <AppButton type="button" variant="secondary" @click="$router.back()">
              Annuler
            </AppButton>
            <AppButton type="submit" :loading="loading" class="bg-primary-400 hover:bg-primary-500">
              Soumettre la demande
            </AppButton>
          </div>
        </form>
      </div>

      <!-- Informations -->
      <div class="mt-6 bg-secondary-50 rounded-xl border border-secondary-100 p-4">
        <div class="flex gap-3">
          <svg class="w-5 h-5 text-secondary-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div class="text-sm text-secondary-800">
            <p class="font-medium mb-1">Informations sur les congés</p>
            <p class="opacity-90">
              Les congés sont décomptés en jours ouvrés (du lundi au vendredi).
              Les week-ends et jours fériés ne sont pas décomptés.
            </p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { reactive, ref, onMounted, computed, watch } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import AppLayout from '@/components/layout/AppLayout.vue'
import AppInput from '@/components/common/AppInput.vue'
import AppSelect from '@/components/common/AppSelect.vue'
import AppButton from '@/components/common/AppButton.vue'
import AppAlert from '@/components/common/AppAlert.vue'
import { useLeavesStore } from '@/stores/leaves'
import { useAuthStore } from '@/stores/auth'
import { useAgentsStore } from '@/stores/agents'
import { useApi } from '@/composables/useApi'
import { LEAVE_TYPE_LABELS, getWorkingDays, getCalendarDays } from '@/utils'
import { LeaveType } from '@/types'
import type { Agent, LeaveBalance } from '@/types'

const router = useRouter()
const leavesStore = useLeavesStore()
const authStore = useAuthStore()
const agentsStore = useAgentsStore()
const { loading, error, validationErrors: ve, execute } = useApi()

const showAgentSelect = authStore.hasAnyRole('admin', 'rh')
const currentYear = ref(new Date().getFullYear())
const balance = ref<LeaveBalance | null>(null)
const agents = ref<Agent[]>([])
const workingDays = ref(0)
const weekendsCount = ref(0)

const form = reactive({
  agent_id: authStore.user?.agent?.id ?? 0,
  type: LeaveType.Annual,
  date_start: '',
  date_end: '',
  reason: '',
})

async function fetchBalance() {
  if (form.agent_id && form.date_start) {
    const year = new Date(form.date_start).getFullYear()
    balance.value = await leavesStore.fetchBalance(form.agent_id, year)
    currentYear.value = year
  }
}

function calculateWorkingDays() {
  if (form.date_start && form.date_end) {
    const start = new Date(form.date_start)
    const end = new Date(form.date_end)
    if (start <= end) {
      workingDays.value = getWorkingDays(form.date_start, form.date_end)
      weekendsCount.value = getCalendarDays(form.date_start, form.date_end) - workingDays.value
    } else {
      workingDays.value = 0
      weekendsCount.value = 0
    }
  } else {
    workingDays.value = 0
    weekendsCount.value = 0
  }
}

function handleDateChange() {
  fetchBalance()
  calculateWorkingDays()
}

async function handleSubmit() {
  const payload = {
    agent_id: form.agent_id,
    type: form.type,
    date_start: form.date_start,
    date_end: form.date_end,
    reason: form.reason || null
  }

  const result = await execute(() => leavesStore.createLeave(payload))
  if (result) {
    router.push({ name: 'leaves' })
  }
}

watch(() => form.agent_id, () => {
  fetchBalance()
  calculateWorkingDays()
})

watch(() => form.date_start, () => {
  if (form.date_end && new Date(form.date_start) > new Date(form.date_end)) {
    form.date_end = ''
  }
  calculateWorkingDays()
})

watch(() => form.date_end, calculateWorkingDays)

onMounted(async () => {
  if (showAgentSelect) {
    await agentsStore.fetchAgents({ active_only: true, per_page: 200 })
    agents.value = agentsStore.agents
  } else {
    await fetchBalance()
  }
})
</script>
