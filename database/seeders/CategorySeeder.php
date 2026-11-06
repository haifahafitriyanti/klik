<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Berdasarkan PRD v0.6 Bagian 9.2:
     * Lima kategori tipe fisik beserta aturan prioritas dan kriteria terukur.
     */
    public function run(): void
    {
        $categories = [
            [
                'nama' => 'MacBook',
                'prioritas' => 1,
                'kriteria' => 'Merek Apple',
            ],
            [
                'nama' => 'Laptop Gaming',
                'prioritas' => 2,
                'kriteria' => 'Memiliki kartu grafis dedicated',
            ],
            [
                'nama' => 'Laptop 2-in-1',
                'prioritas' => 3,
                'kriteria' => 'Tipe 2-in-1 (engsel 360° atau layar dapat dilepas)',
            ],
            [
                'nama' => 'Ultrabook',
                'prioritas' => 4,
                'kriteria' => 'Berat di bawah batas yang ditetapkan (acuan awal: di bawah 1,5 kg)',
            ],
            [
                'nama' => 'Laptop Standar',
                'prioritas' => 5,
                'kriteria' => 'Tidak memenuhi kriteria lain',
            ],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['nama' => $cat['nama']],
                [
                    'prioritas' => $cat['prioritas'],
                    'kriteria' => $cat['kriteria'],
                ]
            );
        }
    }
}
