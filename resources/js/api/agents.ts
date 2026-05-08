import apiClient from './client'
import type { Agent, AgentForm, AgentFilters, PaginatedResponse } from '@/types'

export const agentsApi = {
  index(filters: AgentFilters = {}): Promise<{ data: PaginatedResponse<Agent> }> {
    return apiClient.get('/agents', { params: filters })
  },

  show(id: number): Promise<{ data: { data: Agent } }> {
    return apiClient.get(`/agents/${id}`)
  },

  store(payload: AgentForm): Promise<{ data: { data: Agent } }> {
    return apiClient.post('/agents', payload)
  },

  update(id: number, payload: Partial<AgentForm>): Promise<{ data: { data: Agent } }> {
    return apiClient.put(`/agents/${id}`, payload)
  },

  destroy(id: number): Promise<void> {
    return apiClient.delete(`/agents/${id}`)
  },
}
