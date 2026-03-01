<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  rating: number
  size?: 'sm' | 'md' | 'lg'
  interactive?: boolean
  showCount?: boolean
  count?: number
}

const props = withDefaults(defineProps<Props>(), {
  size: 'md',
  interactive: false,
  showCount: false,
  count: 0,
})

const emit = defineEmits<{
  'update:rating': [value: number]
}>()

const sizeClasses = computed(() => {
  switch (props.size) {
    case 'sm':
      return 'w-4 h-4'
    case 'lg':
      return 'w-8 h-8'
    default:
      return 'w-5 h-5'
  }
})

function handleClick(value: number) {
  if (props.interactive) {
    emit('update:rating', value)
  }
}

function getStarType(starNumber: number): 'full' | 'half' | 'empty' {

  if (props.rating >= starNumber) return 'full'
  if (props.rating >= starNumber - 0.5) return 'half'
  return 'empty'
}
</script>

<template>
  <div class="flex items-center gap-1">
    <div class="flex items-center gap-0.5">
      <button
        v-for="i in 5"
        :key="i"
        type="button"
        :class="[
          sizeClasses,
          interactive ? 'cursor-pointer hover:scale-110 transition-transform' : 'cursor-default',
        ]"
        :disabled="!interactive"
        @click="handleClick(i)"
      >
        <!-- Full star -->
        <svg
          v-if="getStarType(i) === 'full'"
          class="text-yellow-400"
          fill="currentColor"
          viewBox="0 0 20 20"
        >
          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
        </svg>

        <!-- Half star -->
        <svg
          v-else-if="getStarType(i) === 'half'"
          class="text-yellow-400"
          fill="currentColor"
          viewBox="0 0 20 20"
        >
          <defs>
            <linearGradient :id="`half-${i}`">
              <stop offset="50%" stop-color="currentColor" />
              <stop offset="50%" stop-color="#D1D5DB" />
            </linearGradient>
          </defs>
          <path
            :fill="`url(#half-${i})`"
            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
          />
        </svg>

        <!-- Empty star -->
        <svg
          v-else
          class="text-gray-300"
          fill="currentColor"
          viewBox="0 0 20 20"
        >
          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
        </svg>
      </button>
    </div>

    <span
      v-if="showCount && count > 0"
      class="text-sm text-gray-500 ml-1"
    >
      ({{ count }})
    </span>
  </div>
</template>
