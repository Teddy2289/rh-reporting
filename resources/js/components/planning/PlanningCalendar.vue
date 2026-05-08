<template>
  <div class="space-y-3">
    <!-- Navigation semaine -->
    <div class="flex items-center justify-between">
      <AppButton variant="ghost" size="sm" @click="prevWeek">← Semaine préc.</AppButton>
      <span class="text-sm font-semibold text-gray-800">
        {{ formatDate(weekDays[0]) }} — {{ formatDate(weekDays[6]) }}
      </span>
      <AppButton variant="ghost" size="sm" @click="nextWeek">Semaine suiv. →</AppButton>
    </div>

    <!-- Grille calendrier -->
    <div class="overflow-x-auto rounded-xl border border-gray-200">
      <table class="w-full text-xs min-w-[700px]">
        <!-- En-tête jours -->
        <thead>
          <tr class="bg-gray-50">
            <th class="w-32 px-3 py-2 text-left text-gray-500 font-medium border-r border-gray-200">Agent</th>
            <th
              v-for="day in weekDays"
              :key="day.toISOString()"
              :class="[
                'px-2 py-2 text-center font-medium border-r border-gray-100 last:border-0',
                isToday(day) ? 'bg-blue-50 text-blue-700' : 'text-gray-600',
              ]"
            >
              <div>{{ DAY_NAMES[day.getDay()] }}</div>
              <div :class="['text-lg font-bold', isToday(day) ? 'text-blue-700' : 'text-gray-900']">
                {{ day.getDate() }}
              </div>
            </th>
          </tr>
        </thead>

        <!-- Lignes agents -->
        <tbody class="divide-y divide-gray-100">
          <tr v-for="agent in agentsWithSlots" :key="agent.id" class="hover:bg-gray-50/50">
            <td class="px-3 py-2 border-r border-gray-200">
              <div class="font-medium text-gray-800">{{ agent.full_name }}</div>
              <div class="text-gray-400">{{ agent.department?.name }}</div>
            </td>
            <td
              v-for="day in weekDays"
              :key="day.toISOString()"
              class="px-1 py-1 border-r border-gray-100 last:border-0 align-top"
              style="min-width: 100px"
            >
              <div
                v-for="slot in getSlotsForAgentDay(agent.id, day)"
                :key="slot.id"
                :style="{ backgroundColor: SLOT_TYPE_COLORS[slot.type] + '20', borderLeftColor: SLOT_TYPE_COLORS[slot.type] }"
                class="border-l-2 rounded px-2 py-1 mb-1 cursor-pointer hover:opacity-80 transition"
                @click="$emit('slot-click', slot)"
              >
                <div class="font-medium" :style="{ color: SLOT_TYPE_COLORS[slot.type] }">
                  {{ formatTime(slot.time_start) }}–{{ formatTime(slot.time_end) }}
                </div>
                <div class="text-gray-600 truncate">{{ slot.client?.name ?? SLOT_TYPE_LABELS[slot.type] }}</div>
              </div>

              <!-- Bouton ajouter -->
              <button
                class="w-full text-center text-gray-300 hover:text-blue-500 hover:bg-blue-50 rounded py-1 transition text-sm"
                @click="$emit('add-slot', { date: toISODate(day), agent_id: agent.id })"
              >+</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import AppButton from '@/components/common/AppButton.vue'
import { SLOT_TYPE_COLORS, SLOT_TYPE_LABELS, formatDate, formatTime, toISODate, getWeekDays } from '@/utils'
import type { PlanningSlot, Agent } from '@/types'

const props = defineProps<{
  slots: PlanningSlot[]
  agents: Agent[]
}>()

defineEmits<{
  'slot-click': [slot: PlanningSlot]
  'add-slot': [payload: { date: string; agent_id: number }]
}>()

const DAY_NAMES = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam']

const currentDate = ref(new Date())
const weekDays = computed(() => getWeekDays(currentDate.value))

function prevWeek() {
  const d = new Date(currentDate.value)
  d.setDate(d.getDate() - 7)
  currentDate.value = d
}

function nextWeek() {
  const d = new Date(currentDate.value)
  d.setDate(d.getDate() + 7)
  currentDate.value = d
}

function isToday(date: Date) {
  const today = new Date()
  return date.toDateString() === today.toDateString()
}

function getSlotsForAgentDay(agentId: number, day: Date): PlanningSlot[] {
  const dateStr = toISODate(day)
  return props.slots.filter((s) => s.agent_id === agentId && s.date === dateStr)
}

// Agents qui ont au moins un slot cette semaine ou tous les agents
const agentsWithSlots = computed(() => {
  const agentIds = new Set(props.slots.map((s) => s.agent_id))
  return props.agents.filter((a) => agentIds.has(a.id) || props.agents.length <= 10)
})
</script>
