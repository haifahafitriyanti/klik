<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Berdasarkan PRD v0.6 Bagian 9.3:
     * - 3 bulan: 90 hari, Rp40.000 (sekitar Rp444/hari)
     * - 5 bulan: 150 hari, Rp65.000 (sekitar Rp433/hari)
     * - Custom: Rp4.000 per minggu, durasi 1 sampai 52 minggu.
     *   Paket custom tidak menyimpan harga atau durasi tetap yang menyesatkan
     *   karena nominal dan durasi dihitung dinamis saat checkout.
     */
    public function run(): void
    {
        $plans = [
            [
                'nama' => '3 Bulan',
                'is_custom' => false,
                'durasi_hari' => 90,
                'harga' => 40000,
                'harga_per_minggu' => null,
                'is_active' => true,
            ],
            [
                'nama' => '5 Bulan',
                'is_custom' => false,
                'durasi_hari' => 150,
                'harga' => 65000,
                'harga_per_minggu' => null,
                'is_active' => true,
            ],
            [
                'nama' => 'Custom',
                'is_custom' => true,
                'durasi_hari' => null,
                'harga' => null,
                'harga_per_minggu' => 4000,
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(
                ['nama' => $plan['nama']],
                $plan
            );
        }
    }
}
