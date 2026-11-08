<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { register } from '@/routes';

interface Props {
    isAuthenticated?: boolean;
    title?: string;
    description?: string;
}

withDefaults(defineProps<Props>(), {
    isAuthenticated: false,
    title: 'Belum Ada Laptop yang Ditampilkan',
    description: 'Daftar laptop akan muncul di sini setelah penjual mulai mempublikasikan produknya.',
});
</script>

<template>
    <div
        class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-[#1F293D] bg-[#161D2B]/60 px-6 py-16 text-center shadow-lg"
    >
        <div class="flex h-16 w-16 items-center justify-center rounded-2xl border border-[#00EFA5]/30 bg-[#00EFA5]/10 text-[#00EFA5] shadow-[0_0_20px_rgba(0,239,165,0.2)]">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-8 w-8"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="1.5"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                />
            </svg>
        </div>

        <h3 class="mt-5 text-lg font-bold text-[#F7F9FB]">
            {{ title }}
        </h3>

        <p class="mx-auto mt-2 max-w-md text-sm text-[#A4AEBB] leading-relaxed">
            {{ description }}
        </p>

        <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
            <Link
                v-if="!isAuthenticated"
                :href="register()"
                class="inline-flex items-center gap-2 rounded-xl bg-[#00EFA5] px-5 py-2.5 text-xs font-bold text-[#0B101A] shadow-[0_0_15px_rgba(0,239,165,0.25)] transition hover:bg-[#00d694]"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Daftar &amp; Jual Laptop Kamu
            </Link>
            <Link
                href="/#faq"
                class="inline-flex items-center gap-2 rounded-xl border border-[#1F293D] bg-[#0B101A] px-4 py-2.5 text-xs font-semibold text-[#F7F9FB] transition hover:border-[#00EFA5]/40 hover:text-[#00EFA5]"
            >
                Pelajari Cara Kerja KLiK
            </Link>
        </div>
    </div>
</template>
