/**
 * Wine DTOs
 *
 * Data Transfer Objects for wine API endpoints.
 * These represent the exact shape of data sent to/from the backend.
 * Note: Backend uses camelCase for all JSON fields.
 */

import type { WineType } from '@domain/wine/wine.types'



/**
 * Wine summary for list views (matches backend WineSummaryResponse)
 */
export interface WineSummaryDto {
  id: string
  name: string
  vintage?: number | null
  wineType: WineType
  style?: string | null
  wineryName?: string | null
  region?: string | null
  country?: string | null
  priceMin?: number | null
  priceMax?: number | null
  ratingAverage?: number | null
  ratingCount?: number
  imageUrl?: string | null
  isFeatured?: boolean
}

/**
 * Full wine details (matches backend WineDetailsResponse)
 */
export interface WineDetailsDto {
  id: string
  name: string
  vintage?: number | null
  wineType: WineType
  style?: string | null
  grapes?: string[]
  allergens?: string[]
  descriptionEs?: string | null
  descriptionEn?: string | null
  alcoholContent?: number | null
  servingTempMin?: number | null
  servingTempMax?: number | null
  priceMin?: number | null
  priceMax?: number | null
  imageUrl?: string | null
  ratingAverage?: number | null
  ratingCount?: number
  isFeatured?: boolean
  createdAt?: string


  winery?: WineryInfoDto | null


  ratingDistribution?: RatingDistributionDto | null


  featuredReviews?: WineReviewDto[]


  userWineData?: UserWineDataDto | null


  aiProfileStatus?: 'NOT_REQUESTED' | 'GENERATED' | null
  aiProfileGeneratedAt?: string | null
}

/**
 * Winery info embedded in wine details
 */
export interface WineryInfoDto {
  id: string
  name: string
  region?: string | null
  country?: string | null
  logoUrl?: string | null
  websiteUrl?: string | null
}

/**
 * Rating distribution for star ratings
 */
export interface RatingDistributionDto {
  oneStar: number
  twoStar: number
  threeStar: number
  fourStar: number
  fiveStar: number
}

/**
 * User-specific wine data
 */
export interface UserWineDataDto {
  hasReviewed?: boolean
  userRating?: number | null
  inCellar?: boolean
  cellarQuantity?: number | null
}

/**
 * Wine DTO (legacy alias)
 * @deprecated Use WineDetailsDto for full details, WineSummaryDto for lists
 */
export type WineDto = WineDetailsDto

/**
 * Tasting notes structure
 */
export interface TastingNotesDto {
  appearance: string
  aroma: string[]
  palate: string[]
  finish: string
  body: 'light' | 'medium' | 'full'
  sweetness: 'dry' | 'off-dry' | 'medium-sweet' | 'sweet'
  tannins: 'low' | 'medium' | 'high'
  acidity: 'low' | 'medium' | 'high'
}

/**
 * Wine award/recognition
 */
export interface WineAwardDto {
  name: string
  year: number
  medal?: 'gold' | 'silver' | 'bronze'
}



/**
 * Wine review (matches backend ReviewResponse)
 */
export interface WineReviewDto {
  id: string
  wineId: string
  userId: string
  username: string
  userAvatarUrl?: string | null
  rating: number
  title?: string | null
  comment?: string | null
  helpfulCount?: number
  isVerified?: boolean
  createdAt: string
  updatedAt?: string
}

/**
 * Create wine review request
 */
export interface CreateWineReviewRequestDto {
  rating: number
  title?: string
  comment?: string
}



/**
 * Wine comparison result (matches backend WineComparisonResponse)
 */
export interface CompareResultDto {
  wineAId: string
  wineAName?: string
  wineBId: string
  wineBName?: string
  language: string
  comparisonContent: ComparisonContentDto

  summary?: string
  similarities?: string[]
  keyDifferences?: string[]
  attributeComparison?: ComparisonContentDto['attributeComparison']
  bestFor?: ComparisonContentDto['bestFor']
  recommendation?: string
  cached: boolean
  generatedAt: string
}

/**
 * Comparison content from AI
 */
export interface ComparisonContentDto {
  similarities?: string[]
  differences?: string[]
  keyDifferences?: string[]
  recommendation?: string
  summary?: string
  attributeComparison?: {
    attributes: Array<{
      name: string
      wineAValue: string
      wineBValue: string
      wineAScore?: number
      wineBScore?: number
    }>
  }
  bestFor?: {
    wineA: string[]
    wineB: string[]
  }
}

/**
 * Comparison attribute structure
 */
export interface ComparisonAttributeDto {
  attribute: string
  values: string[]
  winner_index?: number | null
}



/**
 * AI-generated wine profile (matches backend WineAiProfileResponse)
 */
export interface AiProfileDto {
  wineId: string
  language: string
  profileContent: AiProfileContentDto
  cached: boolean
  generatedAt: string
}

/**
 * AI profile content
 */
export interface AiProfileContentDto {
  summary?: string

  visualAnalysis?: {
    color: string
    clarity: string
    intensity: string
    viscosity: string
  }
  aromaticProfile?: {
    intensity: string
    primaryAromas: string[]
    secondaryAromas?: string[]
    bouquet: string
  }
  palateAnalysis?: {
    sweetness: string
    acidity: string
    tannin: string
    alcohol: string
    body: string
    texture: string
    balance: string
  }
  flavorProfile?: {
    primaryFlavors: string[]
    secondaryFlavors?: string[]
    intensity: string
    finish: string
  }
  foodPairings?:
    | string[]
    | {
        pairings: string[]
        explanation?: string
      }
  servingRecommendations?: {
    temperature: string
    glassware: string
    decanting: string
    agingPotential: string
  }

  tastingNotes?: TastingNotesDto | string
  sensoryProfile?: {
    color?: string
    intensity?: string
    body?: string
    sweetness?: string
    acidity?: string
    tannin?: string
    alcohol?: string
    complexity?: string
  }
  idealOccasions?: string[]
  similarWines?: string[]
  funFacts?: string[]
  servingRecommendations?: {
    temperature?: string
    glassware?: string
    decanting?: string
    agingPotential?: string
  }
}



/**
 * Create wine request (matches backend format)
 */
export interface CreateWineRequestDto {
  name: string
  wineryId?: string
  wineType: WineType
  vintage?: number
  style?: string
  grapes?: string[]
  allergens?: string[]
  descriptionEs?: string
  descriptionEn?: string
  alcoholContent?: number
  servingTempMin?: number
  servingTempMax?: number
  priceMin?: number
  priceMax?: number
  imageUrl?: string
}

/**
 * Update wine request
 */
export type UpdateWineRequestDto = Partial<CreateWineRequestDto>

/**
 * Wine filter/search parameters (matches backend WineSearchRequest)
 */
export interface WineFilterParamsDto {
  search?: string
  wineType?: WineType
  country?: string
  region?: string
  grapeVariety?: string
  vintage?: number
  minPrice?: number
  maxPrice?: number
  minRating?: number
  featured?: boolean
  sortBy?: 'name' | 'vintage' | 'priceMin' | 'priceMax' | 'ratingAverage'
  sortDirection?: 'ASC' | 'DESC'
  page?: number
  size?: number
}



/**
 * Paginated wines response (matches backend PageResponse)
 */
export interface WinesPageResponseDto {
  items: WineSummaryDto[]
  content: WineSummaryDto[]
  pageNumber: number
  pageSize: number
  totalItems: number
  totalPages: number
  first: boolean
  last: boolean
  hasNext: boolean
  hasPrevious: boolean
}

/**
 * Paginated reviews response (matches backend PageResponse)
 */
export interface ReviewsPageResponseDto {
  items: WineReviewDto[]
  content: WineReviewDto[]
  pageNumber: number
  pageSize: number
  totalItems: number
  totalPages: number
  first: boolean
  last: boolean
  hasNext: boolean
  hasPrevious: boolean
}

/**
 * Wine scan/recognition result
 */
export interface WineScanResultDto {
  confidence: number
  wine?: WineSummaryDto
  suggestions?: WineSummaryDto[]
}
