import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authApi } from '@/api/auth'
import type { AuthUser, LoginCredentials } from '@/types'

const TOKEN_KEY = 'access_token'
const USER_KEY = 'auth_user'

export const useAuthStore = defineStore('auth', () => {
  // ─── State ────────────────────────────────────────────────────────────────
  const token = ref<string | null>(localStorage.getItem(TOKEN_KEY))
  const user = ref<AuthUser | null>(
    JSON.parse(localStorage.getItem(USER_KEY) ?? 'null'),
  )
  const loading = ref(false)

  // ─── Getters ──────────────────────────────────────────────────────────────
  const isAuthenticated = computed(() => !!token.value)
  const isAdmin = computed(() => user.value?.roles.includes('admin') ?? false)
  const isRh = computed(() => user.value?.roles.includes('rh') ?? false)
  const isManager = computed(() => user.value?.roles.includes('manager') ?? false)
  const isAgent = computed(() => user.value?.roles.includes('agent') ?? false)

  const hasRole = (role: string) => user.value?.roles.includes(role) ?? false
  const hasAnyRole = (...roles: string[]) => roles.some((r) => hasRole(r))
  const can = (permission: string) =>
    user.value?.permissions.includes(permission) ?? false

  // ─── Actions ──────────────────────────────────────────────────────────────
  async function login(credentials: LoginCredentials) {
    loading.value = true
    try {
      const { data } = await authApi.login(credentials)
      setSession(data.access_token, data.user)
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      await authApi.logout()
    } finally {
      clearSession()
    }
  }

  async function fetchMe() {
    const { data } = await authApi.me()
    user.value = data
    localStorage.setItem(USER_KEY, JSON.stringify(data))
  }

  function setSession(accessToken: string, userData: AuthUser) {
    token.value = accessToken
    user.value = userData
    localStorage.setItem(TOKEN_KEY, accessToken)
    localStorage.setItem(USER_KEY, JSON.stringify(userData))
  }

  function clearSession() {
    token.value = null
    user.value = null
    localStorage.removeItem(TOKEN_KEY)
    localStorage.removeItem(USER_KEY)
  }

  return {
    // state
    token,
    user,
    loading,
    // getters
    isAuthenticated,
    isAdmin,
    isRh,
    isManager,
    isAgent,
    // methods
    hasRole,
    hasAnyRole,
    can,
    // actions
    login,
    logout,
    fetchMe,
    setSession,
    clearSession,
  }
})
