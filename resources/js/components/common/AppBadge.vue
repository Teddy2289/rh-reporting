<template>
  <span :class="['inline-flex items-center font-medium rounded-full', sizeClasses, colorClasses]">
    <slot />
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = withDefaults(defineProps<{
  color?: 'blue' | 'green' | 'yellow' | 'red' | 'gray' | 'purple' | 'orange'
  size?: 'sm' | 'md'
}>(), {
  color: 'gray',
  size: 'sm',
})

const colorClasses = computed(() => ({
  blue:   'bg-blue-100 text-blue-700',
  green:  'bg-emerald-100 text-emerald-700',
  yellow: 'bg-amber-100 text-amber-700',
  red:    'bg-red-100 text-red-700',
  gray:   'bg-gray-100 text-gray-600',
  purple: 'bg-violet-100 text-violet-700',
  orange: 'bg-orange-100 text-orange-700',
}[props.color]))

const sizeClasses = computed(() => ({
  sm: 'px-2 py-0.5 text-xs',
  md: 'px-2.5 py-1 text-sm',
}[props.size]))
</script>
