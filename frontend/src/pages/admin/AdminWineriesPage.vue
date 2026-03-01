<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '@stores/auth.store'
import http from '@app/http'
import BaseButton from '@components/common/BaseButton.vue'
import BaseCard from '@components/common/BaseCard.vue'
import GoogleMapsEmbed from '@components/common/GoogleMapsEmbed.vue'

interface Winery {
  id: string
  name: string
  country: string
  region: string | null
  subregion: string | null
  description: string | null
  address: string | null
  latitude: number | null
  longitude: number | null
  contactEmail: string | null
  contactPhone: string | null
  websiteUrl: string | null
  logoUrl: string | null
  established: number | null
  winesCount: number
  createdAt: string
}

const wineries = ref<Winery[]>([])
const loading = ref(false)
const search = ref('')
const page = ref(0)
const totalItems = ref(0)
const pageSize = 20
const showModal = ref(false)
const editingWinery = ref<Partial<Winery> | null>(null)
const saving = ref(false)
const confirmDeleteId = ref<string | null>(null)
const searchAddress = ref('')
const geocoding = ref(false)

const totalPages = computed(() => Math.ceil(totalItems.value / pageSize))

const authStore = useAuthStore()
const isAdmin = computed(() => authStore.isAdmin)

async function fetchWineries() {
  loading.value = true
  try {
    const params: Record<string, string | number> = { page: page.value, size: pageSize }
    if (search.value) params.search = search.value
    const res = await http.get('/admin/wineries', { params })
    const responseData = res.data.data ?? res.data
    wineries.value = responseData.items ?? responseData.content ?? []
    totalItems.value = responseData.totalItems ?? responseData.totalElements ?? 0
  } finally {
    loading.value = false
  }
}

async function saveWinery() {
  if (!editingWinery.value || !editingWinery.value.name || !editingWinery.value.country) {
    alert('Por favor completá los campos requeridos: nombre y país')
    return
  }
  saving.value = true
  try {
    const payload = {
      name: editingWinery.value.name,
      country: editingWinery.value.country,
      region: editingWinery.value.region || null,
      subregion: editingWinery.value.subregion || null,
      description: editingWinery.value.description || null,
      address: editingWinery.value.address || null,
      latitude: editingWinery.value.latitude || null,
      longitude: editingWinery.value.longitude || null,
      contactEmail: editingWinery.value.contactEmail || null,
      contactPhone: editingWinery.value.contactPhone || null,
      websiteUrl: editingWinery.value.websiteUrl || null,
      logoUrl: editingWinery.value.logoUrl || null,
      established: editingWinery.value.established || null,
    }
    if (editingWinery.value.id) {
      await http.patch(`/admin/wineries/${editingWinery.value.id}`, payload)
    } else {
      await http.post('/admin/wineries', payload)
    }
    showModal.value = false
    editingWinery.value = null
    await fetchWineries()
  } catch (error: unknown) {
    console.error('Error saving winery:', error instanceof Error ? error.message : error)
    const message = error instanceof Error ? error.message : 'Error desconocido'
    alert('Error al guardar: ' + message)
  } finally {
    saving.value = false
  }
}

async function deleteWinery(id: string) {
  await http.delete(`/admin/wineries/${id}`)
  confirmDeleteId.value = null
  await fetchWineries()
}

function openCreate() {
  editingWinery.value = {
    name: '',
    country: '',
    region: null,
    subregion: null,
    description: null,
    address: null,
    latitude: null,
    longitude: null,
    contactEmail: null,
    contactPhone: null,
    websiteUrl: null,
    logoUrl: null,
    established: null,
  }
  showModal.value = true
}

function openEdit(w: Winery) {
  editingWinery.value = { ...w }
  showModal.value = true
}

async function geocodeAddress() {
  if (!searchAddress.value.trim()) {
    alert('Por favor ingresa un lugar o dirección para buscar')
    return
  }

  geocoding.value = true
  try {
    const apiKey = import.meta.env.VITE_GOOGLE_MAPS_API_KEY

    if (!apiKey) {
      alert('API Key de Google Maps no configurada')
      return
    }

    const query = encodeURIComponent(searchAddress.value.trim())
    const url = `https://maps.googleapis.com/maps/api/geocode/json?address=${query}&key=${apiKey}`

    const response = await fetch(url)
    const data = await response.json()

    if (data.status === 'OK' && data.results.length > 0) {
      const result = data.results[0]
      const location = result.geometry.location


      if (editingWinery.value) {
        editingWinery.value.latitude = location.lat
        editingWinery.value.longitude = location.lng


        if (!editingWinery.value.address) {
          editingWinery.value.address = result.formatted_address
        }
      }

      alert(`✅ Ubicación encontrada: ${result.formatted_address}`)
      searchAddress.value = ''
    } else {
      alert('❌ No se encontró la ubicación. Intenta con un nombre o dirección más específica.')
    }
  } catch (error) {
    console.error('Geocoding error:', error)
    alert('Error al buscar la ubicación. Por favor intenta nuevamente.')
  } finally {
    geocoding.value = false
  }
}

function handleSearch() {
  page.value = 0
  fetchWineries()
}


onMounted(fetchWineries)
</script>

<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold text-gray-900">
        Gestión de Bodegas
      </h1>
      <BaseButton @click="openCreate">
        + Agregar Bodega
      </BaseButton>
    </div>

    <BaseCard>
      <div class="flex gap-3">
        <input
          v-model="search"
          type="text"
          placeholder="Buscar por nombre..."
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
        v-else-if="wineries.length === 0"
        class="p-8 text-center text-gray-500"
      >
        No se encontraron bodegas.
      </div>
      <table
        v-else
        class="w-full text-sm"
      >
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="px-4 py-3 text-left font-medium text-gray-600">
              Nombre
            </th>
            <th class="px-4 py-3 text-left font-medium text-gray-600">
              País / Región
            </th>
            <th class="px-4 py-3 text-left font-medium text-gray-600">
              Fundación
            </th>
            <th class="px-4 py-3 text-left font-medium text-gray-600">
              Vinos
            </th>
            <th class="px-4 py-3 text-right font-medium text-gray-600">
              Acciones
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr
            v-for="w in wineries"
            :key="w.id"
            class="hover:bg-gray-50"
          >
            <td class="px-4 py-3 font-medium text-gray-900">
              {{ w.name }}
            </td>
            <td class="px-4 py-3 text-gray-600">
              {{ w.country }}{{ w.region ? ` / ${w.region}` : '' }}
            </td>
            <td class="px-4 py-3 text-gray-600">
              {{ w.established ?? '-' }}
            </td>
            <td class="px-4 py-3 text-gray-600">
              {{ w.winesCount }} vino{{ w.winesCount !== 1 ? 's' : '' }}
            </td>
            <td class="px-4 py-3 text-right space-x-2">
              <button
                class="text-primary-600 hover:text-primary-800 text-xs font-medium"
                @click="openEdit(w)"
              >
                Editar
              </button>
              <button
                v-if="isAdmin"
                class="text-red-500 hover:text-red-700 text-xs font-medium"
                @click="confirmDeleteId = w.id"
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
        <span>Total: {{ totalItems }} bodegas</span>
        <div class="flex gap-2">
          <button
            :disabled="page === 0"
            :class="page === 0 ? 'opacity-40 cursor-not-allowed' : 'hover:text-primary-600'"
            class="px-2"
            @click="page--; fetchWineries()"
          >
            ← Anterior
          </button>
          <span>Página {{ page + 1 }} / {{ totalPages }}</span>
          <button
            :disabled="page >= totalPages - 1"
            :class="page >= totalPages-1 ? 'opacity-40 cursor-not-allowed' : 'hover:text-primary-600'"
            class="px-2"
            @click="page++; fetchWineries()"
          >
            Siguiente →
          </button>
        </div>
      </div>
    </BaseCard>

    <div
      v-if="showModal && editingWinery"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4 overflow-y-auto"
    >
      <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl my-8">
        <div class="p-6 space-y-4 max-h-[85vh] overflow-y-auto">
          <h2 class="text-lg font-bold text-gray-900 sticky top-0 bg-white pb-2">
            {{ editingWinery.id ? 'Editar' : 'Nueva' }} Bodega
          </h2>

          <div class="space-y-4">
            <div>
              <label class="text-xs font-medium text-gray-600 block mb-1">Nombre *</label>
              <input
                v-model="editingWinery.name"
                type="text"
                required
                placeholder="Ej: Bodega Catena Zapata"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              >
            </div>

            <div>
              <label class="text-xs font-medium text-gray-600 block mb-1">Descripción</label>
              <textarea
                v-model="editingWinery.description"
                rows="4"
                placeholder="Descripción de la bodega..."
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              />
            </div>

            <div class="grid grid-cols-3 gap-4">
              <div>
                <label class="text-xs font-medium text-gray-600 block mb-1">País *</label>
                <input
                  v-model="editingWinery.country"
                  type="text"
                  required
                  placeholder="Argentina"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                >
              </div>
              <div>
                <label class="text-xs font-medium text-gray-600 block mb-1">Región</label>
                <input
                  v-model="editingWinery.region"
                  type="text"
                  placeholder="Mendoza"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                >
              </div>
              <div>
                <label class="text-xs font-medium text-gray-600 block mb-1">Subregión</label>
                <input
                  v-model="editingWinery.subregion"
                  type="text"
                  placeholder="Valle de Uco"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                >
              </div>
            </div>

            <div>
              <label class="text-xs font-medium text-gray-600 block mb-1">Año de fundación</label>
              <input
                v-model.number="editingWinery.established"
                type="number"
                min="1500"
                max="2100"
                placeholder="1902"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              >
            </div>

            <div>
              <label class="text-xs font-medium text-gray-600 block mb-1">Dirección Completa</label>
              <input
                v-model="editingWinery.address"
                type="text"
                placeholder="Calle, número, ciudad"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              >
            </div>

            <div class="border-t pt-4">
              <h4 class="text-sm font-semibold text-gray-700 mb-3">📍 Ubicación en el Mapa</h4>

              <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                <label class="text-xs font-medium text-blue-900 block mb-2">
                  🔍 Buscar ubicación en Google Maps
                </label>
                <div class="flex gap-2">
                  <input
                    v-model="searchAddress"
                    type="text"
                    placeholder="Ej: Bodega Catena Zapata, Mendoza"
                    class="flex-1 border border-blue-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                    @keyup.enter="geocodeAddress"
                  >
                  <button
                    type="button"
                    @click="geocodeAddress"
                    :disabled="geocoding"
                    class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed whitespace-nowrap"
                  >
                    {{ geocoding ? '🔄 Buscando...' : '🔍 Buscar' }}
                  </button>
                </div>
                <p class="text-xs text-blue-700 mt-2">
                  💡 Escribe el nombre de la bodega o su dirección y presiona "Buscar". Las coordenadas se completarán automáticamente.
                </p>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="text-xs font-medium text-gray-600 block mb-1">
                    Latitud <span class="text-green-600">(auto)</span>
                  </label>
                  <input
                    v-model.number="editingWinery.latitude"
                    type="number"
                    step="0.0000001"
                    placeholder="-33.123456"
                    readonly
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-gray-50 cursor-not-allowed"
                  >
                </div>
                <div>
                  <label class="text-xs font-medium text-gray-600 block mb-1">
                    Longitud <span class="text-green-600">(auto)</span>
                  </label>
                  <input
                    v-model.number="editingWinery.longitude"
                    type="number"
                    step="0.0000001"
                    placeholder="-69.123456"
                    readonly
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-gray-50 cursor-not-allowed"
                  >
                </div>
              </div>
              <p class="text-xs text-gray-500 mt-2">
                ℹ️ Las coordenadas GPS se obtienen automáticamente al buscar la ubicación
              </p>

              <div v-if="editingWinery.latitude && editingWinery.longitude" class="mt-4">
                <label class="text-xs font-medium text-gray-600 block mb-2">
                  ✅ Vista previa de la ubicación
                </label>
                <GoogleMapsEmbed
                  mode="view"
                  :center="`${editingWinery.latitude},${editingWinery.longitude}`"
                  :zoom="15"
                  height="300px"
                />
              </div>
            </div>

            <div class="border-t pt-4">
              <h4 class="text-sm font-semibold text-gray-700 mb-3">📞 Información de Contacto</h4>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="text-xs font-medium text-gray-600 block mb-1">Email</label>
                  <input
                    v-model="editingWinery.contactEmail"
                    type="email"
                    placeholder="contacto@bodega.com"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                  >
                </div>
                <div>
                  <label class="text-xs font-medium text-gray-600 block mb-1">Teléfono</label>
                  <input
                    v-model="editingWinery.contactPhone"
                    type="tel"
                    placeholder="+54 261 123 4567"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                  >
                </div>
              </div>
            </div>

            <div>
              <label class="text-xs font-medium text-gray-600 block mb-1">Sitio Web</label>
              <input
                v-model="editingWinery.websiteUrl"
                type="url"
                placeholder="https://www.bodega.com"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              >
            </div>

            <div>
              <label class="text-xs font-medium text-gray-600 block mb-1">URL del Logo</label>
              <input
                v-model="editingWinery.logoUrl"
                type="url"
                placeholder="https://..."
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              >
            </div>
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 sticky bottom-0 bg-white">
            <BaseButton
              variant="ghost"
              @click="showModal = false; editingWinery = null"
            >
              Cancelar
            </BaseButton>
            <BaseButton
              :loading="saving"
              @click="saveWinery"
            >
              {{ editingWinery.id ? 'Guardar Cambios' : 'Crear Bodega' }}
            </BaseButton>
          </div>
        </div>
      </div>
    </div>

    <div
      v-if="confirmDeleteId"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
    >
      <div class="bg-white rounded-xl shadow-xl p-6 max-w-sm w-full space-y-4">
        <h3 class="font-bold text-gray-900">
          ¿Eliminar bodega?
        </h3>
        <p class="text-sm text-gray-600">
          Esta acción eliminará la bodega y todos sus vinos asociados. ¿Continuar?
        </p>
        <div class="flex gap-3 justify-end">
          <BaseButton
            variant="ghost"
            @click="confirmDeleteId = null"
          >
            Cancelar
          </BaseButton>
          <BaseButton
            variant="danger"
            @click="deleteWinery(confirmDeleteId!)"
          >
            Eliminar
          </BaseButton>
        </div>
      </div>
    </div>
  </div>
</template>
