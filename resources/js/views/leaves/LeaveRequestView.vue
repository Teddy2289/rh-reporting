<template>
    <AppLayout>
        <div class="p-8 max-w-2xl mx-auto">
            <div class="mb-6">
                <div class="flex items-center gap-4">
                    <RouterLink :to="{ name: 'leaves' }"
                        class="text-dark-500 hover:text-primary-500 transition p-1 -ml-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </RouterLink>
                    <div>
                        <h1 class="text-2xl font-semibold text-dark-900">Nouvelle demande de congé</h1>
                        <p class="text-sm text-dark-500 mt-1">Saisissez les informations pour votre demande</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-dark-100 p-6">
                <!-- Solde de congés -->
                <div v-if="balance" class="mb-6 p-4 bg-primary-50 rounded-xl">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-primary-700">Solde de congés {{ currentYear }}</p>
                            <p class="text-2xl font-bold text-primary-600">{{ balance.remaining_days }} jours</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-primary-600">Alloués: {{ balance.allocated_days }}</p>
                            <p class="text-xs text-primary-600">Utilisés: {{ balance.used_days }}</p>
                        </div>
                    </div>
                    <div class="mt-3 h-2 bg-primary-200 rounded-full overflow-hidden">
                        <div class="h-full bg-primary-500 rounded-full transition-all"
                            :style="{ width: `${(balance.used_days / balance.allocated_days) * 100}%` }"></div>
                    </div>
                </div>

                <form @submit.prevent="handleSubmit" class="space-y-5">
                    <AppAlert v-if="error" type="error" :message="error" />

                    <AppSelect v-if="showAgentSelect" v-model="form.agent_id" label="Agent" required
                        :error="ve.agent_id?.[0]">
                        <option v-for="a in agents" :key="a.id" :value="a.id">{{ a.full_name }}</option>
                    </AppSelect>

                    <AppSelect v-model="form.type" label="Type de congé" required :error="ve.type?.[0]">
                        <option v-for="(label, key) in LEAVE_TYPE_LABELS" :key="key" :value="key">{{ label }}</option>
                    </AppSelect>

                    <div class="grid grid-cols-2 gap-4">
                        <AppInput v-model="form.date_start" label="Date de début" type="date" required
                            :error="ve.date_start?.[0]" @update:model-value="handleDateChange" />
                        <AppInput v-model="form.date_end" label="Date de fin" type="date" required
                            :min="form.date_start" :error="ve.date_end?.[0]" @update:model-value="handleDateChange" />
                    </div>

                    <!-- Bloc durée -->
                    <div v-if="form.date_start && form.date_end">
                        <!-- Dimanche uniquement -->
                        <div v-if="fullDays === 0"
                            class="flex items-center gap-2 p-3 bg-red-50 rounded-xl border border-red-100">
                            <span class="text-sm text-red-700">
                                ⚠️ La période sélectionnée ne contient aucun jour ouvré (dimanche).
                            </span>
                        </div>

                        <!-- Jours ouvrés -->
                        <div v-else class="flex items-center gap-4 p-3 bg-blue-50 rounded-xl border border-blue-100">
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-blue-700">Durée :</span>
                                <span class="text-lg font-bold text-blue-800">{{ workingDaysLabel }}</span>
                            </div>
                            <!-- Demi-journée : seulement pour 1 jour entier lun-ven -->
                            <label v-if="canChooseHalfDay"
                                class="ml-auto flex items-center gap-2 text-sm text-blue-700 cursor-pointer">
                                <input type="checkbox" v-model="isHalfDay"
                                    class="rounded border-blue-300 text-blue-600" />
                                Demi-journée
                            </label>
                        </div>
                    </div>

                    <AppInput v-model="form.reason" label="Motif (optionnel)" type="textarea"
                        placeholder="Précisez si nécessaire…" />

                    <div class="flex justify-end gap-3 pt-4 border-t border-dark-100">
                        <AppButton type="button" variant="secondary" @click="$router.back()">
                            Annuler
                        </AppButton>
                        <AppButton type="submit" :loading="loading" :disabled="form.date_start !== '' && fullDays === 0"
                            class="bg-primary-400 hover:bg-primary-500">
                            Soumettre la demande
                        </AppButton>
                    </div>
                </form>
            </div>

            <div class="mt-6 bg-secondary-50 rounded-xl border border-secondary-100 p-4">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-secondary-500 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="text-sm text-secondary-800">
                        <p class="font-medium mb-1">Informations sur les congés</p>
                        <p class="opacity-90">
                            Les congés sont décomptés en jours ouvrés (du lundi au samedi matin).
                            Les dimanches ne sont pas décomptés.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { reactive, ref, onMounted, computed, watch } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import AppLayout from '@/components/layout/AppLayout.vue'
import AppInput from '@/components/common/AppInput.vue'
import AppSelect from '@/components/common/AppSelect.vue'
import AppButton from '@/components/common/AppButton.vue'
import AppAlert from '@/components/common/AppAlert.vue'
import { useLeavesStore } from '@/stores/leaves'
import { useAuthStore } from '@/stores/auth'
import { useAgentsStore } from '@/stores/agents'
import { useApi } from '@/composables/useApi'
import { LEAVE_TYPE_LABELS } from '@/utils'
import { LeaveType } from '@/types'
import type { Agent, LeaveBalance } from '@/types'
import { useToast } from '@/composables/useToast'
const toast = useToast()



const router = useRouter()
const leavesStore = useLeavesStore()
const authStore = useAuthStore()
const agentsStore = useAgentsStore()
const { loading, error, validationErrors: ve, execute } = useApi()

const showAgentSelect = authStore.hasAnyRole('admin', 'rh')
const currentYear = ref(new Date().getFullYear())
const balance = ref<LeaveBalance | null>(null)
const agents = ref<Agent[]>([])
const isHalfDay = ref(false)

const form = reactive({
    agent_id: authStore.user?.agent?.id ?? 0,
    type: LeaveType.Annual,
    date_start: '',
    date_end: '',
    reason: '',
})

// ─── Parsing local (évite décalage UTC à Madagascar) ─────────────────────────
function parseLocalDate(dateStr: string): Date {
    const [year, month, day] = dateStr.split('-').map(Number)
    return new Date(year, month - 1, day)
}

// ─── Calcul : lun-ven = 1j | sam = 0.5j | dim = 0 ───────────────────────────
function calcWorkingDays(start: string, end: string): number {
    if (!start || !end) return 0
    const s = parseLocalDate(start)
    const e = parseLocalDate(end)
    if (e < s) return 0
    let count = 0
    const cur = new Date(s)
    while (cur <= e) {
        const d = cur.getDay()
        if (d >= 1 && d <= 5) count += 1    // lun–ven
        else if (d === 6) count += 0.5  // sam
        cur.setDate(cur.getDate() + 1)
    }
    return count
}

const fullDays = computed(() => calcWorkingDays(form.date_start, form.date_end))
const canChooseHalfDay = computed(() => fullDays.value === 1) // 1 seul jour entier lun-ven
const workingDays = computed(() => {
    if (fullDays.value === 0) return 0
    if (isHalfDay.value && canChooseHalfDay.value) return 0.5
    return fullDays.value
})
const workingDaysLabel = computed(() => {
    const d = workingDays.value
    if (d === 0) return ''
    if (d === 0.5) return '½ journée'
    return `${d} ${d <= 1 ? 'jour' : 'jours'}`
})

// Reset demi-journée si période change
watch(fullDays, (d) => {
    if (d !== 1) isHalfDay.value = false
})

// ─── Balance ─────────────────────────────────────────────────────────────────
async function fetchBalance() {
    if (!form.agent_id || !form.date_start) return
    try {
        const [year] = form.date_start.split('-').map(Number)
        currentYear.value = year
        balance.value = await leavesStore.fetchBalance(form.agent_id, year)
    } catch {
        balance.value = null
    }
}

function handleDateChange() {
    isHalfDay.value = false
    // Reset date_end si antérieure à date_start
    if (form.date_end && form.date_start && parseLocalDate(form.date_start) > parseLocalDate(form.date_end)) {
        form.date_end = ''
    }
    fetchBalance()
}

// ─── Submit ───────────────────────────────────────────────────────────────────
async function handleSubmit() {
    if (workingDays.value <= 0) return

    // Payload explicite — pas de spread pour éviter les oublis
    const payload = {
        agent_id: form.agent_id,
        type: form.type,
        date_start: form.date_start,
        date_end: form.date_end,
        working_days: workingDays.value,  // ← inclus explicitement
        reason: form.reason || null,
    }

    const result = await execute(() => leavesStore.createLeave(payload))
    if (result) {
        toast.success('Demande de congé soumise avec succès !')
        router.push({ name: 'leaves' })
    }
}

watch(() => form.agent_id, fetchBalance)

onMounted(async () => {
    if (showAgentSelect) {
        await agentsStore.fetchAgents({ active_only: true, per_page: 200 })
        agents.value = agentsStore.agents
    }
    await fetchBalance()
})
</script>
