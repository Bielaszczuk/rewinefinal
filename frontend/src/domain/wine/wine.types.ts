/**
 * Wine domain types
 *
 * These are the domain models used throughout the application.
 * They represent the canonical shape of data after mapping from DTOs.
 */



export interface Wine {
  id: string
  name: string
  winery: string
  wineryId: string | null
  type: WineType
  region: string
  country: string
  grapeVarieties: string[]
  vintage: number | null
  alcoholContent: number | null
  price: number | null
  rating: number | null
  reviewCount: number
  description: string
  imageUrl: string | null
  tastingNotes: TastingNotes | null
  foodPairings: string[]
  awards: WineAward[]
  createdAt: Date
  updatedAt: Date
}

/**
 * Wine summary for list views (lighter than full Wine)
 */
export interface WineSummary {
  id: string
  name: string
  winery: string
  type: WineType
  region: string
  country: string
  vintage: number | null
  price: number | null
  rating: number | null
  reviewCount: number
  imageUrl: string | null
}

export type WineType = 'red' | 'white' | 'rose' | 'sparkling' | 'dessert' | 'fortified'



export interface TastingNotes {
  appearance: string
  aroma: string[]
  palate: string[]
  finish: string
  body: WineBody
  sweetness: WineSweetness
  tannins: WineLevel
  acidity: WineLevel
}

export type WineBody = 'light' | 'medium' | 'full'
export type WineSweetness = 'dry' | 'off-dry' | 'medium-sweet' | 'sweet'
export type WineLevel = 'low' | 'medium' | 'high'



export interface WineAward {
  name: string
  year: number
  medal: 'gold' | 'silver' | 'bronze' | null
}



export interface WineReview {
  id: string
  wineId: string
  userId: string
  userName: string
  userAvatar: string | null
  rating: number
  comment: string
  helpfulCount: number
  createdAt: Date
}



export interface WineFilter {
  search?: string
  type?: WineType | WineType[]
  region?: string | string[]
  country?: string | string[]
  grapeVariety?: string | string[]
  winery?: string | string[]
  minPrice?: number
  maxPrice?: number
  minRating?: number
  maxRating?: number
  vintage?: number | number[]
  minVintage?: number
  maxVintage?: number
  sortBy?: 'name' | 'price' | 'rating' | 'vintage' | 'createdAt'
  sortOrder?: 'asc' | 'desc'
}



export interface WineComparison {
  wines: Wine[]
  attributes: ComparisonAttribute[]
  aiSummary: string | null

  summary?: string
  similarities?: string[]
  keyDifferences?: string[]
  differences?: string[]
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
  recommendation?: string
  fromCache?: boolean
}

export interface ComparisonAttribute {
  name: string
  values: (string | number | null)[]
  winnerIndex: number | null
}



export interface AiWineProfile {
  wineId: string
  summary: string
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
  foodPairings?: {
    pairings: string[]
    explanation?: string
  }
  servingRecommendations?: {
    temperature: string
    glassware: string
    decanting: string
    agingPotential: string
  }
  generatedAt: Date

  idealOccasions?: string[]
  foodPairingsDetailed?: AiFoodPairing[]
  similarWines?: string[]
  personalityTraits?: string[]
}

export interface AiFoodPairing {
  dish: string
  category: string
  matchScore: number
  reason: string
}



export interface WineScanResult {
  confidence: number
  wine: WineSummary | null
  suggestions: WineSummary[]
}
