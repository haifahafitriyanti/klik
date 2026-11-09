<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import EmptyListingsState from '@/components/marketplace/EmptyListingsState.vue';
import ListingCard from '@/components/marketplace/ListingCard.vue';
import MarketplaceFooter from '@/components/marketplace/MarketplaceFooter.vue';
import MarketplaceNavbar from '@/components/marketplace/MarketplaceNavbar.vue';
import type {
    CatalogFilterErrors,
    CatalogFilters,
    MarketplaceBrand,
    MarketplaceCategory,
    PaginatedListings,
} from '@/types';

interface Props {
    categories: MarketplaceCategory[];
    brands: MarketplaceBrand[];
    listings: PaginatedListings;
    filters: CatalogFilters;
    filterErrors?: CatalogFilterErrors | null;
}

const props = defineProps<Props>();

const page = usePage();

interface AuthUser {
    id: number;
    name: string;
    nim?: string;
    role?: string;
    status_verifikasi?: string;
}

const currentUser = computed(() => {
    return (page.props.auth as { user?: AuthUser | null })?.user || null;
});

const isAuthenticated = computed(() => !!currentUser.value);

// State Filter Interaktif Lokal
const searchQuery = ref(props.filters.q ?? '');
const selectedCategory = ref(props.filters.category ?? '');
const selectedSort = ref(props.filters.sort ?? 'newest');

// Sinkronisasi state lokal jika props filter berubah
watch(
    () => props.filters,
    (newFilters) => {
        searchQuery.value = newFilters.q ?? '';
        selectedCategory.value = newFilters.category ?? '';
        selectedSort.value = newFilters.sort ?? 'newest';
    },
    { deep: true },
);

// Cek apakah filter aktif selain default
const hasActiveFilters = computed(() => {
    return Boolean(
        props.filters.q ||
        props.filters.category ||
        props.filters.brand ||
        props.filters.min_price !== null ||
        props.filters.max_price !== null ||
        (props.filters.sort && props.filters.sort !== 'newest')
    );
});

// Terapkan filter ke URL query string
const applyFilters = (overrides: Partial<CatalogFilters> = {}) => {
    const targetQ = overrides.q !== undefined ? overrides.q : (searchQuery.value.trim() || null);
    const targetCategory = overrides.category !== undefined ? overrides.category : (selectedCategory.value || null);
    const targetSort = overrides.sort !== undefined ? overrides.sort : (selectedSort.value || 'newest');

    const params: Record<string, string | number> = {};

    if (targetQ) params.q = targetQ;
    if (targetCategory) params.category = targetCategory;
    if (targetSort && targetSort !== 'newest') params.sort = targetSort;

    // Reset pagination ke page 1 secara natural
    router.get('/katalog', params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

// Handler Submit Search
const handleSearchSubmit = () => {
    applyFilters({ q: searchQuery.value.trim() || null });
};

// Bersihkan Search
const clearSearch = () => {
    searchQuery.value = '';
    applyFilters({ q: null });
};

// Pilih Kategori dari Kapsul Horizontal
const selectCategoryPill = (slug: string | null) => {
    selectedCategory.value = slug || '';
    applyFilters({ category: slug });
};

// Reset Semua Filter
const resetAllFilters = () => {
    searchQuery.value = '';
    selectedCategory.value = '';
    selectedSort.value = 'newest';

    router.get('/katalog', {}, {
        preserveState: false,
        preserveScroll: false,
    });
};
</script>

<template>
    <Head title="Semua Produk - KLiK Marketplace Laptop Mahasiswa PNP" />

    <!--
      Wrapper Halaman dengan Gradasi Estetik Sesuai Permintaan:
      - Hitam di atas (#060A12)
      - Oren di tengah (#F18A38 / #EA580C)
      - Hijau di bawah (#0C2D21 / #1E503C)
      - Gradasi transisi halus menyatu langsung ke warna atas footer (#81E3A1)
    -->
    <div class="relative min-h-screen bg-gradient-to-b from-[#060A12] via-[#221108] via-42% to-[#0A261B] text-[#F7F9FB] antialiased selection:bg-[#00EFA5] selection:text-[#0B101A] flex flex-col justify-between overflow-hidden">
        <!-- 1. Ambient Pendaran Cahaya Oranye Estetik di Bagian Tengah (Middle Orange Glow) -->
        <div class="pointer-events-none absolute top-[40%] left-1/2 -translate-x-1/2 -translate-y-1/2 w-[1000px] max-w-full h-[550px] bg-gradient-to-r from-[#F18A38]/20 via-[#EA580C]/25 to-[#F59E0B]/18 blur-[170px] rounded-full" />
        <div class="pointer-events-none absolute top-[36%] -left-36 w-[550px] h-[450px] bg-[#EA580C]/18 blur-[160px] rounded-full" />
        <div class="pointer-events-none absolute top-[44%] -right-36 w-[550px] h-[450px] bg-[#F18A38]/18 blur-[160px] rounded-full" />

        <!-- 2. Ambient Pendaran Cahaya Hijau Mint / Emerald di Bagian Bawah (Bottom Green Glow) -->
        <div class="pointer-events-none absolute bottom-24 left-1/2 -translate-x-1/2 w-[1100px] max-w-full h-[520px] bg-gradient-to-t from-[#00EFA5]/25 via-[#3E8D6E]/30 to-transparent blur-[160px] rounded-full" />
        <div class="pointer-events-none absolute bottom-36 -left-20 w-[550px] h-[420px] bg-[#00EFA5]/15 blur-[150px] rounded-full" />
        <div class="pointer-events-none absolute bottom-32 -right-20 w-[550px] h-[420px] bg-[#3E8D6E]/25 blur-[150px] rounded-full" />

        <!-- 1. Header MarketplaceNavbar (Sesuai Screenshot User: Logo KLiK PNP, Katalog, Kategori, Sewa Lapak, FAQ, About Us, Dashboard, Keluar) -->
        <MarketplaceNavbar />

        <!-- 2. Breadcrumbs Sub-nav (Sesuai Screenshot: Beranda / Katalog Laptop) -->
        <div class="relative z-20 border-b border-white/5 bg-[#060A12]/80 py-3.5 backdrop-blur-md">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <nav class="flex items-center gap-2 text-xs font-medium text-[#A4AEBB]">
                    <Link href="/" class="transition hover:text-[#00EFA5]">
                        Beranda
                    </Link>
                    <span class="text-white/30">/</span>
                    <span class="text-[#00EFA5] font-semibold">Katalog Laptop</span>
                </nav>
            </div>
        </div>

        <!-- 3. Konten Utama Katalog -->
        <main class="relative z-10 grow px-4 py-8 sm:py-12 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <!-- Header Banner (Sesuai Screenshot User: Badge Marketplace Sivitas PNP + Judul + Deskripsi + Counter Box) -->
                <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6 border-b border-white/5 pb-8">
                    <div>
                        <!-- Badge "• Marketplace Sivitas PNP" -->
                        <div class="inline-flex items-center gap-2 rounded-full border border-[#00EFA5]/30 bg-[#00EFA5]/10 px-3 py-1 text-[11px] font-bold text-[#00EFA5] shadow-[0_0_15px_rgba(0,239,165,0.15)]">
                            <span class="h-1.5 w-1.5 rounded-full bg-[#00EFA5]" />
                            Marketplace Sivitas PNP
                        </div>

                        <!-- Judul "Semua Produk" -->
                        <h1 class="mt-3 text-3xl font-black tracking-tight text-white sm:text-4xl lg:text-5xl">
                            Semua Produk
                        </h1>

                        <!-- Deskripsi -->
                        <p class="mt-2 text-sm text-[#A4AEBB] max-w-2xl leading-relaxed">
                            Jelajahi seluruh unit laptop bekas yang dipublikasikan oleh mahasiswa dan alumni Politeknik Negeri Padang. Transaksi aman dengan sistem COD tatap muka langsung di lingkungan kampus.
                        </p>
                    </div>

                    <!-- Info Counter "Total Laptop Ditemukan X Unit" (Sesuai Screenshot User) -->
                    <div class="flex items-center gap-3 shrink-0 rounded-2xl border border-white/10 bg-[#0E1522]/80 px-4 py-3 backdrop-blur-md">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#00EFA5]/10 text-[#00EFA5]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <span class="block text-[11px] font-medium text-[#A4AEBB]">Total Laptop Ditemukan</span>
                            <span class="text-base font-extrabold text-white">
                                {{ listings.total }} Unit
                            </span>
                        </div>
                    </div>
                </div>

                <!-- 4. Search Bar & Kapsul Kategori Horizontal (Sesuai All Products.png) -->
                <div class="mt-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <!-- Search Input Bar -->
                    <div class="w-full md:max-w-md">
                        <form @submit.prevent="handleSearchSubmit" class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-[#A4AEBB]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input
                                v-model="searchQuery"
                                type="text"
                                maxlength="100"
                                placeholder="Cari merek atau tipe laptop..."
                                class="w-full rounded-xl border border-white/10 bg-[#0E1522]/90 py-2.5 pl-10 pr-9 text-xs sm:text-sm text-white placeholder-[#A4AEBB]/60 backdrop-blur-md transition-all focus:border-[#00EFA5] focus:bg-[#131D2E] focus:outline-none focus:ring-1 focus:ring-[#00EFA5]"
                            />
                            <button
                                v-if="searchQuery"
                                type="button"
                                @click="clearSearch"
                                class="absolute inset-y-0 right-2.5 flex items-center text-[#A4AEBB] hover:text-white transition"
                                title="Hapus pencarian"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </form>
                    </div>

                    <!-- Dropdown Urutkan -->
                    <div class="flex items-center gap-2 self-end md:self-auto">
                        <label for="catalog-sort" class="text-xs text-[#A4AEBB] shrink-0">Urutkan:</label>
                        <select
                            id="catalog-sort"
                            v-model="selectedSort"
                            @change="applyFilters()"
                            class="rounded-xl border border-white/10 bg-[#0E1522]/90 px-3.5 py-1.5 text-xs font-semibold text-white transition focus:border-[#00EFA5] focus:outline-none"
                        >
                            <option value="newest">Terbaru</option>
                            <option value="price_asc">Harga Termurah</option>
                            <option value="price_desc">Harga Termahal</option>
                        </select>
                    </div>
                </div>

                <!-- 5. Kapsul Filter Kategori Horizontal (Sesuai All Products.png) -->
                <div class="mt-4 flex flex-wrap items-center gap-2 pb-6 border-b border-white/5">
                    <!-- Kapsul "Semua" -->
                    <button
                        type="button"
                        @click="selectCategoryPill(null)"
                        class="rounded-full px-5 py-2 text-xs font-bold transition-all duration-200"
                        :class="!selectedCategory
                            ? 'bg-[#00EFA5] text-[#0B101A] shadow-[0_0_15px_rgba(0,239,165,0.35)]'
                            : 'border border-white/10 bg-[#0E1522]/80 text-white/80 hover:border-[#00EFA5]/40 hover:text-white'"
                    >
                        Semua
                    </button>

                    <!-- Kapsul 5 Kategori Resmi KLiK -->
                    <button
                        v-for="cat in categories"
                        :key="cat.id"
                        type="button"
                        @click="selectCategoryPill(cat.slug || String(cat.id))"
                        class="rounded-full px-5 py-2 text-xs font-semibold transition-all duration-200"
                        :class="selectedCategory === cat.slug || selectedCategory === cat.full_slug || selectedCategory === String(cat.id)
                            ? 'bg-[#00EFA5] text-[#0B101A] font-bold shadow-[0_0_15px_rgba(0,239,165,0.35)]'
                            : 'border border-white/10 bg-[#0E1522]/80 text-white/80 hover:border-[#00EFA5]/40 hover:text-white'"
                    >
                        {{ cat.nama }}
                    </button>

                    <!-- Reset Filter jika Aktif -->
                    <button
                        v-if="hasActiveFilters"
                        type="button"
                        @click="resetAllFilters"
                        class="ml-auto text-xs text-[#F18A38] hover:text-[#FFA048] underline transition"
                    >
                        Reset Filter
                    </button>
                </div>

                <!-- 6. Grid Produk Laptop 4 Kolom Penuh (Sesuai All Products.png) -->
                <div class="mt-8">
                    <!-- Kondisi A: Ada Listing -->
                    <template v-if="listings.data.length > 0">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            <ListingCard
                                v-for="item in listings.data"
                                :key="item.id"
                                :listing="item"
                            />
                        </div>

                        <!-- 7. Pagination Navigasi (Sesuai All Products.png) -->
                        <div
                            v-if="listings.last_page > 1"
                            class="mt-14 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-t border-white/5 pt-8"
                        >
                            <!-- Info Jumlah Range Halaman -->
                            <p class="text-xs text-[#A4AEBB]">
                                Menampilkan
                                <span class="font-bold text-white">{{ listings.from }}</span>
                                -
                                <span class="font-bold text-white">{{ listings.to }}</span>
                                dari
                                <span class="font-bold text-[#00EFA5]">{{ listings.total }}</span>
                                laptop
                            </p>

                            <!-- Pagination Controls -->
                            <nav class="flex flex-wrap items-center gap-2">
                                <template v-for="(link, index) in listings.links" :key="index">
                                    <Link
                                        v-if="link.url"
                                        :href="link.url"
                                        class="flex h-8 min-w-8 items-center justify-center rounded-lg px-2.5 text-xs font-semibold transition"
                                        :class="link.active
                                            ? 'bg-[#00EFA5] text-[#0B101A] font-bold shadow-[0_0_12px_rgba(0,239,165,0.3)]'
                                            : 'border border-white/10 bg-[#0E1522] text-[#A4AEBB] hover:border-[#00EFA5]/40 hover:text-white'"
                                        v-html="link.label"
                                    />
                                    <span
                                        v-else
                                        class="flex h-8 min-w-8 items-center justify-center rounded-lg border border-white/5 bg-[#0E1522]/30 px-2.5 text-xs font-medium text-[#A4AEBB]/30 cursor-not-allowed"
                                        v-html="link.label"
                                    />
                                </template>
                            </nav>
                        </div>
                    </template>

                    <!-- Kondisi B: Kosong -->
                    <template v-else>
                        <div v-if="hasActiveFilters" class="space-y-4">
                            <EmptyListingsState
                                :is-authenticated="isAuthenticated"
                                title="Tidak Ada Laptop yang Sesuai"
                                description="Coba gunakan kata kunci lain atau sesuaikan pilihan filter."
                            />
                            <div class="text-center">
                                <button
                                    type="button"
                                    @click="resetAllFilters"
                                    class="inline-flex items-center gap-2 rounded-xl bg-[#00EFA5] px-5 py-2.5 text-xs font-bold text-[#0B101A] shadow-[0_0_15px_rgba(0,239,165,0.25)] transition hover:bg-[#00d694]"
                                >
                                    Reset Semua Filter
                                </button>
                            </div>
                        </div>
                        <EmptyListingsState
                            v-else
                            :is-authenticated="isAuthenticated"
                            title="Belum Ada Laptop yang Ditampilkan"
                            description="Daftar laptop akan muncul di sini setelah penjual mulai mempublikasikan produknya."
                        />
                    </template>
                </div>
            </div>
        </main>

        <!-- Gradasi Transisi Estetik Menyatu ke Warna Atas Footer (#81E3A1) -->
        <div
            class="pointer-events-none relative z-10 h-28 -mb-px w-full bg-gradient-to-b from-transparent via-[#3E8D6E]/40 to-[#81E3A1]"
            aria-hidden="true"
        />

        <!-- 8. Footer Marketplace -->
        <MarketplaceFooter />
    </div>
</template>
