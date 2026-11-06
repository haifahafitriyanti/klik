<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Merek laptop umum untuk filter dan katalog KLiK (idempoten).
     */
    public function run(): void
    {
        $brands = [
            'Lenovo',
            'ASUS',
            'Acer',
            'HP',
            'Dell',
            'Apple',
            'MSI',
            'Axioo',
            'Toshiba',
        ];

        foreach ($brands as $nama) {
            Brand::firstOrCreate(['nama' => $nama]);
        }
    }
}
