<template>
  <AppLayout>
    <div class="p-8 space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-dark-900">Missions</h1>
          <p class="text-sm text-dark-500 mt-1">Gérez les missions par client</p>
        </div>
        <button
          @click="openCreateModal"
          class="inline-flex items-center gap-2 bg-primary-400 hover:bg-primary-500 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-all duration-200 shadow-sm hover:shadow"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Nouvelle mission
        </button>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-dark-100 p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-dark-500 uppercase tracking-wider">Total missions</p>
              <p class="text-2xl font-semibold text-dark-900 mt-1">{{ missionsStore.missions.length }}</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-primary-100 flex items-center justify-center">
              <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-xl border border-dark-100 p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-dark-500 uppercase tracking-wider">Missions actives</p>
              <p class="text-2xl font-semibold text-green-600 mt-1">{{ activeCount }}</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
              <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-xl border border-dark-100 p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-dark-500 uppercase tracking-wider">Clients</p>
              <p class="text-2xl font-semibold text-secondary-600 mt-1">{{ uniqueClientsCount }}</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-secondary-100 flex items-center justify-center">
              <svg class="w-5 h-5 text-secondary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-xl border border-dark-100 p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-dark-500 uppercase tracking-wider">Missions inactives</p>
              <p class="text-2xl font-semibold text-dark-600 mt-1">{{ inactiveCount }}</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-dark-100 flex items-center justify-center">
              <svg class="w-5 h-5 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="flex flex-wrap items-center gap-3 p-4 bg-white rounded-xl border border-dark-100">
        <div class="flex items-center gap-2 p-1 bg-dark-100 rounded-lg">
          <button
            @click="showActiveOnly = true"
            :class="[
              'px-4 py-1.5 rounded-md text-sm font-medium transition',
              showActiveOnly ? 'bg-white text-dark-900 shadow-sm' : 'text-dark-500 hover:text-dark-700'
            ]"
          >
            Actives uniquement
          </button>
          <button
            @click="showActiveOnly = false"
            :class="[
              'px-4 py-1.5 rounded-md text-sm font-medium transition',
              !showActiveOnly ? 'bg-white text-dark-900 shadow-sm' : 'text-dark-500 hover:text-dark-700'
            ]"
          >
            Toutes les missions
          </button>
        </div>

        <select
          v-model="selectedClientId"
          class="px-3 py-1.5 border border-dark-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent transition bg-white min-w-[180px]"
          @change="filterByClient"
        >
          <option :value="null">Tous les clients</option>
          <option v-for="client in clients" :key="client.id" :value="client.id">
            {{ client.name }}
          </option>
        </select>
      </div>

      <!-- Loading -->
      <div v-if="missionsStore.loading" class="flex items-center justify-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-2 border-primary-400 border-t-transparent"></div>
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredMissions.length === 0" class="text-center py-12 bg-white rounded-xl border border-dark-100">
        <svg class="w-12 h-12 mx-auto text-dark-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
        </svg>
        <p class="text-dark-500">Aucune mission trouvée</p>
        <button
          @click="openCreateModal"
          class="mt-3 text-primary-500 hover:text-primary-600 text-sm font-medium"
        >
          Créer la première mission
        </button>
      </div>

      <!-- Missions List -->
      <div v-else class="bg-white rounded-xl border border-dark-100 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="bg-dark-50 border-b border-dark-100">
                <th class="px-6 py-4 text-left text-xs font-semibold text-dark-600 uppercase tracking-wider">Mission</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-dark-600 uppercase tracking-wider">Client</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-dark-600 uppercase tracking-wider">Code</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-dark-600 uppercase tracking-wider">Statut</th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-dark-600 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-dark-100">
              <tr
                v-for="mission in filteredMissions"
                :key="mission.id"
                class="hover:bg-dark-50/50 transition-colors duration-150 group"
              >
                <td class="px-6 py-4">
                  <div>
                    <div class="font-medium text-dark-900">{{ mission.name }}</div>
                    <div v-if="mission.description" class="text-xs text-dark-500 mt-0.5 line-clamp-1">
                      {{ mission.description }}
                    </div>
                  </div>
                 </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2">
                    <div
                      class="w-6 h-6 rounded flex items-center justify-center text-white text-xs font-medium"
                      :style="{ backgroundColor: mission.client?.color || '#474665' }"
                    >
                      {{ mission.client?.code?.substring(0, 1) }}
                    </div>
                    <span class="text-dark-700">{{ mission.client?.name || '—' }}</span>
                  </div>
                 </td>
                <td class="px-6 py-4">
                  <code class="text-xs bg-dark-100 px-2 py-1 rounded">{{ mission.code || '—' }}</code>
                 </td>
                <td class="px-6 py-4">
                  <span
                    :class="mission.is_active ? 'bg-green-100 text-green-700' : 'bg-dark-100 text-dark-600'"
                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium"
                  >
                    <span class="w-1.5 h-1.5 rounded-full mr-1.5" :class="mission.is_active ? 'bg-green-500' : 'bg-dark-400'"></span>
                    {{ mission.is_active ? 'Active' : 'Inactive' }}
                  </span>
                 </td>
                <td class="px-6 py-4 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <button
                      @click="openEditModal(mission)"
                      class="p-1.5 text-dark-400 hover:text-primary-500 rounded-lg hover:bg-dark-50 transition"
                      title="Modifier"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                    <button
                      @click="confirmDelete(mission)"
                      class="p-1.5 text-dark-400 hover:text-red-500 rounded-lg hover:bg-red-50 transition"
                      title="Supprimer"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                 </td>
               </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal Création/Édition -->
    <div v-if="modalOpen" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="closeModal">
      <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
        <div class="flex items-center justify-between p-5 border-b border-dark-100">
          <h2 class="text-lg font-semibold text-dark-900">{{ editingMission ? 'Modifier la mission' : 'Nouvelle mission' }}</h2>
          <button @click="closeModal" class="text-dark-400 hover:text-dark-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <form @submit.prevent="saveMission" class="p-5 space-y-4">
          <AppSelect
            v-model="form.client_id"
            label="Client"
            required
            :error="ve.client_id?.[0]"
          >
            <option v-for="client in clients" :key="client.id" :value="client.id">
              {{ client.name }}
            </option>
          </AppSelect>

          <AppInput
            v-model="form.name"
            label="Nom de la mission"
            required
            :error="ve.name?.[0]"
          />

          <AppInput
            v-model="form.code"
            label="Code"
            placeholder="Optionnel"
            :error="ve.code?.[0]"
          />

          <div v-if="editingMission" class="flex items-center gap-3 p-3 bg-dark-50 rounded-lg">
            <label class="flex items-center gap-2 cursor-pointer">
              <input
                v-model="form.is_active"
                type="checkbox"
                class="rounded border-dark-300 text-primary-400 focus:ring-primary-400"
              />
              <span class="text-sm text-dark-700">Mission active</span>
            </label>
          </div>

          <AppInput
            v-model="form.description"
            label="Description"
            type="textarea"
            rows="3"
            :error="ve.description?.[0]"
          />

          <div class="flex justify-end gap-3 pt-3">
            <AppButton type="button" variant="secondary" @click="closeModal">Annuler</AppButton>
            <AppButton type="submit" :loading="saving">Enregistrer</AppButton>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Confirmation Suppression -->
    <div v-if="deleteModalOpen" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="closeDeleteModal">
      <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
        <div class="flex items-center justify-between p-5 border-b border-dark-100">
          <h2 class="text-lg font-semibold text-dark-900">Confirmer la suppression</h2>
          <button @click="closeDeleteModal" class="text-dark-400 hover:text-dark-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div class="p-5">
          <p class="text-dark-700">
            Êtes-vous sûr de vouloir supprimer la mission <strong>{{ missionToDelete?.name }}</strong> ?
          </p>
          <p class="text-sm text-red-500 mt-2">
            Cette action est irréversible.
          </p>
          <div class="flex justify-end gap-3 mt-5">
            <AppButton type="button" variant="secondary" @click="closeDeleteModal">Annuler</AppButton>
            <AppButton type="button" variant="danger" @click="deleteMission">Supprimer</AppButton>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import AppLayout from '@/components/layout/AppLayout.vue'
import AppInput from '@/components/common/AppInput.vue'
import AppSelect from '@/components/common/AppSelect.vue'
import AppButton from '@/components/common/AppButton.vue'
import { useMissionsStore } from '@/stores/missions'
import { useClientsStore } from '@/stores/clients'
import { useApi } from '@/composables/useApi'
import type { Mission, MissionForm, Client } from '@/types'

const missionsStore = useMissionsStore()
const clientsStore = useClientsStore()
const { execute, validationErrors: ve } = useApi()

const modalOpen = ref(false)
const deleteModalOpen = ref(false)
const editingMission = ref<Mission | null>(null)
const missionToDelete = ref<Mission | null>(null)
const saving = ref(false)
const showActiveOnly = ref(true)
const selectedClientId = ref<number | null>(null)

const clients = ref<Client[]>([])

const form = ref<MissionForm>({
  client_id: 0,
  name: '',
  code: '',
  description: '',
  is_active: true
})

const filteredMissions = computed(() => {
  let missions = missionsStore.missions

  if (showActiveOnly.value) {
    missions = missions.filter(m => m.is_active)
  }

  if (selectedClientId.value) {
    missions = missions.filter(m => m.client_id === selectedClientId.value)
  }

  return missions
})

const activeCount = computed(() => missionsStore.missions.filter(m => m.is_active).length)
const inactiveCount = computed(() => missionsStore.missions.filter(m => !m.is_active).length)
const uniqueClientsCount = computed(() => {
  return new Set(missionsStore.missions.map(m => m.client_id)).size
})

async function loadData() {
  await Promise.all([
    missionsStore.fetchMissions({ active_only: showActiveOnly.value }),
    clientsStore.fetchClients(false)
  ])
  clients.value = clientsStore.clients
}

async function filterByClient() {
  const filters: any = { active_only: showActiveOnly.value }
  if (selectedClientId.value) {
    filters.client_id = selectedClientId.value
  }
  await missionsStore.fetchMissions(filters)
}

function openCreateModal() {
  editingMission.value = null
  form.value = {
    client_id: clients.value[0]?.id || 0,
    name: '',
    code: '',
    description: '',
    is_active: true
  }
  modalOpen.value = true
}

function openEditModal(mission: Mission) {
  editingMission.value = mission
  form.value = {
    client_id: mission.client_id,
    name: mission.name,
    code: mission.code || '',
    description: mission.description || '',
    is_active: mission.is_active
  }
  modalOpen.value = true
}

async function saveMission() {
  saving.value = true
  try {
    if (editingMission.value) {
      await missionsStore.updateMission(editingMission.value.id, form.value)
    } else {
      await missionsStore.createMission(form.value)
    }
    await loadData()
    closeModal()
  } catch (error) {
    console.error('Erreur lors de la sauvegarde:', error)
  } finally {
    saving.value = false
  }
}

function confirmDelete(mission: Mission) {
  missionToDelete.value = mission
  deleteModalOpen.value = true
}

async function deleteMission() {
  if (missionToDelete.value) {
    await missionsStore.deleteMission(missionToDelete.value.id)
    await loadData()
    closeDeleteModal()
  }
}

function closeModal() {
  modalOpen.value = false
  editingMission.value = null
}

function closeDeleteModal() {
  deleteModalOpen.value = false
  missionToDelete.value = null
}

watch(showActiveOnly, () => {
  loadData()
})

watch(selectedClientId, () => {
  loadData()
})

onMounted(() => {
  loadData()
})
</script>
