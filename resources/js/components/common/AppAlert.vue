<template>
  <div v-if="message" :class="['flex items-start gap-3 rounded-xl px-4 py-3 text-sm', colorClasses]">
    <span class="text-base leading-none mt-0.5">{{ icon }}</span>
    <div class="flex-1">
      <p class="font-medium" v-if="title">{{ title }}</p>
      <p :class="title ? 'mt-0.5 opacity-90' : ''">{{ message }}</p>
    </div>
    <button v-if="dismissible" class="opacity-60 hover:opacity-100 transition" @click="$emit('dismiss')">✕</button>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = withDefaults(defineProps<{
  type?: 'success' | 'error' | 'warning' | 'info'
  message?: string | null
  title?: string
  dismissible?: boolean
}>(), { type: 'info', dismissible: false })

defineEmits<{ dismiss: [] }>()

const colorClasses = computed(() => ({
  success: 'bg-emerald-50 text-emerald-800 border border-emerald-200',
  error:   'bg-red-50 text-red-800 border border-red-200',
  warning: 'bg-amber-50 text-amber-800 border border-amber-200',
  info:    'bg-blue-50 text-blue-800 border border-blue-200',
}[props.type]))

const icon = computed(() => ({ success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' }[props.type]))
</script>
