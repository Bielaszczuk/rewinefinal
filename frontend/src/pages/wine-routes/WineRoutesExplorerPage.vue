<script setup lang="ts">
import { onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useWineRoutesStore } from '@stores/wineRoutes.store'
import BaseCard from '@components/common/BaseCard.vue'
import BaseSpinner from '@components/common/BaseSpinner.vue'
import BaseEmptyState from '@components/common/BaseEmptyState.vue'
import { formatDuration, formatDistance } from '@utils/format'

const { t } = useI18n()
const routesStore = useWineRoutesStore()


const routeDefaultImages = [
  '/images/winery/wineyard.jpg',
  '/images/winery/winewithmap.jpg',
  '/images/winery/wineriecavabar.jpg',
  '/images/winery/bottlesincava.jpg',
]
function routeDefaultImage(index: number): string {
  return routeDefaultImages[index % routeDefaultImages.length]
}

onMounted(() => {
  routesStore.fetchRoutes()
})

const difficultyColors: Record<string, string> = {
  easy: 'bg-green-100 text-green-700',
  moderate: 'bg-yellow-100 text-yellow-700',
  challenging: 'bg-red-100 text-red-700',
}
</script>

<template>
  <div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">{{ t('routes.title') }}</h1>

    <div v-if="routesStore.loading" class="flex justify-center py-12">
      <BaseSpinner size="lg" />
    </div>

    <BaseEmptyState
      v-else-if="!routesStore.hasRoutes"
      icon="map"
      :title="t('routes.noRoutes')"
      :description="t('routes.noRoutesDesc', 'Volvé más tarde para ver rutas curadas del vino')"
    />

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <router-link
        v-for="(route, idx) in routesStore.routes"
        :key="route.id"
        :to="`/wine-routes/${route.id}`"
      >
        <BaseCard hoverable padding="none" class="overflow-hidden">
          <div class="aspect-video bg-gray-100 relative">
            <img
              :src="route.imageUrl || routeDefaultImage(idx)"
              :alt="route.name"
              class="w-full h-full object-cover"
              @error="($event.target as HTMLImageElement).src = routeDefaultImage(idx)"
            />
            <span
              :class="[
                'absolute top-2 right-2 px-2 py-1 text-xs rounded capitalize',
                difficultyColors[route.difficulty] ?? 'bg-gray-100 text-gray-700'
              ]"
            >
              {{ t(`routes.difficulties.${route.difficulty}`, route.difficulty) }}
            </span>
          </div>
          <div class="p-4">
            <h3 class="font-semibold text-gray-900 truncate">{{ route.name }}</h3>
            <p class="text-sm text-gray-600 mt-1">{{ route.region }}, {{ route.country }}</p>
            <div class="flex items-center gap-4 mt-3 text-sm text-gray-500">
              <span>🕐 {{ formatDuration(route.duration * 60) }}</span>
              <span>📏 {{ formatDistance(route.distance) }}</span>
              <span>📍 {{ route.stops.length }} {{ t('routes.stops') }}</span>
            </div>
            <div class="flex items-center justify-between mt-3">
              <span v-if="route.rating" class="text-wine-600">⭐ {{ route.rating.toFixed(1) }}</span>
              <span class="text-sm text-gray-500">{{ route.reviewCount }} {{ t('wines.reviews') }}</span>
            </div>
          </div>
        </BaseCard>
      </router-link>
    </div>
  </div>
</template>

