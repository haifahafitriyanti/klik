<?php

namespace Database\Seeders;

use App\Models\Jurusan;
use App\Models\ProdiCode;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProdiCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Berdasarkan PRD v0.6 Lampiran A (Kode prodi pada digit 3-6 NIM PNP).
     *
     * CATATAN DATA BELUM TERVERIFIKASI KE KAMPUS (sesuai PRD Lampiran A):
     * - D4 Teknologi Rekayasa Mekatronika: Kode belum diketahui (TIDAK dimasukkan
     *   ke seeder ini agar tidak mengarang kode palsu; mahasiswa dengan NIM prodi ini
     *   akan ditangani oleh mekanisme kode_prodi_tidak_dikenal sesuai FR-02).
     * - Kode D3 yang tidak tercatat (0111, 0113-0115) belum tersedia di dokumen acuan.
     */
    public function run(): void
    {
        // 1. Dapatkan akun administrator yang sah untuk memenuhi foreign key audit created_by.
        // Jika akun admin aktif sudah ada di database, gunakan akun tersebut.
        // Jika database masih kosong, buat akun jangkar sistem internal non-loginable (suspended, password acak 64-karakter).
        $creator = User::where('role', 'admin')->where('is_suspended', false)->first()
            ?? User::where('email', 'system.internal@klik.local')->first()
            ?? User::create([
                'name' => 'System Migration Anchor',
                'email' => 'system.internal@klik.local',
                'password' => Hash::make(Str::random(64)),
                'username' => 'system_anchor',
                'role' => 'admin',
                'status_verifikasi' => 'disetujui',
                'status_mahasiswa' => 'aktif',
                'is_suspended' => true, // Dikunci permanen agar akun jangkar audit ini tidak dapat digunakan untuk login
                'email_verified_at' => null,
            ]);

        // 2. Daftar kode program studi resmi PNP per PRD Lampiran A
        $data = [
            // Teknik Mesin
            [
                'jurusan' => 'Teknik Mesin',
                'kode' => '0101',
                'jenjang' => 'D3',
                'nama_prodi' => 'D3 Teknik Mesin',
            ],
            [
                'jurusan' => 'Teknik Mesin',
                'kode' => '0110',
                'jenjang' => 'D3',
                'nama_prodi' => 'D3 Teknik Alat Berat',
            ],
            [
                'jurusan' => 'Teknik Mesin',
                'kode' => '1104',
                'jenjang' => 'D4',
                'nama_prodi' => 'D4 Teknik Manufaktur',
            ],
            [
                'jurusan' => 'Teknik Mesin',
                'kode' => '1112',
                'jenjang' => 'D4',
                'nama_prodi' => 'D4 Rekayasa Perancangan Mekanik',
            ],

            // Teknik Sipil
            [
                'jurusan' => 'Teknik Sipil',
                'kode' => '0102',
                'jenjang' => 'D3',
                'nama_prodi' => 'D3 Teknik Sipil',
            ],
            [
                'jurusan' => 'Teknik Sipil',
                'kode' => '1103',
                'jenjang' => 'D4',
                'nama_prodi' => 'D4 Teknik Perencanaan Irigasi dan Rawa',
            ],
            [
                'jurusan' => 'Teknik Sipil',
                'kode' => '1106',
                'jenjang' => 'D4',
                'nama_prodi' => 'D4 Manajemen Rekayasa Konstruksi',
            ],
            [
                'jurusan' => 'Teknik Sipil',
                'kode' => '1105',
                'jenjang' => 'D4',
                'nama_prodi' => 'D4 Perencanaan Jalan dan Jembatan',
            ],

            // Teknik Elektro
            [
                'jurusan' => 'Teknik Elektro',
                'kode' => '0103',
                'jenjang' => 'D3',
                'nama_prodi' => 'D3 Teknik Listrik',
            ],
            [
                'jurusan' => 'Teknik Elektro',
                'kode' => '0104',
                'jenjang' => 'D3',
                'nama_prodi' => 'D3 Teknik Elektronika',
            ],
            [
                'jurusan' => 'Teknik Elektro',
                'kode' => '0105',
                'jenjang' => 'D3',
                'nama_prodi' => 'D3 Teknik Telekomunikasi',
            ],
            [
                'jurusan' => 'Teknik Elektro',
                'kode' => '1101',
                'jenjang' => 'D4',
                'nama_prodi' => 'D4 Teknik Elektronika',
            ],
            [
                'jurusan' => 'Teknik Elektro',
                'kode' => '1107',
                'jenjang' => 'D4',
                'nama_prodi' => 'D4 Teknik Telekomunikasi',
            ],
            [
                'jurusan' => 'Teknik Elektro',
                'kode' => '1109',
                'jenjang' => 'D4',
                'nama_prodi' => 'D4 Teknologi Rekayasa Instalasi Listrik',
            ],

            // Administrasi Niaga
            [
                'jurusan' => 'Administrasi Niaga',
                'kode' => '0106',
                'jenjang' => 'D3',
                'nama_prodi' => 'D3 Administrasi Bisnis',
            ],
            [
                'jurusan' => 'Administrasi Niaga',
                'kode' => '1113',
                'jenjang' => 'D4',
                'nama_prodi' => 'D4 Usaha Perjalanan Wisata',
            ],
            [
                'jurusan' => 'Administrasi Niaga',
                'kode' => '1111',
                'jenjang' => 'D4',
                'nama_prodi' => 'D4 Destinasi Pariwisata',
            ],
            [
                'jurusan' => 'Administrasi Niaga',
                'kode' => '1114',
                'jenjang' => 'D4',
                'nama_prodi' => 'D4 Bisnis Digital',
            ],
            [
                'jurusan' => 'Administrasi Niaga',
                'kode' => '1115',
                'jenjang' => 'D4',
                'nama_prodi' => 'D4 Logistik Perdagangan Internasional',
            ],

            // Akuntansi
            [
                'jurusan' => 'Akuntansi',
                'kode' => '0107',
                'jenjang' => 'D3',
                'nama_prodi' => 'D3 Akuntansi',
            ],
            [
                'jurusan' => 'Akuntansi',
                'kode' => '1102',
                'jenjang' => 'D4',
                'nama_prodi' => 'D4 Akuntansi',
            ],

            // Teknologi Informasi
            [
                'jurusan' => 'Teknologi Informasi',
                'kode' => '0108',
                'jenjang' => 'D3',
                'nama_prodi' => 'D3 Teknik Komputer',
            ],
            [
                'jurusan' => 'Teknologi Informasi',
                'kode' => '0109',
                'jenjang' => 'D3',
                'nama_prodi' => 'D3 Manajemen Informatika',
            ],
            [
                'jurusan' => 'Teknologi Informasi',
                'kode' => '0116',
                'jenjang' => 'D3',
                'nama_prodi' => 'D3 Sistem Informasi',
            ],
            [
                'jurusan' => 'Teknologi Informasi',
                'kode' => '1108',
                'jenjang' => 'D4',
                'nama_prodi' => 'D4 Teknologi Rekayasa Perangkat Lunak',
            ],
            [
                'jurusan' => 'Teknologi Informasi',
                'kode' => '1116',
                'jenjang' => 'D4',
                'nama_prodi' => 'D4 Animasi',
            ],

            // Bahasa Inggris
            [
                'jurusan' => 'Bahasa Inggris',
                'kode' => '0112',
                'jenjang' => 'D3',
                'nama_prodi' => 'D3 Bahasa Inggris',
            ],
            [
                'jurusan' => 'Bahasa Inggris',
                'kode' => '1110',
                'jenjang' => 'D4',
                'nama_prodi' => 'D4 Bahasa Inggris untuk Komunikasi Bisnis dan Profesional',
            ],
        ];

        // 3. Masukkan secara idempoten tanpa menimpa created_by asli jika data sudah ada
        foreach ($data as $item) {
            $jurusan = Jurusan::where('nama', $item['jurusan'])->firstOrFail();

            $prodi = ProdiCode::firstOrNew(['kode' => $item['kode']]);
            $prodi->jurusan_id = $jurusan->id;
            $prodi->jenjang = $item['jenjang'];
            $prodi->nama_prodi = $item['nama_prodi'];
            if (! $prodi->exists) {
                $prodi->created_by = $creator->id;
            }
            $prodi->save();
        }
    }
}
