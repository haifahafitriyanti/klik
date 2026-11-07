<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { register } from '@/routes';

defineOptions({
    layout: {
        title: 'Masuk ke KLiK',
        description: 'Masukkan 10 digit NIM dan password Anda untuk masuk',
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>
    <Head title="Masuk" />

    <div
        v-if="status"
        class="mb-4 text-center text-sm font-medium text-green-600"
    >
        {{ status }}
    </div>

    <Form
        v-bind="store.form()"
        :reset-on-success="['password']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
    >
        <div class="grid gap-6">
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
                    data-test="nim-input"
                />
                <InputError :message="errors.nim" />
            </div>

            <div class="grid gap-2">
                <div class="flex items-center justify-between">
                    <Label for="password">Password</Label>
                    <TextLink
                        v-if="canResetPassword"
                        :href="request()"
                        class="text-sm"
                        :tabindex="5"
                    >
                        Lupa password?
                    </TextLink>
                </div>
                <PasswordInput
                    id="password"
                    name="password"
                    required
                    :tabindex="2"
                    autocomplete="current-password"
                    placeholder="Masukkan password"
                    data-test="password-input"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <Label for="remember" class="flex items-center space-x-3 cursor-pointer">
                    <Checkbox id="remember" name="remember" :tabindex="3" />
                    <span>Ingat saya</span>
                </Label>
            </div>

            <Button
                type="submit"
                class="mt-2 w-full"
                :tabindex="4"
                :disabled="processing"
                data-test="login-button"
            >
                <Spinner v-if="processing" />
                Masuk
            </Button>
        </div>

        <div class="text-center text-sm text-muted-foreground">
            Belum memiliki akun?
            <TextLink :href="register()" class="font-medium text-foreground underline underline-offset-4 ml-1">
                Daftar sekarang
            </TextLink>
        </div>

        <div class="text-center text-xs text-muted-foreground border-t pt-4">
            Khusus mahasiswa dan alumni Politeknik Negeri Padang.
        </div>
    </Form>
</template>
