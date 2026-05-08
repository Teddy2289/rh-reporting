<template>
  <form class="space-y-4" @submit.prevent="handleSubmit">
    <AppAlert v-if="error" type="error" :message="error" />

    <AppSelect
      v-model="form.agent_id"
      label="Agent"
      required
      :error="ve.agent_id?.[0]"
    >
      <option v-for="a in agents" :key="a.id" :value="a.id">{{ a.full_name }}</option>
    </AppSelect>

    <div class="grid grid-cols-3 gap-3">
      <AppInput v-model="form.date" label="Date" type="date" required :error="ve.date?.[0]" />
      <AppInput v-model="form.time_start" label="Début" type="time" required :error="ve.time_start?.[0]" />
      <AppInput v-model="form.time_end" label="Fin" type="time" required :error="ve.time_end?.[0]" />
    </div>

    <AppSelect v-model="form.type" label="Type" required :error="ve.type?.[0]">
      <option v-for="(label, key) in SLOT_TYPE_LABELS" :key="key" :value="key">{{ label }}</option>
    </AppSelect>

    <div class="grid grid-cols-2 gap-3">
      <AppSelect v-model="form.client_id" label="Client" placeholder="Aucun">
        <option :value="null">Aucun</option>
        <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
      </AppSelect>

      <AppSelect v-model="form.mission_id" label="Mission" placeholder="Aucune">
        <option :value="null">Aucune</option>
        <option v-for="m in filteredMissions" :key="m.id" :value="m.id">{{ m.name }}</option>
      </AppSelect>
    </div>

    <AppInput v-model="form.note" label="Note" placeholder="Optionnel…" />

    <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
      <input v-model="form.is_confirmed" type="checkbox" class="rounded border-gray-300 text-blue-600" />
      Créneau confirmé
    </label>

    <div class="flex justify-end gap-3 pt-2">
      <AppButton type="button" variant="secondary" @click="$emit('cancel')">Annuler</AppButton>
      <AppButton type="submit" :loading="loading">
        {{ slot ? 'Modifier' : 'Créer le créneau' }}
      </AppButton>
    </div>
  </form>
</template>

<script setup lang="ts">
import { reactive, ref, computed, onMounted, watch } from 'vue'
import AppInput from '@/components/common/AppInput.vue'
import AppSelect from '@/components/common/AppSelect.vue'
import AppButton from '@/components/common/AppButton.vue'
import AppAlert from '@/components/common/AppAlert.vue'
import { usePlanningStore } from '@/stores/planning'
import { useAgentsStore } from '@/stores/agents'
import { useApi } from '@/composables/useApi'
import { clientsApi, missionsApi } from '@/api'
import { SLOT_TYPE_LABELS } from '@/utils'
import { SlotType } from '@/types'
import type { PlanningSlot, Client, Mission, Agent } from '@/types'

const props = defineProps<{ slot?: PlanningSlot | null; defaultDate?: string }>()
const emit = defineEmits<{ cancel: []; saved: [slot: PlanningSlot] }>()

const planningStore = usePlanningStore()
const agentsStore = useAgentsStore()
const { loading, error, validationErrors: ve, execute } = useApi()

const agents = ref<Agent[]>([])
const clients = ref<Client[]>([])
const missions = ref<Mission[]>([])

const form = reactive({
  agent_id: props.slot?.agent_id ?? 0,
  date: props.slot?.date ?? props.defaultDate ?? '',
  time_start: props.slot?.time_start ?? '08:00',
  time_end: props.slot?.time_end ?? '17:00',
  type: props.slot?.type ?? SlotType.Work,
  client_id: props.slot?.client_id ?? null,
  mission_id: props.slot?.mission_id ?? null,
  note: props.slot?.note ?? '',
  is_confirmed: props.slot?.is_confirmed ?? false,
})

const filteredMissions = computed(() =>
  form.client_id ? missions.value.filter((m) => m.client_id === form.client_id) : missions.value
)

watch(() => form.client_id, () => { form.mission_id = null })

async function handleSubmit() {
  const result = await execute(async () => {
    if (props.slot) return planningStore.updateSlot(props.slot.id, form)
    return planningStore.createSlot(form)
  })
  if (result) emit('saved', result)
}

onMounted(async () => {
  const [, c, m] = await Promise.all([
    agentsStore.fetchAgents({ active_only: true, per_page: 200 }),
    clientsApi.index(),
    missionsApi.index(),
  ])
  agents.value = agentsStore.agents
  clients.value = c.data.data
  missions.value = m.data.data
})
</script>
