<?php

namespace App\Http\Controllers;

use App\Http\Requests\CatalogFilterRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Listing;
use App\Models\ListingPhoto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    /**
     * Tampilkan halaman utama katalog laptop marketplace KLiK dengan pencarian, filter, dan sorting (Fase 4.3).
     */
    public function index(CatalogFilterRequest $request): Response
    {
        // 1. Ambil seluruh kategori master dan normalisasi slug
        $rawCategories = Category::query()
            ->orderBy('prioritas')
            ->get(['id', 'nama', 'prioritas', 'kriteria']);

        $categories = $rawCategories->map(function (Category $cat) {
            $slug = Str::slug($cat->nama);
            $shortSlug = str_replace('laptop-', '', $slug);
            if ($shortSlug === 'standar') {
                $shortSlug = 'standard';
            }

            return [
                'id' => $cat->id,
                'nama' => $cat->nama,
                'slug' => $shortSlug,
                'full_slug' => $slug,
                'prioritas' => $cat->prioritas,
                'kriteria' => $cat->kriteria,
            ];
        });

        // 2. Ambil seluruh brand master dari database (bukan hardcode)
        $rawBrands = Brand::query()
            ->orderBy('nama')
            ->get(['id', 'nama']);

        $brands = $rawBrands->map(fn (Brand $brand) => [
            'id' => $brand->id,
            'nama' => $brand->nama,
            'slug' => Str::slug($brand->nama),
        ]);

        // 3. Resolusi filter kategori aktif
        $rawCategoryParam = $request->query('category');
        $categoryParam = is_string($rawCategoryParam) && trim($rawCategoryParam) !== '' ? trim($rawCategoryParam) : null;
        $selectedCategory = null;
        $invalidCategoryPassed = false;

        if ($categoryParam !== null) {
            $matched = $rawCategories->first(function (Category $cat) use ($categoryParam) {
                $slug = Str::slug($cat->nama);
                $shortSlug = str_replace('laptop-', '', $slug);
                $paramSlug = Str::slug($categoryParam);

                return (string) $cat->id === $categoryParam
                    || $slug === $paramSlug
                    || $shortSlug === $paramSlug
                    || ($paramSlug === 'standard' && $shortSlug === 'standar')
                    || strcasecmp($cat->nama, $categoryParam) === 0;
            });

            if ($matched) {
                $selectedCategory = $matched;
            } else {
                $invalidCategoryPassed = true;
            }
        }

        // 4. Resolusi filter brand aktif
        $rawBrandParam = $request->query('brand');
        $brandParam = is_string($rawBrandParam) && trim($rawBrandParam) !== '' ? trim($rawBrandParam) : null;
        $selectedBrand = null;
        $invalidBrandPassed = false;

        if ($brandParam !== null) {
            $matched = $rawBrands->first(function (Brand $b) use ($brandParam) {
                $slug = Str::slug($b->nama);
                $paramSlug = Str::slug($brandParam);

                return (string) $b->id === $brandParam
                    || $slug === $paramSlug
                    || strcasecmp($b->nama, $brandParam) === 0;
            });

            if ($matched) {
                $selectedBrand = $matched;
            } else {
                $invalidBrandPassed = true;
            }
        }

        // 5. Resolusi rentang harga
        $minPrice = $request->filled('min_price') ? (int) $request->query('min_price') : null;
        $maxPrice = $request->filled('max_price') ? (int) $request->query('max_price') : null;
        $priceError = null;

        if ($minPrice !== null && $maxPrice !== null && $minPrice > $maxPrice) {
            $priceError = 'Harga minimum tidak boleh lebih besar dari harga maksimum.';
        }

        // 6. Resolusi kata kunci pencarian
        $rawQ = $request->query('q');
        $keyword = is_string($rawQ) ? trim($rawQ) : null;
        if ($keyword === '') {
            $keyword = null;
        }

        // 7. Resolusi sorting
        $rawSort = $request->query('sort');
        $sort = in_array($rawSort, ['newest', 'price_asc', 'price_desc'], true) ? $rawSort : 'newest';

        // 8. Bangun query listing publik
        $query = Listing::query()
            ->forPublicCatalog()
            ->with([
                'category:id,nama',
                'brand:id,nama',
                'photos:id,listing_id,path',
            ]);

        // Terapkan filter kategori
        if ($invalidCategoryPassed) {
            $query->whereRaw('1 = 0');
        } elseif ($selectedCategory) {
            $query->where('category_id', $selectedCategory->id);
        }

        // Terapkan filter brand
        if ($invalidBrandPassed) {
            $query->whereRaw('1 = 0');
        } elseif ($selectedBrand) {
            $query->where('brand_id', $selectedBrand->id);
        }

        // Terapkan pencarian kata kunci dengan grouping OR di dalam where()
        if ($keyword !== null) {
            $escaped = addcslashes($keyword, '%_\\');
            $like = "%{$escaped}%";

            $query->where(function (Builder $nested) use ($like) {
                $nested->where('judul', 'LIKE', $like)
                    ->orWhere('model', 'LIKE', $like)
                    ->orWhereHas('brand', function (Builder $brandQuery) use ($like) {
                        $brandQuery->where('nama', 'LIKE', $like);
                    });
            });
        }

        // Terapkan filter rentang harga
        if ($priceError !== null) {
            $query->whereRaw('1 = 0');
        } else {
            if ($minPrice !== null && $minPrice >= 0) {
                $query->where('harga', '>=', $minPrice);
            }
            if ($maxPrice !== null && $maxPrice >= 0) {
                $query->where('harga', '<=', $maxPrice);
            }
        }

        // Terapkan sorting sebelum pagination
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('harga', 'asc')->orderBy('id', 'desc');
                break;
            case 'price_desc':
                $query->orderBy('harga', 'desc')->orderBy('id', 'desc');
                break;
            case 'newest':
            default:
                $query->latest('created_at')->orderBy('id', 'desc');
                break;
        }

        // 9. Pagination server-side (12 item per halaman) dengan preserving query string
        $listings = $query->paginate(12)
            ->through(function (Listing $listing) {
                /** @var ListingPhoto|null $firstPhoto */
                $firstPhoto = $listing->photos->first();

                return [
                    'id' => $listing->id,
                    'judul' => $listing->judul,
                    'model' => $listing->model,
                    'harga' => $listing->harga,
                    'kondisi' => $listing->kondisi,
                    'prosesor' => $listing->prosesor,
                    'ram_gb' => $listing->ram_gb,
                    'penyimpanan' => $listing->penyimpanan,
                    'brand_name' => $listing->brand?->nama,
                    'category_name' => $listing->category?->nama,
                    'photo_url' => $firstPhoto ? $firstPhoto->path : null,
                    'created_at' => $listing->created_at?->diffForHumans(),
                ];
            })
            ->withQueryString();

        // 10. Siapkan state filter aktif untuk frontend
        $activeCategorySlug = null;
        if ($selectedCategory) {
            $matchedCategoryItem = $categories->firstWhere('id', $selectedCategory->id);
            $activeCategorySlug = $matchedCategoryItem['slug'] ?? Str::slug($selectedCategory->nama);
        } elseif ($categoryParam !== null) {
            $activeCategorySlug = $categoryParam;
        }

        $activeBrandSlug = null;
        if ($selectedBrand) {
            $activeBrandSlug = Str::slug($selectedBrand->nama);
        } elseif ($brandParam !== null) {
            $activeBrandSlug = $brandParam;
        }

        return Inertia::render('Catalog/Index', [
            'categories' => $categories,
            'brands' => $brands,
            'listings' => $listings,
            'filters' => [
                'q' => $keyword,
                'category' => $activeCategorySlug,
                'brand' => $activeBrandSlug,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'sort' => $sort,
            ],
            'filterErrors' => $priceError ? ['price' => $priceError] : null,
        ]);
    }

    /**
     * Tampilkan halaman detail laptop publik marketplace KLiK (Fase 4.4).
     */
    public function show(int $listing): Response
    {
        /** @var Listing $item */
        $item = Listing::query()
            ->forPublicCatalog()
            ->with([
                'category:id,nama',
                'brand:id,nama',
                'photos' => function ($photoQuery) {
                    $photoQuery->select(['id', 'listing_id', 'path', 'created_at'])->orderBy('id', 'asc');
                },
                'lapak.user:id,name,status_mahasiswa,status_verifikasi',
            ])
            ->findOrFail($listing);

        $seller = $item->lapak?->user;

        $photos = $item->photos->map(fn (ListingPhoto $p) => [
            'id' => $p->id,
            'url' => $p->path,
        ])->values()->all();

        $detail = [
            'id' => $item->id,
            'judul' => $item->judul,
            'model' => $item->model,
            'harga' => $item->harga,
            'kondisi' => $item->kondisi,
            'prosesor' => $item->prosesor,
            'ram_gb' => $item->ram_gb,
            'penyimpanan' => $item->penyimpanan,
            'gpu_dedicated' => (bool) $item->gpu_dedicated,
            'tipe_2in1' => (bool) $item->tipe_2in1,
            'berat_kg' => $item->berat_kg !== null ? (string) $item->berat_kg : null,
            'deskripsi' => $item->deskripsi,
            'brand_name' => $item->brand?->nama,
            'category_name' => $item->category?->nama,
            'photos' => $photos,
            'seller' => [
                'name' => $seller ? $seller->name : 'Mahasiswa PNP',
                'status_mahasiswa' => $seller ? $seller->status_mahasiswa : 'aktif',
                'status_verifikasi' => $seller ? $seller->status_verifikasi : 'disetujui',
            ],
            'created_at' => $item->created_at?->diffForHumans(),
            'published_date' => $item->created_at?->format('d M Y'),
        ];

        return Inertia::render('Catalog/Show', [
            'listing' => $detail,
        ]);
    }
}
