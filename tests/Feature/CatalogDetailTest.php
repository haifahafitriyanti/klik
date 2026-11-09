<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Lapak;
use App\Models\Listing;
use App\Models\ListingPhoto;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * Helper pembuatan listing aktif publik yang sah untuk pengujian detail katalog.
 *
 * @param  array<string, mixed>  $attributes
 * @param  array<string, mixed>  $userAttributes
 * @param  array<string, mixed>  $lapakAttributes
 */
function createEligibleDetailListing(
    array $attributes = [],
    array $userAttributes = [],
    array $lapakAttributes = []
): Listing {
    $user = User::factory()->verified()->create(array_merge([
        'is_suspended' => false,
        'status_mahasiswa' => 'aktif',
    ], $userAttributes));

    $lapak = Lapak::factory()->aktif()->create(array_merge([
        'user_id' => $user->id,
        'expires_at' => now()->addDays(30),
    ], $lapakAttributes));

    return Listing::factory()->aktif()->create(array_merge([
        'lapak_id' => $lapak->id,
    ], $attributes));
}

test('1. guest can view active public listing detail page', function () {
    $listing = createEligibleDetailListing([
        'judul' => 'ThinkPad T480 Core i5 Mantap',
    ]);

    $response = $this->get(route('catalog.show', $listing->id));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Catalog/Show')
        ->has('listing')
        ->where('listing.id', $listing->id)
        ->where('listing.judul', 'ThinkPad T480 Core i5 Mantap')
    );
});

test('2. approved student user can view active public listing detail page', function () {
    $user = User::factory()->verified()->create(['is_suspended' => false]);
    $listing = createEligibleDetailListing([
        'judul' => 'MacBook Air M1 2020 Space Grey',
    ]);

    $response = $this->actingAs($user)->get(route('catalog.show', $listing->id));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Catalog/Show')
        ->where('listing.id', $listing->id)
    );
});

test('3. admin can view active public listing detail page', function () {
    $admin = User::factory()->admin()->create();
    $listing = createEligibleDetailListing([
        'judul' => 'ASUS ROG Zephyrus G14 Gaming',
    ]);

    $response = $this->actingAs($admin)->get(route('catalog.show', $listing->id));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Catalog/Show')
        ->where('listing.id', $listing->id)
    );
});

test('4. nonexistent listing id returns 404', function () {
    $response = $this->get(route('catalog.show', 999999));

    $response->assertNotFound();
});

test('5. inactive listing returns 404 on direct access', function () {
    $listing = createEligibleDetailListing();
    $listing->update(['status' => 'kedaluwarsa']);

    $response = $this->get(route('catalog.show', $listing->id));

    $response->assertNotFound();
});

test('6. sold listing returns 404 on direct access', function () {
    $listing = createEligibleDetailListing();
    $listing->update([
        'status' => 'terjual',
        'sold_at' => now(),
    ]);

    $response = $this->get(route('catalog.show', $listing->id));

    $response->assertNotFound();
});

test('7. hidden listing returns 404 on direct access', function () {
    $listing = createEligibleDetailListing();
    $listing->update(['status' => 'tersembunyi']);

    $response = $this->get(route('catalog.show', $listing->id));

    $response->assertNotFound();
});

test('8. listing with inactive lapak returns 404', function () {
    $user = User::factory()->verified()->create(['is_suspended' => false]);
    $lapak = Lapak::factory()->create([
        'user_id' => $user->id,
        'status' => 'ditutup',
        'expires_at' => now()->addDays(30),
    ]);
    $listing = Listing::factory()->aktif()->create(['lapak_id' => $lapak->id]);

    $response = $this->get(route('catalog.show', $listing->id));

    $response->assertNotFound();
});

test('9. listing with expired lapak returns 404', function () {
    $user = User::factory()->verified()->create(['is_suspended' => false]);
    $lapak = Lapak::factory()->aktif()->create([
        'user_id' => $user->id,
        'expires_at' => now()->subDay(),
    ]);
    $listing = Listing::factory()->aktif()->create(['lapak_id' => $lapak->id]);

    $response = $this->get(route('catalog.show', $listing->id));

    $response->assertNotFound();
});

test('10. listing with suspended seller returns 404', function () {
    $user = User::factory()->verified()->create(['is_suspended' => true]);
    $lapak = Lapak::factory()->aktif()->create([
        'user_id' => $user->id,
        'expires_at' => now()->addDays(30),
    ]);
    $listing = Listing::factory()->aktif()->create(['lapak_id' => $lapak->id]);

    $response = $this->get(route('catalog.show', $listing->id));

    $response->assertNotFound();
});

test('11. listing with unapproved pending seller returns 404', function () {
    $user = User::factory()->create([
        'status_verifikasi' => 'menunggu',
        'is_suspended' => false,
    ]);
    $lapak = Lapak::factory()->aktif()->create([
        'user_id' => $user->id,
        'expires_at' => now()->addDays(30),
    ]);
    $listing = Listing::factory()->aktif()->create(['lapak_id' => $lapak->id]);

    $response = $this->get(route('catalog.show', $listing->id));

    $response->assertNotFound();
});

test('12. listing with rejected seller returns 404', function () {
    $user = User::factory()->rejected()->create(['is_suspended' => false]);
    $lapak = Lapak::factory()->aktif()->create([
        'user_id' => $user->id,
        'expires_at' => now()->addDays(30),
    ]);
    $listing = Listing::factory()->aktif()->create(['lapak_id' => $lapak->id]);

    $response = $this->get(route('catalog.show', $listing->id));

    $response->assertNotFound();
});

test('13. detail displays accurate product data matching database fields', function () {
    $brand = Brand::factory()->create(['nama' => 'Lenovo']);
    $category = Category::factory()->create(['nama' => 'Ultrabook']);

    $listing = createEligibleDetailListing([
        'brand_id' => $brand->id,
        'category_id' => $category->id,
        'judul' => 'Lenovo Yoga Slim 7 Pro',
        'model' => 'Yoga Slim 7 Pro 14ACH5',
        'harga' => 8500000,
        'kondisi' => 'mulus',
        'prosesor' => 'AMD Ryzen 7 5800H',
        'ram_gb' => 16,
        'penyimpanan' => '512GB NVMe SSD',
        'gpu_dedicated' => false,
        'tipe_2in1' => true,
        'berat_kg' => '1.32',
        'deskripsi' => 'Kondisi sangat mulus terawat, baterai awet, lengkap kardus.',
    ]);

    $response = $this->get(route('catalog.show', $listing->id));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Catalog/Show')
        ->where('listing.id', $listing->id)
        ->where('listing.judul', 'Lenovo Yoga Slim 7 Pro')
        ->where('listing.model', 'Yoga Slim 7 Pro 14ACH5')
        ->where('listing.harga', 8500000)
        ->where('listing.kondisi', 'mulus')
        ->where('listing.prosesor', 'AMD Ryzen 7 5800H')
        ->where('listing.ram_gb', 16)
        ->where('listing.penyimpanan', '512GB NVMe SSD')
        ->where('listing.gpu_dedicated', false)
        ->where('listing.tipe_2in1', true)
        ->where('listing.berat_kg', '1.32')
        ->where('listing.brand_name', 'Lenovo')
        ->where('listing.category_name', 'Ultrabook')
        ->where('listing.deskripsi', 'Kondisi sangat mulus terawat, baterai awet, lengkap kardus.')
    );
});

test('14. photos are sorted deterministically in response', function () {
    $listing = createEligibleDetailListing();

    $photo1 = ListingPhoto::factory()->create([
        'listing_id' => $listing->id,
        'path' => 'photos/first_photo.jpg',
    ]);
    $photo2 = ListingPhoto::factory()->create([
        'listing_id' => $listing->id,
        'path' => 'photos/second_photo.jpg',
    ]);

    $response = $this->get(route('catalog.show', $listing->id));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Catalog/Show')
        ->has('listing.photos', 2)
        ->where('listing.photos.0.id', $photo1->id)
        ->where('listing.photos.0.url', 'photos/first_photo.jpg')
        ->where('listing.photos.1.id', $photo2->id)
        ->where('listing.photos.1.url', 'photos/second_photo.jpg')
    );
});

test('15. safe photo placeholder provided when listing has no photos', function () {
    $listing = createEligibleDetailListing();

    $response = $this->get(route('catalog.show', $listing->id));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Catalog/Show')
        ->where('listing.photos', [])
    );
});

test('16. inertia props do not expose nim whatsapp ktm email password or payment data', function () {
    $user = User::factory()->verified()->create([
        'name' => 'Fakhri Mahasiswa TI',
        'nim' => '2301082099',
        'email' => 'fakhri_rahasia@pnp.ac.id',
        'ktm_path' => 'ktm/rahasia_fakhri.jpg',
        'is_suspended' => false,
    ]);
    $lapak = Lapak::factory()->aktif()->create([
        'user_id' => $user->id,
        'expires_at' => now()->addDays(30),
    ]);
    $listing = Listing::factory()->aktif()->create([
        'lapak_id' => $lapak->id,
        'no_wa' => '081299998888',
        'judul' => 'Laptop Privasi Aman',
    ]);

    $response = $this->get(route('catalog.show', $listing->id));

    $response->assertOk();
    $response->assertInertia(function (Assert $page) {
        $page->component('Catalog/Show')
            // Cek listing level
            ->missing('listing.no_wa')
            ->missing('listing.ktm_path')
            ->missing('listing.lapak')
            // Cek seller level
            ->has('listing.seller')
            ->where('listing.seller.name', 'Fakhri Mahasiswa TI')
            ->where('listing.seller.status_verifikasi', 'disetujui')
            ->missing('listing.seller.nim')
            ->missing('listing.seller.email')
            ->missing('listing.seller.password')
            ->missing('listing.seller.ktm_path')
            ->missing('listing.seller.no_wa');
    });
});

test('17. catalog index route remains fully operational', function () {
    $response = $this->get(route('catalog.index'));
    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page->component('Catalog/Index'));
});

test('18. phase 4.3 search and filter regressions do not occur', function () {
    $listing1 = createEligibleDetailListing(['judul' => 'ThinkPad Laptop A']);
    createEligibleDetailListing(['judul' => 'MacBook Laptop B']);

    $response = $this->get(route('catalog.index', ['q' => 'ThinkPad']));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Catalog/Index')
        ->where('listings.total', 1)
        ->where('listings.data.0.id', $listing1->id)
    );
});

test('19. homepage route remains fully operational', function () {
    $response = $this->get(route('home'));
    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page->component('Welcome'));
});

test('20. detail page uses correct Catalog/Show inertia component', function () {
    $listing = createEligibleDetailListing(['judul' => 'Laptop Komponen Inertia']);

    $response = $this->get(route('catalog.show', $listing->id));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page->component('Catalog/Show'));
});
