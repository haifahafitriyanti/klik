<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('ktm');
});

test('1. pengguna berstatus menunggu tanpa KTM melihat halaman verifikasi dengan has_ktm false', function () {
    $user = User::factory()->unverified()->create([
        'ktm_path' => null,
        'status_verifikasi' => 'menunggu',
    ]);

    $response = $this->actingAs($user)->get(route('verification.status'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('auth/VerificationStatus')
        ->where('user.name', $user->name)
        ->where('user.nim', $user->nim)
        ->where('user.status_verifikasi', 'menunggu')
        ->where('user.has_ktm', false)
        ->where('user.alasan_penolakan', null)
        ->missing('user.ktm_path')
    );
});

test('2. pengguna berstatus menunggu dengan KTM melihat halaman verifikasi dengan has_ktm true', function () {
    $user = User::factory()->unverified()->create([
        'ktm_path' => 'ktm_dummy_test.jpg',
        'status_verifikasi' => 'menunggu',
    ]);

    $response = $this->actingAs($user)->get(route('verification.status'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('auth/VerificationStatus')
        ->where('user.status_verifikasi', 'menunggu')
        ->where('user.has_ktm', true)
        ->missing('user.ktm_path')
    );
});

test('3. pengguna berstatus ditolak melihat alasan penolakan di halaman verifikasi', function () {
    $user = User::factory()->unverified()->create([
        'ktm_path' => 'ktm_rejected_sample.jpg',
        'status_verifikasi' => 'ditolak',
        'alasan_penolakan' => 'Foto KTM buram dan nomor induk mahasiswa tidak terbaca jelas.',
    ]);

    $response = $this->actingAs($user)->get(route('verification.status'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('auth/VerificationStatus')
        ->where('user.status_verifikasi', 'ditolak')
        ->where('user.has_ktm', true)
        ->where('user.alasan_penolakan', 'Foto KTM buram dan nomor induk mahasiswa tidak terbaca jelas.')
        ->missing('user.ktm_path')
    );
});

test('4. pengguna yang sudah disetujui dialihkan ke dashboard saat mengakses verifikasi status', function () {
    $user = User::factory()->verified()->create([
        'status_verifikasi' => 'disetujui',
    ]);

    $response = $this->actingAs($user)->get(route('verification.status'));

    $response->assertRedirect(route('dashboard'));
});

test('5. admin dialihkan ke admin dashboard saat mengakses verifikasi status', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get(route('verification.status'));

    $response->assertRedirect(route('admin.dashboard'));
});

test('6. tamu dialihkan ke login saat mengakses verifikasi status', function () {
    $response = $this->get(route('verification.status'));

    $response->assertRedirect(route('login'));
});

test('7. pengguna berstatus menunggu tanpa KTM berhasil mengunggah foto KTM JPEG <= 5MB', function () {
    $user = User::factory()->unverified()->create([
        'ktm_path' => null,
        'status_verifikasi' => 'menunggu',
    ]);

    $file = UploadedFile::fake()->image('ktm_mhs.jpeg', 800, 600)->size(2048); // 2 MB

    $response = $this->actingAs($user)->post(route('verification.ktm.upload'), [
        'ktm' => $file,
    ]);

    $response->assertRedirect(route('verification.status'));
    $response->assertSessionHas('status', 'Foto KTM berhasil diunggah dan sedang menunggu verifikasi administrator.');

    $user->refresh();
    expect($user->ktm_path)->not->toBeNull()
        ->and($user->ktm_path)->toStartWith('ktm_')
        ->and($user->status_verifikasi)->toBe('menunggu');

    Storage::disk('ktm')->assertExists($user->ktm_path);
});

test('8. pengguna berstatus menunggu tanpa KTM berhasil mengunggah foto KTM PNG', function () {
    $user = User::factory()->unverified()->create([
        'ktm_path' => null,
        'status_verifikasi' => 'menunggu',
    ]);

    $file = UploadedFile::fake()->image('ktm_mhs.png', 800, 600)->size(1500);

    $response = $this->actingAs($user)->post(route('verification.ktm.upload'), [
        'ktm' => $file,
    ]);

    $response->assertRedirect(route('verification.status'));

    $user->refresh();
    expect($user->ktm_path)->toEndWith('.png');
    Storage::disk('ktm')->assertExists($user->ktm_path);
});

test('9. pengguna berstatus menunggu tanpa KTM berhasil mengunggah foto KTM WebP', function () {
    $user = User::factory()->unverified()->create([
        'ktm_path' => null,
        'status_verifikasi' => 'menunggu',
    ]);

    $file = UploadedFile::fake()->image('ktm_mhs.webp', 800, 600)->size(1000);

    $response = $this->actingAs($user)->post(route('verification.ktm.upload'), [
        'ktm' => $file,
    ]);

    $response->assertRedirect(route('verification.status'));

    $user->refresh();
    expect($user->ktm_path)->toEndWith('.webp');
    Storage::disk('ktm')->assertExists($user->ktm_path);
});

test('10. upload ditolak jika file bukan gambar (PDF)', function () {
    $user = User::factory()->unverified()->create([
        'ktm_path' => null,
        'status_verifikasi' => 'menunggu',
    ]);

    $file = UploadedFile::fake()->create('dokumen_ktm.pdf', 1000, 'application/pdf');

    $response = $this->actingAs($user)->post(route('verification.ktm.upload'), [
        'ktm' => $file,
    ]);

    $response->assertSessionHasErrors('ktm');

    $user->refresh();
    expect($user->ktm_path)->toBeNull();
});

test('11. upload ditolak jika file gambar bukan format yang diizinkan (misal GIF)', function () {
    $user = User::factory()->unverified()->create([
        'ktm_path' => null,
        'status_verifikasi' => 'menunggu',
    ]);

    $file = UploadedFile::fake()->image('animasi.gif');

    $response = $this->actingAs($user)->post(route('verification.ktm.upload'), [
        'ktm' => $file,
    ]);

    $response->assertSessionHasErrors('ktm');

    $user->refresh();
    expect($user->ktm_path)->toBeNull();
});

test('12. upload ditolak jika ukuran file melebihi 5 MB', function () {
    $user = User::factory()->unverified()->create([
        'ktm_path' => null,
        'status_verifikasi' => 'menunggu',
    ]);

    // 5121 KB > 5120 KB (5 MB)
    $file = UploadedFile::fake()->image('foto_besar.jpg')->size(5121);

    $response = $this->actingAs($user)->post(route('verification.ktm.upload'), [
        'ktm' => $file,
    ]);

    $response->assertSessionHasErrors('ktm');

    $user->refresh();
    expect($user->ktm_path)->toBeNull();
});

test('13. upload ditolak jika tidak ada file yang dikirim', function () {
    $user = User::factory()->unverified()->create([
        'ktm_path' => null,
        'status_verifikasi' => 'menunggu',
    ]);

    $response = $this->actingAs($user)->post(route('verification.ktm.upload'), []);

    $response->assertSessionHasErrors('ktm');
});

test('14. pengguna berstatus menunggu yang sudah memiliki KTM tidak dapat mengunggah ulang', function () {
    $existingFilename = 'ktm_sudah_ada.jpg';
    Storage::disk('ktm')->put($existingFilename, 'fake content');

    $user = User::factory()->unverified()->create([
        'ktm_path' => $existingFilename,
        'status_verifikasi' => 'menunggu',
    ]);

    $newFile = UploadedFile::fake()->image('ktm_baru.jpg', 800, 600)->size(1000);

    $response = $this->actingAs($user)->post(route('verification.ktm.upload'), [
        'ktm' => $newFile,
    ]);

    $response->assertRedirect(route('verification.status'));
    $response->assertSessionHasErrors('ktm');

    $user->refresh();
    expect($user->ktm_path)->toBe($existingFilename);
    Storage::disk('ktm')->assertExists($existingFilename);
});

test('15. pengguna berstatus ditolak dapat mengunggah ulang KTM dan mereset status menjadi menunggu', function () {
    $admin = User::factory()->admin()->create();

    $oldFilename = 'ktm_ditolak_lama.jpg';
    Storage::disk('ktm')->put($oldFilename, 'old content');

    $user = User::factory()->unverified()->create([
        'ktm_path' => $oldFilename,
        'status_verifikasi' => 'ditolak',
        'alasan_penolakan' => 'Dokumen tidak terbaca dengan jelas.',
        'verified_by' => $admin->id,
        'verified_at' => now()->subDay(),
    ]);

    $newFile = UploadedFile::fake()->image('ktm_perbaikan.jpg', 800, 600)->size(1500);

    $response = $this->actingAs($user)->post(route('verification.ktm.upload'), [
        'ktm' => $newFile,
    ]);

    $response->assertRedirect(route('verification.status'));
    $response->assertSessionHas('status');

    $user->refresh();

    // Pastikan status direset
    expect($user->status_verifikasi)->toBe('menunggu')
        ->and($user->alasan_penolakan)->toBeNull()
        ->and($user->verified_by)->toBeNull()
        ->and($user->verified_at)->toBeNull()
        ->and($user->ktm_path)->not->toBe($oldFilename);

    // File baru tersimpan di private storage
    Storage::disk('ktm')->assertExists($user->ktm_path);

    // File lama dibersihkan
    Storage::disk('ktm')->assertMissing($oldFilename);
});

test('16. pengguna yang sudah disetujui dialihkan saat mencoba mengunggah KTM', function () {
    $user = User::factory()->verified()->create([
        'status_verifikasi' => 'disetujui',
    ]);

    $file = UploadedFile::fake()->image('ktm.jpg');

    $response = $this->actingAs($user)->post(route('verification.ktm.upload'), [
        'ktm' => $file,
    ]);

    $response->assertRedirect(route('dashboard'));
});

test('17. admin dilarang mengunggah KTM (403 Forbidden)', function () {
    $admin = User::factory()->admin()->create();

    $file = UploadedFile::fake()->image('ktm.jpg');

    $response = $this->actingAs($admin)->post(route('verification.ktm.upload'), [
        'ktm' => $file,
    ]);

    $response->assertForbidden();
});

test('18. pengguna dengan status ditangguhkan diblokir dan sesinya diputus saat mencoba mengunggah KTM', function () {
    $user = User::factory()->suspended()->create();

    $file = UploadedFile::fake()->image('ktm.jpg');

    $response = $this->actingAs($user)->post(route('verification.ktm.upload'), [
        'ktm' => $file,
    ]);

    // EnsureNotSuspended middleware memutus session dan mengalihkan ke login
    $response->assertRedirect(route('login'));
    $this->assertGuest();
    $response->assertSessionHasErrors(['nim' => 'Akun Anda sedang ditangguhkan. Silakan hubungi administrator.']);
});

test('19. tamu dialihkan ke login saat mencoba mengunggah KTM', function () {
    $file = UploadedFile::fake()->image('ktm.jpg');

    $response = $this->post(route('verification.ktm.upload'), [
        'ktm' => $file,
    ]);

    $response->assertRedirect(route('login'));
});

test('20. rollback atomis: file baru dihapus jika terjadi kesalahan database saat upload', function () {
    $user = User::factory()->unverified()->create([
        'ktm_path' => null,
        'status_verifikasi' => 'menunggu',
    ]);

    $file = UploadedFile::fake()->image('ktm_error.jpg', 800, 600)->size(1000);

    // Simulasikan database exception saat proses save
    DB::shouldReceive('transaction')
        ->once()
        ->andThrow(new RuntimeException('Simulated database failure'));

    try {
        $this->actingAs($user)->post(route('verification.ktm.upload'), [
            'ktm' => $file,
        ]);
    } catch (RuntimeException $e) {
        expect($e->getMessage())->toBe('Simulated database failure');
    }

    // Pastikan tidak ada file yatim tersisa di disk ktm
    expect(Storage::disk('ktm')->allFiles())->toBeEmpty();

    $user->refresh();
    expect($user->ktm_path)->toBeNull();
});

test('21. simulasi race condition: request paralel kedua dibatalkan dan file kedua dibersihkan saat transaksi mendeteksi akun sudah berstatus menunggu dengan KTM', function () {
    $user = User::factory()->unverified()->create([
        'ktm_path' => null,
        'status_verifikasi' => 'menunggu',
    ]);

    // Simulasikan stale model pada request: di memori $user masih null,
    // namun di database sudah diisi oleh transaksi paralel pertama
    $firstFile = 'ktm_existing_parallel.jpg';
    Storage::disk('ktm')->put($firstFile, 'first content');

    User::where('id', $user->id)->update([
        'ktm_path' => $firstFile,
        'status_verifikasi' => 'menunggu',
    ]);

    // Kirim request dengan file kedua
    $secondFile = UploadedFile::fake()->image('ktm_second.jpg', 800, 600)->size(1000);
    $response = $this->actingAs($user)->post(route('verification.ktm.upload'), [
        'ktm' => $secondFile,
    ]);

    $response->assertRedirect(route('verification.status'));
    $response->assertSessionHasErrors('ktm');

    // Pastikan file pertama tetap ada dan file kedua dihapus dari storage
    Storage::disk('ktm')->assertExists($firstFile);
    expect(Storage::disk('ktm')->allFiles())->toHaveCount(1)
        ->and(Storage::disk('ktm')->allFiles()[0])->toBe($firstFile);
});

test('22. request yang menyertakan atribut terlarang (user_id, role, status_verifikasi, verified_by, ktm_path) tidak dapat memanipulasi akun', function () {
    $user = User::factory()->unverified()->create([
        'ktm_path' => null,
        'status_verifikasi' => 'menunggu',
        'role' => 'user',
    ]);

    $otherUser = User::factory()->unverified()->create([
        'ktm_path' => null,
    ]);

    $file = UploadedFile::fake()->image('ktm.jpg', 800, 600)->size(1000);

    $response = $this->actingAs($user)->post(route('verification.ktm.upload'), [
        'ktm' => $file,
        'user_id' => $otherUser->id,
        'role' => 'admin',
        'status_verifikasi' => 'disetujui',
        'verified_by' => 999,
        'verified_at' => now()->toDateTimeString(),
        'ktm_path' => 'malicious_path_injection.jpg',
        'is_suspended' => true,
    ]);

    $response->assertRedirect(route('verification.status'));

    $user->refresh();
    $otherUser->refresh();

    // Akun sendiri tidak tereskalasi atau termanipulasi
    expect($user->role)->toBe('user')
        ->and($user->status_verifikasi)->toBe('menunggu')
        ->and($user->verified_by)->toBeNull()
        ->and($user->verified_at)->toBeNull()
        ->and($user->is_suspended)->toBeFalse()
        ->and($user->ktm_path)->not->toBe('malicious_path_injection.jpg')
        ->and($user->ktm_path)->toStartWith('ktm_');

    // Akun user lain tidak terdampak sama sekali
    expect($otherUser->ktm_path)->toBeNull();
});

test('23. file KTM milik pengguna lain tidak dapat dihapus saat seorang pengguna mengunggah ulang', function () {
    $otherUserKtm = 'ktm_milik_user_lain.jpg';
    Storage::disk('ktm')->put($otherUserKtm, 'konten rahasia user lain');

    $otherUser = User::factory()->unverified()->create([
        'ktm_path' => $otherUserKtm,
        'status_verifikasi' => 'menunggu',
    ]);

    $myOldKtm = 'ktm_milik_saya_ditolak.jpg';
    Storage::disk('ktm')->put($myOldKtm, 'konten lama saya');

    $myUser = User::factory()->unverified()->create([
        'ktm_path' => $myOldKtm,
        'status_verifikasi' => 'ditolak',
        'alasan_penolakan' => 'Foto tidak jelas',
    ]);

    $newFile = UploadedFile::fake()->image('ktm_perbaikan_saya.jpg', 800, 600)->size(1200);

    $response = $this->actingAs($myUser)->post(route('verification.ktm.upload'), [
        'ktm' => $newFile,
    ]);

    $response->assertRedirect(route('verification.status'));

    // File milik user lain HARUS tetap utuh ada di disk
    Storage::disk('ktm')->assertExists($otherUserKtm);
    expect(Storage::disk('ktm')->get($otherUserKtm))->toBe('konten rahasia user lain');

    // File lama milik saya terhapus
    Storage::disk('ktm')->assertMissing($myOldKtm);

    // File baru saya tersimpan
    $myUser->refresh();
    Storage::disk('ktm')->assertExists($myUser->ktm_path);
});

test('24. jika transaksi database gagal saat pengajuan ulang, file lama tidak terhapus dan file baru dibersihkan', function () {
    $oldFile = 'ktm_lama_sebelum_gagal.jpg';
    Storage::disk('ktm')->put($oldFile, 'old content to preserve');

    $user = User::factory()->unverified()->create([
        'ktm_path' => $oldFile,
        'status_verifikasi' => 'ditolak',
        'alasan_penolakan' => 'Buram',
    ]);

    $newFile = UploadedFile::fake()->image('ktm_baru.jpg', 800, 600)->size(1000);

    // Simulasikan database throw exception saat commit
    DB::shouldReceive('transaction')
        ->once()
        ->andThrow(new RuntimeException('Database transaction aborted'));

    try {
        $this->actingAs($user)->post(route('verification.ktm.upload'), [
            'ktm' => $newFile,
        ]);
    } catch (RuntimeException $e) {
        expect($e->getMessage())->toBe('Database transaction aborted');
    }

    // File lama TETAP ADA
    Storage::disk('ktm')->assertExists($oldFile);
    expect(Storage::disk('ktm')->get($oldFile))->toBe('old content to preserve');

    // Tidak ada file baru yang tersisa (hanya file lama 1 buah)
    expect(Storage::disk('ktm')->allFiles())->toHaveCount(1);

    // Data user tidak berubah
    $user->refresh();
    expect($user->ktm_path)->toBe($oldFile)
        ->and($user->status_verifikasi)->toBe('ditolak');
});

test('25. path dan isi file KTM tidak terekspos secara publik melalui URL web storage', function () {
    $secretFile = 'ktm_secret_private_file.jpg';
    Storage::disk('ktm')->put($secretFile, 'private image bytes');

    // Akses publik ke URL storage terblokir (403 Forbidden atau 404 Not Found, tidak pernah 200 OK)
    $this->get('/storage/'.$secretFile)->assertForbidden();
    $this->get('/storage/ktm/'.$secretFile)->assertForbidden();
    $this->get('/storage/app/private/ktm/'.$secretFile)->assertForbidden();
    $this->get('/app/private/ktm/'.$secretFile)->assertNotFound();
});
