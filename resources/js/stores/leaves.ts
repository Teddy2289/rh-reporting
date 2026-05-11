// stores/leaves.ts
import { defineStore } from "pinia";
import type { Leave, LeaveForm, LeaveBalance, LeaveFilters } from "@/types";
import apiClient from "@/api/client";
import { computed, ref } from "vue";

export const useLeavesStore = defineStore("leaves", () => {
    const leaves = ref<Leave[]>([]);
    const currentLeave = ref<Leave | null>(null);
    const pagination = ref({
        currentPage: 1,
        lastPage: 1,
        total: 0,
        perPage: 20,
    });
    const balance = ref<LeaveBalance | null>(null);
    const loading = ref(false);
    const error = ref<string | null>(null);

    async function fetchLeaves(filters?: LeaveFilters) {
        loading.value = true;
        error.value = null;

        try {
            const { data } = await apiClient.get("/leaves", { params: filters });
            leaves.value = data.data;
            pagination.value = {
                currentPage: data.meta?.current_page ?? data.current_page,
                lastPage: data.meta?.last_page ?? data.last_page,
                total: data.meta?.total ?? data.total,
                perPage: data.meta?.per_page ?? data.per_page,
            };
            return data;
        } catch (err: any) {
            error.value = err.response?.data?.message || "Erreur de chargement";
            throw err;
        } finally {
            loading.value = false;
        }
    }

    async function fetchLeave(id: number) {
        loading.value = true;

        try {
            const { data } = await apiClient.get(`/leaves/${id}`);
            currentLeave.value = data.data;
            return data.data;
        } finally {
            loading.value = false;
        }
    }

    async function createLeave(payload: LeaveForm) {
        loading.value = true;
        error.value = null;

        try {
            const { data } = await apiClient.post("/leaves", payload);
            const leave = data.data ?? data;

            if (leave?.id) {
                leaves.value.unshift(leave);
            }

            // Recharger le solde
            if (payload.agent_id && payload.date_start) {
                const year = new Date(payload.date_start).getFullYear();
                await fetchBalance(payload.agent_id, year);
            }

            return leave;
        } catch (err: any) {
            error.value = err.response?.data?.message || "Erreur lors de la demande";
            throw err;
        } finally {
            loading.value = false;
        }
    }

    async function deleteLeave(id: number) {
        loading.value = true;

        try {
            await apiClient.delete(`/leaves/${id}`);
            const deletedLeave = leaves.value.find(l => l.id === id);
            leaves.value = leaves.value.filter((l) => l.id !== id);

            // Recharger le solde
            if (deletedLeave?.agent_id && deletedLeave?.date_start) {
                const year = new Date(deletedLeave.date_start).getFullYear();
                await fetchBalance(deletedLeave.agent_id, year);
            }
        } finally {
            loading.value = false;
        }
    }

    async function approveLeave(id: number) {
        loading.value = true;

        try {
            const { data } = await apiClient.post(`/leaves/${id}/approve`);
            const leave = data.leave?.data ?? data.leave;

            if (leave?.id) {
                _updateInList(leave);

                // Recharger le solde
                if (leave.agent_id && leave.date_start) {
                    const year = new Date(leave.date_start).getFullYear();
                    await fetchBalance(leave.agent_id, year);
                }
            }

            return data;
        } finally {
            loading.value = false;
        }
    }

    async function refuseLeave(id: number, reason: string) {
        loading.value = true;

        try {
            const { data } = await apiClient.post(`/leaves/${id}/refuse`, { reason });
            const leave = data.leave?.data ?? data.leave;

            if (leave?.id) {
                _updateInList(leave);

                // Recharger le solde
                if (leave.agent_id && leave.date_start) {
                    const year = new Date(leave.date_start).getFullYear();
                    await fetchBalance(leave.agent_id, year);
                }
            }

            return data;
        } finally {
            loading.value = false;
        }
    }

    async function fetchBalance(agentId: number, year: number) {
        loading.value = true;

        try {
            const { data } = await apiClient.get("/leaves/balance", {
                params: { agent_id: agentId, year },
            });
            balance.value = data;
            return data;
        } finally {
            loading.value = false;
        }
    }

    function _updateInList(leave: Leave) {
        if (currentLeave.value?.id === leave.id) currentLeave.value = leave;
        const index = leaves.value.findIndex((l) => l.id === leave.id);
        if (index !== -1) leaves.value[index] = leave;
    }

    const pendingLeaves = computed(() =>
        leaves.value.filter((l) => l.status.value === "pending")
    );
    const approvedLeaves = computed(() =>
        leaves.value.filter((l) => l.status.value === "approved")
    );
    const refusedLeaves = computed(() =>
        leaves.value.filter((l) => l.status.value === "refused")
    );

    return {
        leaves,
        currentLeave,
        pagination,
        balance,
        loading,
        error,
        pendingLeaves,
        approvedLeaves,
        refusedLeaves,
        fetchLeaves,
        fetchLeave,
        createLeave,
        deleteLeave,
        approveLeave,
        refuseLeave,
        fetchBalance,
    };
});
