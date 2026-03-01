<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  mode?: 'place' | 'directions' | 'view'
  query?: string
  origin?: string
  destination?: string
  waypoints?: string[]
  center?: string
  zoom?: number
  height?: string
  mapType?: 'roadmap' | 'satellite'
}

const props = withDefaults(defineProps<Props>(), {
  mode: 'place',
  zoom: 15,
  height: '450px',
  mapType: 'roadmap',
})

const API_KEY = import.meta.env.VITE_GOOGLE_MAPS_API_KEY || ''

const embedUrl = computed(() => {
  if (!API_KEY) return ''

  const baseUrl = 'https://www.google.com/maps/embed/v1/'
  const params = new URLSearchParams({
    key: API_KEY,
    maptype: props.mapType,
  })

  if (props.zoom) {
    params.append('zoom', props.zoom.toString())
  }

  switch (props.mode) {
    case 'place':
      if (props.query) {
        params.append('q', props.query)
      }
      return `${baseUrl}place?${params.toString()}`

    case 'directions':
      if (props.origin) {
        params.append('origin', props.origin)
      }
      if (props.destination) {
        params.append('destination', props.destination)
      }
      if (props.waypoints && props.waypoints.length > 0) {
        params.append('waypoints', props.waypoints.join('|'))
      }
      return `${baseUrl}directions?${params.toString()}`

    case 'view':
      if (props.center) {
        params.append('center', props.center)
      }
      return `${baseUrl}view?${params.toString()}`

    default:
      return ''
  }
})

const hasValidConfig = computed(() => {
  if (!API_KEY) return false

  switch (props.mode) {
    case 'place':
      return !!props.query
    case 'directions':
      return !!props.origin && !!props.destination
    case 'view':
      return !!props.center
    default:
      return false
  }
})
</script>

<template>
  <div class="relative">
    <iframe
      v-if="hasValidConfig"
      :src="embedUrl"
      :style="{ height }"
      class="w-full border border-gray-200 rounded-lg"
      style="border:0"
      loading="lazy"
      allowfullscreen
      referrerpolicy="no-referrer-when-downgrade"
    />

    <div
      v-else
      :style="{ height }"
      class="flex items-center justify-center bg-gray-100 rounded-lg border-2 border-dashed border-gray-300"
    >
      <div class="text-center p-6">
        <svg
          class="w-12 h-12 text-gray-400 mx-auto mb-2"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"
          />
        </svg>
        <p class="text-sm text-gray-600">
          {{ !API_KEY ? 'Google Maps API Key no configurada' : 'Configuración de mapa incompleta' }}
        </p>
      </div>
    </div>
  </div>
</template>
