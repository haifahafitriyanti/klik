<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { dashboard, login, logout, register } from '@/routes';
import { dashboard as adminDashboard } from '@/routes/admin';
import { status as verificationStatus } from '@/routes/verification';

interface AuthUser {
    id: number;
    name: string;
    nim?: string;
    role: 'user' | 'admin';
    status_verifikasi: 'menunggu' | 'disetujui' | 'ditolak';
    is_suspended?: boolean;
}

const page = usePage<{ auth: { user: AuthUser | null } }>();
const user = computed(() => page.props.auth?.user ?? null);

const isMobileMenuOpen = ref(false);

const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

const closeMobileMenu = () => {
    isMobileMenuOpen.value = false;
};
</script>

<template>
    <header class="sticky top-0 z-50 w-full border-b border-white/5 bg-[#060A12]/80 backdrop-blur-md transition-colors">
        <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <!-- Brand Logo (Sesuai Mockup KLiK) -->
            <Link :href="'/'" class="group flex items-center gap-2.5" @click="closeMobileMenu">
                <span class="text-3xl font-black tracking-tight text-white transition-transform group-hover:scale-105">
                    KL<span class="text-[#00EFA5]">i</span>K
                </span>
                <span class="rounded bg-[#00EFA5]/15 px-1.5 py-0.5 text-[10px] font-bold text-[#00EFA5] border border-[#00EFA5]/30">
                    PNP
                </span>
            </Link>

            <!-- Desktop Nav Links (Fase 4.1 & 4.2) -->
            <nav class="hidden md:flex items-center gap-8 text-base font-medium text-white/80">
                <a href="/katalog" class="transition hover:text-[#00EFA5]">
                    Katalog
                </a>
                <a href="/#kategori" class="transition hover:text-[#00EFA5]">
                    Kategori
                </a>
                <a href="/#sewa-lapak" class="transition hover:text-[#00EFA5]">
                    Sewa Lapak
                </a>
                <a href="/#faq" class="transition hover:text-[#00EFA5]">
                    FAQ
                </a>
                <a href="/#about-us" class="transition hover:text-[#00EFA5]">
                    About Us
                </a>
            </nav>

            <!-- Desktop Auth Action Controls -->
            <div class="hidden md:flex items-center gap-3">
                <!-- Condition A: Guest -->
                <template v-if="!user">
                    <Link
                        :href="login()"
                        class="rounded-full px-5 py-2 text-sm font-semibold text-white/90 transition hover:text-[#00EFA5]"
                    >
                        Masuk (NIM)
                    </Link>
                    <Link
                        :href="register()"
                        class="inline-flex items-center gap-1.5 rounded-full bg-[#00EFA5] px-5 py-2 text-sm font-bold text-[#0B101A] shadow-[0_0_15px_rgba(0,239,165,0.35)] transition hover:bg-[#00d694] hover:shadow-[0_0_20px_rgba(0,239,165,0.5)]"
                    >
                        Daftar
                    </Link>
                </template>

                <!-- Condition B: Admin -->
                <template v-else-if="user.role === 'admin'">
                    <Link
                        :href="adminDashboard()"
                        class="inline-flex items-center gap-2 rounded-full border border-indigo-400/40 bg-indigo-950/50 px-4 py-2 text-xs font-semibold text-indigo-300 transition hover:bg-indigo-900/50"
                    >
                        <span class="h-2 w-2 rounded-full bg-indigo-400 animate-pulse" />
                        Panel Admin
                    </Link>
                    <Link
                        :href="logout()"
                        method="post"
                        as="button"
                        class="rounded-full px-3 py-2 text-xs font-medium text-white/70 transition hover:text-white"
                    >
                        Keluar
                    </Link>
                </template>

                <!-- Condition C: Approved User -->
                <template v-else-if="user.status_verifikasi === 'disetujui'">
                    <Link
                        :href="dashboard()"
                        class="inline-flex items-center gap-2 rounded-full border border-[#00EFA5]/40 bg-[#00EFA5]/15 px-4 py-2 text-xs font-bold text-[#00EFA5] transition hover:bg-[#00EFA5]/25"
                    >
                        Dashboard
                    </Link>
                    <Link
                        :href="logout()"
                        method="post"
                        as="button"
                        class="rounded-full px-3 py-2 text-xs font-medium text-white/70 transition hover:text-white"
                    >
                        Keluar
                    </Link>
                </template>

                <!-- Condition D: Pending User -->
                <template v-else-if="user.status_verifikasi === 'menunggu'">
                    <Link
                        :href="verificationStatus()"
                        class="inline-flex items-center gap-2 rounded-full border border-amber-500/40 bg-amber-950/50 px-4 py-2 text-xs font-semibold text-amber-300"
                    >
                        <span class="h-2 w-2 rounded-full bg-amber-400 animate-pulse" />
                        Verifikasi Diproses
                    </Link>
                    <Link
                        :href="logout()"
                        method="post"
                        as="button"
                        class="rounded-full px-3 py-2 text-xs font-medium text-white/70 hover:text-white"
                    >
                        Keluar
                    </Link>
                </template>

                <!-- Condition E: Rejected User -->
                <template v-else>
                    <Link
                        :href="verificationStatus()"
                        class="inline-flex items-center gap-2 rounded-full border border-rose-500/40 bg-rose-950/50 px-4 py-2 text-xs font-semibold text-rose-300"
                    >
                        Perbaiki Verifikasi
                    </Link>
                    <Link
                        :href="logout()"
                        method="post"
                        as="button"
                        class="rounded-full px-3 py-2 text-xs font-medium text-white/70 hover:text-white"
                    >
                        Keluar
                    </Link>
                </template>
            </div>

            <!-- Mobile Hamburger Button -->
            <button
                type="button"
                class="md:hidden flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-white transition hover:text-[#00EFA5]"
                :aria-expanded="isMobileMenuOpen"
                aria-label="Buka menu navigasi"
                @click="toggleMobileMenu"
            >
                <svg v-if="!isMobileMenuOpen" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Mobile Drawer -->
        <div v-if="isMobileMenuOpen" class="md:hidden border-b border-white/5 bg-[#060A12] px-4 pt-3 pb-6 shadow-2xl">
            <nav class="flex flex-col gap-1 text-base font-medium text-white/80">
                <a
                    href="/katalog"
                    class="rounded-xl px-3.5 py-2.5 hover:bg-white/5 hover:text-[#00EFA5] transition"
                    @click="closeMobileMenu"
                >
                    Katalog
                </a>
                <a
                    href="/#kategori"
                    class="rounded-xl px-3.5 py-2.5 hover:bg-white/5 hover:text-[#00EFA5] transition"
                    @click="closeMobileMenu"
                >
                    Kategori
                </a>
                <a
                    href="/#sewa-lapak"
                    class="rounded-xl px-3.5 py-2.5 hover:bg-white/5 hover:text-[#00EFA5] transition"
                    @click="closeMobileMenu"
                >
                    Sewa Lapak
                </a>
                <a
                    href="/#faq"
                    class="rounded-xl px-3.5 py-2.5 hover:bg-white/5 hover:text-[#00EFA5] transition"
                    @click="closeMobileMenu"
                >
                    FAQ
                </a>
                <a
                    href="/#about-us"
                    class="rounded-xl px-3.5 py-2.5 hover:bg-white/5 hover:text-[#00EFA5] transition"
                    @click="closeMobileMenu"
                >
                    About Us
                </a>
            </nav>

            <div class="mt-4 border-t border-white/10 pt-4 flex flex-col gap-2">
                <template v-if="!user">
                    <Link :href="login()" class="w-full" @click="closeMobileMenu">
                        <button class="w-full rounded-full border border-white/20 bg-white/5 py-2.5 text-sm font-semibold text-white transition hover:text-[#00EFA5]">
                            Masuk (NIM)
                        </button>
                    </Link>
                    <Link :href="register()" class="w-full" @click="closeMobileMenu">
                        <button class="w-full rounded-full bg-[#00EFA5] py-2.5 text-sm font-bold text-[#0B101A] shadow-[0_0_15px_rgba(0,239,165,0.3)]">
                            Daftar Mahasiswa
                        </button>
                    </Link>
                </template>

                <template v-else-if="user.role === 'admin'">
                    <Link :href="adminDashboard()" class="w-full" @click="closeMobileMenu">
                        <button class="w-full rounded-full border border-indigo-500/40 bg-indigo-950/50 py-2.5 text-xs font-semibold text-indigo-300">
                            Buka Panel Admin
                        </button>
                    </Link>
                    <Link :href="logout()" method="post" as="button" class="w-full text-center py-2 text-xs text-white/70 hover:text-white">
                        Keluar
                    </Link>
                </template>

                <template v-else-if="user.status_verifikasi === 'disetujui'">
                    <Link :href="dashboard()" class="w-full" @click="closeMobileMenu">
                        <button class="w-full rounded-full border border-[#00EFA5]/40 bg-[#00EFA5]/15 py-2.5 text-xs font-bold text-[#00EFA5]">
                            Masuk Dashboard
                        </button>
                    </Link>
                    <Link :href="logout()" method="post" as="button" class="w-full text-center py-2 text-xs text-white/70 hover:text-white">
                        Keluar
                    </Link>
                </template>

                <template v-else>
                    <Link :href="verificationStatus()" class="w-full" @click="closeMobileMenu">
                        <button
                            class="w-full rounded-full py-2.5 text-xs font-semibold text-white"
                            :class="user.status_verifikasi === 'menunggu' ? 'bg-amber-600' : 'bg-rose-600'"
                        >
                            {{ user.status_verifikasi === 'menunggu' ? 'Cek Status Verifikasi' : 'Perbaiki Berkas KTM' }}
                        </button>
                    </Link>
                    <Link :href="logout()" method="post" as="button" class="w-full text-center py-2 text-xs text-white/70 hover:text-white">
                        Keluar
                    </Link>
                </template>
            </div>
        </div>
    </header>
</template>
