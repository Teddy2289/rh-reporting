<template>
    <AppLayout>
        <div class="p-8 space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-dark-900">
                        Demandes de congés
                    </h1>
                    <p class="text-sm text-dark-500 mt-1">
                        Gérez les demandes de congés des agents
                    </p>
                </div>
                <RouterLink
                    :to="{ name: 'leaves.request' }"
                    class="inline-flex items-center gap-2 bg-primary-400 hover:bg-primary-500 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-all duration-200 shadow-sm hover:shadow"
                >
                    <svg
                        class="w-4 h-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 4v16m8-8H4"
                        />
                    </svg>
                    Nouvelle demande
                </RouterLink>
            </div>

            <!-- Filters -->
            <div
                class="flex flex-wrap items-center gap-3 p-4 bg-white rounded-xl border border-dark-100"
            >
                <div class="relative">
                    <svg
                        class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-dark-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                        />
                    </svg>
                    <input
                        v-model="filters.search"
                        type="search"
                        placeholder="Rechercher un agent..."
                        class="w-64 pl-9 pr-3 py-2 border border-dark-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent transition"
                        @input="debouncedFetch"
                    />
                </div>

                <select
                    v-model="filters.status"
                    class="px-3 py-2 border border-dark-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent transition bg-white"
                    @change="fetchLeaves"
                >
                    <option :value="undefined">Tous les statuts</option>
                    <option
                        v-for="(label, key) in LEAVE_STATUS_LABELS"
                        :key="key"
                        :value="key"
                    >
                        {{ label }}
                    </option>
                </select>

                <select
                    v-model="filters.year"
                    class="px-3 py-2 border border-dark-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent transition bg-white"
                    @change="fetchLeaves"
                >
                    <option v-for="y in years" :key="y" :value="y">
                        {{ y }}
                    </option>
                </select>

                <div class="flex-1"></div>

                <!-- Stats rapides -->
                <div class="flex gap-4 text-sm">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <span class="text-dark-600"
                            >En attente: {{ stats.pending }}</span
                        >
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                        <span class="text-dark-600"
                            >Approuvés: {{ stats.approved }}</span
                        >
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                        <span class="text-dark-600"
                            >Refusés: {{ stats.refused }}</span
                        >
                    </div>
                </div>
            </div>

            <!-- Loading -->
            <div v-if="loading" class="flex items-center justify-center py-12">
                <div
                    class="animate-spin rounded-full h-8 w-8 border-2 border-primary-400 border-t-transparent"
                ></div>
            </div>

            <!-- Table -->
            <div
                v-else-if="leavesStore.leaves.length === 0"
                class="text-center py-12 bg-white rounded-xl border border-dark-100"
            >
                <svg
                    class="w-12 h-12 mx-auto text-dark-300 mb-3"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                </svg>
                <p class="text-dark-500">Aucune demande de congé trouvée</p>
            </div>

            <div
                v-else
                class="bg-white rounded-xl border border-dark-100 overflow-hidden"
            >
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-dark-50 border-b border-dark-100">
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-dark-600 uppercase tracking-wider"
                                >
                                    Agent
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-dark-600 uppercase tracking-wider"
                                >
                                    Type
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-dark-600 uppercase tracking-wider"
                                >
                                    Période
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-dark-600 uppercase tracking-wider"
                                >
                                    Jours
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-dark-600 uppercase tracking-wider"
                                >
                                    Statut
                                </th>
                                <th
                                    class="px-6 py-4 text-right text-xs font-semibold text-dark-600 uppercase tracking-wider"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-dark-100">
                            <tr
                                v-for="leave in safeLeaves"
                                :key="leave.id"
                                class="hover:bg-dark-50/50 transition-colors duration-150 group"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center"
                                        >
                                            <span
                                                class="text-primary-600 text-xs font-medium"
                                            >
                                                {{
                                                    getInitials(
                                                        leave.agent?.full_name,
                                                    )
                                                }}
                                            </span>
                                        </div>
                                        <div>
                                            <div class="font-medium text-dark-900">
                                                {{ leave.agent?.full_name || "—" }}
                                            </div>
                                            <div class="text-xs text-dark-500">
                                                {{
                                                    leave.agent?.department?.name ||
                                                    "—"
                                                }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium"
                                        :class="getTypeClass(leave.type.value)"
                                    >
                                        {{ LEAVE_TYPE_LABELS[leave.type.value] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-dark-900">
                                        {{ formatDate(leave.date_start) }}
                                    </div>
                                    <div class="text-xs text-dark-500">
                                        → {{ formatDate(leave.date_end) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-semibold text-dark-900">{{
                                        leave.working_days
                                    }}</span>
                                    <span class="text-xs text-dark-500">
                                        jour(s)</span
                                    >
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="getStatusClass(leave.status.value)"
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium"
                                    >
                                        <span
                                            class="w-1.5 h-1.5 rounded-full mr-1.5"
                                            :class="
                                                getStatusDotClass(
                                                    leave.status.value,
                                                )
                                            "
                                        ></span>
                                        {{
                                            LEAVE_STATUS_LABELS[leave.status.value]
                                        }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div
                                        class="flex items-center justify-end gap-2"
                                    >
                                        <button
                                            v-if="
                                                canApprove &&
                                                leave.status.value === 'pending'
                                            "
                                            @click="approve(leave.id)"
                                            class="text-secondary-600 hover:text-secondary-700 transition p-1.5 rounded-lg hover:bg-secondary-50"
                                            title="Approuver"
                                        >
                                            <svg
                                                class="w-4 h-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M5 13l4 4L19 7"
                                                />
                                            </svg>
                                        </button>
                                        <button
                                            v-if="
                                                canApprove &&
                                                leave.status.value === 'pending'
                                            "
                                            @click="openRefuse(leave.id)"
                                            class="text-red-500 hover:text-red-600 transition p-1.5 rounded-lg hover:bg-red-50"
                                            title="Refuser"
                                        >
                                            <svg
                                                class="w-4 h-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"
                                                />
                                            </svg>
                                        </button>
                                        <button
                                            @click="viewDetails(leave)"
                                            class="text-dark-400 hover:text-primary-500 transition p-1.5 rounded-lg hover:bg-dark-50"
                                            title="Détails"
                                        >
                                            <svg
                                                class="w-4 h-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                                />
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                                />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div
                v-if="leavesStore.pagination.total > 0"
                class="flex items-center justify-between"
            >
                <div class="text-sm text-dark-500">
                    Affichage de
                    <span class="font-medium text-dark-700">{{
                        (leavesStore.pagination.currentPage - 1) *
                            leavesStore.pagination.perPage +
                        1
                    }}</span>
                    à
                    <span class="font-medium text-dark-700">{{
                        Math.min(
                            leavesStore.pagination.currentPage *
                                leavesStore.pagination.perPage,
                            leavesStore.pagination.total,
                        )
                    }}</span>
                    sur
                    <span class="font-medium text-dark-700">{{
                        leavesStore.pagination.total
                    }}</span>
                    demandes
                </div>

                <div class="flex items-center gap-2">
                    <button
                        :disabled="currentPage === 1"
                        class="p-2 border border-dark-200 rounded-lg text-dark-500 hover:bg-dark-50 hover:text-dark-700 disabled:opacity-40 disabled:cursor-not-allowed transition"
                        @click="goToPage(currentPage - 1)"
                    >
                        <svg
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 19l-7-7 7-7"
                            />
                        </svg>
                    </button>

                    <div class="flex items-center gap-1">
                        <button
                            v-for="page in visiblePages"
                            :key="page"
                            @click="typeof page === 'number' && goToPage(page)"
                            :class="[
                                'min-w-[36px] h-9 px-3 rounded-lg text-sm font-medium transition',
                                currentPage === page
                                    ? 'bg-primary-400 text-white shadow-sm'
                                    : 'text-dark-600 hover:bg-dark-100',
                            ]"
                            :disabled="typeof page !== 'number'"
                        >
                            {{ page }}
                        </button>
                    </div>

                    <button
                        :disabled="
                            currentPage >= leavesStore.pagination.lastPage
                        "
                        class="p-2 border border-dark-200 rounded-lg text-dark-500 hover:bg-dark-50 hover:text-dark-700 disabled:opacity-40 disabled:cursor-not-allowed transition"
                        @click="goToPage(currentPage + 1)"
                    >
                        <svg
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 5l7 7-7 7"
                            />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Refus -->
        <div
            v-if="refuseModalOpen"
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
            @click.self="closeRefuseModal"
        >
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
                <div
                    class="flex items-center justify-between p-5 border-b border-dark-100"
                >
                    <h2 class="text-lg font-semibold text-dark-900">
                        Refuser la demande
                    </h2>
                    <button
                        @click="closeRefuseModal"
                        class="text-dark-400 hover:text-dark-600"
                    >
                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>
                <div class="p-5">
                    <AppInput
                        v-model="refuseReason"
                        label="Motif du refus"
                        type="textarea"
                        placeholder="Expliquez la raison du refus..."
                        required
                    />
                    <div class="flex justify-end gap-3 mt-5">
                        <AppButton
                            type="button"
                            variant="secondary"
                            @click="closeRefuseModal"
                            >Annuler</AppButton
                        >
                        <AppButton
                            type="button"
                            variant="danger"
                            @click="confirmRefuse"
                            >Confirmer le refus</AppButton
                        >
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Détails -->
        <div
            v-if="detailModalOpen"
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
            @click.self="closeDetailModal"
        >
            <div class="bg-white rounded-xl shadow-xl max-w-lg w-full mx-4">
                <div
                    class="flex items-center justify-between p-5 border-b border-dark-100"
                >
                    <h2 class="text-lg font-semibold text-dark-900">
                        Détails de la demande
                    </h2>
                    <button
                        @click="closeDetailModal"
                        class="text-dark-400 hover:text-dark-600"
                    >
                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>
                <div v-if="selectedLeave" class="p-5 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-dark-500 uppercase">Agent</p>
                            <p class="font-medium text-dark-900 mt-1">
                                {{ selectedLeave.agent?.full_name }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-dark-500 uppercase">
                                Département
                            </p>
                            <p class="font-medium text-dark-900 mt-1">
                                {{
                                    selectedLeave.agent?.department?.name || "—"
                                }}
                            </p>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs text-dark-500 uppercase">
                            Type de congé
                        </p>
                        <p class="font-medium text-dark-900 mt-1">
                            {{ LEAVE_TYPE_LABELS[selectedLeave.type.value] }}
                        </p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-dark-500 uppercase">
                                Date de début
                            </p>
                            <p class="font-medium text-dark-900 mt-1">
                                {{ formatDate(selectedLeave.date_start) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-dark-500 uppercase">
                                Date de fin
                            </p>
                            <p class="font-medium text-dark-900 mt-1">
                                {{ formatDate(selectedLeave.date_end) }}
                            </p>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div class="bg-primary-50 rounded-lg p-2 text-center">
                            <p class="text-xl font-bold text-primary-600">
                                {{ selectedLeave.working_days }}
                            </p>
                            <p class="text-xs text-primary-600">Jours ouvrés</p>
                        </div>
                        <div class="bg-secondary-50 rounded-lg p-2 text-center">
                            <p class="text-xl font-bold text-secondary-600">
                                {{
                                    getCalendarDays(
                                        selectedLeave.date_start,
                                        selectedLeave.date_end,
                                    )
                                }}
                            </p>
                            <p class="text-xs text-secondary-600">
                                Jours calendaires
                            </p>
                        </div>
                        <div class="bg-dark-50 rounded-lg p-2 text-center">
                            <p class="text-xl font-bold text-dark-600">
                                {{
                                    getWeekendsCount(
                                        selectedLeave.date_start,
                                        selectedLeave.date_end,
                                    )
                                }}
                            </p>
                            <p class="text-xs text-dark-600">Week-ends</p>
                        </div>
                    </div>
                    <div v-if="selectedLeave.reason">
                        <p class="text-xs text-dark-500 uppercase">Motif</p>
                        <p
                            class="text-sm text-dark-700 mt-1 bg-dark-50 p-2 rounded-lg"
                        >
                            {{ selectedLeave.reason }}
                        </p>
                    </div>
                    <div v-if="selectedLeave.refusal_reason">
                        <p class="text-xs text-red-500 uppercase">
                            Motif du refus
                        </p>
                        <p
                            class="text-sm text-red-600 mt-1 bg-red-50 p-2 rounded-lg"
                        >
                            {{ selectedLeave.refusal_reason }}
                        </p>
                    </div>
                    <div v-if="selectedLeave.approved_at">
                        <p class="text-xs text-dark-500 uppercase">
                            Approuvé le
                        </p>
                        <p class="text-sm text-dark-700 mt-1">
                            {{ formatDateTime(selectedLeave.approved_at) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { reactive, onMounted, ref, computed } from "vue";
import { RouterLink } from "vue-router";
import AppLayout from "@/components/layout/AppLayout.vue";
import AppInput from "@/components/common/AppInput.vue";
import AppButton from "@/components/common/AppButton.vue";
import { useLeavesStore } from "@/stores/leaves";
import { useAuthStore } from "@/stores/auth";
import { useApi } from "@/composables/useApi";
import { useToast } from "@/composables/useToast";
import {
    LEAVE_STATUS_LABELS,
    LEAVE_TYPE_LABELS,
    formatDate,
    formatDateTime,
} from "@/utils";
import type { LeaveFilters, Leave } from "@/types";

const leavesStore = useLeavesStore();
const authStore = useAuthStore();
const { execute } = useApi();
const { success, error: toastError } = useToast();

const currentYear = new Date().getFullYear();
const years = Array.from({ length: 5 }, (_, i) => currentYear - i);
const currentPage = ref(1);
const loading = ref(false);

const filters = reactive<LeaveFilters & { search?: string }>({
    status: undefined,
    year: currentYear,
    search: "",
    page: 1,
    per_page: 15,
});

const refuseModalOpen = ref(false);
const detailModalOpen = ref(false);
const selectedLeaveId = ref<number | null>(null);
const selectedLeave = ref<Leave | null>(null);
const refuseReason = ref("");

const canApprove = computed(() =>
    authStore.hasAnyRole("admin", "rh", "manager"),
);

const stats = computed(() => {
    const leaves = leavesStore.leaves || [];
    return {
        pending: leaves.filter((l) => l && l.status.value === "pending").length,
        approved: leaves.filter((l) => l && l.status.value === "approved").length,
        refused: leaves.filter((l) => l && l.status.value === "refused").length,
    };
});

const safeLeaves = computed(() =>
    leavesStore.leaves.filter(l => l?.id != null)
);

const visiblePages = computed(() => {
    const total = leavesStore.pagination.lastPage;
    const current = currentPage.value;
    const delta = 2;
    const range: number[] = [];
    const rangeWithDots: (number | string)[] = [];
    let l: number | undefined;

    for (let i = 1; i <= total; i++) {
        if (
            i === 1 ||
            i === total ||
            (i >= current - delta && i <= current + delta)
        ) {
            range.push(i);
        }
    }

    range.forEach((i) => {
        if (l) {
            if (i - l === 2) {
                rangeWithDots.push(l + 1);
            } else if (i - l !== 1) {
                rangeWithDots.push("...");
            }
        }
        rangeWithDots.push(i);
        l = i;
    });

    return rangeWithDots;
});

function getInitials(fullName?: string): string {
    if (!fullName) return "??";
    const parts = fullName.split(" ");
    if (parts.length >= 2) {
        return `${parts[0][0]}${parts[1][0]}`.toUpperCase();
    }
    return fullName.substring(0, 2).toUpperCase();
}

function getTypeClass(type: string): string {
    const classes: Record<string, string> = {
        annual: "bg-primary-100 text-primary-700",
        sick: "bg-secondary-100 text-secondary-700",
        unpaid: "bg-amber-100 text-amber-700",
        maternity: "bg-pink-100 text-pink-700",
        paternity: "bg-blue-100 text-blue-700",
        special: "bg-purple-100 text-purple-700",
    };
    return classes[type] || "bg-dark-100 text-dark-700";
}

function getStatusClass(status: string): string {
    const classes: Record<string, string> = {
        pending: "bg-amber-50 text-amber-700",
        approved: "bg-green-50 text-green-700",
        refused: "bg-red-50 text-red-700",
        cancelled: "bg-dark-100 text-dark-700",
    };
    return classes[status] || "bg-dark-100 text-dark-700";
}

function getStatusDotClass(status: string): string {
    const classes: Record<string, string> = {
        pending: "bg-amber-500",
        approved: "bg-green-500",
        refused: "bg-red-500",
        cancelled: "bg-dark-400",
    };
    return classes[status] || "bg-dark-400";
}

function getCalendarDays(start: string, end: string): number {
    const startDate = new Date(start);
    const endDate = new Date(end);
    const diffTime = Math.abs(endDate.getTime() - startDate.getTime());
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
}

function getWeekendsCount(start: string, end: string): number {
    let count = 0;
    const startDate = new Date(start);
    const endDate = new Date(end);
    const currentDate = new Date(startDate);

    while (currentDate <= endDate) {
        const day = currentDate.getDay();
        if (day === 0 || day === 6) count++;
        currentDate.setDate(currentDate.getDate() + 1);
    }
    return count;
}

let debounceTimer: ReturnType<typeof setTimeout>;
function debouncedFetch() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        currentPage.value = 1;
        filters.page = 1;
        fetchLeaves();
    }, 300);
}

async function fetchLeaves() {
    loading.value = true;
    filters.page = currentPage.value;
    await leavesStore.fetchLeaves(filters);
    loading.value = false;
}

async function goToPage(page: number) {
    if (page === currentPage.value) return;
    currentPage.value = page;
    filters.page = page;
    await fetchLeaves();
}

async function approve(id: number) {
    try {
        await leavesStore.approveLeave(id);
        success("Congé approuvé avec succès.");
        await fetchLeaves(); // Recharger pour mettre à jour
    } catch (err: any) {
        toastError(err.message || "Erreur lors de l'approbation");
    }
}

function openRefuse(id: number) {
    selectedLeaveId.value = id;
    refuseReason.value = "";
    refuseModalOpen.value = true;
}

async function confirmRefuse() {
    if (!selectedLeaveId.value || !refuseReason.value) {
        toastError("Veuillez saisir un motif de refus");
        return;
    }

    try {
        await leavesStore.refuseLeave(selectedLeaveId.value!, refuseReason.value);
        success("Demande de congé refusée.");
        closeRefuseModal();
        await fetchLeaves(); // Recharger pour mettre à jour
    } catch (err: any) {
        toastError(err.message || "Erreur lors du refus");
    }
}

function closeRefuseModal() {
    refuseModalOpen.value = false;
    selectedLeaveId.value = null;
    refuseReason.value = "";
}

function viewDetails(leave: Leave) {
    selectedLeave.value = leave;
    detailModalOpen.value = true;
}

function closeDetailModal() {
    detailModalOpen.value = false;
    selectedLeave.value = null;
}

onMounted(async () => {
    await fetchLeaves();
});
</script>
