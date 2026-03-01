<script setup lang="ts">
/**
 * WineComparePage - Compare wines side by side
 *
 * Features:
 * - Start with wine A from route param or selection
 * - Search modal to add wine B
 * - Show comparison table with AI recommendations
 * - Cache indicator for previously compared pairs
 */

import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useWinesStore } from '@stores/wines.store'
import BaseButton from '@components/common/BaseButton.vue'
import BaseCard from '@components/common/BaseCard.vue'
import BaseModal from '@components/common/BaseModal.vue'
import BaseInput from '@components/common/BaseInput.vue'
import BaseSpinner from '@components/common/BaseSpinner.vue'
import BaseEmptyState from '@components/common/BaseEmptyState.vue'
import type { Wine, WineSummary, WineComparison } from '@domain/wine/wine.types'
import { useDebounce } from '@composables/useDebounce'

const route = useRoute()
const router = useRouter()
const { t } = useI18n()
const winesStore = useWinesStore()

const wineA = ref<Wine | null>(null)
const wineB = ref<Wine | null>(null)
const comparison = ref<WineComparison | null>(null)
const loading = ref(false)
const searchModalOpen = ref(false)
const searchQuery = ref('')
const searchResults = ref<WineSummary[]>([])
const searching = ref(false)
const selectingFor = ref<'A' | 'B'>('B')

const debouncedSearch = useDebounce(searchQuery, 300)

onMounted(async () => {
  const wineAId = route.params.wineAId as string
  if (wineAId) {
    await loadWineA(wineAId)
  }

  const wineBId = route.query.compareWith as string
  if (wineBId) {
    await loadWineB(wineBId)
  }
})

watch(debouncedSearch, async (query) => {
  if (query.length >= 2) {
    searching.value = true
    try {
      searchResults.value = await winesStore.searchWines(query, 10)
    } finally {
      searching.value = false
    }
  } else {
    searchResults.value = []
  }
})

async function loadWineA(id: string) {
  loading.value = true
  try {
    await winesStore.fetchWine(id)
    wineA.value = winesStore.currentWine


    if (wineA.value && wineB.value) {
      await runComparison()
    }
  } finally {
    loading.value = false
  }
}

async function loadWineB(id: string) {
  loading.value = true
  try {
    await winesStore.fetchWine(id)
    wineB.value = winesStore.currentWine


    if (wineA.value && wineB.value) {
      await runComparison()
    }
  } finally {
    loading.value = false
  }
}


async function runComparison() {
  if (!wineA.value || !wineB.value) return

  loading.value = true
  try {
    comparison.value = await winesStore.compareWines([wineA.value.id, wineB.value.id])
  } catch (error) {
    console.error('Comparison failed:', error)
  } finally {
    loading.value = false
  }
}


function openSearchModal(forWine: 'A' | 'B') {
  selectingFor.value = forWine
  searchQuery.value = ''
  searchResults.value = []
  searchModalOpen.value = true
}


async function selectWine(wine: WineSummary) {
  searchModalOpen.value = false

  if (selectingFor.value === 'A') {
    await loadWineA(wine.id)

    router.replace({ params: { wineAId: wine.id }, query: route.query })
  } else {
    await loadWineB(wine.id)

    router.replace({
      params: route.params,
      query: { ...route.query, compareWith: wine.id }
    })
  }
}


function swapWines() {
  const temp = wineA.value
  wineA.value = wineB.value
  wineB.value = temp


  if (wineA.value && wineB.value) {
    router.replace({
      params: { wineAId: wineA.value.id },
      query: { compareWith: wineB.value.id }
    })
  }
}

function clearAll() {
  wineA.value = null
  wineB.value = null
  comparison.value = null
  router.replace({ params: {}, query: {} })
}


function goToWineDetails(wineId: string) {
  router.push(`/wines/${wineId}`)
}

const canCompare = computed(() => wineA.value && wineB.value)
const isCached = computed(() => comparison.value?.fromCache ?? false)


function getWinnerClass(index: number, winnerIndex: number | null): string {
  if (winnerIndex === null) return ''
  return index === winnerIndex ? 'bg-green-50 text-green-700 font-semibold' : ''
}
</script>

<template>
  <div class="max-w-5xl mx-auto space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ t('compare.title') }}</h1>
        <p class="text-gray-600 mt-1">{{ t('compare.subtitle') }}</p>
      </div>
      <div class="flex gap-2">
        <BaseButton v-if="canCompare" variant="ghost" size="sm" @click="swapWines">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
          </svg>
          {{ t('compare.swap') }}
        </BaseButton>
        <BaseButton v-if="wineA || wineB" variant="ghost" size="sm" @click="clearAll">
          {{ t('compare.clearAll') }}
        </BaseButton>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <BaseCard class="relative">
        <div class="absolute top-2 left-2 px-2 py-0.5 bg-wine-100 text-wine-700 text-xs font-medium rounded">
          {{ t('compare.wineA') }}
        </div>

        <div v-if="wineA" class="pt-6">
          <div class="flex gap-4">
            <div class="w-20 h-24 bg-gray-100 rounded flex-shrink-0 flex items-center justify-center">
              <img v-if="wineA.imageUrl" :src="wineA.imageUrl" :alt="wineA.name" class="w-full h-full object-cover rounded" />
              <svg v-else class="w-10 h-10 text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2L8 8h8l-4-6zm0 8c-2.21 0-4 1.79-4 4v6h8v-6c0-2.21-1.79-4-4-4z"/>
              </svg>
            </div>
            <div class="flex-1 min-w-0">
              <h3 class="font-semibold text-gray-900 truncate cursor-pointer hover:text-wine-600" @click="goToWineDetails(wineA.id)">
                {{ wineA.name }}
              </h3>
              <p class="text-sm text-gray-600">{{ wineA.winery }}</p>
              <p class="text-sm text-gray-500">{{ wineA.vintage || 'NV' }} · {{ wineA.region }}</p>
              <div v-if="wineA.rating" class="flex items-center gap-1 mt-1">
                <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                <span class="text-sm font-medium">{{ wineA.rating.toFixed(1) }}</span>
              </div>
            </div>
          </div>
          <BaseButton variant="outline" size="sm" class="mt-4 w-full" @click="openSearchModal('A')">
            {{ t('compare.changeWine') }}
          </BaseButton>
        </div>

        <div v-else class="pt-6 text-center py-8">
          <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
          </div>
          <p class="text-gray-600 mb-4">{{ t('compare.selectFirstWine') }}</p>
          <BaseButton @click="openSearchModal('A')">
            {{ t('compare.selectWine') }}
          </BaseButton>
        </div>
      </BaseCard>

      <BaseCard class="relative">
        <div class="absolute top-2 left-2 px-2 py-0.5 bg-gray-100 text-gray-700 text-xs font-medium rounded">
          {{ t('compare.wineB') }}
        </div>

        <div v-if="wineB" class="pt-6">
          <div class="flex gap-4">
            <div class="w-20 h-24 bg-gray-100 rounded flex-shrink-0 flex items-center justify-center">
              <img v-if="wineB.imageUrl" :src="wineB.imageUrl" :alt="wineB.name" class="w-full h-full object-cover rounded" />
              <svg v-else class="w-10 h-10 text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2L8 8h8l-4-6zm0 8c-2.21 0-4 1.79-4 4v6h8v-6c0-2.21-1.79-4-4-4z"/>
              </svg>
            </div>
            <div class="flex-1 min-w-0">
              <h3 class="font-semibold text-gray-900 truncate cursor-pointer hover:text-wine-600" @click="goToWineDetails(wineB.id)">
                {{ wineB.name }}
              </h3>
              <p class="text-sm text-gray-600">{{ wineB.winery }}</p>
              <p class="text-sm text-gray-500">{{ wineB.vintage || 'NV' }} · {{ wineB.region }}</p>
              <div v-if="wineB.rating" class="flex items-center gap-1 mt-1">
                <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                <span class="text-sm font-medium">{{ wineB.rating.toFixed(1) }}</span>
              </div>
            </div>
          </div>
          <BaseButton variant="outline" size="sm" class="mt-4 w-full" @click="openSearchModal('B')">
            {{ t('compare.changeWine') }}
          </BaseButton>
        </div>

        <div v-else class="pt-6 text-center py-8">
          <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
          </div>
          <p class="text-gray-600 mb-4">{{ t('compare.selectSecondWine') }}</p>
          <BaseButton :disabled="!wineA" @click="openSearchModal('B')">
            {{ t('compare.selectWine') }}
          </BaseButton>
        </div>
      </BaseCard>
    </div>

    <div v-if="loading" class="flex justify-center py-12">
      <BaseSpinner size="lg" />
    </div>

    <template v-else-if="comparison">
      <div v-if="isCached" class="flex items-center gap-2 p-3 bg-blue-50 border border-blue-200 rounded-lg">
        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span class="text-sm text-blue-700">{{ t('compare.cachedResult') }}</span>
      </div>

      <BaseCard padding="none">
        <div class="grid grid-cols-3 border-b bg-gradient-to-r from-wine-50 via-white to-gray-50">
          <div class="p-4 text-right">
            <p class="font-bold text-wine-700">{{ wineA?.name }}</p>
            <p class="text-xs text-gray-500">{{ wineA?.winery }}</p>
          </div>
          <div class="p-4 text-center border-x border-gray-200">
            <p class="text-sm font-semibold text-gray-600 uppercase tracking-wide">{{ t('compare.attributes') }}</p>
          </div>
          <div class="p-4 text-left">
            <p class="font-bold text-gray-700">{{ wineB?.name }}</p>
            <p class="text-xs text-gray-500">{{ wineB?.winery }}</p>
          </div>
        </div>

        <div class="divide-y divide-gray-100">
          <div
            v-for="(attr, idx) in comparison.attributeComparison?.attributes || []"
            :key="idx"
            class="grid grid-cols-3 hover:bg-gray-50 transition-colors"
          >
            <div class="p-3 flex items-center justify-end gap-2">
              <span class="text-sm font-medium text-gray-700 truncate">{{ attr.wineAValue }}</span>
              <div class="flex-1 h-8 flex items-center justify-end">
                <div
                  class="h-6 bg-gradient-to-l from-wine-500 to-wine-400 rounded-l-full transition-all duration-500 flex items-center justify-start pl-2"
                  :style="{ width: `${attr.wineAScore || 50}%` }"
                >
                  <span v-if="(attr.wineAScore || 0) > 30" class="text-xs font-bold text-white">{{ attr.wineAScore }}</span>
                </div>
              </div>
            </div>

            <div class="p-3 flex items-center justify-center border-x border-gray-200 bg-gray-50">
              <span class="text-sm font-semibold text-gray-900">{{ attr.name }}</span>
            </div>

            <div class="p-3 flex items-center gap-2">
              <div class="flex-1 h-8 flex items-center">
                <div
                  class="h-6 bg-gradient-to-r from-gray-500 to-gray-400 rounded-r-full transition-all duration-500 flex items-center justify-end pr-2"
                  :style="{ width: `${attr.wineBScore || 50}%` }"
                >
                  <span v-if="(attr.wineBScore || 0) > 30" class="text-xs font-bold text-white">{{ attr.wineBScore }}</span>
                </div>
              </div>
              <span class="text-sm font-medium text-gray-700 truncate">{{ attr.wineBValue }}</span>
            </div>
          </div>
        </div>
      </BaseCard>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <BaseCard v-if="comparison.similarities?.length">
          <div class="flex items-start gap-3">
            <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="flex-1">
              <h3 class="font-semibold text-gray-900 mb-2">{{ t('compare.similarities') }}</h3>
              <ul class="space-y-2">
                <li v-for="(sim, i) in comparison.similarities" :key="i" class="flex gap-2 text-sm text-gray-600">
                  <span class="text-blue-500 mt-1">•</span>
                  <span>{{ sim }}</span>
                </li>
              </ul>
            </div>
          </div>
        </BaseCard>

        <BaseCard v-if="comparison.keyDifferences?.length || comparison.differences?.length">
          <div class="flex items-start gap-3">
            <div class="flex-shrink-0 w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
              </svg>
            </div>
            <div class="flex-1">
              <h3 class="font-semibold text-gray-900 mb-2">{{ t('compare.keyDifferences') }}</h3>
              <ul class="space-y-2">
                <li v-for="(diff, i) in (comparison.keyDifferences || comparison.differences)" :key="i" class="flex gap-2 text-sm text-gray-600">
                  <span class="text-amber-500 mt-1">•</span>
                  <span>{{ diff }}</span>
                </li>
              </ul>
            </div>
          </div>
        </BaseCard>
      </div>

      <BaseCard v-if="comparison.bestFor" class="bg-gradient-to-br from-wine-50 to-gray-50">
        <h3 class="font-bold text-lg text-gray-900 mb-4 flex items-center gap-2">
          <svg class="w-5 h-5 text-wine-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
          </svg>
          {{ t('compare.bestFor') }}
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="bg-white p-4 rounded-lg border border-wine-200">
            <p class="font-semibold text-wine-700 mb-2">{{ wineA?.name }}</p>
            <ul class="space-y-1">
              <li v-for="(occasion, i) in comparison.bestFor.wineA" :key="i" class="text-sm text-gray-600 flex gap-2">
                <span class="text-wine-500">✓</span>
                <span>{{ occasion }}</span>
              </li>
            </ul>
          </div>
          <div class="bg-white p-4 rounded-lg border border-gray-200">
            <p class="font-semibold text-gray-700 mb-2">{{ wineB?.name }}</p>
            <ul class="space-y-1">
              <li v-for="(occasion, i) in comparison.bestFor.wineB" :key="i" class="text-sm text-gray-600 flex gap-2">
                <span class="text-gray-500">✓</span>
                <span>{{ occasion }}</span>
              </li>
            </ul>
          </div>
        </div>
      </BaseCard>

      <BaseCard v-if="comparison.recommendation || comparison.summary">
        <div class="flex items-start gap-3">
          <div class="flex-shrink-0 w-10 h-10 bg-wine-100 rounded-full flex items-center justify-center">
            <svg class="w-5 h-5 text-wine-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
            </svg>
          </div>
          <div class="flex-1">
            <h3 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
              {{ t('compare.aiRecommendation') }}
              <span class="text-xs px-2 py-0.5 bg-wine-100 text-wine-700 rounded-full font-medium">{{ t('aiProfile.poweredBy') }}</span>
            </h3>
            <div class="prose prose-sm max-w-none text-gray-700">
              <p v-if="comparison.summary" class="mb-3 font-medium text-gray-800">{{ comparison.summary }}</p>
              <div v-if="comparison.recommendation" class="whitespace-pre-line">{{ comparison.recommendation }}</div>
            </div>
          </div>
        </div>
      </BaseCard>
    </template>

    <BaseEmptyState
      v-else-if="!wineA && !wineB"
      icon="wine"
      :title="t('compare.emptyTitle')"
      :description="t('compare.emptyDescription')"
    >
      <template #action>
        <router-link to="/wines">
          <BaseButton>{{ t('compare.browseWines') }}</BaseButton>
        </router-link>
      </template>
    </BaseEmptyState>

    <BaseModal v-model="searchModalOpen" @close="searchModalOpen = false">
      <template #header>
        <h2 class="text-xl font-semibold text-gray-900">{{ t('compare.searchWine') }}</h2>
      </template>

      <div class="space-y-4">
        <BaseInput
          v-model="searchQuery"
          :placeholder="t('compare.searchPlaceholder')"
          autofocus
        >
          <template #prefix>
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </template>
        </BaseInput>

        <div v-if="searching" class="flex justify-center py-8">
          <BaseSpinner />
        </div>

        <div v-else-if="searchResults.length > 0" class="space-y-2 max-h-80 overflow-y-auto">
          <button
            v-for="wine in searchResults"
            :key="wine.id"
            type="button"
            class="w-full flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors text-left"
            :disabled="wine.id === wineA?.id || wine.id === wineB?.id"
            :class="{ 'opacity-50 cursor-not-allowed': wine.id === wineA?.id || wine.id === wineB?.id }"
            @click="selectWine(wine)"
          >
            <div class="w-12 h-14 bg-gray-100 rounded flex-shrink-0 flex items-center justify-center">
              <img v-if="wine.imageUrl" :src="wine.imageUrl" :alt="wine.name" class="w-full h-full object-cover rounded" />
              <svg v-else class="w-6 h-6 text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2L8 8h8l-4-6zm0 8c-2.21 0-4 1.79-4 4v6h8v-6c0-2.21-1.79-4-4-4z"/>
              </svg>
            </div>
            <div class="flex-1 min-w-0">
              <p class="font-medium text-gray-900 truncate">{{ wine.name }}</p>
              <p class="text-sm text-gray-500">{{ wine.winery }} · {{ wine.vintage || 'NV' }}</p>
            </div>
            <div v-if="wine.rating" class="flex items-center gap-1 text-sm">
              <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
              </svg>
              {{ wine.rating.toFixed(1) }}
            </div>
          </button>
        </div>

        <div v-else-if="searchQuery.length >= 2" class="text-center py-8 text-gray-500">
          {{ t('compare.noResults') }}
        </div>

        <div v-else class="text-center py-8 text-gray-500">
          {{ t('compare.typeToSearch') }}
        </div>
      </div>
    </BaseModal>
  </div>
</template>

