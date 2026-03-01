<script setup lang="ts">
import { ref, computed } from 'vue'

interface Props {
  src: string | null | undefined
  alt: string
  fallback?: 'wine' | 'event' | 'user' | 'winery' | 'route'
  class?: string
}

const props = withDefaults(defineProps<Props>(), {
  fallback: 'wine',
  class: '',
})

const imageError = ref(false)

const fallbackImages = {
  wine: '/images/icons/reshot-icon-wine-bottle-T9X8JUFM32.svg',
  event: '/images/icons/reshot-icon-vine-tasting-P5GDV7FUBS.svg',
  user: null,
  winery: '/images/icons/reshot-icon-vine-cellar-PK3MZL62NG.svg',
  route: '/images/icons/reshot-icon-vineyard-H8S2KEC3PT.svg',
}

const showImage = computed(() => props.src && !imageError.value)
const fallbackImage = computed(() => fallbackImages[props.fallback])
const imageSrc = computed(() => props.src || undefined)

function handleError() {
  imageError.value = true
}
</script>

<template>
  <div :class="['relative bg-gray-100 overflow-hidden', props.class]">
    <!-- Real image -->
    <img
      v-if="showImage"
      :src="imageSrc"
      :alt="alt"
      :class="props.class"
      class="w-full h-full object-cover"
      @error="handleError"
    >

    <!-- Fallback -->
    <div
      v-else
      class="w-full h-full flex items-center justify-center bg-gray-50"
    >
      <!-- User icon (SVG) -->
      <svg
        v-if="fallback === 'user'"
        class="w-1/2 h-1/2 text-gray-400"
        fill="currentColor"
        viewBox="0 0 20 20"
      >
        <path
          fill-rule="evenodd"
          d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
          clip-rule="evenodd"
        />
      </svg>

      <!-- Icon fallback -->
      <img
        v-else-if="fallbackImage"
        :src="fallbackImage"
        :alt="alt"
        class="w-1/3 h-1/3 opacity-40"
      >

      <!-- Generic fallback -->
      <div v-else class="text-gray-400 text-center p-4">
        <svg
          class="w-12 h-12 mx-auto mb-2"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
          />
        </svg>
        <p class="text-xs">
          Sin imagen
        </p>
      </div>
    </div>
  </div>
</template>
