import type { NavigationGuardNext, RouteLocationNormalized } from 'vue-router'
import { useAuthStore } from '@stores/auth.store'
import { isNullish, nonNull } from '@utils/object'

/**
 * Route guard for authentication and authorization (RBAC)
 */
export async function authGuard(
  to: RouteLocationNormalized,
  _from: RouteLocationNormalized,
  next: NavigationGuardNext
): Promise<void> {
  const authStore = useAuthStore()

  const requiresAuth = to.meta.requiresAuth === true
  const requiredRoles = to.meta.roles as string[] | undefined
  const guestOnly = to.meta.guestOnly === true


  if (guestOnly && authStore.isAuthenticated) {
    next({ path: '/' })
    return
  }


  if (!requiresAuth) {
    next()
    return
  }


  if (!authStore.isAuthenticated) {
    const returnUrl = to.fullPath
    next({
      path: '/login',
      query: { returnUrl },
    })
    return
  }


  if (nonNull(requiredRoles) && requiredRoles.length > 0) {
    const userRoles = authStore.user?.roles


    if (isNullish(userRoles) || userRoles.length === 0) {
      next({ path: '/403', replace: true })
      return
    }


    const hasRequiredRole = requiredRoles.some((role) => userRoles.includes(role as typeof userRoles[number]))

    if (!hasRequiredRole) {
      next({ path: '/403', replace: true })
      return
    }
  }


  next()
}

/**
 * Check if user has a specific role
 */
export function hasRole(role: string): boolean {
  const authStore = useAuthStore()
  const userRoles = authStore.user?.roles

  if (isNullish(userRoles)) {
    return false
  }

  return userRoles.includes(role as typeof userRoles[number])
}

/**
 * Check if user has any of the specified roles
 */
export function hasAnyRole(roles: string[]): boolean {
  const authStore = useAuthStore()
  const userRoles = authStore.user?.roles

  if (isNullish(userRoles) || userRoles.length === 0) {
    return false
  }

  return roles.some((role) => userRoles.includes(role as typeof userRoles[number]))
}

/**
 * Check if user has all of the specified roles
 */
export function hasAllRoles(roles: string[]): boolean {
  const authStore = useAuthStore()
  const userRoles = authStore.user?.roles

  if (isNullish(userRoles) || userRoles.length === 0) {
    return false
  }

  return roles.every((role) => userRoles.includes(role as typeof userRoles[number]))
}

/**
 * Check route meta for required roles against user roles
 */
export function checkRouteMeta(
  meta: Record<string, unknown>,
  userRoles: string[] | undefined
): boolean {
  const requiredRoles = meta.roles as string[] | undefined

  if (isNullish(requiredRoles) || requiredRoles.length === 0) {
    return true
  }

  if (isNullish(userRoles) || userRoles.length === 0) {
    return false
  }

  return requiredRoles.some((role) => userRoles.includes(role))
}

/**
 * Filter navigation items based on user authentication and roles
 */
export function filterNavigationByRoles<T extends { requiresAuth?: boolean; roles?: string[] }>(
  items: T[],
  isAuthenticated: boolean,
  userRoles: string[] | undefined
): T[] {
  return items.filter((item) => {

    if (item.requiresAuth && !isAuthenticated) {
      return false
    }


    if (nonNull(item.roles) && item.roles.length > 0) {
      if (isNullish(userRoles) || userRoles.length === 0) {
        return false
      }
      return item.roles.some((role) => userRoles.includes(role))
    }

    return true
  })
}
