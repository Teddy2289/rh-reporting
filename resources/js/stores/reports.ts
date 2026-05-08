// stores/reports.ts
import { defineStore } from 'pinia'
import apiClient from '@/api/client'
import type { HourReport, DashboardStats } from '@/types'

interface ReportsState {
  hourReport: HourReport | null
  dashboardStats: DashboardStats | null
  teamReports: HourReport[]
  exportData: any[]
  loading: boolean
  error: string | null
}

export const useReportsStore = defineStore('reports', {
  state: (): ReportsState => ({
    hourReport: null,
    dashboardStats: null,
    teamReports: [],
    exportData: [],
    loading: false,
    error: null
  }),

  getters: {
    hasHourReport: (state) => !!state.hourReport,
    hasDashboardStats: (state) => !!state.dashboardStats,
    totalTeamHours: (state) => {
      return state.teamReports.reduce((sum, report) => sum + (report.worked_hours || 0), 0)
    },
    totalTeamOvertime: (state) => {
      return state.teamReports.reduce((sum, report) => sum + (report.overtime_hours || 0), 0)
    }
  },

  actions: {
    async fetchHourReport(params: {
      agent_id: number
      year: number
      month?: number
      date_from?: string
      date_to?: string
    }) {
      this.loading = true
      this.error = null

      try {
        const { data } = await apiClient.get('/reports/hours', { params })
        this.hourReport = data
        return data
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Erreur lors du chargement du rapport'
        throw err
      } finally {
        this.loading = false
      }
    },

    async fetchDashboardStats(year: number, month: number) {
      this.loading = true
      this.error = null

      try {
        const { data } = await apiClient.get('/reports/dashboard', { params: { year, month } })
        this.dashboardStats = data
        return data
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Erreur lors du chargement des statistiques'
        throw err
      } finally {
        this.loading = false
      }
    },

    async fetchTeamReport(managerId: number, year: number, month: number) {
      this.loading = true
      this.error = null

      try {
        const { data } = await apiClient.get('/reports/team', {
          params: { manager_id: managerId, year, month }
        })
        this.teamReports = data.team_reports || []
        return data.team_reports || []
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Erreur lors du chargement du rapport d\'équipe'
        throw err
      } finally {
        this.loading = false
      }
    },

    async exportReport(params: { year: number; month?: number; format?: string }) {
      this.loading = true
      this.error = null

      try {
        const { data } = await apiClient.get('/reports/export', { params })
        this.exportData = data.export_data || []
        return data.export_data || []
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Erreur lors de l\'export'
        throw err
      } finally {
        this.loading = false
      }
    },

    reset() {
      this.hourReport = null
      this.dashboardStats = null
      this.teamReports = []
      this.exportData = []
      this.loading = false
      this.error = null
    }
  }
})
