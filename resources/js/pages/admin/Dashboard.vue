<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

defineProps<{
    user?: {
        name: string;
        nim: string;
        role: string;
    };
}>();
</script>

<template>
    <Head title="Panel Administrator" />

    <div class="flex min-h-screen flex-col bg-background text-foreground">
        <header class="border-b px-6 py-4 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold">KLiK Administrator</h1>
                <p class="text-xs text-muted-foreground">Area Khusus Manajemen dan Moderasi Marketplace KLiK</p>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm font-medium">Administrator</span>
                <Link href="/logout" method="post" as="button">
                    <Button variant="outline" size="sm">Keluar</Button>
                </Link>
            </div>
        </header>

        <main class="flex-1 p-6 max-w-5xl mx-auto w-full">
            <div class="rounded-xl border bg-card p-6 shadow-sm">
                <div class="flex items-center gap-3 mb-4">
                    <div class="h-3 w-3 rounded-full bg-green-500 animate-pulse" />
                    <h2 class="text-lg font-semibold">Selamat Datang di Area Administrator KLiK</h2>
                </div>
                <p class="text-sm text-muted-foreground mb-6">
                    Anda berhasil masuk menggunakan akun administrator terverifikasi. Panel verifikasi KTM lengkap, moderasi listing, dan peninjauan banding akan diimplementasikan pada Fase 3.4.
                </p>

                <div class="grid gap-4 md:grid-cols-3">
                    <div class="rounded-lg border p-4 bg-muted/40">
                        <div class="text-xs font-semibold text-muted-foreground uppercase">Role Akun</div>
                        <div class="text-base font-bold text-primary mt-1">Administrator</div>
                    </div>
                    <div class="rounded-lg border p-4 bg-muted/40">
                        <div class="text-xs font-semibold text-muted-foreground uppercase">Autentikasi</div>
                        <div class="text-base font-bold mt-1">NIM & Password</div>
                    </div>
                    <div class="rounded-lg border p-4 bg-muted/40">
                        <div class="text-xs font-semibold text-muted-foreground uppercase">Status Keamanan</div>
                        <div class="text-base font-bold text-green-600 mt-1">Aktif / Terlindungi</div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

