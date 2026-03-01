<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '@stores/auth.store'
import http from '@app/http'
import BaseButton from '@components/common/BaseButton.vue'
import BaseCard from '@components/common/BaseCard.vue'

const GRAPE_VARIETIES = [
  'Malbec',
  'Cabernet Sauvignon',
  'Merlot',
  'Pinot Noir',
  'Syrah',
  'Tempranillo',
  'Sangiovese',
  'Nebbiolo',
  'Grenache',
  'Zinfandel',
  'Carménère',
  'Petit Verdot',
  'Cabernet Franc',
  'Bonarda',
  'Tannat',
  'Chardonnay',
  'Sauvignon Blanc',
  'Riesling',
  'Pinot Grigio',
  'Gewürztraminer',
  'Viognier',
  'Chenin Blanc',
  'Semillón',
  'Torrontés',
  'Albariño',
  'Verdejo',
  'Moscatel',
  'Pedro Ximénez',
  'Garnacha Blanca',
  'Blend',
  'Otra'
]


interface Wine {
  id: string
  name: string
  vintage: number | null
  wineType: string
  wineryId: string
  wineryName: string
  region: string
  country: string
  style: string | null
  grapes: string[]
  alcoholContent: number | null
  descriptionEn: string | null
  descriptionEs: string | null
  imageUrl: string | null
  priceMin: number | null
  priceMax: number | null
  ratingAverage: number | null
  isFeatured: boolean
  isActive: boolean
  createdAt: string
}

interface Winery {
  id: string
  name: string
  region: string
}

const wines = ref<Wine[]>([])
const wineries = ref<Winery[]>([])
const loading = ref(false)
const search = ref('')
const page = ref(0)
const totalItems = ref(0)
const pageSize = 20
const showModal = ref(false)
const editingWine = ref<Partial<Wine> | null>(null)
const saving = ref(false)
const confirmDeleteId = ref<string | null>(null)
const grapeSelect = ref('')

const authStore = useAuthStore()
const isAdmin = computed(() => authStore.isAdmin)
const totalPages = computed(() => Math.ceil(totalItems.value / pageSize))

async function fetchWineries() {
  try {
    const res = await http.get('/admin/wineries', { params: { page: 0, size: 100 } })
    const responseData = res.data.data ?? res.data
    wineries.value = responseData.items ?? responseData.content ?? []
  } catch (e) {
    console.error('Error loading wineries:', e)
  }
}

async function fetchWines() {
  loading.value = true
  try {
    const params: Record<string, string | number> = { page: page.value, size: pageSize }
    if (search.value) params.search = search.value
    const res = await http.get('/admin/wines', { params })
    const responseData = res.data.data ?? res.data
    wines.value = responseData.items ?? responseData.content ?? []
    totalItems.value = responseData.totalItems ?? responseData.totalElements ?? 0
  } finally {
    loading.value = false
  }
}

async function saveWine() {
  if (!editingWine.value || !editingWine.value.name || !editingWine.value.wineryId) {
    alert('Por favor completá el nombre y la bodega')
    return
  }
  saving.value = true
  try {
    const payload = {
      name: editingWine.value.name,
      wineType: editingWine.value.wineType || 'RED',
      wineryId: editingWine.value.wineryId,
      vintage: editingWine.value.vintage || null,
      style: editingWine.value.style || null,
      grapes: editingWine.value.grapes || [],
      alcoholContent: editingWine.value.alcoholContent || null,
      descriptionEn: editingWine.value.descriptionEn || null,
      descriptionEs: editingWine.value.descriptionEs || null,
      imageUrl: editingWine.value.imageUrl || null,
      priceMin: editingWine.value.priceMin || null,
      priceMax: editingWine.value.priceMax || null,
      isFeatured: editingWine.value.isFeatured || false,
      isActive: editingWine.value.isActive !== undefined ? editingWine.value.isActive : true,
    }
    if (editingWine.value.id) {
      await http.patch(`/admin/wines/${editingWine.value.id}`, payload)
    } else {
      await http.post('/admin/wines', payload)
    }
    showModal.value = false
    editingWine.value = null
    await fetchWines()
  } catch (error: unknown) {
    console.error('Error saving wine:', error instanceof Error ? error.message : error)
    const message = error instanceof Error ? error.message : 'Error desconocido'
    alert('Error al guardar: ' + message)
  } finally {
    saving.value = false
  }
}

async function deleteWine(id: string) {
  await http.delete(`/admin/wines/${id}`)
  confirmDeleteId.value = null
  await fetchWines()
}

function openCreate() {
  editingWine.value = {
    wineType: 'RED',
    isFeatured: false,
    isActive: true,
    grapes: [],
    priceMin: null,
    priceMax: null,
  }
  showModal.value = true
}

function openEdit(w: Wine) {
  editingWine.value = { ...w, grapes: w.grapes || [] }
  showModal.value = true
}

function addGrape() {
  if (!grapeSelect.value) return
  if (!editingWine.value!.grapes) editingWine.value!.grapes = []
  if (!editingWine.value!.grapes.includes(grapeSelect.value)) {
    editingWine.value!.grapes.push(grapeSelect.value)
  }
  grapeSelect.value = ''
}

function removeGrape(index: number) {
  editingWine.value!.grapes!.splice(index, 1)
}

function handleSearch() {
  page.value = 0
  fetchWines()
}

onMounted(() => {
  fetchWineries()
  fetchWines()
})
</script>

<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold text-gray-900">Gestión de Vinos</h1>
      <BaseButton @click="openCreate">+ Agregar Vino</BaseButton>
    </div>

    <BaseCard>
      <div class="flex gap-3">
        <input v-model="search" @keyup.enter="handleSearch" type="text" placeholder="Buscar por nombre..."
          class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" />
        <BaseButton @click="handleSearch" variant="secondary">Buscar</BaseButton>
      </div>
    </BaseCard>

    <BaseCard padding="none">
      <div v-if="loading" class="p-8 text-center text-gray-500">Cargando...</div>
      <div v-else-if="wines.length === 0" class="p-8 text-center text-gray-500">No se encontraron vinos.</div>
      <table v-else class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="px-4 py-3 text-left font-medium text-gray-600">Nombre</th>
            <th class="px-4 py-3 text-left font-medium text-gray-600">Tipo</th>
            <th class="px-4 py-3 text-left font-medium text-gray-600">Bodega</th>
            <th class="px-4 py-3 text-left font-medium text-gray-600">Precio</th>
            <th class="px-4 py-3 text-left font-medium text-gray-600">Rating</th>
            <th class="px-4 py-3 text-left font-medium text-gray-600">Estado</th>
            <th class="px-4 py-3 text-right font-medium text-gray-600">Acciones</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="w in wines" :key="w.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 font-medium text-gray-900">
              {{ w.name }} <span v-if="w.vintage" class="text-gray-400 font-normal">{{ w.vintage }}</span>
              <span v-if="w.isFeatured" class="ml-1 text-xs bg-yellow-100 text-yellow-700 px-1.5 py-0.5 rounded">★</span>
            </td>
            <td class="px-4 py-3 text-gray-600">{{ w.wineType }}</td>
            <td class="px-4 py-3 text-gray-600">{{ w.wineryName }}</td>
            <td class="px-4 py-3 text-gray-600">
              <span v-if="w.priceMin">$ {{ w.priceMin }}{{ w.priceMax && w.priceMax !== w.priceMin ? ` - ${w.priceMax}` : '' }}</span>
              <span v-else class="text-gray-400">-</span>
            </td>
            <td class="px-4 py-3 text-gray-600">{{ w.ratingAverage ?? '-' }}</td>
            <td class="px-4 py-3">
              <span :class="w.isActive ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                class="text-xs px-2 py-0.5 rounded-full font-medium">
                {{ w.isActive ? 'Activo' : 'Inactivo' }}
              </span>
            </td>
            <td class="px-4 py-3 text-right space-x-2">
              <button @click="openEdit(w)" class="text-primary-600 hover:text-primary-800 text-xs font-medium">Editar</button>
              <button v-if="isAdmin" @click="confirmDeleteId = w.id" class="text-red-500 hover:text-red-700 text-xs font-medium">Eliminar</button>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="totalPages > 1" class="px-4 py-3 border-t border-gray-200 flex justify-between items-center text-sm text-gray-600">
        <span>Total: {{ totalItems }} vinos</span>
        <div class="flex gap-2">
          <button :disabled="page === 0" @click="page--; fetchWines()" :class="page === 0 ? 'opacity-40 cursor-not-allowed' : 'hover:text-primary-600'" class="px-2">← Anterior</button>
          <span>Página {{ page + 1 }} / {{ totalPages }}</span>
          <button :disabled="page >= totalPages - 1" @click="page++; fetchWines()" :class="page >= totalPages-1 ? 'opacity-40 cursor-not-allowed' : 'hover:text-primary-600'" class="px-2">Siguiente →</button>
        </div>
      </div>
    </BaseCard>

    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4 overflow-y-auto">
      <div class="bg-white rounded-xl shadow-xl w-full max-w-3xl my-8">
        <div class="p-6 space-y-4 max-h-[85vh] overflow-y-auto">
          <h2 class="text-lg font-bold text-gray-900 sticky top-0 bg-white pb-2">
            {{ editingWine?.id ? 'Editar' : 'Nuevo' }} Vino
          </h2>

          <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
              <label class="text-xs font-medium text-gray-600 block mb-1">Nombre *</label>
              <input v-model="editingWine!.name" type="text" required
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500" />
            </div>

            <div class="col-span-2">
              <label class="text-xs font-medium text-gray-600 block mb-1">Bodega *</label>
              <select v-model="editingWine!.wineryId" required
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <option value="">-- Seleccionar Bodega --</option>
                <option v-for="w in wineries" :key="w.id" :value="w.id">{{ w.name }} ({{ w.region }})</option>
              </select>
            </div>

            <div>
              <label class="text-xs font-medium text-gray-600 block mb-1">Tipo *</label>
              <select v-model="editingWine!.wineType"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <option value="RED">Tinto</option>
                <option value="WHITE">Blanco</option>
                <option value="ROSE">Rosé</option>
                <option value="SPARKLING">Espumante</option>
                <option value="DESSERT">Dulce</option>
                <option value="FORTIFIED">Fortificado</option>
              </select>
            </div>

            <div>
              <label class="text-xs font-medium text-gray-600 block mb-1">Cosecha</label>
              <input v-model.number="editingWine!.vintage" type="number" min="1800" max="2100" placeholder="2020"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500" />
            </div>

            <div>
              <label class="text-xs font-medium text-gray-600 block mb-1">Estilo</label>
              <input v-model="editingWine!.style" type="text" placeholder="Reserva, Gran Reserva..."
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500" />
            </div>

            <div>
              <label class="text-xs font-medium text-gray-600 block mb-1">Alcohol (%)</label>
              <input v-model.number="editingWine!.alcoholContent" type="number" step="0.1" min="0" max="25" placeholder="13.5"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500" />
            </div>
          </div>

          <div>
            <label class="text-xs font-medium text-gray-600 block mb-1">Uvas / Varietales</label>
            <div class="flex gap-2 mb-2">
              <select v-model="grapeSelect"
                class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <option value="">Seleccionar varietal...</option>
                <option v-for="grape in GRAPE_VARIETIES" :key="grape" :value="grape">{{ grape }}</option>
              </select>
              <button type="button" @click="addGrape"
                class="px-4 py-2 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700">+ Agregar</button>
            </div>
            <div v-if="editingWine!.grapes && editingWine!.grapes.length" class="flex flex-wrap gap-2">
              <span v-for="(grape, idx) in editingWine!.grapes" :key="idx"
                class="inline-flex items-center gap-1 bg-wine-100 text-wine-700 px-2 py-1 rounded text-xs">
                {{ grape }}
                <button type="button" @click="removeGrape(idx)" class="hover:text-wine-900">×</button>
              </span>
            </div>
          </div>

          <div>
            <label class="text-xs font-medium text-gray-600 block mb-1">Descripción (Español)</label>
            <textarea v-model="editingWine!.descriptionEs" rows="3"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"></textarea>
          </div>

          <div>
            <label class="text-xs font-medium text-gray-600 block mb-1">Descripción (English)</label>
            <textarea v-model="editingWine!.descriptionEn" rows="3"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"></textarea>
          </div>

          <div>
            <label class="text-xs font-medium text-gray-600 block mb-1">URL de Imagen</label>
            <input v-model="editingWine!.imageUrl" type="url" placeholder="https://..."
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500" />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="text-xs font-medium text-gray-600 block mb-1">Precio mínimo ($)</label>
              <input v-model.number="editingWine!.priceMin" type="number" step="0.01" min="0" placeholder="1500"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500" />
            </div>
            <div>
              <label class="text-xs font-medium text-gray-600 block mb-1">Precio máximo ($)</label>
              <input v-model.number="editingWine!.priceMax" type="number" step="0.01" min="0" placeholder="2500"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500" />
            </div>
          </div>

          <div class="flex items-center gap-4">
            <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
              <input v-model="editingWine!.isFeatured" type="checkbox" class="w-4 h-4 text-primary-600 rounded focus:ring-2 focus:ring-primary-500" />
              <span>⭐ Destacado</span>
            </label>
            <label v-if="editingWine?.id" class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
              <input v-model="editingWine!.isActive" type="checkbox" class="w-4 h-4 text-primary-600 rounded focus:ring-2 focus:ring-primary-500" />
              <span>✓ Activo</span>
            </label>
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 sticky bottom-0 bg-white">
            <BaseButton variant="ghost" @click="showModal = false; editingWine = null">Cancelar</BaseButton>
            <BaseButton @click="saveWine" :loading="saving">{{ editingWine?.id ? 'Guardar Cambios' : 'Crear Vino' }}</BaseButton>
          </div>
        </div>
      </div>
    </div>

    <div v-if="confirmDeleteId" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl shadow-xl p-6 max-w-sm w-full space-y-4">
        <h3 class="font-bold text-gray-900">¿Eliminar vino?</h3>
        <p class="text-sm text-gray-600">Esta acción lo marcará como inactivo. ¿Continuar?</p>
        <div class="flex gap-3 justify-end">
          <BaseButton variant="ghost" @click="confirmDeleteId = null">Cancelar</BaseButton>
          <BaseButton variant="danger" @click="deleteWine(confirmDeleteId!)">Eliminar</BaseButton>
        </div>
      </div>
    </div>
  </div>
</template>

