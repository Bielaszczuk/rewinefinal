<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '@stores/auth.store'
import http from '@app/http'
import BaseButton from '@components/common/BaseButton.vue'
import BaseCard from '@components/common/BaseCard.vue'
import GoogleMapsEmbed from '@components/common/GoogleMapsEmbed.vue'

interface RouteStop {
  id?: string
  name: string
  description: string
  type: string
  address: string
  latitude: number | null
  longitude: number | null
  stopOrder: number
  estimatedDuration: number
}

interface Route {
  id: string
  name: string
  description: string | null
  country: string
  region: string | null
  subregion: string | null
  estimatedDuration: number | null
  estimatedDays: number | null
  totalDistance: number | null
  difficulty: string | null
  imageUrl: string | null
  mapEmbedUrl: string | null
  status: string
  recommendedWineTypes: string[] | null
  stops?: RouteStop[]
  createdAt: string
}

const routes = ref<Route[]>([])
const loading = ref(false)
const search = ref('')
const page = ref(0)
const totalItems = ref(0)
const pageSize = 20
const editingRoute = ref<Partial<Route> | null>(null)
const showModal = ref(false)
const saving = ref(false)
const confirmDeleteId = ref<string | null>(null)
const wineTypeInput = ref('')
const editingStops = ref<RouteStop[]>([])
const newStop = ref<Partial<RouteStop>>({
  name: '',
  description: '',
  type: 'winery',
  address: '',
  latitude: null,
  longitude: null,
  estimatedDuration: 30,
})


interface Winery {
  id: string
  name: string
  country: string
  region: string | null
  latitude: number | null
  longitude: number | null
  address: string | null
}

const wineries = ref<Winery[]>([])
const selectedWineryId = ref('')
const loadingWineries = ref(false)

const STOP_TYPES = [
  { value: 'winery', label: 'Bodega' },
  { value: 'restaurant', label: 'Restaurante' },
  { value: 'viewpoint', label: 'Mirador' },
  { value: 'attraction', label: 'Atracción' },
  { value: 'accommodation', label: 'Alojamiento' },
]


const generatedRouteQuery = computed(() => {
  const wineryStops = editingStops.value.filter(s => s.type === 'winery' && (s.latitude || s.name))

  if (wineryStops.length === 0) return null
  if (wineryStops.length === 1) {
    return wineryStops[0].name
  }

  return {
    origin: wineryStops[0].latitude && wineryStops[0].longitude
      ? `${wineryStops[0].latitude},${wineryStops[0].longitude}`
      : wineryStops[0].name,
    destination: wineryStops[wineryStops.length - 1].latitude && wineryStops[wineryStops.length - 1].longitude
      ? `${wineryStops[wineryStops.length - 1].latitude},${wineryStops[wineryStops.length - 1].longitude}`
      : wineryStops[wineryStops.length - 1].name,
    waypoints: wineryStops.slice(1, -1).map(stop =>
      stop.latitude && stop.longitude
        ? `${stop.latitude},${stop.longitude}`
        : stop.name
    )
  }
})

const totalPages = computed(() => Math.ceil(totalItems.value / pageSize))

const authStore = useAuthStore()
const isAdmin = computed(() => authStore.isAdmin)

const STATUS_OPTIONS = ['draft', 'active', 'archived']
const DIFFICULTY_OPTIONS = ['easy', 'moderate', 'challenging']
const WINE_TYPE_OPTIONS = ['RED', 'WHITE', 'ROSE', 'SPARKLING', 'DESSERT', 'FORTIFIED']

async function fetchRoutes() {
  loading.value = true
  try {
    const params: Record<string, string | number> = { page: page.value, size: pageSize }
    if (search.value) params.search = search.value
    const res = await http.get('/admin/routes', { params })
    const responseData = res.data.data ?? res.data
    routes.value = responseData.items ?? responseData.content ?? []
    totalItems.value = responseData.totalItems ?? responseData.totalElements ?? 0
  } finally {
    loading.value = false
  }
}

async function fetchWineries() {
  if (wineries.value.length > 0) return

  loadingWineries.value = true
  try {
    const res = await http.get('/admin/wineries', { params: { page: 0, size: 1000 } })
    const responseData = res.data.data ?? res.data
    wineries.value = responseData.items ?? responseData.content ?? []
  } catch (error) {
    console.error('Error fetching wineries:', error)
  } finally {
    loadingWineries.value = false
  }
}

function addWineryAsStop() {
  if (!selectedWineryId.value) {
    alert('Por favor selecciona una bodega')
    return
  }

  const winery = wineries.value.find(w => w.id === selectedWineryId.value)
  if (!winery) return

  const stop: RouteStop = {
    name: winery.name,
    description: `Bodega en ${winery.region || winery.country}`,
    type: 'winery',
    address: winery.address || '',
    latitude: winery.latitude,
    longitude: winery.longitude,
    stopOrder: editingStops.value.length + 1,
    estimatedDuration: 60,
  }

  editingStops.value.push(stop)
  selectedWineryId.value = ''
}

async function saveRoute() {
  if (!editingRoute.value || !editingRoute.value.name || !editingRoute.value.country) {
    alert('Por favor completá los campos requeridos: nombre y país')
    return
  }
  saving.value = true
  try {
    const payload = {
      name: editingRoute.value.name,
      description: editingRoute.value.description || null,
      country: editingRoute.value.country,
      region: editingRoute.value.region || null,
      subregion: editingRoute.value.subregion || null,
      estimatedDuration: editingRoute.value.estimatedDuration || null,
      estimatedDays: editingRoute.value.estimatedDays || null,
      totalDistance: editingRoute.value.totalDistance || null,
      difficulty: editingRoute.value.difficulty || null,
      imageUrl: editingRoute.value.imageUrl || null,
      mapEmbedUrl: editingRoute.value.mapEmbedUrl || null,
      status: editingRoute.value.status || 'draft',
      recommendedWineTypes: editingRoute.value.recommendedWineTypes || null,
      stops: editingStops.value.length > 0 ? editingStops.value : null,
    }
    if (editingRoute.value.id) {
      await http.patch(`/admin/routes/${editingRoute.value.id}`, payload)
    } else {
      await http.post('/admin/routes', payload)
    }
    showModal.value = false
    editingRoute.value = null
    editingStops.value = []
    await fetchRoutes()
  } catch (error: unknown) {
    console.error('Error saving route:', error instanceof Error ? error.message : error)
    const message = error instanceof Error ? error.message : 'Error desconocido'
    alert('Error al guardar: ' + message)
  } finally {
    saving.value = false
  }
}

async function deleteRoute(id: string) {
  await http.delete(`/admin/routes/${id}`)
  confirmDeleteId.value = null
  await fetchRoutes()
}

function openCreate() {
  editingRoute.value = {
    name: '',
    description: null,
    country: '',
    region: null,
    subregion: null,
    estimatedDuration: null,
    estimatedDays: null,
    totalDistance: null,
    difficulty: 'moderate',
    imageUrl: null,
    mapEmbedUrl: null,
    status: 'draft',
    recommendedWineTypes: [],
    stops: [],
  }
  editingStops.value = []
  showModal.value = true
  fetchWineries()
}

async function openEdit(r: Route) {
  loading.value = true
  try {
    const res = await http.get(`/admin/routes/${r.id}`)
    const routeDetail = res.data.data ?? res.data
    editingRoute.value = {
      ...routeDetail,
      recommendedWineTypes: routeDetail.recommendedWineTypes || [],
      mapEmbedUrl: routeDetail.mapEmbedUrl || null,
    }
    editingStops.value = routeDetail.stops ? [...routeDetail.stops].sort((a: RouteStop, b: RouteStop) => a.stopOrder - b.stopOrder) : []
    showModal.value = true
    fetchWineries()
  } catch (error) {
    console.error('Error fetching route details:', error)
    alert('Error al cargar los detalles de la ruta')
  } finally {
    loading.value = false
  }
}

function addWineType() {
  if (!wineTypeInput.value.trim()) return
  if (!editingRoute.value!.recommendedWineTypes) editingRoute.value!.recommendedWineTypes = []
  editingRoute.value!.recommendedWineTypes.push(wineTypeInput.value.trim())
  wineTypeInput.value = ''
}

function removeWineType(index: number) {
  editingRoute.value!.recommendedWineTypes!.splice(index, 1)
}

function addStop() {
  if (!newStop.value.name) {
    alert('El nombre de la parada es requerido')
    return
  }

  const stop: RouteStop = {
    name: newStop.value.name!,
    description: newStop.value.description || '',
    type: newStop.value.type || 'winery',
    address: newStop.value.address || '',
    latitude: newStop.value.latitude || null,
    longitude: newStop.value.longitude || null,
    stopOrder: editingStops.value.length + 1,
    estimatedDuration: newStop.value.estimatedDuration || 30,
  }

  editingStops.value.push(stop)

  newStop.value = {
    name: '',
    description: '',
    type: 'winery',
    address: '',
    latitude: null,
    longitude: null,
    estimatedDuration: 30,
  }
}

function removeStop(index: number) {
  editingStops.value.splice(index, 1)

  editingStops.value.forEach((stop, idx) => {
    stop.stopOrder = idx + 1
  })
}

function moveStopUp(index: number) {
  if (index === 0) return
  const temp = editingStops.value[index]
  editingStops.value[index] = editingStops.value[index - 1]
  editingStops.value[index - 1] = temp

  editingStops.value.forEach((stop, idx) => {
    stop.stopOrder = idx + 1
  })
}

function moveStopDown(index: number) {
  if (index === editingStops.value.length - 1) return
  const temp = editingStops.value[index]
  editingStops.value[index] = editingStops.value[index + 1]
  editingStops.value[index + 1] = temp

  editingStops.value.forEach((stop, idx) => {
    stop.stopOrder = idx + 1
  })
}

function statusColor(s: string) {
  return { active: 'bg-green-100 text-green-700', draft: 'bg-gray-100 text-gray-600', archived: 'bg-yellow-100 text-yellow-700' }[s] ?? 'bg-gray-100 text-gray-600'
}

function handleSearch() { page.value = 0; fetchRoutes() }
onMounted(fetchRoutes)
</script>

<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold text-gray-900">
        Gestión de Rutas
      </h1>
      <BaseButton @click="openCreate">+ Nueva Ruta</BaseButton>
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
        v-else-if="routes.length === 0"
        class="p-8 text-center text-gray-500"
      >
        No se encontraron rutas.
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
              Estado
            </th>
            <th class="px-4 py-3 text-left font-medium text-gray-600">
              Dificultad
            </th>
            <th class="px-4 py-3 text-left font-medium text-gray-600">
              Días / Km
            </th>
            <th class="px-4 py-3 text-right font-medium text-gray-600">
              Acciones
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr
            v-for="r in routes"
            :key="r.id"
            class="hover:bg-gray-50"
          >
            <td class="px-4 py-3 font-medium text-gray-900">
              {{ r.name }}
            </td>
            <td class="px-4 py-3 text-gray-600 text-xs">
              {{ r.country }}{{ r.region ? ` / ${r.region}` : '' }}
            </td>
            <td class="px-4 py-3">
              <span
                :class="statusColor(r.status)"
                class="text-xs px-2 py-0.5 rounded-full font-medium"
              >{{ r.status }}</span>
            </td>
            <td class="px-4 py-3 text-gray-600 text-xs">
              {{ r.difficulty ?? '-' }}
            </td>
            <td class="px-4 py-3 text-gray-600 text-xs">
              {{ r.estimatedDays ?? '-' }} días / {{ r.totalDistance ? `${r.totalDistance}km` : '-' }}
            </td>
            <td class="px-4 py-3 text-right space-x-2">
              <button
                class="text-primary-600 hover:text-primary-800 text-xs font-medium"
                @click="openEdit(r)"
              >
                Editar
              </button>
              <button
                v-if="isAdmin"
                class="text-red-500 hover:text-red-700 text-xs font-medium"
                @click="confirmDeleteId = r.id"
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
        <span>Total: {{ totalItems }} rutas</span>
        <div class="flex gap-2">
          <button
            :disabled="page===0"
            :class="page===0?'opacity-40 cursor-not-allowed':'hover:text-primary-600'"
            class="px-2"
            @click="page--; fetchRoutes()"
          >
            ← Anterior
          </button>
          <span>Página {{ page + 1 }} / {{ totalPages }}</span>
          <button
            :disabled="page>=totalPages-1"
            :class="page>=totalPages-1?'opacity-40 cursor-not-allowed':'hover:text-primary-600'"
            class="px-2"
            @click="page++; fetchRoutes()"
          >
            Siguiente →
          </button>
        </div>
      </div>
    </BaseCard>

    <div
      v-if="showModal && editingRoute"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4 overflow-y-auto"
    >
      <div class="bg-white rounded-xl shadow-xl w-full max-w-3xl my-8">
        <div class="p-6 space-y-4 max-h-[85vh] overflow-y-auto">
          <h2 class="text-lg font-bold text-gray-900 sticky top-0 bg-white pb-2">
            {{ editingRoute.id ? 'Editar' : 'Nueva' }} Ruta
          </h2>

          <div class="space-y-4">
            <div>
              <label class="text-xs font-medium text-gray-600 block mb-1">Nombre *</label>
              <input
                v-model="editingRoute.name"
                type="text"
                required
                placeholder="Ej: Ruta del Vino Valle de Uco"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              >
            </div>

            <div>
              <label class="text-xs font-medium text-gray-600 block mb-1">Descripción</label>
              <textarea
                v-model="editingRoute.description"
                rows="4"
                placeholder="Descripción detallada de la ruta..."
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              ></textarea>
            </div>

            <div class="grid grid-cols-3 gap-4">
              <div>
                <label class="text-xs font-medium text-gray-600 block mb-1">País *</label>
                <input
                  v-model="editingRoute.country"
                  type="text"
                  required
                  placeholder="Argentina"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                >
              </div>
              <div>
                <label class="text-xs font-medium text-gray-600 block mb-1">Región</label>
                <input
                  v-model="editingRoute.region"
                  type="text"
                  placeholder="Mendoza"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                >
              </div>
              <div>
                <label class="text-xs font-medium text-gray-600 block mb-1">Subregión</label>
                <input
                  v-model="editingRoute.subregion"
                  type="text"
                  placeholder="Valle de Uco"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                >
              </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
              <div>
                <label class="text-xs font-medium text-gray-600 block mb-1">Estado *</label>
                <select
                  v-model="editingRoute.status"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                >
                  <option
                    v-for="s in STATUS_OPTIONS"
                    :key="s"
                    :value="s"
                  >
                    {{ s === 'draft' ? 'Borrador' : s === 'active' ? 'Activa' : 'Archivada' }}
                  </option>
                </select>
              </div>
              <div>
                <label class="text-xs font-medium text-gray-600 block mb-1">Dificultad</label>
                <select
                  v-model="editingRoute.difficulty"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                >
                  <option value="">-- Seleccionar --</option>
                  <option
                    v-for="d in DIFFICULTY_OPTIONS"
                    :key="d"
                    :value="d"
                  >
                    {{ d === 'easy' ? 'Fácil' : d === 'moderate' ? 'Moderada' : 'Difícil' }}
                  </option>
                </select>
              </div>
              <div>
                <label class="text-xs font-medium text-gray-600 block mb-1">Días estimados</label>
                <input
                  v-model.number="editingRoute.estimatedDays"
                  type="number"
                  min="1"
                  placeholder="3"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                >
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="text-xs font-medium text-gray-600 block mb-1">Distancia total (km)</label>
                <input
                  v-model.number="editingRoute.totalDistance"
                  type="number"
                  step="0.1"
                  min="0"
                  placeholder="150.5"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                >
              </div>
              <div>
                <label class="text-xs font-medium text-gray-600 block mb-1">Duración estimada (minutos)</label>
                <input
                  v-model.number="editingRoute.estimatedDuration"
                  type="number"
                  min="0"
                  placeholder="180"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                >
              </div>
            </div>

            <div>
              <label class="text-xs font-medium text-gray-600 block mb-1">Tipos de vino recomendados</label>
              <div class="flex gap-2 mb-2">
                <select
                  v-model="wineTypeInput"
                  class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                >
                  <option value="">-- Seleccionar tipo --</option>
                  <option
                    v-for="wt in WINE_TYPE_OPTIONS"
                    :key="wt"
                    :value="wt"
                  >
                    {{ wt }}
                  </option>
                </select>
                <button
                  type="button"
                  @click="addWineType"
                  class="px-4 py-2 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700"
                >
                  + Agregar
                </button>
              </div>
              <div
                v-if="editingRoute.recommendedWineTypes && editingRoute.recommendedWineTypes.length"
                class="flex flex-wrap gap-2"
              >
                <span
                  v-for="(wt, idx) in editingRoute.recommendedWineTypes"
                  :key="idx"
                  class="inline-flex items-center gap-1 bg-wine-100 text-wine-700 px-2 py-1 rounded text-xs"
                >
                  {{ wt }}
                  <button
                    type="button"
                    @click="removeWineType(idx)"
                    class="hover:text-wine-900"
                  >
                    ×
                  </button>
                </span>
              </div>
            </div>

            <div>
              <label class="text-xs font-medium text-gray-600 block mb-1">URL de Imagen</label>
              <input
                v-model="editingRoute.imageUrl"
                type="url"
                placeholder="https://..."
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              >
            </div>

            <div class="border-t pt-4 mt-4">
              <h3 class="text-sm font-semibold text-gray-700 mb-3">Paradas de la Ruta</h3>

              <div class="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                <div class="text-xs font-medium text-blue-900 mb-2">
                  ➕ Agregar Bodega desde la Base de Datos
                </div>
                <div class="flex gap-2">
                  <select
                    v-model="selectedWineryId"
                    class="flex-1 border border-blue-300 rounded-lg px-3 py-2 text-sm bg-white"
                    :disabled="loadingWineries"
                  >
                    <option value="">
                      {{ loadingWineries ? 'Cargando bodegas...' : '-- Seleccionar bodega --' }}
                    </option>
                    <option
                      v-for="winery in wineries"
                      :key="winery.id"
                      :value="winery.id"
                    >
                      {{ winery.name }} - {{ winery.region || winery.country }}
                    </option>
                  </select>
                  <button
                    type="button"
                    @click="addWineryAsStop"
                    class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 disabled:opacity-50"
                    :disabled="!selectedWineryId || loadingWineries"
                  >
                    + Agregar
                  </button>
                </div>
                <p class="text-xs text-blue-700 mt-2">
                  💡 Las bodegas se agregarán con su ubicación GPS automáticamente
                </p>
              </div>

              <div v-if="generatedRouteQuery" class="mb-4">
                <label class="text-xs font-medium text-gray-600 block mb-2">
                  🗺️ Vista previa de la ruta (generada automáticamente)
                </label>

                <GoogleMapsEmbed
                  v-if="typeof generatedRouteQuery === 'string'"
                  mode="place"
                  :query="generatedRouteQuery"
                  height="350px"
                />

                <GoogleMapsEmbed
                  v-else
                  mode="directions"
                  :origin="generatedRouteQuery.origin"
                  :destination="generatedRouteQuery.destination"
                  :waypoints="generatedRouteQuery.waypoints"
                  height="350px"
                />

                <p class="text-xs text-green-700 mt-2 bg-green-50 p-2 rounded">
                  ✅ Ruta generada con {{ editingStops.filter(s => s.type === 'winery').length }} bodega(s)
                </p>
              </div>

              <div v-if="editingStops.length > 0" class="space-y-2 mb-4">
                <div
                  v-for="(stop, index) in editingStops"
                  :key="index"
                  class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg"
                >
                  <div class="flex-shrink-0 w-8 h-8 bg-wine-600 text-white rounded-full flex items-center justify-center font-semibold text-sm">
                    {{ index + 1 }}
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="font-medium text-sm text-gray-900">{{ stop.name }}</div>
                    <div class="text-xs text-gray-500">
                      {{ stop.type }} • {{ stop.estimatedDuration }}min
                      <span v-if="stop.latitude && stop.longitude" class="text-green-600">📍 GPS</span>
                    </div>
                  </div>
                  <div class="flex gap-1">
                    <button
                      v-if="index > 0"
                      type="button"
                      @click="moveStopUp(index)"
                      class="p-1 text-gray-400 hover:text-gray-600"
                      title="Subir"
                    >
                      ↑
                    </button>
                    <button
                      v-if="index < editingStops.length - 1"
                      type="button"
                      @click="moveStopDown(index)"
                      class="p-1 text-gray-400 hover:text-gray-600"
                      title="Bajar"
                    >
                      ↓
                    </button>
                    <button
                      type="button"
                      @click="removeStop(index)"
                      class="p-1 text-red-500 hover:text-red-700"
                      title="Eliminar"
                    >
                      ×
                    </button>
                  </div>
                </div>
              </div>

              <div v-else class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                <p class="text-sm text-gray-500">
                  👆 Selecciona bodegas arriba para armar tu ruta
                </p>
              </div>

              <details class="border border-gray-200 rounded-lg mt-4">
                <summary class="px-3 py-2 bg-gray-100 cursor-pointer text-xs font-medium text-gray-700 hover:bg-gray-200">
                  ➕ Agregar parada manual (restaurante, mirador, etc.)
                </summary>
                <div class="p-3 space-y-3">
                  <div class="grid grid-cols-2 gap-2">
                    <div>
                      <input
                        v-model="newStop.name"
                        type="text"
                        placeholder="Nombre *"
                        class="w-full border border-gray-300 rounded px-2 py-1 text-xs"
                      >
                    </div>
                    <div>
                      <select
                        v-model="newStop.type"
                        class="w-full border border-gray-300 rounded px-2 py-1 text-xs"
                      >
                        <option v-for="type in STOP_TYPES" :key="type.value" :value="type.value">
                          {{ type.label }}
                        </option>
                      </select>
                    </div>
                  </div>

                  <div>
                    <input
                      v-model="newStop.address"
                      type="text"
                      placeholder="Dirección"
                      class="w-full border border-gray-300 rounded px-2 py-1 text-xs"
                    >
                  </div>

                  <div>
                    <textarea
                      v-model="newStop.description"
                      rows="2"
                      placeholder="Descripción"
                      class="w-full border border-gray-300 rounded px-2 py-1 text-xs"
                    ></textarea>
                  </div>

                  <div class="grid grid-cols-3 gap-2">
                    <div>
                      <input
                        v-model.number="newStop.latitude"
                        type="number"
                        step="0.0000001"
                        placeholder="Latitud"
                        class="w-full border border-gray-300 rounded px-2 py-1 text-xs"
                      >
                    </div>
                    <div>
                      <input
                        v-model.number="newStop.longitude"
                        type="number"
                        step="0.0000001"
                        placeholder="Longitud"
                        class="w-full border border-gray-300 rounded px-2 py-1 text-xs"
                      >
                    </div>
                    <div>
                      <input
                        v-model.number="newStop.estimatedDuration"
                        type="number"
                        min="1"
                        placeholder="Minutos"
                        class="w-full border border-gray-300 rounded px-2 py-1 text-xs"
                      >
                    </div>
                  </div>

                  <button
                    type="button"
                    @click="addStop"
                    class="w-full px-3 py-2 bg-gray-600 text-white text-xs rounded-lg hover:bg-gray-700"
                  >
                    + Agregar Parada Manual
                  </button>
                </div>
              </details>
            </div>
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 sticky bottom-0 bg-white">
            <BaseButton
              variant="ghost"
              @click="showModal = false; editingRoute = null"
            >
              Cancelar
            </BaseButton>
            <BaseButton
              :loading="saving"
              @click="saveRoute"
            >
              {{ editingRoute.id ? 'Guardar Cambios' : 'Crear Ruta' }}
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
          ¿Eliminar ruta?
        </h3>
        <div class="flex gap-3 justify-end">
          <BaseButton
            variant="ghost"
            @click="confirmDeleteId = null"
          >
            Cancelar
          </BaseButton>
          <BaseButton
            variant="danger"
            @click="deleteRoute(confirmDeleteId!)"
          >
            Eliminar
          </BaseButton>
        </div>
      </div>
    </div>
  </div>
</template>

