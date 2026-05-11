<template>
    <AppLayout>
        <div class="p-6 space-y-4">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-dark-900">Feuille d'activité</h1>
                    <p class="text-sm text-dark-500 mt-1">Saisie mensuelle des missions par agent</p>
                </div>
                <button
                    @click="saveAll"
                    :disabled="saving || !selectedAgentId"
                    class="inline-flex items-center gap-2 bg-primary-400 hover:bg-primary-500 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-medium px-5 py-2.5 rounded-lg transition shadow-sm"
                >
                    <svg v-if="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ saving ? 'Enregistrement…' : 'Enregistrer' }}
                </button>
            </div>

            <!-- Filtres -->
            <div class="flex flex-wrap items-end gap-4 p-4 bg-white rounded-xl border border-dark-100">
                <div v-if="showAgentSelect" class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-dark-600">Agent</label>
                    <select
                        v-model="selectedAgentId"
                        class="px-3 py-2 border border-dark-200 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary-400 min-w-[180px]"
                        @change="loadEntries"
                    >
                        <option :value="null">-- Sélectionner --</option>
                        <option v-for="a in agents" :key="a.id" :value="a.id">{{ a.full_name }}</option>
                    </select>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-dark-600">Mois</label>
                    <select v-model="selectedMonth" class="px-3 py-2 border border-dark-200 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary-400" @change="loadEntries">
                        <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
                    </select>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-dark-600">Année</label>
                    <select v-model="selectedYear" class="px-3 py-2 border border-dark-200 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary-400" @change="loadEntries">
                        <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                    </select>
                </div>

                <div class="flex-1" />

                <!-- Navigation semaines -->
                <div class="flex items-center gap-2">
                    <button @click="prevWeek" :disabled="weekOffset === 0"
                        class="p-2 border border-dark-200 rounded-lg text-dark-500 hover:bg-dark-50 disabled:opacity-30 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <span class="text-sm font-medium text-dark-700 min-w-[130px] text-center">
                        Semaine {{ weekOffset + 1 }} / {{ totalWeeks }}
                    </span>
                    <button @click="nextWeek" :disabled="weekOffset >= totalWeeks - 1"
                        class="p-2 border border-dark-200 rounded-lg text-dark-500 hover:bg-dark-50 disabled:opacity-30 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Placeholder -->
            <div v-if="!selectedAgentId" class="text-center py-16 bg-white rounded-xl border border-dark-100">
                <svg class="w-12 h-12 mx-auto text-dark-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <p class="text-dark-500">Sélectionnez un agent pour commencer la saisie</p>
            </div>

            <!-- Loading -->
            <div v-else-if="loading" class="flex items-center justify-center py-16">
                <div class="animate-spin rounded-full h-8 w-8 border-2 border-primary-400 border-t-transparent"></div>
            </div>

            <!-- Tableau horizontal style Excel -->
            <div v-else class="bg-white rounded-xl border border-dark-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="border-collapse text-xs w-full min-w-[1200px]">
                        <thead>
                            <!-- Ligne 1 : Nom agent + dates -->
                            <tr>
                                <th class="sticky left-0 z-20 bg-dark-800 text-white px-3 py-2.5 text-left font-bold border-r-2 border-dark-600 min-w-[80px] whitespace-nowrap">
                                    {{ agentName }}
                                </th>
                                <template v-for="date in visibleDates" :key="'h_' + date">
                                    <th
                                        colspan="5"
                                        :class="[
                                            'px-2 py-2.5 text-center font-bold border-r-2 border-dark-300 min-w-[500px] whitespace-nowrap',
                                            isToday(date)   ? 'bg-primary-500 text-white' :
                                            isSunday(date)  ? 'bg-dark-300 text-dark-600' :
                                            isSaturday(date)? 'bg-dark-200 text-dark-600' :
                                                              'bg-[#2ca3a5] text-white'
                                        ]"
                                    >
                                        {{ formatHeaderDate(date) }}
                                    </th>
                                </template>
                            </tr>
                            <!-- Ligne 2 : sous-colonnes Client / Mission / Début / Fin / Pause -->
                            <tr>
                                <th class="sticky left-0 z-20 bg-dark-700 text-dark-300 px-3 py-1.5 text-xs font-medium border-r-2 border-dark-600 uppercase tracking-wider">
                                    Période
                                </th>
                                <template v-for="date in visibleDates" :key="'sub_' + date">
                                    <th :class="[
                                            'px-2 py-1.5 text-center font-semibold border-r border-dark-200 text-xs uppercase tracking-wider',
                                            isSunday(date)  ? 'bg-dark-200 text-dark-400' :
                                            isSaturday(date)? 'bg-dark-100 text-dark-500' :
                                                              'bg-dark-600 text-dark-300'
                                        ]">Client</th>
                                    <th :class="[
                                            'px-2 py-1.5 text-center font-semibold border-r border-dark-200 text-xs uppercase tracking-wider',
                                            isSunday(date)  ? 'bg-dark-200 text-dark-400' :
                                            isSaturday(date)? 'bg-dark-100 text-dark-500' :
                                                              'bg-dark-600 text-dark-300'
                                        ]">Mission</th>
                                    <th :class="[
                                            'px-2 py-1.5 text-center font-semibold border-r border-dark-200 text-xs uppercase tracking-wider',
                                            isSunday(date)  ? 'bg-dark-200 text-dark-400' :
                                            isSaturday(date)? 'bg-dark-100 text-dark-500' :
                                                              'bg-dark-600 text-dark-300'
                                        ]">Début</th>
                                    <th :class="[
                                            'px-2 py-1.5 text-center font-semibold border-r border-dark-200 text-xs uppercase tracking-wider',
                                            isSunday(date)  ? 'bg-dark-200 text-dark-400' :
                                            isSaturday(date)? 'bg-dark-100 text-dark-500' :
                                                              'bg-dark-600 text-dark-300'
                                        ]">Fin</th>
                                    <th :class="[
                                            'px-2 py-1.5 text-center font-semibold border-r-2 border-dark-300 text-xs uppercase tracking-wider',
                                            isSunday(date)  ? 'bg-dark-200 text-dark-400' :
                                            isSaturday(date)? 'bg-dark-100 text-dark-500' :
                                                              'bg-dark-600 text-dark-300'
                                        ]">Pause</th>
                                </template>
                            </tr>
                        </thead>

                        <tbody>
                            <!-- ── Lignes matin ── -->
                            <tr v-for="rowIdx in ROWS_AM" :key="'am_' + rowIdx"
                                :class="rowIdx % 2 === 0 ? 'bg-gray-50' : 'bg-white'">
                                <td class="sticky left-0 z-10 border-r-2 border-b border-dark-200 px-3 py-1.5 text-dark-700 font-semibold"
                                    :class="rowIdx % 2 === 0 ? 'bg-gray-50' : 'bg-white'">
                                    Matin {{ rowIdx + 1 }}
                                </td>

                                <template v-for="date in visibleDates" :key="date + '_am_' + rowIdx">
                                    <!-- Client select -->
                                    <td :class="['border-r border-b border-dark-100 p-0', isSunday(date) ? 'bg-dark-100' : '']">
                                        <select
                                            v-if="!isSunday(date)"
                                            v-model="getEntry(date, rowIdx).client_id"
                                            class="w-full px-2 py-1.5 bg-transparent border-none text-xs focus:outline-none focus:bg-primary-50 min-w-[100px] cursor-pointer"
                                            @change="() => onClientChange(date, rowIdx)"
                                        >
                                            <option :value="null">-- Client --</option>
                                            <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
                                        </select>
                                        <div v-else class="px-2 py-1.5 text-dark-400">--</div>
                                    </td>

                                    <!-- Mission input -->
                                    <td :class="['border-r border-b border-dark-100 p-0', isSunday(date) ? 'bg-dark-100' : '']">
                                        <input
                                            v-if="!isSunday(date)"
                                            v-model="getEntry(date, rowIdx).mission"
                                            type="text"
                                            class="w-full px-2 py-1.5 bg-transparent border-none text-xs focus:outline-none focus:bg-blue-50 min-w-[130px]"
                                            placeholder="Mission"
                                        />
                                        <div v-else class="px-2 py-1.5 text-dark-400">--</div>
                                    </td>

                                    <!-- Heure début -->
                                    <td :class="['border-r border-b border-dark-100 p-0', isSunday(date) ? 'bg-dark-100' : '']">
                                        <input
                                            v-if="!isSunday(date)"
                                            v-model="getEntry(date, rowIdx).start_time"
                                            type="time"
                                            class="w-full px-2 py-1.5 bg-transparent border-none text-xs focus:outline-none focus:bg-blue-50 min-w-[90px]"
                                            @change="() => calculateTotalHours(date, rowIdx)"
                                        />
                                        <div v-else class="px-2 py-1.5 text-dark-400">--</div>
                                    </td>

                                    <!-- Heure fin -->
                                    <td :class="['border-r border-b border-dark-100 p-0', isSunday(date) ? 'bg-dark-100' : '']">
                                        <input
                                            v-if="!isSunday(date)"
                                            v-model="getEntry(date, rowIdx).end_time"
                                            type="time"
                                            class="w-full px-2 py-1.5 bg-transparent border-none text-xs focus:outline-none focus:bg-blue-50 min-w-[90px]"
                                            @change="() => calculateTotalHours(date, rowIdx)"
                                        />
                                        <div v-else class="px-2 py-1.5 text-dark-400">--</div>
                                    </td>

                                    <!-- Pause (minutes) -->
                                    <td :class="['border-r-2 border-b border-dark-200 p-0', isSunday(date) ? 'bg-dark-100' : '']">
                                        <input
                                            v-if="!isSunday(date)"
                                            v-model="getEntry(date, rowIdx).break_minutes"
                                            type="number"
                                            min="0"
                                            step="15"
                                            class="w-full px-2 py-1.5 bg-transparent border-none text-xs focus:outline-none focus:bg-blue-50 min-w-[70px]"
                                            placeholder="min"
                                            @change="() => calculateTotalHours(date, rowIdx)"
                                        />
                                        <div v-else class="px-2 py-1.5 text-dark-400">--</div>
                                    </td>
                                </template>
                            </tr>

                            <!-- ── Ligne récapitulative matinée ── -->
                            <tr class="bg-dark-50">
                                <td class="sticky left-0 z-10 bg-dark-200 border-r-2 border-y border-dark-300 px-3 py-1.5 text-dark-700 font-bold text-xs">
                                    Total Matin
                                </td>
                                <template v-for="date in visibleDates" :key="'total_am_' + date">
                                    <td colspan="5" class="bg-dark-100 border-r-2 border-y border-dark-200 px-2 py-1.5 text-center">
                                        <span v-if="!isSunday(date)" class="text-xs font-semibold text-primary-600">
                                            {{ getTotalHoursForPeriod(date, 'am') }}
                                        </span>
                                    </td>
                                </template>
                            </tr>

                            <!-- ── Ligne PAUSE DÉJEUNER ── -->
                            <tr>
                                <td class="sticky left-0 z-10 bg-dark-300 border-r-2 border-y border-dark-400 px-3 py-1.5 text-dark-600 font-bold uppercase tracking-widest text-xs">
                                    PAUSE DÉJ.
                                </td>
                                <template v-for="date in visibleDates" :key="'pause_' + date">
                                    <td colspan="5" class="bg-dark-200 border-r-2 border-y border-dark-300 px-2 py-1.5 text-center">
                                        <input
                                            v-if="!isSunday(date)"
                                            v-model="getLunchBreak(date).minutes"
                                            type="number"
                                            min="0"
                                            step="15"
                                            class="w-24 px-2 py-1 bg-white border border-dark-200 rounded text-xs text-center"
                                            placeholder="Minutes"
                                            @change="() => calculateTotalHours(date, -1)"
                                        />
                                    </td>
                                </template>
                            </tr>

                            <!-- ── Lignes après-midi ── -->
                            <tr v-for="rowIdx in ROWS_PM" :key="'pm_' + rowIdx"
                                :class="rowIdx % 2 === 0 ? 'bg-gray-50' : 'bg-white'">
                                <td class="sticky left-0 z-10 border-r-2 border-b border-dark-200 px-3 py-1.5 text-dark-700 font-semibold"
                                    :class="rowIdx % 2 === 0 ? 'bg-gray-50' : 'bg-white'">
                                    Après-midi {{ rowIdx + 1 }}
                                </td>

                                <template v-for="date in visibleDates" :key="date + '_pm_' + rowIdx">
                                    <!-- Client select -->
                                    <td :class="['border-r border-b border-dark-100 p-0', isSunday(date) ? 'bg-dark-100' : '']">
                                        <select
                                            v-if="!isSunday(date)"
                                            v-model="getEntry(date, ROWS_AM + rowIdx).client_id"
                                            class="w-full px-2 py-1.5 bg-transparent border-none text-xs focus:outline-none focus:bg-primary-50 min-w-[100px] cursor-pointer"
                                            @change="() => onClientChange(date, ROWS_AM + rowIdx)"
                                        >
                                            <option :value="null">-- Client --</option>
                                            <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
                                        </select>
                                        <div v-else class="px-2 py-1.5 text-dark-400">--</div>
                                    </td>

                                    <!-- Mission input -->
                                    <td :class="['border-r border-b border-dark-100 p-0', isSunday(date) ? 'bg-dark-100' : '']">
                                        <input
                                            v-if="!isSunday(date)"
                                            v-model="getEntry(date, ROWS_AM + rowIdx).mission"
                                            type="text"
                                            class="w-full px-2 py-1.5 bg-transparent border-none text-xs focus:outline-none focus:bg-blue-50 min-w-[130px]"
                                            placeholder="Mission"
                                        />
                                        <div v-else class="px-2 py-1.5 text-dark-400">--</div>
                                    </td>

                                    <!-- Heure début -->
                                    <td :class="['border-r border-b border-dark-100 p-0', isSunday(date) ? 'bg-dark-100' : '']">
                                        <input
                                            v-if="!isSunday(date)"
                                            v-model="getEntry(date, ROWS_AM + rowIdx).start_time"
                                            type="time"
                                            class="w-full px-2 py-1.5 bg-transparent border-none text-xs focus:outline-none focus:bg-blue-50 min-w-[90px]"
                                            @change="() => calculateTotalHours(date, ROWS_AM + rowIdx)"
                                        />
                                        <div v-else class="px-2 py-1.5 text-dark-400">--</div>
                                    </td>

                                    <!-- Heure fin -->
                                    <td :class="['border-r border-b border-dark-100 p-0', isSunday(date) ? 'bg-dark-100' : '']">
                                        <input
                                            v-if="!isSunday(date)"
                                            v-model="getEntry(date, ROWS_AM + rowIdx).end_time"
                                            type="time"
                                            class="w-full px-2 py-1.5 bg-transparent border-none text-xs focus:outline-none focus:bg-blue-50 min-w-[90px]"
                                            @change="() => calculateTotalHours(date, ROWS_AM + rowIdx)"
                                        />
                                        <div v-else class="px-2 py-1.5 text-dark-400">--</div>
                                    </td>

                                    <!-- Pause (minutes) -->
                                    <td :class="['border-r-2 border-b border-dark-200 p-0', isSunday(date) ? 'bg-dark-100' : '']">
                                        <input
                                            v-if="!isSunday(date)"
                                            v-model="getEntry(date, ROWS_AM + rowIdx).break_minutes"
                                            type="number"
                                            min="0"
                                            step="15"
                                            class="w-full px-2 py-1.5 bg-transparent border-none text-xs focus:outline-none focus:bg-blue-50 min-w-[70px]"
                                            placeholder="min"
                                            @change="() => calculateTotalHours(date, ROWS_AM + rowIdx)"
                                        />
                                        <div v-else class="px-2 py-1.5 text-dark-400">--</div>
                                    </td>
                                </template>
                            </tr>

                            <!-- ── Ligne récapitulative après-midi ── -->
                            <tr class="bg-dark-50">
                                <td class="sticky left-0 z-10 bg-dark-200 border-r-2 border-y border-dark-300 px-3 py-1.5 text-dark-700 font-bold text-xs">
                                    Total Après-midi
                                </td>
                                <template v-for="date in visibleDates" :key="'total_pm_' + date">
                                    <td colspan="5" class="bg-dark-100 border-r-2 border-y border-dark-200 px-2 py-1.5 text-center">
                                        <span v-if="!isSunday(date)" class="text-xs font-semibold text-primary-600">
                                            {{ getTotalHoursForPeriod(date, 'pm') }}
                                        </span>
                                    </td>
                                </template>
                            </tr>

                            <!-- ── Ligne récapitulative journalière ── -->
                            <tr class="bg-primary-50">
                                <td class="sticky left-0 z-10 bg-primary-100 border-r-2 border-y border-primary-200 px-3 py-2 text-dark-800 font-bold text-xs">
                                    TOTAL JOUR
                                </td>
                                <template v-for="date in visibleDates" :key="'total_day_' + date">
                                    <td colspan="5" class="bg-primary-50 border-r-2 border-y border-primary-100 px-2 py-2 text-center">
                                        <span v-if="!isSunday(date)" class="text-sm font-bold text-primary-700">
                                            {{ getTotalHoursForDay(date) }}
                                        </span>
                                    </td>
                                </template>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer -->
                <div class="px-4 py-2.5 bg-dark-50 border-t border-dark-100 flex items-center justify-between flex-wrap gap-2">
                    <span class="text-xs text-dark-500">
                        📊 {{ filledCount }} activité(s) saisie(s) ·
                        👤 {{ agents.find(a => a.id === selectedAgentId)?.full_name ?? '' }} ·
                        📅 {{ months.find(m => m.value === selectedMonth)?.label }} {{ selectedYear }}
                    </span>
                    <div class="flex gap-3">
                        <button @click="clearAll" class="text-xs text-red-400 hover:text-red-600 transition">
                            🗑️ Vider le mois
                        </button>
                        <button @click="exportToCSV" class="text-xs text-primary-500 hover:text-primary-700 transition">
                            📥 Exporter CSV
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import AppLayout from '@/components/layout/AppLayout.vue'
import { useAuthStore } from '@/stores/auth'
import { useAgentsStore } from '@/stores/agents'
import { useToast } from '@/composables/useToast'
import apiClient from '@/api/client'

// ─── Constants ────────────────────────────────────────────────────────────────
const ROWS_AM = 4  // réduit à 4 lignes matin pour meilleure lisibilité
const ROWS_PM = 4  // réduit à 4 lignes après-midi

// ─── Types ────────────────────────────────────────────────────────────────────
interface Client { id: number; name: string; code: string }
interface TimeEntry {
    date: string
    row: number
    client_id: number | null
    mission: string
    start_time: string
    end_time: string
    break_minutes: number
    total_hours?: number
}
interface LunchBreak {
    date: string
    minutes: number
}

// ─── Stores ───────────────────────────────────────────────────────────────────
const authStore   = useAuthStore()
const agentsStore = useAgentsStore()
const toast       = useToast()

// ─── State ────────────────────────────────────────────────────────────────────
const agents        = ref<{ id: number; full_name: string }[]>([])
const clients       = ref<Client[]>([])
const entriesMap    = ref<Map<string, TimeEntry>>(new Map())
const lunchBreaksMap = ref<Map<string, LunchBreak>>(new Map())
const now           = new Date()
const showAgentSelect = authStore.hasAnyRole('admin', 'rh', 'manager')
const selectedAgentId = ref<number | null>(
    showAgentSelect ? null : (authStore.user?.agent?.id ?? null)
)
const selectedMonth = ref(now.getMonth() + 1)
const selectedYear  = ref(now.getFullYear())
const weekOffset    = ref(0)
const loading       = ref(false)
const saving        = ref(false)

// ─── Constants UI ─────────────────────────────────────────────────────────────
const months = [
    { value: 1, label: 'Janvier' }, { value: 2, label: 'Février' },
    { value: 3, label: 'Mars' },    { value: 4, label: 'Avril' },
    { value: 5, label: 'Mai' },     { value: 6, label: 'Juin' },
    { value: 7, label: 'Juillet' }, { value: 8, label: 'Août' },
    { value: 9, label: 'Septembre' },{ value: 10, label: 'Octobre' },
    { value: 11, label: 'Novembre' },{ value: 12, label: 'Décembre' },
]
const years = Array.from({ length: 5 }, (_, i) => now.getFullYear() - i + 1)

// ─── Computed ─────────────────────────────────────────────────────────────────
const allDates = computed(() => {
    const days = new Date(selectedYear.value, selectedMonth.value, 0).getDate()
    return Array.from({ length: days }, (_, i) => {
        const d = i + 1
        return `${selectedYear.value}-${String(selectedMonth.value).padStart(2,'0')}-${String(d).padStart(2,'0')}`
    })
})

const weeks = computed(() => {
    const result: string[][] = []
    for (let i = 0; i < allDates.value.length; i += 7)
        result.push(allDates.value.slice(i, i + 7))
    return result
})

const totalWeeks    = computed(() => weeks.value.length)
const visibleDates  = computed(() => weeks.value[weekOffset.value] ?? [])
const agentName     = computed(() => agents.value.find(a => a.id === selectedAgentId.value)?.full_name ?? 'Agent')
const filledCount   = computed(() => {
    let n = 0
    entriesMap.value.forEach(e => {
        if (e.client_id) n++
    })
    return n
})

// ─── Helpers ──────────────────────────────────────────────────────────────────
const isSunday   = (d: string) => new Date(d + 'T12:00:00').getDay() === 0
const isSaturday = (d: string) => new Date(d + 'T12:00:00').getDay() === 6
const isToday    = (d: string) => {
    const t = new Date()
    return d === `${t.getFullYear()}-${String(t.getMonth()+1).padStart(2,'0')}-${String(t.getDate()).padStart(2,'0')}`
}

function formatHeaderDate(dateStr: string): string {
    return new Date(dateStr + 'T12:00:00')
        .toLocaleDateString('fr-FR', { weekday: 'short', day: '2-digit', month: 'short' })
}

function getEntry(date: string, row: number): TimeEntry {
    const key = `${date}_${row}`
    if (!entriesMap.value.has(key)) {
        entriesMap.value.set(key, {
            date,
            row,
            client_id: null,
            mission: '',
            start_time: '',
            end_time: '',
            break_minutes: 0,
            total_hours: 0
        })
    }
    return entriesMap.value.get(key)!
}

function getLunchBreak(date: string): LunchBreak {
    if (!lunchBreaksMap.value.has(date)) {
        lunchBreaksMap.value.set(date, { date, minutes: 0 })
    }
    return lunchBreaksMap.value.get(date)!
}

function calculateTotalHours(date: string, row: number) {
    if (row >= 0) {
        const entry = getEntry(date, row)
        if (entry.start_time && entry.end_time) {
            const start = new Date(`2000-01-01T${entry.start_time}`)
            const end = new Date(`2000-01-01T${entry.end_time}`)
            let diff = (end.getTime() - start.getTime()) / (1000 * 60 * 60) // heures
            diff -= (entry.break_minutes || 0) / 60
            entry.total_hours = Math.max(0, parseFloat(diff.toFixed(2)))
        } else {
            entry.total_hours = 0
        }
    }
}

function getTotalHoursForPeriod(date: string, period: 'am' | 'pm'): string {
    const startRow = period === 'am' ? 0 : ROWS_AM
    const endRow = period === 'am' ? ROWS_AM : ROWS_AM + ROWS_PM
    let total = 0

    for (let i = startRow; i < endRow; i++) {
        const entry = getEntry(date, i)
        if (entry.total_hours) total += entry.total_hours
    }

    return total > 0 ? `${total.toFixed(2)} h` : '-'
}

function getTotalHoursForDay(date: string): string {
    let total = 0
    const lunchBreak = getLunchBreak(date).minutes / 60

    // Total des activités
    for (let i = 0; i < ROWS_AM + ROWS_PM; i++) {
        const entry = getEntry(date, i)
        if (entry.total_hours) total += entry.total_hours
    }

    // Soustraire la pause déjeuner
    total -= lunchBreak

    return total > 0 ? `${total.toFixed(2)} h` : '-'
}

function onClientChange(date: string, row: number) {
    getEntry(date, row).mission = ''
}

function prevWeek() { if (weekOffset.value > 0) weekOffset.value-- }
function nextWeek() { if (weekOffset.value < totalWeeks.value - 1) weekOffset.value++ }
function clearAll() {
    entriesMap.value.clear()
    lunchBreaksMap.value.clear()
}

function exportToCSV() {
    // Export logic here
    toast.info('Export CSV en développement')
}

// ─── API ──────────────────────────────────────────────────────────────────────
async function loadEntries() {
    if (!selectedAgentId.value) return
    loading.value = true
    entriesMap.value.clear()
    lunchBreaksMap.value.clear()
    weekOffset.value = 0
    try {
        const { data } = await apiClient.get('/activity-logs', {
            params: { agent_id: selectedAgentId.value, month: selectedMonth.value, year: selectedYear.value }
        })
        const raw: any[] = data.data ?? data
        const byDate = new Map<string, any[]>()

        for (const r of raw) {
            const d = r.date.substring(0, 10)
            if (!byDate.has(d)) byDate.set(d, [])
            byDate.get(d)!.push(r)
        }

        byDate.forEach((rows, date) => {
            rows.forEach((r, idx) => {
                const entry = getEntry(date, idx)
                entry.client_id = r.client_id
                entry.mission = r.mission ?? ''
                entry.start_time = r.start_time ?? ''
                entry.end_time = r.end_time ?? ''
                entry.break_minutes = r.break_minutes ?? 0
                calculateTotalHours(date, idx)
            })

            // Load lunch break if exists
            const lunchData = rows.find(r => r.type === 'lunch_break')
            if (lunchData) {
                getLunchBreak(date).minutes = lunchData.minutes
            }
        })
    } catch {
        toast.error('Erreur lors du chargement des activités.')
    } finally {
        loading.value = false
    }
}

async function saveAll() {
    if (!selectedAgentId.value) return
    const toSave: any[] = []

    entriesMap.value.forEach(e => {
        if (e.client_id || e.start_time || e.end_time) {
            toSave.push({
                date: e.date,
                row: e.row,
                client_id: e.client_id,
                mission: e.mission,
                start_time: e.start_time,
                end_time: e.end_time,
                break_minutes: e.break_minutes,
                total_hours: e.total_hours
            })
        }
    })

    // Add lunch breaks to save
    lunchBreaksMap.value.forEach(lb => {
        if (lb.minutes > 0) {
            toSave.push({
                date: lb.date,
                type: 'lunch_break',
                minutes: lb.minutes
            })
        }
    })

    saving.value = true
    try {
        await apiClient.post('/activity-logs/bulk', {
            agent_id: selectedAgentId.value,
            month: selectedMonth.value,
            year: selectedYear.value,
            entries: toSave,
        })
        toast.success(`${toSave.length} activité(s) enregistrée(s).`)
    } catch (err: any) {
        toast.error(err?.response?.data?.message ?? 'Erreur lors de la sauvegarde.')
    } finally {
        saving.value = false
    }
}

// ─── Init ─────────────────────────────────────────────────────────────────────
onMounted(async () => {
    if (showAgentSelect) {
        await agentsStore.fetchAgents({ active_only: true, per_page: 200 })
        agents.value = agentsStore.agents
    } else if (authStore.user?.agent) {
        agents.value = [authStore.user.agent]
    }

    const { data } = await apiClient.get('/clients', { params: { per_page: 200, active_only: true } })
    clients.value = data.data ?? data

    if (selectedAgentId.value) await loadEntries()
})
</script>
