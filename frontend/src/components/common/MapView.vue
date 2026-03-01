<script setup lang="ts">
/* eslint-disable no-undef */
import { ref, onMounted, watch, nextTick } from 'vue'

interface Props {
  latitude: number
  longitude: number
  zoom?: number
  height?: string
  interactive?: boolean
  markerTitle?: string
}

const props = withDefaults(defineProps<Props>(), {
  zoom: 15,
  height: '400px',
  interactive: false,
  markerTitle: 'Ubicación',
})

const emit = defineEmits<{
  'location-changed': [lat: number, lng: number]
}>()

const mapRef = ref<HTMLDivElement | null>(null)
const loading = ref(true)
const error = ref<string | null>(null)
let map: google.maps.Map | null = null
let marker: google.maps.Marker | null = null
let initialized = false

const API_KEY = import.meta.env.VITE_GOOGLE_MAPS_API_KEY || ''

function loadGoogleMapsScript(): Promise<void> {
  return new Promise((resolve, reject) => {
    if (window.google && window.google.maps) {
      resolve()
      return
    }

    const existingScript = document.querySelector('script[src*="maps.googleapis.com"]')
    if (existingScript) {
      if (window.google && window.google.maps) {
        resolve()
      } else {
        existingScript.addEventListener('load', () => resolve())
      }
      return
    }

    const script = document.createElement('script')
    script.src = `https://maps.googleapis.com/maps/api/js?key=${API_KEY}&libraries=places`
    script.async = true
    script.defer = true
    script.onload = () => resolve()
    script.onerror = () => reject(new Error('Error al cargar Google Maps'))
    document.head.appendChild(script)
  })
}

async function initMap() {
  if (initialized) return

  if (!API_KEY) {
    error.value = 'Google Maps API Key no configurada'
    loading.value = false
    return
  }

  await nextTick()

  if (!mapRef.value) {
    loading.value = false
    return
  }

  try {
    loading.value = true

    await loadGoogleMapsScript()

    if (!mapRef.value) {
      loading.value = false
      return
    }

    const position = { lat: props.latitude, lng: props.longitude }

    map = new google.maps.Map(mapRef.value, {
      center: position,
      zoom: props.zoom,
      disableDefaultUI: !props.interactive,
      zoomControl: true,
      mapTypeControl: false,
      streetViewControl: props.interactive,
      fullscreenControl: props.interactive,
    })

    marker = new google.maps.Marker({
      position: position,
      map: map,
      title: props.markerTitle,
      draggable: props.interactive,
    })

    if (props.interactive) {
      map.addListener('click', (e: google.maps.MapMouseEvent) => {
        if (e.latLng && marker) {
          const lat = e.latLng.lat()
          const lng = e.latLng.lng()
          marker.setPosition(e.latLng)
          emit('location-changed', lat, lng)
        }
      })

      marker.addListener('dragend', () => {
        const pos = marker?.getPosition()
        if (pos) {
          emit('location-changed', pos.lat(), pos.lng())
        }
      })
    }

    initialized = true
    loading.value = false
  } catch (err) {
    console.error('Error loading Google Maps:', err)
    error.value = 'Error al cargar el mapa'
    loading.value = false
  }
}

watch(() => [props.latitude, props.longitude], ([newLat, newLng]) => {
  if (map && marker && newLat && newLng) {
    const position = { lat: newLat, lng: newLng }
    map.setCenter(position)
    marker.setPosition(position)
  }
})

onMounted(initMap)
</script>

<template>
  <div class="relative">
    <!-- Loading -->
    <div
      v-if="loading"
      class="absolute inset-0 flex items-center justify-center bg-gray-100 rounded-lg"
      :style="{ height }"
    >
      <div class="text-center">
        <svg
          class="animate-spin h-8 w-8 text-primary-600 mx-auto mb-2"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
        >
          <circle
            class="opacity-25"
            cx="12"
            cy="12"
            r="10"
            stroke="currentColor"
            stroke-width="4"
          />
          <path
            class="opacity-75"
            fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
          />
        </svg>
        <p class="text-sm text-gray-600">
          Cargando mapa...
        </p>
      </div>
    </div>

    <!-- Error -->
    <div
      v-else-if="error"
      class="flex items-center justify-center bg-gray-100 rounded-lg border-2 border-dashed border-gray-300"
      :style="{ height }"
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
          {{ error }}
        </p>
      </div>
    </div>

    <!-- Map Container -->
    <div
      ref="mapRef"
      :style="{ height }"
      class="rounded-lg overflow-hidden border border-gray-200"
    />

    <!-- Interactive hint -->
    <p
      v-if="interactive && !loading && !error"
      class="text-xs text-gray-500 mt-2 text-center"
    >
      Haz clic en el mapa o arrastra el marcador para cambiar la ubicación
    </p>
  </div>
</template>
