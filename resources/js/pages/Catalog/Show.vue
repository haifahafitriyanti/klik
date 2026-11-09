<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import MarketplaceFooter from '@/components/marketplace/MarketplaceFooter.vue';
import MarketplaceNavbar from '@/components/marketplace/MarketplaceNavbar.vue';
import type { MarketplaceListingDetail } from '@/types';

interface Props {
    listing: MarketplaceListingDetail;
}

const props = defineProps<Props>();

// State Galeri Foto Aktif (Index Foto)
const activePhotoIndex = ref(0);

// Foto aktif yang sedang ditampilkan
const currentPhotoUrl = computed(() => {
    if (props.listing.photos && props.listing.photos.length > 0) {
        const safeIndex = Math.min(activePhotoIndex.value, props.listing.photos.length - 1);
        return props.listing.photos[safeIndex]?.url || null;
    }
    return null;
});

// Format Harga Rupiah
const formattedPrice = computed(() => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(props.listing.harga);
});

// Format Label Kondisi
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

// Inisial Penjual untuk Avatar
const sellerInitials = computed(() => {
    if (!props.listing.seller?.name) return 'PNP';
    const parts = props.listing.seller.name.trim().split(/\s+/);
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return props.listing.seller.name.slice(0, 2).toUpperCase();
});

// Label Status Mahasiswa Penjual
const sellerStatusLabel = computed(() => {
    return props.listing.seller?.status_mahasiswa === 'alumni'
        ? 'Alumni Politeknik Negeri Padang'
        : 'Mahasiswa Aktif Politeknik Negeri Padang';
});
</script>

<template>
    <Head :title="`${listing.judul} - KLiK Marketplace Laptop Mahasiswa PNP`" />

    <!--
      Wrapper Halaman Detail dengan Estetika KLiK:
      - Dark navy (#060A12) sebagai dasar
      - Ambient warm orange glow di bagian tengah
      - Bottom mint glow yang menyatu langsung ke footer (#81E3A1)
    -->
    <div class="relative min-h-screen bg-gradient-to-b from-[#060A12] via-[#16120E] via-35% to-[#082217] text-[#F7F9FB] antialiased selection:bg-[#00EFA5] selection:text-[#0B101A] flex flex-col justify-between overflow-hidden">
        <!-- Ambient Glowing Effects -->
        <div class="pointer-events-none absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[900px] max-w-full h-[500px] bg-gradient-to-r from-[#F18A38]/15 via-[#EA580C]/18 to-[#F59E0B]/12 blur-[170px] rounded-full" />
        <div class="pointer-events-none absolute bottom-32 left-1/2 -translate-x-1/2 w-[1000px] max-w-full h-[500px] bg-gradient-to-t from-[#00EFA5]/20 via-[#3E8D6E]/25 to-transparent blur-[160px] rounded-full" />

        <!-- 1. Header MarketplaceNavbar (Navigasi Publik KLiK) -->
        <MarketplaceNavbar />

        <!-- 2. Breadcrumbs Navigasi (Beranda / Katalog Laptop / Detail) -->
        <div class="relative z-20 border-b border-white/5 bg-[#060A12]/80 py-3.5 backdrop-blur-md">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <nav class="flex items-center gap-2 text-xs font-medium text-[#A4AEBB]" aria-label="Breadcrumb">
                    <Link href="/" class="transition hover:text-[#00EFA5]">
                        Beranda
                    </Link>
                    <span class="text-white/30">/</span>
                    <Link href="/katalog" class="transition hover:text-[#00EFA5]">
                        Katalog Laptop
                    </Link>
                    <span class="text-white/30">/</span>
                    <span class="text-[#00EFA5] font-semibold truncate max-w-[200px] sm:max-w-md">
                        {{ listing.judul }}
                    </span>
                </nav>
            </div>
        </div>

        <!-- 3. Konten Utama Halaman Detail -->
        <main class="relative z-10 grow px-4 py-8 sm:py-12 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <!-- Tombol Kembali & Info Singkat Bar -->
                <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
                    <Link
                        href="/katalog"
                        class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-[#0E1522]/80 px-4 py-2 text-xs font-semibold text-[#A4AEBB] backdrop-blur-md transition hover:border-[#00EFA5]/50 hover:text-white"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali ke Katalog
                    </Link>

                    <div class="flex items-center gap-2">
                        <!-- Badge Kategori -->
                        <span
                            v-if="listing.category_name"
                            class="rounded-full border border-white/10 bg-[#0E1522]/90 px-3 py-1 text-xs font-semibold text-[#A4AEBB]"
                        >
                            {{ listing.category_name }}
                        </span>

                        <!-- Badge Kondisi -->
                        <span class="rounded-full border border-[#00EFA5]/30 bg-[#00EFA5]/10 px-3 py-1 text-xs font-bold text-[#00EFA5]">
                            {{ conditionLabel }}
                        </span>
                    </div>
                </div>

                <!-- Grid Utama Detail Produk: Galeri Foto (Kiri) & Info Produk (Kanan) -->
                <div class="grid grid-cols-1 gap-10 lg:grid-cols-12 lg:gap-12">
                    <!-- KOLOM KIRI (lg:col-span-7): Galeri Foto & Deskripsi Lengkap -->
                    <div class="space-y-8 lg:col-span-7">
                        <!-- Foto Utama Produk (Hero Image) -->
                        <div class="relative flex aspect-16/10 w-full items-center justify-center overflow-hidden rounded-3xl border border-white/10 bg-gradient-to-b from-[#182133] to-[#0A0F1A] p-4 shadow-2xl backdrop-blur-xl">
                            <!-- Foto Utama -->
                            <img
                                v-if="currentPhotoUrl"
                                :src="currentPhotoUrl"
                                :alt="listing.judul"
                                class="h-full w-full object-contain transition-all duration-300"
                            />
                            <!-- Placeholder Jika Tidak Ada Foto -->
                            <div
                                v-else
                                class="flex h-full w-full flex-col items-center justify-center text-[#A4AEBB]/40 p-8 text-center"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-16 w-16 text-white/20 mb-3"
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
                                <p class="text-sm font-medium text-white/70">Foto Unit Belum Diunggah</p>
                                <p class="text-xs text-[#A4AEBB]/60 mt-1">Penjual belum melampirkan foto untuk laptop ini.</p>
                            </div>

                            <!-- Badge Kondisi di Pojok Foto Utama -->
                            <div class="absolute top-4 left-4">
                                <span class="rounded-lg bg-black/75 px-3 py-1.5 text-xs font-semibold text-white backdrop-blur-md border border-white/10 shadow-md">
                                    {{ conditionLabel }}
                                </span>
                            </div>

                            <!-- Indikator Jumlah Foto -->
                            <div
                                v-if="listing.photos && listing.photos.length > 0"
                                class="absolute bottom-4 right-4 rounded-lg bg-black/75 px-3 py-1 text-xs font-semibold text-white backdrop-blur-md border border-white/10"
                            >
                                {{ activePhotoIndex + 1 }} / {{ listing.photos.length }}
                            </div>
                        </div>

                        <!-- Thumbnails Galeri (Jika Foto Lebih Dari 1) -->
                        <div
                            v-if="listing.photos && listing.photos.length > 1"
                            class="flex flex-wrap items-center gap-3"
                        >
                            <button
                                v-for="(photo, idx) in listing.photos"
                                :key="photo.id"
                                type="button"
                                @click="activePhotoIndex = idx"
                                class="relative h-16 w-24 shrink-0 overflow-hidden rounded-xl border bg-[#0E1522] p-1 transition-all duration-200 focus:outline-none"
                                :class="activePhotoIndex === idx
                                    ? 'border-[#00EFA5] ring-2 ring-[#00EFA5]/60 shadow-[0_0_15px_rgba(0,239,165,0.3)] scale-105'
                                    : 'border-white/10 hover:border-white/30 opacity-70 hover:opacity-100'"
                                :aria-label="`Pilih foto ${idx + 1}`"
                                :aria-current="activePhotoIndex === idx"
                            >
                                <img
                                    :src="photo.url"
                                    :alt="`${listing.judul} - foto ${idx + 1}`"
                                    class="h-full w-full object-cover rounded-lg"
                                />
                            </button>
                        </div>

                        <!-- Deskripsi Produk Dari Penjual -->
                        <div class="rounded-3xl border border-white/10 bg-[#0E1522]/90 p-6 sm:p-8 backdrop-blur-xl shadow-lg">
                            <h2 class="flex items-center gap-2.5 text-base sm:text-lg font-bold text-white mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#00EFA5]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7" />
                                </svg>
                                Deskripsi &amp; Catatan Penjual
                            </h2>
                            <div class="text-sm text-[#A4AEBB] leading-relaxed whitespace-pre-line font-normal">
                                {{ listing.deskripsi }}
                            </div>
                        </div>

                        <!-- Panduan Keselamatan Transaksi COD Kampus (FR-41 PRD) -->
                        <div class="rounded-3xl border border-emerald-500/20 bg-[#0A1A14]/85 p-6 sm:p-7 backdrop-blur-xl shadow-lg">
                            <div class="flex items-start gap-3.5">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#00EFA5]/15 text-[#00EFA5] border border-[#00EFA5]/30">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </div>
                                <div class="space-y-2">
                                    <h3 class="text-sm font-bold text-white">
                                        Panduan Keamanan Transaksi Kampus (COD Tatap Muka)
                                    </h3>
                                    <ul class="text-xs text-[#A4AEBB] space-y-1.5 leading-relaxed">
                                        <li class="flex items-start gap-2">
                                            <span class="text-[#00EFA5] font-bold">&bull;</span>
                                            <span><strong>Lokasi Kampus:</strong> Lakukan transaksi COD tatap muka di area publik kampus Politeknik Negeri Padang (seperti Koridor Jurusan TI, Kantin PKM, Gazebo, atau Perpustakaan).</span>
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <span class="text-[#00EFA5] font-bold">&bull;</span>
                                            <span><strong>Pemeriksaan Unit:</strong> Uji fungsi laptop secara teliti sebelum membayar (layar, keyboard, engsel, port, adapter pengisi daya, dan spesifikasi).</span>
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <span class="text-[#00EFA5] font-bold">&bull;</span>
                                            <span><strong>Tanpa Escrow Online:</strong> KLiK tidak menyediakan perantara pembayaran online; pembayaran dilakukan langsung antara pembeli dan penjual di tempat setelah kesepakatan tercapai.</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KOLOM KANAN (lg:col-span-5): Judul, Harga, Spesifikasi, & Panel Penjual -->
                    <div class="space-y-6 lg:col-span-5">
                        <!-- Card Header Judul & Harga -->
                        <div class="rounded-3xl border border-white/10 bg-[#0E1522]/90 p-6 sm:p-8 backdrop-blur-xl shadow-lg">
                            <div class="flex items-center gap-2 text-xs font-semibold text-[#A4AEBB]">
                                <span v-if="listing.brand_name" class="text-white">{{ listing.brand_name }}</span>
                                <span v-if="listing.brand_name && listing.model">&bull;</span>
                                <span v-if="listing.model">{{ listing.model }}</span>
                            </div>

                            <h1 class="mt-2 text-2xl sm:text-3xl font-black tracking-tight text-white leading-tight">
                                {{ listing.judul }}
                            </h1>

                            <!-- Harga Produk -->
                            <div class="mt-4 border-t border-white/5 pt-4">
                                <span class="block text-xs font-medium text-[#A4AEBB]">Harga Penawaran</span>
                                <span class="text-3xl sm:text-4xl font-black text-[#00EFA5] tracking-tight">
                                    {{ formattedPrice }}
                                </span>
                            </div>

                            <!-- Waktu Publikasi -->
                            <div class="mt-3 flex items-center gap-1.5 text-xs text-[#A4AEBB]/70">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Dipublikasikan {{ listing.created_at || 'Baru saja' }}</span>
                            </div>
                        </div>

                        <!-- Card Spesifikasi Lengkap Produk -->
                        <div class="rounded-3xl border border-white/10 bg-[#0E1522]/90 p-6 sm:p-8 backdrop-blur-xl shadow-lg">
                            <h2 class="text-base font-bold text-white mb-5 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#00EFA5]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                                </svg>
                                Spesifikasi Teknis
                            </h2>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                                <!-- Merek -->
                                <div class="rounded-2xl border border-white/5 bg-white/5 p-3.5">
                                    <span class="block text-[11px] font-medium text-[#A4AEBB]">Merek</span>
                                    <span class="mt-0.5 block text-sm font-bold text-white">{{ listing.brand_name || '-' }}</span>
                                </div>

                                <!-- Model -->
                                <div class="rounded-2xl border border-white/5 bg-white/5 p-3.5">
                                    <span class="block text-[11px] font-medium text-[#A4AEBB]">Model / Tipe</span>
                                    <span class="mt-0.5 block text-sm font-bold text-white">{{ listing.model || '-' }}</span>
                                </div>

                                <!-- Kategori -->
                                <div class="rounded-2xl border border-white/5 bg-white/5 p-3.5">
                                    <span class="block text-[11px] font-medium text-[#A4AEBB]">Kategori Fisik</span>
                                    <span class="mt-0.5 block text-sm font-bold text-white">{{ listing.category_name || '-' }}</span>
                                </div>

                                <!-- Kondisi -->
                                <div class="rounded-2xl border border-white/5 bg-white/5 p-3.5">
                                    <span class="block text-[11px] font-medium text-[#A4AEBB]">Kondisi Fisik</span>
                                    <span class="mt-0.5 block text-sm font-bold text-white">{{ conditionLabel }}</span>
                                </div>

                                <!-- Prosesor -->
                                <div class="rounded-2xl border border-white/5 bg-white/5 p-3.5 sm:col-span-2">
                                    <span class="block text-[11px] font-medium text-[#A4AEBB]">Prosesor (CPU)</span>
                                    <span class="mt-0.5 block text-sm font-bold text-white">{{ listing.prosesor }}</span>
                                </div>

                                <!-- RAM -->
                                <div class="rounded-2xl border border-white/5 bg-white/5 p-3.5">
                                    <span class="block text-[11px] font-medium text-[#A4AEBB]">Memori RAM</span>
                                    <span class="mt-0.5 block text-sm font-bold text-[#00EFA5]">{{ listing.ram_gb }} GB</span>
                                </div>

                                <!-- Penyimpanan -->
                                <div class="rounded-2xl border border-white/5 bg-white/5 p-3.5">
                                    <span class="block text-[11px] font-medium text-[#A4AEBB]">Penyimpanan (Storage)</span>
                                    <span class="mt-0.5 block text-sm font-bold text-white">{{ listing.penyimpanan }}</span>
                                </div>

                                <!-- GPU Dedicated -->
                                <div class="rounded-2xl border border-white/5 bg-white/5 p-3.5">
                                    <span class="block text-[11px] font-medium text-[#A4AEBB]">Kartu Grafis (GPU)</span>
                                    <span class="mt-0.5 block text-sm font-bold text-white">
                                        {{ listing.gpu_dedicated ? 'Dedicated GPU' : 'Integrated Graphics' }}
                                    </span>
                                </div>

                                <!-- Tipe 2-in-1 -->
                                <div class="rounded-2xl border border-white/5 bg-white/5 p-3.5">
                                    <span class="block text-[11px] font-medium text-[#A4AEBB]">Fleksibilitas Bodi</span>
                                    <span class="mt-0.5 block text-sm font-bold text-white">
                                        {{ listing.tipe_2in1 ? '2-in-1 (Layar Sentuh)' : 'Laptop Standar' }}
                                    </span>
                                </div>

                                <!-- Bobot / Berat -->
                                <div v-if="listing.berat_kg" class="rounded-2xl border border-white/5 bg-white/5 p-3.5 sm:col-span-2">
                                    <span class="block text-[11px] font-medium text-[#A4AEBB]">Bobot Unit</span>
                                    <span class="mt-0.5 block text-sm font-bold text-white">{{ listing.berat_kg }} kg</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Informasi Publik Penjual (Sesuai Aturan Privasi PRD) -->
                        <div class="rounded-3xl border border-white/10 bg-[#0E1522]/90 p-6 sm:p-7 backdrop-blur-xl shadow-lg">
                            <h2 class="text-base font-bold text-white mb-4 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#00EFA5]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Informasi Penjual
                            </h2>

                            <div class="flex items-center gap-3.5">
                                <!-- Avatar Inisial -->
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[#00EFA5]/15 text-sm font-extrabold text-[#00EFA5] border border-[#00EFA5]/40 shadow-[0_0_12px_rgba(0,239,165,0.15)]">
                                    {{ sellerInitials }}
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-sm sm:text-base font-bold text-white truncate">
                                        {{ listing.seller.name }}
                                    </h3>
                                    <p class="text-xs text-[#A4AEBB] truncate">
                                        {{ sellerStatusLabel }}
                                    </p>
                                </div>
                            </div>

                            <!-- Badge Terverifikasi KLiK -->
                            <div class="mt-4 rounded-2xl border border-[#00EFA5]/25 bg-[#00EFA5]/10 p-3.5">
                                <div class="flex items-center gap-2 text-xs font-bold text-[#00EFA5]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Terverifikasi Sivitas PNP
                                </div>
                                <p class="mt-1 text-[11px] text-[#A4AEBB] leading-relaxed">
                                    Identitas akun penjual telah diverifikasi oleh sistem KLiK melalui kartu tanda mahasiswa (KTM) Politeknik Negeri Padang.
                                </p>
                            </div>
                        </div>

                        <!-- Card Tombol Kontak Penjual (Batasan Fase 4.4 PRD) -->
                        <div class="rounded-3xl border border-orange-500/20 bg-[#0E1522]/90 p-6 sm:p-7 backdrop-blur-xl shadow-lg">
                            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#F18A38]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                Kontak Penjual
                            </h3>

                            <p class="mt-2 text-xs text-[#A4AEBB] leading-relaxed">
                                Fitur integrasi kontak WhatsApp langsung akan aktif pada tahap pengembangan berikutnya. Kontak penjual tersimpan aman di database dan dilindungi dari akses publik.
                            </p>

                            <div class="mt-4">
                                <button
                                    type="button"
                                    disabled
                                    class="w-full flex items-center justify-center gap-2 rounded-xl border border-white/10 bg-white/5 py-3 px-4 text-xs font-semibold text-[#A4AEBB] cursor-not-allowed"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    Kontak WhatsApp (Segera Hadir)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Gradasi Transisi Estetik Menyatu ke Warna Atas Footer (#81E3A1) -->
        <div
            class="pointer-events-none relative z-10 h-28 -mb-px w-full bg-gradient-to-b from-transparent via-[#3E8D6E]/40 to-[#81E3A1]"
            aria-hidden="true"
        />

        <!-- 4. Footer Marketplace -->
        <MarketplaceFooter />
    </div>
</template>

