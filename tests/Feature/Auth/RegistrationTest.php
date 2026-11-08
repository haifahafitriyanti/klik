<?php

use App\Models\Jurusan;
use App\Models\ProdiCode;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

/*
|--------------------------------------------------------------------------
| A. Validasi Form Registrasi
|--------------------------------------------------------------------------
*/

test('1. halaman register dapat diakses oleh guest dan me-render form Registrasi KLiK', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('auth/Register')
    );
});

test('2. form registrasi tidak dapat diakses oleh pengguna yang sudah login (dialihkan)', function () {
    $user = User::factory()->verified()->create();

    $response = $this->actingAs($user)->get(route('register'));

    $response->assertRedirect(route('home'));
});

test('3. registrasi gagal jika NIM tidak diisi', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Fulan bin Fulan',
        'status_mahasiswa' => 'aktif',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ]);

    $response->assertSessionHasErrors('nim');
    $this->assertGuest();
});

test('4. registrasi gagal jika NIM bukan 10 digit (9 digit atau 11 digit)', function () {
    // 9 digit
    $response1 = $this->post(route('register.store'), [
        'nim' => '210109201',
        'name' => 'Fulan bin Fulan',
        'status_mahasiswa' => 'aktif',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ]);
    $response1->assertSessionHasErrors('nim');

    // 11 digit
    $response2 = $this->post(route('register.store'), [
        'nim' => '21010920001',
        'name' => 'Fulan bin Fulan',
        'status_mahasiswa' => 'aktif',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ]);
    $response2->assertSessionHasErrors('nim');

    $this->assertGuest();
});

test('5. registrasi gagal jika NIM mengandung huruf atau karakter khusus', function () {
    $response = $this->post(route('register.store'), [
        'nim' => '210109200A',
        'name' => 'Fulan bin Fulan',
        'status_mahasiswa' => 'aktif',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ]);

    $response->assertSessionHasErrors('nim');
    $this->assertGuest();
});

test('6. registrasi dengan NIM berawalan angka 0 dipertahankan sebagai string murni', function () {
    $response = $this->post(route('register.store'), [
        'nim' => '0101092001',
        'name' => 'Alumni Senior',
        'status_mahasiswa' => 'alumni',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ]);

    $response->assertRedirect(route('verification.status'));

    $user = User::where('name', 'Alumni Senior')->first();
    expect($user)->not->toBeNull()
        ->and($user->nim)->toBe('0101092001')
        ->and($user->angkatan)->toBe(2001);
});

test('7. registrasi gagal jika nama lengkap kosong', function () {
    $response = $this->post(route('register.store'), [
        'nim' => '2101092002',
        'status_mahasiswa' => 'aktif',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ]);

    $response->assertSessionHasErrors('name');
    $this->assertGuest();
});

test('8. registrasi gagal jika status_mahasiswa kosong atau tidak valid', function () {
    $response1 = $this->post(route('register.store'), [
        'nim' => '2101092003',
        'name' => 'Fulan bin Fulan',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ]);
    $response1->assertSessionHasErrors('status_mahasiswa');

    $response2 = $this->post(route('register.store'), [
        'nim' => '2101092003',
        'name' => 'Fulan bin Fulan',
        'status_mahasiswa' => 'dosen',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ]);
    $response2->assertSessionHasErrors('status_mahasiswa');

    $this->assertGuest();
});

test('9. email opsional dan tidak mewajibkan email institusi @pnp.ac.id', function () {
    // Mendaftar dengan email pribadi umum (misal gmail.com)
    $response = $this->post(route('register.store'), [
        'nim' => '2101092004',
        'name' => 'Ahmad Gmail',
        'status_mahasiswa' => 'aktif',
        'email' => 'ahmad.personal@gmail.com',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ]);

    $response->assertRedirect(route('verification.status'));
    $user = User::where('nim', '2101092004')->first();
    expect($user)->not->toBeNull()
        ->and($user->email)->toBe('ahmad.personal@gmail.com');
});

test('10. pendaftaran tanpa email berhasil dan menyimpan email sebagai null', function () {
    $response = $this->post(route('register.store'), [
        'nim' => '2101092005',
        'name' => 'Budi Tanpa Email',
        'status_mahasiswa' => 'aktif',
        'email' => '',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ]);

    $response->assertRedirect(route('verification.status'));
    $user = User::where('nim', '2101092005')->first();
    expect($user)->not->toBeNull()
        ->and($user->email)->toBeNull();
});

test('11. registrasi gagal jika password tidak diisi atau konfirmasi tidak cocok', function () {
    // Password kosong
    $response1 = $this->post(route('register.store'), [
        'nim' => '2101092006',
        'name' => 'Fulan',
        'status_mahasiswa' => 'aktif',
        'password' => '',
        'password_confirmation' => '',
    ]);
    $response1->assertSessionHasErrors('password');

    // Konfirmasi tidak cocok
    $response2 = $this->post(route('register.store'), [
        'nim' => '2101092006',
        'name' => 'Fulan',
        'status_mahasiswa' => 'aktif',
        'password' => 'secret123',
        'password_confirmation' => 'different123',
    ]);
    $response2->assertSessionHasErrors('password');

    $this->assertGuest();
});

/*
|--------------------------------------------------------------------------
| B. Pembentukan Akun dan Keamanan
|--------------------------------------------------------------------------
*/

test('12. akun baru tersimpan dengan role=user, status_verifikasi=menunggu, ktm_path=null, dan password ter-hash', function () {
    $response = $this->post(route('register.store'), [
        'nim' => '2101092007',
        'name' => 'Randi Safar',
        'status_mahasiswa' => 'aktif',
        'password' => 'SecretPass123!',
        'password_confirmation' => 'SecretPass123!',
    ]);

    $response->assertRedirect(route('verification.status'));

    $user = User::where('nim', '2101092007')->first();
    expect($user)->not->toBeNull()
        ->and($user->role)->toBe('user')
        ->and($user->status_verifikasi)->toBe('menunggu')
        ->and($user->is_suspended)->toBeFalse()
        ->and($user->ktm_path)->toBeNull()
        ->and(Hash::check('SecretPass123!', $user->password))->toBeTrue();
});

test('13. injeksi role=admin atau status_verifikasi=disetujui melalui request registrasi diabaikan', function () {
    $response = $this->post(route('register.store'), [
        'nim' => '2101092008',
        'name' => 'Hacker User',
        'status_mahasiswa' => 'aktif',
        'password' => 'HackerPass123!',
        'password_confirmation' => 'HackerPass123!',
        'role' => 'admin',
        'status_verifikasi' => 'disetujui',
        'is_suspended' => true,
        'ktm_path' => '/malicious/path.jpg',
    ]);

    $response->assertRedirect(route('verification.status'));

    $user = User::where('nim', '2101092008')->first();
    expect($user)->not->toBeNull()
        ->and($user->role)->toBe('user')
        ->and($user->status_verifikasi)->toBe('menunggu')
        ->and($user->is_suspended)->toBeFalse()
        ->and($user->ktm_path)->toBeNull();
});

test('14. registrasi dengan NIM yang sudah terdaftar ditolak dan tidak mengubah data akun lama', function () {
    $existingUser = User::factory()->verified()->create([
        'nim' => '2101092009',
        'name' => 'Akun Lama',
    ]);

    $response = $this->post(route('register.store'), [
        'nim' => '2101092009',
        'name' => 'Akun Peniru',
        'status_mahasiswa' => 'alumni',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertSessionHasErrors('nim');
    expect($existingUser->fresh()->name)->toBe('Akun Lama');
});

/*
|--------------------------------------------------------------------------
| C. Pemetaan Prodi dari NIM
|--------------------------------------------------------------------------
*/

test('15. kode prodi dikenal dipetakan ke prodi_codes dan kode_prodi_tidak_dikenal bernilai false', function () {
    // Siapkan jurusan dan prodi
    $jurusan = Jurusan::factory()->create(['nama' => 'Teknologi Informasi']);
    $prodi = ProdiCode::factory()->create([
        'jurusan_id' => $jurusan->id,
        'kode' => '0109',
        'nama_prodi' => 'D3 Manajemen Informatika',
    ]);

    // NIM: 2201092010 -> Digit 3-6 adalah 0109, angkatan 2022
    $response = $this->post(route('register.store'), [
        'nim' => '2201092010',
        'name' => 'Mahasiswa MI',
        'status_mahasiswa' => 'aktif',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ]);

    $response->assertRedirect(route('verification.status'));

    $user = User::where('nim', '2201092010')->first();
    expect($user)->not->toBeNull()
        ->and($user->kode_prodi_nim)->toBe('0109')
        ->and($user->angkatan)->toBe(2022)
        ->and($user->prodi_code_id)->toBe($prodi->id)
        ->and($user->kode_prodi_tidak_dikenal)->toBeFalse();
});

test('16. kode prodi tidak dikenal tetap diterima dengan fallback kode_prodi_tidak_dikenal=true', function () {
    // NIM: 2399991001 -> Digit 3-6 adalah 9999 (tidak ada di prodi_codes)
    $response = $this->post(route('register.store'), [
        'nim' => '2399991001',
        'name' => 'Mahasiswa Prodi Misterius',
        'status_mahasiswa' => 'aktif',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ]);

    $response->assertRedirect(route('verification.status'));

    $user = User::where('nim', '2399991001')->first();
    expect($user)->not->toBeNull()
        ->and($user->kode_prodi_nim)->toBe('9999')
        ->and($user->angkatan)->toBe(2023)
        ->and($user->prodi_code_id)->toBeNull()
        ->and($user->kode_prodi_tidak_dikenal)->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| D. Hak Akses dan Integrasi Pasca Registrasi
|--------------------------------------------------------------------------
*/

test('17. setelah registrasi pengguna berstatus menunggu dialihkan ke verification.status dan tidak dapat membuka dashboard', function () {
    $response = $this->post(route('register.store'), [
        'nim' => '2301092011',
        'name' => 'User Baru',
        'status_mahasiswa' => 'aktif',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ]);

    // Berhasil dan terautentikasi
    $this->assertAuthenticated();
    $response->assertRedirect(route('verification.status'));

    // Mencoba mengakses dashboard secara langsung: dialihkan ke verification.status
    $dashboardResponse = $this->get(route('dashboard'));
    $dashboardResponse->assertRedirect(route('verification.status'));
});

test('18. akun baru tidak dapat mengakses route admin (403 Forbidden)', function () {
    $this->post(route('register.store'), [
        'nim' => '2301092012',
        'name' => 'User Biasa',
        'status_mahasiswa' => 'aktif',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ]);

    $response = $this->get(route('admin.dashboard'));
    $response->assertForbidden();
});

test('19. akun baru yang berstatus menunggu dapat logout dan login kembali ke verification.status', function () {
    $this->post(route('register.store'), [
        'nim' => '2301092013',
        'name' => 'User Kembali',
        'status_mahasiswa' => 'aktif',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ]);

    // Logout
    $this->post(route('logout'));
    $this->assertGuest();

    // Login kembali dengan NIM dan password
    $loginResponse = $this->post(route('login.store'), [
        'nim' => '2301092013',
        'password' => 'secret123',
    ]);

    $this->assertAuthenticated();
    $loginResponse->assertRedirect(route('verification.status'));
});

test('20. endpoint passkey tetap nonaktif setelah registrasi aktif (404 Not Found)', function () {
    $this->post('/passkeys/login')->assertNotFound();
    $this->get('/passkeys/login/options')->assertNotFound();
    $this->post('/user/passkeys')->assertNotFound();
});
