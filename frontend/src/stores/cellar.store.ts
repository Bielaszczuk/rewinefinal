import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { Wine } from '@domain/wine/wine.types'
import http from '@app/http'
import { API_ENDPOINTS } from '@config/constants'

export interface CellarWine {
  id: string
  wine: Wine
  quantity: number
  purchaseDate: Date | null
  purchasePrice: number | null
  location: string | null
  notes: string | null
  addedAt: Date
}

function mapApiWineToWine(w: Record<string, unknown>): Wine {
  return {
    id: w.id as string,
    name: w.name as string,
    winery: (w.wineryName as string) ?? '',
    type: (w.wineType as Wine['type']) ?? 'RED',
    region: (w.region as string) ?? '',
    country: (w.country as string) ?? '',
    grapeVarieties: [],
    vintage: (w.vintage as number | null) ?? null,
    alcoholContent: null,
    price: (w.priceMin as number | null) ?? null,
    rating: w.ratingAverage ? Number(w.ratingAverage) : null,
    reviewCount: 0,
    description: '',
    imageUrl: (w.imageUrl as string | null) ?? null,
    tastingNotes: null,
    foodPairings: [],
    awards: [],
    createdAt: new Date(),
    updatedAt: new Date(),
  }
}

export const useCellarStore = defineStore('cellar', () => {
  const cellarWines = ref<CellarWine[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  const totalBottles = computed(() =>
    cellarWines.value.reduce((sum, cw) => sum + cw.quantity, 0)
  )

  const totalValue = computed(() =>
    cellarWines.value.reduce((sum, cw) => {
      const price = cw.purchasePrice ?? cw.wine.price ?? 0
      return sum + price * cw.quantity
    }, 0)
  )

  const winesByType = computed(() => {
    const grouped: Record<string, CellarWine[]> = {}
    for (const cw of cellarWines.value) {
      const type = cw.wine.type
      if (!grouped[type]) {
        grouped[type] = []
      }
      grouped[type].push(cw)
    }
    return grouped
  })

  const winesByRegion = computed(() => {
    const grouped: Record<string, CellarWine[]> = {}
    for (const cw of cellarWines.value) {
      const region = cw.wine.region
      if (!grouped[region]) {
        grouped[region] = []
      }
      grouped[region].push(cw)
    }
    return grouped
  })

  async function fetchCellar() {
    loading.value = true
    error.value = null
    try {
      interface CellarApiEntry {
        id: string; wine: Record<string, unknown>; quantity: number; purchaseDate: string | null
        purchasePrice: number | null; location: string | null; notes: string | null; addedAt: string
      }
      const res = await http.get<CellarApiEntry[]>(API_ENDPOINTS.CELLAR)
      cellarWines.value = res.data.map((e: CellarApiEntry) => ({
        id: e.id,
        wine: mapApiWineToWine(e.wine ?? {}),
        quantity: e.quantity,
        purchaseDate: e.purchaseDate ? new Date(e.purchaseDate) : null,
        purchasePrice: e.purchasePrice ?? null,
        location: e.location ?? null,
        notes: e.notes ?? null,
        addedAt: new Date(e.addedAt),
      }))
    } catch (err: unknown) {
      error.value = err instanceof Error ? err.message : 'Failed to fetch cellar'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function addWine(wine: Wine, quantity = 1, options?: Partial<Omit<CellarWine, 'id' | 'wine' | 'addedAt'>>) {
    try {
      const res = await http.post(API_ENDPOINTS.CELLAR, {
        wineId: wine.id,
        quantity,
        purchaseDate: options?.purchaseDate?.toISOString().split('T')[0] ?? null,
        purchasePrice: options?.purchasePrice ?? null,
        location: options?.location ?? null,
        notes: options?.notes ?? null,
      })
      const e = res.data
      const existing = cellarWines.value.findIndex((cw) => cw.wine.id === wine.id)
      const newEntry: CellarWine = {
        id: e.id,
        wine: e.wine ? mapApiWineToWine(e.wine) : wine,
        quantity: e.quantity,
        purchaseDate: e.purchaseDate ? new Date(e.purchaseDate) : null,
        purchasePrice: e.purchasePrice ?? null,
        location: e.location ?? null,
        notes: e.notes ?? null,
        addedAt: new Date(e.addedAt),
      }
      if (existing !== -1) {
        cellarWines.value[existing] = newEntry
      } else {
        cellarWines.value.push(newEntry)
      }
    } catch (err: unknown) {
      error.value = err instanceof Error ? err.message : 'Failed to add wine'
      throw err
    }
  }

  async function removeWine(cellarWineId: string, _quantity = 1) {
    try {
      await http.delete(`${API_ENDPOINTS.CELLAR}/${cellarWineId}`)
      cellarWines.value = cellarWines.value.filter((cw) => cw.id !== cellarWineId)
    } catch (err: unknown) {
      error.value = err instanceof Error ? err.message : 'Failed to remove wine'
      throw err
    }
  }

  async function updateWine(cellarWineId: string, updates: Partial<Omit<CellarWine, 'id' | 'wine' | 'addedAt'>>) {
    try {
      const res = await http.patch(`${API_ENDPOINTS.CELLAR}/${cellarWineId}`, {
        quantity: updates.quantity,
        purchaseDate: updates.purchaseDate?.toISOString().split('T')[0] ?? null,
        purchasePrice: updates.purchasePrice ?? null,
        location: updates.location ?? null,
        notes: updates.notes ?? null,
      })
      const idx = cellarWines.value.findIndex((cw) => cw.id === cellarWineId)
      if (idx !== -1) {
        const e = res.data
        cellarWines.value[idx] = {
          ...cellarWines.value[idx],
          quantity: e.quantity,
          purchaseDate: e.purchaseDate ? new Date(e.purchaseDate) : null,
          purchasePrice: e.purchasePrice ?? null,
          location: e.location ?? null,
          notes: e.notes ?? null,
        }
      }
    } catch (err: unknown) {
      error.value = err instanceof Error ? err.message : 'Failed to update wine'
      throw err
    }
  }

  function clearCellar() {
    cellarWines.value = []
  }

  function reset() {
    cellarWines.value = []
    error.value = null
  }

  return {
    cellarWines,
    loading,
    error,
    totalBottles,
    totalValue,
    winesByType,
    winesByRegion,
    fetchCellar,
    addWine,
    removeWine,
    updateWine,
    clearCellar,
    reset,
  }
})
