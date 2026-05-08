// stores/clients.ts
import { defineStore } from 'pinia'
import apiClient from '@/api/client'
import type { Client, ClientForm } from '@/types'

interface ClientsState {
  clients: Client[]
  currentClient: Client | null
  loading: boolean
  error: string | null
}

export const useClientsStore = defineStore('clients', {
  state: (): ClientsState => ({
    clients: [],
    currentClient: null,
    loading: false,
    error: null
  }),

  getters: {
    activeClients: (state) => state.clients.filter(c => c.is_active),
    inactiveClients: (state) => state.clients.filter(c => !c.is_active),
    clientsCount: (state) => state.clients.length,
    getClientById: (state) => (id: number) => state.clients.find(c => c.id === id)
  },

  actions: {
    async fetchClients(activeOnly: boolean = true) {
      this.loading = true
      this.error = null

      try {
        const { data } = await apiClient.get('/clients', { params: { active_only: activeOnly } })
        this.clients = data.data
        return data.data
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Erreur lors du chargement des clients'
        throw err
      } finally {
        this.loading = false
      }
    },

    async fetchClient(id: number) {
      this.loading = true
      this.error = null

      try {
        const { data } = await apiClient.get(`/clients/${id}`)
        this.currentClient = data.data
        return data.data
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Erreur lors du chargement du client'
        throw err
      } finally {
        this.loading = false
      }
    },

    async createClient(payload: ClientForm) {
      this.loading = true
      this.error = null

      try {
        const { data } = await apiClient.post('/clients', payload)
        this.clients.push(data.data)
        return data.data
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Erreur lors de la création du client'
        throw err
      } finally {
        this.loading = false
      }
    },

    async updateClient(id: number, payload: Partial<ClientForm>) {
      this.loading = true
      this.error = null

      try {
        const { data } = await apiClient.put(`/clients/${id}`, payload)
        const index = this.clients.findIndex(c => c.id === id)
        if (index !== -1) {
          this.clients[index] = data.data
        }
        if (this.currentClient?.id === id) {
          this.currentClient = data.data
        }
        return data.data
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Erreur lors de la mise à jour du client'
        throw err
      } finally {
        this.loading = false
      }
    },

    async deleteClient(id: number) {
      this.loading = true
      this.error = null

      try {
        await apiClient.delete(`/clients/${id}`)
        this.clients = this.clients.filter(c => c.id !== id)
        if (this.currentClient?.id === id) {
          this.currentClient = null
        }
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Erreur lors de la suppression du client'
        throw err
      } finally {
        this.loading = false
      }
    },

    reset() {
      this.clients = []
      this.currentClient = null
      this.loading = false
      this.error = null
    }
  }
})
