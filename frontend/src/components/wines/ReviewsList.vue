<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@stores/auth.store'
import http from '@app/http'
import BaseButton from '@components/common/BaseButton.vue'
import BaseCard from '@components/common/BaseCard.vue'
import RatingStars from '@components/wines/RatingStars.vue'
import ReviewForm from '@components/wines/ReviewForm.vue'

interface Props {
  wineId: string
}

interface Review {
  id: string
  rating: number
  title: string | null
  comment: string | null
  likesCount: number
  commentsCount: number
  likedByCurrentUser: boolean
  reviewer: {
    id: string
    username: string
    displayName: string
    avatarUrl: string | null
    totalReviews: number
  } | null
  createdAt: string
  updatedAt: string
}

const props = defineProps<Props>()

const authStore = useAuthStore()
const reviews = ref<Review[]>([])
const loading = ref(false)
const page = ref(0)
const totalItems = ref(0)
const pageSize = 20
const sortBy = ref<'recent' | 'likes'>('recent')
const showReviewForm = ref(false)
const editingReviewId = ref<string | null>(null)
const submitting = ref(false)

const currentUser = computed(() => authStore.user)
const isAuthenticated = computed(() => authStore.isAuthenticated)
const totalPages = computed(() => Math.ceil(totalItems.value / pageSize))
const hasUserReviewed = computed(() => {
  if (!currentUser.value) return false
  return reviews.value.some(r => r.reviewer?.id === currentUser.value?.id)
})

async function fetchReviews() {
  loading.value = true
  try {
    const params = {
      page: page.value,
      size: pageSize,
      filter: sortBy.value === 'likes' ? 'popular' : 'recent',
    }
    const res = await http.get(`/wines/${props.wineId}/reviews`, { params })
    const responseData = res.data.data ?? res.data
    reviews.value = responseData.items ?? responseData.content ?? []
    totalItems.value = responseData.totalItems ?? responseData.totalElements ?? 0
  } finally {
    loading.value = false
  }
}

async function handleSubmitReview(data: { rating: number; title: string; comment: string }) {
  if (!isAuthenticated.value) return

  submitting.value = true
  try {
    if (editingReviewId.value) {
      await http.put(`/wines/${props.wineId}/reviews/${editingReviewId.value}`, data)
    } else {
      await http.post(`/wines/${props.wineId}/reviews`, data)
    }
    showReviewForm.value = false
    editingReviewId.value = null
    await fetchReviews()
  } catch (error: unknown) {
    console.error('Error submitting review:', error)
    const axiosError = error as { response?: { status?: number } }
    if (axiosError.response?.status === 409) {
      alert('Ya has publicado una reseña para este vino. Puedes editar tu reseña existente.')
    } else {
      alert('Error al guardar la reseña. Por favor intenta nuevamente.')
    }
  } finally {
    submitting.value = false
  }
}

async function handleLike(reviewId: string) {
  if (!isAuthenticated.value) return

  const review = reviews.value.find(r => r.id === reviewId)
  if (!review) return

  try {
    if (review.likedByCurrentUser) {
      await http.delete(`/wines/${props.wineId}/reviews/${reviewId}/like`)
      review.likesCount--
      review.likedByCurrentUser = false
    } else {
      await http.post(`/wines/${props.wineId}/reviews/${reviewId}/like`)
      review.likesCount++
      review.likedByCurrentUser = true
    }
  } catch (error) {
    console.error('Error toggling like:', error)
  }
}

async function handleDelete(reviewId: string) {
  if (!confirm('¿Estás seguro de que deseas eliminar esta reseña?')) return

  try {
    await http.delete(`/wines/${props.wineId}/reviews/${reviewId}`)
    await fetchReviews()
  } catch (error) {
    console.error('Error deleting review:', error)
    alert('Error al eliminar la reseña')
  }
}

function handleEdit(review: Review) {
  editingReviewId.value = review.id
  showReviewForm.value = true
}

function canEditReview(review: Review): boolean {
  if (!currentUser.value) return false
  if (review.reviewer?.id === currentUser.value.id) return true
  return authStore.isAdmin || authStore.isModerator
}

function handleSortChange() {
  page.value = 0
  fetchReviews()
}

function formatDate(dateString: string): string {
  const date = new Date(dateString)
  return date.toLocaleDateString('es-AR', { year: 'numeric', month: 'long', day: 'numeric' })
}

onMounted(fetchReviews)
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <h2 class="text-2xl font-bold text-gray-900">
        Reseñas ({{ totalItems }})
      </h2>

      <!-- Sort selector -->
      <div class="flex items-center gap-2">
        <label class="text-sm text-gray-600">
          Ordenar por:
        </label>
        <select
          v-model="sortBy"
          class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-primary-500"
          @change="handleSortChange"
        >
          <option value="recent">
            Más recientes
          </option>
          <option value="likes">
            Más valoradas
          </option>
        </select>
      </div>
    </div>

    <!-- Add Review Button -->
    <div v-if="isAuthenticated && !hasUserReviewed && !showReviewForm">
      <BaseButton
        class="w-full sm:w-auto"
        @click="showReviewForm = true"
      >
        Escribir Reseña
      </BaseButton>
    </div>

    <!-- Review Form -->
    <ReviewForm
      v-if="showReviewForm"
      :wine-id="wineId"
      :is-editing="!!editingReviewId"
      @submit="handleSubmitReview"
      @cancel="showReviewForm = false; editingReviewId = null"
    />

    <!-- Loading -->
    <div
      v-if="loading"
      class="text-center py-8 text-gray-500"
    >
      Cargando reseñas...
    </div>

    <!-- Reviews List -->
    <div
      v-else-if="reviews.length > 0"
      class="space-y-4"
    >
      <BaseCard
        v-for="review in reviews"
        :key="review.id"
        class="hover:shadow-md transition-shadow"
      >
        <div class="space-y-3">
          <!-- Header -->
          <div class="flex items-start justify-between">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center overflow-hidden">
                <img
                  v-if="review.reviewer?.avatarUrl"
                  :src="review.reviewer.avatarUrl"
                  :alt="review.reviewer.displayName"
                  class="w-full h-full object-cover"
                >
                <svg
                  v-else
                  class="w-6 h-6 text-gray-400"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path
                    fill-rule="evenodd"
                    d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                    clip-rule="evenodd"
                  />
                </svg>
              </div>
              <div>
                <p class="font-medium text-gray-900">
                  {{ review.reviewer?.displayName || 'Usuario' }}
                </p>
                <div class="flex items-center gap-2 text-xs text-gray-500">
                  <span>{{ formatDate(review.createdAt) }}</span>
                  <span
                    v-if="review.createdAt !== review.updatedAt"
                    class="text-gray-400"
                  >
                    (editado)
                  </span>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div
              v-if="canEditReview(review)"
              class="flex items-center gap-2"
            >
              <button
                class="text-sm text-primary-600 hover:text-primary-800"
                @click="handleEdit(review)"
              >
                Editar
              </button>
              <button
                class="text-sm text-red-500 hover:text-red-700"
                @click="handleDelete(review.id)"
              >
                Eliminar
              </button>
            </div>
          </div>

          <!-- Rating -->
          <RatingStars :rating="review.rating" />

          <!-- Title -->
          <h3
            v-if="review.title"
            class="font-semibold text-gray-900"
          >
            {{ review.title }}
          </h3>

          <!-- Comment -->
          <p
            v-if="review.comment"
            class="text-gray-700 text-sm"
          >
            {{ review.comment }}
          </p>

          <!-- Footer -->
          <div class="flex items-center gap-4 pt-2 border-t border-gray-100">
            <!-- Like button -->
            <button
              :class="[
                'flex items-center gap-1 text-sm transition-colors',
                review.likedByCurrentUser
                  ? 'text-primary-600'
                  : 'text-gray-500 hover:text-primary-600'
              ]"
              :disabled="!isAuthenticated"
              @click="handleLike(review.id)"
            >
              <svg
                class="w-4 h-4"
                :fill="review.likedByCurrentUser ? 'currentColor' : 'none'"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"
                />
              </svg>
              <span>{{ review.likesCount }}</span>
            </button>

            <!-- Comments count -->
            <div class="flex items-center gap-1 text-sm text-gray-500">
              <svg
                class="w-4 h-4"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                />
              </svg>
              <span>{{ review.commentsCount }}</span>
            </div>
          </div>
        </div>
      </BaseCard>
    </div>

    <!-- Empty state -->
    <div
      v-else
      class="text-center py-12"
    >
      <svg
        class="w-16 h-16 mx-auto text-gray-300 mb-4"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"
        />
      </svg>
      <p class="text-gray-500 mb-4">
        Aún no hay reseñas para este vino
      </p>
      <BaseButton
        v-if="isAuthenticated"
        @click="showReviewForm = true"
      >
        Sé el primero en reseñar
      </BaseButton>
    </div>

    <!-- Pagination -->
    <div
      v-if="totalPages > 1"
      class="flex justify-center items-center gap-2"
    >
      <button
        :disabled="page === 0"
        :class="[
          'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
          page === 0
            ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
            : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50'
        ]"
        @click="page--; fetchReviews()"
      >
        Anterior
      </button>
      <span class="text-sm text-gray-600">
        Página {{ page + 1 }} de {{ totalPages }}
      </span>
      <button
        :disabled="page >= totalPages - 1"
        :class="[
          'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
          page >= totalPages - 1
            ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
            : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50'
        ]"
        @click="page++; fetchReviews()"
      >
        Siguiente
      </button>
    </div>
  </div>
</template>
