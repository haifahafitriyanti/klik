<?php

use App\Http\Responses\LoginResponse;
use App\Models\User;
use Illuminate\Http\Request;

test('1. pengguna dapat login menggunakan NIM 10 digit dan password yang benar', function () {
    $user = User::factory()->verified()->create([
        'nim' => '2101092001',
        'password' => 'secret123',
    ]);

    $response = $this->post(route('login.store'), [
        'nim' => '2101092001',
        'password' => 'secret123',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('home'));
});

test('2. login ditolak jika password salah', function () {
    $user = User::factory()->verified()->create([
        'nim' => '2101092002',
        'password' => 'secret123',
    ]);

    $response = $this->post(route('login.store'), [
        'nim' => '2101092002',
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('nim');
});

test('3. login ditolak jika NIM tidak terdaftar', function () {
    $response = $this->post(route('login.store'), [
        'nim' => '9999999999',
        'password' => 'secret123',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('nim');
});

test('4. NIM dengan format selain 10 digit ditolak validasi', function () {
    // 9 digit
    $response1 = $this->post(route('login.store'), [
        'nim' => '210109200',
        'password' => 'secret123',
    ]);
    $response1->assertSessionHasErrors('nim');

    // 11 digit
    $response2 = $this->post(route('login.store'), [
        'nim' => '21010920001',
        'password' => 'secret123',
    ]);
    $response2->assertSessionHasErrors('nim');

    // Mengandung huruf
    $response3 = $this->post(route('login.store'), [
        'nim' => '210109200A',
        'password' => 'secret123',
    ]);
    $response3->assertSessionHasErrors('nim');
});

test('5. NIM dengan angka nol di depan tetap valid dan mempertahankan leading zero', function () {
    $user = User::factory()->verified()->create([
        'nim' => '0101092001',
        'password' => 'secret123',
    ]);

    $response = $this->post(route('login.store'), [
        'nim' => '0101092001',
        'password' => 'secret123',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('home'));
});

test('6. email tidak dapat digunakan sebagai credential pengganti NIM', function () {
    $user = User::factory()->verified()->create([
        'email' => 'mahasiswa@pnp.ac.id',
        'nim' => '2101092003',
        'password' => 'secret123',
    ]);

    $response = $this->post(route('login.store'), [
        'nim' => 'mahasiswa@pnp.ac.id',
        'password' => 'secret123',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('nim');
});

test('7. pesan kesalahan login tidak membocorkan keberadaan suatu NIM', function () {
    $user = User::factory()->verified()->create([
        'nim' => '2101092004',
        'password' => 'secret123',
    ]);

    // Percobaan pada NIM terdaftar dengan password salah
    $response1 = $this->post(route('login.store'), [
        'nim' => '2101092004',
        'password' => 'wrong-pass',
    ]);
    $response1->assertSessionHasErrors(['nim' => trans('auth.failed')]);

    // Percobaan pada NIM yang tidak terdaftar
    $response2 = $this->post(route('login.store'), [
        'nim' => '2101092999',
        'password' => 'any-pass',
    ]);
    $response2->assertSessionHasErrors(['nim' => trans('auth.failed')]);
});

test('8 & 9. administrator berhasil login dengan NIM dan diarahkan ke area admin', function () {
    $admin = User::factory()->admin()->create([
        'nim' => '2101099999',
        'password' => 'adminpass',
    ]);

    $response = $this->post(route('login.store'), [
        'nim' => '2101099999',
        'password' => 'adminpass',
    ]);

    $this->assertAuthenticatedAs($admin);
    $response->assertRedirect(route('admin.dashboard'));
});

test('10. pengguna biasa yang disetujui diarahkan ke homepage marketplace', function () {
    $user = User::factory()->verified()->create([
        'nim' => '2101092005',
        'password' => 'secret123',
    ]);

    $response = $this->post(route('login.store'), [
        'nim' => '2101092005',
        'password' => 'secret123',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('home'));
});

test('11. pengguna biasa ditolak dengan 403 saat mengakses route admin secara langsung', function () {
    $user = User::factory()->verified()->create([
        'role' => 'user',
    ]);

    $response = $this->actingAs($user)->get(route('admin.dashboard'));

    $response->assertForbidden();
});

test('12 & 13. role tidak dapat dimanipulasi melalui request login', function () {
    $user = User::factory()->verified()->create([
        'nim' => '2101092006',
        'role' => 'user',
        'password' => 'secret123',
    ]);

    // Mengirim role=admin dalam payload POST
    $response = $this->post(route('login.store'), [
        'nim' => '2101092006',
        'password' => 'secret123',
        'role' => 'admin',
    ]);

    $this->assertAuthenticatedAs($user);
    // Tetap diarahkan ke homepage marketplace, bukan admin
    $response->assertRedirect(route('home'));
    expect($user->fresh()->role)->toBe('user');
});

test('14. akun disetujui memperoleh akses normal ke dashboard', function () {
    $user = User::factory()->verified()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
});

test('15. akun menunggu verifikasi hanya mendapat akses terbatas ke halaman status', function () {
    $user = User::factory()->create([
        'status_verifikasi' => 'menunggu',
    ]);

    // Login dialihkan ke status verifikasi
    $loginResponse = $this->post(route('login.store'), [
        'nim' => $user->nim,
        'password' => 'password',
    ]);
    $loginResponse->assertRedirect(route('verification.status'));

    // Akses ke status verifikasi diperbolehkan
    $statusResponse = $this->actingAs($user)->get(route('verification.status'));
    $statusResponse->assertOk();

    // Akses langsung ke dashboard pengguna dicegah dan dialihkan ke verification.status
    $dashboardResponse = $this->actingAs($user)->get(route('dashboard'));
    $dashboardResponse->assertRedirect(route('verification.status'));
});

test('16 & 17. akun ditolak dapat melihat status dan alasan penolakan miliknya sendiri dan dicegah dari dashboard', function () {
    $user = User::factory()->rejected('Foto KTM buram')->create([
        'nim' => '2101092007',
        'password' => 'secret123',
    ]);

    $loginResponse = $this->post(route('login.store'), [
        'nim' => '2101092007',
        'password' => 'secret123',
    ]);
    $loginResponse->assertRedirect(route('verification.status'));

    // Akses status verifikasi menampilkan alasan penolakan
    $statusResponse = $this->actingAs($user)->get(route('verification.status'));
    $statusResponse->assertOk();
    $statusResponse->assertSee('Foto KTM buram');

    // Akses dashboard pengguna dialihkan
    $dashboardResponse = $this->actingAs($user)->get(route('dashboard'));
    $dashboardResponse->assertRedirect(route('verification.status'));
});

test('18. akun pengguna suspended gagal login', function () {
    $user = User::factory()->suspended()->create([
        'nim' => '2101092008',
        'password' => 'secret123',
    ]);

    $response = $this->post(route('login.store'), [
        'nim' => '2101092008',
        'password' => 'secret123',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('nim');
});

test('19. akun admin suspended gagal login', function () {
    $admin = User::factory()->admin()->suspended()->create([
        'nim' => '2101099998',
        'password' => 'adminpass',
    ]);

    $response = $this->post(route('login.store'), [
        'nim' => '2101099998',
        'password' => 'adminpass',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('nim');
});

test('20. akun sistem internal suspended gagal login', function () {
    $systemAnchor = User::factory()->admin()->suspended()->create([
        'name' => 'System Anchor',
        'email' => 'system.internal@klik.local',
        'nim' => '0000000000',
        'password' => 'RandomSysPassword123!',
    ]);

    $response = $this->post(route('login.store'), [
        'nim' => '0000000000',
        'password' => 'RandomSysPassword123!',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('nim');
});

test('21. session lama pengguna suspended otomatis diputus saat mengakses route terproteksi', function () {
    $user = User::factory()->verified()->create();

    // Simulasikan session aktif
    $this->actingAs($user);

    // Lalu akun dibekukan di database menggunakan forceFill (karena is_suspended terlindungi dari mass assignment)
    $user->forceFill(['is_suspended' => true])->save();

    // Saat mengakses route web terproteksi
    $response = $this->get(route('dashboard'));

    // Session diputus dan dialihkan ke login
    $this->assertGuest();
    $response->assertRedirect(route('login'));
});

test('22. rate limiting membatasi percobaan login setelah 5 kali gagal', function () {
    $user = User::factory()->create(['nim' => '2101092009']);

    for ($i = 0; $i < 5; $i++) {
        $this->post(route('login.store'), [
            'nim' => '2101092009',
            'password' => 'wrong-pass',
        ]);
    }

    $response = $this->post(route('login.store'), [
        'nim' => '2101092009',
        'password' => 'wrong-pass',
    ]);

    $response->assertTooManyRequests();
});

test('23. logout membatalkan session dan mengalihkan pengguna', function () {
    $user = User::factory()->verified()->create();

    $response = $this->actingAs($user)->post(route('logout'));

    $this->assertGuest();
    $response->assertRedirect(route('home'));
});

test('24. session diregenerasi setelah login berhasil', function () {
    $user = User::factory()->verified()->create([
        'nim' => '2101092010',
        'password' => 'secret123',
    ]);

    $previousSessionId = session()->getId();

    $this->post(route('login.store'), [
        'nim' => '2101092010',
        'password' => 'secret123',
    ]);

    expect(session()->getId())->not->toBe($previousSessionId);
});

test('25. guest tidak dapat mengakses halaman terproteksi dan diarahkan ke login', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));

    $adminResponse = $this->get(route('admin.dashboard'));
    $adminResponse->assertRedirect(route('login'));
});

test('26. pengguna yang sudah login dialihkan sesuai role dan status ketika membuka login kembali', function () {
    // 1. Admin dialihkan ke admin.dashboard
    $admin = User::factory()->admin()->create();
    $responseAdmin = $this->actingAs($admin)->get(route('login'));
    $responseAdmin->assertRedirect(route('admin.dashboard'));

    // 2. User disetujui dialihkan ke homepage marketplace
    $userApproved = User::factory()->verified()->create();
    $responseApproved = $this->actingAs($userApproved)->get(route('login'));
    $responseApproved->assertRedirect(route('home'));

    // 3. User menunggu verifikasi dialihkan ke verification.status
    $userPending = User::factory()->create(['status_verifikasi' => 'menunggu']);
    $responsePending = $this->actingAs($userPending)->get(route('login'));
    $responsePending->assertRedirect(route('verification.status'));
});

test('27. seluruh endpoint login dan konfirmasi passkey dinonaktifkan secara fundamental (404 Not Found)', function () {
    $this->post('/passkeys/login')->assertNotFound();
    $this->get('/passkeys/login/options')->assertNotFound();
    $this->post('/passkeys/confirm')->assertNotFound();
    $this->get('/passkeys/confirm/options')->assertNotFound();
});

test('28. pendaftaran akun publik via register aktif untuk mahasiswa dan alumni pada Fase 3.2', function () {
    // GET /register dapat diakses guest
    $getResponse = $this->get('/register');
    $getResponse->assertOk();

    // POST /register berhasil dengan NIM dan password
    $postResponse = $this->post('/register', [
        'nim' => '2101092099',
        'name' => 'Calon Pengguna',
        'status_mahasiswa' => 'aktif',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ]);
    $postResponse->assertRedirect(route('verification.status'));
    $this->assertAuthenticated();
});

test('29. akun suspended dengan password salah tetap mendapatkan pesan error generik auth.failed', function () {
    $user = User::factory()->suspended()->create([
        'nim' => '2101092025',
        'password' => 'CorrectPassword123!',
    ]);

    // Percobaan dengan password yang salah: pesan HARUS generik auth.failed
    $failedResponse = $this->post(route('login.store'), [
        'nim' => '2101092025',
        'password' => 'WrongPassword!',
    ]);
    $this->assertGuest();
    $failedResponse->assertSessionHasErrors(['nim' => trans('auth.failed')]);

    // Percobaan dengan password yang benar: baru menampilkan pesan suspended
    $suspendedResponse = $this->post(route('login.store'), [
        'nim' => '2101092025',
        'password' => 'CorrectPassword123!',
    ]);
    $this->assertGuest();
    $suspendedResponse->assertSessionHasErrors([
        'nim' => 'Akun Anda sedang ditangguhkan. Silakan hubungi administrator.',
    ]);
});

test('30. seluruh endpoint registrasi dan pengelolaan passkey dinonaktifkan secara fundamental (404 Not Found)', function () {
    $user = User::factory()->verified()->create();

    $this->actingAs($user)->post('/user/passkeys')->assertNotFound();
    $this->actingAs($user)->get('/user/passkeys/options')->assertNotFound();
    $this->actingAs($user)->delete('/user/passkeys/1')->assertNotFound();
});

test('31. LoginResponse memutuskan sesi jika akun terbukti suspended pada alur login atau 2FA', function () {
    $user = User::factory()->suspended()->create();

    $this->actingAs($user);

    $request = Request::create('/login', 'POST');
    $request->setUserResolver(fn () => $user);
    $request->setLaravelSession(session()->driver());

    $loginResponse = new LoginResponse;
    $response = $loginResponse->toResponse($request);

    expect(auth()->guard('web')->user())->toBeNull();
    expect($response->isRedirect(route('login')))->toBeTrue();
});

test('32. perubahan status verifikasi di tengah sesi langsung mengalihkan pengguna dari dashboard ke verifikasi.status', function () {
    $user = User::factory()->verified()->create();

    // User disetujui dapat mengakses dashboard
    $this->actingAs($user);
    $this->get(route('dashboard'))->assertOk();

    // Admin mengubah status verifikasi menjadi ditolak
    $user->forceFill(['status_verifikasi' => 'ditolak', 'alasan_penolakan' => 'Dokumen tidak valid'])->save();

    // Request berikutnya ke dashboard langsung dialihkan ke verification.status
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('verification.status'));
});

test('33. perubahan role admin menjadi user di tengah sesi langsung menolak akses ke /admin dengan 403', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);
    $this->get(route('admin.dashboard'))->assertOk();

    // Role admin dicabut menjadi user biasa
    $admin->forceFill(['role' => 'user'])->save();

    // Request berikutnya ke /admin ditolak 403
    $response = $this->get(route('admin.dashboard'));
    $response->assertForbidden();
});

test('34. intended URL yang aman dihormati saat pengguna disetujui login setelah dicegat middleware', function () {
    $user = User::factory()->verified()->create([
        'nim' => '2101092099',
        'password' => 'secret123',
    ]);

    // 1. Guest mencoba mengakses /dashboard yang terproteksi
    $initialResponse = $this->get(route('dashboard'));
    $initialResponse->assertRedirect(route('login'));

    // 2. Guest melakukan login dari sesi tersebut
    $loginResponse = $this->post(route('login.store'), [
        'nim' => '2101092099',
        'password' => 'secret123',
    ]);

    $this->assertAuthenticatedAs($user);
    // Intended URL (/dashboard) dihormati dan diprioritaskan
    $loginResponse->assertRedirect(route('dashboard'));
});

test('35. pengguna disetujui yang login langsung tanpa intended URL diarahkan ke homepage', function () {
    $user = User::factory()->verified()->create([
        'nim' => '2101092098',
        'password' => 'secret123',
    ]);

    $loginResponse = $this->post(route('login.store'), [
        'nim' => '2101092098',
        'password' => 'secret123',
    ]);

    $this->assertAuthenticatedAs($user);
    $loginResponse->assertRedirect(route('home'));
});

test('36. guest dapat mengakses homepage dan katalog publik secara bebas tanpa login', function () {
    $this->assertGuest();

    $homeResponse = $this->get(route('home'));
    $homeResponse->assertOk();

    $catalogResponse = $this->get(route('catalog.index'));
    $catalogResponse->assertOk();
});
