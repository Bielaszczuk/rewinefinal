<script setup lang="ts">
import { ref, computed } from 'vue'
import BaseButton from '@components/common/BaseButton.vue'
import RatingStars from '@components/wines/RatingStars.vue'

interface Props {
  wineId: string
  initialRating?: number
  initialTitle?: string
  initialComment?: string
  isEditing?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  initialRating: 0,
  initialTitle: '',
  initialComment: '',
  isEditing: false,
})

const emit = defineEmits<{
  submit: [data: { rating: number; title: string; comment: string }]
  cancel: []
}>()

const rating = ref(props.initialRating)
const title = ref(props.initialTitle)
const comment = ref(props.initialComment)

const isValid = computed(() => rating.value > 0)

function handleSubmit() {
  if (!isValid.value) return

  emit('submit', {
    rating: rating.value,
    title: title.value.trim(),
    comment: comment.value.trim(),
  })
}

function handleCancel() {
  emit('cancel')
}
</script>

<template>
  <div class="space-y-4 p-6 bg-gray-50 rounded-lg">
    <h3 class="font-semibold text-gray-900">
      {{ isEditing ? 'Editar Reseña' : 'Escribir Reseña' }}
    </h3>

    <!-- Rating -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">
        Calificación *
      </label>
      <RatingStars
        :rating="rating"
        :interactive="true"
        size="lg"
        @update:rating="rating = $event"
      />
      <p
        v-if="rating === 0"
        class="text-xs text-gray-500 mt-1"
      >
        Haz clic en las estrellas para calificar
      </p>
    </div>

    <!-- Title -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">
        Título (opcional)
      </label>
      <input
        v-model="title"
        type="text"
        maxlength="255"
        placeholder="Resumen de tu experiencia..."
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
      >
    </div>

    <!-- Comment -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">
        Comentario (opcional)
      </label>
      <textarea
        v-model="comment"
        rows="4"
        placeholder="Comparte tu opinión sobre este vino..."
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 resize-none"
      />
      <p class="text-xs text-gray-500 mt-1">
        {{ comment.length }} caracteres
      </p>
    </div>

    <!-- Actions -->
    <div class="flex justify-end gap-3 pt-2">
      <BaseButton
        variant="ghost"
        @click="handleCancel"
      >
        Cancelar
      </BaseButton>
      <BaseButton
        :disabled="!isValid"
        @click="handleSubmit"
      >
        {{ isEditing ? 'Guardar Cambios' : 'Publicar Reseña' }}
      </BaseButton>
    </div>
  </div>
</template>
