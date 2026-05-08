<template>
    <div class="flex flex-col gap-1">
        <label v-if="label" :for="selectId" class="text-sm font-medium text-gray-700">
            {{ label }}
            <span v-if="required" class="text-red-500 ml-0.5">*</span>
        </label>

        <select :id="selectId" v-bind="$attrs" :value="modelValue" :disabled="disabled" :required="required" :class="[
            'w-full rounded-lg border text-sm py-2 px-3 transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none bg-no-repeat',
            error ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-white',
            disabled ? 'bg-gray-50 text-gray-400 cursor-not-allowed' : 'cursor-pointer',
        ]" :style="{
        backgroundImage: 'url(\'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'12\' height=\'12\' viewBox=\'0 0 12 12\'%3E%3Cpath fill=\'%236b7280\' d=\'M6 8L1 3h10z\'/%3E%3C/svg%3E\')',
        backgroundPosition: 'right 12px center',
        backgroundRepeat: 'no-repeat'
    }" @change="$emit('update:modelValue', ($event.target as HTMLSelectElement).value)">
            <option v-if="placeholder" value="" disabled :selected="!modelValue">{{ placeholder }}</option>
            <slot />
        </select>

        <p v-if="error" class="text-xs text-red-600">{{ error }}</p>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = withDefaults(defineProps<{
    modelValue?: string | number | null
    label?: string
    placeholder?: string
    disabled?: boolean
    required?: boolean
    error?: string
    id?: string
}>(), {
    disabled: false,
    required: false,
})

defineEmits<{
    'update:modelValue': [value: string]
}>()

const selectId = computed(() => props.id ?? `select-${Math.random().toString(36).slice(2, 7)}`)
</script>
