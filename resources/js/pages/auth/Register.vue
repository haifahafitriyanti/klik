<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/register';

defineOptions({
    layout: {
        title: 'Daftar Akun KLiK',
        description: 'Khusus Mahasiswa dan Alumni Politeknik Negeri Padang',
    },
});
</script>

<template>
    <Head title="Daftar Akun" />

    <Form
        v-bind="store.form()"
        :reset-on-success="['password', 'password_confirmation']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
    >
        <div class="grid gap-5">
            <!-- NIM -->
            <div class="grid gap-2">
                <Label for="nim">Nomor Induk Mahasiswa (NIM)</Label>
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
                />
                <p class="text-xs text-muted-foreground">
                    Program studi dan angkatan akan dipetakan otomatis dari NIM Anda.
                </p>
                <InputError :message="errors.nim" />
            </div>

            <!-- Nama Lengkap -->
            <div class="grid gap-2">
                <Label for="name">Nama Lengkap</Label>
                <Input
                    id="name"
                    type="text"
                    name="name"
                    required
                    :tabindex="2"
                    autocomplete="name"
                    placeholder="Nama lengkap sesuai KTM / KTP"
                    data-test="register-name-input"
                />
                <InputError :message="errors.name" />
            </div>

            <!-- Status Mahasiswa / Alumni -->
            <div class="grid gap-2">
                <Label>Status Civitas di PNP</Label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center gap-2.5 rounded-lg border border-border p-3 cursor-pointer hover:bg-muted/50 transition-colors">
                        <input
                            type="radio"
                            name="status_mahasiswa"
                            value="aktif"
                            checked
                            :tabindex="3"
                            class="accent-primary"
                        />
                        <span class="text-sm font-medium">Mahasiswa Aktif</span>
                    </label>
                    <label class="flex items-center gap-2.5 rounded-lg border border-border p-3 cursor-pointer hover:bg-muted/50 transition-colors">
                        <input
                            type="radio"
                            name="status_mahasiswa"
                            value="alumni"
                            :tabindex="3"
                            class="accent-primary"
                        />
                        <span class="text-sm font-medium">Alumni PNP</span>
                    </label>
                </div>
                <InputError :message="errors.status_mahasiswa" />
            </div>

            <!-- Email (Opsional) -->
            <div class="grid gap-2">
                <div class="flex items-center justify-between">
                    <Label for="email">Alamat Email</Label>
                    <span class="text-xs text-muted-foreground">Opsional</span>
                </div>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    :tabindex="4"
                    autocomplete="email"
                    placeholder="nama@email.com (pribadi atau kampus)"
                    data-test="register-email-input"
                />
                <p class="text-xs text-muted-foreground">
                    Email institusi tidak wajib. Alumni dapat menggunakan email pribadi.
                </p>
                <InputError :message="errors.email" />
            </div>

            <!-- Password -->
            <div class="grid gap-2">
                <Label for="password">Password</Label>
                <PasswordInput
                    id="password"
                    name="password"
                    required
                    :tabindex="5"
                    autocomplete="new-password"
                    placeholder="Minimal 8 karakter"
                    data-test="register-password-input"
                />
                <InputError :message="errors.password" />
            </div>

            <!-- Konfirmasi Password -->
            <div class="grid gap-2">
                <Label for="password_confirmation">Konfirmasi Password</Label>
                <PasswordInput
                    id="password_confirmation"
                    name="password_confirmation"
                    required
                    :tabindex="6"
                    autocomplete="new-password"
                    placeholder="Ulangi password"
                    data-test="register-password-confirmation-input"
                />
                <InputError :message="errors.password_confirmation" />
            </div>

            <!-- Tombol Submit -->
            <Button
                type="submit"
                class="mt-2 w-full"
                :tabindex="7"
                :disabled="processing"
                data-test="register-submit-button"
            >
                <Spinner v-if="processing" />
                Daftar Akun
            </Button>
        </div>

        <div class="text-center text-sm text-muted-foreground">
            Sudah memiliki akun?
            <TextLink :href="login()" class="font-medium text-foreground underline underline-offset-4 ml-1">
                Masuk di sini
            </TextLink>
        </div>

        <div class="text-center text-xs text-muted-foreground border-t pt-4">
            <p>Akun baru akan berstatus <strong>Menunggu Verifikasi</strong>.</p>
            <p class="mt-0.5">Unggah foto KTM untuk aktivasi marketplace akan dilakukan pada tahap verifikasi.</p>
        </div>
    </Form>
</template>
