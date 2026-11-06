<?php

namespace Database\Seeders;

use App\Models\Jurusan;
use Illuminate\Database\Seeder;

class JurusanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Idempoten: Menggunakan firstOrCreate agar aman dijalankan berulang kali.
     */
    public function run(): void
    {
        $jurusans = [
            'Teknik Mesin',
            'Teknik Sipil',
            'Teknik Elektro',
            'Administrasi Niaga',
            'Akuntansi',
            'Teknologi Informasi',
            'Bahasa Inggris',
        ];

        foreach ($jurusans as $nama) {
            Jurusan::firstOrCreate(['nama' => $nama]);
        }
    }
}
