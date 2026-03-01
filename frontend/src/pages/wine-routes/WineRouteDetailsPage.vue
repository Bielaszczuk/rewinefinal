<script setup lang="ts">
import { onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useWineRoutesStore } from '@stores/wineRoutes.store'
import BaseButton from '@components/common/BaseButton.vue'
import BaseCard from '@components/common/BaseCard.vue'
import BaseSpinner from '@components/common/BaseSpinner.vue'
import GoogleMapsEmbed from '@components/common/GoogleMapsEmbed.vue'
import { formatDuration, formatDistance } from '@utils/format'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const routesStore = useWineRoutesStore()

const routeId = computed(() => route.params.id as string)
const wineRoute = computed(() => routesStore.currentRoute)

onMounted(() => {
  if (routeId.value) {
    routesStore.fetchRoute(routeId.value)
  }
})

function goBack() {
  router.back()
}

const difficultyColors = {
  easy: 'bg-green-100 text-green-700',
  moderate: 'bg-yellow-100 text-yellow-700',
  challenging: 'bg-red-100 text-red-700',
}

const stopTypeIcons: Record<string, string> = {
  winery: '/images/icons/reshot-icon-vine-cellar-PK3MZL62NG.svg',
  restaurant: '/images/icons/reshot-icon-menu-UWTLEFDCQN.svg',
  viewpoint: '/images/icons/reshot-icon-vineyard-H8S2KEC3PT.svg',
  attraction: '/images/icons/reshot-icon-vine-tasting-P5GDV7FUBS.svg',
  accommodation: '/images/icons/reshot-icon-guided-tour-TN52W87MA6.svg',
}


function startNavigation() {
  if (!wineRoute.value || !wineRoute.value.stops || wineRoute.value.stops.length === 0) {
    return
  }

  const stops = wineRoute.value.stops
  const origin = stops[0]
  const destination = stops[stops.length - 1]


  const waypoints = stops.slice(1, -1).map(stop => {
    if (stop.location.latitude && stop.location.longitude) {
      return `${stop.location.latitude},${stop.location.longitude}`
    }
    return encodeURIComponent(stop.name)
  })


  let url = 'https://www.google.com/maps/dir/?api=1'


  if (origin.location.latitude && origin.location.longitude) {
    url += `&origin=${origin.location.latitude},${origin.location.longitude}`
  } else {
    url += `&origin=${encodeURIComponent(origin.name)}`
  }


  if (destination.location.latitude && destination.location.longitude) {
    url += `&destination=${destination.location.latitude},${destination.location.longitude}`
  } else {
    url += `&destination=${encodeURIComponent(destination.name)}`
  }


  if (waypoints.length > 0) {
    url += `&waypoints=${waypoints.join('|')}`
  }


  url += '&travelmode=driving'


  window.open(url, '_blank')
}


const routeMapQuery = computed(() => {
  if (!wineRoute.value || !wineRoute.value.stops || wineRoute.value.stops.length === 0) {
    return null
  }

  const stops = wineRoute.value.stops


  if (stops.length === 1) {
    const stop = stops[0]

    if (stop.location.latitude && stop.location.longitude) {
      return `${stop.location.latitude},${stop.location.longitude}`
    }
    return stop.name
  }


  const origin = stops[0]
  const destination = stops[stops.length - 1]
  const middleStops = stops.slice(1, -1)

  return {
    origin: origin.location.latitude && origin.location.longitude
      ? `${origin.location.latitude},${origin.location.longitude}`
      : origin.name,
    destination: destination.location.latitude && destination.location.longitude
      ? `${destination.location.latitude},${destination.location.longitude}`
      : destination.name,
    waypoints: middleStops.map(stop => {
      if (stop.location.latitude && stop.location.longitude) {
        return `${stop.location.latitude},${stop.location.longitude}`
      }
      return stop.name
    }),
  }
})
</script>

<template>
  <div>
    <button
      type="button"
      class="flex items-center gap-2 text-gray-600 hover:text-gray-900 mb-6"
      @click="goBack"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
      </svg>
      Back
    </button>

    <div v-if="routesStore.loading" class="flex justify-center py-12">
      <BaseSpinner size="lg" />
    </div>

    <div v-else-if="wineRoute" class="space-y-6">
      <div class="aspect-[3/1] bg-gray-100 rounded-lg overflow-hidden">
        <img
          v-if="wineRoute.imageUrl"
          :src="wineRoute.imageUrl"
          :alt="wineRoute.name"
          class="w-full h-full object-cover"
        />
        <div v-else class="w-full h-full flex items-center justify-center bg-gray-50">
          <img src="/images/icons/reshot-icon-vineyard-H8S2KEC3PT.svg" alt="Route" class="w-24 h-24 opacity-40" />
        </div>
      </div>

      <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">{{ wineRoute.name }}</h1>
          <p class="text-lg text-gray-600">{{ wineRoute.region }}, {{ wineRoute.country }}</p>
        </div>

        <div class="flex flex-wrap gap-2">
          <span
            :class="[
              'px-3 py-1 rounded-full text-sm capitalize',
              difficultyColors[wineRoute.difficulty]
            ]"
          >
            {{ wineRoute.difficulty }}
          </span>
        </div>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <BaseCard class="text-center">
          <div class="text-2xl font-bold text-wine-600">{{ formatDuration(wineRoute.duration * 60) }}</div>
          <div class="text-sm text-gray-500">Duration</div>
        </BaseCard>
        <BaseCard class="text-center">
          <div class="text-2xl font-bold text-gray-900">{{ formatDistance(wineRoute.distance) }}</div>
          <div class="text-sm text-gray-500">Distance</div>
        </BaseCard>
        <BaseCard class="text-center">
          <div class="text-2xl font-bold text-gray-900">{{ wineRoute.stops.length }}</div>
          <div class="text-sm text-gray-500">Stops</div>
        </BaseCard>
        <BaseCard class="text-center">
          <div class="text-2xl font-bold text-wine-600">{{ wineRoute.rating?.toFixed(1) || '-' }}</div>
          <div class="text-sm text-gray-500">{{ wineRoute.reviewCount }} reviews</div>
        </BaseCard>
      </div>

      <BaseCard>
        <h3 class="font-semibold text-gray-900 mb-2">{{ t('routes.aboutRoute') || 'About this route' }}</h3>
        <p class="text-gray-600 whitespace-pre-line">{{ wineRoute.description }}</p>
      </BaseCard>

      <BaseCard v-if="wineRoute.mapEmbedUrl || routeMapQuery">
        <h3 class="font-semibold text-gray-900 mb-4">{{ t('routes.routeMap') || 'Route Map' }}</h3>

        <GoogleMapsEmbed
          v-if="wineRoute.mapEmbedUrl"
          mode="place"
          :query="wineRoute.mapEmbedUrl"
          height="500px"
        />

        <GoogleMapsEmbed
          v-else-if="routeMapQuery && typeof routeMapQuery === 'object'"
          mode="directions"
          :origin="routeMapQuery.origin"
          :destination="routeMapQuery.destination"
          :waypoints="routeMapQuery.waypoints"
          height="500px"
        />

        <GoogleMapsEmbed
          v-else-if="routeMapQuery"
          mode="place"
          :query="routeMapQuery"
          height="500px"
        />
      </BaseCard>

      <div>
        <h3 class="font-semibold text-gray-900 mb-4">Route Stops</h3>
        <div class="space-y-4">
          <BaseCard
            v-for="(stop, index) in wineRoute.stops"
            :key="stop.id"
            class="flex gap-4"
          >
            <div class="flex-shrink-0 w-10 h-10 bg-wine-100 text-wine-600 rounded-full flex items-center justify-center font-semibold">
              {{ index + 1 }}
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2">
                <img :src="stopTypeIcons[stop.type]" :alt="stop.type" class="w-6 h-6" />
                <h4 class="font-semibold text-gray-900">{{ stop.name }}</h4>
              </div>
              <p class="text-sm text-gray-600 mt-1">{{ stop.description }}</p>
              <div class="flex items-center gap-4 mt-2 text-sm text-gray-500">
                <span>📍 {{ stop.location.city }}</span>
                <span>🕐 {{ formatDuration(stop.duration) }}</span>
              </div>
            </div>
          </BaseCard>
        </div>
      </div>

      <div class="flex justify-center gap-4">
        <BaseButton size="lg" @click="startNavigation">
          <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
          </svg>
          {{ t('routes.startRoute') || 'Start Route' }}
        </BaseButton>
      </div>
    </div>

    <div v-else class="text-center py-12">
      <p class="text-gray-600">Route not found</p>
    </div>
  </div>
</template>

