export interface MarketplaceCategory {
    id: number;
    nama: string;
    prioritas: number;
    kriteria: string | null;
    slug?: string;
    full_slug?: string;
}

export interface MarketplaceBrand {
    id: number;
    nama: string;
    slug: string;
}

export interface MarketplaceListing {
    id: number;
    judul: string;
    model: string;
    harga: number;
    kondisi: string;
    prosesor: string;
    ram_gb: number;
    penyimpanan: string;
    brand_name: string | null;
    category_name: string | null;
    photo_url: string | null;
    created_at: string | null;
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginatedListings {
    data: MarketplaceListing[];
    current_page: number;
    first_page_url: string;
    from: number | null;
    last_page: number;
    links: PaginationLink[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number | null;
    total: number;
}

export interface CatalogFilters {
    q: string | null;
    category: string | null;
    brand: string | null;
    min_price: number | null;
    max_price: number | null;
    sort: 'newest' | 'price_asc' | 'price_desc' | string;
}

export interface CatalogFilterErrors {
    price?: string;
}

export interface MarketplaceListingPhoto {
    id: number;
    url: string;
}

export interface MarketplaceSeller {
    name: string;
    status_mahasiswa: string;
    status_verifikasi: string;
}

export interface MarketplaceListingDetail {
    id: number;
    judul: string;
    model: string;
    harga: number;
    kondisi: string;
    prosesor: string;
    ram_gb: number;
    penyimpanan: string;
    gpu_dedicated: boolean;
    tipe_2in1: boolean;
    berat_kg: string | null;
    deskripsi: string;
    brand_name: string | null;
    category_name: string | null;
    photos: MarketplaceListingPhoto[];
    seller: MarketplaceSeller;
    created_at: string | null;
    published_date: string | null;
}
