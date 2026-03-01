<script setup lang="ts">
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@stores/auth.store'
import BaseButton from '@components/common/BaseButton.vue'

const { t } = useI18n()
const authStore = useAuthStore()
const isAuthenticated = computed(() => authStore.isAuthenticated)

const features = computed(() => [
  {
    icon: '/images/icons/reshot-icon-vine-tasting-P5GDV7FUBS.svg',
    title: t('home.featureDiscover'),
    description: t('home.featureDiscoverDesc'),
  },
  {
    icon: '/images/icons/reshot-icon-wine-bottle-and-cup-375YBXTJSG.svg',
    title: t('home.featureScan'),
    description: t('home.featureScanDesc'),
  },
  {
    icon: '/images/icons/reshot-icon-toast-65A7YV3EXC.svg',
    title: t('nav.events'),
    description: t('home.featureEventsDesc'),
  },
  {
    icon: '/images/icons/reshot-icon-vineyard-H8S2KEC3PT.svg',
    title: t('nav.routes'),
    description: t('home.featureRoutesDesc'),
  },
])

const wineTypes = computed(() => [
  { type: t('wines.types.red'), icon: '/images/icons/reshot-icon-red-wine-L2HFAY75WG.svg', path: '/wines?type=red' },
  { type: t('wines.types.white'), icon: '/images/icons/reshot-icon-white-wine-WMRH4UFN83.svg', path: '/wines?type=white' },
  { type: t('wines.types.rose'), icon: '/images/icons/reshot-icon-rose-5UAFMK7JNE.svg', path: '/wines?type=rose' },
  { type: t('wines.types.sparkling'), icon: '/images/icons/reshot-icon-sparkly-wine-4YT69UR85S.svg', path: '/wines?type=sparkling' },
])

const popularGrapes = [
  'Malbec', 'Cabernet Sauvignon', 'Merlot', 'Pinot Noir',
  'Chardonnay', 'Sauvignon Blanc', 'Torrontés', 'Syrah'
]
</script>

<template>
  <div class="space-y-16">
    <section class="relative overflow-hidden -mt-6">
      <div class="absolute inset-0">
        <img
          src="/images/winery/wineyard.jpg"
          alt="Vineyard"
          class="w-full h-full object-cover"
        />
        <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/50 to-black/40"></div>
      </div>

      <div class="relative z-10 px-4 sm:px-6 lg:px-8 py-24 md:py-32 lg:py-40">
        <div class="max-w-4xl mx-auto text-center">
          <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-6 leading-tight drop-shadow-lg">
            {{ t('home.heroTitle') }}<br class="hidden md:block" />
            <span class="text-wine-300">{{ t('home.heroSubtitle') }}</span>
          </h1>
          <p class="text-lg md:text-xl text-gray-200 mb-10 max-w-2xl mx-auto drop-shadow">
            {{ t('home.heroDescription') }}
          </p>
          <div class="flex flex-wrap justify-center gap-4">
            <router-link to="/wines">
              <BaseButton size="lg" class="px-8">
                <img src="/images/icons/reshot-icon-red-wine-L2HFAY75WG.svg" alt="" class="w-5 h-5 mr-2 inline-block" />
                {{ t('home.exploreWines') }}
              </BaseButton>
            </router-link>
            <router-link to="/wines/scan">
              <BaseButton variant="outline" size="lg" class="px-8 !border-wine-300 !text-white hover:!bg-wine-300/20 hover:!border-wine-200">
                <svg class="w-5 h-5 mr-2 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                {{ t('home.scanWine') }}
              </BaseButton>
            </router-link>
            <router-link v-if="!isAuthenticated" to="/register">
              <BaseButton variant="outline" size="lg" class="px-8 !border-wine-300 !text-white hover:!bg-wine-300/20 hover:!border-wine-200">
                {{ t('home.getStartedFree') }}
              </BaseButton>
            </router-link>
            <router-link v-else to="/cellar">
              <BaseButton variant="outline" size="lg" class="px-8 !border-wine-300 !text-white hover:!bg-wine-300/20 hover:!border-wine-200">
                <img src="/images/icons/reshot-icon-vine-cellar-PK3MZL62NG.svg" alt="" class="w-5 h-5 mr-2 invert inline-block" />
                {{ t('nav.cellar') }}
              </BaseButton>
            </router-link>
          </div>
        </div>
      </div>
    </section>

    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <h2 class="text-2xl md:text-3xl font-bold text-gray-900 text-center mb-10">
        {{ t('home.exploreByType') }}
      </h2>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        <router-link
          v-for="wine in wineTypes"
          :key="wine.type"
          :to="wine.path"
          class="group flex flex-col items-center p-6 bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg hover:border-wine-200 transition-all duration-300"
        >
          <div class="w-24 h-24 mb-4 bg-stone-50 rounded-full flex items-center justify-center group-hover:bg-wine-50 transition-colors">
            <img :src="wine.icon" :alt="wine.type" class="w-16 h-16" />
          </div>
          <span class="font-semibold text-gray-900 group-hover:text-wine-700 transition-colors">{{ wine.type }}</span>
        </router-link>
      </div>
    </section>

    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <h2 class="text-2xl md:text-3xl font-bold text-gray-900 text-center mb-8">
        {{ t('home.popularGrapes') }}
      </h2>
      <div class="flex flex-wrap justify-center gap-3">
        <router-link
          v-for="grape in popularGrapes"
          :key="grape"
          :to="`/wines?grape=${grape.toLowerCase()}`"
          class="px-5 py-2.5 bg-white border border-gray-200 rounded-full text-sm font-medium text-gray-700 hover:border-wine-300 hover:text-wine-700 hover:bg-wine-50 transition-all duration-200"
        >
          {{ grape }}
        </router-link>
      </div>
    </section>

    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <h2 class="text-2xl md:text-3xl font-bold text-gray-900 text-center mb-4">
        {{ t('home.featuresTitle') }}
      </h2>
      <p class="text-gray-600 text-center mb-10 max-w-2xl mx-auto">
        {{ t('home.featuresSubtitle') }}
      </p>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div
          v-for="feature in features"
          :key="feature.title"
          class="group p-6 bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-wine-100 transition-all duration-300"
        >
          <div class="w-14 h-14 mb-4 bg-wine-50 rounded-xl flex items-center justify-center group-hover:bg-wine-100 transition-colors">
            <img :src="feature.icon" :alt="feature.title" class="w-8 h-8" />
          </div>
          <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ feature.title }}</h3>
          <p class="text-gray-600 text-sm">{{ feature.description }}</p>
        </div>
      </div>
    </section>

    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
      <div class="bg-gradient-to-r from-wine-600 to-wine-700 rounded-3xl p-10 md:p-14">
        <h2 class="text-2xl md:text-3xl font-bold text-white mb-4">
          {{ t('home.ctaTitle') }}
        </h2>
        <p class="text-wine-100 mb-8 max-w-xl mx-auto">
          {{ t('home.ctaDescription') }}
        </p>
        <router-link v-if="!isAuthenticated" to="/register">
          <BaseButton variant="secondary" size="lg" class="px-10 bg-white text-wine-700 hover:bg-wine-50">
            {{ t('home.createFreeAccount') }}
          </BaseButton>
        </router-link>
        <router-link v-else to="/wines">
          <BaseButton variant="secondary" size="lg" class="px-10 bg-white text-wine-700 hover:bg-wine-50">
            {{ t('home.startExploring') }}
          </BaseButton>
        </router-link>
      </div>
    </section>
  </div>
</template>

