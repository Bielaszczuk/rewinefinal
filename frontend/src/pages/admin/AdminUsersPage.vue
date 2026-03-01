<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import http from '@app/http'
import BaseButton from '@components/common/BaseButton.vue'
import BaseCard from '@components/common/BaseCard.vue'

interface User {
  id: string; username: string; email: string; name: string | null
  roles: string[]; enabled: boolean; locked: boolean; lockReason: string | null
  lastLoginAt: string | null; createdAt: string
}

const users = ref<User[]>([])
const loading = ref(false)
const search = ref('')
const page = ref(0)
const totalItems = ref(0)
const pageSize = 20
const confirmAction = ref<{ type: 'ban' | 'unban' | 'delete'; user: User } | null>(null)
const actionReason = ref('')
const working = ref(false)

const totalPages = computed(() => Math.ceil(totalItems.value / pageSize))

async function fetchUsers() {
  loading.value = true
  try {
    const params: Record<string, string | number> = { page: page.value, size: pageSize }
    if (search.value) params.search = search.value
    const res = await http.get('/users', { params })
    const responseData = res.data.data ?? res.data
    users.value = responseData.items ?? responseData.content ?? []
    totalItems.value = responseData.totalItems ?? responseData.totalElements ?? 0
  } finally {
    loading.value = false
  }
}

async function updateRole(user: User, role: string) {
  await http.patch(`/users/${user.id}/role`, { role })
  await fetchUsers()
}

async function confirmAndAct() {
  if (!confirmAction.value) return
  working.value = true
  try {
    const { type, user } = confirmAction.value
    if (type === 'ban') await http.post(`/users/${user.id}/ban`, { reason: actionReason.value || 'Banned by admin' })
    else if (type === 'unban') await http.post(`/users/${user.id}/unban`)
    else if (type === 'delete') await http.delete(`/users/${user.id}`)
    confirmAction.value = null
    actionReason.value = ''
    await fetchUsers()
  } finally {
    working.value = false
  }
}


function handleSearch() { page.value = 0; fetchUsers() }
onMounted(fetchUsers)
</script>

<template>
  <div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">
      Gestión de Usuarios
    </h1>

    <BaseCard>
      <div class="flex gap-3">
        <input
          v-model="search"
          type="text"
          placeholder="Buscar por nombre, email o usuario..."
          class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
          @keyup.enter="handleSearch"
        >
        <BaseButton
          variant="secondary"
          @click="handleSearch"
        >
          Buscar
        </BaseButton>
      </div>
    </BaseCard>

    <BaseCard padding="none">
      <div
        v-if="loading"
        class="p-8 text-center text-gray-500"
      >
        Cargando...
      </div>
      <div
        v-else-if="users.length === 0"
        class="p-8 text-center text-gray-500"
      >
        No se encontraron usuarios.
      </div>
      <table
        v-else
        class="w-full text-sm"
      >
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="px-4 py-3 text-left font-medium text-gray-600">
              Usuario
            </th>
            <th class="px-4 py-3 text-left font-medium text-gray-600">
              Email
            </th>
            <th class="px-4 py-3 text-left font-medium text-gray-600">
              Rol
            </th>
            <th class="px-4 py-3 text-left font-medium text-gray-600">
              Estado
            </th>
            <th class="px-4 py-3 text-left font-medium text-gray-600">
              Último acceso
            </th>
            <th class="px-4 py-3 text-right font-medium text-gray-600">
              Acciones
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr
            v-for="u in users"
            :key="u.id"
            class="hover:bg-gray-50"
          >
            <td class="px-4 py-3">
              <div class="font-medium text-gray-900">
                {{ u.username }}
              </div>
              <div
                v-if="u.name"
                class="text-xs text-gray-500"
              >
                {{ u.name }}
              </div>
            </td>
            <td class="px-4 py-3 text-gray-600 text-xs">
              {{ u.email }}
            </td>
            <td class="px-4 py-3">
              <select
                :value="u.roles.find(r => r !== 'ROLE_USER') ?? 'ROLE_USER'"
                class="text-xs border border-gray-200 rounded px-2 py-1"
                @change="updateRole(u, ($event.target as HTMLSelectElement).value)"
              >
                <option value="ROLE_USER">
                  Usuario
                </option>
                <option value="ROLE_MODERATOR">
                  Moderador
                </option>
                <option value="ROLE_PARTNER">
                  Partner
                </option>
                <option value="ROLE_ADMIN">
                  Admin
                </option>
              </select>
            </td>
            <td class="px-4 py-3">
              <span
                :class="u.locked ? 'bg-red-100 text-red-700' : u.enabled ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'"
                class="text-xs px-2 py-0.5 rounded-full font-medium"
              >
                {{ u.locked ? 'Bloqueado' : u.enabled ? 'Activo' : 'Deshabilitado' }}
              </span>
            </td>
            <td class="px-4 py-3 text-xs text-gray-500">
              {{ u.lastLoginAt ? new Date(u.lastLoginAt).toLocaleDateString('es-AR') : 'Nunca' }}
            </td>
            <td class="px-4 py-3 text-right space-x-2">
              <button
                v-if="!u.locked"
                class="text-orange-500 hover:text-orange-700 text-xs font-medium"
                @click="confirmAction = { type: 'ban', user: u }"
              >
                Banear
              </button>
              <button
                v-else
                class="text-green-600 hover:text-green-800 text-xs font-medium"
                @click="confirmAction = { type: 'unban', user: u }"
              >
                Desbanear
              </button>
              <button
                class="text-red-500 hover:text-red-700 text-xs font-medium"
                @click="confirmAction = { type: 'delete', user: u }"
              >
                Eliminar
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <div
        v-if="totalPages > 1"
        class="px-4 py-3 border-t border-gray-200 flex justify-between items-center text-sm text-gray-600"
      >
        <span>Total: {{ totalItems }} usuarios</span>
        <div class="flex gap-2">
          <button
            :disabled="page === 0"
            :class="page===0?'opacity-40 cursor-not-allowed':'hover:text-primary-600'"
            class="px-2"
            @click="page--; fetchUsers()"
          >
            ← Anterior
          </button>
          <span>Página {{ page + 1 }} / {{ totalPages }}</span>
          <button
            :disabled="page >= totalPages-1"
            :class="page>=totalPages-1?'opacity-40 cursor-not-allowed':'hover:text-primary-600'"
            class="px-2"
            @click="page++; fetchUsers()"
          >
            Siguiente →
          </button>
        </div>
      </div>
    </BaseCard>

    <div
      v-if="confirmAction"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
    >
      <div class="bg-white rounded-xl shadow-xl p-6 max-w-sm w-full space-y-4">
        <h3 class="font-bold text-gray-900">
          {{ confirmAction.type === 'ban' ? 'Banear usuario' : confirmAction.type === 'unban' ? 'Desbanear usuario' : 'Eliminar usuario' }}
        </h3>
        <p class="text-sm text-gray-600">
          ¿Confirmar acción sobre <strong>{{ confirmAction.user.username }}</strong>?
        </p>
        <div v-if="confirmAction.type === 'ban'">
          <label class="text-xs font-medium text-gray-600 block mb-1">Razón (opcional)</label>
          <input
            v-model="actionReason"
            type="text"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
          >
        </div>
        <div class="flex gap-3 justify-end">
          <BaseButton
            variant="ghost"
            @click="confirmAction = null; actionReason = ''"
          >
            Cancelar
          </BaseButton>
          <BaseButton
            :variant="confirmAction.type === 'delete' ? 'danger' : 'primary'"
            :loading="working"
            @click="confirmAndAct"
          >
            {{ confirmAction.type === 'ban' ? 'Banear' : confirmAction.type === 'unban' ? 'Desbanear' : 'Eliminar' }}
          </BaseButton>
        </div>
      </div>
    </div>
  </div>
</template>

