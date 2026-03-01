<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useEventsStore } from '@stores/events.store'
import BaseCard from '@components/common/BaseCard.vue'
import BaseSpinner from '@components/common/BaseSpinner.vue'
import BaseEmptyState from '@components/common/BaseEmptyState.vue'
import { formatDate, formatTime } from '@utils/date'

const { t } = useI18n()
const eventsStore = useEventsStore()
const locationError = ref('')


const eventDefaultImages = [
  '/images/winery/cheerswines.jpg',
  '/images/winery/4colorsofwinesincups.jpg',
  '/images/winery/winebarsiing.jpg',
  '/images/winery/servingwine.jpg',
]
function eventDefaultImage(index: number): string {
  return eventDefaultImages[index % eventDefaultImages.length]
}

onMounted(async () => {

  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        eventsStore.fetchNearbyEvents(position.coords.latitude, position.coords.longitude)
      },
      () => {
        locationError.value = t('events.locationError', 'No se pudo obtener la ubicación')
        eventsStore.fetchEvents()
      }
    )
  } else {
    eventsStore.fetchEvents()
  }
})
</script>

<template>
  <div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">{{ t('events.nearby') }}</h1>

    <p v-if="locationError" class="text-sm text-yellow-600">
      {{ locationError }}. {{ t('events.showingAll', 'Mostrando todos los eventos') }}.
    </p>

    <div v-if="eventsStore.loading" class="flex justify-center py-12">
      <BaseSpinner size="lg" />
    </div>

    <BaseEmptyState
      v-else-if="!eventsStore.hasEvents && !eventsStore.hasNearbyEvents"
      icon="calendar"
      :title="t('events.noEvents')"
      :description="t('events.noEventsDesc', 'Volvé más tarde para ver próximos eventos de vino')"
    />

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <router-link
        v-for="(event, idx) in eventsStore.nearbyEvents.length ? eventsStore.nearbyEvents : eventsStore.events"
        :key="event.id"
        :to="`/events/${event.id}`"
      >
        <BaseCard hoverable padding="none" class="overflow-hidden">
          <div class="aspect-video bg-gray-100 relative">
            <img
              :src="event.imageUrl || eventDefaultImage(idx)"
              :alt="event.title"
              class="w-full h-full object-cover"
              @error="($event.target as HTMLImageElement).src = eventDefaultImage(idx)"
            />
            <span class="absolute top-2 right-2 px-2 py-1 bg-wine-600 text-white text-xs rounded capitalize">
              {{ t(`events.types.${event.type?.toLowerCase()}`, event.type) }}
            </span>
          </div>
          <div class="p-4">
            <h3 class="font-semibold text-gray-900 truncate">{{ event.title }}</h3>
            <p class="text-sm text-gray-600 mt-1">
              {{ formatDate(event.startDate) }} · {{ formatTime(event.startDate) }}
            </p>
            <p class="text-sm text-gray-500 mt-1 truncate">📍 {{ event.location.city }}</p>
            <div class="flex items-center justify-between mt-3">
              <span class="text-wine-600 font-semibold">
                {{ event.price ? `$${event.price}` : t('events.free') }}
              </span>
              <span class="text-sm text-gray-500">
                {{ event.currentAttendees }}{{ event.maxAttendees ? `/${event.maxAttendees}` : '' }} {{ t('events.attendees') }}
              </span>
            </div>
          </div>
        </BaseCard>
      </router-link>
    </div>
  </div>
</template>

