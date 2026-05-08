// stores/missions.ts
import { defineStore } from 'pinia'
import apiClient from '@/api/client'
import type { Mission, MissionForm } from '@/types'

interface MissionsState {
  missions: Mission[]
  currentMission: Mission | null
  loading: boolean
  error: string | null
}

export const useMissionsStore = defineStore('missions', {
  state: (): MissionsState => ({
    missions: [],
    currentMission: null,
    loading: false,
    error: null
  }),

  getters: {
    activeMissions: (state) => state.missions.filter(m => m.is_active),
    inactiveMissions: (state) => state.missions.filter(m => !m.is_active),
    missionsCount: (state) => state.missions.length,
    getMissionsByClient: (state) => (clientId: number) =>
      state.missions.filter(m => m.client_id === clientId),
    getMissionsByClientAndActive: (state) => (clientId: number) =>
      state.missions.filter(m => m.client_id === clientId && m.is_active)
  },

  actions: {
    async fetchMissions(filters?: { client_id?: number; active_only?: boolean }) {
      this.loading = true
      this.error = null

      try {
        const { data } = await apiClient.get('/missions', { params: filters })
        this.missions = data.data
        return data.data
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Erreur lors du chargement des missions'
        throw err
      } finally {
        this.loading = false
      }
    },

    async fetchMission(id: number) {
      this.loading = true
      this.error = null

      try {
        const { data } = await apiClient.get(`/missions/${id}`)
        this.currentMission = data.data
        return data.data
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Erreur lors du chargement de la mission'
        throw err
      } finally {
        this.loading = false
      }
    },

    async createMission(payload: MissionForm) {
      this.loading = true
      this.error = null

      try {
        const { data } = await apiClient.post('/missions', payload)
        this.missions.push(data.data)
        return data.data
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Erreur lors de la création de la mission'
        throw err
      } finally {
        this.loading = false
      }
    },

    async updateMission(id: number, payload: Partial<MissionForm>) {
      this.loading = true
      this.error = null

      try {
        const { data } = await apiClient.put(`/missions/${id}`, payload)
        const index = this.missions.findIndex(m => m.id === id)
        if (index !== -1) {
          this.missions[index] = data.data
        }
        if (this.currentMission?.id === id) {
          this.currentMission = data.data
        }
        return data.data
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Erreur lors de la mise à jour de la mission'
        throw err
      } finally {
        this.loading = false
      }
    },

    async deleteMission(id: number) {
      this.loading = true
      this.error = null

      try {
        await apiClient.delete(`/missions/${id}`)
        this.missions = this.missions.filter(m => m.id !== id)
        if (this.currentMission?.id === id) {
          this.currentMission = null
        }
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Erreur lors de la suppression de la mission'
        throw err
      } finally {
        this.loading = false
      }
    },

    reset() {
      this.missions = []
      this.currentMission = null
      this.loading = false
      this.error = null
    }
  }
})
