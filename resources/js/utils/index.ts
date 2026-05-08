import { ContractType, LeaveStatus, LeaveType, SlotType } from '@/types'

// ─── Date utils ───────────────────────────────────────────────────────────────

/**
 * Formate une date ISO en français : "15 janv. 2026"
 */
export function formatDate(date: string | Date | null | undefined): string {
  if (!date) return '—'
  return new Intl.DateTimeFormat('fr-FR', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  }).format(new Date(date))
}

/**
 * Formate une date courte : "15/01/2026"
 */
export function formatDateShort(date: string | Date | null | undefined): string {
  if (!date) return '—'
  return new Intl.DateTimeFormat('fr-FR').format(new Date(date))
}

/**
 * Formate une heure "08:00:00" → "08:00"
 */
export function formatTime(time: string | null | undefined): string {
  if (!time) return '—'
  return time.substring(0, 5)
}

/**
 * Retourne les jours d'une semaine donnée (lundi → dimanche)
 */
export function getWeekDays(date: Date): Date[] {
  const monday = new Date(date)
  const day = date.getDay()
  const diff = (day === 0 ? -6 : 1 - day) // Lundi = 1
  monday.setDate(date.getDate() + diff)

  return Array.from({ length: 7 }, (_, i) => {
    const d = new Date(monday)
    d.setDate(monday.getDate() + i)
    return d
  })
}

/**
 * Retourne "YYYY-MM-DD" à partir d'un objet Date
 */
export function toISODate(date: Date): string {
  return date.toISOString().split('T')[0]
}

/**
 * Retourne le premier et dernier jour d'un mois
 */
export function getMonthRange(year: number, month: number): { from: string; to: string } {
  const from = new Date(year, month - 1, 1)
  const to = new Date(year, month, 0)
  return { from: toISODate(from), to: toISODate(to) }
}

// ─── Number / Hours utils ────────────────────────────────────────────────────

/**
 * Convertit des minutes en "Xh Ym"
 */
export function minutesToHuman(minutes: number): string {
  const h = Math.floor(minutes / 60)
  const m = minutes % 60
  if (m === 0) return `${h}h`
  return `${h}h ${m}m`
}


/**
 * Formate une date et heure au format français (JJ/MM/AAAA HH:MM)
 */
export function formatDateTime(date: string | null | undefined): string {
  if (!date) return '—'
  const d = new Date(date)
  if (isNaN(d.getTime())) return '—'
  return d.toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

/**
 * Formate une durée en minutes en heures décimales
 */
export function formatMinutesToHours(minutes: number): string {
  const hours = minutes / 60
  return `${hours.toFixed(1)}h`
}

/**
 * Formate une durée en minutes en heures et minutes
 */
export function formatDuration(minutes: number): string {
  const hours = Math.floor(minutes / 60)
  const mins = minutes % 60
  if (hours === 0) return `${mins}min`
  if (mins === 0) return `${hours}h`
  return `${hours}h${mins}`
}

/**
 * Calcule le nombre de jours ouvrables entre deux dates
 */
export function getWorkingDays(startDate: string, endDate: string): number {
  const start = new Date(startDate)
  const end = new Date(endDate)
  let count = 0
  const current = new Date(start)

  while (current <= end) {
    const dayOfWeek = current.getDay()
    if (dayOfWeek !== 0 && dayOfWeek !== 6) { // Lundi au vendredi
      count++
    }
    current.setDate(current.getDate() + 1)
  }
  return count
}

/**
 * Calcule le nombre de jours calendaires entre deux dates
 */
export function getCalendarDays(startDate: string, endDate: string): number {
  const start = new Date(startDate)
  const end = new Date(endDate)
  const diffTime = Math.abs(end.getTime() - start.getTime())
  return Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1
}

/**
 * Vérifie si une date est valide
 */
export function isValidDate(date: string | Date): boolean {
  const d = new Date(date)
  return !isNaN(d.getTime())
}

/**
 * Retourne le nom du mois en français
 */
export function getMonthName(date: string | Date): string {
  const d = new Date(date)
  return d.toLocaleDateString('fr-FR', { month: 'long' })
}

/**
 * Retourne le nom du jour en français
 */
export function getDayName(date: string | Date): string {
  const d = new Date(date)
  return d.toLocaleDateString('fr-FR', { weekday: 'long' })
}

// ─── Enum labels ──────────────────────────────────────────────────────────────

export const CONTRACT_TYPE_LABELS: Record<ContractType, string> = {
  [ContractType.CDI]: 'CDI',
  [ContractType.CDD]: 'CDD',
  [ContractType.Freelance]: 'Freelance',
  [ContractType.Intern]: 'Stage',
}

export const LEAVE_STATUS_LABELS: Record<LeaveStatus, string> = {
  [LeaveStatus.Pending]: 'En attente',
  [LeaveStatus.Approved]: 'Approuvé',
  [LeaveStatus.Refused]: 'Refusé',
  [LeaveStatus.Cancelled]: 'Annulé',
}

export const LEAVE_STATUS_COLORS: Record<LeaveStatus, string> = {
  [LeaveStatus.Pending]: 'yellow',
  [LeaveStatus.Approved]: 'green',
  [LeaveStatus.Refused]: 'red',
  [LeaveStatus.Cancelled]: 'gray',
}

export const LEAVE_TYPE_LABELS: Record<LeaveType, string> = {
  [LeaveType.Annual]: 'Congé annuel',
  [LeaveType.Sick]: 'Arrêt maladie',
  [LeaveType.Unpaid]: 'Congé sans solde',
  [LeaveType.Maternity]: 'Congé maternité',
  [LeaveType.Paternity]: 'Congé paternité',
  [LeaveType.Special]: 'Congé spécial',
}

export const SLOT_TYPE_LABELS: Record<SlotType, string> = {
  [SlotType.Work]: 'Travail',
  [SlotType.Pause]: 'Pause',
  [SlotType.Leave]: 'Congé',
  [SlotType.Holiday]: 'Jour férié',
}

export const SLOT_TYPE_COLORS: Record<SlotType, string> = {
  [SlotType.Work]: '#3B82F6',
  [SlotType.Pause]: '#F59E0B',
  [SlotType.Leave]: '#10B981',
  [SlotType.Holiday]: '#8B5CF6',
}

// ─── Error handling ───────────────────────────────────────────────────────────

/**
 * Extrait le message d'erreur d'une réponse Axios / Laravel
 */
export function extractErrorMessage(error: unknown): string {
  if (typeof error === 'object' && error !== null) {
    const e = error as any
    // Erreur de validation Laravel (422)
    if (e.response?.data?.errors) {
      const errors = e.response.data.errors as Record<string, string[]>
      return Object.values(errors).flat().join('\n')
    }
    // Message simple
    if (e.response?.data?.message) {
      return e.response.data.message
    }
    if (e.message) {
      return e.message
    }
  }
  return 'Une erreur inattendue est survenue.'
}

/**
 * Extrait le dictionnaire d'erreurs de validation Laravel
 */
export function extractValidationErrors(error: unknown): Record<string, string[]> {
  if (typeof error === 'object' && error !== null) {
    const e = error as any
    if (e.response?.data?.errors) {
      return e.response.data.errors
    }
  }
  return {}
}
