<template>
  <form class="space-y-5" @submit.prevent="handleSubmit">
    <AppAlert v-if="error" type="error" :message="error" />

    <!-- Solde de congés -->
    <div v-if="balance" class="grid grid-cols-3 gap-3">
      <div class="bg-blue-50 rounded-xl p-3 text-center">
        <p class="text-2xl font-bold text-blue-700">{{ balance.allocated_days }}</p>
        <p class="text-xs text-blue-600 mt-0.5">Alloués</p>
      </div>
      <div class="bg-emerald-50 rounded-xl p-3 text-center">
        <p class="text-2xl font-bold text-emerald-700">{{ balance.remaining_days }}</p>
        <p class="text-xs text-emerald-600 mt-0.5">Restants</p>
      </div>
      <div class="bg-amber-50 rounded-xl p-3 text-center">
        <p class="text-2xl font-bold text-amber-700">{{ balance.pending_days }}</p>
        <p class="text-xs text-amber-600 mt-0.5">En attente</p>
      </div>
    </div>

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
        @update:model-value="fetchBalance"
      />
      <AppInput
        v-model="form.date_end"
        label="Date de fin"
        type="date"
        required
        :min="form.date_start"
        :error="ve.date_end?.[0]"
      />
    </div>

    <AppInput
      v-model="form.reason"
      label="Motif (optionnel)"
      placeholder="Précisez si nécessaire…"
    />

    <div class="flex justify-end gap-3 pt-2">
      <AppButton type="button" variant="secondary" @click="$emit('cancel')">Annuler</AppButton>
      <AppButton type="submit" :loading="loading">Soumettre la demande</AppButton>
    </div>
  </form>
</template>

<script setup lang="ts">
import { reactive, ref, onMounted, watch } from 'vue'
import AppInput from '@/components/common/AppInput.vue'
import AppSelect from '@/components/common/AppSelect.vue'
import AppButton from '@/components/common/AppButton.vue'
import AppAlert from '@/components/common/AppAlert.vue'
import { useLeavesStore } from '@/stores/leaves'
import { useAuthStore } from '@/stores/auth'
import { useAgentsStore } from '@/stores/agents'
import { useApi } from '@/composables/useApi'
import { LEAVE_TYPE_LABELS } from '@/utils'
import { LeaveType } from '@/types'
import type { Leave, LeaveBalance, Agent } from '@/types'

const emit = defineEmits<{
  cancel: []
  saved: [leave: Leave]
}>()

const leavesStore = useLeavesStore()
const authStore = useAuthStore()
const agentsStore = useAgentsStore()
const { loading, error, validationErrors: ve, execute } = useApi()

const showAgentSelect = authStore.hasAnyRole('admin', 'rh')
const balance = ref<LeaveBalance | null>(null)
const agents = ref<Agent[]>([])

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
  }
}

async function handleSubmit() {
  // Corrigé : utiliser createLeave au lieu de requestLeave
  const result = await execute(() => leavesStore.createLeave(form))
  if (result) emit('saved', result)
}

// Corrigé : watch sur form.agent_id au lieu de form.agent_id seul
watch(() => form.agent_id, () => {
  fetchBalance()
})

onMounted(async () => {
  if (showAgentSelect) {
    await agentsStore.fetchAgents({ active_only: true, per_page: 200 })
    agents.value = agentsStore.agents
  }
  await fetchBalance()
})
</script>
