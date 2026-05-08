// stores/agents.ts
import { defineStore } from "pinia";
import { agentsApi } from "@/api/agents";
import type { Agent, AgentForm, AgentFilters } from "@/types";
import { ref } from "vue";

// Définir le type de pagination
interface PaginationState {
  currentPage: number;
  lastPage: number;
  total: number;
  perPage: number;
  from: number;
  to: number;
}

export const useAgentsStore = defineStore("agents", () => {
  const agents = ref<Agent[]>([]);
  const currentAgent = ref<Agent | null>(null);
  const pagination = ref<PaginationState>({
    currentPage: 1,
    lastPage: 1,
    total: 0,
    perPage: 15,
    from: 0,
    to: 0
  });

  async function fetchAgents(filters?: AgentFilters) {
    const { data } = await agentsApi.index(filters);
    agents.value = data.data;
    pagination.value = {
      currentPage: data.meta.current_page,
      lastPage: data.meta.last_page,
      total: data.meta.total,
      perPage: data.meta.per_page,
      from: data.meta.from,
      to: data.meta.to,
    };
    return data.data;
  }

  async function fetchAgent(id: number) {
    const { data } = await agentsApi.show(id);
    currentAgent.value = data.data;
    return data.data;
  }

  async function createAgent(payload: AgentForm) {
    const { data } = await agentsApi.store(payload);
    agents.value.push(data.data); // Ajouter à la liste locale
    return data.data;
  }

  async function updateAgent(id: number, payload: Partial<AgentForm>) {
    const { data } = await agentsApi.update(id, payload);
    currentAgent.value = data.data;
    // Mettre à jour dans la liste
    const index = agents.value.findIndex(a => a.id === id);
    if (index !== -1) {
      agents.value[index] = data.data;
    }
    return data.data;
  }

  async function deleteAgent(id: number) {
    await agentsApi.destroy(id);
    agents.value = agents.value.filter(a => a.id !== id);
  }

  return {
    agents,
    currentAgent,
    pagination,
    fetchAgents,
    fetchAgent,
    createAgent,
    updateAgent,
    deleteAgent,
  };
});
