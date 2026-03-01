<script setup lang="ts">
/**
 * AiProfilePage - Display AI-generated wine profile
 *
 * Features:
 * - Show AI-generated wine analysis
 * - Display generation timestamp
 * - Food pairing recommendations with match scores
 * - Similar wines suggestions
 */

import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useWinesStore } from '@stores/wines.store'
import BaseButton from '@components/common/BaseButton.vue'
import BaseCard from '@components/common/BaseCard.vue'
import BaseSpinner from '@components/common/BaseSpinner.vue'
import type { Wine, AiWineProfile } from '@domain/wine/wine.types'
import { formatDate } from '@utils/date'

const route = useRoute()
const router = useRouter()
const { t } = useI18n()
const winesStore = useWinesStore()

const wine = ref<Wine | null>(null)
const profile = ref<(AiWineProfile & { fromCache: boolean }) | null>(null)
const loading = ref(false)
const error = ref<string | null>(null)

const wineId = computed(() => route.params.id as string)
const isCached = computed(() => profile.value?.fromCache ?? false)

onMounted(async () => {
  if (!wineId.value) {
    router.replace('/wines')
    return
  }

  loading.value = true
  error.value = null

  try {
    await winesStore.fetchWine(wineId.value)
    wine.value = winesStore.currentWine

    profile.value = await winesStore.getAiProfile(wineId.value)
  } catch (err) {
    console.error('Failed to load AI profile:', err)
    error.value = err instanceof Error ? err.message : 'Failed to load profile'
  } finally {
    loading.value = false
  }
})

function goBack() {
  router.back()
}

function goToWineDetails() {
  if (wine.value) {
    router.push(`/wines/${wine.value.id}`)
  }
}
</script>

<template>
  <div class="max-w-4xl mx-auto space-y-6">
    <button
      type="button"
      class="flex items-center gap-2 text-gray-600 hover:text-gray-900"
      @click="goBack"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
      </svg>
      {{ t('common.back') }}
    </button>

    <div v-if="loading" class="flex flex-col items-center justify-center py-16">
      <BaseSpinner size="lg" />
      <p class="mt-4 text-gray-600 text-center">{{ t('aiProfile.loading') }}</p>
      <p class="mt-2 text-sm text-gray-500 text-center">{{ t('aiProfile.generatingMessage') }}</p>
    </div>

    <BaseCard v-else-if="error" class="text-center py-12">
      <div class="w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full flex items-center justify-center">
        <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
      </div>
      <h2 class="text-xl font-semibold text-gray-900 mb-2">{{ t('aiProfile.errorTitle') }}</h2>
      <p class="text-gray-600 mb-4">{{ error }}</p>
      <BaseButton @click="goBack">{{ t('common.goBack') }}</BaseButton>
    </BaseCard>

    <template v-else-if="profile && wine">
      <div class="flex items-start justify-between">
        <div>
          <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 bg-gradient-to-br from-wine-500 to-wine-700 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
              </svg>
            </div>
            <div>
              <h1 class="text-2xl font-bold text-gray-900">{{ t('aiProfile.title') }}</h1>
              <p class="text-gray-500 text-sm">{{ t('aiProfile.poweredBy') }}</p>
            </div>
          </div>
        </div>

        <div class="text-right">
          <div v-if="isCached" class="inline-flex items-center gap-1 px-2 py-1 bg-blue-50 text-blue-700 text-xs font-medium rounded-full mb-1">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            {{ t('aiProfile.cached') }}
          </div>
          <p class="text-xs text-gray-500">
            {{ t('aiProfile.generatedAt') }}: {{ formatDate(profile.generatedAt) }}
          </p>
        </div>
      </div>

      <BaseCard class="bg-gray-50">
        <div class="flex items-center gap-4 cursor-pointer" @click="goToWineDetails">
          <div class="w-16 h-20 bg-white rounded flex-shrink-0 flex items-center justify-center shadow-sm">
            <img v-if="wine.imageUrl" :src="wine.imageUrl" :alt="wine.name" class="w-full h-full object-cover rounded" />
            <svg v-else class="w-8 h-8 text-gray-300" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2L8 8h8l-4-6zm0 8c-2.21 0-4 1.79-4 4v6h8v-6c0-2.21-1.79-4-4-4z"/>
            </svg>
          </div>
          <div>
            <h2 class="text-lg font-semibold text-gray-900 hover:text-wine-600">{{ wine.name }}</h2>
            <p class="text-gray-600">{{ wine.winery }}</p>
            <p class="text-sm text-gray-500">{{ wine.vintage || 'NV' }} · {{ wine.region }}, {{ wine.country }}</p>
          </div>
        </div>
      </BaseCard>

      <BaseCard>
        <h3 class="font-semibold text-gray-900 mb-3">{{ t('aiProfile.summary') }}</h3>
        <p class="text-gray-600 leading-relaxed">{{ profile.summary }}</p>
      </BaseCard>

      <BaseCard v-if="profile.visualAnalysis">
        <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
          <svg class="w-5 h-5 text-wine-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
          </svg>
          Análisis Visual
        </h3>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <span class="text-sm font-medium text-gray-500">Color:</span>
            <p class="text-gray-900">{{ profile.visualAnalysis.color }}</p>
          </div>
          <div>
            <span class="text-sm font-medium text-gray-500">Claridad:</span>
            <p class="text-gray-900">{{ profile.visualAnalysis.clarity }}</p>
          </div>
          <div>
            <span class="text-sm font-medium text-gray-500">Intensidad:</span>
            <p class="text-gray-900">{{ profile.visualAnalysis.intensity }}</p>
          </div>
          <div class="col-span-2">
            <span class="text-sm font-medium text-gray-500">Viscosidad:</span>
            <p class="text-gray-900">{{ profile.visualAnalysis.viscosity }}</p>
          </div>
        </div>
      </BaseCard>

      <BaseCard v-if="profile.aromaticProfile">
        <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
          <svg class="w-5 h-5 text-wine-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
          </svg>
          Perfil Aromático (Nariz)
        </h3>
        <div class="space-y-3">
          <div>
            <span class="text-sm font-medium text-gray-500">Intensidad:</span>
            <p class="text-gray-900">{{ profile.aromaticProfile.intensity }}</p>
          </div>
          <div>
            <span class="text-sm font-medium text-gray-500">Aromas Primarios:</span>
            <div class="flex flex-wrap gap-2 mt-1">
              <span
                v-for="aroma in profile.aromaticProfile.primaryAromas"
                :key="aroma"
                class="px-2 py-1 bg-purple-50 text-purple-700 rounded text-sm"
              >
                {{ aroma }}
              </span>
            </div>
          </div>
          <div v-if="profile.aromaticProfile.secondaryAromas?.length">
            <span class="text-sm font-medium text-gray-500">Aromas Secundarios:</span>
            <div class="flex flex-wrap gap-2 mt-1">
              <span
                v-for="aroma in profile.aromaticProfile.secondaryAromas"
                :key="aroma"
                class="px-2 py-1 bg-amber-50 text-amber-700 rounded text-sm"
              >
                {{ aroma }}
              </span>
            </div>
          </div>
          <div>
            <span class="text-sm font-medium text-gray-500">Bouquet:</span>
            <p class="text-gray-700 mt-1">{{ profile.aromaticProfile.bouquet }}</p>
          </div>
        </div>
      </BaseCard>

      <BaseCard v-if="profile.palateAnalysis">
        <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
          <svg class="w-5 h-5 text-wine-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
          </svg>
          Análisis en Boca (Paladar)
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <span class="text-sm font-medium text-gray-500">Dulzor:</span>
            <p class="text-gray-900">{{ profile.palateAnalysis.sweetness }}</p>
          </div>
          <div>
            <span class="text-sm font-medium text-gray-500">Acidez:</span>
            <p class="text-gray-900">{{ profile.palateAnalysis.acidity }}</p>
          </div>
          <div>
            <span class="text-sm font-medium text-gray-500">Taninos:</span>
            <p class="text-gray-900">{{ profile.palateAnalysis.tannin }}</p>
          </div>
          <div>
            <span class="text-sm font-medium text-gray-500">Alcohol:</span>
            <p class="text-gray-900">{{ profile.palateAnalysis.alcohol }}</p>
          </div>
          <div>
            <span class="text-sm font-medium text-gray-500">Cuerpo:</span>
            <p class="text-gray-900">{{ profile.palateAnalysis.body }}</p>
          </div>
          <div>
            <span class="text-sm font-medium text-gray-500">Textura:</span>
            <p class="text-gray-900">{{ profile.palateAnalysis.texture }}</p>
          </div>
          <div class="md:col-span-2">
            <span class="text-sm font-medium text-gray-500">Balance:</span>
            <p class="text-gray-900">{{ profile.palateAnalysis.balance }}</p>
          </div>
        </div>
      </BaseCard>

      <BaseCard v-if="profile.flavorProfile">
        <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
          <svg class="w-5 h-5 text-wine-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
          </svg>
          Perfil de Sabores
        </h3>
        <div class="space-y-3">
          <div>
            <span class="text-sm font-medium text-gray-500">Sabores Primarios:</span>
            <div class="flex flex-wrap gap-2 mt-1">
              <span
                v-for="flavor in profile.flavorProfile.primaryFlavors"
                :key="flavor"
                class="px-2 py-1 bg-rose-50 text-rose-700 rounded text-sm"
              >
                {{ flavor }}
              </span>
            </div>
          </div>
          <div v-if="profile.flavorProfile.secondaryFlavors?.length">
            <span class="text-sm font-medium text-gray-500">Sabores Secundarios:</span>
            <div class="flex flex-wrap gap-2 mt-1">
              <span
                v-for="flavor in profile.flavorProfile.secondaryFlavors"
                :key="flavor"
                class="px-2 py-1 bg-orange-50 text-orange-700 rounded text-sm"
              >
                {{ flavor }}
              </span>
            </div>
          </div>
          <div>
            <span class="text-sm font-medium text-gray-500">Intensidad:</span>
            <p class="text-gray-900">{{ profile.flavorProfile.intensity }}</p>
          </div>
          <div>
            <span class="text-sm font-medium text-gray-500">Final:</span>
            <p class="text-gray-900">{{ profile.flavorProfile.finish }}</p>
          </div>
        </div>
      </BaseCard>

      <BaseCard v-if="profile.foodPairings">
        <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
          <svg class="w-5 h-5 text-wine-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
          Maridajes Recomendados
        </h3>
        <div class="space-y-3">
          <div class="flex flex-wrap gap-2">
            <span
              v-for="pairing in profile.foodPairings.pairings"
              :key="pairing"
              class="px-3 py-1.5 bg-green-50 text-green-700 rounded-lg text-sm font-medium"
            >
              {{ pairing }}
            </span>
          </div>
          <p v-if="profile.foodPairings.explanation" class="text-gray-600 text-sm">
            {{ profile.foodPairings.explanation }}
          </p>
        </div>
      </BaseCard>

      <BaseCard v-if="profile.servingRecommendations">
        <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
          <svg class="w-5 h-5 text-wine-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Recomendaciones de Servicio
        </h3>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <span class="text-sm font-medium text-gray-500">Temperatura:</span>
            <p class="text-gray-900">{{ profile.servingRecommendations.temperature }}</p>
          </div>
          <div>
            <span class="text-sm font-medium text-gray-500">Copa:</span>
            <p class="text-gray-900">{{ profile.servingRecommendations.glassware }}</p>
          </div>
          <div>
            <span class="text-sm font-medium text-gray-500">Decantación:</span>
            <p class="text-gray-900">{{ profile.servingRecommendations.decanting }}</p>
          </div>
          <div>
            <span class="text-sm font-medium text-gray-500">Guarda:</span>
            <p class="text-gray-900">{{ profile.servingRecommendations.agingPotential }}</p>
          </div>
        </div>
      </BaseCard>

      <div class="flex gap-4 justify-center pt-4">
        <BaseButton variant="outline" @click="goToWineDetails">
          {{ t('aiProfile.viewWineDetails') }}
        </BaseButton>
        <router-link :to="`/wines/compare/${wine.id}`">
          <BaseButton variant="outline">
            {{ t('aiProfile.compareThisWine') }}
          </BaseButton>
        </router-link>
      </div>
    </template>
  </div>
</template>

