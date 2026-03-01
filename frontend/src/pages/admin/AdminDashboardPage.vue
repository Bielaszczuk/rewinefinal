<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { RouterLink } from 'vue-router'
import { useAuthStore } from '@stores/auth.store'
import BaseCard from '@components/common/BaseCard.vue'
import http from '@app/http'

interface Stats {
  users: number; wines: number; events: number; routes: number; reviews: number; wineries: number
}

const authStore = useAuthStore()
const stats = ref<Stats | null>(null)
const loading = ref(false)

async function fetchStats() {
  loading.value = true
  try {
    const res = await http.get('/admin/stats')
    stats.value = res.data
  } finally {
    loading.value = false
  }
}

onMounted(fetchStats)

const allCards = [
  { key: 'wines' as keyof Stats, label: 'Vinos', icon: '/images/icons/reshot-icon-red-wine-L2HFAY75WG.svg', to: '/admin/wines' },
  { key: 'events' as keyof Stats, label: 'Eventos', icon: '/images/icons/reshot-icon-toast-65A7YV3EXC.svg', to: '/admin/events' },
  { key: 'users' as keyof Stats, label: 'Usuarios', icon: '/images/icons/reshot-icon-male-sommelier-4J9YXUNDT3.svg', to: '/admin/users', adminOnly: true },
  { key: 'routes' as keyof Stats, label: 'Rutas', icon: '/images/icons/reshot-icon-vineyard-H8S2KEC3PT.svg', to: '/admin/routes' },
  { key: 'reviews' as keyof Stats, label: 'Reseñas', icon: '/images/icons/reshot-icon-grapes-DH3FRP42X5.svg', to: '/admin/wines' },
  { key: 'wineries' as keyof Stats, label: 'Bodegas', icon: '/images/icons/reshot-icon-vine-cellar-PK3MZL62NG.svg', to: '/admin/wineries' },
]

const cards = computed(() => {
  return allCards.filter(card => !card.adminOnly || authStore.isAdmin)
})
</script>

<template>
  <div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">
      Panel de Administración
    </h1>

    <div
      v-if="loading"
      class="text-center text-gray-500 py-8"
    >
      Cargando estadísticas...
    </div>
    <div
      v-else
      class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4"
    >
      <RouterLink
        v-for="card in cards"
        :key="card.key"
        :to="card.to"
        class="block bg-white rounded-xl border border-gray-200 p-4 hover:border-primary-400 hover:shadow-sm transition-all"
      >
        <img
          :src="card.icon"
          :alt="card.label"
          class="w-8 h-8 mb-2"
        >
        <div class="text-2xl font-bold text-gray-900">
          {{ stats?.[card.key] ?? '–' }}
        </div>
        <div class="text-xs text-gray-500 mt-0.5">
          {{ card.label }}
        </div>
      </RouterLink>
    </div>

    <BaseCard>
      <h3 class="font-semibold text-gray-900 mb-4">
        Acciones rápidas
      </h3>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <RouterLink
          to="/admin/wines"
          class="p-4 bg-gray-50 rounded-lg hover:bg-gray-100 text-center transition-colors"
        >
          <img
            src="/images/icons/reshot-icon-red-wine-L2HFAY75WG.svg"
            alt="Vinos"
            class="w-10 h-10 mx-auto"
          >
          <p class="text-sm font-medium text-gray-900 mt-2">
            Gestionar Vinos
          </p>
        </RouterLink>
        <RouterLink
          to="/admin/events"
          class="p-4 bg-gray-50 rounded-lg hover:bg-gray-100 text-center transition-colors"
        >
          <img
            src="/images/icons/reshot-icon-toast-65A7YV3EXC.svg"
            alt="Eventos"
            class="w-10 h-10 mx-auto"
          >
          <p class="text-sm font-medium text-gray-900 mt-2">
            Gestionar Eventos
          </p>
        </RouterLink>
        <RouterLink
          to="/admin/routes"
          class="p-4 bg-gray-50 rounded-lg hover:bg-gray-100 text-center transition-colors"
        >
          <img
            src="/images/icons/reshot-icon-vineyard-H8S2KEC3PT.svg"
            alt="Rutas"
            class="w-10 h-10 mx-auto"
          >
          <p class="text-sm font-medium text-gray-900 mt-2">
            Gestionar Rutas
          </p>
        </RouterLink>
        <RouterLink
          to="/admin/users"
          class="p-4 bg-gray-50 rounded-lg hover:bg-gray-100 text-center transition-colors"
        >
          <img
            src="/images/icons/reshot-icon-male-sommelier-4J9YXUNDT3.svg"
            alt="Usuarios"
            class="w-10 h-10 mx-auto"
          >
          <p class="text-sm font-medium text-gray-900 mt-2">
            Gestionar Usuarios
          </p>
        </RouterLink>
      </div>
    </BaseCard>
  </div>
</template>

