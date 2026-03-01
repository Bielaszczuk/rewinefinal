<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useWinesStore } from '@stores/wines.store'
import { useDebounce } from '@composables/useDebounce'
import BaseInput from '@components/common/BaseInput.vue'
import BaseSelect from '@components/common/BaseSelect.vue'
import BaseCard from '@components/common/BaseCard.vue'
import BaseSpinner from '@components/common/BaseSpinner.vue'
import BaseEmptyState from '@components/common/BaseEmptyState.vue'
import { WINE_TYPES, WINE_REGIONS, GRAPE_VARIETIES } from '@config/constants'
import type { WineType } from '@domain/wine/wine.types'

const route = useRoute()
const { t } = useI18n()
const winesStore = useWinesStore()

const search = ref('')
const selectedType = ref<WineType | null>(null)
const selectedRegion = ref<string | null>(null)
const selectedGrape = ref<string | null>(null)

const debouncedSearch = useDebounce(search, 300)

const typeOptions = WINE_TYPES.map((item) => ({ value: item.value, label: item.label }))
const regionOptions = WINE_REGIONS.map((r) => ({ value: r.value, label: r.label }))
const grapeOptions = GRAPE_VARIETIES.map((g) => ({ value: g.value, label: g.label }))


function wineDefaultImage(type?: string): string {
  const map: Record<string, string> = {
    red: '/images/mock/winebottle.jpg',
    white: '/images/mock/winebottle2.jpg',
    rose: '/images/mock/winebottle.jpg',
    sparkling: '/images/mock/winebottle2.jpg',
  }
  return map[type?.toLowerCase() ?? ''] ?? '/images/mock/winebottle.jpg'
}

onMounted(() => {
  const queryType = route.query.type as string | undefined
  const queryGrape = route.query.grape as string | undefined
  const querySearch = route.query.search as string | undefined

  if (queryType) selectedType.value = queryType as WineType
  if (querySearch) search.value = querySearch

  if (queryGrape) {
    const match = GRAPE_VARIETIES.find(g => g.value === queryGrape || g.label.toLowerCase() === queryGrape.toLowerCase())
    if (match) selectedGrape.value = match.value
    else search.value = queryGrape
  }

  winesStore.setFilter({
    search: search.value || undefined,
    type: selectedType.value || undefined,
    region: selectedRegion.value || undefined,
    grapeVariety: selectedGrape.value || undefined,
  })
  winesStore.fetchWines()
})

watch([debouncedSearch, selectedType, selectedRegion, selectedGrape], () => {
  winesStore.setFilter({
    search: debouncedSearch.value || undefined,
    type: selectedType.value || undefined,
    region: selectedRegion.value || undefined,
    grapeVariety: selectedGrape.value || undefined,
  })
  winesStore.fetchWines()
})

function clearFilters() {
  search.value = ''
  selectedType.value = null
  selectedRegion.value = null
  selectedGrape.value = null
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <h1 class="text-2xl font-bold text-gray-900">{{ t('compare.browseWines') }}</h1>
    </div>

    <BaseCard padding="md">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
        <BaseInput
          v-model="search"
          type="search"
          :placeholder="t('wines.searchPlaceholder')"
          class="lg:col-span-2"
        />
        <BaseSelect
          v-model="selectedType"
          :options="typeOptions"
          :placeholder="t('wines.type')"
        />
        <BaseSelect
          v-model="selectedGrape"
          :options="grapeOptions"
          :placeholder="t('wines.grapeVariety')"
        />
        <BaseSelect
          v-model="selectedRegion"
          :options="regionOptions"
          :placeholder="t('wines.region')"
        />
      </div>
      <div class="mt-3 flex items-center justify-between">
        <span class="text-sm text-gray-500">
          {{ winesStore.totalWines }} {{ t('wines.title').toLowerCase() }}
        </span>
        <button
          v-if="search || selectedType || selectedGrape || selectedRegion"
          type="button"
          class="text-wine-600 hover:text-wine-700 text-sm font-medium"
          @click="clearFilters"
        >
          {{ t('common.clear') }}
        </button>
      </div>
    </BaseCard>

    <div v-if="winesStore.loading" class="flex justify-center py-12">
      <BaseSpinner size="lg" />
    </div>

    <BaseEmptyState
      v-else-if="!winesStore.hasWines"
      icon="wine"
      :title="t('wines.noWines')"
      :description="t('common.noResults')"
    />

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <router-link
        v-for="wine in winesStore.wines"
        :key="wine.id"
        :to="`/wines/${wine.id}`"
      >
        <BaseCard hoverable padding="none" class="overflow-hidden">
          <div class="aspect-[3/4] bg-gray-100 relative">
            <img
              :src="wine.imageUrl || wineDefaultImage(wine.type)"
              :alt="wine.name"
              class="w-full h-full object-cover"
              @error="($event.target as HTMLImageElement).src = '/images/mock/winebottle.jpg'"
            />
          </div>
          <div class="p-4">
            <h3 class="font-semibold text-gray-900 truncate">{{ wine.name }}</h3>
            <p class="text-sm text-gray-600 truncate">{{ wine.winery }}</p>
            <div class="flex items-center justify-between mt-2">
              <span class="text-sm text-wine-600 capitalize">{{ t(`wines.types.${wine.type?.toLowerCase()}`, wine.type) }}</span>
              <span v-if="wine.rating" class="text-sm text-gray-600">
                ⭐ {{ wine.rating.toFixed(1) }}
              </span>
            </div>
            <p v-if="wine.price" class="text-lg font-semibold text-gray-900 mt-2">
              ${{ wine.price }}
            </p>
          </div>
        </BaseCard>
      </router-link>
    </div>

    <div v-if="winesStore.pagination" class="flex justify-center gap-2">
      <span class="text-sm text-gray-600">
        {{ t('common.viewMore', 'Mostrando') }} {{ winesStore.wines.length }} / {{ winesStore.totalWines }}
      </span>
    </div>
  </div>
</template>

