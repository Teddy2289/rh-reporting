<template>
  <Teleport to="body">
    <div class="fixed top-5 right-5 z-50 flex flex-col gap-2 w-80">
      <TransitionGroup name="toast">
        <div
          v-for="toast in toasts"
          :key="toast.id"
          class="flex items-start gap-3 p-4 rounded-xl shadow-lg border text-sm font-medium"
          :class="{
            'bg-emerald-50 border-emerald-200 text-emerald-800': toast.type === 'success',
            'bg-red-50 border-red-200 text-red-800':             toast.type === 'error',
            'bg-blue-50 border-blue-200 text-blue-800':          toast.type === 'info',
          }"
        >
          <!-- Icône -->
          <span class="shrink-0 text-lg">
            {{ toast.type === 'success' ? '✅' : toast.type === 'error' ? '❌' : 'ℹ️' }}
          </span>
          <span class="flex-1">{{ toast.message }}</span>
          <button
            class="shrink-0 opacity-50 hover:opacity-100 transition"
            @click="remove(toast.id)"
          >✕</button>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { useToast } from '@/composables/useToast'
const { toasts, remove } = useToast()
</script>

<style scoped>
.toast-enter-active, .toast-leave-active { transition: all 0.3s ease; }
.toast-enter-from { opacity: 0; transform: translateX(100%); }
.toast-leave-to   { opacity: 0; transform: translateX(100%); }
</style>
