<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Listing;
use App\Models\ListingPhoto;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Tampilkan halaman utama (homepage) marketplace KLiK.
     */
    public function index(Request $request): Response
    {
        // 1. Ambil lima kategori laptop berdasarkan prioritas tipe fisik di PRD
        $categories = Category::query()
            ->orderBy('prioritas')
            ->get(['id', 'nama', 'prioritas', 'kriteria'])
            ->map(fn (Category $cat) => [
                'id' => $cat->id,
                'nama' => $cat->nama,
                'prioritas' => $cat->prioritas,
                'kriteria' => $cat->kriteria,
            ]);

        // 2. Ambil preview listing laptop terbaru yang sah untuk dipublikasikan
        $latestListings = Listing::query()
            ->forPublicCatalog()
            ->with([
                'category:id,nama',
                'brand:id,nama',
                'photos:id,listing_id,path',
            ])
            ->latest('created_at')
            ->orderBy('id', 'desc')
            ->take(8)
            ->get()
            ->map(function (Listing $listing) {
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
            });

        return Inertia::render('Welcome', [
            'categories' => $categories,
            'latestListings' => $latestListings,
        ]);
    }
}
