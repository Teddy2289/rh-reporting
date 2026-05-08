import apiClient from './client'
import type {
  Department, DepartmentForm,
  Client, ClientForm,
  Mission, MissionForm,
  Leave, LeaveForm, LeaveBalance, LeaveFilters,
  PlanningSlot, PlanningSlotForm, PlanningFilters, GeneratePlanningForm, BulkUpdateForm,
  HourReport, DashboardStats,
  PaginatedResponse,
} from '@/types'

// ─── Departments ──────────────────────────────────────────────────────────────

export const departmentsApi = {
  index(): Promise<{ data: { data: Department[] } }> {
    return apiClient.get('/departments')
  },
  store(payload: DepartmentForm): Promise<{ data: { data: Department } }> {
    return apiClient.post('/departments', payload)
  },
  update(id: number, payload: Partial<DepartmentForm>): Promise<{ data: { data: Department } }> {
    return apiClient.put(`/departments/${id}`, payload)
  },
  destroy(id: number): Promise<void> {
    return apiClient.delete(`/departments/${id}`)
  },
}

// ─── Clients ──────────────────────────────────────────────────────────────────

export const clientsApi = {
  index(activeOnly = true): Promise<{ data: { data: Client[] } }> {
    return apiClient.get('/clients', { params: { active_only: activeOnly } })
  },
  store(payload: ClientForm): Promise<{ data: { data: Client } }> {
    return apiClient.post('/clients', payload)
  },
  update(id: number, payload: Partial<ClientForm>): Promise<{ data: { data: Client } }> {
    return apiClient.put(`/clients/${id}`, payload)
  },
  destroy(id: number): Promise<void> {
    return apiClient.delete(`/clients/${id}`)
  },
}

// ─── Missions ─────────────────────────────────────────────────────────────────

export const missionsApi = {
  index(clientId?: number, activeOnly = true): Promise<{ data: { data: Mission[] } }> {
    return apiClient.get('/missions', { params: { client_id: clientId, active_only: activeOnly } })
  },
  store(payload: MissionForm): Promise<{ data: { data: Mission } }> {
    return apiClient.post('/missions', payload)
  },
  update(id: number, payload: Partial<MissionForm>): Promise<{ data: { data: Mission } }> {
    return apiClient.put(`/missions/${id}`, payload)
  },
  destroy(id: number): Promise<void> {
    return apiClient.delete(`/missions/${id}`)
  },
}

// ─── Leaves ───────────────────────────────────────────────────────────────────

export const leavesApi = {
  index(filters: LeaveFilters = {}): Promise<{ data: PaginatedResponse<Leave> }> {
    return apiClient.get('/leaves', { params: filters })
  },
  show(id: number): Promise<{ data: { data: Leave } }> {
    return apiClient.get(`/leaves/${id}`)
  },
  store(payload: LeaveForm): Promise<{ data: { data: Leave } }> {
    return apiClient.post('/leaves', payload)
  },
  approve(id: number): Promise<{ data: { message: string; leave: Leave } }> {
    return apiClient.post(`/leaves/${id}/approve`)
  },
  refuse(id: number, reason: string): Promise<{ data: { message: string; leave: Leave } }> {
    return apiClient.post(`/leaves/${id}/refuse`, { reason })
  },
  destroy(id: number): Promise<void> {
    return apiClient.delete(`/leaves/${id}`)
  },
  balance(agentId: number, year: number): Promise<{ data: LeaveBalance }> {
    return apiClient.get('/leaves/balance', { params: { agent_id: agentId, year } })
  },
}

// ─── Planning ─────────────────────────────────────────────────────────────────

export const planningApi = {
  index(filters: PlanningFilters = {}): Promise<{ data: { data: PlanningSlot[] } }> {
    return apiClient.get('/planning', { params: filters })
  },
  show(id: number): Promise<{ data: { data: PlanningSlot } }> {
    return apiClient.get(`/planning/${id}`)
  },
  store(payload: PlanningSlotForm): Promise<{ data: { data: PlanningSlot } }> {
    return apiClient.post('/planning', payload)
  },
  update(id: number, payload: Partial<PlanningSlotForm>): Promise<{ data: { data: PlanningSlot } }> {
    return apiClient.put(`/planning/${id}`, payload)
  },
  destroy(id: number): Promise<void> {
    return apiClient.delete(`/planning/${id}`)
  },
  generate(payload: GeneratePlanningForm): Promise<{ data: { message: string; results: any[] } }> {
    return apiClient.post('/planning/generate', payload)
  },
  bulkUpdate(payload: BulkUpdateForm): Promise<{ data: { message: string; count: number } }> {
    return apiClient.post('/planning/bulk', payload)
  },
}

// ─── Reports ──────────────────────────────────────────────────────────────────

export const reportsApi = {
  agentHours(params: {
    agent_id: number
    year: number
    month?: number
    date_from?: string
    date_to?: string
  }): Promise<{ data: HourReport }> {
    return apiClient.get('/reports/hours', { params })
  },

  dashboard(year: number, month: number): Promise<{ data: DashboardStats }> {
    return apiClient.get('/reports/dashboard', { params: { year, month } })
  },

  teamReport(managerId: number, year: number, month: number): Promise<{ data: { team_reports: HourReport[] } }> {
    return apiClient.get('/reports/team', { params: { manager_id: managerId, year, month } })
  },

  export(year: number, month?: number): Promise<{ data: { export_data: any[] } }> {
    return apiClient.get('/reports/export', { params: { year, month } })
  },
}
