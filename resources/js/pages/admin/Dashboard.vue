<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Button } from '@/components/ui/button';
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

// Filter states
const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || 'menunggu');
const ktmFilter = ref(props.filters.has_ktm || 'semua');

const applyFilters = () => {
    router.get(
        dashboard.url(),
        {
            status: statusFilter.value,
            has_ktm: ktmFilter.value,
            search: search.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

const resetFilters = () => {
    search.value = '';
    statusFilter.value = 'menunggu';
    ktmFilter.value = 'semua';
    applyFilters();
};

// Modal Review State
const selectedSubmission = ref<Submission | null>(null);
const showRejectForm = ref(false);
const rejectReason = ref('');
const isSubmitting = ref(false);
const actionError = ref<string | null>(null);

const openReviewModal = (sub: Submission) => {
    selectedSubmission.value = sub;
    showRejectForm.value = false;
    rejectReason.value = '';
    actionError.value = null;
};

const closeReviewModal = () => {
    if (isSubmitting.value) return;
    selectedSubmission.value = null;
    showRejectForm.value = false;
    rejectReason.value = '';
    actionError.value = null;
};

const handleApprove = () => {
    if (!selectedSubmission.value || !selectedSubmission.value.has_ktm) return;

    if (!confirm(`Apakah Anda yakin ingin MENYETUJUI verifikasi akun NIM ${selectedSubmission.value.nim} (${selectedSubmission.value.name})?`)) {
        return;
    }

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
                closeReviewModal();
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
                closeReviewModal();
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
</script>

<template>
    <div class="flex min-h-screen flex-col bg-muted/20 text-foreground">
        <Head title="Panel Verifikasi KTM - Administrator KLiK" />

        <!-- Top Header Navigation -->
        <header class="border-b bg-card px-6 py-4 shadow-xs">
            <div class="mx-auto flex max-w-7xl items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary text-primary-foreground shadow-xs font-bold text-lg">
                        K
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-lg font-bold tracking-tight text-foreground sm:text-xl">KLiK Administrator</h1>
                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300">
                                Panel Verifikasi
                            </span>
                        </div>
                        <p class="text-xs text-muted-foreground">Verifikasi Identitas Mahasiswa & Alumni Politeknik Negeri Padang</p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="hidden text-right sm:block">
                        <span class="block text-xs font-semibold text-foreground">Administrator PNP</span>
                        <span class="block text-[11px] text-muted-foreground">Sesi Terautentikasi</span>
                    </div>
                    <Link :href="logout.url()" method="post" as="button">
                        <Button variant="outline" size="sm" class="cursor-pointer">
                            Keluar
                        </Button>
                    </Link>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="mx-auto w-full max-w-7xl flex-1 p-4 sm:p-6 lg:p-8">
            <!-- Flash Notification -->
            <div
                v-if="status"
                class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50/70 p-4 text-xs font-medium text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/20 dark:text-emerald-300 flex items-center justify-between"
            >
                <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-emerald-600 dark:text-emerald-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ status }}</span>
                </div>
            </div>

            <!-- Global Error Banner -->
            <div
                v-if="errors?.verifikasi"
                class="mb-6 rounded-xl border border-rose-200 bg-rose-50/70 p-4 text-xs font-medium text-rose-800 dark:border-rose-900/50 dark:bg-rose-950/20 dark:text-rose-300 flex items-center gap-2"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-rose-600 dark:text-rose-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <span>{{ errors.verifikasi }}</span>
            </div>

            <!-- Stats Overview Cards -->
            <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                <div class="rounded-xl border bg-card p-4 shadow-xs">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Menunggu Verifikasi</span>
                    <div class="mt-1 flex items-baseline gap-2">
                        <span class="text-2xl font-extrabold text-amber-600 dark:text-amber-400">{{ stats.total_menunggu }}</span>
                        <span class="text-[10px] text-muted-foreground">total antrean</span>
                    </div>
                </div>

                <div class="rounded-xl border bg-card p-4 shadow-xs border-amber-300/40 bg-amber-50/20">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-amber-900 dark:text-amber-300">Siap Ditinjau</span>
                    <div class="mt-1 flex items-baseline gap-2">
                        <span class="text-2xl font-extrabold text-amber-600 dark:text-amber-400">{{ stats.siap_verifikasi }}</span>
                        <span class="text-[10px] text-muted-foreground">KTM ada</span>
                    </div>
                </div>

                <div class="rounded-xl border bg-card p-4 shadow-xs">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Menunggu KTM</span>
                    <div class="mt-1 flex items-baseline gap-2">
                        <span class="text-2xl font-extrabold text-muted-foreground">{{ stats.menunggu_ktm }}</span>
                        <span class="text-[10px] text-muted-foreground">belum upload</span>
                    </div>
                </div>

                <div class="rounded-xl border bg-card p-4 shadow-xs">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Akun Disetujui</span>
                    <div class="mt-1 flex items-baseline gap-2">
                        <span class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">{{ stats.total_disetujui }}</span>
                        <span class="text-[10px] text-muted-foreground">terverifikasi</span>
                    </div>
                </div>

                <div class="rounded-xl border bg-card p-4 shadow-xs">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Akun Ditolak</span>
                    <div class="mt-1 flex items-baseline gap-2">
                        <span class="text-2xl font-extrabold text-rose-600 dark:text-rose-400">{{ stats.total_ditolak }}</span>
                        <span class="text-[10px] text-muted-foreground">perlu perbaikan</span>
                    </div>
                </div>
            </div>

            <!-- Filters and Search Toolbar -->
            <div class="mb-4 rounded-xl border bg-card p-4 shadow-xs">
                <form @submit.prevent="applyFilters" class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center">
                        <!-- Search Box -->
                        <div class="relative flex-1">
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Cari berdasarkan NIM atau Nama..."
                                class="w-full rounded-lg border border-input bg-background px-3 py-2 text-xs placeholder:text-muted-foreground focus:border-primary focus:outline-hidden"
                            />
                        </div>

                        <!-- Status Filter -->
                        <div class="flex items-center gap-2">
                            <label class="text-[11px] font-medium text-muted-foreground whitespace-nowrap">Status:</label>
                            <select
                                v-model="statusFilter"
                                @change="applyFilters"
                                class="rounded-lg border border-input bg-background px-2.5 py-2 text-xs focus:border-primary focus:outline-hidden"
                            >
                                <option value="semua">Semua Status</option>
                                <option value="menunggu">Menunggu</option>
                                <option value="disetujui">Disetujui</option>
                                <option value="ditolak">Ditolak</option>
                            </select>
                        </div>

                        <!-- KTM Filter -->
                        <div class="flex items-center gap-2">
                            <label class="text-[11px] font-medium text-muted-foreground whitespace-nowrap">Dokumen:</label>
                            <select
                                v-model="ktmFilter"
                                @change="applyFilters"
                                class="rounded-lg border border-input bg-background px-2.5 py-2 text-xs focus:border-primary focus:outline-hidden"
                            >
                                <option value="semua">Semua Dokumen</option>
                                <option value="ada">KTM Sudah Ada</option>
                                <option value="belum">Belum Ada KTM</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <Button type="submit" size="sm" class="cursor-pointer text-xs">
                            Terapkan
                        </Button>
                        <Button type="button" variant="outline" size="sm" @click="resetFilters" class="cursor-pointer text-xs">
                            Reset
                        </Button>
                    </div>
                </form>
            </div>

            <!-- Submissions Table -->
            <div class="overflow-hidden rounded-xl border bg-card shadow-xs">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="border-b bg-muted/40 font-semibold text-muted-foreground">
                            <tr>
                                <th class="px-4 py-3">Mahasiswa / Alumni</th>
                                <th class="px-4 py-3">NIM & Prodi</th>
                                <th class="px-4 py-3">Status Mhs</th>
                                <th class="px-4 py-3">Dokumen KTM</th>
                                <th class="px-4 py-3">Status Verifikasi</th>
                                <th class="px-4 py-3">Waktu Pengajuan</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-if="submissions.data.length === 0">
                                <td colspan="7" class="px-4 py-12 text-center text-muted-foreground">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="mb-2 h-8 w-8 text-muted-foreground/60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p class="font-medium text-foreground">Tidak ada data pengajuan verifikasi</p>
                                        <p class="text-[11px] text-muted-foreground mt-0.5">Coba sesuaikan filter atau kata kunci pencarian Anda.</p>
                                    </div>
                                </td>
                            </tr>
                            <tr
                                v-for="sub in submissions.data"
                                :key="sub.id"
                                class="hover:bg-muted/30 transition-colors"
                            >
                                <td class="px-4 py-3.5">
                                    <div class="font-semibold text-foreground">{{ sub.name }}</div>
                                    <div class="text-[11px] text-muted-foreground">ID #{{ sub.id }}</div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="font-mono font-medium text-foreground">{{ sub.nim }}</div>
                                    <div class="text-[11px] text-muted-foreground truncate max-w-xs">
                                        <span v-if="sub.prodi_name">{{ sub.prodi_name }}</span>
                                        <span v-else-if="sub.kode_prodi_tidak_dikenal" class="text-amber-700 font-medium dark:text-amber-400">
                                            Kode [{{ sub.kode_prodi_nim }}] Tidak Dikenal
                                        </span>
                                        <span v-else>Prodi Belum Terdaftar</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold capitalize"
                                        :class="sub.status_mahasiswa === 'aktif' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950/40 dark:text-blue-300' : 'bg-purple-100 text-purple-800 dark:bg-purple-950/40 dark:text-purple-300'"
                                    >
                                        {{ sub.status_mahasiswa }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span
                                        v-if="sub.has_ktm"
                                        class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        KTM Terunggah
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1 rounded-full bg-muted px-2 py-0.5 text-[10px] font-medium text-muted-foreground"
                                    >
                                        Belum Ada
                                    </span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span
                                        v-if="sub.status_verifikasi === 'menunggu'"
                                        class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-800 dark:bg-amber-950/40 dark:text-amber-300"
                                    >
                                        Menunggu
                                    </span>
                                    <span
                                        v-else-if="sub.status_verifikasi === 'disetujui'"
                                        class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300"
                                    >
                                        Disetujui
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-semibold text-rose-800 dark:bg-rose-950/40 dark:text-rose-300"
                                    >
                                        Ditolak
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-muted-foreground text-[11px]">
                                    {{ sub.updated_at || sub.created_at }}
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        @click="openReviewModal(sub)"
                                        class="cursor-pointer text-xs"
                                    >
                                        {{ sub.status_verifikasi === 'menunggu' && sub.has_ktm ? 'Tinjau KTM' : 'Detail' }}
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Bar -->
                <div v-if="submissions.links.length > 3" class="flex items-center justify-between border-t px-4 py-3 bg-muted/20">
                    <div class="text-[11px] text-muted-foreground">
                        Menampilkan {{ submissions.data.length }} dari total {{ submissions.total }} pengajuan
                    </div>
                    <div class="flex items-center gap-1">
                        <template v-for="(link, i) in submissions.links" :key="i">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="rounded-md px-2.5 py-1 text-xs font-medium transition-colors"
                                :class="link.active ? 'bg-primary text-primary-foreground font-bold' : 'hover:bg-muted text-muted-foreground'"
                                v-html="link.label"
                            />
                            <span
                                v-else
                                class="px-2.5 py-1 text-xs text-muted-foreground/50"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </main>

        <!-- MODAL DETAIL DAN TINJAUAN KTM -->
        <div
            v-if="selectedSubmission"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs"
            @click.self="closeReviewModal"
        >
            <div class="relative max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl border bg-card p-6 shadow-xl sm:p-7">
                <!-- Modal Header -->
                <div class="mb-5 flex items-start justify-between border-b pb-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-lg font-bold text-foreground">Detail Verifikasi Identitas Mahasiswa</h2>
                            <span
                                class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                :class="selectedSubmission.status_verifikasi === 'menunggu' ? 'bg-amber-100 text-amber-800' : (selectedSubmission.status_verifikasi === 'disetujui' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800')"
                            >
                                {{ selectedSubmission.status_verifikasi.toUpperCase() }}
                            </span>
                        </div>
                        <p class="text-xs text-muted-foreground">Periksa kesesuaian identitas NIM dan foto Kartu Tanda Mahasiswa (KTM)</p>
                    </div>
                    <button
                        type="button"
                        @click="closeReviewModal"
                        class="rounded-lg p-1 text-muted-foreground hover:bg-muted hover:text-foreground transition cursor-pointer"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <!-- Error in Action -->
                <div
                    v-if="actionError"
                    class="mb-4 rounded-xl border border-rose-200 bg-rose-50/70 p-3 text-xs font-medium text-rose-800 flex items-center gap-2"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-rose-600" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ actionError }}</span>
                </div>

                <!-- Identity Grid -->
                <div class="mb-5 grid grid-cols-2 gap-3 rounded-xl border bg-muted/20 p-4 text-xs">
                    <div>
                        <span class="text-muted-foreground">Nama Mahasiswa:</span>
                        <p class="font-semibold text-foreground text-sm">{{ selectedSubmission.name }}</p>
                    </div>
                    <div>
                        <span class="text-muted-foreground">Nomor Induk Mahasiswa (NIM):</span>
                        <p class="font-mono font-bold text-foreground text-sm">{{ selectedSubmission.nim }}</p>
                    </div>
                    <div>
                        <span class="text-muted-foreground">Program Studi:</span>
                        <p class="font-medium text-foreground">
                            {{ selectedSubmission.prodi_name || 'Tidak Dikenal' }}
                        </p>
                        <span v-if="selectedSubmission.jurusan_name" class="text-[11px] text-muted-foreground">
                            Jurusan: {{ selectedSubmission.jurusan_name }}
                        </span>
                    </div>
                    <div>
                        <span class="text-muted-foreground">Status / Angkatan:</span>
                        <p class="font-medium text-foreground capitalize">
                            {{ selectedSubmission.status_mahasiswa }} (Angkatan {{ selectedSubmission.angkatan || '-' }})
                        </p>
                    </div>

                    <!-- Warning for unknown prodi code -->
                    <div v-if="selectedSubmission.kode_prodi_tidak_dikenal" class="col-span-2 rounded-lg border border-amber-200 bg-amber-50 p-2.5 text-[11px] text-amber-900">
                        <strong class="font-semibold">Perhatian Manual Review:</strong> Kode prodi [{{ selectedSubmission.kode_prodi_nim }}] dari NIM ini tidak ditemukan pada master data resmi PNP. Periksa nama jurusan/prodi pada foto KTM fisik di bawah secara teliti.
                    </div>
                </div>

                <!-- KTM Photo Section -->
                <div class="mb-6">
                    <h3 class="mb-2 text-xs font-semibold text-foreground flex items-center justify-between">
                        <span>Foto Kartu Tanda Mahasiswa (KTM)</span>
                        <span class="text-[11px] font-normal text-muted-foreground">Akses Privat Terenkripsi</span>
                    </h3>

                    <!-- When KTM exists: show private stream preview -->
                    <div
                        v-if="selectedSubmission.has_ktm"
                        class="relative overflow-hidden rounded-xl border bg-black/5 p-2 flex items-center justify-center min-h-60"
                    >
                        <img
                            :src="ktm.url(selectedSubmission.id)"
                            alt="Foto KTM Mahasiswa"
                            class="max-h-80 w-auto rounded-lg object-contain shadow-xs"
                            loading="lazy"
                        />
                    </div>

                    <!-- When KTM does not exist -->
                    <div
                        v-else
                        class="rounded-xl border border-dashed border-border bg-muted/20 p-8 text-center"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto mb-2 h-8 w-8 text-muted-foreground/60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <p class="text-xs font-semibold text-foreground">Pengguna Belum Mengunggah Foto KTM</p>
                        <p class="mt-1 text-[11px] text-muted-foreground max-w-sm mx-auto">
                            Akun ini belum melengkapi dokumen KTM pada halaman status verifikasi miliknya. Keputusan persetujuan atau penolakan hanya dapat diproses setelah foto KTM terunggah.
                        </p>
                    </div>
                </div>

                <!-- Existing Decision History (If not waiting) -->
                <div v-if="selectedSubmission.status_verifikasi !== 'menunggu'" class="mb-5 rounded-xl border bg-muted/20 p-3 text-xs">
                    <div class="flex justify-between text-muted-foreground mb-1">
                        <span>Diverifikasi oleh: <strong class="text-foreground">{{ selectedSubmission.verifier_name || 'Administrator' }}</strong></span>
                        <span>Waktu: {{ selectedSubmission.verified_at }}</span>
                    </div>
                    <div v-if="selectedSubmission.status_verifikasi === 'ditolak'" class="mt-2 rounded-lg border border-rose-200 bg-rose-50 p-2 text-rose-900">
                        <span class="font-semibold block">Catatan Alasan Penolakan:</span>
                        <p class="mt-0.5">{{ selectedSubmission.alasan_penolakan }}</p>
                    </div>
                </div>

                <!-- Rejection Form Input (If rejection toggled) -->
                <div v-if="showRejectForm" class="mb-5 rounded-xl border border-rose-200 bg-rose-50/50 p-4">
                    <label for="alasan_penolakan" class="block text-xs font-semibold text-rose-900 mb-1">
                        Alasan Penolakan Verifikasi (Wajib diisi, min. 5 karakter):
                    </label>
                    <p class="text-[11px] text-rose-800/80 mb-2">
                        Jelaskan secara spesifik alasan penolakan agar mahasiswa/alumni dapat memperbaikinya (contoh: "Foto KTM buram pada bagian nama dan NIM", "Bukan KTM resmi Politeknik Negeri Padang").
                    </p>
                    <textarea
                        id="alasan_penolakan"
                        v-model="rejectReason"
                        rows="3"
                        placeholder="Tuliskan catatan alasan penolakan secara jelas..."
                        class="w-full rounded-lg border border-rose-300 bg-background p-2.5 text-xs text-foreground placeholder:text-muted-foreground focus:border-rose-500 focus:outline-hidden"
                    />
                    <div class="mt-3 flex items-center justify-end gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            @click="showRejectForm = false"
                            :disabled="isSubmitting"
                            class="cursor-pointer text-xs"
                        >
                            Batal
                        </Button>
                        <Button
                            type="button"
                            variant="destructive"
                            size="sm"
                            @click="handleReject"
                            :disabled="isSubmitting || rejectReason.trim().length < 5"
                            class="cursor-pointer text-xs"
                        >
                            <Spinner v-if="isSubmitting" class="mr-1.5 h-3.5 w-3.5" />
                            Konfirmasi Tolak Pengajuan
                        </Button>
                    </div>
                </div>

                <!-- Modal Action Buttons Footer -->
                <div class="flex items-center justify-between border-t pt-4">
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        @click="closeReviewModal"
                        :disabled="isSubmitting"
                        class="cursor-pointer text-xs"
                    >
                        Tutup
                    </Button>

                    <!-- Decision buttons for pending submission with KTM -->
                    <div v-if="selectedSubmission.status_verifikasi === 'menunggu' && selectedSubmission.has_ktm && !showRejectForm" class="flex items-center gap-2">
                        <Button
                            type="button"
                            variant="destructive"
                            size="sm"
                            @click="showRejectForm = true"
                            :disabled="isSubmitting"
                            class="cursor-pointer text-xs"
                        >
                            Tolak Verifikasi
                        </Button>
                        <Button
                            type="button"
                            size="sm"
                            @click="handleApprove"
                            :disabled="isSubmitting"
                            class="cursor-pointer text-xs bg-emerald-600 hover:bg-emerald-700 text-white"
                        >
                            <Spinner v-if="isSubmitting" class="mr-1.5 h-3.5 w-3.5" />
                            Setujui Akun
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
