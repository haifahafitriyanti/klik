<script setup lang="ts">
import { computed } from 'vue';
import type { MarketplaceCategory } from '@/types';

interface Props {
    category: MarketplaceCategory;
}

const props = defineProps<Props>();

/**
 * Ringkasan spesifikasi visual untuk masing-masing kategori fisik
 */
const meta = computed(() => {
    switch (props.category.prioritas) {
        case 1:
            return {
                label: 'MacBook',
                spec: 'Brand Apple (macOS)',
                badge: 'Apple',
                iconColor: 'text-[#F7F9FB]',
                accentGlow: 'group-hover:shadow-[0_0_20px_rgba(255,255,255,0.15)]',
            };
        case 2:
            return {
                label: 'Laptop Gaming',
                spec: 'Dedicated GPU • RAM ≥ 8GB',
                badge: 'High Perf',
                iconColor: 'text-[#00EFA5]',
                accentGlow: 'group-hover:shadow-[0_0_20px_rgba(0,239,165,0.2)]',
            };
        case 3:
            return {
                label: 'Laptop 2-in-1',
                spec: 'Touchscreen • Engsel 360°',
                badge: 'Convertible',
                iconColor: 'text-[#F18A38]',
                accentGlow: 'group-hover:shadow-[0_0_20px_rgba(241,138,56,0.2)]',
            };
        case 4:
            return {
                label: 'Ultrabook',
                spec: 'Berat ≤ 1,5 kg • SSD',
                badge: 'Slim & Light',
                iconColor: 'text-sky-400',
                accentGlow: 'group-hover:shadow-[0_0_20px_rgba(56,189,248,0.2)]',
            };
        default:
            return {
                label: 'Laptop Standar',
                spec: 'Kebutuhan Harian Kuliah',
                badge: 'Essential',
                iconColor: 'text-[#A4AEBB]',
                accentGlow: 'group-hover:shadow-[0_0_20px_rgba(164,174,187,0.15)]',
            };
    }
});
</script>

<template>
    <a
        href="#listing-terbaru"
        class="group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-[#1F293D] bg-[#161D2B] p-5 transition-all duration-300 hover:-translate-y-1 hover:border-[#00EFA5]/60 hover:bg-[#1A2436]"
        :class="meta.accentGlow"
    >
        <!-- Top Bar: Icon + Mini Tag -->
        <div class="flex items-center justify-between">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#0B101A] border border-[#1F293D] group-hover:border-[#00EFA5]/40 transition-colors">
                <!-- MacBook Icon -->
                <svg
                    v-if="category.prioritas === 1"
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5"
                    :class="meta.iconColor"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="1.75"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>

                <!-- Gaming Icon -->
                <svg
                    v-else-if="category.prioritas === 2"
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5"
                    :class="meta.iconColor"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="1.75"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>

                <!-- 2-in-1 Icon -->
                <svg
                    v-else-if="category.prioritas === 3"
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5"
                    :class="meta.iconColor"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="1.75"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>

                <!-- Ultrabook Icon -->
                <svg
                    v-else-if="category.prioritas === 4"
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5"
                    :class="meta.iconColor"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="1.75"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>

                <!-- Standard Icon -->
                <svg
                    v-else
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5"
                    :class="meta.iconColor"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="1.75"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>

            <span class="rounded-md border border-[#1F293D] bg-[#0B101A] px-2 py-0.5 text-[10px] font-semibold text-[#A4AEBB]">
                {{ meta.badge }}
            </span>
        </div>

        <!-- Title & Subtitle -->
        <div class="mt-4">
            <h4 class="text-sm font-bold text-[#F7F9FB] transition-colors group-hover:text-[#00EFA5]">
                {{ category.nama }}
            </h4>
            <p class="mt-1 text-xs text-[#A4AEBB] line-clamp-1">
                {{ meta.spec }}
            </p>
        </div>
    </a>
</template>
