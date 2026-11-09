<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { logout } from '@/routes';

interface ProdiCodeData {
    id: number;
    kode: string;
    jenjang: string | null;
    nama_prodi: string;
}

interface AuthUser {
    id: number;
    name: string;
    nim?: string;
    role?: string;
    status_verifikasi?: string;
    prodi_code?: ProdiCodeData | null;
}

const page = usePage<{ auth: { user: AuthUser | null } }>();
const user = computed(() => page.props.auth?.user ?? null);

// Inisial pengguna untuk avatar (misal: "Rizky Aulia" -> "RA")
const userInitials = computed(() => {
    if (!user.value?.name) return 'RA';
    const parts = user.value.name.trim().split(/\s+/);
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return parts[0].slice(0, 2).toUpperCase();
});

// Nama Program Studi
const prodiName = computed(() => {
    if (user.value?.prodi_code) {
        const { jenjang, nama_prodi } = user.value.prodi_code;
        if (!jenjang) return nama_prodi;
        if (nama_prodi.toLowerCase().startsWith(jenjang.toLowerCase())) {
            return nama_prodi;
        }
        return `${jenjang} ${nama_prodi}`;
    }
    return 'D3 Teknik Komputer';
});

// State Dropdown Profil User
const isUserMenuOpen = ref(false);
const userMenuRef = ref<HTMLElement | null>(null);

const toggleUserMenu = () => {
    isUserMenuOpen.value = !isUserMenuOpen.value;
};

const closeUserMenu = (event: MouseEvent) => {
    if (userMenuRef.value && !userMenuRef.value.contains(event.target as Node)) {
        isUserMenuOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', closeUserMenu);
});

onUnmounted(() => {
    document.removeEventListener('click', closeUserMenu);
});
</script>

<template>
    <Head title="Dashboard - KLiK Marketplace Laptop Mahasiswa PNP" />

    <!-- Container Utama dengan Background Oren Aesthetic & Dark Slate Atmosphere -->
    <div class="relative min-h-screen bg-[#07090E] text-[#F7F9FB] antialiased selection:bg-[#F18A38] selection:text-[#0B101A] overflow-hidden flex flex-col justify-between">
        <!-- Ambient Aesthetic Orange Glowing Effects -->
        <!-- 1. Top Center Sunburst Orange Ambient Glow -->
        <div class="pointer-events-none absolute -top-32 left-1/2 -translate-x-1/2 w-[850px] h-[450px] bg-gradient-to-b from-[#F18A38]/25 via-[#FF6B00]/12 to-transparent blur-[150px] rounded-full" />
        <!-- 2. Ambient Warm Orange Sunset Glow (Kiri Bawah) -->
        <div class="pointer-events-none absolute bottom-0 left-0 w-[550px] h-[350px] bg-gradient-to-tr from-[#EA580C]/15 via-[#F18A38]/08 to-transparent blur-[160px] rounded-full" />
        <!-- 3. Ambient Warm Amber Glow (Kanan) -->
        <div class="pointer-events-none absolute top-1/3 -right-20 w-[600px] h-[400px] bg-gradient-to-l from-[#F59E0B]/12 via-[#D97706]/08 to-transparent blur-[160px] rounded-full" />

        <!-- Top Header Navigation Sesuai Mockup Dashboard Pembeli -->
        <header class="relative z-30 w-full border-b border-orange-500/10 bg-[#07090E]/70 backdrop-blur-md">
            <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <!-- Brand Logo (KLiK) -->
                <Link href="/" class="group flex items-center gap-2.5">
                    <span class="text-3xl font-black tracking-tight text-white transition-transform group-hover:scale-105">
                        KL<span class="text-[#00EFA5]">i</span>K
                    </span>
                    <span class="rounded bg-[#F18A38]/15 px-1.5 py-0.5 text-[10px] font-bold text-[#F18A38] border border-[#F18A38]/30">
                        PNP
                    </span>
                </Link>

                <!-- Navigation Links -->
                <div class="flex items-center gap-6 sm:gap-8">
                    <nav class="hidden md:flex items-center gap-8 text-sm sm:text-base font-medium text-white/80">
                        <Link href="/katalog" class="transition hover:text-[#00EFA5]">
                            Katalog
                        </Link>
                        <a href="/#sewa-lapak" class="transition hover:text-[#00EFA5]">
                            Sell
                        </a>
                        <a href="/#faq" class="transition hover:text-[#00EFA5]">
                            FAQ
                        </a>
                        <a href="/#about-us" class="transition hover:text-[#00EFA5]">
                            About Us
                        </a>
                    </nav>

                    <!-- User Actions: Notification Bell + User Avatar Pill -->
                    <div class="flex items-center gap-3">
                        <!-- Notification Bell -->
                        <button
                            type="button"
                            class="relative flex h-10 w-10 items-center justify-center rounded-full text-white/70 hover:text-white hover:bg-white/5 transition"
                            title="Notifikasi"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <!-- Notification Dot -->
                            <span class="absolute top-2.5 right-2.5 h-2 w-2 rounded-full bg-[#00EFA5] ring-2 ring-[#07090E]" />
                        </button>

                        <!-- User Profile Dropdown Pill -->
                        <div ref="userMenuRef" class="relative">
                            <button
                                type="button"
                                @click="toggleUserMenu"
                                class="flex items-center gap-2 rounded-full border border-white/10 bg-white/5 p-1 pr-2.5 hover:border-[#00EFA5]/40 transition group"
                            >
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[#00EFA5]/20 text-xs font-bold text-[#00EFA5] border border-[#00EFA5]/40 group-hover:scale-105 transition-transform">
                                    {{ userInitials }}
                                </div>
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-3.5 w-3.5 text-white/60 transition-transform group-hover:text-white"
                                    :class="{ 'rotate-180': isUserMenuOpen }"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Dropdown Menu -->
                            <div
                                v-if="isUserMenuOpen"
                                class="absolute right-0 mt-2 w-56 rounded-2xl border border-orange-500/20 bg-[#0E131F]/95 p-2 shadow-2xl backdrop-blur-xl z-50 animate-in fade-in zoom-in-95 duration-150"
                            >
                                <div class="border-b border-white/10 px-3 py-2.5">
                                    <p class="text-xs font-bold text-white truncate">{{ user?.name || 'Rizky Aulia' }}</p>
                                    <p class="text-[11px] text-[#A4AEBB] font-mono">{{ user?.nim || '2301082014' }}</p>
                                </div>

                                <div class="py-1">
                                    <Link
                                        href="/"
                                        class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-medium text-white/80 hover:bg-white/5 hover:text-[#00EFA5] transition"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                        </svg>
                                        Beranda Marketplace
                                    </Link>
                                    <Link
                                        href="/katalog"
                                        class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-medium text-white/80 hover:bg-white/5 hover:text-[#00EFA5] transition"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                        </svg>
                                        Jelajahi Katalog
                                    </Link>
                                </div>

                                <div class="border-t border-white/10 pt-1">
                                    <Link
                                        :href="logout()"
                                        method="post"
                                        as="button"
                                        class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-semibold text-rose-400 hover:bg-rose-500/10 transition text-left"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        Keluar
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="relative z-10 grow px-4 py-8 sm:py-12 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-4xl">
                <!-- Page Title -->
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white mb-8">
                    Dashboard
                </h1>

                <!-- Card 1: Status Pengajuan Penjualan (Sesuai Gambar "Dashboard Pembeli.png") -->
                <div class="relative overflow-hidden rounded-3xl border border-orange-500/20 bg-[#0E131F]/90 p-8 sm:p-14 text-center shadow-[0_8px_32px_rgba(241,138,56,0.08)] backdrop-blur-xl transition hover:border-orange-500/35">
                    <!-- Subtle Glow Inside Card -->
                    <div class="pointer-events-none absolute -top-20 left-1/2 -translate-x-1/2 w-96 h-40 bg-[#F18A38]/10 blur-[80px] rounded-full" />

                    <!-- Orange Laptop Outline Icon (Sesuai Request) -->
                    <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-12 w-12 text-[#F18A38] drop-shadow-[0_0_12px_rgba(241,138,56,0.35)]"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="1.75"
                        >
                            <!-- Laptop Open Display -->
                            <rect x="3" y="4" width="18" height="12" rx="1.5" />
                            <!-- Laptop Keyboard / Base -->
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2 18h20a1 1 0 011 1v1H1v-1a1 1 0 011-1z" />
                            <!-- Trackpad notch -->
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 18h4" />
                        </svg>
                    </div>

                    <!-- Heading -->
                    <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">
                        Kamu belum mengajukan penjualan
                    </h2>

                    <!-- Description Subtitle -->
                    <p class="mx-auto mt-3 max-w-lg text-xs sm:text-sm text-[#A4AEBB] leading-relaxed">
                        Ingin menjual laptopmu? Sewa lapak untuk memasang satu laptop selama masa sewa, lalu pembeli menghubungimu lewat WhatsApp.
                    </p>

                    <!-- Action Button: Sewa Lapak (Outlined Orange Line Sesuai Request) -->
                    <div class="mt-8 flex flex-col items-center justify-center gap-3">
                        <a
                            href="/#sewa-lapak"
                            class="inline-flex items-center justify-center rounded-xl sm:rounded-2xl border-2 border-[#F18A38] bg-transparent px-8 py-3 text-sm font-bold text-[#F18A38] transition-all duration-200 hover:bg-[#F18A38]/10 hover:text-[#FFA048] hover:border-[#FFA048] hover:shadow-[0_0_20px_rgba(241,138,56,0.35)] hover:scale-[1.02]"
                        >
                            Sewa Lapak
                        </a>

                        <!-- Secondary Link: Jelajahi Katalog (Warna Putih Sesuai Request) -->
                        <Link
                            href="/katalog"
                            class="text-xs sm:text-sm font-semibold text-white hover:text-white/80 hover:underline transition"
                        >
                            Jelajahi Katalog
                        </Link>
                    </div>
                </div>

                <!-- Card 2: Profil & Identitas Terverifikasi (Sesuai Gambar "Dashboard Pembeli.png") -->
                <div class="mt-6 rounded-2xl border border-orange-500/20 bg-[#0E131F]/90 px-6 py-5 shadow-[0_8px_32px_rgba(241,138,56,0.06)] backdrop-blur-xl transition hover:border-orange-500/35">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5">
                        <!-- Left Section: Avatar + Name + Campus -->
                        <div class="flex items-center gap-3.5">
                            <!-- Initials Avatar Circle (RA) -->
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#00EFA5]/15 text-sm font-bold text-[#00EFA5] border border-[#00EFA5]/35 shadow-[0_0_12px_rgba(0,239,165,0.15)]">
                                {{ userInitials }}
                            </div>
                            <div>
                                <h3 class="text-sm sm:text-base font-bold text-white tracking-tight">
                                    {{ user?.name || 'Rizky Aulia' }}
                                </h3>
                                <p class="text-xs text-[#A4AEBB]">
                                    Politeknik Negeri Padang
                                </p>
                            </div>
                        </div>

                        <!-- Right Section: NIM + Program Studi + Badge Terverifikasi -->
                        <div class="flex flex-wrap items-center justify-between sm:justify-end gap-6 sm:gap-10 border-t border-white/5 pt-4 sm:border-0 sm:pt-0">
                            <!-- Column NIM -->
                            <div>
                                <span class="block text-[11px] font-medium text-[#A4AEBB]">NIM</span>
                                <span class="text-xs sm:text-sm font-bold text-white font-mono">
                                    {{ user?.nim || '2301082014' }}
                                </span>
                            </div>

                            <!-- Column Program Studi -->
                            <div>
                                <span class="block text-[11px] font-medium text-[#A4AEBB]">Program studi</span>
                                <span class="text-xs sm:text-sm font-bold text-white">
                                    {{ prodiName }}
                                </span>
                            </div>

                            <!-- Badge Terverifikasi (Green Check Pill) -->
                            <div>
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-[#00EFA5]/30 bg-[#00EFA5]/10 px-3 py-1 text-xs font-semibold text-[#00EFA5] shadow-[0_0_12px_rgba(0,239,165,0.15)]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Terverifikasi
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Subtle Footer Minimalis -->
        <footer class="relative z-10 border-t border-white/5 py-6 text-center text-xs text-[#A4AEBB]/60">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                &copy; {{ new Date().getFullYear() }} KLiK &bull; Marketplace Laptop Bekas Khusus Sivitas Politeknik Negeri Padang.
            </div>
        </footer>
    </div>
</template>
