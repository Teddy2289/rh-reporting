<template>
  <AppLayout>
    <div class="p-6 space-y-6">
      <h1 class="text-2xl font-bold text-gray-900">Tableau de bord</h1>

      <!-- Stats -->
      <div v-if="stats" class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <StatCard label="Agents actifs" :value="stats.active_agents" icon="👥" color="blue" />
        <StatCard label="Congés en attente" :value="stats.pending_leaves" icon="⏳" color="yellow" />
        <StatCard label="Congés approuvés" :value="stats.approved_leaves_month" icon="✅" color="green" />
        <StatCard label="Heures ce mois" :value="`${stats.total_hours_month}h`" icon="🕒" color="purple" />
      </div>

      <div v-else-if="loading" class="text-gray-500">Chargement des statistiques…</div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AppLayout from '@/components/layout/AppLayout.vue'
import StatCard from '@/components/common/StatCard.vue'
import { reportsApi } from '@/api'
import { useApi } from '@/composables/useApi'
import type { DashboardStats } from '@/types'

const stats = ref<DashboardStats | null>(null)
const { loading, execute } = useApi()

onMounted(async () => {
  const now = new Date()
  const result = await execute(() =>
    reportsApi.dashboard(now.getFullYear(), now.getMonth() + 1),
  )
  if (result) stats.value = result.data
})
</script>
