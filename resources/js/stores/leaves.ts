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

    async function fetchLeaves(filters?: LeaveFilters) {
        const { data } = await apiClient.get("/leaves", { params: filters });
        leaves.value = data.data;
        pagination.value = {
            currentPage: data.meta?.current_page ?? data.current_page,
            lastPage: data.meta?.last_page ?? data.last_page,
            total: data.meta?.total ?? data.total,
            perPage: data.meta?.per_page ?? data.per_page,
        };
        return data;
    }

    async function fetchLeave(id: number) {
        const { data } = await apiClient.get(`/leaves/${id}`);
        currentLeave.value = data.data;
        return data.data;
    }

    async function createLeave(payload: LeaveForm) {
        const { data } = await apiClient.post("/leaves", payload);
        leaves.value.unshift(data.data); // ← ajout en tête de liste
        return data.data; // ← retourne bien l'objet pour useApi
    }

    async function deleteLeave(id: number) {
        await apiClient.delete(`/leaves/${id}`);
        leaves.value = leaves.value.filter((l) => l.id !== id);
    }

    async function approveLeave(id: number) {
        const { data } = await apiClient.post(`/leaves/${id}/approve`);
        _updateInList(data.leave);
        return data;
    }

    async function refuseLeave(id: number, reason: string) {
        const { data } = await apiClient.post(`/leaves/${id}/refuse`, {
            reason,
        });
        _updateInList(data.leave);
        return data;
    }

    async function fetchBalance(agentId: number, year: number) {
        const { data } = await apiClient.get("/leaves/balance", {
            params: { agent_id: agentId, year },
        });
        balance.value = data;
        return data;
    }

    // Helper interne
    function _updateInList(leave: Leave) {
        if (currentLeave.value?.id === leave.id) currentLeave.value = leave;
        const i = leaves.value.findIndex((l) => l.id === leave.id);
        if (i !== -1) leaves.value[i] = leave;
    }

    const pendingLeaves = computed(() =>
        leaves.value.filter((l) => l.status === "pending"),
    );
    const approvedLeaves = computed(() =>
        leaves.value.filter((l) => l.status === "approved"),
    );
    const refusedLeaves = computed(() =>
        leaves.value.filter((l) => l.status === "refused"),
    );

    return {
        leaves,
        currentLeave,
        pagination,
        balance,
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
