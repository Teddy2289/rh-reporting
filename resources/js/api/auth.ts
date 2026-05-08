import apiClient from './client'
import type { AuthResponse, AuthUser, LoginCredentials } from '@/types'

export const authApi = {
  login(credentials: LoginCredentials): Promise<{ data: AuthResponse }> {
    return apiClient.post('/auth/login', credentials)
  },

  logout(): Promise<void> {
    return apiClient.post('/auth/logout')
  },

  me(): Promise<{ data: AuthUser }> {
    return apiClient.get('/auth/me')
  },

  refresh(): Promise<{ data: AuthResponse }> {
    return apiClient.post('/auth/refresh')
  },
}
