<script setup lang="ts">
/**
 * WineDetailsPage - Display wine details with AI profile integration
 */

import { onMounted, computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useWinesStore } from '@stores/wines.store'
import { useCellarStore } from '@stores/cellar.store'
import { useAuthStore } from '@stores/auth.store'
import BaseButton from '@components/common/BaseButton.vue'
import BaseCard from '@components/common/BaseCard.vue'
import BaseSpinner from '@components/common/BaseSpinner.vue'
import ReviewsList from '@components/wines/ReviewsList.vue'

const route = useRoute()
const router = useRouter()
const { t } = useI18n()
const winesStore = useWinesStore()
const cellarStore = useCellarStore()
const authStore = useAuthStore()

const wineId = computed(() => route.params.id as string)
const wine = computed(() => winesStore.currentWine)
const hasAiProfile = computed(() => winesStore.hasAiProfile(wineId.value))
const isAuthenticated = computed(() => authStore.isAuthenticated)


const showCellarModal = ref(false)
const addingToCellar = ref(false)
const cellarForm = ref({ quantity: 1, purchasePrice: null as number | null, location: '', notes: '' })

onMounted(() => {
  if (wineId.value) {
    winesStore.fetchWine(wineId.value)
  }
})

function goBack() {
  router.back()
}

function addToComparison() {
  if (wine.value) {
    winesStore.addToComparison(wine.value)
  }
}

function goToCompare() {
  if (wine.value) {
    router.push(`/wines/compare/${wine.value.id}`)
  }
}

function goToAiProfile() {
  if (wine.value) {
    router.push(`/wines/${wine.value.id}/ai-profile`)
  }
}

async function confirmAddToCellar() {
  if (!wine.value) return
  addingToCellar.value = true
  try {
    await cellarStore.addWine(wine.value, cellarForm.value.quantity, {
      purchasePrice: cellarForm.value.purchasePrice,
      location: cellarForm.value.location || null,
      notes: cellarForm.value.notes || null,
    })
    showCellarModal.value = false
    cellarForm.value = { quantity: 1, purchasePrice: null, location: '', notes: '' }
  } finally {
    addingToCellar.value = false
  }
}
</script>

<template>
  <div>
    <button
      type="button"
      class="flex items-center gap-2 text-gray-600 hover:text-gray-900 mb-6"
      @click="goBack"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
      </svg>
      {{ t('common.back') }}
    </button>

    <div v-if="winesStore.loading" class="flex justify-center py-12">
      <BaseSpinner size="lg" />
    </div>

    <div v-else-if="wine" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <div class="lg:col-span-1">
        <BaseCard padding="none" class="overflow-hidden">
          <div class="aspect-[3/4] bg-gray-100">
            <img
              v-if="wine.imageUrl"
              :src="wine.imageUrl"
              :alt="wine.name"
              class="w-full h-full object-cover"
            />
            <div v-else class="w-full h-full flex items-center justify-center bg-gray-50">
              <img src="/images/icons/reshot-icon-wine-bottle-T9X8JUFM32.svg" alt="Wine" class="w-24 h-24 opacity-40" />
            </div>
          </div>
        </BaseCard>
      </div>

      <div class="lg:col-span-2 space-y-6">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ wine.name }}</h1>
          <router-link
            v-if="wine.wineryId"
            :to="`/wineries/${wine.wineryId}`"
            class="text-xl text-wine-600 hover:text-wine-700 hover:underline cursor-pointer transition-colors"
          >
            {{ wine.winery }}
          </router-link>
          <p v-else class="text-xl text-gray-600">
            {{ wine.winery }}
          </p>
        </div>

        <div class="flex flex-wrap gap-4">
          <span class="px-3 py-1 bg-wine-100 text-wine-700 rounded-full capitalize">
            {{ wine.type }}
          </span>
          <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full">
            {{ wine.region }}, {{ wine.country }}
          </span>
          <span v-if="wine.vintage" class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full">
            {{ wine.vintage }}
          </span>
        </div>

        <div class="flex items-center gap-6">
          <div v-if="wine.rating" class="text-center">
            <div class="text-3xl font-bold text-wine-600">{{ wine.rating.toFixed(1) }}</div>
            <div class="text-sm text-gray-500">{{ wine.reviewCount }} {{ t('wines.reviews') }}</div>
          </div>
          <div v-if="wine.price" class="text-center">
            <div class="text-3xl font-bold text-gray-900">${{ wine.price }}</div>
            <div class="text-sm text-gray-500">{{ t('wines.price') }}</div>
          </div>
          <div v-if="wine.alcoholContent" class="text-center">
            <div class="text-3xl font-bold text-gray-900">{{ wine.alcoholContent }}%</div>
            <div class="text-sm text-gray-500">{{ t('wineDetails.alcohol') }}</div>
          </div>
        </div>

        <div class="flex flex-wrap gap-3">
          <BaseButton variant="outline" @click="addToComparison">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            {{ t('wines.addToCompare') }}
          </BaseButton>
          <BaseButton variant="outline" @click="goToCompare">
            {{ t('wines.compare') }}
          </BaseButton>
          <BaseButton v-if="isAuthenticated" @click="showCellarModal = true">
            {{ t('wineDetails.addToCellar') }}
          </BaseButton>
        </div>

        <BaseCard class="bg-gradient-to-r from-wine-50 to-wine-100 border-wine-200">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-wine-600 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
              </div>
              <div>
                <h3 class="font-semibold text-gray-900">{{ t('wineDetails.aiSommelier') }}</h3>
                <p class="text-sm text-gray-600">
                  {{ hasAiProfile ? t('wineDetails.profileGenerated') : t('wineDetails.consultAi') }}
                </p>
              </div>
            </div>
            <BaseButton
              :variant="hasAiProfile ? 'outline' : 'primary'"
              @click="goToAiProfile"
            >
              {{ hasAiProfile ? t('wineDetails.viewProfile') : t('wineDetails.generateProfile') }}
            </BaseButton>
          </div>
        </BaseCard>

        <BaseCard v-if="wine.description">
          <h3 class="font-semibold text-gray-900 mb-2">{{ t('wines.description') }}</h3>
          <p class="text-gray-600">{{ wine.description }}</p>
        </BaseCard>

        <BaseCard v-if="wine.grapeVarieties?.length">
          <h3 class="font-semibold text-gray-900 mb-2">{{ t('wines.grapeVariety') }}</h3>
          <div class="flex flex-wrap gap-2">
            <span
              v-for="grape in wine.grapeVarieties"
              :key="grape"
              class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-sm"
            >
              {{ grape }}
            </span>
          </div>
        </BaseCard>

        <BaseCard v-if="wine.foodPairings?.length">
          <h3 class="font-semibold text-gray-900 mb-2">{{ t('wines.foodPairings') }}</h3>
          <div class="flex flex-wrap gap-2">
            <span
              v-for="pairing in wine.foodPairings"
              :key="pairing"
              class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-sm"
            >
              {{ pairing }}
            </span>
          </div>
        </BaseCard>
      </div>
    </div>

    <div
      v-if="wine"
      class="mt-12"
    >
      <ReviewsList :wine-id="wineId" />
    </div>

    <div
      v-else
      class="text-center py-12"
    >
      <p class="text-gray-600">
        {{ t('wineDetails.notFound') }}
      </p>
    </div>
  </div>

  <div v-if="showCellarModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md space-y-4">
      <h2 class="text-lg font-bold text-gray-900">{{ t('wineDetails.addToCellar') }}: {{ wine?.name }}</h2>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('cellar.quantity') }}</label>
        <input v-model.number="cellarForm.quantity" type="number" min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('cellar.purchasePrice') }}</label>
        <input v-model.number="cellarForm.purchasePrice" type="number" step="0.01" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('cellar.location') }}</label>
        <input v-model="cellarForm.location" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('cellar.notes') }}</label>
        <textarea v-model="cellarForm.notes" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
      </div>
      <div class="flex justify-end gap-3 pt-2">
        <BaseButton variant="ghost" @click="showCellarModal = false">{{ t('common.cancel') }}</BaseButton>
        <BaseButton :loading="addingToCellar" @click="confirmAddToCellar">{{ t('common.save') }}</BaseButton>
      </div>
    </div>
  </div>
</template>

