<template>
  <AppLayout>
    <div class="p-8 max-w-3xl mx-auto">
      <div class="mb-6">
        <div class="flex items-center gap-4">
          <RouterLink :to="{ name: 'agents' }" class="text-dark-500 hover:text-primary-500 transition p-1 -ml-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
          </RouterLink>
          <div>
            <h1 class="text-2xl font-semibold text-dark-900">
              {{ isEdit ? 'Modifier l\'agent' : 'Nouvel agent' }}
            </h1>
            <p class="text-sm text-dark-500 mt-1">
              {{ isEdit ? 'Mettez à jour les informations de l\'agent' : 'Ajoutez un nouvel agent à la plateforme' }}
            </p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl border border-dark-100 p-6">
        <form class="space-y-5" @submit.prevent="handleSubmit">
          <AppAlert v-if="error" type="error" :message="error" />

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <AppInput
              v-model="form.first_name"
              label="Prénom"
              required
              :error="ve.first_name?.[0]"
              class="focus-within:ring-primary-400"
            />
            <AppInput
              v-model="form.last_name"
              label="Nom"
              required
              :error="ve.last_name?.[0]"
            />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <AppInput
              v-model="form.email"
              label="Email"
              type="email"
              required
              :error="ve.email?.[0]"
            />
            <AppInput
              v-model="form.password"
              label="Mot de passe"
              type="password"
              :required="!isEdit"
              :placeholder="isEdit ? 'Laisser vide pour ne pas changer' : ''"
              :error="ve.password?.[0]"
            />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <AppInput
              v-model="form.employee_code"
              label="Matricule"
              required
              :error="ve.employee_code?.[0]"
            />
            <AppInput
              v-model="form.phone"
              label="Téléphone"
              type="tel"
              :error="ve.phone?.[0]"
            />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <AppSelect
              v-model="form.department_id"
              label="Département"
              required
              :error="ve.department_id?.[0]"
            >
              <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
            </AppSelect>

            <AppSelect
              v-model="form.manager_id"
              label="Manager"
              placeholder="Aucun manager"
              :error="ve.manager_id?.[0]"
            >
              <option :value="null">Aucun</option>
              <option v-for="a in managers" :key="a.id" :value="a.id">{{ a.full_name }}</option>
            </AppSelect>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <AppSelect
              v-model="form.contract_type"
              label="Type de contrat"
              required
              :error="ve.contract_type?.[0]"
            >
              <option v-for="(label, key) in CONTRACT_TYPE_LABELS" :key="key" :value="key">{{ label }}</option>
            </AppSelect>

            <AppInput
              v-model="form.hire_date"
              label="Date d'embauche"
              type="date"
              required
              :error="ve.hire_date?.[0]"
            />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <AppInput
              v-model="form.contract_end_date"
              label="Fin de contrat"
              type="date"
              :error="ve.contract_end_date?.[0]"
            />
            <AppInput
              v-model="form.weekly_hours"
              label="Heures / semaine"
              type="number"
              required
              :error="ve.weekly_hours?.[0]"
            />
          </div>

          <AppInput
            v-model="form.annual_leave_days"
            label="Jours de congés annuels"
            type="number"
            required
            :error="ve.annual_leave_days?.[0]"
          />

          <div class="flex justify-end gap-3 pt-4 border-t border-dark-100">
            <AppButton type="button" variant="secondary" @click="$emit('cancel')">
              Annuler
            </AppButton>
            <AppButton type="submit" :loading="loading" class="bg-primary-400 hover:bg-primary-500">
              {{ isEdit ? 'Mettre à jour' : 'Créer l\'agent' }}
            </AppButton>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/components/layout/AppLayout.vue'
import { reactive, onMounted, ref, computed } from 'vue'
import AppInput from '@/components/common/AppInput.vue'
import AppSelect from '@/components/common/AppSelect.vue'
import AppButton from '@/components/common/AppButton.vue'
import AppAlert from '@/components/common/AppAlert.vue'
import { useAgentsStore } from '@/stores/agents'
import { useApi } from '@/composables/useApi'
import { departmentsApi } from '@/api'
import { CONTRACT_TYPE_LABELS } from '@/utils'
import { ContractType } from '@/types'
import type { Agent, AgentForm, Department } from '@/types'

const props = defineProps<{
    agent?: Agent | null
}>()

const emit = defineEmits<{
    cancel: []
    saved: [agent: Agent]
}>()

const agentsStore = useAgentsStore()
const { loading, error, validationErrors: ve, execute } = useApi()

const isEdit = computed(() => !!props.agent)
const departments = ref<Department[]>([])
const managers = ref<Agent[]>([])

const form = reactive<AgentForm & { password?: string }>({
    first_name: props.agent?.first_name ?? '',
    last_name: props.agent?.last_name ?? '',
    email: props.agent?.user?.email ?? '',
    password: '',
    employee_code: props.agent?.employee_code ?? '',
    department_id: props.agent?.department_id ?? 0,
    manager_id: props.agent?.manager_id ?? null,
    phone: props.agent?.phone ?? '',
    contract_type: props.agent?.contract_type ?? ContractType.CDI,
    hire_date: props.agent?.hire_date ?? '',
    contract_end_date: props.agent?.contract_end_date ?? null,
    weekly_hours: props.agent?.weekly_hours ?? 35,
    annual_leave_days: props.agent?.annual_leave_days ?? 25,
})

async function handleSubmit() {
    const result = await execute(async () => {
        if (isEdit.value && props.agent) {
            return agentsStore.updateAgent(props.agent.id, form)
        }
        return agentsStore.createAgent(form)
    })
    if (result) emit('saved', result)
}

onMounted(async () => {
    const [depts, agts] = await Promise.all([
        departmentsApi.index(),
        agentsStore.fetchAgents({ active_only: true, per_page: 100 }),
    ])
    departments.value = depts.data.data
    managers.value = agentsStore.agents
})
</script>
