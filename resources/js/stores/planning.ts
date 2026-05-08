// stores/planning.ts
import { defineStore } from 'pinia'
import apiClient from '@/api/client'
import type {
  PlanningSlot,
  PlanningSlotForm,
  PlanningFilters,
  GeneratePlanningForm,
  BulkUpdateForm,
  HourLog
} from '@/types'

export const usePlanningStore = defineStore('planning', {
  state: () => ({
    slots: [] as PlanningSlot[],
    currentSlot: null as PlanningSlot | null,
    hourLogs: [] as HourLog[],
    loading: false,
    error: null as string | null,
    generationResults: null as { agent: string; created: number }[] | null
  }),

  getters: {
    slotsByDate: (state) => (date: string) =>
      state.slots.filter(slot => slot.date === date),

    slotsByAgent: (state) => (agentId: number) =>
      state.slots.filter(slot => slot.agent_id === agentId),

    slotsByAgentAndDate: (state) => (agentId: number, date: string) =>
      state.slots.filter(slot => slot.agent_id === agentId && slot.date === date),

    slotsByType: (state) => (type: string) =>
      state.slots.filter(slot => slot.type === type),

    workedSlots: (state) =>
      state.slots.filter(slot => slot.type === 'work'),

    leaveSlots: (state) =>
      state.slots.filter(slot => slot.type === 'leave'),

    totalWorkedMinutesForAgent: (state) => (agentId: number) =>
      state.slots
        .filter(slot => slot.agent_id === agentId && slot.type === 'work')
        .reduce((total, slot) => total + slot.duration_minutes, 0),

    totalWorkedHoursForAgent: (state) => (agentId: number) =>
      Math.round((state.slots
        .filter(slot => slot.agent_id === agentId && slot.type === 'work')
        .reduce((total, slot) => total + slot.duration_minutes, 0) / 60) * 100) / 100,

    // Récupérer les heures par jour pour un agent
    hourLogsByAgent: (state) => (agentId: number) =>
      state.hourLogs.filter(log => log.agent_id === agentId),

    hourLogsByDate: (state) => (date: string) =>
      state.hourLogs.filter(log => log.date === date),

    // Vérifier si un créneau est en conflit
    hasConflict: (state) => (agentId: number, date: string, timeStart: string, timeEnd: string, excludeId?: number) => {
      return state.slots.some(slot =>
        slot.agent_id === agentId &&
        slot.date === date &&
        slot.id !== excludeId &&
        ((timeStart >= slot.time_start && timeStart < slot.time_end) ||
         (timeEnd > slot.time_start && timeEnd <= slot.time_end) ||
         (timeStart <= slot.time_start && timeEnd >= slot.time_end))
      )
    }
  },

  actions: {
    async fetchSlots(filters?: PlanningFilters) {
      this.loading = true
      this.error = null

      try {
        const { data } = await apiClient.get('/planning', { params: filters })
        this.slots = data.data
        return data.data
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Erreur lors du chargement du planning'
        throw err
      } finally {
        this.loading = false
      }
    },

    async fetchSlot(id: number) {
      this.loading = true
      this.error = null

      try {
        const { data } = await apiClient.get(`/planning/${id}`)
        this.currentSlot = data.data
        return data.data
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Erreur lors du chargement du créneau'
        throw err
      } finally {
        this.loading = false
      }
    },

    async createSlot(payload: PlanningSlotForm) {
      this.loading = true
      this.error = null

      try {
        const { data } = await apiClient.post('/planning', payload)
        this.slots.push(data.data)
        return data.data
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Erreur lors de la création du créneau'
        throw err
      } finally {
        this.loading = false
      }
    },

    async updateSlot(id: number, payload: Partial<PlanningSlotForm>) {
      this.loading = true
      this.error = null

      try {
        const { data } = await apiClient.put(`/planning/${id}`, payload)
        const index = this.slots.findIndex(s => s.id === id)
        if (index !== -1) {
          this.slots[index] = data.data
        }
        if (this.currentSlot?.id === id) {
          this.currentSlot = data.data
        }
        return data.data
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Erreur lors de la mise à jour du créneau'
        throw err
      } finally {
        this.loading = false
      }
    },

    async deleteSlot(id: number) {
      this.loading = true
      this.error = null

      try {
        await apiClient.delete(`/planning/${id}`)
        this.slots = this.slots.filter(s => s.id !== id)
        if (this.currentSlot?.id === id) {
          this.currentSlot = null
        }
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Erreur lors de la suppression du créneau'
        throw err
      } finally {
        this.loading = false
      }
    },

    async generatePlanning(payload: GeneratePlanningForm) {
      this.loading = true
      this.error = null

      try {
        const { data } = await apiClient.post('/planning/generate', payload)
        this.generationResults = data.results
        return data
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Erreur lors de la génération du planning'
        throw err
      } finally {
        this.loading = false
      }
    },

    async bulkUpdate(payload: BulkUpdateForm) {
      this.loading = true
      this.error = null

      try {
        const { data } = await apiClient.post('/planning/bulk', payload)
        // Rafraîchir les slots modifiés si nécessaire
        if (payload.slot_ids.length > 0 && payload.slot_ids.length <= 100) {
          await this.fetchSlots({ slot_ids: payload.slot_ids.join(',') })
        }
        return data
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Erreur lors de la mise à jour en masse'
        throw err
      } finally {
        this.loading = false
      }
    },

    async fetchHourLogs(agentId: number, dateFrom: string, dateTo: string) {
      this.loading = true
      this.error = null

      try {
        const { data } = await apiClient.get('/reports/hours', {
          params: { agent_id: agentId, date_from: dateFrom, date_to: dateTo }
        })
        this.hourLogs = data.data
        return data.data
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Erreur lors du chargement des heures'
        throw err
      } finally {
        this.loading = false
      }
    },

    // Helpers
    async fetchMonthPlanning(agentId: number, year: number, month: number) {
      return await this.fetchSlots({
        agent_id: agentId,
        year,
        month
      })
    },

    async fetchPeriodPlanning(agentId: number, dateFrom: string, dateTo: string) {
      return await this.fetchSlots({
        agent_id: agentId,
        date_from: dateFrom,
        date_to: dateTo
      })
    },

    async fetchDayPlanning(agentId: number, date: string) {
      return await this.fetchSlots({
        agent_id: agentId,
        date
      })
    },

    async fetchWeekPlanning(agentId: number, startDate: string) {
      const endDate = new Date(startDate)
      endDate.setDate(endDate.getDate() + 6)
      return await this.fetchPeriodPlanning(
        agentId,
        startDate,
        endDate.toISOString().split('T')[0]
      )
    },

    // Reset store
    reset() {
      this.slots = []
      this.currentSlot = null
      this.hourLogs = []
      this.loading = false
      this.error = null
      this.generationResults = null
    }
  }
})
