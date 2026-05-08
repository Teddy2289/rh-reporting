<template>
  <AppLayout>
    <div class="p-8 space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-dark-900">Clients</h1>
          <p class="text-sm text-dark-500 mt-1">Gérez vos clients et leurs missions</p>
        </div>
        <button
          @click="openCreateModal"
          class="inline-flex items-center gap-2 bg-primary-400 hover:bg-primary-500 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-all duration-200 shadow-sm hover:shadow"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Nouveau client
        </button>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-dark-100 p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-dark-500 uppercase tracking-wider">Total clients</p>
              <p class="text-2xl font-semibold text-dark-900 mt-1">{{ clientsStore.clients.length }}</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-primary-100 flex items-center justify-center">
              <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-xl border border-dark-100 p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-dark-500 uppercase tracking-wider">Clients actifs</p>
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
              <p class="text-xs text-dark-500 uppercase tracking-wider">Clients inactifs</p>
              <p class="text-2xl font-semibold text-dark-600 mt-1">{{ inactiveCount }}</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-dark-100 flex items-center justify-center">
              <svg class="w-5 h-5 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-xl border border-dark-100 p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-dark-500 uppercase tracking-wider">Missions total</p>
              <p class="text-2xl font-semibold text-secondary-600 mt-1">{{ totalMissionsCount }}</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-secondary-100 flex items-center justify-center">
              <svg class="w-5 h-5 text-secondary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Filter Toggle -->
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 p-1 bg-dark-100 rounded-lg">
          <button
            @click="showActiveOnly = true"
            :class="[
              'px-4 py-1.5 rounded-md text-sm font-medium transition',
              showActiveOnly ? 'bg-white text-dark-900 shadow-sm' : 'text-dark-500 hover:text-dark-700'
            ]"
          >
            Actifs uniquement
          </button>
          <button
            @click="showActiveOnly = false"
            :class="[
              'px-4 py-1.5 rounded-md text-sm font-medium transition',
              !showActiveOnly ? 'bg-white text-dark-900 shadow-sm' : 'text-dark-500 hover:text-dark-700'
            ]"
          >
            Tous les clients
          </button>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="clientsStore.loading" class="flex items-center justify-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-2 border-primary-400 border-t-transparent"></div>
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredClients.length === 0" class="text-center py-12 bg-white rounded-xl border border-dark-100">
        <svg class="w-12 h-12 mx-auto text-dark-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <p class="text-dark-500">Aucun client trouvé</p>
        <button
          @click="openCreateModal"
          class="mt-3 text-primary-500 hover:text-primary-600 text-sm font-medium"
        >
          Créer le premier client
        </button>
      </div>

      <!-- Clients Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <div
          v-for="client in filteredClients"
          :key="client.id"
          class="bg-white rounded-xl border border-dark-100 overflow-hidden hover:shadow-md transition-all duration-200 group"
        >
          <div class="p-5">
            <div class="flex items-start justify-between mb-3">
              <div class="flex items-center gap-3">
                <div
                  class="w-10 h-10 rounded-lg flex items-center justify-center text-white font-semibold text-sm"
                  :style="{ backgroundColor: client.color || '#474665' }"
                >
                  {{ client.code?.substring(0, 2).toUpperCase() }}
                </div>
                <div>
                  <h3 class="font-semibold text-dark-900">{{ client.name }}</h3>
                  <p class="text-xs text-dark-500">{{ client.code }}</p>
                </div>
              </div>
              <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition">
                <button
                  @click="openEditModal(client)"
                  class="p-1.5 text-dark-400 hover:text-primary-500 rounded-lg hover:bg-dark-50 transition"
                  title="Modifier"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </button>
                <button
                  @click="confirmDelete(client)"
                  class="p-1.5 text-dark-400 hover:text-red-500 rounded-lg hover:bg-red-50 transition"
                  title="Supprimer"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </div>

            <div class="space-y-2 mt-3">
              <div v-if="client.contact_email" class="flex items-center gap-2 text-sm">
                <svg class="w-4 h-4 text-dark-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span class="text-dark-600 truncate">{{ client.contact_email }}</span>
              </div>
              <div v-if="client.contact_phone" class="flex items-center gap-2 text-sm">
                <svg class="w-4 h-4 text-dark-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                <span class="text-dark-600">{{ client.contact_phone }}</span>
              </div>
            </div>

            <p v-if="client.notes" class="text-sm text-dark-600 mt-3 line-clamp-2">
              {{ client.notes }}
            </p>

            <div class="flex items-center justify-between mt-4 pt-3 border-t border-dark-100">
              <div class="flex items-center gap-1">
                <svg class="w-4 h-4 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <span class="text-sm text-dark-600">{{ client.missions?.length || 0 }} mission(s)</span>
              </div>
              <span
                :class="client.is_active ? 'bg-green-100 text-green-700' : 'bg-dark-100 text-dark-600'"
                class="text-xs font-medium px-2 py-1 rounded-full"
              >
                {{ client.is_active ? 'Actif' : 'Inactif' }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Création/Édition -->
    <div v-if="modalOpen" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="closeModal">
      <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white flex items-center justify-between p-5 border-b border-dark-100">
          <h2 class="text-lg font-semibold text-dark-900">{{ editingClient ? 'Modifier le client' : 'Nouveau client' }}</h2>
          <button @click="closeModal" class="text-dark-400 hover:text-dark-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <form @submit.prevent="saveClient" class="p-5 space-y-4">
          <AppInput
            v-model="form.name"
            label="Nom du client"
            required
            :error="ve.name?.[0]"
          />

          <AppInput
            v-model="form.code"
            label="Code"
            required
            placeholder="ex: CLT001"
            :error="ve.code?.[0]"
          />

          <AppInput
            v-model="form.color"
            label="Couleur"
            type="color"
            :error="ve.color?.[0]"
          />

          <AppInput
            v-model="form.contact_email"
            label="Email de contact"
            type="email"
            :error="ve.contact_email?.[0]"
          />

          <AppInput
            v-model="form.contact_phone"
            label="Téléphone de contact"
            :error="ve.contact_phone?.[0]"
          />

          <div v-if="editingClient" class="flex items-center gap-3 p-3 bg-dark-50 rounded-lg">
            <label class="flex items-center gap-2 cursor-pointer">
              <input
                v-model="form.is_active"
                type="checkbox"
                class="rounded border-dark-300 text-primary-400 focus:ring-primary-400"
              />
              <span class="text-sm text-dark-700">Client actif</span>
            </label>
          </div>

          <AppInput
            v-model="form.notes"
            label="Notes"
            type="textarea"
            rows="3"
            :error="ve.notes?.[0]"
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
            Êtes-vous sûr de vouloir supprimer le client <strong>{{ clientToDelete?.name }}</strong> ?
          </p>
          <p class="text-sm text-red-500 mt-2">
            Cette action est irréversible. Les missions associées seront également supprimées.
          </p>
          <div class="flex justify-end gap-3 mt-5">
            <AppButton type="button" variant="secondary" @click="closeDeleteModal">Annuler</AppButton>
            <AppButton type="button" variant="danger" @click="deleteClient">Supprimer</AppButton>
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
import AppButton from '@/components/common/AppButton.vue'
import { useClientsStore } from '@/stores/clients'
import { useApi } from '@/composables/useApi'
import type { Client, ClientForm } from '@/types'

const clientsStore = useClientsStore()
const { execute, validationErrors: ve } = useApi()

const modalOpen = ref(false)
const deleteModalOpen = ref(false)
const editingClient = ref<Client | null>(null)
const clientToDelete = ref<Client | null>(null)
const saving = ref(false)
const showActiveOnly = ref(true)

const form = ref<ClientForm>({
  name: '',
  code: '',
  color: '#31b6b8',
  contact_email: '',
  contact_phone: '',
  notes: '',
  is_active: true
})

const filteredClients = computed(() => {
  if (showActiveOnly.value) {
    return clientsStore.clients.filter(c => c.is_active)
  }
  return clientsStore.clients
})

const activeCount = computed(() => clientsStore.clients.filter(c => c.is_active).length)
const inactiveCount = computed(() => clientsStore.clients.filter(c => !c.is_active).length)
const totalMissionsCount = computed(() => {
  return clientsStore.clients.reduce((sum, client) => sum + (client.missions?.length || 0), 0)
})

async function loadClients() {
  await execute(() => clientsStore.fetchClients(showActiveOnly.value))
}

function openCreateModal() {
  editingClient.value = null
  form.value = {
    name: '',
    code: '',
    color: '#31b6b8',
    contact_email: '',
    contact_phone: '',
    notes: '',
    is_active: true
  }
  modalOpen.value = true
}

function openEditModal(client: Client) {
  editingClient.value = client
  form.value = {
    name: client.name,
    code: client.code,
    color: client.color || '#31b6b8',
    contact_email: client.contact_email || '',
    contact_phone: client.contact_phone || '',
    notes: client.notes || '',
    is_active: client.is_active
  }
  modalOpen.value = true
}

async function saveClient() {
  saving.value = true
  try {
    if (editingClient.value) {
      await clientsStore.updateClient(editingClient.value.id, form.value)
    } else {
      await clientsStore.createClient(form.value)
    }
    await loadClients()
    closeModal()
  } catch (error) {
    console.error('Erreur lors de la sauvegarde:', error)
  } finally {
    saving.value = false
  }
}

function confirmDelete(client: Client) {
  clientToDelete.value = client
  deleteModalOpen.value = true
}

async function deleteClient() {
  if (clientToDelete.value) {
    await clientsStore.deleteClient(clientToDelete.value.id)
    await loadClients()
    closeDeleteModal()
  }
}

function closeModal() {
  modalOpen.value = false
  editingClient.value = null
}

function closeDeleteModal() {
  deleteModalOpen.value = false
  clientToDelete.value = null
}

watch(showActiveOnly, () => {
  loadClients()
})

onMounted(() => {
  loadClients()
})
</script>
