<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'klik:create-admin {--force : Opsi force (tidak diizinkan melewati penolakan pada environment produksi)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Provisioning akun administrator KLiK pertama secara interaktif dan aman pada environment lokal/testing.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('=========================================');
        $this->info('   PROVISIONING ADMINISTRATOR KLiK PNP   ');
        $this->info('=========================================');

        // Pengamanan lingkungan: Tolak mutlak pada environment production
        if (app()->isProduction()) {
            $this->error('Perhatian Keamanan: Command provisioning admin ditolak pada lingkungan produksi.');
            $this->error('Flag --force tidak diizinkan untuk melewati kebijakan keamanan production.');
            $this->error('Provisioning administrator pada lingkungan produksi memerlukan prosedur operator yang berwenang secara terpisah.');

            return self::FAILURE;
        }

        // Hanya diizinkan pada environment lokal atau testing
        if (! app()->environment(['local', 'testing'])) {
            $this->error('Perhatian Keamanan: Command ini hanya diizinkan berjalan pada environment local dan testing.');

            return self::FAILURE;
        }

        // 1. Input NIM Administrator
        $nim = trim((string) $this->ask('Masukkan NIM Administrator (10 digit numerik)'));

        if (! preg_match('/^[0-9]{10}$/', $nim)) {
            $this->error('Validasi Gagal: NIM harus tepat 10 digit numerik (contoh: 1901099001).');

            return self::FAILURE;
        }

        if ($nim === '0000000000') {
            $this->error('Validasi Gagal: NIM 0000000000 merupakan identitas jangkar sistem internal dan tidak dapat digunakan.');

            return self::FAILURE;
        }

        if (User::where('nim', $nim)->exists()) {
            $this->error("Validasi Gagal: Pengguna dengan NIM [{$nim}] sudah terdaftar pada database.");

            return self::FAILURE;
        }

        // 2. Input Nama Lengkap
        $name = trim((string) $this->ask('Masukkan Nama Lengkap Administrator'));

        if (mb_strlen($name) < 3 || mb_strlen($name) > 100) {
            $this->error('Validasi Gagal: Nama lengkap harus terdiri dari 3 hingga 100 karakter.');

            return self::FAILURE;
        }

        // 3. Input Password (Tersembunyi)
        $password = (string) $this->secret('Masukkan Password Administrator (minimal 8 karakter)');

        if (mb_strlen($password) < 8) {
            $this->error('Validasi Gagal: Password minimal harus 8 karakter.');

            return self::FAILURE;
        }

        // 4. Konfirmasi Password (Tersembunyi)
        $confirmPassword = (string) $this->secret('Konfirmasi Password Administrator');

        if ($password !== $confirmPassword) {
            $this->error('Validasi Gagal: Konfirmasi password tidak cocok.');

            return self::FAILURE;
        }

        // Ringkasan akun yang akan dibuat
        $this->table(
            ['Atribut', 'Nilai'],
            [
                ['NIM', $nim],
                ['Nama Lengkap', $name],
                ['Role Akun', 'admin'],
                ['Status Verifikasi', 'disetujui'],
                ['Status Penangguhan', 'Tidak Ditangguhkan (Aktif)'],
            ]
        );

        // 5. Konfirmasi Akhir Operator
        if (! $this->confirm('Apakah Anda yakin ingin membuat akun administrator ini?', true)) {
            $this->warn('Operasi dibatalkan. Tidak ada akun administrator yang dibuat.');

            return self::SUCCESS;
        }

        // Simpan akun ke database secara aman
        $admin = new User;
        $admin->name = $name;
        $admin->nim = $nim;
        $admin->password = Hash::make($password);
        $admin->role = 'admin';
        $admin->status_verifikasi = 'disetujui';
        $admin->status_mahasiswa = 'aktif';
        $admin->is_suspended = false;
        $admin->save();

        $this->info("Berhasil! Akun administrator [{$name}] dengan NIM [{$nim}] telah dibuat dan siap digunakan untuk login.");

        return self::SUCCESS;
    }
}
