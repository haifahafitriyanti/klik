<?php

use App\Models\Jurusan;
use App\Models\ProdiCode;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('ktm');
});

/*
|--------------------------------------------------------------------------
| A. PENGUJIAN OTORISASI AKSES PANEL ADMIN
|--------------------------------------------------------------------------
*/

test('1. tamu (guest) dialihkan ke login saat mengakses dashboard admin', function () {
    $response = $this->get(route('admin.dashboard'));

    $response->assertRedirect(route('login'));
});

test('2. user biasa berstatus disetujui ditolak (403 Forbidden) saat mengakses dashboard admin', function () {
    $user = User::factory()->verified()->create([
        'role' => 'user',
    ]);

    $response = $this->actingAs($user)->get(route('admin.dashboard'));

    $response->assertForbidden();
});

test('3. user biasa berstatus menunggu atau ditolak ditolak (403 Forbidden) saat mengakses dashboard admin', function () {
    $waitingUser = User::factory()->unverified()->create(['role' => 'user', 'status_verifikasi' => 'menunggu']);
    $rejectedUser = User::factory()->unverified()->create(['role' => 'user', 'status_verifikasi' => 'ditolak']);

    $this->actingAs($waitingUser)->get(route('admin.dashboard'))->assertForbidden();
    $this->actingAs($rejectedUser)->get(route('admin.dashboard'))->assertForbidden();
});

test('4. admin suspended diblokir dan sesinya diputus saat mengakses dashboard admin', function () {
    $admin = User::factory()->admin()->suspended()->create();

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertRedirect(route('login'));
    $this->assertGuest();
});

test('5. admin aktif dapat mengakses dashboard admin dan melihat komponen Inertia', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/Dashboard')
        ->has('stats')
        ->has('submissions')
        ->has('filters')
    );
});

/*
|--------------------------------------------------------------------------
| B. PENGUJIAN DAFTAR PENGAJUAN DAN FILTER
|--------------------------------------------------------------------------
*/

test('6. dashboard membedakan akun menunggu tanpa KTM dan akun dengan KTM terunggah', function () {
    $admin = User::factory()->admin()->create();

    // User A: menunggu tanpa KTM
    User::factory()->unverified()->create([
        'nim' => '2101092001',
        'ktm_path' => null,
        'status_verifikasi' => 'menunggu',
    ]);

    // User B: menunggu dengan KTM
    User::factory()->unverified()->create([
        'nim' => '2101092002',
        'ktm_path' => 'ktm_sample_b.jpg',
        'status_verifikasi' => 'menunggu',
    ]);

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/Dashboard')
        ->where('stats.total_menunggu', 2)
        ->where('stats.siap_verifikasi', 1)
        ->where('stats.menunggu_ktm', 1)
    );
});

test('7. data prodi dan jurusan ditampilkan sesuai relasi master data', function () {
    $admin = User::factory()->admin()->create();

    $jurusan = Jurusan::create(['nama' => 'Teknologi Informasi']);
    $prodi = ProdiCode::create([
        'jurusan_id' => $jurusan->id,
        'kode' => '0109',
        'nama_prodi' => 'D3 Manajemen Informatika',
        'jenjang' => 'D3',
        'created_by' => $admin->id,
    ]);

    $user = User::factory()->unverified()->create([
        'nim' => '2101092003',
        'prodi_code_id' => $prodi->id,
        'kode_prodi_nim' => '0109',
        'kode_prodi_tidak_dikenal' => false,
    ]);

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->has('submissions.data', 1, fn (Assert $item) => $item
            ->where('nim', '2101092003')
            ->where('prodi_name', 'D3 Manajemen Informatika')
            ->where('jurusan_name', 'Teknologi Informasi')
            ->where('kode_prodi_tidak_dikenal', false)
            ->etc()
        )
    );
});

test('8. tanda kode prodi tidak dikenal ditampilkan jelas pada submission', function () {
    $admin = User::factory()->admin()->create();

    User::factory()->unverified()->create([
        'nim' => '2199992004',
        'prodi_code_id' => null,
        'kode_prodi_nim' => '9999',
        'kode_prodi_tidak_dikenal' => true,
    ]);

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->has('submissions.data', 1, fn (Assert $item) => $item
            ->where('nim', '2199992004')
            ->where('kode_prodi_tidak_dikenal', true)
            ->etc()
        )
    );
});

test('9. filter status dan filter ktm menyaring data dengan benar', function () {
    $admin = User::factory()->admin()->create();

    $approvedUser = User::factory()->verified()->create(['nim' => '2101092010']);
    $rejectedUser = User::factory()->unverified()->create(['nim' => '2101092011', 'status_verifikasi' => 'ditolak', 'ktm_path' => 'ktm_rej.jpg']);
    $waitingWithKtm = User::factory()->unverified()->create(['nim' => '2101092012', 'status_verifikasi' => 'menunggu', 'ktm_path' => 'ktm_wait.jpg']);
    $waitingNoKtm = User::factory()->unverified()->create(['nim' => '2101092013', 'status_verifikasi' => 'menunggu', 'ktm_path' => null]);

    // Filter status ditolak
    $resDitolak = $this->actingAs($admin)->get(route('admin.dashboard', ['status' => 'ditolak']));
    $resDitolak->assertInertia(fn (Assert $page) => $page
        ->has('submissions.data', 1)
        ->where('submissions.data.0.nim', '2101092011')
    );

    // Filter status menunggu dengan ktm ada
    $resWaitKtm = $this->actingAs($admin)->get(route('admin.dashboard', ['status' => 'menunggu', 'has_ktm' => 'ada']));
    $resWaitKtm->assertInertia(fn (Assert $page) => $page
        ->has('submissions.data', 1)
        ->where('submissions.data.0.nim', '2101092012')
    );

    // Filter pencarian berdasarkan NIM
    $resSearch = $this->actingAs($admin)->get(route('admin.dashboard', ['search' => '2101092013', 'status' => 'semua']));
    $resSearch->assertInertia(fn (Assert $page) => $page
        ->has('submissions.data', 1)
        ->where('submissions.data.0.nim', '2101092013')
    );
});

/*
|--------------------------------------------------------------------------
| C. PENGUJIAN AKSES STREAMING FOTO KTM PRIVAT
|--------------------------------------------------------------------------
*/

test('10. admin aktif dapat mengakses streaming foto KTM pengguna secara privat dengan header generik tanpa membocorkan NIM', function () {
    $admin = User::factory()->admin()->create();

    $filename = 'ktm_test_stream_01.jpg';
    Storage::disk('ktm')->put($filename, 'dummy-ktm-binary-content');

    $user = User::factory()->unverified()->create([
        'nim' => '2101092020',
        'ktm_path' => $filename,
    ]);

    $response = $this->actingAs($admin)->get(route('admin.verifikasi.ktm', $user));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'image/jpeg');
    $response->assertHeader('Content-Disposition', 'inline; filename="ktm_document.jpg"');
    expect($response->headers->get('Content-Disposition'))->not->toContain('2101092020')
        ->and($response->headers->get('Cache-Control'))->toContain('no-store')
        ->and($response->headers->get('Cache-Control'))->toContain('private');
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    expect($response->getContent())->toBe('dummy-ktm-binary-content');
});

test('11. user biasa dilarang mengakses endpoint foto KTM (403 Forbidden)', function () {
    $regularUser = User::factory()->verified()->create();

    $targetUser = User::factory()->unverified()->create([
        'ktm_path' => 'ktm_target.jpg',
    ]);
    Storage::disk('ktm')->put('ktm_target.jpg', 'target-content');

    $response = $this->actingAs($regularUser)->get(route('admin.verifikasi.ktm', $targetUser));

    $response->assertForbidden();
});

test('12. tamu dialihkan ke login saat mengakses endpoint foto KTM', function () {
    $targetUser = User::factory()->unverified()->create([
        'ktm_path' => 'ktm_target.jpg',
    ]);

    $response = $this->get(route('admin.verifikasi.ktm', $targetUser));

    $response->assertRedirect(route('login'));
});

test('13. admin suspended dilarang mengakses endpoint foto KTM', function () {
    $admin = User::factory()->admin()->suspended()->create();
    $targetUser = User::factory()->unverified()->create([
        'ktm_path' => 'ktm_target.jpg',
    ]);

    $response = $this->actingAs($admin)->get(route('admin.verifikasi.ktm', $targetUser));

    $response->assertRedirect(route('login'));
});

test('14. foto KTM akun yang tidak ada atau berkas fisik hilang mengembalikan 404', function () {
    $admin = User::factory()->admin()->create();

    // User tanpa ktm_path
    $userNoKtm = User::factory()->unverified()->create(['ktm_path' => null]);
    $this->actingAs($admin)->get(route('admin.verifikasi.ktm', $userNoKtm))->assertNotFound();

    // User dengan ktm_path tetapi file fisiknya tidak ada di disk
    $userMissingFile = User::factory()->unverified()->create(['ktm_path' => 'ktm_nonexistent.jpg']);
    $this->actingAs($admin)->get(route('admin.verifikasi.ktm', $userMissingFile))->assertNotFound();
});

test('15. endpoint foto KTM menolak akses jika target adalah akun administrator', function () {
    $admin1 = User::factory()->admin()->create();
    $admin2 = User::factory()->admin()->create();

    $response = $this->actingAs($admin1)->get(route('admin.verifikasi.ktm', $admin2));

    $response->assertForbidden();
});

/*
|--------------------------------------------------------------------------
| D. PENGUJIAN AKSI PERSETUJUAN (APPROVE)
|--------------------------------------------------------------------------
*/

test('16. admin dapat menyetujui akun berstatus menunggu yang memiliki KTM', function () {
    $admin = User::factory()->admin()->create();

    $filename = 'ktm_approve_test.jpg';
    Storage::disk('ktm')->put($filename, 'content');

    $user = User::factory()->unverified()->create([
        'nim' => '2101092030',
        'ktm_path' => $filename,
        'status_verifikasi' => 'menunggu',
        'alasan_penolakan' => 'Alasan lama',
    ]);

    $token = hash_hmac('sha256', (string) $user->ktm_path.'|'.$user->updated_at?->timestamp, (string) config('app.key'));

    $response = $this->actingAs($admin)->post(route('admin.verifikasi.approve', $user), [
        'document_token' => $token,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('status');

    $user->refresh();
    expect($user->status_verifikasi)->toBe('disetujui')
        ->and($user->verified_by)->toBe($admin->id)
        ->and($user->verified_at)->not->toBeNull()
        ->and($user->alasan_penolakan)->toBeNull()
        ->and($user->role)->toBe('user')
        ->and($user->nim)->toBe('2101092030');

    // Pengguna yang disetujui kini dapat mengakses dashboard
    $this->actingAs($user)->get(route('dashboard'))->assertOk();
});

test('17. admin tidak dapat menyetujui akun yang belum mengunggah foto KTM', function () {
    $admin = User::factory()->admin()->create();

    $user = User::factory()->unverified()->create([
        'nim' => '2101092031',
        'ktm_path' => null,
        'status_verifikasi' => 'menunggu',
    ]);

    $response = $this->actingAs($admin)->post(route('admin.verifikasi.approve', $user));

    $response->assertSessionHasErrors('verifikasi');

    $user->refresh();
    expect($user->status_verifikasi)->toBe('menunggu')
        ->and($user->verified_by)->toBeNull();
});

/*
|--------------------------------------------------------------------------
| E. PENGUJIAN AKSI PENOLAKAN (REJECT)
|--------------------------------------------------------------------------
*/

test('18. admin dapat menolak pengajuan verifikasi dengan menyertakan alasan penolakan', function () {
    $admin = User::factory()->admin()->create();

    $filename = 'ktm_reject_test.jpg';
    Storage::disk('ktm')->put($filename, 'content');

    $user = User::factory()->unverified()->create([
        'nim' => '2101092040',
        'ktm_path' => $filename,
        'status_verifikasi' => 'menunggu',
    ]);

    $reason = 'Foto KTM buram pada bagian nama dan nomor identitas mahasiswa.';

    $response = $this->actingAs($admin)->post(route('admin.verifikasi.reject', $user), [
        'alasan_penolakan' => $reason,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('status');

    $user->refresh();
    expect($user->status_verifikasi)->toBe('ditolak')
        ->and($user->alasan_penolakan)->toBe($reason)
        ->and($user->verified_by)->toBe($admin->id)
        ->and($user->verified_at)->not->toBeNull()
        ->and($user->role)->toBe('user');

    // Berkas KTM tetap tersimpan di storage privat untuk keperluan audit/pengajuan ulang
    Storage::disk('ktm')->assertExists($filename);

    // Pengguna yang ditolak melihat alasan penolakan di halaman status verifikasi miliknya
    $this->actingAs($user)->get(route('verification.status'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('user.status_verifikasi', 'ditolak')
            ->where('user.alasan_penolakan', $reason)
        );
});

test('19. penolakan ditolak validasi jika alasan kosong atau kurang dari 5 karakter', function () {
    $admin = User::factory()->admin()->create();

    $filename = 'ktm_reject_val.jpg';
    Storage::disk('ktm')->put($filename, 'content');

    $user = User::factory()->unverified()->create([
        'ktm_path' => $filename,
        'status_verifikasi' => 'menunggu',
    ]);

    // Kosong
    $this->actingAs($admin)->post(route('admin.verifikasi.reject', $user), [
        'alasan_penolakan' => '',
    ])->assertSessionHasErrors('alasan_penolakan');

    // Kurang dari 5 karakter
    $this->actingAs($admin)->post(route('admin.verifikasi.reject', $user), [
        'alasan_penolakan' => 'no',
    ])->assertSessionHasErrors('alasan_penolakan');

    $user->refresh();
    expect($user->status_verifikasi)->toBe('menunggu');
});

/*
|--------------------------------------------------------------------------
| F. PENGUJIAN KEAMANAN KONKURENSI DAN INTEGRITAS
|--------------------------------------------------------------------------
*/

test('20. keputusan ganda ditolak jika akun telah diproses sebelumnya', function () {
    $adminA = User::factory()->admin()->create();
    $adminB = User::factory()->admin()->create();

    $filename = 'ktm_double_decision.jpg';
    Storage::disk('ktm')->put($filename, 'content');

    $user = User::factory()->unverified()->create([
        'ktm_path' => $filename,
        'status_verifikasi' => 'menunggu',
    ]);

    // Admin A menyetujui akun terlebih dahulu
    $this->actingAs($adminA)->post(route('admin.verifikasi.approve', $user))->assertSessionHas('status');

    // Admin B mencoba menolak akun yang sudah disetujui
    $responseB = $this->actingAs($adminB)->post(route('admin.verifikasi.reject', $user), [
        'alasan_penolakan' => 'Dokumen ini tidak sah menurut saya.',
    ]);

    $responseB->assertSessionHasErrors('verifikasi');

    // Keputusan Admin A tetap bertahan
    $user->refresh();
    expect($user->status_verifikasi)->toBe('disetujui')
        ->and($user->verified_by)->toBe($adminA->id);
});

test('21. keputusan ditolak jika dokumen KTM telah diganti oleh pengguna (stale document token check)', function () {
    $admin = User::factory()->admin()->create();

    $oldFile = 'ktm_old_version.jpg';
    Storage::disk('ktm')->put($oldFile, 'old-version');

    $user = User::factory()->unverified()->create([
        'ktm_path' => $oldFile,
        'status_verifikasi' => 'menunggu',
    ]);

    // Admin membuka halaman dan mendapatkan token untuk dokumen lama
    $staleToken = hash_hmac('sha256', (string) $user->ktm_path.'|'.$user->updated_at?->timestamp, (string) config('app.key'));

    // Pengguna mengunggah ulang dokumen baru sebelum admin menekan tombol setujui
    $newFile = 'ktm_new_version.jpg';
    Storage::disk('ktm')->put($newFile, 'new-version');
    $user->forceFill([
        'ktm_path' => $newFile,
        'updated_at' => now()->addMinute(),
    ])->save();

    // Admin mengirim request dengan stale token
    $response = $this->actingAs($admin)->post(route('admin.verifikasi.approve', $user), [
        'document_token' => $staleToken,
    ]);

    $response->assertSessionHasErrors('verifikasi');

    $user->refresh();
    expect($user->status_verifikasi)->toBe('menunggu')
        ->and($user->verified_by)->toBeNull();
});

test('22. user biasa tidak dapat memanggil endpoint approve atau reject', function () {
    $regularUser = User::factory()->verified()->create();
    $targetUser = User::factory()->unverified()->create(['ktm_path' => 'ktm.jpg']);

    $this->actingAs($regularUser)->post(route('admin.verifikasi.approve', $targetUser))->assertForbidden();
    $this->actingAs($regularUser)->post(route('admin.verifikasi.reject', $targetUser), [
        'alasan_penolakan' => 'Penolakan ilegal',
    ])->assertForbidden();
});

test('23. upaya menyetujui akun administrator ditolak dengan 403 Forbidden', function () {
    $admin1 = User::factory()->admin()->create();
    $admin2 = User::factory()->admin()->create();

    $this->actingAs($admin1)->post(route('admin.verifikasi.approve', $admin2))->assertForbidden();
    $this->actingAs($admin1)->post(route('admin.verifikasi.reject', $admin2), [
        'alasan_penolakan' => 'Tolak admin',
    ])->assertForbidden();
});

test('24. request persetujuan tidak dapat menyuntikkan verified_by, role, atau mengubah NIM', function () {
    $admin = User::factory()->admin()->create();
    $fakeAdmin = User::factory()->admin()->create();

    $filename = 'ktm_injection_test.jpg';
    Storage::disk('ktm')->put($filename, 'content');

    $user = User::factory()->unverified()->create([
        'nim' => '2101092099',
        'ktm_path' => $filename,
        'status_verifikasi' => 'menunggu',
        'role' => 'user',
    ]);

    $this->actingAs($admin)->post(route('admin.verifikasi.approve', $user), [
        'verified_by' => $fakeAdmin->id,
        'role' => 'admin',
        'nim' => '9999999999',
    ])->assertSessionHas('status');

    $user->refresh();
    expect($user->status_verifikasi)->toBe('disetujui')
        ->and($user->verified_by)->toBe($admin->id) // Tetap ID admin yang login, bukan fakeAdmin
        ->and($user->role)->toBe('user') // Role tidak berubah menjadi admin
        ->and($user->nim)->toBe('2101092099'); // NIM tidak berubah
});
