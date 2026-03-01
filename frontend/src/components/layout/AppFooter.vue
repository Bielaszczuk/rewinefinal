<script setup lang="ts">
import { useI18n } from 'vue-i18n'
import { computed } from 'vue'

const { t } = useI18n()
const currentYear = new Date().getFullYear()

const footerLinks = computed(() => ({
  discover: [
    { label: t('compare.browseWines'), path: '/wines' },
    { label: t('nav.routes'), path: '/wine-routes' },
    { label: t('nav.events'), path: '/events' },
    { label: t('scan.scanWine'), path: '/wines/scan' },
  ],
  company: [
    { label: t('footer.aboutUs'), path: '/about' },
    { label: t('footer.contact'), path: '/contact' },
    { label: t('footer.careers'), path: '/careers' },
    { label: t('footer.press'), path: '/press' },
  ],
  legal: [
    { label: t('footer.termsOfUse'), path: '/terms' },
    { label: t('auth.privacyPolicy'), path: '/privacy' },
    { label: t('footer.cookiePolicy'), path: '/cookies' },
    { label: t('footer.contentPolicy'), path: '/content-policy' },
  ],
}))

const socialLinks = [
  {
    name: 'Instagram',
    url: 'https://instagram.com/rewine',
    icon: 'M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z'
  },
  {
    name: 'Facebook',
    url: 'https://facebook.com/rewine',
    icon: 'M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z'
  },
  {
    name: 'X (Twitter)',
    url: 'https://x.com/rewine',
    icon: 'M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z'
  },
]

const features = computed(() => [
  {
    icon: '/images/icons/reshot-icon-vine-tasting-P5GDV7FUBS.svg',
    title: t('footer.featureDiscover'),
    description: t('footer.featureDiscoverDesc'),
  },
  {
    icon: '/images/icons/reshot-icon-male-sommelier-4J9YXUNDT3.svg',
    title: t('footer.featureExpert'),
    description: t('footer.featureExpertDesc'),
  },
  {
    icon: '/images/icons/reshot-icon-wine-transport-RMVBTH7EU4.svg',
    title: t('footer.featureRoutes'),
    description: t('footer.featureRoutesDesc'),
  },
  {
    icon: '/images/icons/reshot-icon-wine-card-9HA4ZDXEGJ.svg',
    title: t('footer.featureReviews'),
    description: t('footer.featureReviewsDesc'),
  },
])
</script>

<template>
  <footer class="bg-stone-100">
    <!-- Trust Section -->
    <div class="border-t border-stone-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h2 class="text-2xl md:text-3xl font-bold text-gray-900 text-center mb-10">
          {{ t('footer.trustedBy') }}
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          <div v-for="feature in features" :key="feature.title" class="flex items-start gap-4">
            <img :src="feature.icon" :alt="feature.title" class="w-12 h-12 flex-shrink-0" />
            <div>
              <h3 class="font-semibold text-gray-900 mb-1">{{ feature.title }}</h3>
              <p class="text-sm text-gray-600">{{ feature.description }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Footer -->
    <div class="bg-stone-600">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8 lg:gap-12">
          <!-- Brand Column -->
          <div class="lg:col-span-2">
            <router-link to="/" class="flex items-center gap-2 mb-4">
              <img src="/images/icons/reshot-icon-grapes-DH3FRP42X5.svg" alt="Rewine" class="w-8 h-8 brightness-0 invert" />
              <span class="text-2xl font-bold text-white">rewine</span>
            </router-link>
            <p class="text-stone-300 text-sm mb-6 max-w-sm">
              {{ t('footer.brandDescription') }}
            </p>

            <!-- Social Links -->
            <div class="flex items-center gap-4">
              <a
                v-for="social in socialLinks"
                :key="social.name"
                :href="social.url"
                target="_blank"
                rel="noopener noreferrer"
                class="w-10 h-10 bg-stone-500 hover:bg-stone-400 rounded-full flex items-center justify-center transition-colors"
                :aria-label="social.name"
              >
                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                  <path :d="social.icon" />
                </svg>
              </a>
            </div>
          </div>

          <!-- Discover Links -->
          <div>
            <h4 class="text-white font-semibold mb-4">{{ t('footer.discover') }}</h4>
            <ul class="space-y-3">
              <li v-for="link in footerLinks.discover" :key="link.path">
                <router-link
                  :to="link.path"
                  class="text-stone-300 hover:text-white text-sm transition-colors"
                >
                  {{ link.label }}
                </router-link>
              </li>
            </ul>
          </div>

          <!-- Company Links -->
          <div>
            <h4 class="text-white font-semibold mb-4">{{ t('footer.company') }}</h4>
            <ul class="space-y-3">
              <li v-for="link in footerLinks.company" :key="link.path">
                <router-link
                  :to="link.path"
                  class="text-stone-300 hover:text-white text-sm transition-colors"
                >
                  {{ link.label }}
                </router-link>
              </li>
            </ul>
          </div>

          <!-- Legal Links -->
          <div>
            <h4 class="text-white font-semibold mb-4">{{ t('footer.legal') }}</h4>
            <ul class="space-y-3">
              <li v-for="link in footerLinks.legal" :key="link.path">
                <router-link
                  :to="link.path"
                  class="text-stone-300 hover:text-white text-sm transition-colors"
                >
                  {{ link.label }}
                </router-link>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Bottom Bar -->
      <div class="border-t border-stone-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <p class="text-stone-300 text-sm">
              © {{ currentYear }} Rewine. {{ t('footer.allRightsReserved') }}
            </p>
            <p class="text-stone-300 text-sm">
              {{ t('footer.madeWith') }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>

