<script setup lang="ts">
import { computed, ref } from 'vue';
import type { MarketplaceListing } from '@/types';

interface Props {
    listing: MarketplaceListing;
}

const props = defineProps<Props>();

const isLiked = ref(false);

const toggleLike = () => {
    isLiked.value = !isLiked.value;
};

/**
 * Format harga ke format mata uang Rupiah Indonesia
 */
const formattedPrice = computed(() => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(props.listing.harga);
});

/**
 * Label display untuk kondisi unit
 */
const conditionLabel = computed(() => {
    switch (props.listing.kondisi) {
        case 'mulus':
            return 'Bekas • Mulus';
        case 'pemakaian_wajar':
            return 'Bekas • Wajar';
        case 'minus_ringan':
            return 'Bekas • Minus Ringan';
        default:
            return 'Bekas';
    }
});
</script>

<template>
    <div
        class="group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-white/10 bg-[#0F1420]/90 p-4 shadow-lg backdrop-blur-xl transition-all duration-300 hover:-translate-y-1.5 hover:border-[#00EFA5]/50 hover:shadow-[0_12px_35px_rgba(0,0,0,0.55)]"
    >
        <div>
            <!-- Thumbnail Gambar Laptop (Sesuai Mockup All Products.png) -->
            <div class="relative flex aspect-16/10 w-full items-center justify-center overflow-hidden rounded-xl bg-gradient-to-b from-[#1E2638] to-[#0E131F] p-3 border border-white/5">
                <img
                    v-if="listing.photo_url"
                    :src="listing.photo_url"
                    :alt="listing.judul"
                    class="h-full w-full object-contain transition-transform duration-500 group-hover:scale-105"
                    loading="lazy"
                />
                <div
                    v-else
                    class="flex h-full w-full flex-col items-center justify-center text-[#A4AEBB]/40"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-10 w-10 text-white/20 group-hover:text-[#00EFA5]/50 transition-colors"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="1.25"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                        />
                    </svg>
                    <span class="mt-1 font-mono text-[10px] text-[#A4AEBB]/60">Foto Unit</span>
                </div>

                <!-- Badge "Bekas" Top Left (Sesuai All Products.png) -->
                <div class="absolute top-2.5 left-2.5">
                    <span class="rounded-md bg-black/70 px-2.5 py-1 text-[11px] font-semibold text-white backdrop-blur-md border border-white/10">
                        {{ conditionLabel }}
                    </span>
                </div>

                <!-- Heart Wishlist Icon Top Right (Sesuai All Products.png) -->
                <button
                    type="button"
                    @click.stop="toggleLike"
                    class="absolute top-2.5 right-2.5 flex h-7 w-7 items-center justify-center rounded-full bg-black/60 text-white/80 transition hover:bg-black hover:text-rose-400 backdrop-blur-md"
                    title="Simpan ke favorit"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 transition-colors"
                        :class="isLiked ? 'fill-rose-500 text-rose-500' : 'fill-none stroke-current'"
                        viewBox="0 0 24 24"
                        stroke-width="2"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </button>
            </div>

            <!-- Card Body: Judul & Spesifikasi (Sesuai All Products.png) -->
            <div class="mt-4">
                <h3
                    class="text-sm sm:text-base font-bold text-white transition-colors group-hover:text-[#00EFA5] line-clamp-1"
                    :title="listing.judul"
                >
                    {{ listing.judul }}
                </h3>

                <!-- Specs Line 1: Prosesor -->
                <p class="mt-1 text-xs text-[#94A3B8] truncate">
                    {{ listing.prosesor }}
                </p>

                <!-- Specs Line 2: RAM & Penyimpanan -->
                <p class="mt-0.5 text-xs text-[#94A3B8] truncate">
                    {{ listing.ram_gb }}GB RAM, {{ listing.penyimpanan }}
                </p>

                <!-- Price Highlight -->
                <div class="mt-2.5">
                    <span class="text-base sm:text-lg font-black text-[#00EFA5] tracking-tight">
                        {{ formattedPrice }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Card Footer: Lokasi COD & Tombol Lihat Detail (Sesuai All Products.png) -->
        <div class="mt-4 flex items-center justify-between border-t border-white/5 pt-3">
            <!-- Lokasi Pin -->
            <div class="flex items-center gap-1 text-xs text-[#94A3B8]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-[#A4AEBB]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="truncate">Area Kampus PNP</span>
            </div>

            <!-- Tombol Lihat Detail -->
            <button
                type="button"
                class="rounded-lg border border-[#00EFA5]/60 bg-transparent px-3 py-1.5 text-xs font-semibold text-[#00EFA5] transition-all hover:bg-[#00EFA5]/15 hover:border-[#00EFA5]"
            >
                Lihat Detail
            </button>
        </div>
    </div>
</template>
