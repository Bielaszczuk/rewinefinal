/**
 * Axios HTTP client with authentication interceptors
 *
 * Features:
 * - Automatic Authorization header injection
 * - Token refresh on 401 with request queuing
 * - Only one refresh request at a time (concurrent requests wait)
 * - Session cleanup and redirect on refresh failure
 */

import axios, {
  type AxiosInstance,
  type AxiosError,
  type InternalAxiosRequestConfig,
  type AxiosResponse,
} from 'axios'
import { env } from './env'

interface ExtendedAxiosRequestConfig extends InternalAxiosRequestConfig {
  _retry?: boolean
  _retryCount?: number
}

type QueuedRequest = {
  resolve: (token: string) => void
  reject: (error: Error) => void
}


let accessTokenGetter: (() => string | null) | null = null
let refreshTokenHandler: (() => Promise<string>) | null = null
let sessionClearHandler: (() => void) | null = null
let sessionExpiredHandler: (() => void) | null = null

/**
 * Configure token handlers - called from auth initialization
 * This avoids circular dependencies by using dependency injection
 */
export function configureHttpAuth(handlers: {
  getAccessToken: () => string | null
  refreshToken: () => Promise<string>
  clearSession: () => void
  onSessionExpired: () => void
}): void {
  accessTokenGetter = handlers.getAccessToken
  refreshTokenHandler = handlers.refreshToken
  sessionClearHandler = handlers.clearSession
  sessionExpiredHandler = handlers.onSessionExpired
}


let isRefreshing = false
let refreshQueue: QueuedRequest[] = []

/**
 * Process queued requests after token refresh
 */
function processQueue(error: Error | null, token: string | null = null): void {
  refreshQueue.forEach((request) => {
    if (error) {
      request.reject(error)
    } else if (token) {
      request.resolve(token)
    }
  })
  refreshQueue = []
}

/**
 * Attempt to refresh the access token
 * Queues concurrent requests to prevent multiple refresh calls
 */
async function attemptTokenRefresh(): Promise<string> {
  if (isRefreshing) {
    return new Promise<string>((resolve, reject) => {
      refreshQueue.push({ resolve, reject })
    })
  }

  isRefreshing = true

  try {
    if (!refreshTokenHandler) {
      throw new Error('Refresh token handler not configured')
    }

    const newToken = await refreshTokenHandler()
    processQueue(null, newToken)
    return newToken
  } catch (error) {
    const refreshError = error instanceof Error ? error : new Error('Token refresh failed')
    processQueue(refreshError)
    throw refreshError
  } finally {
    isRefreshing = false
  }
}

/**
 * Handle session expiration
 */
function handleSessionExpired(): void {
  if (sessionClearHandler) {
    sessionClearHandler()
  }
  if (sessionExpiredHandler) {
    sessionExpiredHandler()
  }
}

// Axios Instance

export const http: AxiosInstance = axios.create({
  baseURL: env.apiBaseUrl,
  timeout: env.apiTimeout,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
})

http.interceptors.request.use(
  (config: InternalAxiosRequestConfig): InternalAxiosRequestConfig => {
    const token = accessTokenGetter?.()

    if (token && config.headers) {
      config.headers.Authorization = `Bearer ${token}`
    }

    return config
  },
  (error: AxiosError): Promise<never> => {
    return Promise.reject(error)
  }
)

http.interceptors.response.use(
  (response: AxiosResponse): AxiosResponse => {
    return response
  },
  async (error: AxiosError): Promise<AxiosResponse> => {
    const originalRequest = error.config as ExtendedAxiosRequestConfig | undefined

    if (!originalRequest) {
      return Promise.reject(error)
    }

    const isUnauthorized = error.response?.status === 401
    const hasNotRetried = !originalRequest._retry
    const hasRefreshHandler = !!refreshTokenHandler

    // Don't try to refresh token for auth endpoints (login, register, etc.)
    const isAuthEndpoint = originalRequest.url?.includes('/auth/')

    if (isUnauthorized && hasNotRetried && hasRefreshHandler && !isAuthEndpoint) {
      originalRequest._retry = true
      originalRequest._retryCount = (originalRequest._retryCount || 0) + 1

      const isRefreshRequest = originalRequest.url?.includes('/auth/refresh')
      if (isRefreshRequest) {
        handleSessionExpired()
        return Promise.reject(error)
      }

      try {
        const newToken = await attemptTokenRefresh()

        if (originalRequest.headers) {
          originalRequest.headers.Authorization = `Bearer ${newToken}`
        }

        return http(originalRequest)
      } catch (refreshError) {
        handleSessionExpired()
        return Promise.reject(refreshError)
      }
    }

    return Promise.reject(error)
  }
)


/**
 * Create a cancel token source for request cancellation
 */
export function createCancelToken() {
  return axios.CancelToken.source()
}

/**
 * Check if an error is a cancellation
 */
export function isCancel(error: unknown): boolean {
  return axios.isCancel(error)
}

export default http

