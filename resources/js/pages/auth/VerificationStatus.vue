<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { logout } from '@/routes';
import { upload } from '@/routes/verification/ktm';

const props = defineProps<{
    user: {
        name: string;
        nim: string;
        status_verifikasi: 'menunggu' | 'ditolak' | 'disetujui';
        alasan_penolakan: string | null;
        has_ktm: boolean;
    };
    status?: string;
}>();

const form = useForm<{ ktm: File | null }>({
    ktm: null,
});

const previewUrl = ref<string | null>(null);
const fileInputRef = ref<HTMLInputElement | null>(null);
const clientError = ref<string | null>(null);

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    clientError.value = null;

    if (!target.files || target.files.length === 0) {
        form.ktm = null;
        if (previewUrl.value) {
            URL.revokeObjectURL(previewUrl.value);
            previewUrl.value = null;
        }
        return;
    }

    const file = target.files[0];
    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

    if (!allowedTypes.includes(file.type)) {
        clientError.value = 'Format file tidak didukung. Harap unggah foto dengan format JPG, JPEG, PNG, atau WebP.';
        form.ktm = null;
        target.value = '';
        if (previewUrl.value) {
            URL.revokeObjectURL(previewUrl.value);
            previewUrl.value = null;
        }
        return;
    }

    // Max 5 MB = 5 * 1024 * 1024 bytes
    if (file.size > 5 * 1024 * 1024) {
        clientError.value = 'Ukuran file terlalu besar. Maksimal ukuran foto KTM adalah 5 MB.';
        form.ktm = null;
        target.value = '';
        if (previewUrl.value) {
            URL.revokeObjectURL(previewUrl.value);
            previewUrl.value = null;
        }
        return;
    }

    form.ktm = file;
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
    }
    previewUrl.value = URL.createObjectURL(file);
};

const removeSelectedFile = () => {
    form.ktm = null;
    clientError.value = null;
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
        previewUrl.value = null;
    }
};

const submitKtm = () => {
    if (!form.ktm) {
        clientError.value = 'Silakan pilih foto KTM terlebih dahulu.';
        return;
    }

    form.post(upload.url(), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            removeSelectedFile();
        },
    });
};
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-muted/40 p-4">
        <Head title="Status Verifikasi Akun" />

        <div class="w-full max-w-lg rounded-2xl border bg-card p-6 shadow-sm sm:p-8">
            <div class="mb-6 text-center">
                <div class="mb-3 inline-flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h1 class="text-xl font-bold tracking-tight text-foreground sm:text-2xl">Status Verifikasi Akun KLiK</h1>
                <p class="mt-1 text-xs text-muted-foreground">Marketplace Laptop Bekas Mahasiswa & Alumni PNP</p>
            </div>

            <!-- Flash Notification -->
            <div
                v-if="status"
                class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50/70 p-4 text-xs font-medium text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/20 dark:text-emerald-300"
            >
                <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-emerald-600 dark:text-emerald-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ status }}</span>
                </div>
            </div>

            <!-- Detail Akun -->
            <div class="mb-6 space-y-2.5 rounded-xl border bg-muted/30 p-4 text-sm">
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Nama Pengguna:</span>
                    <span class="font-medium text-foreground">{{ user.name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">NIM:</span>
                    <span class="font-mono font-medium text-foreground">{{ user.nim }}</span>
                </div>
                <div class="flex items-center justify-between border-t pt-2.5">
                    <span class="text-muted-foreground">Status:</span>
                    <span
                        v-if="user.status_verifikasi === 'menunggu'"
                        class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800 dark:bg-amber-900/40 dark:text-amber-300"
                    >
                        Menunggu Verifikasi
                    </span>
                    <span
                        v-else-if="user.status_verifikasi === 'ditolak'"
                        class="inline-flex items-center rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-semibold text-rose-800 dark:bg-rose-900/40 dark:text-rose-300"
                    >
                        Verifikasi Ditolak
                    </span>
                    <span
                        v-else
                        class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300"
                    >
                        Terverifikasi
                    </span>
                </div>
            </div>

            <!-- KONDISI B: Menunggu & Sudah Ada KTM Terunggah -->
            <div
                v-if="user.status_verifikasi === 'menunggu' && user.has_ktm"
                class="mb-6 rounded-xl border border-amber-200 bg-amber-50/50 p-4 text-sm text-amber-900 dark:border-amber-900/50 dark:bg-amber-950/20 dark:text-amber-200"
            >
                <div class="mb-1.5 flex items-center gap-2 font-semibold">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-amber-600 dark:text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                    </svg>
                    <span>KTM Anda Sedang Diverifikasi oleh Admin PNP</span>
                </div>
                <p class="text-xs leading-relaxed text-amber-800/90 dark:text-amber-300/90">
                    Dokumen KTM Anda telah berhasil diterima dan sedang dalam antrean pemeriksaan oleh tim administrator KLiK untuk memastikan keabsahan civitas akademika PNP. Verifikasi biasanya diproses pada hari kerja (Senin &ndash; Jumat).
                </p>
                <p class="mt-2 text-xs font-medium text-amber-700 dark:text-amber-400">
                    Fitur transaksi, sewa lapak, dan listing laptop akan aktif otomatis setelah akun disetujui.
                </p>
            </div>

            <!-- KONDISI C Pesan: Alasan Penolakan -->
            <div
                v-if="user.status_verifikasi === 'ditolak'"
                class="mb-6 rounded-xl border border-rose-200 bg-rose-50/60 p-4 text-sm text-rose-900 dark:border-rose-900/50 dark:bg-rose-950/20 dark:text-rose-200"
            >
                <div class="mb-1.5 flex items-center gap-2 font-semibold">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-rose-600 dark:text-rose-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <span>Pengajuan Verifikasi Sebelumnya Ditolak</span>
                </div>
                <div class="mt-2 rounded-lg border border-rose-200 bg-rose-100/70 p-3 text-xs dark:border-rose-800 dark:bg-rose-900/30">
                    <span class="font-medium text-rose-950 dark:text-rose-100">Alasan Penolakan:</span>
                    <p class="mt-1 font-semibold text-rose-900 dark:text-rose-200">
                        {{ user.alasan_penolakan || 'Dokumen KTM tidak memenuhi persyaratan keaslian atau tidak terbaca dengan jelas.' }}
                    </p>
                </div>
                <p class="mt-3 text-xs leading-relaxed text-rose-800/90 dark:text-rose-300/90">
                    Silakan upload ulang foto KTM yang lebih jelas atau perbaiki dokumen sesuai catatan admin di atas untuk mengajukan verifikasi kembali.
                </p>
            </div>

            <!-- FORM UPLOAD: KONDISI A (Menunggu & Belum Ada KTM) atau KONDISI C (Ditolak) -->
            <div
                v-if="(user.status_verifikasi === 'menunggu' && !user.has_ktm) || user.status_verifikasi === 'ditolak'"
                class="mb-6 rounded-xl border bg-card p-4 sm:p-5"
            >
                <h2 class="mb-1 text-sm font-semibold text-foreground">
                    {{ user.status_verifikasi === 'ditolak' ? 'Unggah Ulang Foto KTM' : 'Unggah Foto KTM' }}
                </h2>
                <p class="mb-4 text-xs text-muted-foreground">
                    Format file yang didukung: <span class="font-medium text-foreground">JPG, JPEG, PNG, WebP</span> (Maksimal <span class="font-medium text-foreground">5 MB</span>). Pastikan nama, NIM, dan logo PNP terlihat jelas tanpa buram atau pantulan cahaya.
                </p>

                <form @submit.prevent="submitKtm" class="space-y-4">
                    <!-- Drop/File Input Area -->
                    <div
                        v-if="!previewUrl"
                        class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-border p-6 text-center hover:bg-muted/30 transition cursor-pointer"
                        @click="fileInputRef?.click()"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="mb-2 h-8 w-8 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="text-xs font-medium text-foreground">Pilih file foto KTM dari perangkat Anda</p>
                        <p class="mt-1 text-[11px] text-muted-foreground">Klik di sini untuk memilih gambar</p>
                        <input
                            ref="fileInputRef"
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            class="hidden"
                            @change="handleFileChange"
                        />
                    </div>

                    <!-- Image Preview Area -->
                    <div v-else class="space-y-2">
                        <div class="relative overflow-hidden rounded-xl border bg-muted/40">
                            <img
                                :src="previewUrl"
                                alt="Pratinjau Foto KTM"
                                class="max-h-64 w-full object-contain p-2"
                            />
                            <button
                                type="button"
                                @click="removeSelectedFile"
                                class="absolute top-2 right-2 rounded-full bg-black/60 p-1.5 text-white hover:bg-black/80 transition"
                                title="Hapus dan pilih ulang"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <p class="text-[11px] text-muted-foreground text-center">
                            {{ form.ktm?.name }} ({{ ((form.ktm?.size || 0) / 1024 / 1024).toFixed(2) }} MB)
                        </p>
                    </div>

                    <!-- Client and Server Errors -->
                    <InputError :message="clientError || form.errors.ktm" />

                    <!-- Submit Button -->
                    <Button
                        type="submit"
                        class="w-full"
                        :disabled="form.processing || !form.ktm"
                    >
                        <Spinner v-if="form.processing" class="mr-2 h-4 w-4" />
                        <span>
                            {{ form.processing ? 'Mengunggah...' : (user.status_verifikasi === 'ditolak' ? 'Kirim Ulang Foto KTM' : 'Kirim Foto KTM') }}
                        </span>
                    </Button>
                </form>
            </div>

            <!-- Tombol Keluar Akun -->
            <div class="pt-2">
                <Link
                    :href="logout.url()"
                    method="post"
                    as="button"
                    class="w-full"
                >
                    <Button variant="outline" class="w-full">
                        Keluar dari Akun
                    </Button>
                </Link>
            </div>
        </div>
    </div>
</template>
