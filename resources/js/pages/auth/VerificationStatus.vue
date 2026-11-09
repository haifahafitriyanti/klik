<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { home, logout } from '@/routes';
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
    <Head title="Status Verifikasi Akun - KLiK Marketplace Laptop Mahasiswa PNP" />

    <!--
      Halaman Status Verifikasi KLiK dengan visual presisi sesuai tema Login dan Register:
      - Sisi Kiri: Branding KLiK terpusat, Headline, Subtitle, & Dark Glass Showcase Card dengan ambient glow teal & amber
      - Sisi Kanan: Kartu Form Glassmorphic melayang, badge status akurat, upload KTM modern, dan tombol mint berdaya kontras tinggi
    -->
    <div class="relative min-h-screen bg-[#0A0D14] text-[#F7F9FB] antialiased selection:bg-[#00FFA3] selection:text-[#0A0D14] overflow-x-hidden flex flex-col justify-center">
        <!-- Background Ambient Glow Sesuai Login & Register -->
        <div class="pointer-events-none absolute -top-40 -left-40 w-[600px] h-[600px] bg-[#00FFA3]/12 blur-[160px] rounded-full" />
        <div class="pointer-events-none absolute top-1/4 left-1/3 w-[500px] h-[500px] bg-[#D97706]/10 blur-[180px] rounded-full" />
        <div class="pointer-events-none absolute -bottom-40 -right-40 w-[600px] h-[600px] bg-[#00FFA3]/8 blur-[180px] rounded-full" />

        <div class="grid grid-cols-1 lg:grid-cols-12 min-h-screen w-full">
            <!-- SISI KIRI (lg:col-span-5 xl:col-span-5): Branding & Showcase Terpusat -->
            <div class="hidden lg:flex lg:col-span-5 xl:col-span-5 flex-col items-center justify-center p-8 xl:p-14 relative z-10 text-center border-r border-white/5 bg-[#0A0D14]/70 backdrop-blur-sm">
                <div class="w-full max-w-md flex flex-col items-center space-y-8">
                    <!-- Logo KLiK dengan Mouse Pointer Squircle Mint -->
                    <Link :href="home()" class="group inline-flex items-center gap-3.5">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#00FFA3] text-[#0A0D14] shadow-[0_0_24px_rgba(0,255,163,0.35)] transition-transform duration-200 group-hover:scale-105">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 stroke-[2.3]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                            </svg>
                        </div>
                        <div class="flex flex-col items-start">
                            <span class="text-3xl font-black tracking-tight text-white leading-none">
                                KL<span class="text-[#00FFA3]">i</span>K
                            </span>
                            <div class="h-0.5 w-full bg-white/90 mt-1 rounded-full"></div>
                        </div>
                    </Link>

                    <!-- Headline & Subtitle Terpusat -->
                    <div class="space-y-4">
                        <h2 class="text-3xl xl:text-4xl font-black text-white tracking-tight leading-tight">
                            Platform Jual Beli<br />
                            Laptop Bekas Khusus<br />
                            Mahasiswa PNP
                        </h2>
                        <p class="text-sm text-slate-400 leading-relaxed max-w-sm mx-auto">
                            Temukan laptop bekas siap pakai dengan harga mahasiswa dan proses transaksi yang aman.
                        </p>
                    </div>

                    <!-- Decorative Showcase Card -->
                    <div class="relative w-full h-56 xl:h-64 rounded-3xl border border-white/10 bg-gradient-to-br from-[#0c2a20]/70 via-[#0e1420]/80 to-[#3b1d07]/60 p-6 shadow-2xl backdrop-blur-xl overflow-hidden flex flex-col justify-between text-left">
                        <div class="pointer-events-none absolute -top-10 -left-10 w-40 h-40 bg-[#00FFA3]/20 blur-3xl rounded-full" />
                        <div class="pointer-events-none absolute -bottom-10 -right-10 w-44 h-44 bg-[#EA580C]/25 blur-3xl rounded-full" />

                        <div class="relative z-10 flex items-center justify-between">
                            <span class="rounded-full border border-[#00FFA3]/30 bg-[#00FFA3]/10 px-3 py-1 text-[11px] font-bold text-[#00FFA3]">
                                &bull; Sivitas Akademika PNP
                            </span>
                            <span class="text-xs font-semibold text-slate-400">
                                COD Kampus
                            </span>
                        </div>

                        <div class="relative z-10 space-y-2">
                            <p class="text-xs text-slate-400 font-medium">Marketplace Khusus Politeknik Negeri Padang</p>
                            <h3 class="text-lg font-bold text-white">Transaksi Nyaman &amp; Terverifikasi</h3>
                            <div class="flex items-center gap-2 pt-1">
                                <span class="inline-flex items-center gap-1.5 rounded-lg bg-white/10 px-2.5 py-1 text-[11px] text-white backdrop-blur-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-[#00FFA3]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Verifikasi KTM
                                </span>
                                <span class="inline-flex items-center gap-1.5 rounded-lg bg-white/10 px-2.5 py-1 text-[11px] text-white backdrop-blur-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-[#00FFA3]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    0% Potongan
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Kecil Sisi Kiri -->
                    <p class="text-xs text-slate-500 pt-2">
                        &copy; {{ new Date().getFullYear() }} KLiK &bull; Politeknik Negeri Padang
                    </p>
                </div>
            </div>

            <!-- SISI KANAN (lg:col-span-7 xl:col-span-7): Status & Upload KTM Card -->
            <div class="col-span-12 lg:col-span-7 xl:col-span-7 flex flex-col justify-center items-center p-4 sm:p-8 lg:p-12 relative z-10 py-10">
                <!-- Mobile Header Logo (Tampil hanya di layar kecil) -->
                <div class="lg:hidden flex items-center justify-between w-full max-w-lg mb-6">
                    <Link :href="home()" class="inline-flex items-center gap-2.5">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#00FFA3] text-[#0A0D14]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 stroke-[2.3]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                            </svg>
                        </div>
                        <span class="text-2xl font-black tracking-tight text-white">
                            KL<span class="text-[#00FFA3]">i</span>K
                        </span>
                    </Link>
                    <Link :href="home()" class="text-xs text-slate-400 hover:text-white transition">
                        &larr; Beranda
                    </Link>
                </div>

                <!-- Floating Dark Card (Sesuai Kartu Login & Register) -->
                <div class="w-full max-w-lg rounded-3xl border border-white/10 bg-[#121622]/90 backdrop-blur-2xl p-6 sm:p-9 shadow-2xl shadow-black/70 space-y-6">
                    <!-- Header Kartu -->
                    <div class="space-y-1.5">
                        <div class="inline-flex items-center gap-2 rounded-full border border-[#00FFA3]/30 bg-[#00FFA3]/10 px-3 py-1 text-[11px] font-bold text-[#00FFA3] mb-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            Verifikasi Identitas Kampus
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                            Status Verifikasi Akun
                        </h1>
                        <p class="text-xs sm:text-sm text-slate-400">
                            Marketplace Laptop Bekas Mahasiswa &amp; Alumni PNP
                        </p>
                    </div>

                    <!-- Flash Notification jika ada status berhasil -->
                    <div
                        v-if="status"
                        class="rounded-2xl border border-[#00FFA3]/30 bg-[#00FFA3]/10 p-4 text-xs font-semibold text-[#00FFA3] flex items-center gap-2.5 shadow-[0_0_20px_rgba(0,255,163,0.15)]"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ status }}</span>
                    </div>

                    <!-- Detail Akun Pengguna -->
                    <div class="rounded-2xl border border-white/10 bg-[#161C28] p-4.5 space-y-3">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-400">Nama Pengguna:</span>
                            <span class="font-bold text-white">{{ user.name }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-400">NIM:</span>
                            <span class="font-mono font-bold text-white tracking-wide">{{ user.nim }}</span>
                        </div>
                        <div class="flex items-center justify-between border-t border-white/5 pt-3">
                            <span class="text-xs text-slate-400">Status Akun:</span>
                            <span
                                v-if="user.status_verifikasi === 'menunggu'"
                                class="inline-flex items-center gap-1.5 rounded-full border border-amber-500/30 bg-amber-500/10 px-3 py-1 text-xs font-bold text-amber-400"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Menunggu Verifikasi
                            </span>
                            <span
                                v-else-if="user.status_verifikasi === 'ditolak'"
                                class="inline-flex items-center gap-1.5 rounded-full border border-rose-500/30 bg-rose-500/10 px-3 py-1 text-xs font-bold text-rose-400"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Verifikasi Ditolak
                            </span>
                            <span
                                v-else
                                class="inline-flex items-center gap-1.5 rounded-full border border-[#00FFA3]/30 bg-[#00FFA3]/10 px-3 py-1 text-xs font-bold text-[#00FFA3]"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Terverifikasi
                            </span>
                        </div>
                    </div>

                    <!-- KONDISI B: Menunggu & Sudah Ada Dokumen KTM Terunggah -->
                    <div
                        v-if="user.status_verifikasi === 'menunggu' && user.has_ktm"
                        class="rounded-2xl border border-amber-500/30 bg-amber-500/10 p-4.5 space-y-2.5 text-xs text-amber-200"
                    >
                        <div class="flex items-center gap-2 font-bold text-amber-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>KTM Anda Sedang Diverifikasi oleh Admin PNP</span>
                        </div>
                        <p class="leading-relaxed text-amber-200/90">
                            Dokumen KTM Anda telah berhasil diterima dan sedang dalam antrean pemeriksaan oleh tim administrator KLiK untuk memastikan keabsahan civitas akademika PNP. Verifikasi diproses pada hari kerja (Senin &ndash; Jumat).
                        </p>
                        <p class="font-semibold text-amber-400">
                            Fitur sewa lapak dan pasang listing laptop akan aktif secara otomatis setelah akun disetujui.
                        </p>
                    </div>

                    <!-- KONDISI C: Status Ditolak & Catatan Alasan Penolakan -->
                    <div
                        v-if="user.status_verifikasi === 'ditolak'"
                        class="rounded-2xl border border-rose-500/30 bg-rose-500/10 p-4.5 space-y-3 text-xs text-rose-200"
                    >
                        <div class="flex items-center gap-2 font-bold text-rose-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>Pengajuan Verifikasi Sebelumnya Ditolak</span>
                        </div>
                        <div class="rounded-xl border border-rose-500/20 bg-rose-950/60 p-3.5 space-y-1">
                            <span class="font-bold text-rose-300">Alasan Penolakan:</span>
                            <p class="font-medium text-white">
                                {{ user.alasan_penolakan || 'Dokumen KTM tidak memenuhi persyaratan keaslian atau tidak terbaca dengan jelas.' }}
                            </p>
                        </div>
                        <p class="leading-relaxed text-rose-300/90">
                            Silakan unggah ulang foto KTM yang lebih jelas atau perbaiki dokumen sesuai catatan admin di atas untuk mengajukan verifikasi kembali.
                        </p>
                    </div>

                    <!-- FORM UPLOAD: KONDISI A (Menunggu & Belum Ada KTM) atau KONDISI C (Ditolak) -->
                    <div
                        v-if="(user.status_verifikasi === 'menunggu' && !user.has_ktm) || user.status_verifikasi === 'ditolak'"
                        class="space-y-4 pt-1"
                    >
                        <div class="space-y-1">
                            <h2 class="text-sm font-bold text-white">
                                {{ user.status_verifikasi === 'ditolak' ? 'Unggah Ulang Foto KTM' : 'Unggah Foto KTM' }}
                            </h2>
                            <p class="text-[11px] text-slate-400 leading-relaxed">
                                Format: <strong class="text-white">JPG, JPEG, PNG, WebP</strong> (Maksimal <strong class="text-white">5 MB</strong>). Pastikan nama, NIM, dan logo PNP terlihat jelas tanpa buram.
                            </p>
                        </div>

                        <form @submit.prevent="submitKtm" class="space-y-4">
                            <!-- Drop / File Input Box -->
                            <div
                                v-if="!previewUrl"
                                class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-[#232B3E] hover:border-[#00FFA3]/50 bg-[#161C28]/60 p-6 text-center transition-colors cursor-pointer group"
                                @click="fileInputRef?.click()"
                            >
                                <div class="mb-2.5 flex h-11 w-11 items-center justify-center rounded-xl bg-white/5 text-slate-400 group-hover:text-[#00FFA3] group-hover:bg-[#00FFA3]/10 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 stroke-[1.8]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <p class="text-xs font-bold text-white group-hover:text-[#00FFA3] transition-colors">
                                    Pilih file foto KTM dari perangkat Anda
                                </p>
                                <p class="mt-1 text-[11px] text-slate-400">
                                    Klik di sini untuk memilih gambar (Maks. 5 MB)
                                </p>
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
                                <div class="relative overflow-hidden rounded-2xl border border-white/10 bg-[#161C28] p-2">
                                    <img
                                        :src="previewUrl"
                                        alt="Pratinjau Foto KTM"
                                        class="max-h-56 w-full object-contain rounded-xl"
                                    />
                                    <button
                                        type="button"
                                        @click="removeSelectedFile"
                                        class="absolute top-4 right-4 rounded-full bg-black/80 hover:bg-rose-600 p-2 text-white transition-colors cursor-pointer shadow-lg"
                                        title="Hapus dan pilih ulang"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                <p class="text-[11px] text-slate-400 text-center font-mono">
                                    {{ form.ktm?.name }} ({{ ((form.ktm?.size || 0) / 1024 / 1024).toFixed(2) }} MB)
                                </p>
                            </div>

                            <!-- Error Alert -->
                            <InputError :message="clientError || form.errors.ktm" />

                            <!-- Submit Button Hijau Mint Solid -->
                            <Button
                                type="submit"
                                class="w-full h-12 rounded-xl bg-[#00FFA3] hover:bg-[#00e090] text-[#0A0D14] font-black text-sm tracking-wide transition-all duration-200 shadow-[0_0_25px_rgba(0,255,163,0.35)] hover:shadow-[0_0_30px_rgba(0,255,163,0.5)] hover:scale-[1.01] active:scale-[0.99] disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 cursor-pointer"
                                :disabled="form.processing || !form.ktm"
                            >
                                <Spinner v-if="form.processing" />
                                <span>
                                    {{ form.processing ? 'Mengunggah...' : (user.status_verifikasi === 'ditolak' ? 'Kirim Ulang Foto KTM' : 'Kirim Foto KTM') }}
                                </span>
                            </Button>
                        </form>
                    </div>

                    <!-- Tombol Keluar dari Akun (Logout) -->
                    <div class="border-t border-white/5 pt-4">
                        <Link
                            :href="logout.url()"
                            method="post"
                            as="button"
                            class="w-full"
                        >
                            <button
                                type="button"
                                class="w-full h-11 rounded-xl border border-white/10 bg-white/5 hover:bg-white/10 text-slate-300 hover:text-white font-semibold text-xs transition-colors flex items-center justify-center gap-2 cursor-pointer"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                <span>Keluar dari Akun</span>
                            </button>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
