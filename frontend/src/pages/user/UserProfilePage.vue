<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '@stores/auth.store'
import { useRouter } from 'vue-router'
import http from '@app/http'
import BaseButton from '@components/common/BaseButton.vue'
import BaseCard from '@components/common/BaseCard.vue'
import ImageWithFallback from '@components/common/ImageWithFallback.vue'

const authStore = useAuthStore()
const router = useRouter()

const user = computed(() => authStore.user)
const saving = ref(false)
const changingPassword = ref(false)


const profileForm = ref({
  name: '',
  email: '',
  avatarUrl: '',
})


const passwordForm = ref({
  currentPassword: '',
  newPassword: '',
  confirmPassword: '',
})

const showPasswordForm = ref(false)


const stats = ref({
  reviewsCount: 0,
  cellarCount: 0,
  eventsAttended: 0,
})

async function fetchUserStats() {
  try {
    const res = await http.get('/users/me/stats')
    stats.value = res.data
  } catch (error) {
    console.error('Error fetching stats:', error)
  }
}

async function saveProfile() {
  if (!profileForm.value.name || !profileForm.value.email) {
    window.alert('Por favor completa todos los campos requeridos')
    return
  }

  saving.value = true
  try {
    const payload = {
      name: profileForm.value.name,
      email: profileForm.value.email,
      avatarUrl: profileForm.value.avatarUrl || null,
    }

    const res = await http.patch('/users/me', payload)
    authStore.updateUser(res.data)
    window.alert('Perfil actualizado exitosamente')
  } catch (error: unknown) {
    console.error('Error updating profile:', error)
    window.alert('Error al actualizar el perfil')
  } finally {
    saving.value = false
  }
}

async function changePassword() {
  if (!passwordForm.value.currentPassword || !passwordForm.value.newPassword) {
    window.alert('Por favor completa todos los campos')
    return
  }

  if (passwordForm.value.newPassword !== passwordForm.value.confirmPassword) {
    window.alert('Las contraseñas no coinciden')
    return
  }

  if (passwordForm.value.newPassword.length < 8) {
    window.alert('La nueva contraseña debe tener al menos 8 caracteres')
    return
  }

  changingPassword.value = true
  try {
    await http.post('/users/me/change-password', {
      currentPassword: passwordForm.value.currentPassword,
      newPassword: passwordForm.value.newPassword,
    })

    passwordForm.value = {
      currentPassword: '',
      newPassword: '',
      confirmPassword: '',
    }
    showPasswordForm.value = false
    window.alert('Contraseña cambiada exitosamente')
  } catch (error: unknown) {
    console.error('Error changing password:', error)
    window.alert('Error al cambiar la contraseña. Verifica que la contraseña actual sea correcta.')
  } finally {
    changingPassword.value = false
  }
}

function initForm() {
  if (user.value) {
    profileForm.value = {
      name: user.value.name || '',
      email: user.value.email || '',
      avatarUrl: user.value.avatar || '',
    }
  }
}

onMounted(() => {
  if (!authStore.isAuthenticated) {
    router.push('/login')
    return
  }
  initForm()
  fetchUserStats()
})
</script>

<template>
  <div class="max-w-4xl mx-auto space-y-6">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">
        Mi Perfil
      </h1>
      <p class="text-gray-600 mt-1">
        Gestiona tu información personal y preferencias
      </p>
    </div>

    <BaseCard>
      <div class="flex items-start gap-6">
        <div class="flex-shrink-0">
          <ImageWithFallback
            :src="profileForm.avatarUrl"
            :alt="user?.name || 'Avatar'"
            fallback="user"
            class="w-24 h-24 rounded-full"
          />
        </div>

        <div class="flex-1 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Nombre de usuario
            </label>
            <input
              :value="user?.username"
              type="text"
              disabled
              class="w-full border border-gray-300 bg-gray-50 rounded-lg px-3 py-2 text-sm text-gray-500 cursor-not-allowed"
            >
            <p class="text-xs text-gray-500 mt-1">
              El nombre de usuario no se puede cambiar
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Nombre completo *
            </label>
            <input
              v-model="profileForm.name"
              type="text"
              required
              placeholder="Juan Pérez"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            >
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Email *
            </label>
            <input
              v-model="profileForm.email"
              type="email"
              required
              placeholder="email@ejemplo.com"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            >
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              URL del Avatar
            </label>
            <input
              v-model="profileForm.avatarUrl"
              type="url"
              placeholder="https://..."
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            >
            <p class="text-xs text-gray-500 mt-1">
              URL de la imagen de perfil
            </p>
          </div>

          <div class="flex justify-end">
            <BaseButton
              :loading="saving"
              @click="saveProfile"
            >
              Guardar Cambios
            </BaseButton>
          </div>
        </div>
      </div>
    </BaseCard>

    <BaseCard>
      <h2 class="text-lg font-semibold text-gray-900 mb-4">
        Estadísticas
      </h2>
      <div class="grid grid-cols-3 gap-4">
        <div class="text-center p-4 bg-gray-50 rounded-lg">
          <div class="text-3xl font-bold text-primary-600">
            {{ stats.reviewsCount }}
          </div>
          <div class="text-sm text-gray-600 mt-1">
            Reseñas
          </div>
        </div>
        <div class="text-center p-4 bg-gray-50 rounded-lg">
          <div class="text-3xl font-bold text-wine-600">
            {{ stats.cellarCount }}
          </div>
          <div class="text-sm text-gray-600 mt-1">
            Vinos en Bodega
          </div>
        </div>
        <div class="text-center p-4 bg-gray-50 rounded-lg">
          <div class="text-3xl font-bold text-green-600">
            {{ stats.eventsAttended }}
          </div>
          <div class="text-sm text-gray-600 mt-1">
            Eventos Asistidos
          </div>
        </div>
      </div>
    </BaseCard>

    <BaseCard>
      <h2 class="text-lg font-semibold text-gray-900 mb-4">
        Seguridad
      </h2>

      <div v-if="!showPasswordForm">
        <p class="text-sm text-gray-600 mb-4">
          Cambia tu contraseña regularmente para mantener tu cuenta segura
        </p>
        <BaseButton
          variant="outline"
          @click="showPasswordForm = true"
        >
          Cambiar Contraseña
        </BaseButton>
      </div>

      <div
        v-else
        class="space-y-4"
      >
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Contraseña Actual *
          </label>
          <input
            v-model="passwordForm.currentPassword"
            type="password"
            required
            autocomplete="current-password"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Nueva Contraseña *
          </label>
          <input
            v-model="passwordForm.newPassword"
            type="password"
            required
            autocomplete="new-password"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
          <p class="text-xs text-gray-500 mt-1">
            Mínimo 8 caracteres
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Confirmar Nueva Contraseña *
          </label>
          <input
            v-model="passwordForm.confirmPassword"
            type="password"
            required
            autocomplete="new-password"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
        </div>

        <div class="flex justify-end gap-3">
          <BaseButton
            variant="ghost"
            @click="showPasswordForm = false; passwordForm = { currentPassword: '', newPassword: '', confirmPassword: '' }"
          >
            Cancelar
          </BaseButton>
          <BaseButton
            :loading="changingPassword"
            @click="changePassword"
          >
            Cambiar Contraseña
          </BaseButton>
        </div>
      </div>
    </BaseCard>

    <BaseCard>
      <h2 class="text-lg font-semibold text-gray-900 mb-4">
        Información de la Cuenta
      </h2>
      <div class="space-y-3 text-sm">
        <div class="flex justify-between py-2 border-b border-gray-100">
          <span class="text-gray-600">Rol:</span>
          <span class="font-medium text-gray-900">
            {{ user?.roles?.join(', ') || 'Usuario' }}
          </span>
        </div>
        <div class="flex justify-between py-2 border-b border-gray-100">
          <span class="text-gray-600">Cuenta verificada:</span>
          <span
            :class="user?.isVerified ? 'text-green-600' : 'text-yellow-600'"
            class="font-medium"
          >
            {{ user?.isVerified ? 'Sí' : 'Pendiente' }}
          </span>
        </div>
        <div class="flex justify-between py-2">
          <span class="text-gray-600">Miembro desde:</span>
          <span class="font-medium text-gray-900">
            {{ user?.createdAt ? new Date(user.createdAt).toLocaleDateString('es-AR', { year: 'numeric', month: 'long', day: 'numeric' }) : '-' }}
          </span>
        </div>
      </div>
    </BaseCard>

    <BaseCard
      v-if="authStore.isAdmin"
      class="border-red-200 bg-red-50"
    >
      <h2 class="text-lg font-semibold text-red-900 mb-4">
        Zona de Peligro
      </h2>
      <p class="text-sm text-red-700 mb-4">
        Estas acciones son irreversibles. Procede con precaución.
      </p>
      <BaseButton
        variant="danger"
        @click="() => window.alert('La eliminación de cuenta no está implementada aún')"
      >
        Eliminar Cuenta
      </BaseButton>
    </BaseCard>
  </div>
</template>
