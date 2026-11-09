<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Lapak;
use App\Models\Listing;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * Helper untuk membuat listing aktif yang sah dengan lapak unik tersendiri.
 * (1 lapak aktif hanya boleh memiliki 1 listing aktif sesuai konstrain uq_listings_active_lapak_id)
 *
 * @param  array<string, mixed>  $attributes
 */
function makeActiveListing(array $attributes = []): Listing
{
    if (! isset($attributes['lapak_id'])) {
        $user = User::factory()->verified()->create(['is_suspended' => false]);
        $lapak = Lapak::factory()->aktif()->create([
            'user_id' => $user->id,
            'expires_at' => now()->addDays(30),
        ]);
        $attributes['lapak_id'] = $lapak->id;
    }

    return Listing::factory()->aktif()->create($attributes);
}

test('1. guest can access marketplace catalog page with categories and brands props', function () {
    Category::factory()->create(['nama' => 'Laptop Gaming', 'prioritas' => 2]);
    Brand::factory()->create(['nama' => 'ASUS']);

    $response = $this->get(route('catalog.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Catalog/Index')
        ->has('categories')
        ->has('brands')
        ->has('listings')
        ->has('filters')
        ->where('filters.sort', 'newest')
    );
});

test('2. authenticated users with any verification status and admin can access catalog page', function () {
    $admin = User::factory()->admin()->create();
    $verifiedUser = User::factory()->verified()->create();
    $pendingUser = User::factory()->create(['status_verifikasi' => 'menunggu']);
    $rejectedUser = User::factory()->rejected()->create();

    $this->actingAs($admin)->get(route('catalog.index'))->assertOk();
    $this->actingAs($verifiedUser)->get(route('catalog.index'))->assertOk();
    $this->actingAs($pendingUser)->get(route('catalog.index'))->assertOk();
    $this->actingAs($rejectedUser)->get(route('catalog.index'))->assertOk();
});

test('3. search by keyword in listing title returns matching items', function () {
    $match = makeActiveListing([
        'judul' => 'ThinkPad X1 Carbon Gen 9 Mahasiswa',
    ]);

    makeActiveListing([
        'judul' => 'MacBook Air M1 Retina',
    ]);

    $response = $this->get(route('catalog.index', ['q' => 'thinkpad']));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Catalog/Index')
        ->where('listings.total', 1)
        ->where('listings.data.0.id', $match->id)
        ->where('filters.q', 'thinkpad')
    );
});

test('4. search by brand name matches items via brand relationship', function () {
    $asus = Brand::factory()->create(['nama' => 'ASUS']);
    $lenovo = Brand::factory()->create(['nama' => 'Lenovo']);

    $asusLaptop = makeActiveListing([
        'brand_id' => $asus->id,
        'judul' => 'Zenbook 14 OLED Slim',
    ]);

    makeActiveListing([
        'brand_id' => $lenovo->id,
        'judul' => 'Legion 5 Pro',
    ]);

    $response = $this->get(route('catalog.index', ['q' => 'ASUS']));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Catalog/Index')
        ->where('listings.total', 1)
        ->where('listings.data.0.id', $asusLaptop->id)
    );
});

test('5. search by laptop model matches items', function () {
    $match = makeActiveListing([
        'judul' => 'Laptop Kuliah Cepat',
        'model' => 'Yoga-9i-Convertible',
    ]);

    makeActiveListing([
        'judul' => 'Laptop Kuliah Biasa',
        'model' => 'Aspire-5-A514',
    ]);

    $response = $this->get(route('catalog.index', ['q' => 'yoga-9i']));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Catalog/Index')
        ->where('listings.total', 1)
        ->where('listings.data.0.id', $match->id)
    );
});

test('6. category filter accurately filters listings by slug or id', function () {
    $gamingCat = Category::factory()->create(['nama' => 'Laptop Gaming', 'prioritas' => 2]);
    $ultrabookCat = Category::factory()->create(['nama' => 'Ultrabook', 'prioritas' => 4]);

    $gamingListing = makeActiveListing([
        'category_id' => $gamingCat->id,
        'judul' => 'ROG Zephyrus G14',
    ]);

    $ultrabookListing = makeActiveListing([
        'category_id' => $ultrabookCat->id,
        'judul' => 'Zenbook 13 Ultralight',
    ]);

    // Uji dengan slug 'gaming'
    $responseSlug = $this->get(route('catalog.index', ['category' => 'gaming']));
    $responseSlug->assertOk();
    $responseSlug->assertInertia(fn (Assert $page) => $page
        ->component('Catalog/Index')
        ->where('listings.total', 1)
        ->where('listings.data.0.id', $gamingListing->id)
        ->where('filters.category', 'gaming')
    );

    // Uji dengan slug 'ultrabook'
    $responseUltrabook = $this->get(route('catalog.index', ['category' => 'ultrabook']));
    $responseUltrabook->assertOk();
    $responseUltrabook->assertInertia(fn (Assert $page) => $page
        ->component('Catalog/Index')
        ->where('listings.total', 1)
        ->where('listings.data.0.id', $ultrabookListing->id)
    );
});

test('7. brand filter accurately filters listings by slug', function () {
    $apple = Brand::factory()->create(['nama' => 'Apple']);
    $dell = Brand::factory()->create(['nama' => 'Dell']);

    $appleLaptop = makeActiveListing([
        'brand_id' => $apple->id,
        'judul' => 'MacBook Air M2',
    ]);

    makeActiveListing([
        'brand_id' => $dell->id,
        'judul' => 'Dell XPS 13',
    ]);

    $response = $this->get(route('catalog.index', ['brand' => 'apple']));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Catalog/Index')
        ->where('listings.total', 1)
        ->where('listings.data.0.id', $appleLaptop->id)
        ->where('filters.brand', 'apple')
    );
});

test('8. min_price filter excludes listings priced below threshold', function () {
    makeActiveListing([
        'harga' => 3000000,
        'judul' => 'Laptop Murah 3 Juta',
    ]);

    $expensive = makeActiveListing([
        'harga' => 7500000,
        'judul' => 'Laptop Menengah 7.5 Juta',
    ]);

    $response = $this->get(route('catalog.index', ['min_price' => 5000000]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Catalog/Index')
        ->where('listings.total', 1)
        ->where('listings.data.0.id', $expensive->id)
    );
});

test('9. max_price filter excludes listings priced above threshold', function () {
    $cheap = makeActiveListing([
        'harga' => 4000000,
        'judul' => 'Laptop Masuk Budget',
    ]);

    makeActiveListing([
        'harga' => 9000000,
        'judul' => 'Laptop Di Atas Budget',
    ]);

    $response = $this->get(route('catalog.index', ['max_price' => 5000000]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Catalog/Index')
        ->where('listings.total', 1)
        ->where('listings.data.0.id', $cheap->id)
    );
});

test('10. price range filter accurately matches listings between min and max inclusive', function () {
    makeActiveListing(['harga' => 2000000]);
    $item2 = makeActiveListing(['harga' => 4000000]);
    $item3 = makeActiveListing(['harga' => 6000000]);
    makeActiveListing(['harga' => 8000000]);

    $response = $this->get(route('catalog.index', [
        'min_price' => 4000000,
        'max_price' => 6000000,
    ]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Catalog/Index')
        ->where('listings.total', 2)
    );
});

test('11. sort newest orders listings by created_at desc then id desc', function () {
    $old = makeActiveListing([
        'created_at' => now()->subDays(5),
        'judul' => 'Laptop Lama',
    ]);

    $new = makeActiveListing([
        'created_at' => now()->subDay(),
        'judul' => 'Laptop Baru',
    ]);

    $response = $this->get(route('catalog.index', ['sort' => 'newest']));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Catalog/Index')
        ->where('listings.data.0.id', $new->id)
        ->where('listings.data.1.id', $old->id)
    );
});

test('12. sort price_asc orders listings by harga ascending', function () {
    $cheap = makeActiveListing(['harga' => 3500000]);
    $expensive = makeActiveListing(['harga' => 9000000]);

    $response = $this->get(route('catalog.index', ['sort' => 'price_asc']));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Catalog/Index')
        ->where('listings.data.0.id', $cheap->id)
        ->where('listings.data.1.id', $expensive->id)
    );
});

test('13. sort price_desc orders listings by harga descending', function () {
    $cheap = makeActiveListing(['harga' => 3500000]);
    $expensive = makeActiveListing(['harga' => 9000000]);

    $response = $this->get(route('catalog.index', ['sort' => 'price_desc']));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Catalog/Index')
        ->where('listings.data.0.id', $expensive->id)
        ->where('listings.data.1.id', $cheap->id)
    );
});

test('14. combined search category brand price and sort work seamlessly together', function () {
    $gamingCat = Category::factory()->create(['nama' => 'Laptop Gaming', 'prioritas' => 2]);
    $standardCat = Category::factory()->create(['nama' => 'Laptop Standar', 'prioritas' => 5]);

    $lenovo = Brand::factory()->create(['nama' => 'Lenovo']);
    $asus = Brand::factory()->create(['nama' => 'ASUS']);

    // Target match
    $targetMatch = makeActiveListing([
        'category_id' => $gamingCat->id,
        'brand_id' => $lenovo->id,
        'judul' => 'Lenovo Legion 5 R7 RTX',
        'harga' => 8500000,
    ]);

    // Beda brand
    makeActiveListing([
        'category_id' => $gamingCat->id,
        'brand_id' => $asus->id,
        'judul' => 'ASUS TUF Legion Rival',
        'harga' => 8500000,
    ]);

    // Beda harga (terlalu mahal)
    makeActiveListing([
        'category_id' => $gamingCat->id,
        'brand_id' => $lenovo->id,
        'judul' => 'Lenovo Legion Pro 7 High End',
        'harga' => 15000000,
    ]);

    // Beda kategori
    makeActiveListing([
        'category_id' => $standardCat->id,
        'brand_id' => $lenovo->id,
        'judul' => 'Lenovo Ideapad 3 Non Gaming',
        'harga' => 5000000,
    ]);

    $response = $this->get(route('catalog.index', [
        'q' => 'legion',
        'category' => 'gaming',
        'brand' => 'lenovo',
        'min_price' => 7000000,
        'max_price' => 10000000,
        'sort' => 'price_asc',
    ]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Catalog/Index')
        ->where('listings.total', 1)
        ->where('listings.data.0.id', $targetMatch->id)
    );
});

test('15. pagination maintains active search filter and sort query parameters', function () {
    $lenovo = Brand::factory()->create(['nama' => 'Lenovo']);

    // Buat 15 listing Lenovo unik
    for ($i = 1; $i <= 15; $i++) {
        makeActiveListing([
            'brand_id' => $lenovo->id,
            'judul' => sprintf('Lenovo Laptop #%02d', $i),
            'harga' => 5000000 + ($i * 100000),
            'created_at' => now()->subMinutes(100 - $i),
        ]);
    }

    $response = $this->get(route('catalog.index', [
        'brand' => 'lenovo',
        'sort' => 'price_asc',
        'page' => 2,
    ]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Catalog/Index')
        ->where('listings.total', 15)
        ->where('listings.current_page', 2)
        ->has('listings.data', 3)
        ->where('listings.prev_page_url', fn ($url) => str_contains((string) $url, 'brand=lenovo') && str_contains((string) $url, 'sort=price_asc'))
    );
});

test('16. empty result shows appropriate state when active filter returns no match', function () {
    makeActiveListing([
        'judul' => 'ThinkPad Laptop Biasa',
    ]);

    $response = $this->get(route('catalog.index', ['q' => 'Alienware']));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Catalog/Index')
        ->where('listings.total', 0)
        ->where('listings.data', [])
        ->where('filters.q', 'Alienware')
    );
});

test('17. negative price inputs are sanitized safely without causing errors', function () {
    $response = $this->get(route('catalog.index', [
        'min_price' => -500000,
        'max_price' => -100000,
    ]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Catalog/Index')
        ->where('filters.min_price', null)
        ->where('filters.max_price', null)
    );
});

test('18. non numeric price inputs are sanitized safely without causing errors', function () {
    $response = $this->get(route('catalog.index', [
        'min_price' => 'harga_murah',
        'max_price' => 'bukan_angka',
    ]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Catalog/Index')
        ->where('filters.min_price', null)
        ->where('filters.max_price', null)
    );
});

test('19. price validation error when min_price is greater than max_price', function () {
    $response = $this->get(route('catalog.index', [
        'min_price' => 8000000,
        'max_price' => 3000000,
    ]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Catalog/Index')
        ->where('listings.total', 0)
        ->where('filterErrors.price', 'Harga minimum tidak boleh lebih besar dari harga maksimum.')
    );
});

test('20. unknown sort value safely defaults to newest', function () {
    $response = $this->get(route('catalog.index', ['sort' => 'random_sql_injection_attempt']));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Catalog/Index')
        ->where('filters.sort', 'newest')
    );
});

test('21. invalid category or brand parameter is handled safely without 500 error', function () {
    $response = $this->get(route('catalog.index', [
        'category' => 'kategori_tidak_ada_di_db',
        'brand' => 'brand_khayalan_xyz',
    ]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Catalog/Index')
        ->where('listings.total', 0)
    );
});

test('22. search OR condition never exposes private inactive expired or unapproved listings', function () {
    // Valid public listing matching search
    $validListing = makeActiveListing([
        'judul' => 'ThinkPad Laptop Sah',
    ]);

    // Inactive listing matching search -> MUST NOT APPEAR
    $activeUser = User::factory()->verified()->create(['is_suspended' => false]);
    $activeLapak = Lapak::factory()->aktif()->create(['user_id' => $activeUser->id, 'expires_at' => now()->addDays(30)]);
    Listing::factory()->create([
        'lapak_id' => $activeLapak->id,
        'status' => 'terjual',
        'judul' => 'ThinkPad Laptop Terjual',
    ]);

    // Expired lapak listing matching search -> MUST NOT APPEAR
    $expiredLapak = Lapak::factory()->aktif()->create(['user_id' => $activeUser->id, 'expires_at' => now()->subDay()]);
    Listing::factory()->aktif()->create([
        'lapak_id' => $expiredLapak->id,
        'judul' => 'ThinkPad Laptop Expired',
    ]);

    // Suspended seller listing matching search -> MUST NOT APPEAR
    $suspendedUser = User::factory()->verified()->create(['is_suspended' => true]);
    $suspendedLapak = Lapak::factory()->aktif()->create(['user_id' => $suspendedUser->id, 'expires_at' => now()->addDays(30)]);
    Listing::factory()->aktif()->create([
        'lapak_id' => $suspendedLapak->id,
        'judul' => 'ThinkPad Laptop Penjual Suspended',
    ]);

    // Unapproved seller listing matching search -> MUST NOT APPEAR
    $unapprovedUser = User::factory()->create(['status_verifikasi' => 'menunggu']);
    $unapprovedLapak = Lapak::factory()->aktif()->create(['user_id' => $unapprovedUser->id, 'expires_at' => now()->addDays(30)]);
    Listing::factory()->aktif()->create([
        'lapak_id' => $unapprovedLapak->id,
        'judul' => 'ThinkPad Laptop Penjual Menunggu',
    ]);

    $response = $this->get(route('catalog.index', ['q' => 'ThinkPad']));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Catalog/Index')
        ->where('listings.total', 1)
        ->where('listings.data.0.id', $validListing->id)
    );
});

test('23. catalog does not expose sensitive seller data', function () {
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

test('24. reset filter without parameters returns full eligible catalog', function () {
    makeActiveListing(['judul' => 'Laptop A']);
    makeActiveListing(['judul' => 'Laptop B']);

    $response = $this->get(route('catalog.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Catalog/Index')
        ->where('listings.total', 2)
        ->where('filters.q', null)
        ->where('filters.category', null)
        ->where('filters.brand', null)
        ->where('filters.min_price', null)
        ->where('filters.max_price', null)
    );
});

test('25. homepage and catalog have consistent publication rules', function () {
    $validListing = makeActiveListing([
        'judul' => 'Konsistensi Listing Laptop',
    ]);

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

test('26. homepage category cards provide valid category slugs that match catalog filter', function () {
    $gaming = Category::factory()->create(['nama' => 'Laptop Gaming', 'prioritas' => 2]);
    $gamingLaptop = makeActiveListing([
        'category_id' => $gaming->id,
        'judul' => 'Laptop Gaming Kampus',
    ]);

    // Kategori slug 'gaming' dari kartu kategori homepage dapat langsung digunakan di /katalog
    $response = $this->get(route('catalog.index', ['category' => 'gaming']));
    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Catalog/Index')
        ->where('listings.total', 1)
        ->where('listings.data.0.id', $gamingLaptop->id)
    );
});
