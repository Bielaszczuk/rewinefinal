<script setup lang="ts">
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const teamMembers = [
  {
    name: 'Cristhian Bielaszczuk',
    role: t('about.team.developer'),
    initials: 'CB'
  }
]

const stats = [
  { value: '500+', label: t('about.stats.wines') },
  { value: '50+', label: t('about.stats.wineries') },
  { value: '100+', label: t('about.stats.events') },
  { value: '1000+', label: t('about.stats.users') }
]

const values = [
  {
    icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
    title: t('about.values.quality'),
    description: t('about.values.qualityDesc')
  },
  {
    icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
    title: t('about.values.community'),
    description: t('about.values.communityDesc')
  },
  {
    icon: 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z',
    title: t('about.values.innovation'),
    description: t('about.values.innovationDesc')
  },
  {
    icon: 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    title: t('about.values.sustainability'),
    description: t('about.values.sustainabilityDesc')
  }
]
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <section class="relative bg-wine-900 text-white py-20">
      <div class="absolute inset-0 opacity-20">
        <img
          src="/images/winery/wineyard.jpg"
          alt=""
          class="w-full h-full object-cover"
        />
      </div>
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl md:text-5xl font-bold mb-6">
          {{ t('about.title') }}
        </h1>
        <p class="text-xl text-wine-200 max-w-3xl mx-auto">
          {{ t('about.subtitle') }}
        </p>
      </div>
    </section>

    <section class="py-16 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-2 gap-12 items-center">
          <div>
            <h2 class="text-3xl font-bold text-gray-900 mb-6">
              {{ t('about.mission.title') }}
            </h2>
            <p class="text-lg text-gray-600 mb-4">
              {{ t('about.mission.paragraph1') }}
            </p>
            <p class="text-lg text-gray-600 mb-4">
              {{ t('about.mission.paragraph2') }}
            </p>
            <p class="text-lg text-gray-600">
              {{ t('about.mission.paragraph3') }}
            </p>
          </div>
          <div class="relative">
            <img
              src="/images/winery/bottlesincava.jpg"
              alt="Wine cellar"
              class="rounded-lg shadow-xl"
            />
            <div class="absolute -bottom-6 -left-6 bg-wine-600 text-white p-6 rounded-lg shadow-lg">
              <p class="text-3xl font-bold">2024</p>
              <p class="text-wine-200">{{ t('about.founded') }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="py-16 bg-wine-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-center text-gray-900 mb-12">
          {{ t('about.values.title') }}
        </h2>
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
          <div
            v-for="value in values"
            :key="value.title"
            class="bg-white p-6 rounded-lg shadow-md text-center"
          >
            <div class="w-16 h-16 bg-wine-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg class="w-8 h-8 text-wine-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="value.icon" />
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ value.title }}</h3>
            <p class="text-gray-600">{{ value.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <section class="py-16 bg-wine-900 text-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
          <div v-for="stat in stats" :key="stat.label">
            <p class="text-4xl md:text-5xl font-bold text-wine-300">{{ stat.value }}</p>
            <p class="text-wine-200 mt-2">{{ stat.label }}</p>
          </div>
        </div>
      </div>
    </section>

    <section class="py-16 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-center text-gray-900 mb-4">
          {{ t('about.team.title') }}
        </h2>
        <p class="text-lg text-gray-600 text-center mb-12 max-w-2xl mx-auto">
          {{ t('about.team.subtitle') }}
        </p>
        <div class="flex justify-center">
          <div
            v-for="member in teamMembers"
            :key="member.name"
            class="text-center"
          >
            <div class="w-32 h-32 mx-auto mb-4 rounded-full overflow-hidden bg-wine-600 flex items-center justify-center">
              <span class="text-3xl font-bold text-white">{{ member.initials }}</span>
            </div>
            <h3 class="text-xl font-semibold text-gray-900">{{ member.name }}</h3>
            <p class="text-wine-600">{{ member.role }}</p>
          </div>
        </div>
      </div>
    </section>

    <section class="py-16 bg-gray-50">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold text-gray-900 mb-6">
          {{ t('about.cta.title') }}
        </h2>
        <p class="text-lg text-gray-600 mb-8">
          {{ t('about.cta.description') }}
        </p>
        <div class="flex flex-wrap justify-center gap-4">
          <router-link
            to="/wines"
            class="inline-flex items-center px-6 py-3 bg-wine-600 text-white font-semibold rounded-lg hover:bg-wine-700 transition-colors"
          >
            {{ t('about.cta.exploreWines') }}
          </router-link>
          <router-link
            to="/contact"
            class="inline-flex items-center px-6 py-3 border-2 border-wine-600 text-wine-600 font-semibold rounded-lg hover:bg-wine-50 transition-colors"
          >
            {{ t('about.cta.contactUs') }}
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

