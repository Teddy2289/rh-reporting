<template>
  <RouterView />
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

// Rafraîchit les infos user au démarrage si un token existe
onMounted(async () => {
  if (authStore.isAuthenticated) {
    try {
      await authStore.fetchMe()
    } catch {
      authStore.clearSession()
    }
  }
})
</script>
