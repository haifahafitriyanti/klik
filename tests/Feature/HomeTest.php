<?php

use App\Models\Category;
use App\Models\Lapak;
use App\Models\Listing;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guest can access marketplace homepage', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Welcome')
        ->has('categories')
        ->has('latestListings')
    );
});

test('authenticated admin and users can access marketplace homepage', function () {
    $admin = User::factory()->admin()->create();
    $verifiedUser = User::factory()->verified()->create();
    $pendingUser = User::factory()->create(['status_verifikasi' => 'menunggu']);
    $rejectedUser = User::factory()->rejected()->create();

    $this->actingAs($admin)->get(route('home'))->assertOk();
    $this->actingAs($verifiedUser)->get(route('home'))->assertOk();
    $this->actingAs($pendingUser)->get(route('home'))->assertOk();
    $this->actingAs($rejectedUser)->get(route('home'))->assertOk();
});

test('homepage returns categories ordered by priority', function () {
    Category::factory()->create(['nama' => 'Laptop Standar', 'prioritas' => 5]);
    Category::factory()->create(['nama' => 'MacBook', 'prioritas' => 1]);
    Category::factory()->create(['nama' => 'Laptop Gaming', 'prioritas' => 2]);

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Welcome')
        ->where('categories.0.prioritas', 1)
        ->where('categories.0.nama', 'MacBook')
        ->where('categories.1.prioritas', 2)
        ->where('categories.1.nama', 'Laptop Gaming')
        ->where('categories.2.prioritas', 5)
        ->where('categories.2.nama', 'Laptop Standar')
    );
});

test('homepage returns empty listings when no listings exist', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Welcome')
        ->has('latestListings', 0)
    );
});

test('homepage displays only active listings from active unexpired lapak', function () {
    // 1. Lapak aktif dan belum expired
    $activeLapak = Lapak::factory()->aktif()->create([
        'expires_at' => now()->addDays(30),
    ]);

    // Listing aktif dalam lapak aktif -> harus muncul
    $validListing = Listing::factory()->aktif()->create([
        'lapak_id' => $activeLapak->id,
        'judul' => 'ThinkPad T480 Mulus PNP',
    ]);

    // Listing non-aktif dalam lapak aktif -> tidak boleh muncul
    Listing::factory()->create([
        'lapak_id' => $activeLapak->id,
        'status' => 'terjual',
        'judul' => 'MacBook Air Terjual',
    ]);

    // 2. Lapak expired -> tidak boleh muncul
    $expiredLapak = Lapak::factory()->aktif()->create([
        'expires_at' => now()->subDay(),
    ]);
    Listing::factory()->aktif()->create([
        'lapak_id' => $expiredLapak->id,
        'judul' => 'Laptop Lapak Expired',
    ]);

    // 3. Lapak tidak aktif (misal jeda/banned) -> tidak boleh muncul
    $inactiveLapak = Lapak::factory()->jeda()->create([
        'expires_at' => now()->addDays(10),
    ]);
    Listing::factory()->aktif()->create([
        'lapak_id' => $inactiveLapak->id,
        'judul' => 'Laptop Lapak Jeda',
    ]);

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Welcome')
        ->has('latestListings', 1)
        ->where('latestListings.0.id', $validListing->id)
        ->where('latestListings.0.judul', 'ThinkPad T480 Mulus PNP')
    );
});

test('homepage listings preview does not expose sensitive seller data', function () {
    $lapak = Lapak::factory()->aktif()->create([
        'expires_at' => now()->addDays(30),
    ]);

    $listing = Listing::factory()->aktif()->create([
        'lapak_id' => $lapak->id,
        'no_wa' => '081234567890',
        'judul' => 'Asus ROG Strix G15',
    ]);

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertInertia(function (Assert $page) use ($listing) {
        $page->component('Welcome')
            ->has('latestListings', 1)
            ->where('latestListings.0.id', $listing->id)
            ->missing('latestListings.0.no_wa')
            ->missing('latestListings.0.user')
            ->missing('latestListings.0.ktm_path');
    });
});

test('homepage excludes listings from unapproved sellers with pending or rejected verification status', function () {
    $approvedSeller = User::factory()->verified()->create();
    $pendingSeller = User::factory()->create(['status_verifikasi' => 'menunggu']);
    $rejectedSeller = User::factory()->rejected()->create();

    $approvedLapak = Lapak::factory()->aktif()->create([
        'user_id' => $approvedSeller->id,
        'expires_at' => now()->addDays(30),
    ]);
    $approvedListing = Listing::factory()->aktif()->create([
        'lapak_id' => $approvedLapak->id,
        'judul' => 'Laptop Sah di Homepage',
    ]);

    $pendingLapak = Lapak::factory()->aktif()->create([
        'user_id' => $pendingSeller->id,
        'expires_at' => now()->addDays(30),
    ]);
    Listing::factory()->aktif()->create([
        'lapak_id' => $pendingLapak->id,
        'judul' => 'Laptop Pending di Homepage',
    ]);

    $rejectedLapak = Lapak::factory()->aktif()->create([
        'user_id' => $rejectedSeller->id,
        'expires_at' => now()->addDays(30),
    ]);
    Listing::factory()->aktif()->create([
        'lapak_id' => $rejectedLapak->id,
        'judul' => 'Laptop Rejected di Homepage',
    ]);

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Welcome')
        ->has('latestListings', 1)
        ->where('latestListings.0.id', $approvedListing->id)
        ->where('latestListings.0.judul', 'Laptop Sah di Homepage')
    );
});
