<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useCellarStore } from '@stores/cellar.store'
import type { CellarWine } from '@stores/cellar.store'
import BaseCard from '@components/common/BaseCard.vue'
import BaseButton from '@components/common/BaseButton.vue'
import BaseEmptyState from '@components/common/BaseEmptyState.vue'
import BaseSpinner from '@components/common/BaseSpinner.vue'
import { formatCurrency } from '@utils/format'

const { t } = useI18n()
const cellarStore = useCellarStore()

const cellarWines = computed(() => cellarStore.cellarWines)
const totalBottles = computed(() => cellarStore.totalBottles)
const totalValue = computed(() => cellarStore.totalValue)
const loading = computed(() => cellarStore.loading)


const editingEntry = ref<CellarWine | null>(null)
const editForm = ref({ quantity: 1, purchasePrice: null as number | null, location: '', notes: '' })
const saving = ref(false)
const confirmDeleteId = ref<string | null>(null)
const deleting = ref(false)

onMounted(() => cellarStore.fetchCellar())

function openEdit(entry: CellarWine) {
  editingEntry.value = entry
  editForm.value = {
    quantity: entry.quantity,
    purchasePrice: entry.purchasePrice,
    location: entry.location ?? '',
    notes: entry.notes ?? '',
  }
}

async function saveEdit() {
  if (!editingEntry.value) return
  saving.value = true
  try {
    await cellarStore.updateWine(editingEntry.value.id, {
      quantity: editForm.value.quantity,
      purchasePrice: editForm.value.purchasePrice,
      location: editForm.value.location || null,
      notes: editForm.value.notes || null,
    })
    editingEntry.value = null
  } finally {
    saving.value = false
  }
}

async function confirmDelete() {
  if (!confirmDeleteId.value) return
  deleting.value = true
  try {
    await cellarStore.removeWine(confirmDeleteId.value)
    confirmDeleteId.value = null
  } finally {
    deleting.value = false
  }
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">
          {{ t('cellar.title') }}
        </h1>
        <p class="text-sm text-gray-500 mt-1">
          {{ totalBottles }} {{ t('cellar.bottles') }}
        </p>
      </div>
      <router-link to="/wines">
        <BaseButton>
          + {{ t('cellar.addWine') }}
        </BaseButton>
      </router-link>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <BaseCard class="text-center">
        <div class="text-3xl font-bold text-wine-600">
          {{ totalBottles }}
        </div>
        <div class="text-sm text-gray-500">
          {{ t('cellar.bottles') }}
        </div>
      </BaseCard>
      <BaseCard class="text-center">
        <div class="text-3xl font-bold text-gray-900">
          {{ formatCurrency(totalValue) }}
        </div>
        <div class="text-sm text-gray-500">
          {{ t('cellar.totalValue') }}
        </div>
      </BaseCard>
      <BaseCard class="text-center">
        <div class="text-3xl font-bold text-gray-900">
          {{ Object.keys(cellarStore.winesByType).length }}
        </div>
        <div class="text-sm text-gray-500">
          {{ t('wines.type') }}
        </div>
      </BaseCard>
    </div>

    <div
      v-if="loading"
      class="flex justify-center py-12"
    >
      <BaseSpinner size="lg" />
    </div>

    <BaseEmptyState
      v-else-if="cellarWines.length === 0"
      icon="wine"
      :title="t('cellar.empty')"
      :description="t('cellar.addFirst')"
    >
      <template #action>
        <router-link to="/wines">
          <BaseButton>
            {{ t('cellar.addWine') }}
          </BaseButton>
        </router-link>
      </template>
    </BaseEmptyState>

    <div
      v-else
      class="space-y-4"
    >
      <BaseCard
        v-for="cellarWine in cellarWines"
        :key="cellarWine.id"
      >
        <div class="flex gap-4">
          <div class="w-16 h-20 bg-gray-100 rounded flex items-center justify-center flex-shrink-0 overflow-hidden">
            <img
              :src="cellarWine.wine.imageUrl || (cellarWine.wine.type === 'white' || cellarWine.wine.type === 'sparkling' ? '/images/mock/winebottle2.jpg' : '/images/mock/winebottle.jpg')"
              :alt="cellarWine.wine.name"
              class="w-full h-full object-cover rounded"
              @error="($event.target as HTMLImageElement).src = '/images/mock/winebottle.jpg'"
            >
          </div>
          <div class="flex-1 min-w-0">
            <router-link
              :to="`/wines/${cellarWine.wine.id}`"
              class="font-semibold text-gray-900 hover:text-wine-600 truncate block"
            >
              {{ cellarWine.wine.name }}
            </router-link>
            <p class="text-sm text-gray-600">
              {{ cellarWine.wine.winery }}
            </p>
            <p class="text-sm text-gray-500">
              {{ cellarWine.wine.vintage ?? 'NV' }} · {{ cellarWine.wine.region }}
            </p>
            <div class="flex items-center gap-4 mt-2 text-sm text-gray-500">
              <span class="font-medium text-gray-700">{{ cellarWine.quantity }} {{ t('cellar.bottles') }}</span>
              <span v-if="cellarWine.location">
                📍 {{ cellarWine.location }}
              </span>
              <span v-if="cellarWine.notes" class="italic truncate max-w-xs">
                {{ cellarWine.notes }}
              </span>
            </div>
          </div>
          <div class="text-right flex flex-col items-end gap-2">
            <p
              v-if="cellarWine.purchasePrice"
              class="font-semibold text-gray-900"
            >
              {{ formatCurrency(cellarWine.purchasePrice) }}
            </p>
            <p class="text-sm text-gray-400 capitalize">
              {{ cellarWine.wine.type }}
            </p>
            <div class="flex gap-2 mt-1">
              <button
                class="text-xs text-primary-600 hover:text-primary-800 font-medium"
                @click="openEdit(cellarWine)"
              >
                {{ t('common.edit') }}
              </button>
              <button
                class="text-xs text-red-500 hover:text-red-700 font-medium"
                @click="confirmDeleteId = cellarWine.id"
              >
                {{ t('common.delete') }}
              </button>
            </div>
          </div>
        </div>
      </BaseCard>
    </div>

    <div
      v-if="editingEntry"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
    >
      <div class="bg-white rounded-2xl p-6 w-full max-w-md space-y-4">
        <h2 class="text-lg font-bold text-gray-900">
          Editar entrada: {{ editingEntry.wine.name }}
        </h2>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('cellar.quantity') }}</label>
          <input
            v-model.number="editForm.quantity"
            type="number"
            min="1"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
          >
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('cellar.purchasePrice') }}</label>
          <input
            v-model.number="editForm.purchasePrice"
            type="number"
            step="0.01"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
          >
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('cellar.location') }}</label>
          <input
            v-model="editForm.location"
            type="text"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
          >
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('cellar.notes') }}</label>
          <textarea
            v-model="editForm.notes"
            rows="2"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
          />
        </div>
        <div class="flex justify-end gap-3 pt-2">
          <BaseButton
            variant="ghost"
            @click="editingEntry = null"
          >
            {{ t('common.cancel') }}
          </BaseButton>
          <BaseButton
            :loading="saving"
            @click="saveEdit"
          >
            {{ t('common.save') }}
          </BaseButton>
        </div>
      </div>
    </div>

    <div
      v-if="confirmDeleteId"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
    >
      <div class="bg-white rounded-2xl p-6 w-full max-w-sm space-y-4">
        <h3 class="font-bold text-gray-900">
          ¿Eliminar de tu bodega?
        </h3>
        <p class="text-sm text-gray-600">
          Esta acción eliminará la entrada permanentemente.
        </p>
        <div class="flex justify-end gap-3">
          <BaseButton
            variant="ghost"
            @click="confirmDeleteId = null"
          >
            {{ t('common.cancel') }}
          </BaseButton>
          <BaseButton
            variant="danger"
            :loading="deleting"
            @click="confirmDelete"
          >
            {{ t('common.delete') }}
          </BaseButton>
        </div>
      </div>
    </div>
  </div>
</template>

