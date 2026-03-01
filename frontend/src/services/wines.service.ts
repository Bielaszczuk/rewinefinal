/**
 * Wines Service
 *
 * Application service layer for wine-related operations.
 * Orchestrates: API client calls -> DTO mapping -> domain model returns.
 */

import { winesClient } from '@api/clients/wines.client'
import {
  mapWineFromDto,
  mapWineSummaryFromDto,
  mapWineReviewFromDto,
  mapWineToDto,
  mapWineComparisonFromDto,
  mapAiProfileFromDto,
  mapWineScanResultFromDto,
} from '@domain/wine/wine.mappers'
import type {
  Wine,
  WineSummary,
  WineReview,
  WineFilter,
  WineComparison,
  AiWineProfile,
  WineScanResult,
} from '@domain/wine/wine.types'
import type { WineFilterParamsDto, CreateWineRequestDto, UpdateWineRequestDto } from '@api/dto/wines.dto'
import type { PageMeta } from '@api/api.types'



export interface WinesResult {
  wines: WineSummary[]
  pagination: PageMeta
}

export interface WineDetailsResult {
  wine: Wine
}

export interface WineReviewsResult {
  reviews: WineReview[]
  pagination: PageMeta
}



/**
 * Wines service - orchestrates wine-related business logic
 */
export const winesService = {


  /**
   * Get paginated list of wines
   */
  async getWines(filter?: WineFilter, page = 0, pageSize = 20): Promise<WinesResult> {
    const params = mapFilterToParams(filter, page, pageSize)
    const response = await winesClient.getWines(params)


    const items = response.items ?? response.content ?? []

    return {
      wines: items.map(mapWineSummaryFromDto),
      pagination: {
        pageNumber: response.pageNumber,
        pageSize: response.pageSize,
        totalItems: Number(response.totalItems),
        totalPages: response.totalPages,
        hasNext: response.hasNext,
        hasPrevious: response.hasPrevious,
      },
    }
  },

  /**
   * Get a single wine by ID
   */
  async getWine(id: string): Promise<Wine> {
    const response = await winesClient.getWine(id)
    return mapWineFromDto(response)
  },

  /**
   * Get similar wines
   */
  async getSimilarWines(wineId: string, limit = 5): Promise<WineSummary[]> {
    const response = await winesClient.getSimilarWines(wineId, limit)
    return response.map(mapWineSummaryFromDto)
  },

  /**
   * Get recommended wines for current user
   */
  async getRecommendedWines(limit = 10): Promise<WineSummary[]> {
    const response = await winesClient.getRecommendedWines(limit)
    return response.map(mapWineSummaryFromDto)
  },

  /**
   * Get popular wines
   */
  async getPopularWines(limit = 10): Promise<WineSummary[]> {
    const response = await winesClient.getPopularWines(limit)
    return response.map(mapWineSummaryFromDto)
  },



  /**
   * Create a new wine
   */
  async createWine(wine: Omit<Wine, 'id' | 'rating' | 'reviewCount' | 'awards' | 'createdAt' | 'updatedAt'>): Promise<Wine> {
    const dto = mapWineToDto(wine) as unknown as CreateWineRequestDto
    const response = await winesClient.createWine(dto)
    return mapWineFromDto(response)
  },

  /**
   * Update a wine
   */
  async updateWine(id: string, wine: Partial<Wine>): Promise<Wine> {
    const dto = mapWineToDto(wine) as UpdateWineRequestDto
    const response = await winesClient.updateWine(id, dto)
    return mapWineFromDto(response)
  },

  /**
   * Delete a wine
   */
  async deleteWine(id: string): Promise<void> {
    await winesClient.deleteWine(id)
  },



  /**
   * Get reviews for a wine
   */
  async getWineReviews(wineId: string, page = 0, pageSize = 10): Promise<WineReviewsResult> {
    const response = await winesClient.getWineReviews(wineId, { page, pageSize })


    const reviews = response.items ?? response.content ?? []

    return {
      reviews: reviews.map(mapWineReviewFromDto),
      pagination: {
        pageNumber: response.pageNumber ?? page,
        pageSize: response.pageSize ?? pageSize,
        totalItems: response.totalItems ?? 0,
        totalPages: response.totalPages ?? 0,
        hasNext: response.hasNext ?? false,
        hasPrevious: response.hasPrevious ?? false,
      },
    }
  },

  /**
   * Add a review to a wine
   */
  async addReview(wineId: string, rating: number, comment?: string): Promise<WineReview> {
    const response = await winesClient.createWineReview(wineId, { rating, comment })
    return mapWineReviewFromDto(response)
  },

  /**
   * Update a review
   */
  async updateReview(wineId: string, reviewId: string, rating: number, comment?: string): Promise<WineReview> {
    const response = await winesClient.updateWineReview(wineId, reviewId, { rating, comment })
    return mapWineReviewFromDto(response)
  },

  /**
   * Delete a review
   */
  async deleteReview(wineId: string, reviewId: string): Promise<void> {
    await winesClient.deleteWineReview(wineId, reviewId)
  },

  /**
   * Mark a review as helpful
   */
  async markReviewHelpful(wineId: string, reviewId: string): Promise<void> {
    await winesClient.markReviewHelpful(wineId, reviewId)
  },



  /**
   * Compare two wines
   */
  async compareWines(wineAId: string, wineBId: string, language = 'es'): Promise<WineComparison> {
    const response = await winesClient.compareWines(wineAId, wineBId, language)
    return mapWineComparisonFromDto(response)
  },

  /**
   * Get AI-generated wine profile
   */
  async getAiProfile(wineId: string, language = 'es'): Promise<AiWineProfile> {
    const response = await winesClient.getAiProfile(wineId, language)
    return mapAiProfileFromDto(response)
  },

  /**
   * Scan wine label image
   */
  async scanWineLabel(imageFile: File | string): Promise<WineScanResult> {
    const response = await winesClient.scanWineLabel(imageFile)
    return mapWineScanResultFromDto(response)
  },



  /**
   * Add wine to favorites
   */
  async addToFavorites(wineId: string): Promise<void> {
    await winesClient.addToFavorites(wineId)
  },

  /**
   * Remove wine from favorites
   */
  async removeFromFavorites(wineId: string): Promise<void> {
    await winesClient.removeFromFavorites(wineId)
  },

  /**
   * Get user's favorite wines
   */
  async getFavoriteWines(page = 1, pageSize = 20): Promise<WinesResult> {
    const response = await winesClient.getFavoriteWines({ page, pageSize })


    const items = response.data ?? []

    return {
      wines: items.map(mapWineSummaryFromDto),
      pagination: {
        pageNumber: response.pagination?.pageNumber ?? 0,
        pageSize: response.pagination?.pageSize ?? pageSize,
        totalItems: Number(response.pagination?.totalItems ?? 0),
        totalPages: response.pagination?.totalPages ?? 0,
        hasNext: response.pagination?.hasNext ?? false,
        hasPrevious: response.pagination?.hasPrevious ?? false,
      },
    }
  },
}



/**
 * Map domain filter to API filter params (matches backend WineSearchRequest)
 */
function mapFilterToParams(filter: WineFilter | undefined, page: number, pageSize: number): WineFilterParamsDto {

  const sortByMap: Record<string, 'name' | 'vintage' | 'priceMin' | 'priceMax' | 'ratingAverage'> = {
    'name': 'name',
    'price': 'priceMin',
    'rating': 'ratingAverage',
    'vintage': 'vintage',
  }

  return {
    page,
    size: pageSize,
    search: filter?.search,
    wineType: filter?.type as WineFilterParamsDto['wineType'],
    region: Array.isArray(filter?.region) ? filter?.region[0] : filter?.region,
    country: Array.isArray(filter?.country) ? filter?.country[0] : filter?.country,
    grapeVariety: Array.isArray(filter?.grapeVariety) ? filter?.grapeVariety[0] : filter?.grapeVariety,
    minPrice: filter?.minPrice,
    maxPrice: filter?.maxPrice,
    minRating: filter?.minRating,
    vintage: Array.isArray(filter?.vintage) ? filter?.vintage[0] : filter?.vintage,
    sortBy: filter?.sortBy ? sortByMap[filter.sortBy] : undefined,
    sortDirection: filter?.sortOrder?.toUpperCase() as 'ASC' | 'DESC' | undefined,
  }
}

export default winesService
