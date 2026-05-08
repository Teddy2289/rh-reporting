<template>
  <AppLayout>
    <div class="p-8 max-w-2xl mx-auto">
      <div class="mb-6">
        <div class="flex items-center gap-4">
          <RouterLink :to="{ name: 'planning' }" class="text-dark-500 hover:text-primary-500 transition p-1 -ml-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
          </RouterLink>
          <div>
            <h1 class="text-2xl font-semibold text-dark-900">Générer le planning</h1>
            <p class="text-sm text-dark-500 mt-1">Créez automatiquement les plannings pour l'année</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl border border-dark-100 p-6">
        <form @submit.prevent="handleGenerate" class="space-y-5">
          <AppAlert v-if="error" type="error" :message="error" />
          <AppAlert v-if="successMessage" type="success" :message="successMessage" />

          <AppSelect v-model="form.agent_id" label="Agent (optionnel)" :clearable="true">
            <option :value="null">Tous les agents</option>
            <option v-for="agent in agents" :key="agent.id" :value="agent.id">{{ agent.full_name }}</option>
          </AppSelect>

          <AppInput v-model="form.year" label="Année" type="number" required :min="2020" :max="2050" />

          <div class="flex items-center gap-3 p-3 bg-dark-50 rounded-lg">
            <input
              v-model="form.overwrite"
              type="checkbox"
              id="overwrite"
              class="rounded border-dark-300 text-primary-400 focus:ring-primary-400"
            />
            <label for="overwrite" class="text-sm text-dark-700">
              Écraser les plannings existants
              <span class="text-xs text-dark-500 block">Si non coché, ne créera que les créneaux manquants</span>
            </label>
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t border-dark-100">
            <AppButton type="button" variant="secondary" @click="$router.back()">Annuler</AppButton>
            <AppButton type="submit" :loading="generating" class="bg-secondary-500 hover:bg-secondary-600">
              Générer le planning
            </AppButton>
          </div>
        </form>

        <!-- Results -->
        <div v-if="results.length > 0" class="mt-6 pt-6 border-t border-dark-100">
          <h3 class="font-semibold text-dark-900 mb-3">Résultats de la génération</h3>
          <div class="space-y-2">
            <div
              v-for="result in results"
              :key="result.agent"
              class="flex items-center justify-between p-3 bg-dark-50 rounded-lg"
            >
              <span class="text-sm font-medium text-dark-700">{{ result.agent }}</span>
              <span class="text-sm text-primary-600 font-semibold">{{ result.created }} créneaux créés</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Information card -->
      <div class="mt-6 bg-primary-50 rounded-xl border border-primary-100 p-4">
        <div class="flex gap-3">
          <svg class="w-5 h-5 text-primary-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div class="text-sm text-primary-800">
            <p class="font-medium mb-1">Comment fonctionne la génération ?</p>
            <p class="opacity-90">
              La génération automatique crée des créneaux de travail pour chaque agent en fonction de leur contrat,
              des jours fériés et des congés déjà planifiés. Les créneaux sont créés pour tous les jours ouvrés
              de l'année sélectionnée.
            </p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import AppLayout from '@/components/layout/AppLayout.vue'
import AppInput from '@/components/common/AppInput.vue'
import AppSelect from '@/components/common/AppSelect.vue'
import AppButton from '@/components/common/AppButton.vue'
import AppAlert from '@/components/common/AppAlert.vue'
import { usePlanningStore } from '@/stores/planning'
import { useAgentsStore } from '@/stores/agents'
import { useApi } from '@/composables/useApi'
import type { Agent, GeneratePlanningForm } from '@/types'

const router = useRouter()
const planningStore = usePlanningStore()
const agentsStore = useAgentsStore()
const { execute, error } = useApi()

const agents = ref<Agent[]>([])
const generating = ref(false)
const successMessage = ref('')
const results = ref<{ agent: string; created: number }[]>([])

const form = ref<GeneratePlanningForm>({
  year: new Date().getFullYear(),
  agent_id: null,
  overwrite: false
})

async function handleGenerate() {
  generating.value = true
  successMessage.value = ''
  results.value = []

  const result = await execute(async () => {
    return await planningStore.generatePlanning(form.value)
  })

  if (result) {
    successMessage.value = 'Planning généré avec succès !'
    results.value = planningStore.generationResults || []

    // Rediriger après 2 secondes
    setTimeout(() => {
      router.push({ name: 'planning' })
    }, 2000)
  }

  generating.value = false
}

onMounted(async () => {
  await agentsStore.fetchAgents({ active_only: true, per_page: 200 })
  agents.value = agentsStore.agents
})
</script>
