<?php

use App\Models\Appeal;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Inquiry;
use App\Models\Jurusan;
use App\Models\Lapak;
use App\Models\Listing;
use App\Models\ListingPhoto;
use App\Models\ModerationAction;
use App\Models\Notification;
use App\Models\Plan;
use App\Models\ProdiCode;
use App\Models\Report;
use App\Models\Transaction;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Eloquent Models and Relations Test — KLiK Phase 2.3
|--------------------------------------------------------------------------
| Memvalidasi seluruh model Eloquent, penamaan tabel, relasi foreign key,
| perlindungan mass assignment, casting tipe data, timestamps khusus,
| dan business helper method sesuai acuan PRD v0.6 & ERD v6.
|--------------------------------------------------------------------------
*/

test('seluruh 15 model Eloquent dapat diinstansiasi dengan nama tabel yang sesuai', function () {
    $models = [
        Jurusan::class => 'jurusans',
        ProdiCode::class => 'prodi_codes',
        User::class => 'users',
        Notification::class => 'notifications',
        Plan::class => 'plans',
        Lapak::class => 'lapaks',
        Transaction::class => 'transactions',
        Brand::class => 'brands',
        Category::class => 'categories',
        Listing::class => 'listings',
        ListingPhoto::class => 'listing_photos',
        Inquiry::class => 'inquiries',
        ModerationAction::class => 'moderation_actions',
        Appeal::class => 'appeals',
        Report::class => 'reports',
    ];

    foreach ($models as $class => $expectedTable) {
        $instance = new $class;
        expect($instance)->toBeInstanceOf($class)
            ->and($instance->getTable())->toBe($expectedTable);
    }
});

test('perlindungan mass assignment dan atribut sensitif berfungsi dengan benar', function () {
    // 1. User: role, status_verifikasi, verified_by, is_suspended TIDAK boleh diubah via mass assignment
    $user = new User([
        'name' => 'Budi Santoso',
        'email' => 'budi@pnp.ac.id',
        'password' => 'secret123',
        'nim' => '2101092001',
        'username' => 'budis',
        'role' => 'admin',                       // Sensitif -> Harus diabaikan
        'status_verifikasi' => 'disetujui',       // Sensitif -> Harus diabaikan
        'verified_by' => 999,                    // Sensitif -> Harus diabaikan
        'is_suspended' => true,                  // Sensitif -> Harus diabaikan
    ]);

    expect($user->name)->toBe('Budi Santoso')
        ->and($user->nim)->toBe('2101092001')
        ->and($user->username)->toBe('budis')
        ->and($user->role)->toBeNull()
        ->and($user->status_verifikasi)->toBeNull()
        ->and($user->verified_by)->toBeNull()
        ->and($user->is_suspended)->toBeNull();

    // 2. User toArray / json tidak boleh mengekspos password, token, atau ktm_path
    $user->password = 'hashed_secret';
    $user->remember_token = 'token_secret';
    $user->ktm_path = 'ktm/private/2101092001.jpg';
    $userArray = $user->toArray();

    expect($userArray)->not->toHaveKey('password')
        ->and($userArray)->not->toHaveKey('two_factor_secret')
        ->and($userArray)->not->toHaveKey('two_factor_recovery_codes')
        ->and($userArray)->not->toHaveKey('remember_token')
        ->and($userArray)->not->toHaveKey('ktm_path');

    // 3. Listing: active_lapak_id (generated column) TIDAK boleh masuk fillable
    $listing = new Listing([
        'judul' => 'ThinkPad T480',
        'active_lapak_id' => 99999, // Generated column -> Harus diabaikan
    ]);
    expect($listing->active_lapak_id)->toBeNull();

    // 4. Report: active_reporter_id (generated column) TIDAK boleh masuk fillable
    $report = new Report([
        'alasan' => 'tidak_sesuai',
        'active_reporter_id' => 88888, // Generated column -> Harus diabaikan
    ]);
    expect($report->active_reporter_id)->toBeNull();

    // 5. Transaction: status, paid_at, gateway_status, perlu_tinjauan TIDAK boleh diubah via mass assignment
    $transaction = new Transaction([
        'order_id' => 'KLIK-ORD-001',
        'jumlah' => 15000,
        'status' => 'paid',            // Sensitif -> Harus diabaikan
        'paid_at' => now(),            // Sensitif -> Harus diabaikan
        'perlu_tinjauan' => true,      // Sensitif -> Harus diabaikan
    ]);
    expect($transaction->order_id)->toBe('KLIK-ORD-001')
        ->and($transaction->jumlah)->toBe(15000)
        ->and($transaction->status)->toBeNull()
        ->and($transaction->paid_at)->toBeNull()
        ->and($transaction->perlu_tinjauan)->toBeNull();

    // 6. Appeal: status, decided_by, catatan_admin TIDAK boleh diubah via mass assignment
    $appeal = new Appeal([
        'nomor_banding' => 1,
        'alasan' => 'Klarifikasi foto',
        'status' => 'diterima',        // Administratif -> Harus diabaikan
        'decided_by' => 1,             // Administratif -> Harus diabaikan
    ]);
    expect($appeal->nomor_banding)->toBe(1)
        ->and($appeal->alasan)->toBe('Klarifikasi foto')
        ->and($appeal->status)->toBeNull()
        ->and($appeal->decided_by)->toBeNull();
});

test('konfigurasi casting tipe data pada model berfungsi sesuai harapan', function () {
    // 1. Transaction form_payload array <-> JSON
    $tx = new Transaction([
        'order_id' => 'KLIK-JSON-001',
        'jumlah' => '25000',
        'durasi_hari' => '150',
        'form_payload' => ['item' => 'Paket 5 Bulan', 'buyer' => 'Mahasiswa'],
    ]);
    expect($tx->jumlah)->toBeInt()->toBe(25000)
        ->and($tx->durasi_hari)->toBeInt()->toBe(150)
        ->and($tx->form_payload)->toBeArray()->toHaveKey('item');

    // 2. Listing boolean, integer, dan decimal casts
    $listing = new Listing([
        'ram_gb' => '16',
        'harga' => '4500000',
        'gpu_dedicated' => 1,
        'tipe_2in1' => 0,
        'berat_kg' => '1.85',
        'sold_at' => '2026-11-05 10:00:00',
    ]);
    expect($listing->ram_gb)->toBeInt()->toBe(16)
        ->and($listing->harga)->toBeInt()->toBe(4500000)
        ->and($listing->gpu_dedicated)->toBeTrue()
        ->and($listing->tipe_2in1)->toBeFalse()
        ->and($listing->berat_kg)->toBe('1.85')
        ->and($listing->sold_at)->toBeInstanceOf(DateTimeInterface::class);

    // 3. User casts
    $user = new User([
        'angkatan' => '2021',
        'kode_prodi_tidak_dikenal' => 1,
    ]);
    $user->is_suspended = 0;
    expect($user->angkatan)->toBeInt()->toBe(2021)
        ->and($user->kode_prodi_tidak_dikenal)->toBeTrue()
        ->and($user->is_suspended)->toBeFalse();

    // 4. Notification casts
    $notif = new Notification([
        'ref_id' => '12345',
        'dibaca' => 1,
    ]);
    expect($notif->ref_id)->toBeInt()->toBe(12345)
        ->and($notif->dibaca)->toBeTrue();
});

test('model dengan konfigurasi timestamps khusus tidak menulis kolom yang tidak tersedia dan dapat di-update dengan aman', function () {
    // 1. Verifikasi konstanta UPDATED_AT = null
    expect(ProdiCode::UPDATED_AT)->toBeNull()
        ->and(Notification::UPDATED_AT)->toBeNull()
        ->and(Transaction::UPDATED_AT)->toBeNull()
        ->and(Listing::UPDATED_AT)->toBeNull()
        ->and(ListingPhoto::UPDATED_AT)->toBeNull()
        ->and(Report::UPDATED_AT)->toBeNull();

    // 2. Verifikasi $timestamps = false pada Inquiry
    $inquiry = new Inquiry;
    expect($inquiry->timestamps)->toBeFalse();

    // 3. Verifikasi operasi create dan update faktual pada model tanpa updated_at
    $user = User::create([
        'name' => 'User Timestamp Test',
        'email' => 'ts_test@pnp.ac.id',
        'password' => bcrypt('password'),
    ]);

    $jurusan = Jurusan::create(['nama' => 'Teknik Elektro']);

    // Test create & update pada ProdiCode (hanya created_at)
    $prodi = ProdiCode::create([
        'jurusan_id' => $jurusan->id,
        'kode' => '3001',
        'jenjang' => 'D3',
        'nama_prodi' => 'Teknik Listrik',
        'created_by' => $user->id,
    ]);
    expect($prodi->exists)->toBeTrue();

    $prodi->nama_prodi = 'D3 Teknik Listrik Industri';
    $prodi->save();
    expect($prodi->fresh()->nama_prodi)->toBe('D3 Teknik Listrik Industri');

    // Test create & update pada Notification (hanya created_at)
    $notif = Notification::create([
        'user_id' => $user->id,
        'tipe' => 'info',
        'pesan' => 'Pesan awal',
        'dibaca' => false,
    ]);
    expect($notif->exists)->toBeTrue();

    $notif->dibaca = true;
    $notif->save();
    expect($notif->fresh()->dibaca)->toBeTrue();

    // Test create & update pada Listing (hanya created_at)
    $brand = Brand::create(['nama' => 'Asus']);
    $cat = Category::create(['nama' => 'Office', 'prioritas' => 1, 'kriteria' => 'Basic']);
    $plan = Plan::create(['nama' => '3 Bulan']);
    $lapak = Lapak::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'harga_saat_beli' => 15000,
        'durasi_hari' => 90,
        'tc_accepted_at' => now(),
        'started_at' => now(),
        'expires_at' => now()->addDays(90),
    ]);
    $listing = Listing::create([
        'lapak_id' => $lapak->id,
        'category_id' => $cat->id,
        'brand_id' => $brand->id,
        'judul' => 'Asus Vivobook',
        'model' => 'A412',
        'prosesor' => 'Core i3',
        'ram_gb' => 8,
        'penyimpanan' => '256GB SSD',
        'harga' => 3000000,
        'kondisi' => 'Mulus',
        'deskripsi' => 'Laptop harian',
        'no_wa' => '081234567890',
        'status' => 'aktif',
    ]);

    $listing->harga = 2800000;
    $listing->save();
    expect($listing->fresh()->harga)->toBe(2800000);

    // Test create & update pada Inquiry ($timestamps = false)
    $inq = Inquiry::create([
        'listing_id' => $listing->id,
        'buyer_id' => $user->id,
        'clicked_at' => now(),
    ]);
    expect($inq->exists)->toBeTrue();

    $inq->buyer_id = $user->id;
    $inq->save();
    expect($inq->fresh()->buyer_id)->toBe($user->id);
});

test('seluruh relasi Eloquent terhubung dan menghasilkan query dengan foreign key yang tepat', function () {
    // 1. Setup User & Jurusan
    $admin = User::create([
        'name' => 'Admin Jurusan',
        'email' => 'admin@pnp.ac.id',
        'password' => bcrypt('password'),
    ]);
    $admin->role = 'admin';
    $admin->status_verifikasi = 'disetujui';
    $admin->save();

    $jurusan = Jurusan::create(['nama' => 'Teknologi Informasi']);

    $prodi = ProdiCode::create([
        'jurusan_id' => $jurusan->id,
        'kode' => '2001',
        'jenjang' => 'D3',
        'nama_prodi' => 'Manajemen Informatika',
        'berlaku_dari' => 2015,
        'created_by' => $admin->id,
    ]);

    // Relasi Jurusan <-> ProdiCode
    expect($jurusan->prodiCodes)->toHaveCount(1)
        ->and($jurusan->prodiCodes->first()->id)->toBe($prodi->id)
        ->and($prodi->jurusan->id)->toBe($jurusan->id)
        ->and($prodi->creator->id)->toBe($admin->id)
        ->and($admin->createdProdiCodes)->toHaveCount(1);

    // 2. Setup Seller User yang diverifikasi oleh Admin
    $seller = User::create([
        'name' => 'Ahmad Penjual',
        'email' => 'ahmad@pnp.ac.id',
        'password' => bcrypt('password'),
        'nim' => '2101092002',
        'username' => 'ahmadp',
        'prodi_code_id' => $prodi->id,
    ]);
    $seller->status_verifikasi = 'disetujui';
    $seller->verified_by = $admin->id;
    $seller->verified_at = now();
    $seller->save();

    // Relasi User <-> ProdiCode & User verifier
    expect($seller->prodiCode->id)->toBe($prodi->id)
        ->and($seller->verifier->id)->toBe($admin->id)
        ->and($admin->verifiedUsers)->toHaveCount(1)
        ->and($prodi->users)->toHaveCount(1);

    // 3. Plan & Lapak & Transaction
    $plan = Plan::create([
        'nama' => '3 Bulan',
        'durasi_hari' => 90,
        'harga' => 15000,
        'is_active' => true,
    ]);

    $lapak = Lapak::create([
        'user_id' => $seller->id,
        'plan_id' => $plan->id,
        'status' => 'aktif',
        'harga_saat_beli' => 15000,
        'durasi_hari' => 90,
        'tc_accepted_at' => now(),
        'started_at' => now(),
        'expires_at' => now()->addDays(90),
    ]);

    $tx = Transaction::create([
        'user_id' => $seller->id,
        'plan_id' => $plan->id,
        'lapak_id' => $lapak->id,
        'order_id' => 'KLIK-TEST-REL-001',
        'jumlah' => 15000,
        'durasi_hari' => 90,
        'expires_at' => now()->addDay(),
        'tc_accepted_at' => now(),
    ]);

    // Relasi Plan, Lapak, Transaction
    expect($seller->lapaks)->toHaveCount(1)
        ->and($lapak->user->id)->toBe($seller->id)
        ->and($lapak->plan->id)->toBe($plan->id)
        ->and($plan->lapaks)->toHaveCount(1)
        ->and($plan->transactions)->toHaveCount(1)
        ->and($tx->lapak->id)->toBe($lapak->id)
        ->and($lapak->transaction->id)->toBe($tx->id)
        ->and($seller->transactions)->toHaveCount(1);

    // 4. Brand, Category, Listing & ListingPhoto
    $brand = Brand::create(['nama' => 'Lenovo']);
    $cat = Category::create(['nama' => 'Coding', 'prioritas' => 1, 'kriteria' => 'RAM min 8GB']);

    $listing = Listing::create([
        'lapak_id' => $lapak->id,
        'category_id' => $cat->id,
        'brand_id' => $brand->id,
        'judul' => 'ThinkPad T480 Core i5',
        'model' => 'T480',
        'prosesor' => 'Intel Core i5-8250U',
        'ram_gb' => 16,
        'penyimpanan' => '512GB SSD',
        'harga' => 4500000,
        'kondisi' => 'Mulus 95%',
        'deskripsi' => 'Laptop kuliah teknik',
        'no_wa' => '081234567890',
        'status' => 'aktif',
    ]);

    $photo = ListingPhoto::create([
        'listing_id' => $listing->id,
        'path' => 'photos/thinkpad-t480-1.jpg',
    ]);

    expect($listing->lapak->id)->toBe($lapak->id)
        ->and($listing->category->id)->toBe($cat->id)
        ->and($listing->brand->id)->toBe($brand->id)
        ->and($listing->photos)->toHaveCount(1)
        ->and($photo->listing->id)->toBe($listing->id)
        ->and($brand->listings)->toHaveCount(1)
        ->and($cat->listings)->toHaveCount(1)
        ->and($lapak->listings)->toHaveCount(1)
        ->and($lapak->activeListing->id)->toBe($listing->id);

    // 5. Buyer User & Inquiry
    $buyer = User::create([
        'name' => 'Citra Pembeli',
        'email' => 'citra@pnp.ac.id',
        'password' => bcrypt('password'),
        'nim' => '2101092003',
        'username' => 'citrap',
    ]);

    $inquiry = Inquiry::create([
        'listing_id' => $listing->id,
        'buyer_id' => $buyer->id,
        'clicked_at' => now(),
    ]);

    expect($listing->inquiries)->toHaveCount(1)
        ->and($inquiry->listing->id)->toBe($listing->id)
        ->and($inquiry->buyer->id)->toBe($buyer->id)
        ->and($buyer->inquiries)->toHaveCount(1);

    // 6. ModerationAction & Appeal
    $mod = ModerationAction::create([
        'listing_id' => $listing->id,
        'admin_id' => $admin->id,
        'kode_alasan' => 'M2',
        'alasan' => 'Foto buram',
        'hidden_at' => now(),
        'response_deadline' => now()->addHours(72),
        'status' => 'menunggu',
    ]);

    $appeal = Appeal::create([
        'moderation_action_id' => $mod->id,
        'nomor_banding' => 1,
        'alasan' => 'Foto sudah diperjelas',
        'submitted_at' => now(),
    ]);

    // Admin memutuskan banding
    $appeal->status = 'diterima';
    $appeal->decided_by = $admin->id;
    $appeal->decided_at = now();
    $appeal->save();

    expect($listing->moderationActions)->toHaveCount(1)
        ->and($mod->listing->id)->toBe($listing->id)
        ->and($mod->admin->id)->toBe($admin->id)
        ->and($admin->moderationActions)->toHaveCount(1)
        ->and($mod->appeals)->toHaveCount(1)
        ->and($appeal->moderationAction->id)->toBe($mod->id)
        ->and($appeal->decidedBy->id)->toBe($admin->id)
        ->and($admin->decidedAppeals)->toHaveCount(1);

    // 7. Report
    $report = Report::create([
        'listing_id' => $listing->id,
        'reporter_id' => $buyer->id,
        'alasan' => 'tidak_tersedia',
        'catatan' => 'Laptop sudah terjual saat dichat',
        'status' => 'menunggu',
    ]);

    expect($listing->reports)->toHaveCount(1)
        ->and($report->listing->id)->toBe($listing->id)
        ->and($report->reporter->id)->toBe($buyer->id)
        ->and($buyer->reports)->toHaveCount(1);

    // 8. Notification
    $notif = Notification::create([
        'user_id' => $seller->id,
        'tipe' => 'laporan_masuk',
        'ref_id' => $report->id,
        'pesan' => 'Listing Anda dilaporkan pembeli.',
        'dibaca' => false,
    ]);

    expect($seller->notifications)->toHaveCount(1)
        ->and($notif->user->id)->toBe($seller->id);
});

test('helper methods pada model berjalan sesuai logika bisnis KLiK', function () {
    // User helpers
    $admin = new User(['status_mahasiswa' => 'alumni']);
    $admin->role = 'admin';
    $admin->status_verifikasi = 'disetujui';

    expect($admin->isAdmin())->toBeTrue()
        ->and($admin->isVerified())->toBeTrue()
        ->and($admin->isMahasiswaAktif())->toBeFalse()
        ->and($admin->isAlumni())->toBeTrue();

    // Lapak helpers
    $lapakAktif = new Lapak(['status' => 'aktif']);
    $lapakJeda = new Lapak(['status' => 'jeda']);
    expect($lapakAktif->isAktif())->toBeTrue()
        ->and($lapakAktif->isJeda())->toBeFalse()
        ->and($lapakJeda->isJeda())->toBeTrue();

    // Listing helpers
    $listingAktif = new Listing(['status' => 'aktif']);
    $listingTerjual = new Listing(['status' => 'terjual']);
    expect($listingAktif->isAktif())->toBeTrue()
        ->and($listingAktif->isTerjual())->toBeFalse()
        ->and($listingTerjual->isTerjual())->toBeTrue();

    // Transaction helpers
    $txPaid = new Transaction;
    $txPaid->status = 'paid';
    $txPending = new Transaction;
    $txPending->status = 'pending';
    expect($txPaid->isPaid())->toBeTrue()
        ->and($txPaid->isPending())->toBeFalse()
        ->and($txPending->isPending())->toBeTrue();

    // ModerationAction helpers
    $mod = new ModerationAction(['status' => 'menunggu']);
    expect($mod->isMenunggu())->toBeTrue()
        ->and($mod->isPulih())->toBeFalse();

    // Report helpers
    $repMenunggu = new Report(['status' => 'menunggu']);
    $repDitinjau = new Report(['status' => 'ditinjau']);
    $repSelesai = new Report(['status' => 'selesai']);
    expect($repMenunggu->isAktif())->toBeTrue()
        ->and($repDitinjau->isAktif())->toBeTrue()
        ->and($repSelesai->isAktif())->toBeFalse();

    // Appeal helpers
    $app = new Appeal;
    $app->status = 'diterima';
    expect($app->isDiterima())->toBeTrue()
        ->and($app->isMenunggu())->toBeFalse();
});
