// stores/leaves.ts
import { defineStore } from 'pinia'
import type { Leave, LeaveForm, LeaveBalance, LeaveFilters } from '@/types'
import apiClient from '@/api/client'
import { computed, ref } from 'vue'

export const useLeavesStore = defineStore('leaves', () => {
  // State
  const leaves = ref<Leave[]>([])
  const currentLeave = ref<Leave | null>(null)
  const pagination = ref({
    currentPage: 1,
    lastPage: 1,
    total: 0,
    perPage: 20
  })
  const balance = ref<LeaveBalance | null>(null)

  // Actions
  async function fetchLeaves(filters?: LeaveFilters) {
    const { data } = await apiClient.get('/leaves', { params: filters })
    leaves.value = data.data
    pagination.value = {
      currentPage: data.current_page,
      lastPage: data.last_page,
      total: data.total,
      perPage: data.per_page
    }
    return data
  }

  async function fetchLeave(id: number) {
    const { data } = await apiClient.get(`/leaves/${id}`)
    currentLeave.value = data.data
    return data.data
  }

  async function createLeave(payload: LeaveForm) {
    const { data } = await apiClient.post('/leaves', payload)
    return data.data
  }

  async function deleteLeave(id: number) {
    await apiClient.delete(`/leaves/${id}`)
  }

  async function approveLeave(id: number) {
    const { data } = await apiClient.post(`/leaves/${id}/approve`)
    // Mettre à jour le leave dans le store si présent
    if (currentLeave.value?.id === id) {
      currentLeave.value = data.leave
    }
    // Mettre à jour dans la liste
    const index = leaves.value.findIndex(l => l.id === id)
    if (index !== -1) {
      leaves.value[index] = data.leave
    }
    return data
  }

  async function refuseLeave(id: number, reason: string) {
    const { data } = await apiClient.post(`/leaves/${id}/refuse`, { reason })
    if (currentLeave.value?.id === id) {
      currentLeave.value = data.leave
    }
    const index = leaves.value.findIndex(l => l.id === id)
    if (index !== -1) {
      leaves.value[index] = data.leave
    }
    return data
  }

  async function fetchBalance(agentId: number, year: number) {
    const { data } = await apiClient.get('/leaves/balance', {
      params: { agent_id: agentId, year }
    })
    balance.value = data
    return data
  }

  // Getters
  const pendingLeaves = computed(() =>
    leaves.value.filter(leave => leave.status === 'pending')
  )

  const approvedLeaves = computed(() =>
    leaves.value.filter(leave => leave.status === 'approved')
  )

  const refusedLeaves = computed(() =>
    leaves.value.filter(leave => leave.status === 'refused')
  )

  return {
    // State
    leaves,
    currentLeave,
    pagination,
    balance,

    // Getters
    pendingLeaves,
    approvedLeaves,
    refusedLeaves,

    // Actions
    fetchLeaves,
    fetchLeave,
    createLeave,
    deleteLeave,
    approveLeave,
    refuseLeave,
    fetchBalance
  }
})
