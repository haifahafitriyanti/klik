<?php

use Illuminate\Database\Connection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Database Constraints Test — KLiK Phase 2.2
|--------------------------------------------------------------------------
| Pengujian ini memvalidasi constraint bisnis pada level database (MariaDB).
|
| KONEKSI: mariadb_testing → klik_test_db (BUKAN klik_db development).
|
| ISOLASI DATABASE:
| Setiap test dibungkus dalam database transaction pada koneksi mariadb_testing
| yang dimulai pada beforeEach() dan di-rollback otomatis pada afterEach().
| Dengan demikian, tidak ada data pengujian yang tertinggal di klik_test_db,
| dan seluruh pengujian dapat dijalankan berulang kali secara deterministik.
|
| PENGAMANAN DATABASE:
| assertSafeTestConnection() memastikan sebelum setiap operasi database bahwa:
| 1. Koneksi yang aktif adalah mariadb_testing.
| 2. Nama database yang diakses benar-benar 'klik_test_db'.
| 3. Jika koneksi mengarah ke 'klik_db' (development) atau database lain,
|    pengujian langsung dihentikan demi melindungi integritas data.
|--------------------------------------------------------------------------
*/

/** Nama database testing MariaDB yang diizinkan secara ketat. */
const ALLOWED_TEST_DB = 'klik_test_db';

/** Nama koneksi MariaDB testing pada config/database.php. */
const MARIADB_TEST_CONNECTION = 'mariadb_testing';

/**
 * Guard keamanan tingkat tinggi:
 * Memastikan koneksi aktif secara faktual terhubung ke 'klik_test_db',
 * bukan 'klik_db' (development) atau database lainnya.
 */
function assertSafeTestConnection(): void
{
    $connection = DB::connection(MARIADB_TEST_CONNECTION);
    $actualDb = $connection->getDatabaseName();

    if ($actualDb === 'klik_db') {
        test()->fail(
            "FATAL: Koneksi mengarah ke database development 'klik_db'! "
            .'Pengujian dihentikan seketika untuk mencegah pencemaran data.'
        );
    }

    if ($actualDb !== ALLOWED_TEST_DB) {
        test()->fail(
            "FATAL: Database testing tidak valid. Diharapkan '".ALLOWED_TEST_DB
            ."', tetapi mendapatkan '{$actualDb}'."
        );
    }
}

/**
 * Ambil koneksi testing yang sudah diverifikasi aman.
 */
function testDb(): Connection
{
    assertSafeTestConnection();

    return DB::connection(MARIADB_TEST_CONNECTION);
}

/**
 * Insert data ke tabel pada koneksi testing dan kembalikan ID baru.
 *
 * @param  array<string, mixed>  $data
 */
function dbInsert(string $table, array $data): int
{
    return testDb()->table($table)->insertGetId($data);
}

/**
 * Buat fixture data minimal untuk pengujian constraint.
 *
 * @return array{userId: int, planId: int, lapakId: int, brandId: int, catId: int}
 */
function setupBaseFixture(string $nimSuffix, string $emailPrefix): array
{
    $userId = dbInsert('users', [
        'name' => "Test User {$nimSuffix}",
        'email' => "{$emailPrefix}@klik-test.local",
        'password' => bcrypt('secret'),
        'nim' => "21010{$nimSuffix}",
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $planId = dbInsert('plans', [
        'nama' => '3 Bulan',
        'durasi_hari' => 90,
        'harga' => 15000,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $lapakId = dbInsert('lapaks', [
        'user_id' => $userId,
        'plan_id' => $planId,
        'status' => 'aktif',
        'harga_saat_beli' => 15000,
        'durasi_hari' => 90,
        'tc_accepted_at' => now(),
        'started_at' => now(),
        'expires_at' => now()->addDays(90),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $brandId = dbInsert('brands', [
        'nama' => "Brand-{$nimSuffix}",
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $catId = dbInsert('categories', [
        'nama' => "Kategori-{$nimSuffix}",
        'prioritas' => 1,
        'kriteria' => 'RAM min 8GB',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return compact('userId', 'planId', 'lapakId', 'brandId', 'catId');
}

/**
 * Buat data listing pada koneksi testing.
 *
 * @param  array<string, mixed>  $override
 */
function makeListing(int $lapakId, int $catId, int $brandId, string $judul, string $status = 'aktif', array $override = []): int
{
    return dbInsert('listings', array_merge([
        'lapak_id' => $lapakId,
        'category_id' => $catId,
        'brand_id' => $brandId,
        'judul' => $judul,
        'model' => 'Model-X',
        'prosesor' => 'Core i5',
        'ram_gb' => 8,
        'penyimpanan' => '256GB SSD',
        'harga' => 3500000,
        'kondisi' => 'Mulus',
        'deskripsi' => 'Laptop test constraint',
        'no_wa' => '081234567890',
        'status' => $status,
        'created_at' => now(),
    ], $override));
}

// ---------------------------------------------------------------------------
// Setup Isolasi Transaksi Database per Test
// ---------------------------------------------------------------------------

beforeEach(function () {
    assertSafeTestConnection();
    DB::connection(MARIADB_TEST_CONNECTION)->beginTransaction();
});

afterEach(function () {
    if (DB::connection(MARIADB_TEST_CONNECTION)->transactionLevel() > 0) {
        DB::connection(MARIADB_TEST_CONNECTION)->rollBack();
    }
});

// ---------------------------------------------------------------------------
// TEST 1 — Satu listing aktif per lapak
// ---------------------------------------------------------------------------

test('hanya satu listing aktif yang diperbolehkan per lapak pada basis data', function () {
    $db = testDb();
    $fix = setupBaseFixture('92001', 'listing-aktif');

    // Listing 1 aktif → Berhasil
    $listing1Id = makeListing($fix['lapakId'], $fix['catId'], $fix['brandId'], 'ThinkPad T480');
    expect($listing1Id)->toBeGreaterThan(0);

    // Listing 2 aktif pada lapak yang sama → Database HARUS menolak (generated column unique)
    expect(function () use ($fix) {
        makeListing($fix['lapakId'], $fix['catId'], $fix['brandId'], 'ThinkPad X280');
    })->toThrow(QueryException::class);

    // Setelah listing 1 terjual, active_lapak_id menjadi NULL → slot terbuka
    $db->table('listings')->where('id', $listing1Id)->update([
        'status' => 'terjual',
        'sold_at' => now(),
    ]);

    // Sekarang listing 2 aktif BOLEH disimpan
    $listing2Id = makeListing($fix['lapakId'], $fix['catId'], $fix['brandId'], 'ThinkPad X280 Baru');
    expect($listing2Id)->toBeGreaterThan(0);
})->group('mariadb-constraints');

// ---------------------------------------------------------------------------
// TEST 2 — Satu laporan aktif per pelapor per listing
// ---------------------------------------------------------------------------

test('hanya satu laporan aktif yang diperbolehkan per pelapor per listing', function () {
    $db = testDb();
    $fix = setupBaseFixture('92002', 'laporan-aktif');

    $listingId = makeListing($fix['lapakId'], $fix['catId'], $fix['brandId'], 'HP Pavilion Gaming');

    // Laporan 1 berstatus 'menunggu' → Berhasil
    $report1Id = dbInsert('reports', [
        'listing_id' => $listingId,
        'reporter_id' => $fix['userId'],
        'alasan' => 'tidak_sesuai',
        'catatan' => 'Laporan pertama',
        'status' => 'menunggu',
        'created_at' => now(),
    ]);
    expect($report1Id)->toBeGreaterThan(0);

    // Laporan 2 oleh pelapor yang sama pada listing yang sama → Database HARUS menolak
    expect(function () use ($listingId, $fix) {
        dbInsert('reports', [
            'listing_id' => $listingId,
            'reporter_id' => $fix['userId'],
            'alasan' => 'mencurigakan',
            'catatan' => 'Laporan kedua aktif',
            'status' => 'ditinjau',
            'created_at' => now(),
        ]);
    })->toThrow(QueryException::class);

    // Setelah laporan 1 selesai, active_reporter_id menjadi NULL
    $db->table('reports')->where('id', $report1Id)->update(['status' => 'selesai']);

    // Sekarang pelapor BOLEH mengirim laporan baru
    $report2Id = dbInsert('reports', [
        'listing_id' => $listingId,
        'reporter_id' => $fix['userId'],
        'alasan' => 'tidak_tersedia',
        'catatan' => 'Laporan setelah laporan lama selesai',
        'status' => 'menunggu',
        'created_at' => now(),
    ]);
    expect($report2Id)->toBeGreaterThan(0);
})->group('mariadb-constraints');

// ---------------------------------------------------------------------------
// TEST 3 — Satu transaksi per lapak
// ---------------------------------------------------------------------------

test('satu lapak hanya dapat ditautkan ke satu transaksi pembelian', function () {
    $db = testDb();
    $fix = setupBaseFixture('92003', 'transaksi-unik');

    $baseTransaction = [
        'user_id' => $fix['userId'],
        'plan_id' => $fix['planId'],
        'lapak_id' => $fix['lapakId'],
        'jumlah' => 15000,
        'durasi_hari' => 90,
        'status' => 'paid',
        'expires_at' => now()->addDay(),
        'tc_accepted_at' => now(),
        'created_at' => now(),
    ];

    // Transaksi 1 dengan lapak_id → Berhasil
    $db->table('transactions')->insert(array_merge($baseTransaction, ['order_id' => 'KLIK-C-001']));

    // Transaksi 2 dengan lapak_id yang sama → Database HARUS menolak (UNIQUE)
    expect(function () use ($db, $baseTransaction) {
        $db->table('transactions')->insert(array_merge($baseTransaction, ['order_id' => 'KLIK-C-002']));
    })->toThrow(QueryException::class);

    // Transaksi dengan lapak_id = NULL BOLEH lebih dari satu (banyak NULL di UNIQUE index)
    $db->table('transactions')->insert(array_merge($baseTransaction, [
        'lapak_id' => null,
        'order_id' => 'KLIK-NULL-C-001',
        'status' => 'pending',
    ]));
    $db->table('transactions')->insert(array_merge($baseTransaction, [
        'lapak_id' => null,
        'order_id' => 'KLIK-NULL-C-002',
        'status' => 'pending',
    ]));

    $nullCount = $db->table('transactions')->whereNull('lapak_id')->count();
    expect($nullCount)->toBe(2);
})->group('mariadb-constraints');

// ---------------------------------------------------------------------------
// TEST 4 — CHECK constraint: nomor_banding BETWEEN 1 AND 3
// ---------------------------------------------------------------------------

test('CHECK constraint nomor_banding menolak nilai di luar rentang 1 sampai 3 dan menegakkan unique constraint', function () {
    $db = testDb();
    $fix = setupBaseFixture('92004', 'mod-banding');

    $db->table('users')->where('id', $fix['userId'])->update(['role' => 'admin']);

    $listingId = makeListing($fix['lapakId'], $fix['catId'], $fix['brandId'], 'Dell Latitude 7490');

    $modId = dbInsert('moderation_actions', [
        'listing_id' => $listingId,
        'admin_id' => $fix['userId'],
        'kode_alasan' => 'M2',
        'alasan' => 'Foto tidak jelas',
        'hidden_at' => now(),
        'response_deadline' => now()->addHours(72),
        'status' => 'menunggu',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // 1. nomor_banding = 1 DITERIMA (batas bawah valid)
    $appeal1Id = dbInsert('appeals', [
        'moderation_action_id' => $modId,
        'nomor_banding' => 1,
        'alasan' => 'Banding pertama',
        'status' => 'menunggu',
        'submitted_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    expect($appeal1Id)->toBeGreaterThan(0);

    // 2. nomor_banding = 3 DITERIMA (batas atas valid)
    $appeal3Id = dbInsert('appeals', [
        'moderation_action_id' => $modId,
        'nomor_banding' => 3,
        'alasan' => 'Banding ketiga dan terakhir',
        'status' => 'menunggu',
        'submitted_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    expect($appeal3Id)->toBeGreaterThan(0);

    // 3. Duplikat (moderation_action_id, nomor_banding=1) DITOLAK oleh unique constraint
    expect(function () use ($modId) {
        dbInsert('appeals', [
            'moderation_action_id' => $modId,
            'nomor_banding' => 1,
            'alasan' => 'Duplikat banding 1',
            'status' => 'menunggu',
            'submitted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    })->toThrow(function (QueryException $e) {
        // Harus gagal karena pelanggaran unique constraint
        return str_contains($e->getMessage(), 'Duplicate entry')
            || str_contains($e->getMessage(), 'uq_appeals_action_nomor')
            || $e->getCode() === '23000';
    });

    // 4. nomor_banding = 0 DITOLAK oleh CHECK constraint (di bawah batas minimum 1)
    expect(function () use ($modId) {
        dbInsert('appeals', [
            'moderation_action_id' => $modId,
            'nomor_banding' => 0,
            'alasan' => 'Nomor 0 tidak valid — melanggar CHECK constraint',
            'status' => 'menunggu',
            'submitted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    })->toThrow(function (QueryException $e) {
        // Harus gagal karena CHECK constraint chk_appeals_nomor_banding
        return str_contains($e->getMessage(), 'chk_appeals_nomor_banding')
            || str_contains($e->getMessage(), 'CHECK constraint')
            || str_contains($e->getMessage(), 'Check constraint')
            || $e->getCode() === '23000'
            || $e->getCode() === 'HY000';
    });

    // 5. nomor_banding = 4 DITOLAK oleh CHECK constraint (di atas batas maksimum 3)
    expect(function () use ($modId) {
        dbInsert('appeals', [
            'moderation_action_id' => $modId,
            'nomor_banding' => 4,
            'alasan' => 'Nomor 4 tidak valid — melanggar CHECK constraint',
            'status' => 'menunggu',
            'submitted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    })->toThrow(function (QueryException $e) {
        // Harus gagal karena CHECK constraint chk_appeals_nomor_banding
        return str_contains($e->getMessage(), 'chk_appeals_nomor_banding')
            || str_contains($e->getMessage(), 'CHECK constraint')
            || str_contains($e->getMessage(), 'Check constraint')
            || $e->getCode() === '23000'
            || $e->getCode() === 'HY000';
    });
})->group('mariadb-constraints');
