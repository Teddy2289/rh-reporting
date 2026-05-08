<template>
  <div class="flex flex-col gap-1">
    <label v-if="label" :for="inputId" class="text-sm font-medium text-gray-700">
      {{ label }}
      <span v-if="required" class="text-red-500 ml-0.5">*</span>
    </label>

    <div class="relative">
      <span v-if="$slots.prefix" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">
        <slot name="prefix" />
      </span>

      <input
        :id="inputId"
        v-bind="$attrs"
        :value="modelValue"
        :type="type"
        :placeholder="placeholder"
        :disabled="disabled"
        :required="required"
        :class="[
          'w-full rounded-lg border text-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent',
          error ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-white',
          disabled ? 'bg-gray-50 text-gray-400 cursor-not-allowed' : '',
          $slots.prefix ? 'pl-8' : 'pl-3',
          $slots.suffix ? 'pr-8' : 'pr-3',
          'py-2',
        ]"
        @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
        @blur="$emit('blur', $event)"
      />

      <span v-if="$slots.suffix" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">
        <slot name="suffix" />
      </span>
    </div>

    <p v-if="error" class="text-xs text-red-600">{{ error }}</p>
    <p v-else-if="hint" class="text-xs text-gray-500">{{ hint }}</p>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = withDefaults(defineProps<{
  modelValue?: string | number | null
  label?: string
  type?: string
  placeholder?: string
  disabled?: boolean
  required?: boolean
  error?: string
  hint?: string
  id?: string
}>(), {
  type: 'text',
  disabled: false,
  required: false,
})

defineEmits<{
  'update:modelValue': [value: string]
  'blur': [event: FocusEvent]
}>()

const inputId = computed(() => props.id ?? `input-${Math.random().toString(36).slice(2, 7)}`)
</script>
