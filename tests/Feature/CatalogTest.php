<?php

use App\Models\Lapak;
use App\Models\Listing;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guest can access marketplace catalog page', function () {
    $response = $this->get(route('catalog.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Catalog/Index')
        ->has('categories')
        ->has('listings')
    );
});

test('authenticated users with any verification status and admin can access catalog page', function () {
    $admin = User::factory()->admin()->create();
    $verifiedUser = User::factory()->verified()->create();
    $pendingUser = User::factory()->create(['status_verifikasi' => 'menunggu']);
    $rejectedUser = User::factory()->rejected()->create();

    $this->actingAs($admin)->get(route('catalog.index'))->assertOk();
    $this->actingAs($verifiedUser)->get(route('catalog.index'))->assertOk();
    $this->actingAs($pendingUser)->get(route('catalog.index'))->assertOk();
    $this->actingAs($rejectedUser)->get(route('catalog.index'))->assertOk();
});

test('empty catalog returns correct pagination structure with zero items', function () {
    $response = $this->get(route('catalog.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Catalog/Index')
        ->where('listings.total', 0)
        ->where('listings.data', [])
        ->where('listings.current_page', 1)
        ->where('listings.per_page', 12)
    );
});

test('catalog displays only active listings from active unexpired lapak and non-suspended user', function () {
    $activeUser = User::factory()->verified()->create(['is_suspended' => false]);
    $activeLapak = Lapak::factory()->aktif()->create([
        'user_id' => $activeUser->id,
        'expires_at' => now()->addDays(30),
    ]);

    $validListing = Listing::factory()->aktif()->create([
        'lapak_id' => $activeLapak->id,
        'judul' => 'ThinkPad T490 PNP Edition',
    ]);

    // Listing terjual dalam lapak aktif -> tidak boleh tampil
    Listing::factory()->create([
        'lapak_id' => $activeLapak->id,
        'status' => 'terjual',
        'judul' => 'ThinkPad Terjual',
    ]);

    // Listing tersembunyi moderator -> tidak boleh tampil
    Listing::factory()->create([
        'lapak_id' => $activeLapak->id,
        'status' => 'tersembunyi',
        'judul' => 'ThinkPad Tersembunyi',
    ]);

    // Lapak expired -> tidak boleh tampil
    $expiredLapak = Lapak::factory()->aktif()->create([
        'user_id' => $activeUser->id,
        'expires_at' => now()->subDay(),
    ]);
    Listing::factory()->aktif()->create([
        'lapak_id' => $expiredLapak->id,
        'judul' => 'Laptop Lapak Expired',
    ]);

    // Pengguna suspended -> listing tidak boleh tampil
    $suspendedUser = User::factory()->verified()->create(['is_suspended' => true]);
    $suspendedLapak = Lapak::factory()->aktif()->create([
        'user_id' => $suspendedUser->id,
        'expires_at' => now()->addDays(30),
    ]);
    Listing::factory()->aktif()->create([
        'lapak_id' => $suspendedLapak->id,
        'judul' => 'Laptop Pengguna Suspended',
    ]);

    $response = $this->get(route('catalog.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Catalog/Index')
        ->where('listings.total', 1)
        ->where('listings.data.0.id', $validListing->id)
        ->where('listings.data.0.judul', 'ThinkPad T490 PNP Edition')
    );
});

test('catalog excludes listings from unapproved sellers with pending or rejected verification status', function () {
    $approvedSeller = User::factory()->verified()->create();
    $pendingSeller = User::factory()->create(['status_verifikasi' => 'menunggu']);
    $rejectedSeller = User::factory()->rejected()->create();

    // Listing penjual disetujui (harus tampil)
    $approvedLapak = Lapak::factory()->aktif()->create([
        'user_id' => $approvedSeller->id,
        'expires_at' => now()->addDays(30),
    ]);
    $approvedListing = Listing::factory()->aktif()->create([
        'lapak_id' => $approvedLapak->id,
        'judul' => 'Laptop Penjual Disetujui',
    ]);

    // Listing penjual menunggu verifikasi (tidak boleh tampil)
    $pendingLapak = Lapak::factory()->aktif()->create([
        'user_id' => $pendingSeller->id,
        'expires_at' => now()->addDays(30),
    ]);
    Listing::factory()->aktif()->create([
        'lapak_id' => $pendingLapak->id,
        'judul' => 'Laptop Penjual Menunggu',
    ]);

    // Listing penjual ditolak (tidak boleh tampil)
    $rejectedLapak = Lapak::factory()->aktif()->create([
        'user_id' => $rejectedSeller->id,
        'expires_at' => now()->addDays(30),
    ]);
    Listing::factory()->aktif()->create([
        'lapak_id' => $rejectedLapak->id,
        'judul' => 'Laptop Penjual Ditolak',
    ]);

    $response = $this->get(route('catalog.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Catalog/Index')
        ->where('listings.total', 1)
        ->where('listings.data.0.id', $approvedListing->id)
        ->where('listings.data.0.judul', 'Laptop Penjual Disetujui')
    );
});

test('catalog pagination limits listings to 12 items per page with deterministic ordering', function () {
    $user = User::factory()->verified()->create(['is_suspended' => false]);

    // Buat 15 listing aktif dengan tanggal berbeda
    for ($i = 1; $i <= 15; $i++) {
        $lapak = Lapak::factory()->aktif()->create([
            'user_id' => $user->id,
            'expires_at' => now()->addDays(30),
        ]);
        Listing::factory()->aktif()->create([
            'lapak_id' => $lapak->id,
            'judul' => sprintf('Laptop Unit #%02d', $i),
            'created_at' => now()->subMinutes(100 - $i),
        ]);
    }

    // Halaman 1: harus memuat 12 item terbaru (Unit #15 s.d. #04)
    $responsePage1 = $this->get(route('catalog.index'));
    $responsePage1->assertOk();
    $responsePage1->assertInertia(fn (Assert $page) => $page
        ->component('Catalog/Index')
        ->where('listings.total', 15)
        ->where('listings.per_page', 12)
        ->where('listings.current_page', 1)
        ->where('listings.last_page', 2)
        ->has('listings.data', 12)
        ->where('listings.data.0.judul', 'Laptop Unit #15')
    );

    // Halaman 2: harus memuat 3 item sisanya (Unit #03 s.d. #01)
    $responsePage2 = $this->get(route('catalog.index', ['page' => 2]));
    $responsePage2->assertOk();
    $responsePage2->assertInertia(fn (Assert $page) => $page
        ->component('Catalog/Index')
        ->where('listings.total', 15)
        ->where('listings.current_page', 2)
        ->has('listings.data', 3)
    );
});

test('catalog does not expose sensitive seller data', function () {
    $user = User::factory()->verified()->create([
        'email' => 'rahasia@pnp.ac.id',
        'is_suspended' => false,
    ]);
    $lapak = Lapak::factory()->aktif()->create([
        'user_id' => $user->id,
        'expires_at' => now()->addDays(30),
    ]);
    $listing = Listing::factory()->aktif()->create([
        'lapak_id' => $lapak->id,
        'no_wa' => '081234567890',
        'judul' => 'MacBook Air M1 Bersih Data',
    ]);

    $response = $this->get(route('catalog.index'));

    $response->assertOk();
    $response->assertInertia(function (Assert $page) use ($listing) {
        $page->component('Catalog/Index')
            ->has('listings.data', 1)
            ->where('listings.data.0.id', $listing->id)
            ->missing('listings.data.0.no_wa')
            ->missing('listings.data.0.user')
            ->missing('listings.data.0.ktm_path');
    });
});

test('homepage and catalog have consistent publication rules', function () {
    $activeUser = User::factory()->verified()->create(['is_suspended' => false]);
    $suspendedUser = User::factory()->verified()->create(['is_suspended' => true]);

    $validLapak = Lapak::factory()->aktif()->create([
        'user_id' => $activeUser->id,
        'expires_at' => now()->addDays(15),
    ]);
    $validListing = Listing::factory()->aktif()->create([
        'lapak_id' => $validLapak->id,
        'judul' => 'Konsistensi Listing Laptop',
    ]);

    // Listing invalid karena lapak expired
    $expiredLapak = Lapak::factory()->aktif()->create([
        'user_id' => $activeUser->id,
        'expires_at' => now()->subDay(),
    ]);
    Listing::factory()->aktif()->create([
        'lapak_id' => $expiredLapak->id,
        'judul' => 'Laptop Expired',
    ]);

    // Listing invalid karena user suspended
    $suspendedLapak = Lapak::factory()->aktif()->create([
        'user_id' => $suspendedUser->id,
        'expires_at' => now()->addDays(15),
    ]);
    Listing::factory()->aktif()->create([
        'lapak_id' => $suspendedLapak->id,
        'judul' => 'Laptop Suspended',
    ]);

    // Keduanya harus sama persis: hanya menampilkan 1 listing valid
    $homeRes = $this->get(route('home'));
    $homeRes->assertOk();
    $homeRes->assertInertia(fn (Assert $page) => $page
        ->component('Welcome')
        ->has('latestListings', 1)
        ->where('latestListings.0.id', $validListing->id)
    );

    $catalogRes = $this->get(route('catalog.index'));
    $catalogRes->assertOk();
    $catalogRes->assertInertia(fn (Assert $page) => $page
        ->component('Catalog/Index')
        ->where('listings.total', 1)
        ->where('listings.data.0.id', $validListing->id)
    );
});
