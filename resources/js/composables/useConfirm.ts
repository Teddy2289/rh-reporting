import { ref } from 'vue'

interface ConfirmOptions {
  title?: string
  message: string
  confirmLabel?: string
  cancelLabel?: string
  danger?: boolean
}

const isOpen = ref(false)
const options = ref<ConfirmOptions>({ message: '' })
let resolvePromise: ((value: boolean) => void) | null = null

/**
 * Composable de confirmation — utilisé globalement (singleton).
 * Nécessite le composant <ConfirmDialog /> dans App.vue.
 *
 * @example
 * const { confirm } = useConfirm()
 * const ok = await confirm({ message: 'Supprimer cet agent ?', danger: true })
 * if (ok) await deleteAgent(id)
 */
export function useConfirm() {
  async function confirm(opts: ConfirmOptions): Promise<boolean> {
    options.value = opts
    isOpen.value = true

    return new Promise((resolve) => {
      resolvePromise = resolve
    })
  }

  function resolve(value: boolean) {
    isOpen.value = false
    resolvePromise?.(value)
    resolvePromise = null
  }

  return { confirm, resolve, isOpen, options }
}
