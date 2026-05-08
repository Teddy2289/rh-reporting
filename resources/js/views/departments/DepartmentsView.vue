<template>
  <AppLayout>
    <div class="p-8 space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-dark-900">Départements</h1>
          <p class="text-sm text-dark-500 mt-1">Gérez les départements de l'entreprise</p>
        </div>
        <button
          @click="openCreateModal"
          class="inline-flex items-center gap-2 bg-primary-400 hover:bg-primary-500 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-all duration-200 shadow-sm hover:shadow"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Nouveau département
        </button>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-dark-100 p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-dark-500 uppercase tracking-wider">Total départements</p>
              <p class="text-2xl font-semibold text-dark-900 mt-1">{{ departmentsStore.departments.length }}</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-primary-100 flex items-center justify-center">
              <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-xl border border-dark-100 p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-dark-500 uppercase tracking-wider">Départements actifs</p>
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
              <p class="text-xs text-dark-500 uppercase tracking-wider">Agents total</p>
              <p class="text-2xl font-semibold text-dark-900 mt-1">{{ totalAgentsCount }}</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-secondary-100 flex items-center justify-center">
              <svg class="w-5 h-5 text-secondary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="departmentsStore.loading" class="flex items-center justify-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-2 border-primary-400 border-t-transparent"></div>
      </div>

      <!-- Grid -->
      <div v-else-if="departmentsStore.departments.length === 0" class="text-center py-12 bg-white rounded-xl border border-dark-100">
        <svg class="w-12 h-12 mx-auto text-dark-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
        </svg>
        <p class="text-dark-500">Aucun département trouvé</p>
        <button
          @click="openCreateModal"
          class="mt-3 text-primary-500 hover:text-primary-600 text-sm font-medium"
        >
          Créer le premier département
        </button>
      </div>

      <!-- Cards Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <div
          v-for="department in departmentsStore.departments"
          :key="department.id"
          class="bg-white rounded-xl border border-dark-100 overflow-hidden hover:shadow-md transition-all duration-200 group"
        >
          <div class="p-5">
            <div class="flex items-start justify-between mb-3">
              <div class="flex items-center gap-3">
                <div
                  class="w-10 h-10 rounded-lg flex items-center justify-center text-white font-semibold"
                  :style="{ backgroundColor: department.color || '#474665' }"
                >
                  {{ department.code?.substring(0, 2).toUpperCase() }}
                </div>
                <div>
                  <h3 class="font-semibold text-dark-900">{{ department.name }}</h3>
                  <p class="text-xs text-dark-500">{{ department.code }}</p>
                </div>
              </div>
              <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition">
                <button
                  @click="openEditModal(department)"
                  class="p-1.5 text-dark-400 hover:text-primary-500 rounded-lg hover:bg-dark-50 transition"
                  title="Modifier"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </button>
                <button
                  @click="confirmDelete(department)"
                  class="p-1.5 text-dark-400 hover:text-red-500 rounded-lg hover:bg-red-50 transition"
                  title="Supprimer"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </div>

            <p v-if="department.description" class="text-sm text-dark-600 mt-2 line-clamp-2">
              {{ department.description }}
            </p>

            <div class="flex items-center justify-between mt-4 pt-3 border-t border-dark-100">
              <div class="flex items-center gap-2">
                <div class="flex items-center gap-1">
                  <svg class="w-4 h-4 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                  </svg>
                  <span class="text-sm text-dark-600">{{ department.agents_count || 0 }} agent(s)</span>
                </div>
              </div>
              <span
                :class="department.is_active ? 'bg-green-100 text-green-700' : 'bg-dark-100 text-dark-600'"
                class="text-xs font-medium px-2 py-1 rounded-full"
              >
                {{ department.is_active ? 'Actif' : 'Inactif' }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Création/Édition -->
    <div v-if="modalOpen" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="closeModal">
      <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
        <div class="flex items-center justify-between p-5 border-b border-dark-100">
          <h2 class="text-lg font-semibold text-dark-900">{{ editingDepartment ? 'Modifier le département' : 'Nouveau département' }}</h2>
          <button @click="closeModal" class="text-dark-400 hover:text-dark-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <form @submit.prevent="saveDepartment" class="p-5 space-y-4">
          <AppInput
            v-model="form.name"
            label="Nom du département"
            required
            :error="ve.name?.[0]"
          />

          <AppInput
            v-model="form.code"
            label="Code"
            required
            placeholder="ex: RH, IT, MKT"
            :error="ve.code?.[0]"
          />

          <AppInput
            v-model="form.color"
            label="Couleur"
            type="color"
            :error="ve.color?.[0]"
          />

          <div v-if="editingDepartment" class="flex items-center gap-3 p-3 bg-dark-50 rounded-lg">
            <label class="flex items-center gap-2 cursor-pointer">
              <input
                v-model="form.is_active"
                type="checkbox"
                class="rounded border-dark-300 text-primary-400 focus:ring-primary-400"
              />
              <span class="text-sm text-dark-700">Département actif</span>
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
            Êtes-vous sûr de vouloir supprimer le département <strong>{{ departmentToDelete?.name }}</strong> ?
          </p>
          <p class="text-sm text-red-500 mt-2">
            Cette action est irréversible. Les agents associés ne seront pas supprimés.
          </p>
          <div class="flex justify-end gap-3 mt-5">
            <AppButton type="button" variant="secondary" @click="closeDeleteModal">Annuler</AppButton>
            <AppButton type="button" variant="danger" @click="deleteDepartment">Supprimer</AppButton>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import AppLayout from '@/components/layout/AppLayout.vue'
import AppInput from '@/components/common/AppInput.vue'
import AppButton from '@/components/common/AppButton.vue'
import { useDepartmentsStore } from '@/stores/departments'
import { useApi } from '@/composables/useApi'
import type { Department, DepartmentForm } from '@/types'

const departmentsStore = useDepartmentsStore()
const { execute, validationErrors: ve } = useApi()

const modalOpen = ref(false)
const deleteModalOpen = ref(false)
const editingDepartment = ref<Department | null>(null)
const departmentToDelete = ref<Department | null>(null)
const saving = ref(false)

const form = ref<DepartmentForm>({
  name: '',
  code: '',
  color: '#474665',
  description: '',
  is_active: true
})

const activeCount = computed(() => departmentsStore.activeDepartments.length)
const totalAgentsCount = computed(() => {
  return departmentsStore.departments.reduce((sum, dept) => sum + (dept.agents_count || 0), 0)
})

async function loadDepartments() {
  await execute(() => departmentsStore.fetchDepartments())
}

function openCreateModal() {
  editingDepartment.value = null
  form.value = {
    name: '',
    code: '',
    color: '#474665',
    description: '',
    is_active: true
  }
  modalOpen.value = true
}

function openEditModal(department: Department) {
  editingDepartment.value = department
  form.value = {
    name: department.name,
    code: department.code,
    color: department.color || '#474665',
    description: department.description || '',
    is_active: department.is_active
  }
  modalOpen.value = true
}

async function saveDepartment() {
  saving.value = true
  try {
    if (editingDepartment.value) {
      await departmentsStore.updateDepartment(editingDepartment.value.id, form.value)
    } else {
      await departmentsStore.createDepartment(form.value)
    }
    closeModal()
  } catch (error) {
    console.error('Erreur lors de la sauvegarde:', error)
  } finally {
    saving.value = false
  }
}

function confirmDelete(department: Department) {
  departmentToDelete.value = department
  deleteModalOpen.value = true
}

async function deleteDepartment() {
  if (departmentToDelete.value) {
    await departmentsStore.deleteDepartment(departmentToDelete.value.id)
    closeDeleteModal()
  }
}

function closeModal() {
  modalOpen.value = false
  editingDepartment.value = null
}

function closeDeleteModal() {
  deleteModalOpen.value = false
  departmentToDelete.value = null
}

onMounted(() => {
  loadDepartments()
})
</script>
