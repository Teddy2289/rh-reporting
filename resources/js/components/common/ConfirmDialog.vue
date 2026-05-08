<template>
  <AppModal
    :model-value="isOpen"
    :title="options.title ?? 'Confirmation'"
    size="sm"
    :close-on-backdrop="false"
    @update:model-value="resolve(false)"
  >
    <p class="text-gray-600 text-sm">{{ options.message }}</p>

    <template #footer>
      <AppButton variant="secondary" @click="resolve(false)">
        {{ options.cancelLabel ?? 'Annuler' }}
      </AppButton>
      <AppButton :variant="options.danger ? 'danger' : 'primary'" @click="resolve(true)">
        {{ options.confirmLabel ?? 'Confirmer' }}
      </AppButton>
    </template>
  </AppModal>
</template>

<script setup lang="ts">
import AppModal from './AppModal.vue'
import AppButton from './AppButton.vue'
import { useConfirm } from '@/composables/useConfirm'

const { isOpen, options, resolve } = useConfirm()
</script>
