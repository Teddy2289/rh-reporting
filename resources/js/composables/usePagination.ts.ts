import { ref, computed } from 'vue'

/**
 * Composable de pagination réutilisable.
 *
 * @example
 * const { page, perPage, onPageChange } = usePagination()
 * watch([page, perPage], () => fetchAgents({ page: page.value, per_page: perPage.value }))
 */
export function usePagination(defaultPerPage = 15) {
  const page = ref(1)
  const perPage = ref(defaultPerPage)
  const total = ref(0)
  const lastPage = ref(1)

  const hasNext = computed(() => page.value < lastPage.value)
  const hasPrev = computed(() => page.value > 1)

  function onPageChange(newPage: number) {
    page.value = newPage
  }

  function reset() {
    page.value = 1
  }

  function setMeta(meta: { total: number; last_page: number; current_page: number }) {
    total.value = meta.total
    lastPage.value = meta.last_page
    page.value = meta.current_page
  }

  return { page, perPage, total, lastPage, hasNext, hasPrev, onPageChange, reset, setMeta }
}
