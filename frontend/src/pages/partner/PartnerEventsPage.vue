<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@stores/auth.store'
import http from '@app/http'
import BaseButton from '@components/common/BaseButton.vue'
import BaseCard from '@components/common/BaseCard.vue'
import EventFormModal, { type EventFormData } from '@components/events/EventFormModal.vue'

interface Event {
  id: string
  title: string
  type: string
  status: string
  startDate: string
  endDate: string
  locationCity: string
  price: number
  currentAttendees: number
  maxAttendees: number | null
  description?: string
  locationName?: string
  locationAddress?: string
  locationRegion?: string
  latitude?: number
  longitude?: number
  imageUrl?: string
  contactEmail?: string
  contactPhone?: string
  websiteUrl?: string
}

const authStore = useAuthStore()
const router = useRouter()

const events = ref<Event[]>([])
const loading = ref(false)
const page = ref(0)
const totalItems = ref(0)
const pageSize = 20
const showModal = ref(false)
const editingEvent = ref<Partial<EventFormData> | null>(null)
const saving = ref(false)
const confirmDeleteId = ref<string | null>(null)

const totalPages = computed(() => Math.ceil(totalItems.value / pageSize))

async function fetchMyEvents() {
  loading.value = true
  try {
    const res = await http.get('/partner/events', {
      params: { page: page.value, size: pageSize },
    })
    const responseData = res.data.data ?? res.data
    events.value = responseData.items ?? responseData.content ?? []
    totalItems.value = responseData.totalItems ?? responseData.totalElements ?? 0
  } catch (error) {
    console.error('Error fetching events:', error)
  } finally {
    loading.value = false
  }
}

function openCreate() {
  editingEvent.value = {
    title: '',
    description: null,
    type: 'TASTING',
    status: 'DRAFT',
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

async function openEdit(event: Event) {
  try {
    const res = await http.get(`/events/${event.id}`)
    const e = res.data
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
  } catch (error) {
    console.error('Error loading event:', error)
    window.alert('Error al cargar el evento')
  }
}

async function handleSaveEvent(eventData: EventFormData) {
  saving.value = true
  try {
    const payload = {
      title: eventData.title,
      description: eventData.description || null,
      type: eventData.type || 'TASTING',
      status: eventData.status || 'DRAFT',
      startDate: eventData.startDate,
      endDate: eventData.endDate,
      locationName: eventData.locationName || null,
      locationAddress: eventData.locationAddress || null,
      locationCity: eventData.locationCity || null,
      locationRegion: eventData.locationRegion || null,
      latitude: eventData.latitude || null,
      longitude: eventData.longitude || null,
      price: eventData.price || 0,
      maxAttendees: eventData.maxAttendees || null,
      imageUrl: eventData.imageUrl || null,
      contactEmail: eventData.contactEmail || null,
      contactPhone: eventData.contactPhone || null,
      websiteUrl: eventData.websiteUrl || null,
    }

    if (eventData.id) {
      await http.put(`/partner/events/${eventData.id}`, payload)
    } else {
      await http.post('/partner/events', payload)
    }

    showModal.value = false
    editingEvent.value = null
    await fetchMyEvents()
  } catch (error) {
    console.error('Error saving event:', error)
    window.alert('Error al guardar el evento')
  } finally {
    saving.value = false
  }
}

async function deleteEvent(eventId: string) {
  try {
    await http.delete(`/partner/events/${eventId}`)
    confirmDeleteId.value = null
    await fetchMyEvents()
  } catch (error) {
    console.error('Error deleting event:', error)
    window.alert('Error al eliminar el evento')
  }
}

function handleCloseModal() {
  showModal.value = false
  editingEvent.value = null
}

function formatDate(dateString: string): string {
  const date = new Date(dateString)
  return date.toLocaleDateString('es-AR', { year: 'numeric', month: 'short', day: 'numeric' })
}

function getStatusColor(status: string): string {
  switch (status) {
    case 'PUBLISHED': return 'bg-green-100 text-green-700'
    case 'DRAFT': return 'bg-gray-100 text-gray-700'
    case 'CANCELLED': return 'bg-red-100 text-red-700'
    case 'COMPLETED': return 'bg-blue-100 text-blue-700'
    default: return 'bg-gray-100 text-gray-700'
  }
}

onMounted(() => {
  if (!authStore.isAuthenticated) {
    router.push('/login')
    return
  }
  fetchMyEvents()
})
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">
          Mis Eventos
        </h1>
        <p class="text-gray-600 mt-1">
          Gestiona los eventos que organizas
        </p>
      </div>
      <BaseButton @click="openCreate">
        + Crear Evento
      </BaseButton>
    </div>

    <BaseCard>
      <div
        v-if="loading"
        class="p-8 text-center text-gray-500"
      >
        Cargando...
      </div>
      <div
        v-else-if="events.length === 0"
        class="p-8 text-center"
      >
        <svg
          class="w-16 h-16 mx-auto text-gray-300 mb-4"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
          />
        </svg>
        <p class="text-gray-600 mb-4">
          Aún no has creado ningún evento
        </p>
        <BaseButton @click="openCreate">
          Crear mi primer evento
        </BaseButton>
      </div>
      <table
        v-else
        class="w-full text-sm"
      >
        <thead class="border-b border-gray-200 bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left font-medium text-gray-600">
              Título
            </th>
            <th class="px-4 py-3 text-left font-medium text-gray-600">
              Tipo
            </th>
            <th class="px-4 py-3 text-left font-medium text-gray-600">
              Fecha Inicio
            </th>
            <th class="px-4 py-3 text-left font-medium text-gray-600">
              Ciudad
            </th>
            <th class="px-4 py-3 text-left font-medium text-gray-600">
              Estado
            </th>
            <th class="px-4 py-3 text-left font-medium text-gray-600">
              Asistentes
            </th>
            <th class="px-4 py-3 text-right font-medium text-gray-600">
              Acciones
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="e in events"
            :key="e.id"
            class="border-b border-gray-100 hover:bg-gray-50"
          >
            <td class="px-4 py-3 font-medium text-gray-900">
              {{ e.title }}
            </td>
            <td class="px-4 py-3 text-gray-600 capitalize">
              {{ e.type }}
            </td>
            <td class="px-4 py-3 text-gray-600">
              {{ formatDate(e.startDate) }}
            </td>
            <td class="px-4 py-3 text-gray-600">
              {{ e.locationCity || '-' }}
            </td>
            <td class="px-4 py-3">
              <span
                :class="getStatusColor(e.status)"
                class="text-xs px-2 py-0.5 rounded-full font-medium"
              >
                {{ e.status }}
              </span>
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
        <div>
          Mostrando {{ events.length }} de {{ totalItems }}
        </div>
        <div class="flex gap-2">
          <button
            :disabled="page === 0"
            :class="page === 0 ? 'opacity-40 cursor-not-allowed' : 'hover:text-primary-600'"
            class="px-2"
            @click="page--; fetchMyEvents()"
          >
            Anterior
          </button>
          <span>Página {{ page + 1 }} de {{ totalPages }}</span>
          <button
            :disabled="page >= totalPages - 1"
            :class="page >= totalPages - 1 ? 'opacity-40 cursor-not-allowed' : 'hover:text-primary-600'"
            class="px-2"
            @click="page++; fetchMyEvents()"
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
      <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h3 class="font-bold text-gray-900 mb-2">
          ¿Eliminar evento?
        </h3>
        <p class="text-sm text-gray-600 mb-6">
          Esta acción no se puede deshacer. ¿Continuar?
        </p>
        <div class="flex justify-end gap-3">
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

