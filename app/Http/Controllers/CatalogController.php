<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Listing;
use App\Models\ListingPhoto;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    /**
     * Tampilkan halaman utama katalog laptop marketplace KLiK (Fase 4.2).
     */
    public function index(Request $request): Response
    {
        // 1. Ambil seluruh kategori untuk referensi klasifikasi laptop
        $categories = Category::query()
            ->orderBy('prioritas')
            ->get(['id', 'nama', 'prioritas', 'kriteria'])
            ->map(fn (Category $cat) => [
                'id' => $cat->id,
                'nama' => $cat->nama,
                'prioritas' => $cat->prioritas,
                'kriteria' => $cat->kriteria,
            ]);

        // 2. Ambil data listing publik yang sah dengan pagination server-side (12 item per halaman)
        $listings = Listing::query()
            ->forPublicCatalog()
            ->with([
                'category:id,nama',
                'brand:id,nama',
                'photos:id,listing_id,path',
            ])
            ->latest('created_at')
            ->orderBy('id', 'desc')
            ->paginate(12)
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

        return Inertia::render('Catalog/Index', [
            'categories' => $categories,
            'listings' => $listings,
        ]);
    }
}
