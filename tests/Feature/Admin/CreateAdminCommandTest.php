<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('1. command klik:create-admin berhasil membuat akun administrator dengan NIM 10 digit', function () {
    $this->artisan('klik:create-admin')
        ->expectsQuestion('Masukkan NIM Administrator (10 digit numerik)', '1901099001')
        ->expectsQuestion('Masukkan Nama Lengkap Administrator', 'Admin KLiK PNP')
        ->expectsQuestion('Masukkan Password Administrator (minimal 8 karakter)', 'SuperAdmin123!')
        ->expectsQuestion('Konfirmasi Password Administrator', 'SuperAdmin123!')
        ->expectsConfirmation('Apakah Anda yakin ingin membuat akun administrator ini?', 'yes')
        ->expectsOutputToContain('Berhasil! Akun administrator [Admin KLiK PNP] dengan NIM [1901099001] telah dibuat')
        ->assertSuccessful();

    $admin = User::where('nim', '1901099001')->first();
    expect($admin)->not->toBeNull()
        ->and($admin->name)->toBe('Admin KLiK PNP')
        ->and($admin->role)->toBe('admin')
        ->and($admin->is_suspended)->toBeFalse()
        ->and($admin->status_verifikasi)->toBe('disetujui')
        ->and(Hash::check('SuperAdmin123!', $admin->password))->toBeTrue();
});

test('2. NIM berawalan angka nol dipertahankan sebagai string 10 digit', function () {
    $this->artisan('klik:create-admin')
        ->expectsQuestion('Masukkan NIM Administrator (10 digit numerik)', '0901099002')
        ->expectsQuestion('Masukkan Nama Lengkap Administrator', 'Admin Nol Depan')
        ->expectsQuestion('Masukkan Password Administrator (minimal 8 karakter)', 'SuperAdmin123!')
        ->expectsQuestion('Konfirmasi Password Administrator', 'SuperAdmin123!')
        ->expectsConfirmation('Apakah Anda yakin ingin membuat akun administrator ini?', 'yes')
        ->assertSuccessful();

    $admin = User::where('nim', '0901099002')->first();
    expect($admin)->not->toBeNull()
        ->and($admin->nim)->toBe('0901099002');
});

test('3. NIM selain 10 digit numerik ditolak oleh command', function () {
    // 9 digit
    $this->artisan('klik:create-admin')
        ->expectsQuestion('Masukkan NIM Administrator (10 digit numerik)', '190109900')
        ->expectsOutputToContain('Validasi Gagal: NIM harus tepat 10 digit numerik')
        ->assertFailed();

    // 11 digit
    $this->artisan('klik:create-admin')
        ->expectsQuestion('Masukkan NIM Administrator (10 digit numerik)', '19010990001')
        ->expectsOutputToContain('Validasi Gagal: NIM harus tepat 10 digit numerik')
        ->assertFailed();

    // Karakter huruf
    $this->artisan('klik:create-admin')
        ->expectsQuestion('Masukkan NIM Administrator (10 digit numerik)', '190109900A')
        ->expectsOutputToContain('Validasi Gagal: NIM harus tepat 10 digit numerik')
        ->assertFailed();
});

test('4. NIM jangkar sistem internal 0000000000 ditolak', function () {
    $this->artisan('klik:create-admin')
        ->expectsQuestion('Masukkan NIM Administrator (10 digit numerik)', '0000000000')
        ->expectsOutputToContain('NIM 0000000000 merupakan identitas jangkar sistem internal dan tidak dapat digunakan')
        ->assertFailed();
});

test('5. NIM yang sudah terdaftar pada database ditolak', function () {
    User::factory()->create([
        'nim' => '2101092001',
    ]);

    $this->artisan('klik:create-admin')
        ->expectsQuestion('Masukkan NIM Administrator (10 digit numerik)', '2101092001')
        ->expectsOutputToContain('sudah terdaftar pada database')
        ->assertFailed();
});

test('6. nama administrator kurang dari 3 karakter ditolak', function () {
    $this->artisan('klik:create-admin')
        ->expectsQuestion('Masukkan NIM Administrator (10 digit numerik)', '1901099005')
        ->expectsQuestion('Masukkan Nama Lengkap Administrator', 'AB')
        ->expectsOutputToContain('Validasi Gagal: Nama lengkap harus terdiri dari 3 hingga 100 karakter')
        ->assertFailed();
});

test('7. password kurang dari 8 karakter ditolak', function () {
    $this->artisan('klik:create-admin')
        ->expectsQuestion('Masukkan NIM Administrator (10 digit numerik)', '1901099006')
        ->expectsQuestion('Masukkan Nama Lengkap Administrator', 'Admin Password Pendek')
        ->expectsQuestion('Masukkan Password Administrator (minimal 8 karakter)', 'short')
        ->expectsOutputToContain('Validasi Gagal: Password minimal harus 8 karakter')
        ->assertFailed();
});

test('8. konfirmasi password yang tidak cocok ditolak', function () {
    $this->artisan('klik:create-admin')
        ->expectsQuestion('Masukkan NIM Administrator (10 digit numerik)', '1901099007')
        ->expectsQuestion('Masukkan Nama Lengkap Administrator', 'Admin Password Beda')
        ->expectsQuestion('Masukkan Password Administrator (minimal 8 karakter)', 'Password123!')
        ->expectsQuestion('Konfirmasi Password Administrator', 'DifferentPass123!')
        ->expectsOutputToContain('Validasi Gagal: Konfirmasi password tidak cocok')
        ->assertFailed();
});

test('9. pembatalan pada prompt konfirmasi tidak menciptakan akun', function () {
    $this->artisan('klik:create-admin')
        ->expectsQuestion('Masukkan NIM Administrator (10 digit numerik)', '1901099008')
        ->expectsQuestion('Masukkan Nama Lengkap Administrator', 'Admin Batal')
        ->expectsQuestion('Masukkan Password Administrator (minimal 8 karakter)', 'SuperAdmin123!')
        ->expectsQuestion('Konfirmasi Password Administrator', 'SuperAdmin123!')
        ->expectsConfirmation('Apakah Anda yakin ingin membuat akun administrator ini?', 'no')
        ->expectsOutputToContain('Operasi dibatalkan. Tidak ada akun administrator yang dibuat')
        ->assertSuccessful();

    expect(User::where('nim', '1901099008')->exists())->toBeFalse();
});

test('10. password tidak pernah ditampilkan dalam output console', function () {
    $secretPassword = 'UniqueSecretAdminPassword99!';

    $this->artisan('klik:create-admin')
        ->expectsQuestion('Masukkan NIM Administrator (10 digit numerik)', '1901099009')
        ->expectsQuestion('Masukkan Nama Lengkap Administrator', 'Admin Privacy Check')
        ->expectsQuestion('Masukkan Password Administrator (minimal 8 karakter)', $secretPassword)
        ->expectsQuestion('Konfirmasi Password Administrator', $secretPassword)
        ->expectsConfirmation('Apakah Anda yakin ingin membuat akun administrator ini?', 'yes')
        ->doesntExpectOutputToContain($secretPassword)
        ->assertSuccessful();
});

test('11. command tidak melakukan promosi otomatis terhadap akun user biasa', function () {
    $user = User::factory()->create([
        'nim' => '2101092055',
        'role' => 'user',
    ]);

    // Command harus menolak karena NIM sudah terdaftar, dan tidak boleh mengubah role user yang ada
    $this->artisan('klik:create-admin')
        ->expectsQuestion('Masukkan NIM Administrator (10 digit numerik)', '2101092055')
        ->assertFailed();

    $user->refresh();
    expect($user->role)->toBe('user');
});

test('12. eksekusi pada environment produksi ditolak secara mutlak', function () {
    app()->detectEnvironment(fn () => 'production');

    $this->artisan('klik:create-admin')
        ->expectsOutputToContain('Perhatian Keamanan: Command provisioning admin ditolak pada lingkungan produksi')
        ->assertFailed();

    // Pulihkan environment kembali ke testing
    app()->detectEnvironment(fn () => 'testing');
});

test('13. flag --force tidak dapat melewati penolakan pada environment produksi', function () {
    app()->detectEnvironment(fn () => 'production');

    $this->artisan('klik:create-admin', ['--force' => true])
        ->expectsOutputToContain('Perhatian Keamanan: Command provisioning admin ditolak pada lingkungan produksi')
        ->expectsOutputToContain('Flag --force tidak diizinkan untuk melewati kebijakan keamanan production')
        ->assertFailed();

    // Pulihkan environment kembali ke testing
    app()->detectEnvironment(fn () => 'testing');

    expect(User::where('nim', '1901099013')->exists())->toBeFalse();
});

test('14. eksekusi pada environment selain local dan testing (misal staging) ditolak', function () {
    app()->detectEnvironment(fn () => 'staging');

    $this->artisan('klik:create-admin')
        ->expectsOutputToContain('Command ini hanya diizinkan berjalan pada environment local dan testing')
        ->assertFailed();

    // Pulihkan environment kembali ke testing
    app()->detectEnvironment(fn () => 'testing');
});
