<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

defineProps<{
    user: {
        name: string;
        nim: string;
        status_verifikasi: 'menunggu' | 'ditolak' | 'disetujui';
        alasan_penolakan: string | null;
    };
}>();
</script>

<template>
    <Head title="Status Verifikasi Akun" />

    <div class="flex min-h-screen items-center justify-center bg-muted/40 p-4">
        <div class="w-full max-w-md rounded-2xl border bg-card p-6 shadow-sm">
            <div class="text-center mb-6">
                <div class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 text-primary mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h1 class="text-xl font-bold">Status Verifikasi Akun KLiK</h1>
                <p class="text-xs text-muted-foreground mt-1">Marketplace Laptop Bekas Mahasiswa & Alumni PNP</p>
            </div>

            <!-- Detail Akun -->
            <div class="rounded-xl border p-4 bg-muted/30 mb-6 space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Nama Pengguna:</span>
                    <span class="font-medium text-foreground">{{ user.name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">NIM:</span>
                    <span class="font-mono font-medium text-foreground">{{ user.nim }}</span>
                </div>
                <div class="flex justify-between items-center pt-2 border-t">
                    <span class="text-muted-foreground">Status:</span>
                    <span
                        v-if="user.status_verifikasi === 'menunggu'"
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300"
                    >
                        Menunggu Verifikasi
                    </span>
                    <span
                        v-else-if="user.status_verifikasi === 'ditolak'"
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300"
                    >
                        Verifikasi Ditolak
                    </span>
                    <span
                        v-else
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300"
                    >
                        Terverifikasi
                    </span>
                </div>
            </div>

            <!-- Pesan Status Menunggu -->
            <div
                v-if="user.status_verifikasi === 'menunggu'"
                class="rounded-xl border border-amber-200 bg-amber-50/50 p-4 text-sm text-amber-900 dark:border-amber-900/50 dark:bg-amber-950/20 dark:text-amber-200 mb-6"
            >
                <div class="font-semibold mb-1 flex items-center gap-2">
                    <span>Sedang Ditinjau Administrator</span>
                </div>
                <p class="text-xs leading-relaxed text-amber-800/90 dark:text-amber-300/90">
                    Pengajuan pendaftaran dan kartu tanda mahasiswa (KTM) Anda sedang ditinjau oleh tim administrator KLiK untuk memastikan keabsahan civitas akademika PNP. Fitur transaksi dan sewa lapak akan aktif setelah akun disetujui.
                </p>
            </div>

            <!-- Pesan Status Ditolak -->
            <div
                v-else-if="user.status_verifikasi === 'ditolak'"
                class="rounded-xl border border-rose-200 bg-rose-50/50 p-4 text-sm text-rose-900 dark:border-rose-900/50 dark:bg-rose-950/20 dark:text-rose-200 mb-6"
            >
                <div class="font-semibold mb-1 flex items-center gap-2">
                    <span>Pengajuan Verifikasi Ditolak</span>
                </div>
                <p class="text-xs mb-2 text-rose-800/90 dark:text-rose-300/90">
                    Alasan penolakan:
                    <strong class="block mt-1 font-medium bg-rose-100/70 p-2 rounded border border-rose-200 dark:bg-rose-900/30 dark:border-rose-800">
                        {{ user.alasan_penolakan || 'Dokumen KTM tidak memenuhi persyaratan keaslian atau tidak terbaca dengan jelas.' }}
                    </strong>
                </p>
                <p class="text-xs text-muted-foreground mt-3 italic">
                    Catatan: Alur unggah ulang dokumen perbaikan KTM akan tersedia pada Fase 3.3.
                </p>
            </div>

            <!-- Tombol Keluar -->
            <div class="flex flex-col gap-3">
                <Link href="/logout" method="post" as="button" class="w-full">
                    <Button variant="outline" class="w-full">
                        Keluar dari Akun
                    </Button>
                </Link>
            </div>
        </div>
    </div>
</template>

