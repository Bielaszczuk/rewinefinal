<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import http from '@app/http'
import BaseCard from '@components/common/BaseCard.vue'
import BaseSpinner from '@components/common/BaseSpinner.vue'
import GoogleMapsEmbed from '@components/common/GoogleMapsEmbed.vue'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()

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
}

interface Wine {
  id: string
  name: string
  wineType: string
  vintage: number | null
  imageUrl: string | null
  rating: number | null
}

const wineryId = computed(() => route.params.id as string)
const winery = ref<Winery | null>(null)
const wines = ref<Wine[]>([])
const loading = ref(false)
const loadingWines = ref(false)

onMounted(() => {
  if (wineryId.value) {
    fetchWinery()
    fetchWineryWines()
  }
})

async function fetchWinery() {
  loading.value = true
  try {
    const response = await http.get(`/wineries/${wineryId.value}`)
    winery.value = response.data
  } catch (error) {
    console.error('Error fetching winery:', error)
  } finally {
    loading.value = false
  }
}

async function fetchWineryWines() {
  loadingWines.value = true
  try {
    const response = await http.get('/wines', {
      params: {
        wineryId: wineryId.value,
        page: 0,
        size: 20,
      },
    })
    wines.value = response.data.items ?? response.data.content ?? []
  } catch (error) {
    console.error('Error fetching winery wines:', error)
  } finally {
    loadingWines.value = false
  }
}

function goBack() {
  router.back()
}

function viewWine(wineId: string) {
  router.push(`/wines/${wineId}`)
}

const mapQuery = computed(() => {
  if (!winery.value) return null

  if (winery.value.latitude && winery.value.longitude) {
    return `${winery.value.latitude},${winery.value.longitude}`
  }

  if (winery.value.address) {
    return winery.value.address
  }

  return winery.value.name
})
</script>

<template>
  <div class="max-w-6xl mx-auto">
    <button
      type="button"
      class="flex items-center gap-2 text-gray-600 hover:text-gray-900 mb-6"
      @click="goBack"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
      </svg>
      {{ t('common.back') || 'Volver' }}
    </button>

    <div v-if="loading" class="flex justify-center py-12">
      <BaseSpinner size="lg" />
    </div>

    <div v-else-if="winery" class="space-y-6">
      <BaseCard class="overflow-hidden">
        <div class="flex flex-col md:flex-row gap-6">
          <div class="flex-shrink-0">
            <div class="w-32 h-32 bg-gray-100 rounded-lg overflow-hidden flex items-center justify-center">
              <img
                v-if="winery.logoUrl"
                :src="winery.logoUrl"
                :alt="winery.name"
                class="w-full h-full object-contain"
              />
              <svg v-else class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
            </div>
          </div>

          <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-4">
              <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ winery.name }}</h1>
                <div class="flex items-center gap-2 mt-2 text-gray-600">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  <span>
                    {{ winery.subregion ? `${winery.subregion}, ` : '' }}
                    {{ winery.region ? `${winery.region}, ` : '' }}
                    {{ winery.country }}
                  </span>
                </div>
                <div v-if="winery.established" class="mt-1 text-sm text-gray-500">
                  {{ t('wineries.established') || 'Fundada en' }} {{ winery.established }}
                </div>
              </div>

              <a
                v-if="winery.websiteUrl"
                :href="winery.websiteUrl"
                target="_blank"
                rel="noopener noreferrer"
                class="flex items-center gap-2 px-4 py-2 text-sm text-wine-600 hover:text-wine-700 border border-wine-600 rounded-lg hover:bg-wine-50 transition-colors"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                </svg>
                {{ t('wineries.visitWebsite') || 'Visitar sitio web' }}
              </a>
            </div>

            <p v-if="winery.description" class="mt-4 text-gray-700 leading-relaxed">
              {{ winery.description }}
            </p>
          </div>
        </div>
      </BaseCard>

      <div class="grid md:grid-cols-2 gap-6">
        <BaseCard>
          <h3 class="text-lg font-semibold text-gray-900 mb-4">
            📞 {{ t('wineries.contact') || 'Contacto' }}
          </h3>
          <div class="space-y-3">
            <div v-if="winery.contactEmail" class="flex items-start gap-3">
              <svg class="w-5 h-5 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
              <a :href="`mailto:${winery.contactEmail}`" class="text-wine-600 hover:text-wine-700">
                {{ winery.contactEmail }}
              </a>
            </div>

            <div v-if="winery.contactPhone" class="flex items-start gap-3">
              <svg class="w-5 h-5 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
              </svg>
              <a :href="`tel:${winery.contactPhone}`" class="text-wine-600 hover:text-wine-700">
                {{ winery.contactPhone }}
              </a>
            </div>

            <div v-if="winery.address" class="flex items-start gap-3">
              <svg class="w-5 h-5 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              <span class="text-gray-700">{{ winery.address }}</span>
            </div>

            <div v-if="!winery.contactEmail && !winery.contactPhone && !winery.address" class="text-gray-500 text-sm">
              {{ t('wineries.noContactInfo') || 'No hay información de contacto disponible' }}
            </div>
          </div>
        </BaseCard>

        <BaseCard v-if="mapQuery">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">
            🗺️ {{ t('wineries.location') || 'Ubicación' }}
          </h3>
          <GoogleMapsEmbed
            mode="place"
            :query="mapQuery"
            height="250px"
          />
        </BaseCard>
      </div>

      <BaseCard>
        <h3 class="text-xl font-semibold text-gray-900 mb-4">
          🍷 {{ t('wineries.wines') || 'Vinos de esta bodega' }}
        </h3>

        <div v-if="loadingWines" class="flex justify-center py-8">
          <BaseSpinner />
        </div>

        <div v-else-if="wines.length > 0" class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
          <div
            v-for="wine in wines"
            :key="wine.id"
            class="group cursor-pointer bg-gray-50 rounded-lg overflow-hidden hover:shadow-md transition-shadow"
            @click="viewWine(wine.id)"
          >
            <div class="aspect-square bg-white flex items-center justify-center p-4">
              <img
                v-if="wine.imageUrl"
                :src="wine.imageUrl"
                :alt="wine.name"
                class="max-h-full max-w-full object-contain"
              />
              <div v-else class="text-gray-300">
                <svg class="w-20 h-20" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6z" />
                </svg>
              </div>
            </div>
            <div class="p-3">
              <h4 class="font-medium text-gray-900 group-hover:text-wine-600 transition-colors">
                {{ wine.name }}
              </h4>
              <div class="flex items-center justify-between mt-1">
                <span class="text-sm text-gray-500">{{ wine.wineType }}</span>
                <span v-if="wine.vintage" class="text-sm text-gray-500">{{ wine.vintage }}</span>
              </div>
              <div v-if="wine.rating" class="flex items-center gap-1 mt-2">
                <span class="text-yellow-500">★</span>
                <span class="text-sm font-medium">{{ wine.rating.toFixed(1) }}</span>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-8 text-gray-500">
          {{ t('wineries.noWines') || 'Esta bodega aún no tiene vinos publicados' }}
        </div>
      </BaseCard>
    </div>

    <div v-else class="text-center py-12">
      <p class="text-gray-600">{{ t('wineries.notFound') || 'Bodega no encontrada' }}</p>
    </div>
  </div>
</template>
