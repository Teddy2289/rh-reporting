<template>
  <div v-if="lastPage > 1" class="flex items-center justify-between text-sm text-gray-600">
    <span>{{ total }} résultat{{ total > 1 ? 's' : '' }}</span>

    <div class="flex items-center gap-1">
      <button
        :disabled="currentPage === 1"
        class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition"
        @click="$emit('change', currentPage - 1)"
      >‹</button>

      <template v-for="p in pages" :key="p">
        <span v-if="p === '...'" class="w-8 h-8 flex items-center justify-center text-gray-400">…</span>
        <button
          v-else
          :class="[
            'w-8 h-8 flex items-center justify-center rounded-lg border transition text-sm font-medium',
            p === currentPage
              ? 'bg-blue-600 border-blue-600 text-white'
              : 'border-gray-200 hover:bg-gray-50 text-gray-700',
          ]"
          @click="$emit('change', p as number)"
        >{{ p }}</button>
      </template>

      <button
        :disabled="currentPage === lastPage"
        class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition"
        @click="$emit('change', currentPage + 1)"
      >›</button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  currentPage: number
  lastPage: number
  total: number
}>()

defineEmits<{ change: [page: number] }>()

const pages = computed(() => {
  const range: (number | string)[] = []
  const delta = 2

  for (let i = 1; i <= props.lastPage; i++) {
    if (
      i === 1 ||
      i === props.lastPage ||
      (i >= props.currentPage - delta && i <= props.currentPage + delta)
    ) {
      range.push(i)
    } else if (range[range.length - 1] !== '...') {
      range.push('...')
    }
  }
  return range
})
</script>
