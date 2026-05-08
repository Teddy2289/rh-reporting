// stores/departments.ts
import { defineStore } from 'pinia'
import apiClient from '@/api/client'
import type { Department, DepartmentForm } from '@/types'

interface DepartmentsState {
  departments: Department[]
  currentDepartment: Department | null
  loading: boolean
  error: string | null
}

export const useDepartmentsStore = defineStore('departments', {
  state: (): DepartmentsState => ({
    departments: [],
    currentDepartment: null,
    loading: false,
    error: null
  }),

  getters: {
    activeDepartments: (state) => state.departments.filter(d => d.is_active),
    inactiveDepartments: (state) => state.departments.filter(d => !d.is_active),
    departmentsCount: (state) => state.departments.length
  },

  actions: {
    async fetchDepartments() {
      this.loading = true
      this.error = null

      try {
        const { data } = await apiClient.get('/departments')
        this.departments = data.data
        return data.data
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Erreur lors du chargement des départements'
        throw err
      } finally {
        this.loading = false
      }
    },

    async createDepartment(payload: DepartmentForm) {
      this.loading = true
      this.error = null

      try {
        const { data } = await apiClient.post('/departments', payload)
        this.departments.push(data.data)
        return data.data
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Erreur lors de la création du département'
        throw err
      } finally {
        this.loading = false
      }
    },

    async updateDepartment(id: number, payload: Partial<DepartmentForm>) {
      this.loading = true
      this.error = null

      try {
        const { data } = await apiClient.put(`/departments/${id}`, payload)
        const index = this.departments.findIndex(d => d.id === id)
        if (index !== -1) {
          this.departments[index] = data.data
        }
        if (this.currentDepartment?.id === id) {
          this.currentDepartment = data.data
        }
        return data.data
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Erreur lors de la mise à jour du département'
        throw err
      } finally {
        this.loading = false
      }
    },

    async deleteDepartment(id: number) {
      this.loading = true
      this.error = null

      try {
        await apiClient.delete(`/departments/${id}`)
        this.departments = this.departments.filter(d => d.id !== id)
        if (this.currentDepartment?.id === id) {
          this.currentDepartment = null
        }
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Erreur lors de la suppression du département'
        throw err
      } finally {
        this.loading = false
      }
    },

    reset() {
      this.departments = []
      this.currentDepartment = null
      this.loading = false
      this.error = null
    }
  }
})
