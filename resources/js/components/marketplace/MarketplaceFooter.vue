<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { login, register } from '@/routes';

const currentYear = new Date().getFullYear();
</script>

<template>
    <footer
        class="relative overflow-hidden text-white"
        style="background: linear-gradient(180deg, #81e3a1 3.4%, #3e8d6e 37%, #000000 78%);"
    >
        <!-- Giant Watermark "KLiK" Background (Sesuai Referensi Gambar User) -->
        <div
            class="pointer-events-none absolute -bottom-[17%] left-[1.7%] z-0 select-none font-black text-white/[0.08] leading-none text-[clamp(200px,34.7vw,666px)] tracking-tight max-sm:text-[64vw] max-sm:-bottom-[6%]"
            aria-hidden="true"
        >
            KLiK
        </div>

        <div class="relative z-10 mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-12 md:grid-cols-2 lg:grid-cols-4">
                <!-- Col 1: Brand Info -->
                <div class="space-y-4 lg:col-span-2">
                    <div class="flex items-center gap-2.5">
                        <span class="text-3xl font-black tracking-tight text-white">
                            KL<span class="text-[#00EFA5]">i</span>K
                        </span>
                        <span class="rounded bg-[#00EFA5]/15 px-2 py-0.5 text-xs font-bold text-[#00EFA5] border border-[#00EFA5]/30">
                            PNP
                        </span>
                    </div>

                    <p class="max-w-md text-sm leading-relaxed text-[#A4AEBB]">
                        Platform katalog laptop bekas antar-mahasiswa dan alumni Politeknik Negeri Padang. Transaksi jual-beli langsung tatap muka (COD) di kawasan kampus PNP Limau Manis.
                    </p>

                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4 text-xs text-[#A4AEBB] max-w-md backdrop-blur-xs">
                        <span class="font-bold text-white">Konteks Akademik:</span>
                        Proyek pengembangan mata kuliah Kewirausahaan &amp; E-Commerce, Program Studi D3 Manajemen Informatika, Jurusan Teknologi Informasi, Politeknik Negeri Padang.
                    </div>
                </div>

                <!-- Col 2: Navigasi Cepat (Sesuai Footer.png) -->
                <div>
                    <h4 class="text-base font-extrabold text-[#F18A38]">
                        Navigasi Cepat
                    </h4>
                    <ul class="mt-4 space-y-2.5 text-sm font-medium">
                        <li>
                            <a href="/" class="text-[#f45f08] sm:text-[#fff2dc] transition hover:text-white hover:underline">
                                Beranda
                            </a>
                        </li>
                        <li>
                            <a href="/katalog" class="text-[#f45f08] sm:text-[#fff2dc] transition hover:text-white hover:underline">
                                Katalog Produk
                            </a>
                        </li>
                        <li>
                            <a href="/#kategori" class="text-[#f45f08] sm:text-[#fff2dc] transition hover:text-white hover:underline">
                                Kategori Laptop
                            </a>
                        </li>
                        <li>
                            <a href="/#sewa-lapak" class="text-[#f45f08] sm:text-[#fff2dc] transition hover:text-white hover:underline">
                                Sewa Lapak
                            </a>
                        </li>
                        <li>
                            <a href="/#faq" class="text-[#f45f08] sm:text-[#fff2dc] transition hover:text-white hover:underline">
                                FAQ
                            </a>
                        </li>
                        <li>
                            <a href="/#about-us" class="text-[#f45f08] sm:text-[#fff2dc] transition hover:text-white hover:underline">
                                About Us
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Col 3: Untuk Penjual / Akun (Sesuai Footer.png) -->
                <div>
                    <h4 class="text-base font-extrabold text-[#F18A38]">
                        Untuk Penjual
                    </h4>
                    <ul class="mt-4 space-y-2.5 text-sm font-medium">
                        <li>
                            <Link :href="login()" class="text-[#f45f08] sm:text-[#fff2dc] transition hover:text-white hover:underline">
                                Masuk Akun Mahasiswa
                            </Link>
                        </li>
                        <li>
                            <Link :href="register()" class="text-[#f45f08] sm:text-[#fff2dc] transition hover:text-white hover:underline">
                                Buka Lapak &amp; Registrasi
                            </Link>
                        </li>
                        <li>
                            <a href="#faq" class="text-[#f45f08] sm:text-[#fff2dc] transition hover:text-white hover:underline">
                                Syarat Verifikasi KTM
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Red/Orange Accent Divider (Sesuai Footer.png) -->
            <div class="mt-12 h-px w-full bg-gradient-to-r from-transparent via-[#F18A38]/50 to-transparent" />

            <!-- Bottom Disclaimer & Copyright -->
            <div class="mt-6 flex flex-col gap-3 text-xs text-white/60 sm:flex-row sm:items-center sm:justify-between">
                <p class="max-w-2xl leading-relaxed">
                    <strong class="text-white/80">Disclaimer:</strong> KLiK merupakan proyek aplikasi perkuliahan dan bukan layanan resmi institusi PNP. KLiK tidak menyediakan perantara pembayaran online dan tidak menjamin kondisi fisik barang. Transaksi dilakukan mandiri melalui COD.
                </p>
                <p class="shrink-0 text-white/70 font-mono">
                    &copy; {{ currentYear }} KLiK. All rights reserved.
                </p>
            </div>
        </div>
    </footer>
</template>
