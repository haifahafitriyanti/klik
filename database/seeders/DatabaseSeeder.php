<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Hanya memasukkan data master KLiK (Jurusan, Kode Prodi, Paket, Brand, Kategori)
     * secara idempoten tanpa menyisipkan data transaksi atau listing dummy.
     */
    public function run(): void
    {
        $this->call([
            JurusanSeeder::class,
            ProdiCodeSeeder::class,
            PlanSeeder::class,
            BrandSeeder::class,
            CategorySeeder::class,
        ]);
    }
}
