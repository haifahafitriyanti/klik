export interface MarketplaceCategory {
    id: number;
    nama: string;
    prioritas: number;
    kriteria: string | null;
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

