<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import CategoryCard from '@/components/marketplace/CategoryCard.vue';
import EmptyListingsState from '@/components/marketplace/EmptyListingsState.vue';
import ListingCard from '@/components/marketplace/ListingCard.vue';
import MarketplaceFooter from '@/components/marketplace/MarketplaceFooter.vue';
import MarketplaceNavbar from '@/components/marketplace/MarketplaceNavbar.vue';
import { dashboard, login, register } from '@/routes';
import { dashboard as adminDashboard } from '@/routes/admin';
import { status as verificationStatus } from '@/routes/verification';
import { index as catalogRoute } from '@/routes/catalog';
import type { MarketplaceCategory, MarketplaceListing } from '@/types';

interface Props {
    categories: MarketplaceCategory[];
    latestListings: MarketplaceListing[];
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
const isAdmin = computed(() => currentUser.value?.role === 'admin');
const isApprovedUser = computed(() => currentUser.value?.role === 'user' && currentUser.value?.status_verifikasi === 'disetujui');
const isPendingUser = computed(() => currentUser.value?.status_verifikasi === 'menunggu');
const isRejectedUser = computed(() => currentUser.value?.status_verifikasi === 'ditolak');

/**
 * FAQ Accordion State (5 Pertanyaan Resmi KLiK Fase 4.1)
 */
const openFaq = ref<number | null>(0); // Item 0 terbuka secara default

const toggleFaq = (index: number) => {
    openFaq.value = openFaq.value === index ? null : index;
};

const faqs = [
    {
        q: 'Bagaimana cara membeli laptop di KLiK?',
        a: 'Pilih unit laptop di katalog, masuk menggunakan NIM akun mahasiswa Anda untuk melihat kontak WhatsApp penjual, lalu hubungi penjual untuk membuat janji temu COD langsung di area kampus PNP.',
    },
    {
        q: 'Mengapa pendaftaran akun wajib diverifikasi dengan KTM?',
        a: 'Verifikasi manual foto KTM oleh admin bertujuan memastikan bahwa seluruh pengguna platform adalah sivitas akademika Politeknik Negeri Padang (mahasiswa aktif / alumni) guna meminimalkan risiko penipuan pihak luar.',
    },
    {
        q: 'Di mana lokasi pertemuan COD yang disarankan di kampus?',
        a: 'Disarankan bertemu di area publik kampus PNP pada jam perkuliahan seperti koridor Jurusan Teknologi Informasi, kantin PKM, perpustakaan, atau gazebo kampus PNP Limau Manis.',
    },
    {
        q: 'Apakah ada potongan komisi atau biaya transaksi untuk pembelian laptop?',
        a: 'Tidak ada komisi transaksi (0% potongan). KLiK tidak menyediakan perantara pembayaran online (escrow); seluruh pembayaran dilakukan langsung antara pembeli dan penjual saat pemeriksaan unit di tempat.',
    },
    {
        q: 'Bagaimana cara membuka lapak untuk menjual laptop pribadi?',
        a: 'Mahasiswa terverifikasi dapat masuk ke Dashboard dan menyewa lapak aktif untuk mengunggah spesifikasi, foto unit, kondisi fisik, dan nomor WhatsApp penjual.',
    },
];
</script>

<template>
    <Head title="KLiK - Marketplace Laptop Bekas Mahasiswa PNP" />

    <div class="min-h-screen bg-[#060A12] text-[#F7F9FB] antialiased selection:bg-[#00EFA5] selection:text-[#0B101A] flex flex-col justify-between">
        <!-- 1. Header & Navigasi -->
        <MarketplaceNavbar />

        <main class="grow">
            <!-- 2. Hero Section: Seamless Dark Tech Canvas (Melebur Halus ke Section Berikutnya) -->
            <section
                id="beranda"
                class="relative overflow-hidden bg-[#060A12] py-16 sm:py-24 lg:py-28"
            >
                <!-- Background Image: Interior Teknologi Bernuansa Sci-Fi -->
                <div
                    class="absolute inset-0 z-0 bg-cover bg-center bg-no-repeat opacity-90"
                    style="background-image: url('/images/hero-background.png');"
                />

                <!-- Atmospheric Dark Gradient Overlays (Multi-stop Fade Menuju #060A12) -->
                <div class="absolute inset-0 z-0 bg-gradient-to-r from-[#060A12]/95 via-[#060A12]/80 to-[#060A12]/35" />
                <div class="absolute inset-x-0 bottom-0 z-0 h-48 bg-gradient-to-t from-[#060A12] via-[#060A12]/80 to-transparent" />

                <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-12 lg:gap-8">
                        <!-- Hero Left Content -->
                        <div class="lg:col-span-7">
                            <div class="inline-flex items-center gap-2 rounded-full border border-[#00EFA5]/30 bg-[#00EFA5]/10 px-3.5 py-1 text-xs font-bold text-[#00EFA5] backdrop-blur-md mb-4">
                                <span class="h-2 w-2 rounded-full bg-[#00EFA5] animate-pulse" />
                                Eksklusif Mahasiswa &amp; Alumni PNP
                            </div>

                            <h1 class="text-4xl font-black tracking-tight text-white sm:text-5xl lg:text-6xl leading-[1.08]">
                                Marketplace Laptop
                                <span class="block text-white">Terpercaya</span>
                                <span
                                    class="mt-2.5 block text-2xl sm:text-3xl lg:text-4xl font-extrabold"
                                    style="background: linear-gradient(95deg, #ffffff 0%, #67fce2 38%, #dfad33 68%, #f48b08 94%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"
                                >
                                    Jual Cepat, Beli Aman, Sewa Lapak Mudah
                                </span>
                            </h1>

                            <p class="mt-5 text-sm sm:text-base leading-relaxed text-[#A4AEBB] max-w-2xl drop-shadow-sm">
                                Platform laptop bekas khusus sivitas akademika Politeknik Negeri Padang. Penjual dapat menyewa lapak digital untuk menjangkau pembeli, pembeli langsung terhubung via WhatsApp tanpa perantara dan 0% potongan transaksi.
                            </p>

                            <!-- Dual Pill CTA Buttons (Palet Neon Mint & Oranye Hangat) -->
                            <div class="mt-8 flex flex-wrap items-center gap-4">
                                <template v-if="!isAuthenticated">
                                    <Link
                                        :href="catalogRoute()"
                                        class="inline-flex items-center justify-center rounded-full bg-gradient-to-r from-[#0ff0c6] to-[#31d59c] px-8 py-3.5 text-sm font-extrabold text-[#04120e] shadow-[0_0_25px_rgba(15,240,198,0.35)] transition-all hover:scale-105 hover:brightness-110"
                                    >
                                        Explore Katalog
                                    </Link>
                                    <a
                                        href="#sewa-lapak"
                                        class="inline-flex items-center justify-center rounded-full border-2 border-[#ff5c00] bg-[#111212]/80 px-8 py-3.5 text-sm font-bold text-white shadow-[0_0_15px_rgba(255,92,0,0.2)] transition-all hover:bg-[#ff5c00]/20 hover:scale-105"
                                    >
                                        Sewa Lapak Sekarang
                                    </a>
                                </template>

                                <template v-else-if="isAdmin">
                                    <Link
                                        :href="adminDashboard()"
                                        class="inline-flex items-center justify-center rounded-full bg-gradient-to-r from-[#0ff0c6] to-[#31d59c] px-8 py-3.5 text-sm font-extrabold text-[#04120e] shadow-[0_0_25px_rgba(15,240,198,0.35)] transition-all hover:scale-105"
                                    >
                                        Panel Admin
                                    </Link>
                                    <a
                                        href="#sewa-lapak"
                                        class="inline-flex items-center justify-center rounded-full border-2 border-[#ff5c00] bg-[#111212]/80 px-8 py-3.5 text-sm font-bold text-white hover:bg-[#ff5c00]/20 transition-all hover:scale-105"
                                    >
                                        Lihat Paket Lapak
                                    </a>
                                </template>

                                <template v-else-if="isApprovedUser">
                                    <Link
                                        :href="dashboard()"
                                        class="inline-flex items-center justify-center rounded-full bg-gradient-to-r from-[#0ff0c6] to-[#31d59c] px-8 py-3.5 text-sm font-extrabold text-[#04120e] shadow-[0_0_25px_rgba(15,240,198,0.35)] transition-all hover:scale-105"
                                    >
                                        Buka Lapak di Dashboard
                                    </Link>
                                    <a
                                        href="#sewa-lapak"
                                        class="inline-flex items-center justify-center rounded-full border-2 border-[#ff5c00] bg-[#111212]/80 px-8 py-3.5 text-sm font-bold text-white hover:bg-[#ff5c00]/20 transition-all hover:scale-105"
                                    >
                                        Lihat Paket Lapak
                                    </a>
                                </template>

                                <template v-else>
                                    <Link
                                        :href="verificationStatus()"
                                        class="inline-flex items-center justify-center rounded-full px-8 py-3.5 text-sm font-extrabold text-white shadow-lg transition-all hover:scale-105"
                                        :class="isPendingUser ? 'bg-amber-600 hover:bg-amber-700' : 'bg-rose-600 hover:bg-rose-700'"
                                    >
                                        {{ isPendingUser ? 'Cek Status Verifikasi' : 'Perbaiki Berkas KTM' }}
                                    </Link>
                                    <a
                                        href="#sewa-lapak"
                                        class="inline-flex items-center justify-center rounded-full border-2 border-[#ff5c00] bg-[#111212]/80 px-8 py-3.5 text-sm font-bold text-white hover:bg-[#ff5c00]/20 transition-all hover:scale-105"
                                    >
                                        Lihat Paket Lapak
                                    </a>
                                </template>
                            </div>
                        </div>

                        <!-- Hero Right: 3D Gaming Laptop Showcase -->
                        <div class="lg:col-span-5 relative flex flex-col items-center justify-center">
                            <div class="relative w-full max-w-[500px] transition-transform duration-700 hover:scale-[1.03]">
                                <img
                                    src="/images/hero-laptop.png"
                                    alt="KLiK Laptop Marketplace Gaming Showcase"
                                    class="w-full h-auto object-contain drop-shadow-[0_30px_50px_rgba(0,0,0,0.9)]"
                                    loading="eager"
                                />

                                <!-- Carousel Indicator Dots (Sesuai Referensi Mockup) -->
                                <div class="absolute -bottom-6 right-6 flex items-center gap-2">
                                    <span class="h-2.5 w-2.5 rounded-full bg-white shadow-[0_0_10px_white]" />
                                    <span class="h-2 w-2 rounded-full bg-white/40" />
                                    <span class="h-2 w-2 rounded-full bg-white/20" />
                                    <span class="h-2 w-2 rounded-full bg-white/20" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 3. Kategori Laptop: Menyatu Alami Tanpa Border Pemisah Kasar -->
            <section
                id="kategori"
                class="scroll-mt-20 relative overflow-hidden bg-[#060A12] py-12 sm:py-16"
            >
                <!-- Subtle Centered Ambient Glow -->
                <div class="pointer-events-none absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[700px] h-[300px] bg-[#00EFA5]/[0.025] blur-[150px] rounded-full" />

                <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 border-b border-white/5 pb-6">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-[#00EFA5]">
                                Klasifikasi Spesifikasi
                            </span>
                            <h2 class="mt-1 text-2xl font-black tracking-tight text-white sm:text-3xl">
                                Kategori Laptop
                            </h2>
                        </div>
                        <p class="text-xs sm:text-sm text-[#A4AEBB] max-w-md">
                            Diprioritaskan berdasarkan kebutuhan studi dan standar hardware laptop di lingkungan kampus PNP.
                        </p>
                    </div>

                    <!-- Grid 5 Kategori (CategoryCard) -->
                    <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                        <CategoryCard
                            v-for="cat in categories"
                            :key="cat.id"
                            :category="cat"
                        />
                    </div>
                </div>
            </section>

            <!-- 4. Explore Katalog Produk: Menyambung Mulus dari Kategori -->
            <section
                id="listing-terbaru"
                class="scroll-mt-20 relative overflow-hidden py-16 sm:py-24 bg-[#060A12]"
            >
                <!-- Subtle Ambient Glows (Mint di Kiri, Amber di Kanan Tanpa Menabrak Kontras) -->
                <div class="pointer-events-none absolute top-10 left-1/4 w-[600px] h-[350px] bg-[#00EFA5]/[0.035] blur-[140px] rounded-full" />
                <div class="pointer-events-none absolute bottom-10 right-1/4 w-[600px] h-[350px] bg-[#F18A38]/[0.035] blur-[140px] rounded-full" />

                <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <!-- Heading: Explore katalog produk & View All Link -->
                    <div class="flex items-center justify-between gap-4 border-b border-white/5 pb-6">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-[#00EFA5]">
                                Marketplace Sivitas PNP
                            </span>
                            <h2 class="mt-1 text-2xl font-black tracking-tight text-white sm:text-3xl lg:text-4xl">
                                Explore katalog produk
                            </h2>
                        </div>
                        <Link
                            :href="catalogRoute()"
                            class="flex items-center gap-2 text-sm font-bold text-white transition hover:text-[#00EFA5]"
                        >
                            <span>View All Products</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </Link>
                    </div>

                    <!-- Grid Cards Laptop -->
                    <div class="mt-10">
                        <div
                            v-if="latestListings.length > 0"
                            class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                        >
                            <ListingCard
                                v-for="item in latestListings"
                                :key="item.id"
                                :listing="item"
                            />
                        </div>

                        <EmptyListingsState
                            v-else
                            :is-authenticated="isAuthenticated"
                        />
                    </div>
                </div>
            </section>

            <!-- 5. FAQ Section: Nuansa Oranye Hangat & Mengalir Alami di Kanvas Gelap -->
            <section
                id="faq"
                class="scroll-mt-20 relative overflow-hidden py-24 sm:py-32"
                style="background: linear-gradient(180deg, #060A12 0%, #150D0A 18%, #2A1207 42%, #381508 55%, #240E06 72%, #0e0705 88%, #060A12 100%);"
            >
                <!-- Background Image: Gedung PNP Transparan di Belakang -->
                <div
                    class="absolute inset-0 z-0 bg-cover bg-center bg-no-repeat opacity-20 pointer-events-none scale-105"
                    style="background-image: url('/images/bg-pnp.png');"
                />

                <!-- Central Warm Orange Radial Ambient Glows -->
                <div
                    class="pointer-events-none absolute inset-0 z-0"
                    style="background: radial-gradient(ellipse 85% 65% at 50% 50%, rgba(201, 78, 11, 0.38) 0%, rgba(143, 49, 9, 0.22) 42%, rgba(143, 49, 9, 0.05) 72%, transparent 100%);"
                />
                <div
                    class="pointer-events-none absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-3/4 max-w-3xl h-96 z-0"
                    style="background: radial-gradient(ellipse at center, rgba(241, 138, 56, 0.22) 0%, transparent 70%); filter: blur(50px);"
                />

                <!-- Top & Bottom Soft Blend Masks (Peralihan Mulus ke Section Sebelum & Sesudah) -->
                <div class="pointer-events-none absolute inset-x-0 top-0 h-28 bg-gradient-to-b from-[#060A12] to-transparent z-0" />
                <div class="pointer-events-none absolute inset-x-0 bottom-0 h-28 bg-gradient-to-t from-[#060A12] to-transparent z-0" />

                <div class="relative z-10 mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                    <!-- Heading -->
                    <div class="text-center">
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-[#F18A38]/30 bg-[#F18A38]/10 px-3.5 py-1 text-xs font-semibold text-[#F18A38]">
                            Bantuan &amp; Panduan Transaksi
                        </span>
                        <h2 class="mt-3 text-2xl font-black tracking-tight text-[#F7F9FB] sm:text-3xl lg:text-4xl">
                            Pertanyaan yang Sering Diajukan (FAQ)
                        </h2>
                        <p class="mx-auto mt-3 max-w-xl text-xs sm:text-sm text-[#F7F9FB]/75 leading-relaxed">
                            Informasi seputar alur transaksi aman, titik pertemuan COD kampus, dan verifikasi akun mahasiswa Politeknik Negeri Padang.
                        </p>
                    </div>

                    <!-- Accordion List (Dark Charcoal Cards with Warm Hover & Active States) -->
                    <div class="mt-10 space-y-3.5">
                        <div
                            v-for="(item, idx) in faqs"
                            :key="idx"
                            class="overflow-hidden rounded-2xl border transition-all duration-200"
                            :class="openFaq === idx ? 'border-[#F18A38]/60 bg-[#281D18]/95 shadow-[0_10px_30px_rgba(201,78,11,0.2)]' : 'border-white/10 bg-[#1C211F]/90 hover:border-[#F18A38]/40 hover:bg-[#222825]/90 backdrop-blur-md'"
                        >
                            <button
                                type="button"
                                class="group flex w-full items-center justify-between px-6 py-4.5 text-left text-sm sm:text-base font-bold text-[#F7F9FB] transition hover:text-[#F18A38]"
                                @click="toggleFaq(idx)"
                            >
                                <span class="pr-4">{{ item.q }}</span>
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5 shrink-0 transition-transform duration-300 text-[#F18A38]"
                                    :class="openFaq === idx ? 'rotate-180' : 'opacity-70 group-hover:opacity-100'"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2.5"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div
                                v-show="openFaq === idx"
                                class="border-t border-[#F18A38]/20 px-6 py-4.5 text-xs sm:text-sm leading-relaxed text-[#F7F9FB]/85 bg-[#181310]/95"
                            >
                                {{ item.a }}
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 6. About Us Section: Nuansa Oranye Hangat & Dark Modern Tech Style (Tanpa Background Image) -->
            <section
                id="about-us"
                class="scroll-mt-20 relative overflow-hidden py-24 sm:py-32"
                style="background: linear-gradient(180deg, #060A12 0%, #150D0A 20%, #2A1207 42%, #381508 55%, #240E06 72%, #0e0705 88%, #060A12 100%);"
            >
                <!-- Central Warm Orange Radial Ambient Glows -->
                <div
                    class="pointer-events-none absolute inset-0 z-0"
                    style="background: radial-gradient(ellipse 85% 65% at 50% 50%, rgba(201, 78, 11, 0.35) 0%, rgba(143, 49, 9, 0.20) 42%, rgba(143, 49, 9, 0.05) 72%, transparent 100%);"
                />
                <div
                    class="pointer-events-none absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-3/4 max-w-3xl h-96 z-0"
                    style="background: radial-gradient(ellipse at center, rgba(241, 138, 56, 0.20) 0%, transparent 70%); filter: blur(50px);"
                />

                <!-- Top & Bottom Soft Blend Masks (Menjamin Transisi Mulus Tanpa Garis Pemisah) -->
                <div class="pointer-events-none absolute inset-x-0 top-0 h-28 bg-gradient-to-b from-[#060A12] to-transparent z-0" />
                <div class="pointer-events-none absolute inset-x-0 bottom-0 h-28 bg-gradient-to-t from-[#060A12] to-transparent z-0" />

                <div class="relative z-10 mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                    <!-- Heading -->
                    <div class="text-center">
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-[#F18A38]/30 bg-[#F18A38]/10 px-3.5 py-1 text-xs font-bold text-[#F18A38]">
                            Tentang KLiK
                        </span>
                        <h2 class="mt-3 text-3xl font-black tracking-tight text-[#F7F9FB] sm:text-4xl lg:text-5xl">
                            About Us
                        </h2>
                        <p class="mx-auto mt-4 max-w-2xl text-base sm:text-lg text-[#F7F9FB]/90 leading-relaxed font-semibold">
                            Platform Marketplace Laptop Bekas Khusus Mahasiswa dan Alumni Politeknik Negeri Padang
                        </p>
                    </div>

                    <!-- 3 Value Pillars (Charcoal Gelap #121824 Panels dengan Kontras Tinggi) -->
                    <div class="mt-12 grid grid-cols-1 gap-6 md:grid-cols-3">
                        <div class="rounded-2xl border border-white/10 bg-[#121824]/85 p-6 backdrop-blur-md transition-all duration-300 hover:border-[#F18A38]/50 hover:bg-[#161F2E]/90 hover:-translate-y-1 shadow-[0_12px_30px_rgba(0,0,0,0.4)]">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#F18A38]/15 text-[#F18A38] border border-[#F18A38]/30">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <h3 class="mt-4 text-base font-bold text-[#F7F9FB]">
                                Verifikasi Mahasiswa &amp; Alumni
                            </h3>
                            <p class="mt-2 text-xs sm:text-sm text-[#D2D7E0] leading-relaxed">
                                Pendaftaran akun wajib menggunakan NIM dan verifikasi foto identitas KTM oleh admin, memastikan transaksi hanya melibatkan sivitas akademika PNP.
                            </p>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-[#121824]/85 p-6 backdrop-blur-md transition-all duration-300 hover:border-[#00EFA5]/50 hover:bg-[#161F2E]/90 hover:-translate-y-1 shadow-[0_12px_30px_rgba(0,0,0,0.4)]">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#00EFA5]/15 text-[#00EFA5] border border-[#00EFA5]/30">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="mt-4 text-base font-bold text-[#F7F9FB]">
                                0% Biaya Komisi Transaksi
                            </h3>
                            <p class="mt-2 text-xs sm:text-sm text-[#D2D7E0] leading-relaxed">
                                KLiK tidak memotong komisi penjualan. Pembeli langsung terhubung ke WhatsApp penjual terverifikasi tanpa perantara pembayaran online.
                            </p>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-[#121824]/85 p-6 backdrop-blur-md transition-all duration-300 hover:border-[#F18A38]/50 hover:bg-[#161F2E]/90 hover:-translate-y-1 shadow-[0_12px_30px_rgba(0,0,0,0.4)]">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#F18A38]/15 text-[#F18A38] border border-[#F18A38]/30">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <h3 class="mt-4 text-base font-bold text-[#F7F9FB]">
                                COD Aman di Kawasan Kampus
                            </h3>
                            <p class="mt-2 text-xs sm:text-sm text-[#D2D7E0] leading-relaxed">
                                Pertemuan langsung (COD) di area kampus Limau Manis (Gedung TI, PKM, Gazebo) memungkinkan pembeli mengecek fisik &amp; fungsi laptop sebelum membayar.
                            </p>
                        </div>
                    </div>

                    <!-- Academic Project Context Note -->
                    <div class="mt-12 rounded-2xl border border-white/10 bg-[#121824]/75 p-6 text-center backdrop-blur-md shadow-lg">
                        <p class="text-xs sm:text-sm text-[#D2D7E0]">
                            <strong class="text-[#F7F9FB]">Konteks Akademik:</strong> Proyek pengembangan aplikasi pada mata kuliah Kewirausahaan &amp; E-Commerce, Program Studi D3 Manajemen Informatika, Jurusan Teknologi Informasi, Politeknik Negeri Padang.
                        </p>
                    </div>
                </div>
            </section>

            <!-- 7. Section Sewa Lapak: "Pilih Lapak, Raih Pasar" (Tepat di Atas Footer, Menyambung Mulus via Gradasi Mint) -->
            <section
                id="sewa-lapak"
                class="scroll-mt-20 relative overflow-hidden pt-20 sm:pt-28 pb-14 sm:pb-20"
                style="background: radial-gradient(ellipse 100% 45% at 50% 105%, #81e3a1 50%, rgba(129, 227, 161, 0.45) 80%, transparent 100%), radial-gradient(ellipse 70% 56% at 50% 93%, rgba(223, 173, 51, 0.25) 20%, rgba(244, 139, 8, 0.45) 50%, transparent 80%), radial-gradient(ellipse 65% 50% at 50% 68%, rgba(244, 95, 8, 0.55) 0%, rgba(244, 95, 8, 0.2) 50%, transparent 80%), #060A12;"
            >
                <!-- Top Soft Blend Mask: Menjamin peralihan mulus dari section About Us (#060A12) -->
                <div class="pointer-events-none absolute inset-x-0 top-0 h-24 bg-gradient-to-b from-[#060A12] to-transparent z-0" />

                <!-- Bottom Ambient Mint Bleed: Menghubungkan dasar paket langsung ke puncak gradasi hijau Footer (#81e3a1) -->
                <div
                    class="pointer-events-none absolute inset-x-0 bottom-0 h-32 z-0"
                    style="background: radial-gradient(ellipse 100% 90% at 50% 100%, #81e3a1 25%, rgba(129, 227, 161, 0.6) 65%, transparent 100%);"
                />

                <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <!-- Heading: Sesuai Screenshot Referensi (Tanpa Toggle Bulanan/Mingguan) -->
                    <div class="text-center max-w-2xl mx-auto">
                        <h2 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl lg:text-5xl">
                            Pilih Lapak, Raih Pasar
                        </h2>
                        <p class="mt-3 text-sm sm:text-base text-[#bbb6b1] leading-relaxed">
                            Sewa lapak di KLiK dan jangkau lebih banyak pembeli. Pilih paket yang sesuai kebutuhanmu.
                        </p>
                    </div>

                    <!-- 3 Kartu Paket Berjajar Horizontal (Desktop) dan Vertikal (Mobile) -->
                    <div class="mt-14 grid grid-cols-1 gap-7 lg:grid-cols-3 items-stretch max-w-6xl mx-auto">
                        <!-- Kartu 1: Paket 90 Hari -->
                        <div class="relative flex flex-col justify-between rounded-[28px] border border-[#5d5050]/60 bg-black/65 p-8 sm:p-9 backdrop-blur-md shadow-xl transition-all hover:border-white/30">
                            <div>
                                <h3 class="text-2xl font-bold text-white tracking-tight">
                                    Paket 90 Hari
                                </h3>
                                <p class="mt-2 text-xs sm:text-sm text-[#bbb6b1] leading-relaxed">
                                    Sewa satu lapak selama 90 hari untuk menampilkan satu listing laptop aktif hingga akhir semester perkuliahan.
                                </p>

                                <ul class="mt-7 space-y-3 text-xs sm:text-sm text-[#f0e9e1]">
                                    <li class="flex items-start gap-2.5">
                                        <span class="text-[#6cf3bd] font-bold shrink-0">✓</span>
                                        <span>Sewa satu lapak selama 90 hari</span>
                                    </li>
                                    <li class="flex items-start gap-2.5">
                                        <span class="text-[#6cf3bd] font-bold shrink-0">✓</span>
                                        <span>1 lapak untuk 1 listing laptop aktif</span>
                                    </li>
                                    <li class="flex items-start gap-2.5">
                                        <span class="text-[#6cf3bd] font-bold shrink-0">✓</span>
                                        <span>Listing tampil selama lapak masih aktif</span>
                                    </li>
                                    <li class="flex items-start gap-2.5">
                                        <span class="text-[#6cf3bd] font-bold shrink-0">✓</span>
                                        <span>Pembeli hubungi via WhatsApp</span>
                                    </li>
                                </ul>
                            </div>

                            <div class="mt-10 pt-6 border-t border-white/5">
                                <div>
                                    <div class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                                        Rp40.000
                                    </div>
                                    <div class="mt-1 text-xs text-[#ccc5b9]">
                                        / 90 hari • Sekitar Rp444/hari
                                    </div>
                                </div>

                                <template v-if="!isAuthenticated">
                                    <Link
                                        :href="register()"
                                        class="mt-6 inline-flex w-full min-h-[46px] items-center justify-center rounded-xl border border-white/40 bg-black/20 text-[#6cf3bd] font-bold text-xs uppercase tracking-wider transition hover:bg-[#6cf3bd]/15 hover:border-[#6cf3bd]"
                                    >
                                        Pelajari Sewa Lapak
                                    </Link>
                                </template>
                                <template v-else-if="isAdmin">
                                    <Link
                                        :href="adminDashboard()"
                                        class="mt-6 inline-flex w-full min-h-[46px] items-center justify-center rounded-xl border border-white/40 bg-black/20 text-[#6cf3bd] font-bold text-xs uppercase tracking-wider transition hover:bg-[#6cf3bd]/15 hover:border-[#6cf3bd]"
                                    >
                                        Panel Admin
                                    </Link>
                                </template>
                                <template v-else-if="isApprovedUser">
                                    <Link
                                        :href="dashboard()"
                                        class="mt-6 inline-flex w-full min-h-[46px] items-center justify-center rounded-xl border border-white/40 bg-black/20 text-[#6cf3bd] font-bold text-xs uppercase tracking-wider transition hover:bg-[#6cf3bd]/15 hover:border-[#6cf3bd]"
                                    >
                                        Buka Lapak di Dashboard
                                    </Link>
                                </template>
                                <template v-else>
                                    <Link
                                        :href="verificationStatus()"
                                        class="mt-6 inline-flex w-full min-h-[46px] items-center justify-center rounded-xl border border-white/40 bg-black/20 text-[#6cf3bd] font-bold text-xs uppercase tracking-wider transition hover:bg-[#6cf3bd]/15 hover:border-[#6cf3bd]"
                                    >
                                        Cek Status Verifikasi
                                    </Link>
                                </template>

                                <p class="mt-2.5 text-[11px] text-[#d3cbc0] text-center">
                                    Tanpa komisi transaksi (0%)
                                </p>
                            </div>
                        </div>

                        <!-- Kartu 2: Paket 150 Hari (FEATURED - Outline Oranye, Badge Durasi Lebih Panjang, Tombol Putih Terang) -->
                        <div class="relative flex flex-col justify-between rounded-[28px] border-2 border-[#f45f08] bg-black/90 p-8 sm:p-9 shadow-[0_0_50px_rgba(244,95,8,0.25)] transition-all hover:scale-[1.02]">
                            <!-- Badge 'Durasi Lebih Panjang' (Sesuai Referensi Screenshot) -->
                            <div class="absolute top-6 right-6 px-3.5 py-1 rounded-full bg-[#6cf3bd] text-[#06120e] text-[11px] font-black uppercase tracking-wider shadow-md">
                                Durasi Lebih Panjang
                            </div>

                            <div>
                                <h3 class="text-2xl font-bold text-white tracking-tight">
                                    Paket 150 Hari
                                </h3>
                                <p class="mt-2 text-xs sm:text-sm text-[#bbb6b1] leading-relaxed">
                                    Masa aktif terpanjang untuk fleksibilitas penjualan optimal antar-semester perkuliahan.
                                </p>

                                <ul class="mt-7 space-y-3 text-xs sm:text-sm text-[#f0e9e1]">
                                    <li class="flex items-start gap-2.5">
                                        <span class="text-[#6cf3bd] font-bold shrink-0">✓</span>
                                        <span>Sewa satu lapak selama 150 hari</span>
                                    </li>
                                    <li class="flex items-start gap-2.5">
                                        <span class="text-[#6cf3bd] font-bold shrink-0">✓</span>
                                        <span>1 lapak untuk 1 listing laptop aktif</span>
                                    </li>
                                    <li class="flex items-start gap-2.5">
                                        <span class="text-[#6cf3bd] font-bold shrink-0">✓</span>
                                        <span>Listing tampil selama lapak masih aktif</span>
                                    </li>
                                    <li class="flex items-start gap-2.5">
                                        <span class="text-[#6cf3bd] font-bold shrink-0">✓</span>
                                        <span>Pembeli hubungi via WhatsApp</span>
                                    </li>
                                </ul>
                            </div>

                            <div class="mt-10 pt-6 border-t border-white/10">
                                <div>
                                    <div class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                                        Rp65.000
                                    </div>
                                    <div class="mt-1 text-xs text-[#ccc5b9]">
                                        / 150 hari • Sekitar Rp433/hari
                                    </div>
                                </div>

                                <template v-if="!isAuthenticated">
                                    <Link
                                        :href="register()"
                                        class="mt-6 inline-flex w-full min-h-[46px] items-center justify-center rounded-xl bg-white text-[#080808] font-bold text-xs uppercase tracking-wider shadow-lg transition hover:bg-[#6cf3bd] hover:text-[#04120e]"
                                    >
                                        Pelajari Sewa Lapak
                                    </Link>
                                </template>
                                <template v-else-if="isAdmin">
                                    <Link
                                        :href="adminDashboard()"
                                        class="mt-6 inline-flex w-full min-h-[46px] items-center justify-center rounded-xl bg-white text-[#080808] font-bold text-xs uppercase tracking-wider shadow-lg transition hover:bg-[#6cf3bd] hover:text-[#04120e]"
                                    >
                                        Panel Admin
                                    </Link>
                                </template>
                                <template v-else-if="isApprovedUser">
                                    <Link
                                        :href="dashboard()"
                                        class="mt-6 inline-flex w-full min-h-[46px] items-center justify-center rounded-xl bg-white text-[#080808] font-bold text-xs uppercase tracking-wider shadow-lg transition hover:bg-[#6cf3bd] hover:text-[#04120e]"
                                    >
                                        Buka Lapak di Dashboard
                                    </Link>
                                </template>
                                <template v-else>
                                    <Link
                                        :href="verificationStatus()"
                                        class="mt-6 inline-flex w-full min-h-[46px] items-center justify-center rounded-xl bg-white text-[#080808] font-bold text-xs uppercase tracking-wider shadow-lg transition hover:bg-[#6cf3bd] hover:text-[#04120e]"
                                    >
                                        Cek Status Verifikasi
                                    </Link>
                                </template>

                                <p class="mt-2.5 text-[11px] text-[#d3cbc0] text-center">
                                    Tanpa komisi transaksi (0%)
                                </p>
                            </div>
                        </div>

                        <!-- Kartu 3: Paket Custom -->
                        <div class="relative flex flex-col justify-between rounded-[28px] border border-[#5d5050]/60 bg-black/65 p-8 sm:p-9 backdrop-blur-md shadow-xl transition-all hover:border-white/30">
                            <div>
                                <h3 class="text-2xl font-bold text-white tracking-tight">
                                    Paket Custom
                                </h3>
                                <p class="mt-2 text-xs sm:text-sm text-[#bbb6b1] leading-relaxed">
                                    Pilih durasi fleksibel per minggu sesuai kebutuhan masa aktif penjualan laptop kamu.
                                </p>

                                <ul class="mt-7 space-y-3 text-xs sm:text-sm text-[#f0e9e1]">
                                    <li class="flex items-start gap-2.5">
                                        <span class="text-[#6cf3bd] font-bold shrink-0">✓</span>
                                        <span>Pilih durasi sesuai kebutuhan</span>
                                    </li>
                                    <li class="flex items-start gap-2.5">
                                        <span class="text-[#6cf3bd] font-bold shrink-0">✓</span>
                                        <span>Minimal 1 minggu, maksimal 52 minggu</span>
                                    </li>
                                    <li class="flex items-start gap-2.5">
                                        <span class="text-[#6cf3bd] font-bold shrink-0">✓</span>
                                        <span>1 lapak untuk 1 listing laptop aktif</span>
                                    </li>
                                    <li class="flex items-start gap-2.5">
                                        <span class="text-[#6cf3bd] font-bold shrink-0">✓</span>
                                        <span>Total harga dihitung per jumlah minggu</span>
                                    </li>
                                </ul>
                            </div>

                            <div class="mt-10 pt-6 border-t border-white/5">
                                <div>
                                    <div class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                                        Rp4.000
                                    </div>
                                    <div class="mt-1 text-xs text-[#ccc5b9]">
                                        / minggu • Fleksibel 1 sampai 52 minggu
                                    </div>
                                </div>

                                <template v-if="!isAuthenticated">
                                    <Link
                                        :href="register()"
                                        class="mt-6 inline-flex w-full min-h-[46px] items-center justify-center rounded-xl border border-white/40 bg-black/20 text-[#6cf3bd] font-bold text-xs uppercase tracking-wider transition hover:bg-[#6cf3bd]/15 hover:border-[#6cf3bd]"
                                    >
                                        Pelajari Sewa Lapak
                                    </Link>
                                </template>
                                <template v-else-if="isAdmin">
                                    <Link
                                        :href="adminDashboard()"
                                        class="mt-6 inline-flex w-full min-h-[46px] items-center justify-center rounded-xl border border-white/40 bg-black/20 text-[#6cf3bd] font-bold text-xs uppercase tracking-wider transition hover:bg-[#6cf3bd]/15 hover:border-[#6cf3bd]"
                                    >
                                        Panel Admin
                                    </Link>
                                </template>
                                <template v-else-if="isApprovedUser">
                                    <Link
                                        :href="dashboard()"
                                        class="mt-6 inline-flex w-full min-h-[46px] items-center justify-center rounded-xl border border-white/40 bg-black/20 text-[#6cf3bd] font-bold text-xs uppercase tracking-wider transition hover:bg-[#6cf3bd]/15 hover:border-[#6cf3bd]"
                                    >
                                        Buka Lapak di Dashboard
                                    </Link>
                                </template>
                                <template v-else>
                                    <Link
                                        :href="verificationStatus()"
                                        class="mt-6 inline-flex w-full min-h-[46px] items-center justify-center rounded-xl border border-white/40 bg-black/20 text-[#6cf3bd] font-bold text-xs uppercase tracking-wider transition hover:bg-[#6cf3bd]/15 hover:border-[#6cf3bd]"
                                    >
                                        Cek Status Verifikasi
                                    </Link>
                                </template>

                                <p class="mt-2.5 text-[11px] text-[#d3cbc0] text-center">
                                    Dihitung saat aktivasi lapak
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <!-- 8. Footer -->
        <MarketplaceFooter />
    </div>
</template>
