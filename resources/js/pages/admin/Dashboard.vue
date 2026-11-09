<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Spinner } from '@/components/ui/spinner';
import { logout } from '@/routes';
import { dashboard } from '@/routes/admin';
import { approve, ktm, reject } from '@/routes/admin/verifikasi';

interface Submission {
    id: number;
    name: string;
    nim: string;
    status_mahasiswa: 'aktif' | 'alumni';
    kode_prodi_nim: string | null;
    angkatan: number | null;
    prodi_name: string | null;
    jenjang: string | null;
    jurusan_name: string | null;
    kode_prodi_tidak_dikenal: boolean;
    status_verifikasi: 'menunggu' | 'disetujui' | 'ditolak';
    has_ktm: boolean;
    document_token: string | null;
    created_at: string | null;
    updated_at: string | null;
    verified_at: string | null;
    verifier_name: string | null;
    alasan_penolakan: string | null;
}

interface Stats {
    total_menunggu: number;
    siap_verifikasi: number;
    menunggu_ktm: number;
    total_disetujui: number;
    total_ditolak: number;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedSubmissions {
    data: Submission[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    total: number;
}

const props = defineProps<{
    stats: Stats;
    submissions: PaginatedSubmissions;
    filters: {
        status: string;
        has_ktm: string;
        search: string;
    };
    status?: string;
    errors?: Record<string, string>;
}>();

const page = usePage();
const currentUser = computed(() => (page.props.auth as any)?.user);

const adminName = computed(() => currentUser.value?.name || 'Admin PNP');
const adminInitials = computed(() => {
    const parts = (adminName.value || 'Admin PNP').trim().split(/\s+/);
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return parts[0].slice(0, 2).toUpperCase();
});

// Active Tab ('ringkasan' | 'validasi')
const currentTab = ref<'ringkasan' | 'validasi'>('ringkasan');
const mobileSidebarOpen = ref(false);

// Filter states
const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || 'menunggu');
const ktmFilter = ref(props.filters.has_ktm || 'semua');

// Modal Approval State
const approveModalOpen = ref(false);

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Escape') {
        if (approveModalOpen.value && !isSubmitting.value) {
            closeApproveModal();
        }
        if (zoomKtmOpen.value) {
            zoomKtmOpen.value = false;
        }
    }
};

// Check URL query on mount for tab & register escape listener
onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('tab') === 'validasi' || props.filters.search || props.filters.status !== 'menunggu') {
        currentTab.value = 'validasi';
    }
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});

const setTab = (tab: 'ringkasan' | 'validasi') => {
    currentTab.value = tab;
    mobileSidebarOpen.value = false;
};

const applyFilters = () => {
    router.get(
        dashboard.url(),
        {
            status: statusFilter.value,
            has_ktm: ktmFilter.value,
            search: search.value || undefined,
            tab: 'validasi',
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

// Selection State for Validasi Akun View
const selectedSubmission = ref<Submission | null>(props.submissions.data[0] || null);
const showRejectForm = ref(false);
const rejectReason = ref('');
const isSubmitting = ref(false);
const actionError = ref<string | null>(null);
const zoomKtmOpen = ref(false);

const selectSubmission = (sub: Submission) => {
    selectedSubmission.value = sub;
    showRejectForm.value = false;
    rejectReason.value = '';
    actionError.value = null;
};

const openApproveModal = () => {
    if (!selectedSubmission.value || !selectedSubmission.value.has_ktm) return;
    actionError.value = null;
    approveModalOpen.value = true;
};

const closeApproveModal = () => {
    if (isSubmitting.value) return;
    approveModalOpen.value = false;
    actionError.value = null;
};

const executeApprove = () => {
    if (!selectedSubmission.value || !selectedSubmission.value.has_ktm || isSubmitting.value) return;

    isSubmitting.value = true;
    actionError.value = null;

    router.post(
        approve.url(selectedSubmission.value.id),
        {
            document_token: selectedSubmission.value.document_token,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                approveModalOpen.value = false;
                showRejectForm.value = false;
            },
            onError: (err) => {
                actionError.value = err.verifikasi || 'Gagal menyetujui pengajuan.';
            },
            onFinish: () => {
                isSubmitting.value = false;
            },
        }
    );
};

const handleReject = () => {
    if (!selectedSubmission.value || !selectedSubmission.value.has_ktm) return;

    if (rejectReason.value.trim().length < 5) {
        actionError.value = 'Alasan penolakan minimal 5 karakter.';
        return;
    }

    isSubmitting.value = true;
    actionError.value = null;

    router.post(
        reject.url(selectedSubmission.value.id),
        {
            alasan_penolakan: rejectReason.value.trim(),
            document_token: selectedSubmission.value.document_token,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                showRejectForm.value = false;
                rejectReason.value = '';
            },
            onError: (err) => {
                actionError.value = err.verifikasi || err.alasan_penolakan || 'Gagal menolak pengajuan.';
            },
            onFinish: () => {
                isSubmitting.value = false;
            },
        }
    );
};

// Date Formatters
const formattedToday = computed(() => {
    return new Intl.DateTimeFormat('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(new Date());
});

const formattedCurrentMonth = computed(() => {
    return new Intl.DateTimeFormat('id-ID', {
        month: 'long',
        year: 'numeric',
    }).format(new Date());
});

// Helper for initials avatar
const getInitials = (name: string) => {
    const parts = name.trim().split(/\s+/);
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return parts[0].slice(0, 2).toUpperCase();
};
</script>

<template>
    <Head title="Admin Dashboard - KLiK Marketplace Laptop PNP" />

    <!--
      Layout Utama Administrator KLiK (Sesuai Referensi Dashboard Admin.png, Validasi Akun.png, & Validasi Akun — Tolak.png):
      - Background Dark Navy: #111620
      - Sidebar: #0B101A dengan Border #242D3B
      - Panels: #0A0F19 / #121622
      - Aksen Utama: Hijau Mint #00EFA5 / #00FFA3 & Hijau Mint Gelap #07352E
    -->
    <div class="min-h-screen bg-[#111620] text-[#F7F9FB] font-sans antialiased flex flex-col">
        <!-- ======================= SIDEBAR (DESKTOP & MOBILE DRAWER) ======================= -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-40 w-64 bg-[#0B101A] border-r border-[#242D3B]/70 flex flex-col justify-between transition-transform duration-300 ease-in-out',
                mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
            ]"
        >
            <!-- Bagian Atas Sidebar: Logo & Menu Navigasi -->
            <div class="p-6">
                <!-- Logo KLiK Sesuai Gambar Referensi -->
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-[#00EFA5] text-[#0A0D14] shadow-[0_0_20px_rgba(0,239,165,0.4)]">
                        <!-- Mouse cursor icon persis seperti di gambar -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 stroke-[2.3]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                        </svg>
                    </div>
                    <span class="text-2xl font-black tracking-tight text-white leading-none">
                        KL<span class="text-[#00EFA5]">i</span>K
                    </span>
                </div>

                <!-- Label ADMIN DASHBOARD -->
                <div class="mt-8 mb-4">
                    <span class="text-[10px] font-bold tracking-wider text-slate-500 uppercase">
                        ADMIN DASHBOARD
                    </span>
                </div>

                <!-- Daftar Menu Navigasi -->
                <nav class="space-y-1.5">
                    <!-- 1. Ringkasan -->
                    <button
                        type="button"
                        @click="setTab('ringkasan')"
                        :class="[
                            'w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 cursor-pointer',
                            currentTab === 'ringkasan'
                                ? 'bg-[#07352E] text-[#00EFA5] shadow-xs'
                                : 'text-slate-300 hover:text-white hover:bg-white/5'
                        ]"
                    >
                        <div class="flex items-center gap-3">
                            <!-- Icon Grid 4 Kotak -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 stroke-[1.8]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <rect x="3" y="3" width="7" height="7" rx="1.5" stroke-width="2" />
                                <rect x="14" y="3" width="7" height="7" rx="1.5" stroke-width="2" />
                                <rect x="14" y="14" width="7" height="7" rx="1.5" stroke-width="2" />
                                <rect x="3" y="14" width="7" height="7" rx="1.5" stroke-width="2" />
                            </svg>
                            <span>Ringkasan</span>
                        </div>
                    </button>

                    <!-- 2. Validasi Akun -->
                    <button
                        type="button"
                        @click="setTab('validasi')"
                        :class="[
                            'w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 cursor-pointer',
                            currentTab === 'validasi'
                                ? 'bg-[#07352E] text-[#00EFA5] shadow-xs'
                                : 'text-slate-300 hover:text-white hover:bg-white/5'
                        ]"
                    >
                        <div class="flex items-center gap-3">
                            <!-- Icon Shield Check -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 stroke-[1.8]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            <span>Validasi Akun</span>
                        </div>
                        <!-- Badge Counter Menunggu Verifikasi -->
                        <span
                            v-if="stats.total_menunggu > 0"
                            :class="[
                                'px-2 py-0.5 rounded-full text-[11px] font-bold',
                                currentTab === 'validasi'
                                    ? 'bg-[#00EFA5]/20 text-[#00EFA5]'
                                    : 'bg-white/10 text-slate-400'
                            ]"
                        >
                            {{ stats.total_menunggu }}
                        </span>
                    </button>

                    <!-- Menu Tambahan Desain (Moderasi Lapak, Kelola Paket, Transaksi, Kategori) Sesuai Gambar -->
                    <!-- Sesuai PRD Batasan Timeline, menu ini ditandai non-aktif tanpa membuat navigasi palsu -->
                    <div class="pt-2 space-y-1 opacity-45 select-none">
                        <div class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs text-slate-400 cursor-not-allowed">
                            <div class="flex items-center gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Moderasi Lapak</span>
                            </div>
                            <span class="text-[10px] text-slate-500 font-mono">3</span>
                        </div>

                        <div class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs text-slate-400 cursor-not-allowed">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <span>Kelola Paket</span>
                        </div>

                        <div class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs text-slate-400 cursor-not-allowed">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span>Transaksi</span>
                        </div>

                        <div class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs text-slate-400 cursor-not-allowed">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                            <span>Kategori &amp; Merek</span>
                        </div>
                    </div>
                </nav>
            </div>

            <!-- Bagian Bawah Sidebar: Card Mahasiswa PNP & Profil Admin -->
            <div class="p-6 space-y-4 border-t border-[#242D3B]/40">
                <!-- Info Card PNP Sesuai Gambar -->
                <div class="rounded-2xl border border-[#242D3B]/70 bg-[#0E1422] p-4 flex items-center gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#00EFA5]/10 text-[#00EFA5]">
                        <!-- Graduation cap icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5" />
                        </svg>
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-xs font-bold text-white truncate">Khusus mahasiswa PNP</p>
                        <p class="text-[10px] text-slate-400 truncate">Politeknik Negeri Padang</p>
                    </div>
                </div>

                <!-- Profil Admin & Logout -->
                <div class="flex items-center justify-between pt-1">
                    <div class="flex items-center gap-2.5 overflow-hidden">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#07352E] border border-[#00EFA5]/30 text-xs font-bold text-[#00EFA5]">
                            {{ adminInitials }}
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-xs font-bold text-white truncate">{{ adminName }}</p>
                            <p class="text-[10px] text-slate-400">Administrator</p>
                        </div>
                    </div>

                    <!-- Tombol Keluar Logout -->
                    <Link
                        :href="logout.url()"
                        method="post"
                        as="button"
                        class="p-2 text-slate-400 hover:text-rose-400 transition-colors cursor-pointer"
                        title="Keluar dari akun admin"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </Link>
                </div>
            </div>
        </aside>

        <!-- Backdrop overlay untuk mobile saat drawer sidebar terbuka -->
        <div
            v-if="mobileSidebarOpen"
            class="fixed inset-0 z-30 bg-black/60 backdrop-blur-xs lg:hidden"
            @click="mobileSidebarOpen = false"
        />

        <!-- ======================= KONTEN UTAMA (HEADER + PAGES) ======================= -->
        <div class="lg:pl-64 flex flex-col flex-1 min-h-screen">
            <!-- HEADER ATAS (Persis Seperti Gambar Referensi) -->
            <header class="h-16 border-b border-[#242D3B]/60 bg-[#0B101A]/90 backdrop-blur-md px-4 sm:px-8 flex items-center justify-between sticky top-0 z-20">
                <!-- Sisi Kiri: Tombol Hamburger Mobile & Input Pencarian -->
                <div class="flex items-center gap-3 flex-1 max-w-md">
                    <!-- Hamburger button (khusus mobile) -->
                    <button
                        type="button"
                        class="p-2 text-slate-400 hover:text-white lg:hidden cursor-pointer"
                        @click="mobileSidebarOpen = !mobileSidebarOpen"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <!-- Search Input Bergaya Charcoal Sesuai Gambar -->
                    <div class="relative w-full hidden sm:block">
                        <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input
                            v-model="search"
                            type="text"
                            @keyup.enter="applyFilters"
                            placeholder="Cari akun, NIM, atau data mahasiswa..."
                            class="w-full h-10 rounded-xl border border-[#242D3B]/80 bg-[#131925] pl-10 pr-4 text-xs text-white placeholder-slate-500 focus:border-[#00EFA5] focus:outline-hidden transition-colors"
                        />
                    </div>
                </div>

                <!-- Sisi Kanan: Tanggal Aktual, Notifikasi, & Profil Admin Sesuai Gambar -->
                <div class="flex items-center gap-4 sm:gap-6">
                    <!-- Tanggal Aktual Format Indonesia (cth: "Sabtu, 24 Oktober 2026") -->
                    <span class="text-xs text-slate-400 hidden md:block">
                        {{ formattedToday }}
                    </span>

                    <!-- Ikon Notifikasi dengan Titik Indikator Hijau Mint -->
                    <button type="button" class="relative p-2 text-slate-400 hover:text-white transition-colors cursor-pointer" title="Notifikasi aktivitas">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span class="absolute top-2 right-2 h-2 w-2 rounded-full bg-[#00EFA5]" />
                    </button>

                    <div class="h-5 w-px bg-[#242D3B]/80 hidden sm:block" />

                    <!-- Avatar Admin -->
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[#07352E] text-xs font-bold text-[#00EFA5] border border-[#00EFA5]/30">
                            {{ adminInitials }}
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400 hidden sm:block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </header>

            <!-- NOTIFIKASI FLASH & ERROR GLOBAL -->
            <div class="px-4 sm:px-8 pt-4">
                <div
                    v-if="status"
                    class="rounded-2xl border border-[#00EFA5]/30 bg-[#00EFA5]/10 p-4 text-xs font-semibold text-[#00EFA5] flex items-center justify-between shadow-[0_0_20px_rgba(0,239,165,0.15)]"
                >
                    <div class="flex items-center gap-2.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ status }}</span>
                    </div>
                </div>

                <div
                    v-if="errors?.verifikasi || actionError"
                    class="mt-2 rounded-2xl border border-rose-500/30 bg-rose-500/10 p-4 text-xs font-semibold text-rose-300 flex items-center gap-2.5"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ errors?.verifikasi || actionError }}</span>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- TAB 1: HALAMAN RINGKASAN ADMIN (SESUAI DOKUMEN Dashboard Admin.png)       -->
            <!-- ========================================================================= -->
            <main v-if="currentTab === 'ringkasan'" class="p-4 sm:p-8 flex-1 space-y-8">
                <!-- Heading Ringkasan & Bulan Aktif -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-black text-white tracking-tight">Ringkasan</h1>
                        <p class="text-xs sm:text-sm text-slate-400 mt-1">
                            Selamat datang, Admin. Berikut aktivitas marketplace KLiK hari ini.
                        </p>
                    </div>

                    <!-- Month Picker Pill Sesuai Gambar Referensi -->
                    <div class="inline-flex items-center gap-2 rounded-xl border border-[#242D3B]/80 bg-[#131925] px-4 py-2 text-xs font-semibold text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>{{ formattedCurrentMonth }}</span>
                    </div>
                </div>

                <!-- 4 KARTU STATISTIK DENGAN DATA AKTUAL DARI DATABASE -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Kartu 1: Akun Menunggu Verifikasi -->
                    <div class="rounded-3xl border border-[#242D3B]/70 bg-[#0E1422] p-6 space-y-4 hover:border-[#00EFA5]/40 transition-colors">
                        <div class="flex items-center justify-between text-xs font-semibold text-slate-400">
                            <span>Akun menunggu verifikasi</span>
                            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#00EFA5]/10 text-[#00EFA5]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="text-4xl font-black text-white tracking-tight">
                            {{ stats.total_menunggu }}
                        </div>
                        <p class="text-xs text-slate-400">
                            <span class="text-[#00EFA5] font-semibold">{{ stats.siap_verifikasi }} akun</span> siap ditinjau hari ini
                        </p>
                    </div>

                    <!-- Kartu 2: Menunggu Dokumen KTM -->
                    <div class="rounded-3xl border border-[#242D3B]/70 bg-[#0E1422] p-6 space-y-4 hover:border-[#00EFA5]/40 transition-colors">
                        <div class="flex items-center justify-between text-xs font-semibold text-slate-400">
                            <span>Menunggu upload KTM</span>
                            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#00EFA5]/10 text-[#00EFA5]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                        <div class="text-4xl font-black text-white tracking-tight">
                            {{ stats.menunggu_ktm }}
                        </div>
                        <p class="text-xs text-slate-400">
                            Mahasiswa belum mengunggah KTM
                        </p>
                    </div>

                    <!-- Kartu 3: Akun Disetujui -->
                    <div class="rounded-3xl border border-[#242D3B]/70 bg-[#0E1422] p-6 space-y-4 hover:border-[#00EFA5]/40 transition-colors">
                        <div class="flex items-center justify-between text-xs font-semibold text-slate-400">
                            <span>Akun terverifikasi</span>
                            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#00EFA5]/10 text-[#00EFA5]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                        </div>
                        <div class="text-4xl font-black text-white tracking-tight">
                            {{ stats.total_disetujui }}
                        </div>
                        <p class="text-xs text-slate-400">
                            Sivitas akademika aktif disetujui
                        </p>
                    </div>

                    <!-- Kartu 4: Akun Ditolak (Perlu Perbaikan) -->
                    <div class="rounded-3xl border border-[#242D3B]/70 bg-[#0E1422] p-6 space-y-4 hover:border-[#00EFA5]/40 transition-colors">
                        <div class="flex items-center justify-between text-xs font-semibold text-slate-400">
                            <span>Pengajuan ditolak</span>
                            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#00EFA5]/10 text-[#00EFA5]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                        </div>
                        <div class="text-4xl font-black text-white tracking-tight">
                            {{ stats.total_ditolak }}
                        </div>
                        <p class="text-xs text-amber-400">
                            Perlu perbaikan foto KTM oleh user
                        </p>
                    </div>
                </div>

                <!-- 2 PANEL BESAR BAWAH (Antrean Validasi & Riwayat Proses Terakhir) -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Panel 1: Antrean Validasi Sesuai Mockup Gambar -->
                    <div class="rounded-3xl border border-[#242D3B]/70 bg-[#0E1422] p-6 sm:p-7 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h2 class="text-lg font-bold text-white">Antrean validasi</h2>
                                    <p class="text-xs text-slate-400 mt-0.5">
                                        {{ stats.total_menunggu }} akun menunggu pemeriksaan KTM
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    @click="setTab('validasi')"
                                    class="inline-flex items-center gap-1.5 text-xs font-bold text-[#00EFA5] hover:underline cursor-pointer"
                                >
                                    <span>Lihat semua</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Daftar Item Antrean Validasi -->
                            <div class="divide-y divide-[#242D3B]/40">
                                <div
                                    v-for="item in submissions.data.slice(0, 4)"
                                    :key="item.id"
                                    @click="selectSubmission(item); setTab('validasi')"
                                    class="py-3.5 flex items-center justify-between hover:bg-white/5 px-2 rounded-xl transition-colors cursor-pointer group"
                                >
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#07352E] text-xs font-bold text-[#00EFA5] border border-[#00EFA5]/30">
                                            {{ getInitials(item.name) }}
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-white group-hover:text-[#00EFA5] transition-colors">
                                                {{ item.name }}
                                            </p>
                                            <p class="text-[11px] text-slate-400">
                                                {{ item.prodi_name || (item.kode_prodi_tidak_dikenal ? 'Kode Prodi Belum Terdaftar' : 'Prodi PNP') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs text-slate-400">
                                            {{ item.created_at || item.updated_at || '-' }}
                                        </span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-500 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </div>
                                </div>

                                <div v-if="submissions.data.length === 0" class="py-12 text-center text-xs text-slate-400">
                                    Tidak ada pengajuan verifikasi dalam antrean.
                                </div>
                            </div>
                        </div>

                        <!-- Footer Info -->
                        <div class="pt-4 border-t border-[#242D3B]/40 text-[11px] text-slate-500">
                            Prioritas verifikasi diberikan pada mahasiswa yang telah mengunggah berkas foto KTM.
                        </div>
                    </div>

                    <!-- Panel 2: Panduan & Informasi Operasional Verifikasi Akun -->
                    <div class="rounded-3xl border border-[#242D3B]/70 bg-[#0E1422] p-6 sm:p-7 flex flex-col justify-between">
                        <div>
                            <div class="mb-4">
                                <h2 class="text-lg font-bold text-white">Panduan Validasi Kampus</h2>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    Ketentuan verifikasi identitas civitas akademika Politeknik Negeri Padang
                                </p>
                            </div>

                            <div class="space-y-3.5">
                                <div class="rounded-2xl border border-white/5 bg-[#121622] p-4 space-y-1.5">
                                    <div class="flex items-center gap-2 text-xs font-bold text-[#00EFA5]">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>Kesesuaian NIM 10 Digit &amp; Nama</span>
                                    </div>
                                    <p class="text-[11px] text-slate-400 leading-relaxed">
                                        Pastikan 10 digit NIM dan nama lengkap terdaftar sesuai dengan kartu tanda mahasiswa fisik yang diterbitkan resmi oleh PNP.
                                    </p>
                                </div>

                                <div class="rounded-2xl border border-white/5 bg-[#121622] p-4 space-y-1.5">
                                    <div class="flex items-center gap-2 text-xs font-bold text-[#00EFA5]">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>Kejelasan Foto KTM</span>
                                    </div>
                                    <p class="text-[11px] text-slate-400 leading-relaxed">
                                        Foto KTM tidak boleh terpotong, buram, ataupun terkena pantulan cahaya tajam. Jika tidak terbaca, gunakan tombol tolak dengan alasan jelas.
                                    </p>
                                </div>

                                <div class="rounded-2xl border border-white/5 bg-[#121622] p-4 space-y-1.5">
                                    <div class="flex items-center gap-2 text-xs font-bold text-[#00EFA5]">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>Perlindungan Privasi Berkas</span>
                                    </div>
                                    <p class="text-[11px] text-slate-400 leading-relaxed">
                                        File KTM hanya dapat diakses melalui token sesi terproteksi khusus administrator KLiK untuk mencegah kebocoran data pribadi mahasiswa.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Catatan Waktu di Bawah Panel -->
                        <div class="pt-4 border-t border-[#242D3B]/40 flex items-center gap-2 text-xs text-amber-400 font-medium">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ stats.siap_verifikasi }} pengajuan siap diputuskan hari ini.</span>
                        </div>
                    </div>
                </div>

                <!-- Footer Dashboard Sesuai Gambar -->
                <div class="pt-4 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-2">
                    <p>Data diperbarui {{ formattedToday }}</p>
                    <p>KLiK &bull; Politeknik Negeri Padang</p>
                </div>
            </main>

            <!-- ========================================================================= -->
            <!-- TAB 2: HALAMAN VALIDASI AKUN (SESUAI Validasi Akun.png & Tolak.png)       -->
            <!-- ========================================================================= -->
            <main v-else-if="currentTab === 'validasi'" class="p-4 sm:p-8 flex-1 space-y-6">
                <!-- Heading Validasi Akun & Counter Badge -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-black text-white tracking-tight">Validasi Akun</h1>
                        <p class="text-xs sm:text-sm text-slate-400 mt-1">
                            Periksa KTM dan kecocokan data mahasiswa sebelum menyetujui akun.
                        </p>
                    </div>

                    <!-- Badge Counter Menunggu Sesuai Gambar -->
                    <div class="inline-flex items-center gap-2 rounded-full border border-amber-500/30 bg-amber-500/10 px-3.5 py-1.5 text-xs font-bold text-amber-400">
                        <span class="h-2 w-2 rounded-full bg-amber-400 animate-pulse" />
                        <span>{{ stats.total_menunggu }} menunggu</span>
                    </div>
                </div>

                <!-- GRID 2 KOLOM (KIRI: ANTREAN VALIDASI, KANAN: DETAIL MAHASISWA) -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                    <!-- =================== KOLOM KIRI (lg:col-span-6 xl:col-span-5): ANTREAN VALIDASI =================== -->
                    <div class="lg:col-span-6 xl:col-span-5 rounded-3xl border border-[#242D3B]/70 bg-[#0E1422] p-5 sm:p-6 space-y-4">
                        <!-- Header Antrean -->
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-base font-bold text-white">Antrean validasi</h2>
                                <p class="text-[11px] text-slate-400">Urutan berdasarkan tanggal daftar terbaru</p>
                            </div>
                            <button
                                type="button"
                                @click="applyFilters"
                                class="p-1.5 text-slate-400 hover:text-white transition-colors cursor-pointer"
                                title="Muat ulang antrean"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </button>
                        </div>

                        <!-- Search Box di Panel Antrean Sesuai Gambar -->
                        <div class="relative">
                            <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input
                                v-model="search"
                                type="text"
                                @keyup.enter="applyFilters"
                                placeholder="Cari nama atau NIM..."
                                class="w-full h-9 rounded-xl border border-[#242D3B]/80 bg-[#131925] pl-9 pr-3 text-xs text-white placeholder-slate-500 focus:border-[#00EFA5] focus:outline-hidden transition-colors"
                            />
                        </div>

                        <!-- Filter Status Pills Cepat -->
                        <div class="flex items-center gap-1.5 pt-1 overflow-x-auto pb-1 text-xs">
                            <button
                                type="button"
                                @click="statusFilter = 'menunggu'; applyFilters()"
                                :class="[
                                    'px-2.5 py-1 rounded-lg font-medium transition cursor-pointer text-[11px]',
                                    statusFilter === 'menunggu'
                                        ? 'bg-[#00EFA5]/15 text-[#00EFA5] border border-[#00EFA5]/30'
                                        : 'text-slate-400 hover:text-white bg-white/5'
                                ]"
                            >
                                Menunggu ({{ stats.total_menunggu }})
                            </button>
                            <button
                                type="button"
                                @click="statusFilter = 'disetujui'; applyFilters()"
                                :class="[
                                    'px-2.5 py-1 rounded-lg font-medium transition cursor-pointer text-[11px]',
                                    statusFilter === 'disetujui'
                                        ? 'bg-[#00EFA5]/15 text-[#00EFA5] border border-[#00EFA5]/30'
                                        : 'text-slate-400 hover:text-white bg-white/5'
                                ]"
                            >
                                Disetujui ({{ stats.total_disetujui }})
                            </button>
                            <button
                                type="button"
                                @click="statusFilter = 'ditolak'; applyFilters()"
                                :class="[
                                    'px-2.5 py-1 rounded-lg font-medium transition cursor-pointer text-[11px]',
                                    statusFilter === 'ditolak'
                                        ? 'bg-[#00EFA5]/15 text-[#00EFA5] border border-[#00EFA5]/30'
                                        : 'text-slate-400 hover:text-white bg-white/5'
                                ]"
                            >
                                Ditolak ({{ stats.total_ditolak }})
                            </button>
                        </div>

                        <!-- Tabel Antrean Sesuai Gambar Validasi Akun.png -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="border-b border-[#242D3B]/60 text-slate-400 font-semibold">
                                        <th class="pb-2.5 pl-2 font-medium">Nama</th>
                                        <th class="pb-2.5 px-2 font-medium">NIM</th>
                                        <th class="pb-2.5 px-2 font-medium">Prodi</th>
                                        <th class="pb-2.5 pr-2 font-medium">Tanggal daftar</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#242D3B]/30">
                                    <tr
                                        v-for="sub in submissions.data"
                                        :key="sub.id"
                                        @click="selectSubmission(sub)"
                                        :class="[
                                            'transition-all duration-150 cursor-pointer group',
                                            selectedSubmission?.id === sub.id
                                                ? 'bg-[#07352E]/90 text-[#00EFA5] font-semibold border-l-3 border-[#00EFA5]'
                                                : 'hover:bg-white/5 text-slate-200'
                                        ]"
                                    >
                                        <td class="py-3 pl-2 max-w-[120px] truncate">
                                            <span :class="selectedSubmission?.id === sub.id ? 'text-[#00EFA5]' : 'text-white font-medium'">
                                                {{ sub.name }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-2 font-mono text-[11px]">
                                            {{ sub.nim }}
                                        </td>
                                        <td class="py-3 px-2 max-w-[110px] truncate text-[11px] text-slate-400">
                                            {{ sub.prodi_name || (sub.kode_prodi_tidak_dikenal ? 'Prodi ?' : '-') }}
                                        </td>
                                        <td class="py-3 pr-2 whitespace-nowrap text-[11px] text-slate-400">
                                            {{ sub.created_at?.split(' ')[0] || sub.created_at || '-' }}
                                        </td>
                                    </tr>

                                    <tr v-if="submissions.data.length === 0">
                                        <td colspan="4" class="py-8 text-center text-xs text-slate-400">
                                            Tidak ada antrean mahasiswa yang cocok dengan filter.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination di Bawah Panel Antrean Sesuai Gambar -->
                        <div class="pt-3 border-t border-[#242D3B]/40 flex items-center justify-between text-xs text-slate-400">
                            <span>
                                {{ submissions.total > 0 ? (submissions.current_page * 15 - 14) : 0 }}–{{ Math.min(submissions.current_page * 15, submissions.total) }} dari {{ submissions.total }} akun
                            </span>
                            <div class="flex items-center gap-1.5">
                                <template v-for="(link, i) in submissions.links" :key="i">
                                    <Link
                                        v-if="link.url"
                                        :href="link.url"
                                        :class="[
                                            'h-7 min-w-7 px-2 flex items-center justify-center rounded-lg text-xs font-semibold transition',
                                            link.active
                                                ? 'bg-[#00EFA5] text-[#0A0D14]'
                                                : 'bg-[#131925] text-slate-400 hover:text-white'
                                        ]"
                                        v-html="link.label"
                                    />
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- =================== KOLOM KANAN (lg:col-span-6 xl:col-span-7): DETAIL MAHASISWA =================== -->
                    <div
                        v-if="selectedSubmission"
                        class="lg:col-span-6 xl:col-span-7 rounded-3xl border border-[#242D3B]/70 bg-[#0E1422] p-6 sm:p-8 space-y-6"
                    >
                        <!-- Header Detail Mahasiswa Sesuai Gambar -->
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-xl font-bold text-white">Detail mahasiswa</h2>
                                <p class="text-xs text-slate-400 mt-1">
                                    Diajukan {{ selectedSubmission.created_at || selectedSubmission.updated_at || '-' }} WIB
                                </p>
                            </div>

                            <!-- Badge Status Verifikasi di Kanan Atas -->
                            <span
                                v-if="selectedSubmission.status_verifikasi === 'menunggu'"
                                class="inline-flex items-center gap-1.5 rounded-full border border-amber-500/30 bg-amber-500/10 px-3 py-1 text-xs font-bold text-amber-400"
                            >
                                <span class="h-1.5 w-1.5 rounded-full bg-amber-400" />
                                <span>Menunggu</span>
                            </span>
                            <span
                                v-else-if="selectedSubmission.status_verifikasi === 'disetujui'"
                                class="inline-flex items-center gap-1.5 rounded-full border border-[#00EFA5]/30 bg-[#00EFA5]/10 px-3 py-1 text-xs font-bold text-[#00EFA5]"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Disetujui</span>
                            </span>
                            <span
                                v-else
                                class="inline-flex items-center gap-1.5 rounded-full border border-rose-500/30 bg-rose-500/10 px-3 py-1 text-xs font-bold text-rose-400"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <span>Ditolak</span>
                            </span>
                        </div>

                        <!-- 2 SUB-KOLOM (KIRI: KARTU TANDA MAHASISWA, KANAN: DATA TERDAFTAR) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                            <!-- Sub-Kolom 1: Kartu Tanda Mahasiswa (KTM) -->
                            <div class="space-y-2.5">
                                <h3 class="text-xs font-semibold text-slate-300">Kartu Tanda Mahasiswa</h3>

                                <div v-if="selectedSubmission.has_ktm" class="space-y-2">
                                    <!-- Bingkai Gambar KTM Persis Gambar Referensi -->
                                    <div class="relative overflow-hidden rounded-2xl border border-[#242D3B] bg-[#121622] p-2 group">
                                        <img
                                            :src="ktm.url(selectedSubmission.id)"
                                            alt="Foto KTM Mahasiswa"
                                            class="w-full h-44 object-contain rounded-xl bg-black/40 transition-transform duration-200 group-hover:scale-[1.02]"
                                        />
                                    </div>
                                    <div class="flex items-center justify-between text-[11px] text-slate-400 pt-0.5">
                                        <span>Dokumen KTM terverifikasi server</span>
                                        <!-- Tombol Perbesar Pratinjau Sesuai Gambar -->
                                        <button
                                            type="button"
                                            @click="zoomKtmOpen = true"
                                            class="inline-flex items-center gap-1 text-[#00EFA5] hover:underline cursor-pointer font-medium"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                            </svg>
                                            <span>Perbesar pratinjau</span>
                                        </button>
                                    </div>
                                </div>

                                <div v-else class="rounded-2xl border border-dashed border-[#242D3B] bg-[#121622] p-8 text-center text-xs text-slate-400">
                                    Mahasiswa belum mengunggah foto kartu tanda mahasiswa (KTM).
                                </div>
                            </div>

                            <!-- Sub-Kolom 2: Data Terdaftar Sesuai Gambar Referensi -->
                            <div class="space-y-3.5">
                                <h3 class="text-xs font-semibold text-slate-300">Data terdaftar</h3>

                                <div class="space-y-3 rounded-2xl border border-[#242D3B]/60 bg-[#121622] p-4 text-xs">
                                    <div>
                                        <span class="text-slate-400 text-[11px] block">Nama</span>
                                        <span class="font-bold text-white text-sm">{{ selectedSubmission.name }}</span>
                                    </div>

                                    <div>
                                        <span class="text-slate-400 text-[11px] block">NIM</span>
                                        <span class="font-mono font-bold text-white text-sm">{{ selectedSubmission.nim }}</span>
                                    </div>

                                    <div>
                                        <span class="text-slate-400 text-[11px] block">Prodi</span>
                                        <span class="font-semibold text-white">
                                            {{ selectedSubmission.prodi_name || (selectedSubmission.kode_prodi_tidak_dikenal ? 'Kode Prodi Tidak Dikenal' : '-') }}
                                        </span>
                                    </div>

                                    <div>
                                        <span class="text-slate-400 text-[11px] block">Status Civitas PNP</span>
                                        <span class="text-slate-200">
                                            {{ selectedSubmission.status_mahasiswa === 'aktif' ? 'Mahasiswa Aktif' : 'Alumni PNP' }}
                                            <span v-if="selectedSubmission.angkatan" class="text-slate-400"> (Angkatan {{ selectedSubmission.angkatan }})</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ================= AKSI 1: KONDISI SEBELUM MENYETUJUI (SESUAI Validasi Akun.png) ================= -->
                        <div
                            v-if="selectedSubmission.status_verifikasi === 'menunggu' && selectedSubmission.has_ktm && !showRejectForm"
                            class="space-y-5 pt-2"
                        >
                            <!-- Kotak Checklist "Sebelum menyetujui" Persis Gambar -->
                            <div class="rounded-2xl border border-[#242D3B]/60 bg-[#121622] p-5 space-y-3">
                                <h4 class="text-xs font-bold text-white">Sebelum menyetujui</h4>
                                <ul class="space-y-2.5 text-xs text-slate-300">
                                    <li class="flex items-center gap-2.5">
                                        <div class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#00EFA5]/15 text-[#00EFA5]">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <span>Nama dan NIM sesuai dengan KTM</span>
                                    </li>
                                    <li class="flex items-center gap-2.5">
                                        <div class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#00EFA5]/15 text-[#00EFA5]">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <span>KTM terbaca dan berasal dari PNP</span>
                                    </li>
                                    <li class="flex items-center gap-2.5">
                                        <div class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#00EFA5]/15 text-[#00EFA5]">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <span>Mahasiswa masih berstatus aktif atau alumni sah</span>
                                    </li>
                                </ul>
                            </div>

                            <!-- Baris Tombol Tolak & Setujui Sesuai Gambar -->
                            <div class="flex items-center gap-3">
                                <!-- Tombol Tolak Charcoal -->
                                <button
                                    type="button"
                                    @click="showRejectForm = true"
                                    class="flex-1 h-12 rounded-xl border border-[#242D3B] bg-[#131925] hover:bg-white/5 text-white font-bold text-xs transition-colors cursor-pointer"
                                >
                                    Tolak
                                </button>

                                <!-- Tombol Setujui Hijau Mint Solid -->
                                <button
                                    type="button"
                                    @click="openApproveModal"
                                    :disabled="isSubmitting"
                                    class="flex-1 h-12 rounded-xl bg-[#00EFA5] hover:bg-[#00df9a] text-[#0A0D14] font-black text-xs tracking-wide transition-all duration-200 shadow-[0_0_20px_rgba(0,239,165,0.35)] hover:shadow-[0_0_25px_rgba(0,239,165,0.45)] hover:scale-[1.01] active:scale-[0.99] disabled:opacity-50 flex items-center justify-center gap-2 cursor-pointer"
                                >
                                    <Spinner v-if="isSubmitting" />
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Setujui</span>
                                </button>
                            </div>

                            <p class="text-[11px] text-slate-500 text-center">
                                Keputusan verifikasi akan dikirim melalui notifikasi akun.
                            </p>
                        </div>

                        <!-- ================= AKSI 2: FORM PENOLAKAN (SESUAI Validasi Akun — Tolak.png) ================= -->
                        <div
                            v-else-if="selectedSubmission.status_verifikasi === 'menunggu' && selectedSubmission.has_ktm && showRejectForm"
                            class="space-y-4 pt-2"
                        >
                            <div class="space-y-1.5">
                                <label for="reject-reason" class="text-xs font-semibold text-white block">
                                    Alasan penolakan *
                                </label>
                                <textarea
                                    id="reject-reason"
                                    v-model="rejectReason"
                                    rows="4"
                                    placeholder="Jelaskan data atau bagian KTM yang perlu diperbaiki..."
                                    class="w-full rounded-2xl border border-[#242D3B] bg-[#131925] p-3.5 text-xs text-white placeholder-slate-500 focus:border-[#00EFA5] focus:outline-hidden transition-colors"
                                />
                                <p class="text-[11px] text-slate-400">
                                    Wajib diisi. Alasan ini akan dikirim kepada mahasiswa.
                                </p>
                            </div>

                            <!-- Tombol Batal & Tolak Sesuai Gambar Referensi -->
                            <div class="flex items-center gap-3 pt-1">
                                <!-- Tombol Batal Charcoal -->
                                <button
                                    type="button"
                                    @click="showRejectForm = false; rejectReason = ''; actionError = null"
                                    class="flex-1 h-12 rounded-xl border border-[#242D3B] bg-[#131925] hover:bg-white/5 text-white font-bold text-xs transition-colors cursor-pointer"
                                >
                                    Batal
                                </button>

                                <!-- Tombol Tolak Hijau Mint Solid Sesuai Gambar Tolak -->
                                <button
                                    type="button"
                                    @click="handleReject"
                                    :disabled="isSubmitting || rejectReason.trim().length < 5"
                                    class="flex-1 h-12 rounded-xl bg-[#00EFA5] hover:bg-[#00df9a] text-[#0A0D14] font-black text-xs tracking-wide transition-all duration-200 shadow-[0_0_20px_rgba(0,239,165,0.35)] hover:shadow-[0_0_25px_rgba(0,239,165,0.45)] hover:scale-[1.01] active:scale-[0.99] disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 cursor-pointer"
                                >
                                    <Spinner v-if="isSubmitting" />
                                    <span>Tolak</span>
                                </button>
                            </div>

                            <p class="text-[11px] text-slate-500 text-center">
                                * Alasan penolakan wajib diisi sebelum mengirim keputusan.
                            </p>
                        </div>

                        <!-- Info jika akun sudah disetujui / ditolak sebelumnya -->
                        <div v-else-if="selectedSubmission.status_verifikasi === 'disetujui'" class="rounded-2xl border border-[#00EFA5]/30 bg-[#00EFA5]/10 p-4 text-xs text-[#00EFA5]">
                            Akun mahasiswa ini telah diverifikasi dan disetujui pada <strong>{{ selectedSubmission.verified_at || '-' }}</strong> oleh <strong>{{ selectedSubmission.verifier_name || 'Admin PNP' }}</strong>.
                        </div>

                        <div v-else-if="selectedSubmission.status_verifikasi === 'ditolak'" class="rounded-2xl border border-rose-500/30 bg-rose-500/10 p-4 space-y-1.5 text-xs text-rose-300">
                            <p class="font-bold">Pengajuan verifikasi telah ditolak pada {{ selectedSubmission.verified_at || '-' }}.</p>
                            <p class="text-[11px] text-slate-300">Alasan: {{ selectedSubmission.alasan_penolakan || '-' }}</p>
                        </div>
                    </div>

                    <!-- Placeholder jika tidak ada akun yang dipilih -->
                    <div v-else class="lg:col-span-6 xl:col-span-7 rounded-3xl border border-[#242D3B]/70 bg-[#0E1422] p-12 text-center text-slate-400 text-xs">
                        Pilih salah satu mahasiswa dari antrean di sebelah kiri untuk melihat detail berkas KTM.
                    </div>
                </div>
            </main>
        </div>

        <!-- ======================= CUSTOM CONFIRMATION MODAL PERSETUJUAN ======================= -->
        <Teleport to="body">
            <div
                v-if="approveModalOpen && selectedSubmission"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md"
                role="dialog"
                aria-modal="true"
                aria-labelledby="approve-modal-title"
                @click.self="closeApproveModal"
            >
                <div
                    class="relative w-full max-w-md rounded-3xl border border-[#293443] bg-[#0E1422] p-6 sm:p-7 shadow-2xl space-y-5 animate-in fade-in zoom-in-95 duration-150"
                >
                    <!-- Header Modal & Icon Shield Check Mint -->
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#00EFA5]/15 text-[#00EFA5] border border-[#00EFA5]/30">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 stroke-[2.2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div class="space-y-1">
                            <h3 id="approve-modal-title" class="text-lg font-bold text-white tracking-tight">
                                Setujui Verifikasi Akun?
                            </h3>
                            <p class="text-xs text-[#A7B1C0] leading-relaxed">
                                Pastikan berkas KTM telah sesuai dengan identitas civitas akademika Politeknik Negeri Padang. Akun akan langsung aktif setelah disetujui.
                            </p>
                        </div>
                    </div>

                    <!-- Kotak Informasi Mahasiswa yang Dipilih -->
                    <div class="rounded-2xl border border-[#293443] bg-[#141D2A] p-4 space-y-2.5 text-xs">
                        <div class="flex justify-between items-center">
                            <span class="text-[#A7B1C0]">Nama Mahasiswa:</span>
                            <span class="font-bold text-white">{{ selectedSubmission.name }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-[#A7B1C0]">NIM:</span>
                            <span class="font-mono font-bold text-white">{{ selectedSubmission.nim }}</span>
                        </div>
                        <div v-if="selectedSubmission.prodi_name" class="flex justify-between items-center border-t border-white/5 pt-2">
                            <span class="text-[#A7B1C0]">Program Studi:</span>
                            <span class="font-medium text-slate-200 text-right truncate max-w-[200px]">{{ selectedSubmission.prodi_name }}</span>
                        </div>
                    </div>

                    <!-- Pesan Error Aksi jika ada -->
                    <div
                        v-if="actionError"
                        class="rounded-xl border border-rose-500/30 bg-rose-500/10 p-3 text-xs text-rose-300 font-medium"
                    >
                        {{ actionError }}
                    </div>

                    <!-- Baris Tombol Batal & Ya, Setujui -->
                    <div class="flex items-center gap-3 pt-1">
                        <button
                            type="button"
                            @click="closeApproveModal"
                            :disabled="isSubmitting"
                            class="flex-1 h-11 rounded-xl border border-[#293443] bg-[#141D2A] hover:bg-white/5 text-white font-semibold text-xs transition cursor-pointer disabled:opacity-50"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            @click="executeApprove"
                            :disabled="isSubmitting"
                            class="flex-1 h-11 rounded-xl bg-[#00EFA5] hover:bg-[#00df9a] text-[#0A0D14] font-black text-xs tracking-wide transition shadow-[0_0_20px_rgba(0,239,165,0.35)] hover:shadow-[0_0_25px_rgba(0,239,165,0.5)] flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
                        >
                            <Spinner v-if="isSubmitting" />
                            <span>{{ isSubmitting ? 'Memproses...' : 'Ya, Setujui' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- ======================= MODAL PERBESAR PRATINJAU KTM ======================= -->
        <Teleport to="body">
            <div
                v-if="zoomKtmOpen && selectedSubmission?.has_ktm"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-md p-4"
                role="dialog"
                aria-modal="true"
                @click.self="zoomKtmOpen = false"
            >
                <div class="relative max-w-4xl w-full rounded-3xl border border-[#242D3B] bg-[#0E1422] p-6 shadow-2xl space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-[#242D3B]">
                        <div>
                            <h3 class="text-sm font-bold text-white">Pratinjau Kartu Tanda Mahasiswa (KTM)</h3>
                            <p class="text-xs text-slate-400 font-mono mt-0.5">{{ selectedSubmission.name }} &bull; NIM: {{ selectedSubmission.nim }}</p>
                        </div>
                        <button
                            type="button"
                            @click="zoomKtmOpen = false"
                            class="p-2 text-slate-400 hover:text-white rounded-full bg-white/5 hover:bg-white/10 transition-colors cursor-pointer"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="overflow-hidden rounded-2xl bg-black flex items-center justify-center p-2 max-h-[75vh]">
                        <img
                            :src="ktm.url(selectedSubmission.id)"
                            alt="Foto KTM Ukuran Penuh"
                            class="max-h-[70vh] w-auto object-contain rounded-xl"
                        />
                    </div>

                    <div class="flex items-center justify-between text-xs text-slate-400 pt-1">
                        <span>Dokumen dilindungi otorisasi streaming privat KLiK.</span>
                        <button
                            type="button"
                            @click="zoomKtmOpen = false"
                            class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white font-semibold cursor-pointer"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>
