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
use Database\Seeders\BrandSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\JurusanSeeder;
use Database\Seeders\PlanSeeder;
use Database\Seeders\ProdiCodeSeeder;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    // Pastikan koneksi testing terisolasi dan bukan database production/development klik_db
    $connection = config('database.default');
    $databaseName = (string) config("database.connections.{$connection}.database");

    expect($databaseName)->not->toBe('klik_db');
});

test('DatabaseSeeder dapat dijalankan ulang secara idempoten tanpa duplikasi atau error', function (): void {
    // Run seeder first time
    $this->seed(DatabaseSeeder::class);

    $jurusanCount1 = Jurusan::count();
    $prodiCount1 = ProdiCode::count();
    $planCount1 = Plan::count();
    $brandCount1 = Brand::count();
    $categoryCount1 = Category::count();

    expect($jurusanCount1)->toBe(7)
        ->and($prodiCount1)->toBe(28)
        ->and($planCount1)->toBe(3)
        ->and($brandCount1)->toBe(9)
        ->and($categoryCount1)->toBe(5);

    // Simpan snapshot ID untuk memastikan stabilitas record ID (tidak dihapus dan dibuat ulang)
    $jurusanIds1 = Jurusan::pluck('id', 'nama')->all();
    $prodiIds1 = ProdiCode::pluck('id', 'kode')->all();
    $planIds1 = Plan::pluck('id', 'nama')->all();
    $brandIds1 = Brand::pluck('id', 'nama')->all();
    $categoryIds1 = Category::pluck('id', 'nama')->all();

    // Run seeder second time (idempotence verification)
    $this->seed(DatabaseSeeder::class);

    expect(Jurusan::count())->toBe($jurusanCount1)
        ->and(ProdiCode::count())->toBe($prodiCount1)
        ->and(Plan::count())->toBe($planCount1)
        ->and(Brand::count())->toBe($brandCount1)
        ->and(Category::count())->toBe($categoryCount1)
        ->and(Jurusan::pluck('id', 'nama')->all())->toBe($jurusanIds1)
        ->and(ProdiCode::pluck('id', 'kode')->all())->toBe($prodiIds1)
        ->and(Plan::pluck('id', 'nama')->all())->toBe($planIds1)
        ->and(Brand::pluck('id', 'nama')->all())->toBe($brandIds1)
        ->and(Category::pluck('id', 'nama')->all())->toBe($categoryIds1);
});

test('PlanSeeder membuat paket 3 bulan, 5 bulan, dan custom sesuai spesifikasi PRD 9.3', function (): void {
    $this->seed(PlanSeeder::class);

    $tigaBulan = Plan::where('nama', '3 Bulan')->firstOrFail();
    expect($tigaBulan->durasi_hari)->toBe(90)
        ->and($tigaBulan->harga)->toBe(40000)
        ->and($tigaBulan->harga_per_minggu)->toBeNull();

    $limaBulan = Plan::where('nama', '5 Bulan')->firstOrFail();
    expect($limaBulan->durasi_hari)->toBe(150)
        ->and($limaBulan->harga)->toBe(65000)
        ->and($limaBulan->harga_per_minggu)->toBeNull();

    $custom = Plan::where('nama', 'Custom')->firstOrFail();
    expect($custom->durasi_hari)->toBeNull()
        ->and($custom->harga)->toBeNull()
        ->and($custom->harga_per_minggu)->toBe(4000);
});

test('Jurusan dan ProdiCode seeder menggunakan data resmi PNP dan berelasi dengan benar', function (): void {
    $this->seed(JurusanSeeder::class);
    $this->seed(ProdiCodeSeeder::class);

    $jurusanTi = Jurusan::where('nama', 'Teknologi Informasi')->firstOrFail();
    expect($jurusanTi->prodiCodes()->count())->toBe(5);

    $prodiTrpl = ProdiCode::where('kode', '1108')->firstOrFail();
    expect($prodiTrpl->nama_prodi)->toBe('D4 Teknologi Rekayasa Perangkat Lunak')
        ->and($prodiTrpl->jenjang)->toBe('D4')
        ->and($prodiTrpl->jurusan_id)->toBe($jurusanTi->id)
        ->and($prodiTrpl->creator)->not->toBeNull();

    // Verifikasi kode prodi yang belum diverifikasi tidak ada di database seeder
    expect(ProdiCode::where('nama_prodi', 'like', '%Mekatronika%')->exists())->toBeFalse();
});

test('BrandSeeder mengisi 9 brand laptop umum tanpa duplikasi', function (): void {
    $this->seed(BrandSeeder::class);

    $brands = Brand::pluck('nama')->all();
    sort($brands, SORT_NATURAL | SORT_FLAG_CASE);

    expect($brands)->toBe([
        'Acer',
        'Apple',
        'ASUS',
        'Axioo',
        'Dell',
        'HP',
        'Lenovo',
        'MSI',
        'Toshiba',
    ]);
});

test('CategorySeeder mengisi 5 kategori dengan prioritas dan kriteria sesuai PRD 9.2', function (): void {
    $this->seed(CategorySeeder::class);

    $categories = Category::orderBy('prioritas')->get();
    expect($categories)->toHaveCount(5);

    expect($categories[0]->nama)->toBe('MacBook')
        ->and($categories[0]->prioritas)->toBe(1)
        ->and($categories[1]->nama)->toBe('Laptop Gaming')
        ->and($categories[1]->prioritas)->toBe(2)
        ->and($categories[2]->nama)->toBe('Laptop 2-in-1')
        ->and($categories[2]->prioritas)->toBe(3)
        ->and($categories[3]->nama)->toBe('Ultrabook')
        ->and($categories[3]->prioritas)->toBe(4)
        ->and($categories[4]->nama)->toBe('Laptop Standar')
        ->and($categories[4]->prioritas)->toBe(5);
});

test('seluruh Factory Eloquent dapat membuat instance valid sesuai schema dan constraint database', function (): void {
    // 1. UserFactory
    $user = User::factory()->create();
    expect($user->exists)->toBeTrue()
        ->and($user->nim)->toMatch('/^[0-9]{10}$/');

    $admin = User::factory()->admin()->create();
    expect($admin->role)->toBe('admin');

    $verified = User::factory()->verified()->create();
    expect($verified->status_verifikasi)->toBe('disetujui');

    $alumni = User::factory()->alumni()->create();
    expect($alumni->status_mahasiswa)->toBe('alumni');

    // 2. JurusanFactory & ProdiCodeFactory
    $jurusan = Jurusan::factory()->create();
    $prodi = ProdiCode::factory()->recycle($jurusan)->recycle($admin)->create();
    expect($prodi->jurusan_id)->toBe($jurusan->id)
        ->and($prodi->created_by)->toBe($admin->id);

    // 3. PlanFactory
    $plan3Bulan = Plan::factory()->tigaBulan()->create();
    $plan5Bulan = Plan::factory()->limaBulan()->create();
    $planCustom = Plan::factory()->custom()->create();
    expect($plan3Bulan->durasi_hari)->toBe(90)
        ->and($plan5Bulan->durasi_hari)->toBe(150)
        ->and($planCustom->harga_per_minggu)->toBe(4000);

    // 4. LapakFactory
    $lapak = Lapak::factory()->recycle($user)->recycle($plan3Bulan)->aktif()->create();
    expect($lapak->status)->toBe('aktif')
        ->and($lapak->user_id)->toBe($user->id);

    // 5. TransactionFactory
    $transaction = Transaction::factory()->recycle($user)->paid($lapak->id)->create();
    expect($transaction->status)->toBe('paid')
        ->and($transaction->lapak_id)->toBe($lapak->id)
        ->and($transaction->user_id)->toBe($user->id);

    // 6. BrandFactory & CategoryFactory
    $brand = Brand::factory()->create();
    $category = Category::factory()->create();
    expect($brand->exists)->toBeTrue()
        ->and($category->exists)->toBeTrue();

    // 7. ListingFactory
    $listing = Listing::factory()
        ->recycle($lapak)
        ->recycle($category)
        ->recycle($brand)
        ->aktif()
        ->create();
    expect($listing->status)->toBe('aktif')
        ->and($listing->lapak_id)->toBe($lapak->id)
        ->and($listing->no_wa)->toStartWith('08');

    // Refresh listing untuk memverifikasi generated column active_lapak_id
    $listing->refresh();
    expect($listing->active_lapak_id)->toBe($lapak->id);

    // 8. ListingPhotoFactory
    $photo = ListingPhoto::factory()->recycle($listing)->create();
    expect($photo->listing_id)->toBe($listing->id)
        ->and($photo->path)->toBeString();

    // 9. InquiryFactory
    $buyer = User::factory()->create();
    $inquiry = Inquiry::factory()->recycle($listing)->recycle($buyer)->create();
    expect($inquiry->listing_id)->toBe($listing->id)
        ->and($inquiry->buyer_id)->toBe($buyer->id);

    // 10. ModerationActionFactory
    $moderation = ModerationAction::factory()
        ->recycle($listing)
        ->recycle($admin)
        ->banding()
        ->create();
    expect($moderation->status)->toBe('banding')
        ->and($moderation->listing_id)->toBe($listing->id);

    // 11. AppealFactory
    $appeal = Appeal::factory()
        ->recycle($moderation)
        ->recycle($admin)
        ->nomor(1)
        ->create();
    expect($appeal->nomor_banding)->toBe(1)
        ->and($appeal->moderation_action_id)->toBe($moderation->id);

    // 12. ReportFactory
    $reporter = User::factory()->create();
    $report = Report::factory()
        ->recycle($listing)
        ->recycle($reporter)
        ->menunggu()
        ->create();
    expect($report->status)->toBe('menunggu')
        ->and($report->listing_id)->toBe($listing->id);

    // Refresh report untuk memverifikasi generated column active_reporter_id
    $report->refresh();
    expect($report->active_reporter_id)->toBe($reporter->id);

    // 13. NotificationFactory
    $notification = Notification::factory()
        ->recycle($user)
        ->belumDibaca()
        ->create();
    expect($notification->user_id)->toBe($user->id)
        ->and($notification->dibaca)->toBeFalse();
});

test('ProdiCodeSeeder tidak membuat akun admin loginable dengan kredensial tetap', function (): void {
    $this->seed(JurusanSeeder::class);
    $this->seed(ProdiCodeSeeder::class);

    // 1. Pastikan tidak ada akun default backdoor dengan password tertebak
    expect(User::where('email', 'admin@klik.pnp.ac.id')->exists())->toBeFalse();

    // 2. Akun sistem anchor yang dibuat berstatus suspended (tidak dapat digunakan untuk login)
    $systemUser = User::where('email', 'system.internal@klik.local')->first();
    expect($systemUser)->not->toBeNull()
        ->and($systemUser->is_suspended)->toBeTrue()
        ->and($systemUser->email_verified_at)->toBeNull();

    // 3. Jika admin sudah ada sebelumnya, seeder memakai admin tersebut tanpa membuat user baru
    $existingAdmin = User::factory()->admin()->create([
        'email' => 'admin.resmi@pnp.ac.id',
        'is_suspended' => false,
    ]);
    $userCountBefore = User::count();

    // Jalankan ulang seeder
    $this->seed(ProdiCodeSeeder::class);
    expect(User::count())->toBe($userCountBefore);
});

test('database production development klik_db tetap bersih dan tidak tersentuh', function (): void {
    // Kembalikan nama database mariadb ke klik_db karena di phpunit.xml tertimpa :memory:
    config(['database.connections.mariadb.database' => 'klik_db']);
    DB::purge('mariadb');

    // Verifikasi database klik_db tidak pernah tersentuh pengujian
    $tables = [
        'users',
        'jurusans',
        'prodi_codes',
        'plans',
        'lapaks',
        'transactions',
        'brands',
        'categories',
        'listings',
        'listing_photos',
        'inquiries',
        'moderation_actions',
        'appeals',
        'reports',
        'notifications',
    ];

    foreach ($tables as $table) {
        $count = DB::connection('mariadb')->table($table)->count();
        expect($count)->toBe(0, "Tabel {$table} di database dev klik_db tidak boleh memiliki data dari test");
    }
});
