<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import EmptyListingsState from '@/components/marketplace/EmptyListingsState.vue';
import ListingCard from '@/components/marketplace/ListingCard.vue';
import MarketplaceFooter from '@/components/marketplace/MarketplaceFooter.vue';
import MarketplaceNavbar from '@/components/marketplace/MarketplaceNavbar.vue';
import type { MarketplaceCategory, PaginatedListings } from '@/types';

interface Props {
    categories: MarketplaceCategory[];
    listings: PaginatedListings;
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
</script>

<template>
    <Head title="Katalog Laptop - KLiK Marketplace Laptop Mahasiswa PNP" />

    <div class="min-h-screen bg-[#060A12] text-[#F7F9FB] antialiased selection:bg-[#00EFA5] selection:text-[#0B101A] flex flex-col justify-between">
        <!-- 1. Header & Navigasi -->
        <MarketplaceNavbar />

        <main class="grow">
            <!-- 2. Breadcrumbs Sub-nav -->
            <div class="border-b border-white/5 bg-[#0B101A]/60 py-3.5 backdrop-blur-md">
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

            <!-- 3. Header Katalog & Overview -->
            <section class="relative overflow-hidden pt-10 pb-8 sm:pt-14 sm:pb-10">
                <!-- Atmospheric Ambient Glows (Mint di Kiri, Amber di Kanan) -->
                <div class="pointer-events-none absolute top-0 left-1/4 w-[500px] h-[250px] bg-[#00EFA5]/[0.035] blur-[140px] rounded-full" />
                <div class="pointer-events-none absolute top-10 right-1/4 w-[500px] h-[250px] bg-[#F18A38]/[0.03] blur-[140px] rounded-full" />

                <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6 border-b border-white/5 pb-8">
                        <div>
                            <div class="inline-flex items-center gap-2 rounded-full border border-[#00EFA5]/30 bg-[#00EFA5]/10 px-3 py-1 text-[11px] font-bold text-[#00EFA5] shadow-[0_0_15px_rgba(0,239,165,0.15)]">
                                <span class="h-1.5 w-1.5 rounded-full bg-[#00EFA5]" />
                                Marketplace Sivitas PNP
                            </div>
                            <h1 class="mt-3 text-3xl font-black tracking-tight text-white sm:text-4xl lg:text-5xl">
                                Semua Produk
                            </h1>
                            <p class="mt-2 text-sm text-[#A4AEBB] max-w-2xl leading-relaxed">
                                Jelajahi seluruh unit laptop bekas yang dipublikasikan oleh mahasiswa dan alumni Politeknik Negeri Padang. Transaksi aman dengan sistem COD tatap muka langsung di lingkungan kampus.
                            </p>
                        </div>

                        <!-- Info Counter Unit Tersedia -->
                        <div class="flex items-center gap-3 shrink-0 rounded-2xl border border-[#1F293D] bg-[#161D2B]/80 px-4 py-3 backdrop-blur-xs">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#00EFA5]/10 text-[#00EFA5]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <span class="block text-[11px] font-medium text-[#A4AEBB]">Total Laptop Aktif</span>
                                <span class="text-base font-extrabold text-white">
                                    {{ listings.total }} Unit
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Area Placeholder Kategori & Filter (Fase 4.3 Container) -->
                    <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-xl border border-[#00EFA5]/40 bg-[#00EFA5]/15 px-3.5 py-1.5 text-xs font-bold text-[#00EFA5]">
                                Semua Kategori
                            </span>
                            <span
                                v-for="cat in categories"
                                :key="cat.id"
                                class="rounded-xl border border-[#1F293D] bg-[#161D2B] px-3.5 py-1.5 text-xs font-medium text-[#A4AEBB] transition"
                            >
                                {{ cat.nama }}
                            </span>
                        </div>

                        <div class="flex items-center gap-2 text-xs text-[#A4AEBB]/70">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-[#F18A38]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Filter interaktif &amp; pencarian kata kunci hadir di Fase 4.3</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 5. Grid Katalog Produk -->
            <section class="py-8 pb-20 sm:pb-28">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <!-- Ada Listing Laptop yang Sah -->
                    <template v-if="listings.data.length > 0">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            <ListingCard
                                v-for="item in listings.data"
                                :key="item.id"
                                :listing="item"
                            />
                        </div>

                        <!-- 6. Server-Side Pagination Navigasi -->
                        <div
                            v-if="listings.last_page > 1"
                            class="mt-12 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-t border-white/5 pt-8"
                        >
                            <p class="text-xs text-[#A4AEBB]">
                                Menampilkan
                                <span class="font-bold text-white">{{ listings.from }}</span>
                                sampai
                                <span class="font-bold text-white">{{ listings.to }}</span>
                                dari
                                <span class="font-bold text-[#00EFA5]">{{ listings.total }}</span>
                                laptop
                            </p>

                            <!-- Pagination Controls -->
                            <nav class="flex flex-wrap items-center gap-1.5">
                                <template v-for="(link, index) in listings.links" :key="index">
                                    <Link
                                        v-if="link.url"
                                        :href="link.url"
                                        class="inline-flex min-h-[36px] min-w-[36px] items-center justify-center rounded-xl px-3 py-1.5 text-xs font-semibold transition"
                                        :class="[
                                            link.active
                                                ? 'bg-[#00EFA5] text-[#0B101A] font-extrabold shadow-[0_0_15px_rgba(0,239,165,0.3)]'
                                                : 'border border-[#1F293D] bg-[#161D2B] text-white/80 hover:border-[#00EFA5]/40 hover:bg-[#1A2436] hover:text-[#00EFA5]',
                                        ]"
                                        v-html="link.label"
                                    />
                                    <span
                                        v-else
                                        class="inline-flex min-h-[36px] min-w-[36px] items-center justify-center rounded-xl border border-transparent px-3 py-1.5 text-xs text-white/30"
                                        v-html="link.label"
                                    />
                                </template>
                            </nav>
                        </div>
                    </template>

                    <!-- Empty State saat Katalog Belum Memiliki Listing Sah -->
                    <EmptyListingsState
                        v-else
                        :is-authenticated="isAuthenticated"
                        title="Belum Ada Laptop yang Ditampilkan"
                        description="Daftar laptop akan muncul di sini setelah penjual mulai mempublikasikan produknya."
                    />
                </div>
            </section>
        </main>

        <!-- 7. Footer -->
        <MarketplaceFooter />
    </div>
</template>

