import { authClient } from '@api/clients/auth.client'
import { mapUserFromDto } from '@domain/user/user.mappers'
import type { User } from '@domain/user/user.types'
import type { LoginRequestDto, RegisterRequestDto } from '@api/dto/auth.dto'
import axios from 'axios'

export interface AuthTokens {
  accessToken: string
  refreshToken: string
  expiresIn: number
}

export interface AuthResult {
  user: User
  tokens: AuthTokens
}

/**
 * Auth validation error class
 */
export class AuthValidationError extends Error {
  public readonly fieldErrors: Record<string, string[]>

  constructor(message: string, fieldErrors: Record<string, string[]> = {}) {
    super(message)
    this.name = 'AuthValidationError'
    this.fieldErrors = fieldErrors
  }
}

/**
 * Auth API error class
 */
export class AuthApiError extends Error {
  public readonly statusCode: number
  public readonly isSessionExpired: boolean

  constructor(message: string, statusCode: number = 500, isSessionExpired: boolean = false) {
    super(message)
    this.name = 'AuthApiError'
    this.statusCode = statusCode
    this.isSessionExpired = isSessionExpired
  }
}


/**
 * Authentication service with validation
 */
export const authService = {
  /**
   * Login user with email/username and password
   * @throws AuthValidationError if validation fails
   * @throws AuthApiError if API call fails
   */
  async login(usernameOrEmail: string, password: string): Promise<AuthResult> {

    if (!usernameOrEmail || !password) {
      throw new AuthValidationError('Invalid login credentials', {
        usernameOrEmail: !usernameOrEmail ? ['Email or username is required'] : [],
        password: !password ? ['Password is required'] : [],
      })
    }

    try {
      const data: LoginRequestDto = { usernameOrEmail, password }
      const response = await authClient.login(data)

      return {
        user: mapUserFromDto({
          id: response.user.id,
          username: response.user.username,
          email: response.user.email,
          name: response.user.name ?? '',
          avatarUrl: response.user.avatarUrl,
          roles: response.user.roles,
          emailVerified: response.user.emailVerified,
          createdAt: new Date().toISOString(),
          updatedAt: new Date().toISOString(),
        }),
        tokens: {
          accessToken: response.accessToken,
          refreshToken: response.refreshToken,
          expiresIn: response.expiresIn,
        },
      }
    } catch (error: unknown) {
      if (axios.isAxiosError(error)) {
        const status = error.response?.status ?? 0
        const data = error.response?.data as Record<string, unknown> | undefined

        if (status === 401) {
          throw new AuthApiError('Invalid email or password', 401, false)
        }

        const message = (data?.message as string) || (data?.error as string) || 'Login failed'

        if (status === 422 && data?.errors) {
          const backendErrors = data.errors as Record<string, string[]>
          throw new AuthValidationError(message, backendErrors)
        }
        throw new AuthApiError(message, status, false)
      }
      throw new AuthApiError('Network error. Please check your connection.', 0, false)
    }
  },

  /**
   * Register a new user
   * @throws AuthValidationError if validation fails
   * @throws AuthApiError if API call fails
   */
  async register(username: string, email: string, password: string, name?: string): Promise<AuthResult> {

    if (!username || !email || !password) {
      throw new AuthValidationError('Invalid registration data', {
        username: !username ? ['Username is required'] : [],
        email: !email ? ['Email is required'] : [],
        password: !password ? ['Password is required'] : [],
      })
    }

    try {
      const data: RegisterRequestDto = { username, email, password, name }
      const response = await authClient.register(data)

      return {
        user: mapUserFromDto({
          id: response.user.id,
          username: response.user.username,
          email: response.user.email,
          name: response.user.name ?? '',
          avatarUrl: response.user.avatarUrl,
          roles: response.user.roles,
          emailVerified: response.user.emailVerified,
          createdAt: new Date().toISOString(),
          updatedAt: new Date().toISOString(),
        }),
        tokens: {
          accessToken: response.accessToken,
          refreshToken: response.refreshToken,
          expiresIn: response.expiresIn,
        },
      }
    } catch (error: unknown) {
      if (axios.isAxiosError(error)) {
        const status = error.response?.status ?? 0
        const data = error.response?.data as Record<string, unknown> | undefined
        const message = (data?.message as string) || (data?.error as string) || 'Registration failed'
        if (status === 422 && data?.errors) {
          const backendErrors = data.errors as Record<string, string[]>
          throw new AuthValidationError(message, backendErrors)
        }
        throw new AuthApiError(message, status)
      }
      throw new AuthApiError('Network error. Please check your connection.', 0)
    }
  },

  /**
   * Refresh access token
   * @throws AuthApiError if refresh fails (session expired)
   */
  async refreshToken(refreshToken: string): Promise<{ accessToken: string; expiresIn: number }> {
    try {
      const response = await authClient.refreshToken({ refreshToken })
      return {
        accessToken: response.accessToken,
        expiresIn: response.expiresIn,
      }
    } catch (error: unknown) {
      if (axios.isAxiosError(error)) {
        throw new AuthApiError('Session expired. Please login again.', error.response?.status ?? 401)
      }
      throw new AuthApiError('Session expired. Please login again.', 401)
    }
  },

  /**
   * Logout user
   */
  async logout(): Promise<void> {
    try {
      await authClient.logout()
    } catch {
      // Ignore logout errors - user is logging out anyway
    }
  },

  /**
   * Get current authenticated user
   * @throws AuthApiError if not authenticated or session expired
   */
  async getCurrentUser(): Promise<User> {
    try {
      const response = await authClient.getCurrentUser()
      return mapUserFromDto({
        id: response.id,
        username: response.username,
        email: response.email,
        name: response.name ?? '',
        avatarUrl: response.avatarUrl,
        roles: response.roles,
        emailVerified: response.emailVerified,
        createdAt: response.createdAt ?? new Date().toISOString(),
        updatedAt: response.updatedAt ?? new Date().toISOString(),
      })
    } catch (error: unknown) {
      if (axios.isAxiosError(error)) {
        const statusCode = error.response?.status ?? 500
        throw new AuthApiError(
          statusCode === 401 ? 'Session expired' : 'Failed to get user',
          statusCode
        )
      }
      throw new AuthApiError('Network error', 0)
    }
  },
}

export default authService
