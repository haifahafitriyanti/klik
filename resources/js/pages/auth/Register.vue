<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { home, login } from '@/routes';
import { store } from '@/routes/register';
</script>

<template>
    <Head title="Daftar Akun - KLiK Marketplace Laptop Mahasiswa PNP" />

    <!--
      Halaman Registrasi KLiK dengan visual presisi sesuai referensi public/images/Login.png:
      - Sisi Kiri: Branding KLiK terpusat, Headline, Subtitle, & Dark Glass Showcase Card dengan ambient glow teal & amber
      - Sisi Kanan: Kartu Form Glassmorphic melayang, input bergaya modern dark, tombol mint kontras tinggi, dan link navigasi di bawah kartu
    -->
    <div class="relative min-h-screen bg-[#0A0D14] text-[#F7F9FB] antialiased selection:bg-[#00FFA3] selection:text-[#0A0D14] overflow-x-hidden flex flex-col justify-center">
        <!-- Background Ambient Glow Sesuai Login.png -->
        <div class="pointer-events-none absolute -top-40 -left-40 w-[600px] h-[600px] bg-[#00FFA3]/12 blur-[160px] rounded-full" />
        <div class="pointer-events-none absolute top-1/4 left-1/3 w-[500px] h-[500px] bg-[#D97706]/10 blur-[180px] rounded-full" />
        <div class="pointer-events-none absolute -bottom-40 -right-40 w-[600px] h-[600px] bg-[#00FFA3]/8 blur-[180px] rounded-full" />

        <div class="grid grid-cols-1 lg:grid-cols-12 min-h-screen w-full">
            <!-- SISI KIRI (lg:col-span-5 xl:col-span-5): Branding & Showcase Terpusat Sesuai Login.png -->
            <div class="hidden lg:flex lg:col-span-5 xl:col-span-5 flex-col items-center justify-center p-8 xl:p-14 relative z-10 text-center border-r border-white/5 bg-[#0A0D14]/70 backdrop-blur-sm">
                <div class="w-full max-w-md flex flex-col items-center space-y-8">
                    <!-- Logo KLiK Sesuai Visual Login.png -->
                    <Link :href="home()" class="group inline-flex items-center gap-3.5">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#00FFA3] text-[#0A0D14] shadow-[0_0_24px_rgba(0,255,163,0.35)] transition-transform duration-200 group-hover:scale-105">
                            <!-- Mouse pointer icon with click rays -->
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

                    <!-- Headline & Subtitle Terpusat (Persis Login.png) -->
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

                    <!-- Decorative Showcase Card (Sesuai Kotak di Bawah Tulisan pada Login.png) -->
                    <div class="relative w-full h-56 xl:h-64 rounded-3xl border border-white/10 bg-gradient-to-br from-[#0c2a20]/70 via-[#0e1420]/80 to-[#3b1d07]/60 p-6 shadow-2xl backdrop-blur-xl overflow-hidden flex flex-col justify-between text-left">
                        <!-- Pendaran Cahaya Teal di Kiri Atas & Amber di Kanan Bawah -->
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

            <!-- SISI KANAN (lg:col-span-7 xl:col-span-7): Form Registrasi Elegan -->
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

                <!-- Floating Dark Card (Sesuai Kartu Form di Login.png) -->
                <div class="w-full max-w-lg rounded-3xl border border-white/10 bg-[#121622]/90 backdrop-blur-2xl p-6 sm:p-9 shadow-2xl shadow-black/70">
                    <!-- Judul & Subjudul Form Sesuai Login.png -->
                    <div class="mb-6 space-y-1.5">
                        <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                            Daftar Akun KLiK
                        </h1>
                        <p class="text-xs sm:text-sm text-slate-400">
                            Khusus mahasiswa dan alumni Politeknik Negeri Padang
                        </p>
                    </div>

                    <!-- Form Registrasi -->
                    <Form
                        v-bind="store.form()"
                        :reset-on-success="['password', 'password_confirmation']"
                        v-slot="{ errors, processing }"
                        class="flex flex-col gap-4.5"
                    >
                        <!-- 1. Input NIM -->
                        <div class="grid gap-1.5">
                            <Label for="nim" class="text-xs font-semibold text-slate-200">
                                Nomor Induk Mahasiswa (NIM)
                            </Label>
                            <Input
                                id="nim"
                                type="text"
                                name="nim"
                                inputmode="numeric"
                                pattern="[0-9]{10}"
                                maxlength="10"
                                required
                                v-focus
                                :tabindex="1"
                                autocomplete="username"
                                placeholder="10 digit NIM (contoh: 2101092001)"
                                data-test="register-nim-input"
                                class="h-11 rounded-xl border-[#232B3E] bg-[#161C28] text-white placeholder-slate-500 focus:border-[#00FFA3] focus:ring-1 focus:ring-[#00FFA3]"
                            />
                            <p class="text-[11px] text-slate-400">
                                Program studi dan angkatan dipetakan otomatis dari NIM Anda.
                            </p>
                            <InputError :message="errors.nim" />
                        </div>

                        <!-- 2. Input Nama Lengkap -->
                        <div class="grid gap-1.5">
                            <Label for="name" class="text-xs font-semibold text-slate-200">
                                Nama Lengkap
                            </Label>
                            <Input
                                id="name"
                                type="text"
                                name="name"
                                required
                                :tabindex="2"
                                autocomplete="name"
                                placeholder="Nama lengkap sesuai KTM / KTP"
                                data-test="register-name-input"
                                class="h-11 rounded-xl border-[#232B3E] bg-[#161C28] text-white placeholder-slate-500 focus:border-[#00FFA3] focus:ring-1 focus:ring-[#00FFA3]"
                            />
                            <InputError :message="errors.name" />
                        </div>

                        <!-- 3. Status Civitas di PNP (Radio Cards) -->
                        <div class="grid gap-1.5">
                            <Label class="text-xs font-semibold text-slate-200">
                                Status Civitas di PNP
                            </Label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="flex items-center gap-2.5 rounded-xl border border-[#232B3E] bg-[#161C28] p-3 cursor-pointer hover:border-[#00FFA3]/40 transition-colors">
                                    <input
                                        type="radio"
                                        name="status_mahasiswa"
                                        value="aktif"
                                        checked
                                        :tabindex="3"
                                        class="accent-[#00FFA3] h-4 w-4"
                                    />
                                    <span class="text-xs font-medium text-white">Mahasiswa Aktif</span>
                                </label>
                                <label class="flex items-center gap-2.5 rounded-xl border border-[#232B3E] bg-[#161C28] p-3 cursor-pointer hover:border-[#00FFA3]/40 transition-colors">
                                    <input
                                        type="radio"
                                        name="status_mahasiswa"
                                        value="alumni"
                                        :tabindex="3"
                                        class="accent-[#00FFA3] h-4 w-4"
                                    />
                                    <span class="text-xs font-medium text-white">Alumni PNP</span>
                                </label>
                            </div>
                            <InputError :message="errors.status_mahasiswa" />
                        </div>

                        <!-- 4. Input Email (Opsional) -->
                        <div class="grid gap-1.5">
                            <div class="flex items-center justify-between">
                                <Label for="email" class="text-xs font-semibold text-slate-200">
                                    Alamat Email
                                </Label>
                                <span class="text-[11px] text-slate-400 font-medium">Opsional</span>
                            </div>
                            <Input
                                id="email"
                                type="email"
                                name="email"
                                :tabindex="4"
                                autocomplete="email"
                                placeholder="nama@email.com (pribadi atau institusi)"
                                data-test="register-email-input"
                                class="h-11 rounded-xl border-[#232B3E] bg-[#161C28] text-white placeholder-slate-500 focus:border-[#00FFA3] focus:ring-1 focus:ring-[#00FFA3]"
                            />
                            <InputError :message="errors.email" />
                        </div>

                        <!-- 5. Password -->
                        <div class="grid gap-1.5">
                            <Label for="password" class="text-xs font-semibold text-slate-200">
                                Password
                            </Label>
                            <PasswordInput
                                id="password"
                                name="password"
                                required
                                :tabindex="5"
                                autocomplete="new-password"
                                placeholder="Minimal 8 karakter"
                                data-test="register-password-input"
                                class="[&>input]:h-11 [&>input]:rounded-xl [&>input]:bg-[#161C28] [&>input]:border-[#232B3E] [&>input]:text-white [&>input]:placeholder-slate-500 [&>input]:focus:border-[#00FFA3] [&>input]:focus:ring-1 [&>input]:focus:ring-[#00FFA3]"
                            />
                            <InputError :message="errors.password" />
                        </div>

                        <!-- 6. Konfirmasi Password -->
                        <div class="grid gap-1.5">
                            <Label for="password_confirmation" class="text-xs font-semibold text-slate-200">
                                Konfirmasi Password
                            </Label>
                            <PasswordInput
                                id="password_confirmation"
                                name="password_confirmation"
                                required
                                :tabindex="6"
                                autocomplete="new-password"
                                placeholder="Ulangi password"
                                data-test="register-password-confirmation-input"
                                class="[&>input]:h-11 [&>input]:rounded-xl [&>input]:bg-[#161C28] [&>input]:border-[#232B3E] [&>input]:text-white [&>input]:placeholder-slate-500 [&>input]:focus:border-[#00FFA3] [&>input]:focus:ring-1 [&>input]:focus:ring-[#00FFA3]"
                            />
                            <InputError :message="errors.password_confirmation" />
                        </div>

                        <!-- 7. Tombol Submit Hijau Mint Solid (Persis Tombol Masuk di Login.png) -->
                        <Button
                            type="submit"
                            class="mt-2 w-full h-12 rounded-xl bg-[#00FFA3] hover:bg-[#00e090] text-[#0A0D14] font-black text-sm tracking-wide transition-all duration-200 shadow-[0_0_25px_rgba(0,255,163,0.35)] hover:shadow-[0_0_30px_rgba(0,255,163,0.5)] hover:scale-[1.01] active:scale-[0.99] disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 cursor-pointer"
                            :tabindex="7"
                            :disabled="processing"
                            data-test="register-submit-button"
                        >
                            <Spinner v-if="processing" />
                            Daftar Akun
                        </Button>
                    </Form>
                </div>

                <!-- Navigasi Masuk (Di Luar Kartu, Persis Desain Login.png) -->
                <div class="mt-6 text-center text-xs text-slate-400">
                    Sudah punya akun?
                    <Link :href="login()" class="font-semibold text-white hover:text-[#00FFA3] underline underline-offset-4 ml-1 transition-colors">
                        Masuk di sini
                    </Link>
                </div>

                <!-- Info Verifikasi Akun Baru -->
                <div class="mt-4 text-center max-w-sm text-[11px] text-slate-500 leading-relaxed">
                    Setelah mendaftar, akun dapat diverifikasi dengan kartu tanda mahasiswa (KTM) untuk mulai berjualan.
                </div>
            </div>
        </div>
    </div>
</template>
