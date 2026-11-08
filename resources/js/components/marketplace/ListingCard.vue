<script setup lang="ts">
import { computed } from 'vue';
import type { MarketplaceListing } from '@/types';

interface Props {
    listing: MarketplaceListing;
}

const props = defineProps<Props>();

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
 * Metadata label dan warna kondisi laptop
 */
const conditionMeta = computed(() => {
    switch (props.listing.kondisi) {
        case 'mulus':
            return {
                label: 'Mulus',
                badge: 'bg-[#00EFA5]/15 text-[#00EFA5] border-[#00EFA5]/30',
            };
        case 'pemakaian_wajar':
            return {
                label: 'Wajar',
                badge: 'bg-sky-500/15 text-sky-400 border-sky-500/30',
            };
        case 'minus_ringan':
            return {
                label: 'Minus Ringan',
                badge: 'bg-[#F18A38]/15 text-[#F18A38] border-[#F18A38]/30',
            };
        default:
            return {
                label: props.listing.kondisi,
                badge: 'bg-white/10 text-white/80 border-white/10',
            };
    }
});
</script>

<template>
    <div
        class="group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-[#1F293D] bg-[#161D2B] p-5 shadow-lg transition-all duration-300 hover:-translate-y-1.5 hover:border-[#00EFA5]/50 hover:shadow-[0_12px_35px_rgba(0,0,0,0.55)]"
    >
        <div>
            <!-- Thumbnail Gambar Laptop -->
            <div class="relative flex aspect-16/10 w-full items-center justify-center overflow-hidden rounded-xl bg-[#0B101A] border border-[#1F293D]/60 p-2">
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
                        class="h-12 w-12 text-[#1F293D] group-hover:text-[#00EFA5]/50 transition-colors"
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
                    <span class="mt-1 font-mono text-[10px] text-[#A4AEBB]/60">Foto Unit Belum Tersedia</span>
                </div>

                <!-- Badges over Image -->
                <div class="absolute top-2.5 left-2.5 flex flex-wrap gap-1.5">
                    <span
                        v-if="listing.category_name"
                        class="rounded-md border border-[#1F293D] bg-[#0B101A]/90 px-2 py-0.5 text-[10px] font-bold text-[#F7F9FB] backdrop-blur-xs"
                    >
                        {{ listing.category_name }}
                    </span>
                    <span
                        class="rounded-md border px-2 py-0.5 text-[10px] font-bold backdrop-blur-xs"
                        :class="conditionMeta.badge"
                    >
                        {{ conditionMeta.label }}
                    </span>
                </div>

                <!-- Verified Badge on Photo -->
                <div class="absolute right-2.5 bottom-2.5">
                    <span
                        class="inline-flex items-center gap-1 rounded-md border border-[#1F293D] bg-[#0B101A]/90 px-2 py-0.5 text-[10px] font-semibold text-[#00EFA5] backdrop-blur-xs"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Akun Terverifikasi
                    </span>
                </div>
            </div>

            <!-- Brand & Model Title -->
            <div class="mt-4">
                <span class="block text-[11px] font-extrabold uppercase tracking-wider text-[#A4AEBB]">
                    {{ listing.brand_name || 'Laptop' }}
                </span>
                <h3
                    class="mt-0.5 text-base font-bold text-[#F7F9FB] transition-colors group-hover:text-[#00EFA5] line-clamp-1"
                    :title="listing.judul"
                >
                    {{ listing.model || listing.judul }}
                </h3>
            </div>

            <!-- Spesifikasi Berikon yang Rapi & Selaras -->
            <div class="mt-3.5 space-y-1.5 text-xs text-[#A4AEBB]">
                <!-- Processor -->
                <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0 text-[#00EFA5]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="4" y="4" width="16" height="16" rx="2" />
                        <rect x="9" y="9" width="6" height="6" />
                    </svg>
                    <span class="truncate font-medium text-[#F7F9FB]/90">{{ listing.prosesor }}</span>
                </div>

                <!-- RAM & Storage -->
                <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0 text-[#00EFA5]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 19v-3M10 19v-3M14 19v-3M18 19v-3M2 8a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2z" />
                    </svg>
                    <span class="truncate font-medium text-[#F7F9FB]/90">{{ listing.ram_gb }} GB RAM • {{ listing.penyimpanan }}</span>
                </div>

                <!-- COD Kampus PNP -->
                <div class="flex items-center gap-2 text-[11px] text-[#00EFA5]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="truncate font-semibold">COD Area Kampus PNP</span>
                </div>
            </div>
        </div>

        <!-- Footer Card: Harga & Tombol Aksi Merah/Oranye (Sesuai Mockup) -->
        <div class="mt-5 flex items-center justify-between border-t border-[#1F293D] pt-3.5">
            <div>
                <span class="block text-[10px] font-medium text-[#A4AEBB]">Harga Penjual</span>
                <span class="text-base font-black text-[#00EFA5] sm:text-lg">
                    {{ formattedPrice }}
                </span>
            </div>

            <div
                class="flex h-9 w-9 items-center justify-center rounded-full bg-[#E25C38] text-white shadow-md transition-all duration-300 group-hover:scale-110 group-hover:bg-[#f06843] group-hover:shadow-[0_0_15px_rgba(226,92,56,0.4)]"
                title="Detail unit segera hadir pada Fase 4.4"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </div>
        </div>
    </div>
</template>
