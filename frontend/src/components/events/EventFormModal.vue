<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import BaseButton from '@components/common/BaseButton.vue'
import MapView from '@components/common/MapView.vue'

export interface EventFormData {
  id?: string
  title: string
  description: string | null
  type: string
  status: string
  startDate: string
  endDate: string
  locationName: string | null
  locationAddress: string | null
  locationCity: string | null
  locationRegion: string | null
  latitude: number | null
  longitude: number | null
  price: number | null
  maxAttendees: number | null
  imageUrl: string | null
  contactEmail: string | null
  contactPhone: string | null
  websiteUrl: string | null
}

const props = defineProps<{
  show: boolean
  event: Partial<EventFormData> | null
  saving: boolean
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'save', data: EventFormData): void
}>()

const searchAddress = ref('')
const geocoding = ref(false)

const localEvent = ref<Partial<EventFormData>>({})

watch(() => props.event, (newVal) => {
  if (newVal) {
    localEvent.value = { ...newVal }
  }
}, { immediate: true, deep: true })

const isEditing = computed(() => !!localEvent.value?.id)

const EVENT_TYPES = [
  { value: 'TASTING', label: 'Degustación' },
  { value: 'FESTIVAL', label: 'Festival' },
  { value: 'TOUR', label: 'Tour' },
  { value: 'CLASS', label: 'Clase' },
  { value: 'DINNER', label: 'Cena' },
  { value: 'PAIRING', label: 'Maridaje' },
  { value: 'OTHER', label: 'Otro' },
]

const STATUS_OPTIONS = [
  { value: 'DRAFT', label: 'Borrador' },
  { value: 'PUBLISHED', label: 'Publicado' },
  { value: 'CANCELLED', label: 'Cancelado' },
  { value: 'COMPLETED', label: 'Completado' },
]

async function geocodeAddress() {
  if (!searchAddress.value.trim()) {
    window.alert('Por favor ingresá un lugar o dirección para buscar')
    return
  }

  geocoding.value = true
  try {
    const apiKey = import.meta.env.VITE_GOOGLE_MAPS_API_KEY

    if (!apiKey) {
      window.alert('API Key de Google Maps no configurada')
      return
    }

    const query = encodeURIComponent(searchAddress.value.trim())
    const url = `https://maps.googleapis.com/maps/api/geocode/json?address=${query}&key=${apiKey}`

    const response = await fetch(url)
    const data = await response.json()

    if (data.status === 'OK' && data.results.length > 0) {
      const result = data.results[0]
      const loc = result.geometry.location

      localEvent.value.latitude = loc.lat
      localEvent.value.longitude = loc.lng
      localEvent.value.locationAddress = result.formatted_address

      const addressComponents = result.address_components || []
      for (const component of addressComponents) {
        if (component.types.includes('locality')) {
          localEvent.value.locationCity = component.long_name
        }
        if (component.types.includes('administrative_area_level_1')) {
          localEvent.value.locationRegion = component.long_name
        }
      }

      if (!localEvent.value.locationName) {
        localEvent.value.locationName = searchAddress.value.trim()
      }

      window.alert(`Ubicación encontrada: ${result.formatted_address}`)
      searchAddress.value = ''
    } else {
      window.alert('No se encontró la ubicación. Intentá con un nombre o dirección más específica.')
    }
  } catch (error) {
    console.error('Geocoding error:', error)
    window.alert('Error al buscar la ubicación. Por favor intentá nuevamente.')
  } finally {
    geocoding.value = false
  }
}

function handleSave() {
  if (!localEvent.value.title || !localEvent.value.startDate || !localEvent.value.endDate) {
    window.alert('Por favor completá los campos requeridos: título, fecha de inicio y fecha de fin')
    return
  }
  emit('save', localEvent.value as EventFormData)
}

function handleClose() {
  searchAddress.value = ''
  emit('close')
}

function handleLocationChanged(lat: number, lng: number) {
  localEvent.value.latitude = lat
  localEvent.value.longitude = lng
}
</script>

<template>
  <div
    v-if="show && localEvent"
    class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4 overflow-y-auto"
  >
    <div class="bg-white rounded-xl shadow-xl w-full max-w-3xl my-8">
      <div class="p-6 space-y-4 max-h-[85vh] overflow-y-auto">
        <div class="flex justify-between items-center sticky top-0 bg-white pb-2 border-b">
          <h2 class="text-lg font-bold text-gray-900">
            {{ isEditing ? 'Editar Evento' : 'Crear Evento' }}
          </h2>
          <button
            class="text-gray-400 hover:text-gray-600"
            @click="handleClose"
          >
            <svg
              class="w-6 h-6"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </button>
        </div>

        <form
          class="space-y-4"
          @submit.prevent="handleSave"
        >
          <div>
            <label class="text-xs font-medium text-gray-600 block mb-1">
              Título del evento *
            </label>
            <input
              v-model="localEvent.title"
              type="text"
              required
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            >
          </div>

          <div>
            <label class="text-xs font-medium text-gray-600 block mb-1">
              Descripción
            </label>
            <textarea
              v-model="localEvent.description"
              rows="3"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="text-xs font-medium text-gray-600 block mb-1">
                Tipo de evento *
              </label>
              <select
                v-model="localEvent.type"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              >
                <option
                  v-for="t in EVENT_TYPES"
                  :key="t.value"
                  :value="t.value"
                >
                  {{ t.label }}
                </option>
              </select>
            </div>
            <div>
              <label class="text-xs font-medium text-gray-600 block mb-1">
                Estado *
              </label>
              <select
                v-model="localEvent.status"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              >
                <option
                  v-for="s in STATUS_OPTIONS"
                  :key="s.value"
                  :value="s.value"
                >
                  {{ s.label }}
                </option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="text-xs font-medium text-gray-600 block mb-1">
                Fecha y hora de inicio *
              </label>
              <input
                v-model="localEvent.startDate"
                type="datetime-local"
                required
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              >
            </div>
            <div>
              <label class="text-xs font-medium text-gray-600 block mb-1">
                Fecha y hora de fin *
              </label>
              <input
                v-model="localEvent.endDate"
                type="datetime-local"
                required
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              >
            </div>
          </div>

          <div class="space-y-3">
            <h4 class="text-sm font-semibold text-gray-700 border-b pb-1">
              Ubicación
            </h4>

            <div>
              <label class="text-xs font-medium text-gray-600 block mb-1">
                Buscar ubicación en el mapa
              </label>
              <div class="flex gap-2">
                <input
                  v-model="searchAddress"
                  type="text"
                  placeholder="Ej: Bodega Catena Zapata, Mendoza"
                  class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                  @keyup.enter.prevent="geocodeAddress"
                >
                <BaseButton
                  :loading="geocoding"
                  variant="secondary"
                  type="button"
                  @click="geocodeAddress"
                >
                  Buscar
                </BaseButton>
              </div>
              <p class="text-xs text-gray-500 mt-1">
                Ingresá el nombre del lugar o dirección y presioná "Buscar"
              </p>
            </div>

            <div>
              <label class="text-xs font-medium text-gray-600 block mb-1">
                Nombre del lugar
              </label>
              <input
                v-model="localEvent.locationName"
                type="text"
                placeholder="Ej: Bodega Vista Hermosa"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              >
            </div>
            <div>
              <label class="text-xs font-medium text-gray-600 block mb-1">
                Dirección
              </label>
              <input
                v-model="localEvent.locationAddress"
                type="text"
                placeholder="Se completa automáticamente"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              >
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="text-xs font-medium text-gray-600 block mb-1">
                  Ciudad
                </label>
                <input
                  v-model="localEvent.locationCity"
                  type="text"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                >
              </div>
              <div>
                <label class="text-xs font-medium text-gray-600 block mb-1">
                  Región / Provincia
                </label>
                <input
                  v-model="localEvent.locationRegion"
                  type="text"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                >
              </div>
            </div>

            <div v-if="localEvent.latitude && localEvent.longitude">
              <label class="text-xs font-medium text-gray-600 block mb-2">
                Ubicación en el mapa
              </label>
              <MapView
                :latitude="localEvent.latitude"
                :longitude="localEvent.longitude"
                :interactive="true"
                :marker-title="localEvent.locationName || 'Ubicación del evento'"
                height="250px"
                @location-changed="handleLocationChanged"
              />
            </div>
            <div
              v-else
              class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center text-gray-500 text-sm"
            >
              <p>Buscá una ubicación para ver el mapa</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="text-xs font-medium text-gray-600 block mb-1">
                Precio ($)
              </label>
              <input
                v-model.number="localEvent.price"
                type="number"
                step="0.01"
                min="0"
                placeholder="5000"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              >
            </div>
            <div>
              <label class="text-xs font-medium text-gray-600 block mb-1">
                Máximo de asistentes
              </label>
              <input
                v-model.number="localEvent.maxAttendees"
                type="number"
                min="1"
                placeholder="50"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              >
            </div>
          </div>

          <div class="space-y-3">
            <h4 class="text-sm font-semibold text-gray-700 border-b pb-1">
              Información de Contacto
            </h4>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="text-xs font-medium text-gray-600 block mb-1">
                  Email de contacto
                </label>
                <input
                  v-model="localEvent.contactEmail"
                  type="email"
                  placeholder="contacto@ejemplo.com"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                >
              </div>
              <div>
                <label class="text-xs font-medium text-gray-600 block mb-1">
                  Teléfono de contacto
                </label>
                <input
                  v-model="localEvent.contactPhone"
                  type="tel"
                  placeholder="+54 261 123-4567"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                >
              </div>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="text-xs font-medium text-gray-600 block mb-1">
                URL de Imagen
              </label>
              <input
                v-model="localEvent.imageUrl"
                type="url"
                placeholder="https://..."
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              >
            </div>
            <div>
              <label class="text-xs font-medium text-gray-600 block mb-1">
                URL del Sitio Web
              </label>
              <input
                v-model="localEvent.websiteUrl"
                type="url"
                placeholder="https://..."
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              >
            </div>
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 sticky bottom-0 bg-white">
            <BaseButton
              variant="ghost"
              type="button"
              @click="handleClose"
            >
              Cancelar
            </BaseButton>
            <BaseButton
              :loading="saving"
              type="submit"
            >
              {{ isEditing ? 'Guardar Cambios' : 'Crear Evento' }}
            </BaseButton>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

