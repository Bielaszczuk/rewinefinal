<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '@stores/auth.store'
import http from '@app/http'
import BaseButton from '@components/common/BaseButton.vue'
import BaseCard from '@components/common/BaseCard.vue'
import EventFormModal, { type EventFormData } from '@components/events/EventFormModal.vue'

interface Event {
  id: string
  title: string
  description: string | null
  type: string
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
  currentAttendees: number
  status: string
  imageUrl: string | null
  organizerId: string
  organizerType: string
  contactEmail: string | null
  contactPhone: string | null
  websiteUrl: string | null
  createdAt: string
}

const events = ref<Event[]>([])
const loading = ref(false)
const search = ref('')
const page = ref(0)
const totalItems = ref(0)
const pageSize = 20
const editingEvent = ref<Partial<EventFormData> | null>(null)
const showModal = ref(false)
const saving = ref(false)
const confirmDeleteId = ref<string | null>(null)

const totalPages = computed(() => Math.ceil(totalItems.value / pageSize))

const authStore = useAuthStore()
const isAdmin = computed(() => authStore.isAdmin)

async function fetchEvents() {
  loading.value = true
  try {
    const params: Record<string, string | number> = { page: page.value, size: pageSize }
    if (search.value) params.search = search.value
    const res = await http.get('/admin/events', { params })
    const responseData = res.data.data ?? res.data
    events.value = responseData.items ?? responseData.content ?? []
    totalItems.value = responseData.totalItems ?? responseData.totalElements ?? 0
  } finally {
    loading.value = false
  }
}

async function handleSaveEvent(eventData: EventFormData) {
  saving.value = true
  try {
    const payload: Record<string, unknown> = {
      title: eventData.title,
      description: eventData.description || null,
      type: eventData.type || 'TASTING',
      startDate: eventData.startDate,
      endDate: eventData.endDate,
      status: eventData.status || 'DRAFT',
      locationName: eventData.locationName || null,
      locationAddress: eventData.locationAddress || null,
      locationCity: eventData.locationCity || null,
      locationRegion: eventData.locationRegion || null,
      latitude: eventData.latitude,
      longitude: eventData.longitude,
      price: eventData.price,
      maxAttendees: eventData.maxAttendees,
      imageUrl: eventData.imageUrl || null,
      contactEmail: eventData.contactEmail || null,
      contactPhone: eventData.contactPhone || null,
      websiteUrl: eventData.websiteUrl || null,
    }

    if (eventData.id) {
      await http.patch(`/admin/events/${eventData.id}`, payload)
    } else {
      await http.post('/admin/events', payload)
    }
    showModal.value = false
    editingEvent.value = null
    await fetchEvents()
  } catch (error: unknown) {
    console.error('Error saving event:', error)
    let message = 'Error desconocido'
    if (error && typeof error === 'object' && 'response' in error) {
      const axiosError = error as { response?: { data?: { message?: string; details?: Array<{field: string; message: string}>; errors?: Record<string, string[]> } } }
      if (axiosError.response?.data?.details && axiosError.response.data.details.length > 0) {
        message = axiosError.response.data.details.map(d => `${d.field}: ${d.message}`).join('\n')
      } else if (axiosError.response?.data?.errors) {
        const errors = Object.values(axiosError.response.data.errors).flat()
        message = errors.join(', ')
      } else if (axiosError.response?.data?.message) {
        message = axiosError.response.data.message
      }
    } else if (error instanceof Error) {
      message = error.message
    }
    window.alert('Error al guardar:\n' + message)
  } finally {
    saving.value = false
  }
}

async function deleteEvent(id: string) {
  await http.delete(`/admin/events/${id}`)
  confirmDeleteId.value = null
  await fetchEvents()
}

function openCreate() {
  editingEvent.value = {
    title: '',
    type: 'TASTING',
    status: 'DRAFT',
    description: null,
    startDate: new Date().toISOString().slice(0, 16),
    endDate: new Date().toISOString().slice(0, 16),
    locationName: null,
    locationAddress: null,
    locationCity: null,
    locationRegion: null,
    latitude: null,
    longitude: null,
    price: null,
    maxAttendees: null,
    imageUrl: null,
    contactEmail: null,
    contactPhone: null,
    websiteUrl: null,
  }
  showModal.value = true
}

function formatDateForInput(dateStr: string | null | undefined): string {
  if (!dateStr) return new Date().toISOString().slice(0, 16)
  try {
    const date = new Date(dateStr)
    if (isNaN(date.getTime())) return new Date().toISOString().slice(0, 16)
    return date.toISOString().slice(0, 16)
  } catch {
    return new Date().toISOString().slice(0, 16)
  }
}

function openEdit(e: Event) {
  editingEvent.value = {
    id: e.id,
    title: e.title,
    description: e.description,
    type: e.type,
    status: e.status,
    startDate: formatDateForInput(e.startDate),
    endDate: formatDateForInput(e.endDate),
    locationName: e.locationName,
    locationAddress: e.locationAddress,
    locationCity: e.locationCity,
    locationRegion: e.locationRegion,
    latitude: e.latitude,
    longitude: e.longitude,
    price: e.price,
    maxAttendees: e.maxAttendees,
    imageUrl: e.imageUrl,
    contactEmail: e.contactEmail,
    contactPhone: e.contactPhone,
    websiteUrl: e.websiteUrl,
  }
  showModal.value = true
}

function statusColor(s: string) {
  const colors: Record<string, string> = {
    PUBLISHED: 'bg-green-100 text-green-700',
    DRAFT: 'bg-gray-100 text-gray-600',
    CANCELLED: 'bg-red-100 text-red-700',
    COMPLETED: 'bg-blue-100 text-blue-700'
  }
  return colors[s] ?? 'bg-gray-100 text-gray-600'
}

function handleCloseModal() {
  showModal.value = false
  editingEvent.value = null
}

function handleSearch() {
  page.value = 0
  fetchEvents()
}

onMounted(fetchEvents)
</script>

<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold text-gray-900">
        Gestión de Eventos
      </h1>
      <BaseButton @click="openCreate">
        + Nuevo Evento
      </BaseButton>
    </div>

    <BaseCard>
      <div class="flex gap-3">
        <input
          v-model="search"
          type="text"
          placeholder="Buscar por título..."
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
        v-else-if="events.length === 0"
        class="p-8 text-center text-gray-500"
      >
        No se encontraron eventos.
      </div>
      <table
        v-else
        class="w-full text-sm"
      >
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="px-4 py-3 text-left font-medium text-gray-600">
              Título
            </th>
            <th class="px-4 py-3 text-left font-medium text-gray-600">
              Tipo
            </th>
            <th class="px-4 py-3 text-left font-medium text-gray-600">
              Estado
            </th>
            <th class="px-4 py-3 text-left font-medium text-gray-600">
              Fecha inicio
            </th>
            <th class="px-4 py-3 text-left font-medium text-gray-600">
              Ciudad
            </th>
            <th class="px-4 py-3 text-left font-medium text-gray-600">
              Asistentes
            </th>
            <th class="px-4 py-3 text-right font-medium text-gray-600">
              Acciones
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr
            v-for="e in events"
            :key="e.id"
            class="hover:bg-gray-50"
          >
            <td class="px-4 py-3 font-medium text-gray-900">
              {{ e.title }}
            </td>
            <td class="px-4 py-3 text-gray-600 text-xs">
              {{ e.type }}
            </td>
            <td class="px-4 py-3">
              <span
                :class="statusColor(e.status)"
                class="text-xs px-2 py-0.5 rounded-full font-medium"
              >
                {{ e.status }}
              </span>
            </td>
            <td class="px-4 py-3 text-gray-600 text-xs">
              {{ new Date(e.startDate).toLocaleDateString('es-AR') }}
            </td>
            <td class="px-4 py-3 text-gray-600 text-xs">
              {{ e.locationCity ?? '-' }}
            </td>
            <td class="px-4 py-3 text-gray-600 text-xs">
              {{ e.currentAttendees }}{{ e.maxAttendees ? ` / ${e.maxAttendees}` : '' }}
            </td>
            <td class="px-4 py-3 text-right space-x-2">
              <button
                class="text-primary-600 hover:text-primary-800 text-xs font-medium"
                @click="openEdit(e)"
              >
                Editar
              </button>
              <button
                v-if="isAdmin"
                class="text-red-500 hover:text-red-700 text-xs font-medium"
                @click="confirmDeleteId = e.id"
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
        <span>Total: {{ totalItems }} eventos</span>
        <div class="flex gap-2">
          <button
            :disabled="page === 0"
            :class="page === 0 ? 'opacity-40 cursor-not-allowed' : 'hover:text-primary-600'"
            class="px-2"
            @click="page--; fetchEvents()"
          >
            Anterior
          </button>
          <span>Página {{ page + 1 }} / {{ totalPages }}</span>
          <button
            :disabled="page >= totalPages - 1"
            :class="page >= totalPages - 1 ? 'opacity-40 cursor-not-allowed' : 'hover:text-primary-600'"
            class="px-2"
            @click="page++; fetchEvents()"
          >
            Siguiente
          </button>
        </div>
      </div>
    </BaseCard>

    <EventFormModal
      :show="showModal"
      :event="editingEvent"
      :saving="saving"
      @close="handleCloseModal"
      @save="handleSaveEvent"
    />

    <div
      v-if="confirmDeleteId"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
    >
      <div class="bg-white rounded-xl shadow-xl p-6 max-w-sm w-full space-y-4">
        <h3 class="font-bold text-gray-900">
          ¿Eliminar evento?
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
            @click="deleteEvent(confirmDeleteId!)"
          >
            Eliminar
          </BaseButton>
        </div>
      </div>
    </div>
  </div>
</template>

