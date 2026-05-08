<template>
  <div class="bg-white rounded-xl border border-gray-200 p-5">
    <div class="flex items-center justify-between mb-3">
      <span class="text-2xl">{{ icon }}</span>
      <span
        :class="`bg-${color}-50 text-${color}-700`"
        class="text-xs font-medium px-2 py-1 rounded-full"
      >
        {{ label }}
      </span>
    </div>
    <p class="text-3xl font-bold text-gray-900">{{ value }}</p>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  label: string
  value: string | number
  icon: string
  color: 'blue' | 'green' | 'yellow' | 'purple' | 'red' | 'gray'
}>()
</script>
