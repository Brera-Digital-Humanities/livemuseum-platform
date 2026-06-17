// LiveMuseum API — TypeScript types
// Generated from /api/v1 response shapes

export type SchedaType = 'base' | 'unlockable' | 'livemuseum' | 'community'

export type DayCode = 'mon' | 'tue' | 'wed' | 'thu' | 'fri' | 'sat' | 'sun'

export interface PoiCategory {
  id: number
  name_en: string
  name_it: string
}

export interface Badge {
  id: number
  code: string
  name: string
  category: string
  descr: string
  picture: string | null
  earned: boolean
  earned_at: string | null  // ISO 8601
  progress: number
  target: number
}

export interface User {
  id: number
  username: string
  avatar: string | null
  badges: Badge[]
}

export interface Picture {
  id: number
  poi_id: number
  user: User | null
  picture: string
  created_at: string        // ISO 8601
  caption: string | null
  exif_lat: number | null
  exif_lng: number | null
  likes_num: number
  bookmarks_num: number
  comments_num: number
  likes: {
    users: User[]
  }
}

// Returned by /pois/highlights
export interface PoiSummary {
  id: number
  slug: string
  title: string
  type: string
  category_app: string
  category: PoiCategory
  schedaType: SchedaType
  city: string | null
  province: string | null
  lat: number | null
  lng: number | null
  image_url: string | null
  num_pictures: number
  num_likes: number
  num_bookmarks: number
  num_comments: number
}

// Returned by /pois/list and /pois/search (full shape, related compact)
export interface Poi {
  id: number
  id_opendata: string
  code: string
  slug: string
  type: string
  category_app: string
  schedaType: SchedaType
  title: string
  fulladdress: string | null
  address: string | null
  region: string | null
  province: string | null
  city: string | null
  zipcode: string | null
  lat: number | null
  lng: number | null
  distance: number | null   // metres, present on /pois/nearby
  category: PoiCategory
  descr: string
  image_url: string | null
  phone: string | null
  email: string | null
  website: string | null
  services: string[]
  num_pictures: number
  num_likes: number
  num_bookmarks: number
  num_comments: number
  days_open: DayCode[]
  hours_open: string | null
  opening_hours: string | null
  tickets_info: string | null
  booking_info: string | null
  source_url: string | null
  license: string | null
  source: string | null
  source_file: string | null
  last_picture: Picture | null
  pictures: Picture[]
  likes: { users: User[] }
  comments: { comments: unknown[] }
  bookmarks: { users: User[] }
}

// Pagination meta (search / list)
export interface PaginationMeta {
  total: number
  page: number
  per_page: number
  last_page: number
  has_more: boolean
  filters?: {
    categories: string[]
    services: string[]
  }
}

// Highlights meta
export interface HighlightsMeta {
  seed: number
  scheda_type: SchedaType | 'all'
  total_pool: number
}

// Paginated responses
export interface PoiListResponse {
  data: Poi[]
  meta: PaginationMeta
}

export interface PoiHighlightsResponse {
  data: PoiSummary[]
  meta: HighlightsMeta
}

export interface PoiCategoriesResponse {
  data: (PoiCategory & { count: number })[]
}

export interface PoiServicesResponse {
  data: string[]
}
