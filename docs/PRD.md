# PRD: KLiK — Marketplace Laptop Bekas Internal Kampus PNP

**Baseline implementasi: PRD v0.6 FINAL (sinkron ERD v6).** Empat keputusan implementasi dikonfirmasi pemilik pada 4 November 2026: Inertia + Vue 3; login NIM dan kata sandi; verifikasi ulang pada akun ditolak yang sama; dan satu laporan aktif per (listing, pelapor) wajib dijaga di MySQL serta Laravel. **FINAL** berarti keputusan tersebut terkunci untuk baseline migration, bukan seluruh usulan P1/P2 telah disetujui.


| | |
|---|---|
| **Nama produk** | KLiK |
| **Jenis** | Website marketplace laptop bekas, khusus mahasiswa Politeknik Negeri Padang (PNP) |
| **Konteks** | Proyek gabungan mata kuliah Kewirausahaan dan E-Commerce |
| **Pemilik** | Aifa, D3 Manajemen Informatika |
| **Versi** | 0.6 FINAL (baseline migration), diselaraskan dengan ERD v6 |
| **Tanggal** | 4 November 2026 |
| **Pengguna dokumen** | Penulis proyek dan dosen penilai |

**Cara membaca dokumen ini.** Setiap aturan diberi label:
- **[Keputusan]**: sudah diputuskan oleh pemilik proyek. Dipertahankan kecuali keputusan baru yang eksplisit.
- **[Usulan]**: usulan yang belum dikonfirmasi pemilik proyek. Setujui atau ubah.
- **[Terbuka]**: belum diputuskan, daftar lengkap ada di Bagian 14.
- **[Isi]**: bagian yang menunggu data dari pemilik proyek (misalnya hasil survei). Sengaja dikosongkan karena datanya tidak ada di dokumen ini dan tidak boleh dikarang.
- **Δ** di samping ID atau judul: baru atau diubah dibanding versi awal. Ringkasan ada di Bagian 0.

Prioritas fitur: **P0** wajib ada saat demo, **P1** sebaiknya ada, **P2** ditunda ke pengembangan lanjutan.

---

## 0. Ringkasan perubahan dan status keputusan (v0.6)

**Riwayat revisi v0.5:** memperbaiki konflik NIM unik dengan daftar ulang, mengamankan checkout dan webhook, menegakkan satu listing aktif per lapak, menyelaraskan prioritas P0/P1, dan menggambar ulang ERD v5. Keputusan baru yang belum dikonfirmasi pemilik tetap diberi **[Usulan]**. Keputusan terdahulu tidak dihapus secara diam-diam; bila perlu penafsiran teknis baru, alasannya dicatat.

### Keputusan final v0.6

| Item | Keputusan terkonfirmasi | Implementasi |
|---|---|---|
| UI | Laravel + Inertia + **Vue 3** | Komponen Vue 3 dan starter kit yang kompatibel |
| Autentikasi | **NIM + kata sandi** | `users.nim` unik sebagai identifier login; username nama tampilan |
| Verifikasi ulang | **Akun ditolak diperbaiki lalu diverifikasi ulang, bukan dibuat ulang** | NIM tetap unik; akses terbatas hanya jalur pengajuan ulang |
| Laporan | **Satu laporan aktif per listing per pelapor** | Generated column `active_reporter_id` + UNIQUE (`listing_id`, `active_reporter_id`) di MySQL, serta validasi Laravel dan transaksi database |

### Riwayat historis v0.4 (bukan prioritas terbaru)

Semua perubahan berikut berstatus **[Usulan]** kecuali dinyatakan lain. Tidak ada [Keputusan] dari v0.3 yang saya ubah.

| No | Perubahan | Lokasi | Alasan (temuan review) |
|---|---|---|---|
| 1 | Kerangka klien Inertia dan cara login ditandai **blocker** dengan tenggat. Login diusulkan memakai NIM | 11, 14 | Keduanya dibutuhkan Minggu 4, padahal masih terbuka |
| 2 | Proses berbasis waktu diberi mekanisme (perintah artisan idempoten, dipicu scheduler saat demo dan saat halaman diakses). Non-goal "penjadwal otomatis" dipersempit menjadi "penjadwal produksi" | 4, 8.9 (FR-80 s/d FR-84), FR-15 | Banyak FR bergantung pada waktu, tetapi non-goal melarang penjadwal |
| 3 | Admin dapat menampilkan kembali listing (FR-59, P0). Kriteria moderasi didefinisikan (9.5). Pemutus banding dibedakan dari pelaku hide bila memungkinkan. Ada versi minimal moderasi bila banding dipotong | 8.6, 9.5, 13 | Tanpa ini hide P0 tidak punya jalan keluar, dan kriteria moderasi belum ada |
| 4 | Pembayaran yang masuk setelah transaksi dibatalkan atau kedaluwarsa ditandai untuk tinjauan manual (FR-20b). Pembatalan dari sisi Midtrans dicoba bila memungkinkan. Klaim soal webhook kedaluwarsa dikoreksi dan diverifikasi lewat uji sandbox | 8.3, 10.1, 12 | Celah: QRIS dibayar setelah jendela ditutup |
| 5 | Jadwal: ditambah jalur paralel Kewirausahaan, titik cek uji alur P0, dan urutan pemotongan fitur yang diperbarui | 13 | Pekerjaan Kewirausahaan belum terjadwal, pengujian hanya di akhir |
| 6 | Katalog dan detail listing dapat dilihat tanpa login. Tombol WhatsApp wajib login. Alasannya ada di FR-11 dan FR-40 | 5, 7, 8.2, 8.5, 14 | Alur di Bagian 7 dan FR-11 saling bertentangan |
| 7 | Tabel rujukan data survei (kosong, menunggu data kamu). Tujuan "memastikan mahasiswa" diperlunak. Ukuran keberhasilan dipisah menjadi ukuran tugas dan ukuran hasil | 2, 3 | Klaim masalah dan harga belum berdata |
| 8 | Tabel ekonomi per lapak dan biaya gateway. FR-26 (batasi ke QRIS dan e-wallet) naik ke P0. Risiko biaya gateway ditulis ulang | 9.3.1, 8.3, 15 | Virtual account flat dilaporkan Rp4.000, setara 10% harga paket 3 bulan |
| 9 | Fitur lapor listing dari pembeli (FR-44, P1) dan konfirmasi ketersediaan berkala (FR-37, P2) | 8.4, 8.5 | Status terjual hanya bergantung pada penjual, listing basi bisa tayang sampai 1 tahun |
| 10 | NIM dengan kode prodi yang tidak ada di tabel tidak ditolak otomatis, melainkan diteruskan ke admin. Alur verifikasi alumni diusulkan | 8.1, 9.1 | Tabel kode prodi punya celah, bisa menolak mahasiswa asli saat demo |
| 11 | Perubahan historis v0.4: FR-34 dipisahkan dari FR-38 (saat itu P1), lalu **FR-38 dinaikkan menjadi P0 dalam v0.5**; perubahan lain meliputi FR-13, FR-27 dan skenario uji | 8.x, 9.4, Lampiran B | Catatan riwayat, bukan penetapan prioritas v0.6 |
| 12 | Keamanan dan keselamatan: pembatasan percobaan login, nomor WhatsApp hanya tampil ke pengguna login, saran titik temu aman | 8.5, 10.1 | Celah yang belum tercakup |

---

## 1. Ringkasan

KLiK adalah website khusus mahasiswa PNP untuk menjual dan mencari laptop bekas. Penjual menyewa **lapak** (slot untuk satu laptop dengan masa aktif tertentu) dengan membayar ke platform lewat Midtrans (mode sandbox). Pembeli menelusuri katalog terstruktur dengan filter, lalu menghubungi penjual lewat tombol yang mengarah ke WhatsApp penjual. Transaksi antarpengguna dilakukan **COD di kampus**: pembeli memeriksa barang dulu, baru membayar. Platform tidak memegang uang transaksi antarpengguna. Pendapatan platform berasal dari penjualan lapak.

Rencana pengerjaan 9 minggu. Proyek hanya akan didemokan kepada dosen, bukan dipakai pengguna nyata.

## 2. Latar belakang dan masalah Δ

Jual-beli laptop bekas antarmahasiswa saat ini berlangsung lewat pesan siaran di grup WhatsApp angkatan atau kampus. Masalah yang diidentifikasi:

1. **Postingan cepat tenggelam** oleh obrolan lain, sehingga pembeli sulit menemukan laptop dengan spesifikasi tertentu tanpa bertanya satu per satu.
2. **Tidak ada struktur data**: spesifikasi, kondisi, dan harga tertulis bebas, sulit dibandingkan.
3. **Rawan penipuan atau barang tidak sesuai deskripsi**, karena tidak ada verifikasi identitas dan tidak ada pihak penengah.
4. **Status tidak jelas**: tidak diketahui apakah barang masih tersedia atau sudah terjual.

Survei pengalaman (Google Form) sudah dilakukan pada Minggu 1. **Klaim di atas belum dirujuk ke data survei.** Tabel berikut harus diisi dari hasil survei sebelum dokumen ini dipakai untuk laporan. Tanpa angka ini, masalah dan harga lapak belum punya dasar.

| Klaim | Data pendukung dari survei | Status |
|---|---|---|
| Postingan tenggelam, sulit menemukan spesifikasi tertentu | **[Isi]** misalnya persentase responden yang pernah mencari laptop bekas lewat grup dan kesulitan menemukannya | Menunggu data |
| Informasi tidak terstruktur | **[Isi]** | Menunggu data |
| Pernah mengalami atau mendengar penipuan / barang tidak sesuai | **[Isi]** | Menunggu data |
| Status ketersediaan tidak jelas | **[Isi]** | Menunggu data |
| Jumlah responden dan profilnya (angkatan, jurusan) | **[Isi]** | Menunggu data |
| Pernah atau berniat menjual laptop bekas, dan kesediaan membayar sewa lapak pada harga Rp40.000 per 3 bulan | **[Isi]** | Menunggu data |

## 3. Tujuan dan ukuran keberhasilan Δ

### 3.1 Tujuan produk
- Memberi katalog laptop bekas yang terstruktur dan dapat difilter.
- **Mengurangi kemungkinan** non-mahasiswa PNP masuk lewat validasi NIM dan KTM. Ini bukan jaminan identitas: NIM dapat ditebak dan KTM dapat dipalsukan (lihat 9.1).
- Mengurangi risiko barang tidak sesuai lewat inspeksi langsung sebelum pembayaran (COD).
- Menunjukkan model bisnis yang berjalan: pendapatan dari sewa lapak.

### 3.2 Ukuran keberhasilan

**A. Ukuran penyelesaian tugas (konteks tugas kuliah)**
- Alur inti berjalan end-to-end saat demo: registrasi, validasi, beli lapak, listing tayang, pencarian, kontak WhatsApp, moderasi.
- Seluruh skenario uji P0 (Lampiran B) lulus pada fase pengujian.
- Proyeksi keuangan memakai tarif gateway nyata, dengan skenario konservatif, dan memasukkan biaya tenaga admin untuk verifikasi manual **[Usulan]**.

**B. Ukuran hasil (apakah produk menjawab masalah di Bagian 2) [Usulan]**

| Ukuran | Cara mengukur | Target |
|---|---|---|
| Calon pengguna dapat mendaftar, memasang listing, dan menemukan laptop tanpa bantuan | Uji coba dengan calon pengguna, hitung persentase yang menyelesaikan tiap tugas | **[Terbuka]**, ditetapkan setelah survei dianalisis |
| Waktu menemukan laptop dengan spesifikasi tertentu | Bandingkan dengan mencari lewat grup WhatsApp pada tugas yang sama | **[Terbuka]** |
| Kesediaan membayar harga lapak | Persentase responden survei yang bersedia pada harga paket | **[Isi]** dari survei |

## 4. Di luar lingkup (non-goals) Δ

| Tidak dibuat | Alasan |
|---|---|
| Escrow dan penahanan dana pembeli | Diganti COD **[Keputusan]** |
| Refund antarpengguna dan sengketa berbasis dana | Tidak ada uang antarpengguna di platform |
| Pengajuan beli atau pesanan di dalam aplikasi | Kontak lewat tombol WhatsApp **[Keputusan]** |
| Perpanjangan masa sewa | Lapak maksimal 1 tahun, lebih dari itu beli lapak baru **[Keputusan]** |
| Aplikasi mobile (Flutter) | Produk berbentuk website |
| Notifikasi email | Notifikasi cukup di dalam website **[Keputusan]** |
| Verifikasi email saat registrasi | Validasi lewat NIM dan KTM |
| Chat di dalam aplikasi | Kontak lewat WhatsApp |
| Akun Midtrans produksi | Pengembangan lanjutan |
| Penjadwal produksi (cron di server hosting) | Proses berbasis waktu tetap ada lewat FR-80 s/d FR-84 (perintah artisan dan evaluasi saat halaman diakses). Yang ditunda hanya pemasangan cron di server produksi **[Usulan]** |
| Pengembalian dana otomatis untuk pembayaran terlambat | Pembayaran yang masuk setelah transaksi batal ditandai dan ditangani manual lewat dashboard Midtrans (FR-20b) **[Usulan]** |

## 5. Pengguna dan peran Δ

| Peran | Siapa | Kemampuan utama |
|---|---|---|
| **Pengunjung** | Siapa pun yang belum login **[Usulan]** | Melihat katalog dan halaman detail listing. Tidak melihat nomor WhatsApp, tidak dapat menghubungi penjual |
| **Pembeli** | Mahasiswa atau alumni PNP yang sudah divalidasi | Semua kemampuan pengunjung, ditambah menghubungi penjual lewat WhatsApp dan melaporkan listing |
| **Penjual (penyewa lapak)** | Pengguna yang sudah membeli lapak | Semua kemampuan pembeli, ditambah mengelola lapak dan listing sendiri |
| **Admin** | Pengelola platform | Validasi akun, moderasi (sembunyikan dan tampilkan kembali) listing, kelola paket dan kategori, lihat transaksi keuangan |

Satu akun dapat menjadi pembeli sekaligus penjual. Peran admin dibuat lewat seeder, bukan lewat registrasi **[Usulan]**.

## 6. Konsep inti

- **Lapak**: slot sewa untuk **satu laptop** selama masa paket **[Keputusan]**. Jika laptop terjual sebelum masa habis, penjual boleh mengganti dengan laptop baru pada lapak yang sama **[Keputusan]**.
- **Paket sewa lapak**: tiga paket, yaitu **3 bulan, 5 bulan, dan custom** **[Keputusan]**. Paket prabayar dengan masa aktif, bukan langganan otomatis. Menambah lapak berarti membeli lapak baru **[Keputusan]**.
- **Batas masa lapak**: maksimal 1 tahun. Lebih dari itu, pengguna harus membeli lapak baru, tidak ada perpanjangan **[Keputusan]**.
- **Kategori**: lima kategori tipe fisik **[Keputusan]**: Laptop Standar, Ultrabook, Laptop 2-in-1, Laptop Gaming, MacBook. Detail di bagian 9.2.
- **Listing**: data laptop yang dijual (spesifikasi, kondisi, harga, foto, nomor WhatsApp).

## 7. Alur pengguna utama Δ

1. **Daftar:** pengguna mengisi form registrasi dan mengunggah KTM. Sistem mengecek NIM otomatis, lalu admin mencocokkan KTM. Akun aktif setelah disetujui, login hanya bisa setelah itu **[Keputusan]**.
2. **Menjelajah:** siapa pun dapat melihat katalog dengan filter dan halaman detail tanpa login. Menghubungi penjual membutuhkan login dengan akun yang sudah disetujui **[Usulan]**.
3. **Menyewa lapak:** penjual memilih paket di landing page, mengisi form penjualan, lalu membayar lewat Midtrans. Setelah pembayaran terkonfirmasi, lapak dibuat aktif dan listing tayang.
4. **Transaksi:** pembeli menekan tombol WhatsApp di halaman detail, bertemu penjual di kampus, memeriksa laptop, dan membayar langsung bila sesuai. Penjual menandai terjual.
5. **Moderasi:** admin menyembunyikan listing yang tidak sesuai kriteria (9.5). Penjual diberi tahu beserta alasan dan dapat mengajukan banding. Admin juga dapat menampilkan kembali listing.

Diagram rinci tersedia di dokumen terpisah: flowchart KLiK (alur utama, serta alur moderasi dan banding). Flowchart perlu diperbarui untuk langkah 2 (katalog publik) dan penampilan kembali listing oleh admin.

## 8. Kebutuhan fungsional

### 8.1 Registrasi dan validasi mahasiswa Δ

| ID | Kebutuhan | Prioritas |
|---|---|---|
| FR-01 | Form registrasi memuat nama, username, kata sandi, NIM, prodi, jurusan, dan unggah foto KTM **[Keputusan]**. Kolom jenis kelamin dihapus **[Keputusan]**, dan sudah tidak ada di ERD v6 | P0 |
| FR-02 Δ | Sistem memeriksa NIM otomatis sebelum menerima pendaftaran (lihat 9.1). NIM yang jelas salah ditolak. NIM dengan kode prodi yang **tidak ada di tabel kode prodi** tidak ditolak otomatis, melainkan diterima sebagai berstatus menunggu dengan penanda "kode prodi tidak dikenal" untuk diperiksa admin **[Usulan]** | P0 |
| FR-03 | Satu NIM hanya dapat dipakai satu akun (kolom NIM unik) | P0 |
| FR-04 | Akun baru berstatus *menunggu* dan belum bisa login | P0 |
| FR-05 | Admin mencocokkan KTM (foto, nama, NIM, prodi) dengan data pendaftar, lalu menyetujui atau menolak dengan alasan. Admin pemutus dan waktunya dicatat | P0 |
| FR-06 Δ | **[Keputusan]** Akun `ditolak` mengajukan ulang verifikasi pada akun yang sama, bukan registrasi NIM baru. Pemilik memakai NIM + kata sandi melalui jalur autentikasi terbatas khusus pengajuan ulang, boleh memperbaiki data dan mengunggah bukti baru, kemudian status kembali `menunggu`. Batasi percobaan; sesi terbatas tidak dapat mengakses fitur pengguna disetujui. Jika lupa kata sandi, pemulihan identitas memerlukan prosedur admin terverifikasi, bukan registrasi akun kedua | P0 |
| FR-07 Δ | Berkas KTM dihapus dari penyimpanan privat saat keputusan admin (disetujui/ditolak), `ktm_path` menjadi NULL. Yang disimpan status, pemutus, waktu, dan alasan. Penghapusan ditangani aman bila file sudah tidak ada, dan diterapkan pula pada pengajuan ulang. **[Keputusan, prioritas dinaikkan P0 untuk privasi]** | P0 |
| FR-08 | Akun dapat ditangguhkan admin (kolom `is_suspended`) | P2 |
| FR-09 Δ | Mahasiswa aktif dan alumni boleh memakai platform **[Keputusan]**. Status mahasiswa dicatat. Alumni yang tidak lagi memiliki KTM berlaku dapat memakai KTM lama atau bukti kelulusan lain yang ditentukan admin, dan admin yang memutuskan **[Usulan, lihat item terbuka #8]**. Titik temu COD tetap di kampus | P0 |

**Kriteria penerimaan:** NIM dengan kode prodi dikenal tetapi tidak cocok dengan prodi pilihan ditolak. Kode yang belum terdaftar diterima dengan penanda untuk admin. NIM unik untuk seluruh akun, termasuk yang ditolak. NIM yang sudah digunakan tidak boleh didaftarkan sebagai akun baru; pemilik akun berstatus ditolak dapat mengajukan ulang verifikasi lewat autentikasi terbatas. Akun belum disetujui tidak dapat login biasa. Berkas KTM dihapus setelah keputusan admin.

### 8.2 Landing page dan katalog Δ

| ID | Kebutuhan | Prioritas |
|---|---|---|
| FR-10 | Navbar berisi tautan "Sewa Lapak" yang menggulir otomatis ke bagian paket di halaman utama **[Keputusan]**. Tautan harus tetap berfungsi dari halaman lain **[Usulan]** | P0 |
| FR-11 Δ | Landing page menampilkan katalog laptop yang sedang tayang. Katalog dan halaman detail dapat dilihat **tanpa login** **[Usulan, lihat item terbuka #9]** | P0 |
| FR-12 | Bagian paket sewa lapak di bawah katalog, berisi tiga kolom: 3 bulan, 5 bulan, custom **[Keputusan]** | P0 |
| FR-13 Δ | Filter P0: kategori, merek, RAM, dan rentang harga. Filter P1: kondisi dan atribut lain, hanya bila tersimpan sebagai data terstruktur (lihat item terbuka #4) **[Usulan]** | P0 |
| FR-14 Δ | Halaman detail listing: spesifikasi, kondisi, harga, foto. Tombol WhatsApp ditampilkan untuk pengguna login. Pengunjung melihat ajakan login sebagai pengganti nomor | P0 |
| FR-15 Δ | Hanya listing berstatus *aktif* yang tampil di katalog. Kueri katalog juga memeriksa bahwa tanggal berakhir lapak belum lewat, sehingga lapak yang masanya habis tidak tampil walau proses FR-82 belum berjalan **[Usulan]** | P0 |

**Kriteria penerimaan (baru):** pengunjung tanpa login dapat membuka katalog dan detail, tetapi tidak melihat nomor WhatsApp. Listing berstatus tersembunyi, terjual, atau kedaluwarsa tidak tampil di katalog. Setiap filter P0 mengurangi hasil sesuai nilainya dan kombinasi filter bekerja bersamaan.

### 8.3 Paket sewa lapak dan pembayaran Δ

| ID | Kebutuhan | Prioritas |
|---|---|---|
| FR-20 Δ | Pengguna memilih paket, mengisi form, menyetujui syarat, lalu checkout. **Tidak ada draft**: saat alur ditinggalkan, pengguna mengisi ulang form bila ingin mencoba lagi **[Keputusan]**. Data form dan path foto sementara berada di `transactions.form_payload`; `lapaks`, `listings`, dan foto permanen baru dibuat setelah webhook pembayaran valid. Batas pembayaran lokal `transactions.expires_at = created_at + 30 menit` **[Keputusan]**. **Menutup jendela** diartikan sebagai membatalkan **alur checkout di UI**, bukan bukti pembatalan transaksi gateway. Server meminta pembatalan ke Midtrans bila transaksi dan metodenya mendukung. Jika tidak terkonfirmasi batal, order lama tetap diawasi dan pembayaran masuk setelah pembatalan lokal masuk tinjauan (FR-20b) **[Usulan implementasi untuk memenuhi niat keputusan]** | P0 |
| FR-20a Δ | Foto sementara dihapus saat transaksi *expire*, *cancel*, atau *failed* **setelah status akhir dipastikan secara lokal dan rekonsiliasi seperlunya**, dan foto yang tidak pernah terhubung ke transaksi dibersihkan setelah 24 jam **[Keputusan]**. Midtrans tidak selalu mengirim notifikasi (misalnya bila pengguna menutup halaman Snap sebelum memilih metode bayar, belum ada transaksi di sisi Midtrans), sehingga sistem juga memeriksa sendiri transaksi *pending* yang melewati batas waktu (FR-80). Perilaku persisnya, termasuk apakah notifikasi *expire* dikirim setelah metode bayar dipilih, diverifikasi lewat uji sandbox pada Minggu 7 **[Usulan]** | P0 |
| FR-20b Δ | **Pembayaran terlambat:** notifikasi status bayar berhasil dari Midtrans untuk transaksi lokal `cancel`/`expire` tidak membuat lapak. Simpan status terakhir gateway di `gateway_status`, tandai `perlu_tinjauan = true` dan isi `review_reason`; tampilkan di daftar transaksi admin P0. Tidak menjanjikan refund otomatis; admin menindaklanjuti sesuai kemampuan dan ketentuan Midtrans di sandbox **[Usulan]** | P0 |
| FR-21 | Pembayaran memakai Midtrans mode sandbox **[Keputusan]** | P0 |
| FR-22 Δ | Lapak aktif hanya dibuat setelah notifikasi server Midtrans yang lolos verifikasi tanda tangan, identitas order, nominal dan status pembayaran; halaman browser tidak dipercaya. Handler berjalan dalam **database transaction**, mengunci baris transaksi, memeriksa status terkini, lalu menciptakan tepat satu lapak dan satu listing; webhook duplikat tidak membuat duplikasi. Callback dari browser bukan sumber kebenaran pembayaran **[Usulan penguatan]** | P0 |
| FR-23 | Harga dan durasi paket disalin ke catatan lapak dan transaksi saat pembelian, sehingga perubahan paket oleh admin tidak memengaruhi pembelian lama **[Keputusan]** | P0 |
| FR-24 | Paket custom: pilih jumlah minggu (1 sampai 52), harga dihitung otomatis dari tarif per minggu, total harga dan tanggal berakhir ditampilkan sebelum bayar **[Keputusan]** | P0 |
| FR-25 | Saat harga custom melebihi harga paket tetap yang durasinya sama atau lebih panjang, tampilkan saran paket tetap yang lebih hemat **[Usulan]** | P1 |
| FR-26 Δ | Pembayaran dibatasi ke QRIS dan e-wallet **[Usulan]**. Naik ke P0 karena menentukan margin: virtual account dilaporkan berbiaya flat Rp4.000 per transaksi (lihat 9.3.1) | P0 |
| FR-27 Δ | Pengguna harus menyetujui syarat sebelum membayar; simpan waktu persetujuan pada **transaksi** (`tc_accepted_at`), lalu salin ke lapak saat pembayaran berhasil. Syarat menyebut konsekuensi sewa saat lapak ditutup. **[Usulan penguatan]** | P0 |
| FR-28 | Mode pembayaran simulasi sebagai cadangan demo **[Usulan]** | P1 |

**Kriteria penerimaan:** pembayaran sandbox valid membuat tepat satu lapak serta satu listing. Webhook dengan tanda tangan/order/nominal tidak valid ditolak. Dua webhook bersamaan atau berulang tidak menghasilkan dua lapak. Order yang telah `cancel`/`expire` tidak membuat lapak walau webhook gateway kemudian menunjukkan berhasil, melainkan masuk tinjauan admin. `expires_at` disimpan dan dibedakan dari status gateway. Batal di browser tidak diasumsikan otomatis batal di Midtrans.

### 8.4 Listing dan dashboard penjual Δ

| ID | Kebutuhan | Prioritas |
|---|---|---|
| FR-30 | Form listing: judul, kategori, merek, model, prosesor, RAM, penyimpanan, GPU dedicated, tipe 2-in-1, berat, kondisi, harga, deskripsi, nomor WhatsApp, foto | P0 |
| FR-31 | Sistem memeriksa kategori yang dipilih terhadap spesifikasi (misal Gaming wajib punya GPU dedicated) **[Usulan]** | P1 |
| FR-32 | Dashboard menampilkan keadaan kosong ("belum mengajukan penjualan") bagi pengguna tanpa lapak **[Keputusan]** | P0 |
| FR-33 | Dashboard penjual hanya menampilkan lapak dan listing milik sendiri, dapat mengedit listing **[Keputusan]** | P0 |
| FR-34 Δ | Satu lapak paling banyak memiliki satu listing `aktif` sekaligus **[Keputusan]**. MySQL menegakkan aturan dengan generated column `active_lapak_id = IF(status = 'aktif', lapak_id, NULL)` serta UNIQUE INDEX pada kolom ini; service Laravel juga memvalidasi di dalam transaksi. Listing baru hanya boleh diaktifkan ketika lapak aktif dan listing sebelumnya terjual **[Usulan implementasi]** | P0 |
| FR-35 Δ | Setelah masa sewa habis, lapak berstatus *habis*, listing berstatus *kedaluwarsa*, dan riwayat tetap tampil dengan tombol untuk membeli lapak baru **[Usulan]** | P1 |
| FR-36 | Pengingat 5 hari sebelum masa sewa habis sebagai notifikasi di dalam website, dengan penanda agar tidak terkirim ganda **[Keputusan 5 hari, mekanisme Usulan]** | P1 |
| FR-37 Δ | Konfirmasi ketersediaan berkala: penjual diminta mengonfirmasi bahwa laptop masih tersedia lewat notifikasi di dalam website. Interval dan tindak lanjut bila tidak dijawab **[Terbuka]** | P2 |
| FR-38 Δ | Penjual dapat mengganti laptop pada lapak yang sama setelah listing sebelumnya `terjual`, selama lapak masih `aktif` dan belum kedaluwarsa. Tidak boleh membuat listing pengganti ketika lapak `jeda`/`ditutup` atau listing sebelumnya `tersembunyi`. **[Keputusan ganti laptop; kondisi teknis Usulan]** | P0 |

**Kriteria penerimaan:** penjual A tidak bisa mengelola data penjual B. Upaya membuat listing aktif kedua ditolak meski dua permintaan datang bersamaan. Setelah listing pertama terjual, penjual dapat membuat pengganti tanpa membeli lapak baru bila masa aktif tersisa. Lapak jeda/ditutup/habis tidak bisa menerima listing pengganti; penjual tanpa lapak melihat keadaan kosong.

### 8.5 Kontak dan transaksi COD Δ

| ID | Kebutuhan | Prioritas |
|---|---|---|
| FR-40 Δ | Halaman detail listing memiliki tombol yang mengarah ke WhatsApp penjual **[Keputusan]**. Tombol hanya aktif untuk pengguna login dengan akun disetujui, dan pengunjung diarahkan ke login **[Usulan]**. Klik tombol dicatat (siapa pembeli, listing mana, kapan) | P0 |
| FR-41 Δ | Serah-terima dan pembayaran terjadi langsung di kampus: pembeli memeriksa laptop terlebih dahulu **[Keputusan]**. Halaman detail menampilkan saran keselamatan: bertemu di area publik kampus, memeriksa laptop sebelum membayar **[Usulan]** | P0 |
| FR-42 | Penjual menandai listing terjual (waktu terjual dicatat) | P0 |
| FR-43 | Ulasan penjual dan pembeli setelah transaksi | P2 |
| FR-44 Δ | **Lapor listing [Keputusan constraint].** Pembeli disetujui dapat melaporkan listing dengan alasan: *tidak tersedia/sudah terjual*, *tidak sesuai deskripsi*, atau *mencurigakan*. Laporan masuk antrean admin P1. Status laporan `menunggu` atau `ditinjau` berarti **aktif**; `selesai` berarti tidak aktif. Hanya satu laporan aktif per (`listing_id`, `reporter_id`), ditegakkan oleh generated column dan UNIQUE index di MySQL **serta** validasi Laravel. Setelah laporan `selesai`, pengguna boleh membuat laporan baru untuk listing yang sama | P1 |

Karena kontak terjadi di luar aplikasi (WhatsApp), platform tidak mengetahui apakah transaksi benar-benar terjadi. Status *terjual* bergantung pada penjual menandainya. Penjual yang sudah menjual dan tidak punya laptop kedua tidak mendapat apa pun dengan menandai terjual, sehingga listing bisa tetap tayang sampai masa sewa habis. FR-44 dan FR-37 mengurangi, tetapi tidak menghilangkan, keterbatasan ini. Catat di laporan.

### 8.6 Moderasi dan banding Δ

| ID | Kebutuhan | Prioritas |
|---|---|---|
| FR-50 Δ | Admin hanya dapat **menyembunyikan** (hide) listing yang melanggar kriteria moderasi (9.5), tidak mengedit atau menghapus **[Keputusan]**. Admin memilih kode alasan dari 9.5 dan dapat menambah catatan | P0 |
| FR-51 | Penjual menerima notifikasi di dalam website berisi alasan hide **[Keputusan]** | P0 |
| FR-52 Δ | Masa sewa berhenti ketika listing **aktif** di-hide sampai dipulihkan **[Keputusan]**. Lapak menjadi `jeda`, `paused_at` dicatat; saat restore, `expires_at` digeser sebesar durasi jeda tepat satu kali dalam database transaction. Listing `terjual`/`kedaluwarsa` tidak dapat di-hide dalam alur P0; saat jeda, penjual boleh mengedit listing tersembunyi tetapi tidak membuat listing baru atau mengaktifkan sendiri **[Usulan batasan]** | P0 |
| FR-53 | Penjual dapat mengajukan banding setelah memperbaiki listing atau memberi penjelasan **[Keputusan, syarat perbaikan Usulan]** | P1 |
| FR-54 | Banding yang ditolak boleh diajukan lagi, maksimal 3 kali pengajuan per tindakan moderasi **[Keputusan]** | P1 |
| FR-55 Δ | Batas **3 hari** berlaku untuk penjual mengajukan banding pertama setelah hide dan banding berikutnya setelah penolakan **[Keputusan]**. Batas ini **tidak** berlaku selama banding berstatus `menunggu` keputusan admin; admin tidak boleh menutup lapak otomatis karena tenggat penjual lewat saat banding masih diperiksa. Jika batas pengajuan penjual terlewat dan tidak ada banding menunggu, tindakan menjadi `tanpa_banding` dan lapak ditutup **[Usulan]** | P1 |
| FR-56 | Banding ketiga ditolak: lapak ditutup, sisa masa sewa hangus tanpa pengembalian dana **[Keputusan]**. Berlaku hanya bila FR-27 sudah aktif | P1 |
| FR-57 | Setiap tindakan hide, banding, dan keputusan admin dicatat (siapa, kapan, alasan) | P0 |
| FR-58 | Batas 3 hari dapat diubah lewat pengaturan agar demo tidak perlu menunggu **[Usulan]** | P1 |
| FR-59 Δ | Admin dapat memulihkan listing yang `tersembunyi` dan lapak `jeda`, sekalipun belum ada banding. `moderation_actions.status = pulih`, lapak `aktif`, listing `aktif`, `paused_at` dikosongkan dan `expires_at` digeser sekali sesuai FR-52; penjual dinotifikasi. Lapak `ditutup` tidak dipulihkan melalui tombol ini **[Usulan]** | **P0** |

**Pemutus banding.** Bila ada lebih dari satu admin, banding diputuskan admin yang berbeda dari yang melakukan hide **[Usulan]**. Bila hanya satu admin (kondisi demo), jalur banding tetap berjalan tetapi dicatat sebagai keterbatasan di laporan, karena pelaku hide dan pemutus banding orang yang sama.

**Versi minimal moderasi bila banding dipotong (lihat urutan pemotongan di Bagian 13) [Usulan].** Hide dan tampilkan kembali (FR-59) tetap ada. Lapak tetap berstatus *jeda* sampai admin menampilkan kembali listing. Penutupan otomatis lapak (FR-55, FR-56) tidak aktif.

**Kriteria penerimaan:** hide hanya berlaku pada listing aktif dan mengubah lapak menjadi jeda. Penjual dapat mengedit listing tersembunyi tanpa mempublikasikannya sendiri. Restore hanya berlaku pada lapak jeda, menampilkan listing kembali dan menggeser tanggal berakhir sekali. Banding yang masih menunggu keputusan admin tidak diperlakukan sebagai melewati tenggat penjual. Banding ketiga yang ditolak menutup lapak.

### 8.7 Dashboard admin Δ

| ID | Kebutuhan | Prioritas |
|---|---|---|
| FR-60 | Ringkasan dashboard admin | P1 |
| FR-61 | Validasi akun pendaftar, termasuk penanda "kode prodi tidak dikenal" dari FR-02 | P0 |
| FR-62 Δ | P0: antrean moderasi admin untuk hide dan restore listing. Penanganan banding serta antrean laporan listing (FR-44) adalah P1, tidak disyaratkan untuk menuntaskan P0 **[Usulan pemisahan lingkup]** | P0 |
| FR-63 | Kelola paket sewa lapak (nama, durasi, harga, tarif per minggu, aktif atau nonaktif) | P0 |
| FR-64 Δ | Daftar minimum pembayaran lapak beserta `status`, `gateway_status`, nominal, waktu dan `perlu_tinjauan`; admin dapat melihat kasus pembayaran terlambat. Laporan analitik/ekspor lanjutan tetap P1 **[Usulan penyesuaian dependensi]** | P0 |
| FR-65 | Pengaturan kategori (nama, prioritas, kriteria) dan merek | P0 |
| FR-66 | Pengelolaan kode prodi dan jurusan, termasuk periode berlaku | P1 |

### 8.8 Notifikasi

| ID | Kebutuhan | Prioritas |
|---|---|---|
| FR-70 | Notifikasi di dalam website (ikon di navbar dan daftar, dengan tanda sudah dibaca) untuk: listing di-hide beserta alasan, hasil banding, lapak ditutup | P0 |
| FR-71 | Pengingat masa sewa (lihat FR-36) | P1 |

### 8.9 Proses berbasis waktu Δ (bagian baru)

Beberapa kebutuhan bergantung pada berlalunya waktu, bukan pada tindakan pengguna. Semuanya dijalankan oleh **satu perintah artisan yang idempoten** (nama contoh: `klik:proses-waktu`), yang aman dijalankan berulang kali **[Usulan]**. Perintah ini dipicu dengan dua cara:

1. Laravel scheduler setiap menit (`php artisan schedule:work` selama demo dan pengembangan).
2. Dipanggil juga saat halaman terkait diakses (dashboard penjual, katalog), sebagai cadangan bila scheduler tidak berjalan.

| ID | Proses | Rujukan | Prioritas |
|---|---|---|---|
| FR-80 Δ | Untuk transaksi `pending` yang melewati `expires_at`, lakukan rekonsiliasi status ke gateway bila tersedia sebelum menetapkan `expire` lokal. Bersihkan payload/foto setelah dinyatakan tidak dapat diproses; webhook berhasil yang datang setelah status final diarahkan ke tinjauan, tidak membuat lapak. Perintah idempoten dan aman terhadap webhook paralel | FR-20, FR-20a, FR-20b | P0 |
| FR-81 | Foto tanpa transaksi lebih dari 24 jam dibersihkan | FR-20a | P1 |
| FR-82 | Lapak yang lewat tanggal berakhir menjadi *habis*, listingnya menjadi *kedaluwarsa* | FR-35 | P1 |
| FR-83 | Tindakan moderasi yang lewat batas banding menjadi *tanpa_banding*, lapak ditutup | FR-55 | P1 |
| FR-84 | Pengingat 5 hari sebelum masa sewa habis dikirim satu kali (penanda `reminder_sent`) | FR-36 | P1 |

**Kriteria penerimaan:** menjalankan perintah dua kali berturut-turut tidak membuat efek ganda. Mengatur batas waktu ke nilai kecil (FR-58) lalu menjalankan perintah memicu kedaluwarsa tanpa menunggu hari sebenarnya.

## 9. Aturan bisnis

### 9.1 Validasi NIM Δ
Struktur NIM PNP, contoh `2401093006` **[Keputusan]**:

| Digit | Arti | Contoh |
|---|---|---|
| 1-2 | Tahun masuk | 24 (2024) |
| 3-6 | Kode prodi | 0109 (D3 Manajemen Informatika) |
| 7 | Jalur masuk (1 SNBP, 2 SNBT, 3 mandiri) | 3 |
| 8-10 | Nomor urut | 006 |

Aturan pemeriksaan otomatis: panjang 10 digit angka, jalur 1-3, nomor urut bukan 000, tahun masuk tidak di masa depan, NIM belum dipakai. Untuk kode prodi: bila kode ada di tabel kode prodi, kode harus cocok dengan prodi yang dipilih (dan berlaku pada tahun masuk tersebut), bila tidak cocok pendaftaran ditolak. Bila kode **tidak ada** di tabel (misalnya prodi yang kodenya belum diketahui, lihat Lampiran A), pendaftaran tidak ditolak otomatis dan diteruskan ke admin dengan penanda **[Usulan]**. Alumni diperbolehkan **[Keputusan]**, sehingga batas lama studi tidak dipakai untuk menolak pendaftar.

Pemeriksaan format ini hanya penyaring awal, bukan bukti identitas. Pola NIM dapat ditebak, sehingga verifikasi KTM oleh admin tetap menjadi syarat aktivasi. Struktur NIM dan tabel kode prodi berasal dari pemilik proyek dan belum diverifikasi ke pihak kampus (lihat Bagian 16).

### 9.2 Kategori laptop
Lima kategori **[Keputusan]**. Karena satu laptop dapat memenuhi lebih dari satu kategori, dipakai aturan prioritas (kolom `prioritas` di tabel kategori) **[Usulan]**:

| Prioritas | Kategori | Kriteria terukur [Usulan] |
|---|---|---|
| 1 | MacBook | Merek Apple |
| 2 | Laptop Gaming | Memiliki kartu grafis dedicated |
| 3 | Laptop 2-in-1 | Tipe 2-in-1 (engsel 360° atau layar dapat dilepas) |
| 4 | Ultrabook | Berat di bawah batas yang ditetapkan (acuan awal: di bawah 1,5 kg) |
| 5 | Laptop Standar | Tidak memenuhi kriteria lain |

### 9.3 Harga lapak

| Paket | Durasi | Harga | Per hari |
|---|---|---|---|
| 3 bulan | 90 hari | Rp40.000 | sekitar Rp444 |
| 5 bulan | 150 hari | Rp65.000 | sekitar Rp433 |
| Custom | 1 sampai 52 minggu | Rp4.000 per minggu | sekitar Rp571 |

Ketiga harga sudah diputuskan pemilik proyek. Custom dibuat selalu lebih mahal per hari daripada paket tetap agar tidak ada celah memilih custom untuk durasi panjang. Paket 1 bulan dan 2 bulan tidak dijual: durasi pendek dibeli lewat custom (misalnya 4 minggu = Rp16.000). Durasi disimpan dalam hari.

Diskon paket 5 bulan dibanding 3 bulan hanya sekitar 2,5% per hari. Selisih ini kecil sebagai insentif, tetapi harga sudah diputuskan, jadi tidak diubah. Perlu diuji di survei apakah dua paket tetap ini cukup membedakan.

### 9.3.1 Ekonomi per lapak dan biaya gateway Δ (bagian baru, ilustrasi, bukan proyeksi)

Tarif Midtrans yang dipakai perhitungan di bawah. **Hanya tarif QRIS yang saya verifikasi dari dokumentasi Midtrans. Tarif lain berasal dari situs pihak ketiga dan harus diverifikasi di midtrans.com/pricing sebelum masuk proyeksi keuangan.**

| Metode | Tarif | Status verifikasi |
|---|---|---|
| QRIS | 0,7% per transaksi | Dokumentasi Midtrans |
| E-wallet (GoPay langsung, ShopeePay) | sekitar 2% | Pihak ketiga, perlu verifikasi |
| E-wallet (OVO, DANA) | sekitar 1,5% | Pihak ketiga, perlu verifikasi |
| Virtual account / transfer bank | flat sekitar Rp4.000 | Pihak ketiga, perlu verifikasi |

| Paket | Harga | QRIS (0,7%) | GoPay langsung (2%) | Virtual account (Rp4.000) |
|---|---|---|---|---|
| 3 bulan | Rp40.000 | Rp280 | Rp800 | Rp4.000 (10%) |
| 5 bulan | Rp65.000 | Rp455 | Rp1.300 | Rp4.000 (6,2%) |
| Custom 4 minggu | Rp16.000 | Rp112 | Rp320 | Rp4.000 (25%) |
| Custom 1 minggu | Rp4.000 | Rp28 | Rp80 | Rp4.000 (100%) |

Kesimpulan: virtual account tidak masuk akal untuk nominal sekecil ini, sehingga FR-26 menjadi P0. Pendapatan per lapak per bulan (30 hari) sekitar Rp13.300 (paket 3 bulan), Rp13.000 (paket 5 bulan), dan Rp17.100 (custom). Sebagai gambaran skala, 100 lapak aktif pada paket tetap menghasilkan sekitar Rp1,3 juta per bulan, sebelum biaya hosting dan tenaga admin untuk verifikasi KTM manual. Proyeksi keuangan harus memuat biaya-biaya tersebut.

### 9.4 Siklus status (sesuai ERD v6) Δ

**Akun (`status_verifikasi`):** menunggu, disetujui, ditolak.

**Lapak:** aktif, jeda (listing di-hide), habis (masa sewa selesai), ditutup (banding ketiga ditolak atau batas pengajuan banding terlewat) **[Keputusan]**. Semua status tercantum di ERD v6.

**Listing:** aktif, tersembunyi, terjual, kedaluwarsa. Saat lapak berstatus *ditutup*, listing tetap *tersembunyi* dan tidak dapat ditampilkan kembali **[Usulan]**, sehingga tidak perlu status baru di ERD.

**Pembayaran lapak:** pending, paid, expire, cancel, failed. `perlu_tinjauan` adalah penanda independen, bukan status pembayaran keenam. `gateway_status` mencatat status terakhir dari Midtrans agar rekonsiliasi tidak menghapus sejarah status lokal **[Usulan]**.

**Tindakan moderasi:** `menunggu` = masa pengajuan banding penjual masih berjalan; `banding` = banding diajukan dan menunggu keputusan admin; `pulih` = listing dipulihkan (FR-59 atau banding diterima); `hangus` = lapak ditutup akibat banding ketiga ditolak; `tanpa_banding` = batas pengajuan penjual terlewat tanpa banding tertunda. Perintah waktu tidak boleh menutup aksi berstatus `banding`. Penolakan banding ke-1/2 mengembalikan aksi ke `menunggu` dengan deadline baru **[Usulan definisi teruji]**.

**Banding:** menunggu, diterima, ditolak.

### 9.5 Kriteria moderasi Δ (bagian baru) **[Usulan, daftar ditetapkan pemilik proyek]**

Admin hanya boleh menyembunyikan listing atas alasan di bawah. Tiap tindakan hide wajib memilih satu kode dan dapat menambah catatan. Daftar ini juga menjadi acuan penjual saat memperbaiki listing sebelum banding.

| Kode | Alasan hide | Contoh |
|---|---|---|
| M1 | Barang bukan laptop | Aksesori, ponsel, komponen lepas |
| M2 | Spesifikasi atau kondisi menyesatkan | Spesifikasi tidak cocok dengan foto atau deskripsi |
| M3 | Kategori tidak sesuai kriteria 9.2 | Laptop tanpa GPU dedicated dicatat sebagai Gaming |
| M4 | Foto bukan barang sebenarnya | Foto dari internet atau milik orang lain |
| M5 | Harga tidak wajar atau placeholder | Harga Rp1 atau Rp0 |
| M6 | Konten terlarang | Kata kasar, iklan barang lain, ajakan transaksi di luar COD kampus |
| M7 | Dugaan barang ilegal atau hasil kejahatan | Laporan dari pembeli atau temuan admin |
| M8 | Listing ganda | Laptop yang sama dipasang lebih dari sekali |

## 10. Kebutuhan non-fungsional

### 10.1 Keamanan Δ
- Kata sandi disimpan dalam bentuk hash.
- Kolom peran (`role`) tidak boleh dapat diisi dari form (lindungi dari mass assignment). Akun admin dibuat lewat seeder atau perintah artisan.
- Halaman admin berada di bawah awalan rute tersendiri dengan middleware pemeriksa peran.
- Unggahan file (KTM, foto listing, bukti banding) divalidasi jenis dan ukurannya. KTM disimpan di penyimpanan privat, tidak dapat diakses publik.
- Server Key Midtrans disimpan di `.env`, tidak dimasukkan ke Git atau kode frontend. Hanya Client Key yang boleh dipakai di browser.
- Notifikasi webhook diverifikasi tanda tangannya, dan jumlah dibandingkan dengan harga tersimpan. Pemrosesan ganda untuk order yang sama dicegah (nomor order unik). Notifikasi untuk transaksi yang sudah berstatus akhir tidak mengubah status secara diam-diam, kecuali kasus FR-20b.
- Aktivitas admin dicatat (FR-57).
- Percobaan login dan registrasi dibatasi (throttle) per akun dan per alamat IP **[Usulan]**.
- Pesan kesalahan login tidak membedakan "NIM tidak ada" dari "kata sandi salah" **[Usulan]**.
- Nomor WhatsApp penjual hanya ditampilkan kepada pengguna login (FR-14, FR-40) **[Usulan]**.

### 10.2 Privasi
- Kumpulkan data seminimal mungkin. Kolom jenis kelamin dihapus **[Keputusan]**.
- Akses ke data KTM dibatasi untuk admin, dan berkas dihapus setelah keputusan (FR-07).
- Isian form sementara di catatan transaksi dibersihkan setelah transaksi selesai atau kedaluwarsa **[Usulan]**.

### 10.3 Kegunaan dan kompatibilitas
- Tampilan responsif untuk desktop dan mobile (diuji pada fase pengujian).
- Notifikasi terlihat jelas: ikon di navbar dan banner di dashboard untuk lapak yang di-hide, lengkap dengan hitung mundur batas banding.

### 10.4 Ketahanan saat demo
- Mode pembayaran simulasi sebagai cadangan jika Midtrans sandbox atau terowongan (tunnel) bermasalah.
- Rekaman demo cadangan.
- Data contoh yang realistis di katalog, dengan keterangan bahwa itu data contoh.
- Proses berbasis waktu (8.9) dapat dipicu manual dari terminal bila scheduler tidak berjalan saat demo **[Usulan]**.

## 11. Arsitektur dan teknologi Δ

| Komponen | Pilihan |
|---|---|
| Backend dan tampilan | Laravel dengan Inertia **[Keputusan]** |
| Kerangka sisi klien Inertia | **Vue 3 [Keputusan final 4 November 2026]**, dengan Laravel + Inertia. Pilihan React dan Svelte ditutup untuk baseline v0.6 |
| Database | MySQL |
| Desain antarmuka | Figma |
| Pembayaran | Midtrans, mode sandbox |
| Proses berbasis waktu | Perintah artisan idempoten, dipicu scheduler dan saat halaman diakses **[Usulan]** |
| Diagram | draw.io (flowchart dan ERD), diekspor ke PDF |
| Lingkungan pengembangan | WSL atau Docker |

Alur pembayaran: server membuat transaksi di Midtrans, pengguna membayar lewat halaman atau jendela Snap, Midtrans mengirim notifikasi ke server, server memverifikasi lalu membuat lapak dan listing dari isian form yang tersimpan. Karena server berjalan lokal, pengujian webhook memerlukan terowongan ke alamat publik.

Diagram arsitektur infrastruktur (server, database, payment gateway, keamanan) perlu diperbarui: tambahkan Midtrans, hapus escrow.

## 12. Model data (ERD KLiK v6) Δ

ERD v6 memuat **15 tabel**: 14 tabel dasar dari v4, ditambah `reports` (P1). Atribut P1/P2 boleh disiapkan di rancangan namun bukan syarat implementasi MVP. Semua foreign key utama, termasuk self-reference dan kolom audit, didaftar pada ERD v6.

| Tabel | Isi utama |
|---|---|
| `jurusans` | Nama jurusan |
| `prodi_codes` | Jurusan, kode prodi (4 digit), jenjang (D3/D4), nama prodi, periode berlaku (dari-sampai, tahun), pembuat |
| `users` | Nama, username, password hash, NIM unik, status mahasiswa, kode prodi mentah, penanda kode tidak dikenal, angkatan, prodi, KTM privat nullable, status/pemutus/waktu verifikasi, alasan penolakan, role, suspend; tanpa jenis_kelamin |
| `notifications` | Penerima, tipe, referensi, pesan, sudah dibaca |
| `plans` | Nama, penanda custom, durasi (hari), harga, tarif per minggu, aktif |
| `lapaks` | Pemilik, paket, status (aktif/jeda/habis/ditutup), harga dan durasi salinan, persetujuan syarat (salinan), mulai, berakhir, waktu jeda, pengingat terkirim |
| `transactions` | Pengguna, paket, lapak nullable **unik**, order_id unik, nominal dan durasi salinan, status lokal, status gateway, batas kedaluwarsa, persetujuan syarat, jenis pembayaran, payload sementara, waktu bayar, penanda/alasan tinjauan |
| `inquiries` | Listing, pembeli, waktu klik (catatan klik tombol WhatsApp) |
| `brands` | Nama merek (unik) |
| `categories` | Nama, prioritas, kriteria |
| `listings` | Lapak, kategori, merek, spesifikasi dan harga, nomor WhatsApp, status, waktu terjual; generated column `active_lapak_id` (unik ketika aktif) dan konfirmasi terakhir opsional P2 |
| `listing_photos` | Listing, lokasi berkas foto |
| `moderation_actions` | Listing, admin, **kode alasan M1-M8**, catatan, waktu hide, tenggat respons penjual, status dan waktu selesai |
| `appeals` | Tindakan moderasi, nomor banding (1-3), alasan, bukti, waktu pengajuan, status, pemutus, catatan admin, waktu putusan, batas pengajuan berikutnya |
| `reports` **(P1)** | Listing, pelapor, alasan, catatan, waktu, status; generated `active_reporter_id` untuk status `menunggu`/`ditinjau`, UNIQUE (`listing_id`, `active_reporter_id`), serta validasi Laravel |

**Constraint dan aturan implementasi ERD v6:**
- `users.nim` UNIQUE untuk seluruh status; autentikasi memakai **NIM + password**, username hanya nama tampilan, tidak wajib UNIQUE. Pengajuan ulang memakai akun yang sama.
- `transactions.order_id` UNIQUE dan `transactions.lapak_id` UNIQUE nullable (MySQL mengizinkan banyak NULL). Satu order berhasil menciptakan paling banyak satu lapak.
- `listings.active_lapak_id` generated stored: `CASE WHEN status = 'aktif' THEN lapak_id ELSE NULL END`, UNIQUE. Karena MySQL mengizinkan beberapa NULL, riwayat banyak listing tetap dapat dipertahankan.
- **[Keputusan]** `reports.active_reporter_id` generated STORED: `CASE WHEN status IN ('menunggu','ditinjau') THEN reporter_id ELSE NULL END`; UNIQUE (`listing_id`, `active_reporter_id`). MySQL membolehkan lebih dari satu `NULL`, sehingga beberapa laporan historis `selesai` diperbolehkan. Laravel tetap melakukan validasi user terverifikasi, kepemilikan laporan, dan error handling saat UNIQUE dilanggar.
- `appeals` UNIQUE (`moderation_action_id`, `nomor_banding`), nomor 1-3; batas berikutnya tidak berjalan ketika ada banding menunggu admin.
- `lapaks.expires_at` bertambah durasi jeda satu kali saat restore, lalu `paused_at = NULL`.
- Paket custom dibatasi **1 sampai 52 minggu**; 52 minggu = 364 hari. Gunakan satuan hari sebagai batas implementasi, bukan satu tahun kalender yang dapat berbeda.
- Webhook memverifikasi signature, order, nominal, status, lalu mengunci transaksi dan menciptakan lapak/listing secara atomik serta idempoten.
- `transactions.form_payload` dan path foto sementara tidak menjadi data permanen; dihapus setelah keberhasilan, pembatalan atau expiry sesuai FR-20a. Penghapusan KTM terjadi setelah keputusan (P0).
- FK audit `users.verified_by`, `prodi_codes.created_by`, `moderation_actions.admin_id` dan `appeals.decided_by` menunjuk `users.id`; diagram menampilkan garis relasinya.

**Catatan rancangan:** tabel `reports` dan `listings.terakhir_dikonfirmasi` disiapkan untuk P1/P2. Kehadirannya pada ERD tidak berarti harus diimplementasikan untuk demo P0.

### 12.1 Spesifikasi constraint untuk migration MySQL (baseline v0.6)

Aturan berikut merupakan **rancangan implementasi**, bukan klaim bahwa migration sudah dijalankan. Target awal MySQL 8.0+; gunakan engine InnoDB dan sesuaikan sintaks migration Laravel dengan versi yang benar-benar dipasang.

| Tabel | Constraint / index | Tujuan |
|---|---|---|
| `users` | UNIQUE (`nim`) | Mencegah dua akun memakai NIM sama, termasuk akun ditolak |
| `transactions` | UNIQUE (`order_id`), UNIQUE (`lapak_id`) nullable | Idempotensi order dan paling banyak satu transaksi pembelian untuk satu lapak |
| `listings` | generated STORED `active_lapak_id = CASE WHEN status='aktif' THEN lapak_id ELSE NULL END`; UNIQUE (`active_lapak_id`) | Maksimum satu listing aktif tiap lapak |
| `reports` | generated STORED `active_reporter_id = CASE WHEN status IN ('menunggu','ditinjau') THEN reporter_id ELSE NULL END`; UNIQUE (`listing_id`,`active_reporter_id`) | Maksimum satu laporan aktif per listing dan pelapor |
| `appeals` | UNIQUE (`moderation_action_id`,`nomor_banding`); CHECK nomor_banding BETWEEN 1 AND 3 | Tidak ada banding duplikat/di luar rentang |
| `transactions` | INDEX (`status`,`expires_at`) | Memproses transaksi pending yang kedaluwarsa |
| `lapaks` | INDEX (`status`,`expires_at`) | Memproses kedaluwarsa masa sewa |
| `moderation_actions` | INDEX (`status`,`response_deadline`) | Memproses batas banding, hanya saat status `menunggu` |
| `listings` | INDEX (`status`,`category_id`,`brand_id`,`harga`) | Mendukung katalog dan filter P0 |

Semua FK yang ditandai `[FK]` dalam ERD wajib diwujudkan di migration, termasuk self-reference `users.verified_by → users.id`. Untuk akun/admin yang dihapus, gunakan `RESTRICT` atau `SET NULL` sesuai sifat kolom; **jangan menggunakan cascade delete pada transaksi, lapak, dan catatan moderasi** tanpa kebijakan retensi data eksplisit. Proses webhook dan pemulihan jeda menggunakan database transaction dan row lock untuk menghindari race condition. Jangan mengandalkan validasi Laravel saja untuk constraint unik.

### 12.2 Contoh SQL constraint laporan aktif (MySQL 8.0+)

```sql
ALTER TABLE reports
  ADD COLUMN active_reporter_id BIGINT
  GENERATED ALWAYS AS (
    CASE WHEN status IN ('menunggu', 'ditinjau')
      THEN reporter_id ELSE NULL END
  ) STORED,
  ADD UNIQUE KEY uq_reports_one_active (listing_id, active_reporter_id);
```

SQL ini berlaku **setelah** kolom `listing_id`, `reporter_id`, dan `status` dibuat. Validasi Laravel tetap harus mengembalikan pesan yang dapat dipahami pengguna ketika constraint unik gagal karena dua permintaan bersamaan. State `selesai` mengizinkan laporan berikutnya dan riwayat laporan lama tetap disimpan.

### 12.3 Batas kesiapan implementasi

Baseline desain telah diselaraskan, tetapi **belum diverifikasi melalui `php artisan migrate` atau integrasi Midtrans sandbox**. Keberhasilan migration harus diuji secara nyata, termasuk percobaan dua listing aktif, dua laporan aktif, dan webhook duplikat. Data survei, kode prodi yang belum dipastikan, tarif gateway, dan alur bukti alumni tidak boleh dianggap tervalidasi hanya karena dokumen final.

## 13. Rencana pengerjaan (9 minggu) Δ

Status per v0.3: Minggu 1 sampai 3 selesai dan disetujui dosen (survei, flowchart dan ERD, desain Figma). Minggu 4 sedang berjalan. **Perbarui status ini sesuai kondisi sebenarnya.** Total 9 minggu sudah termasuk Minggu 1 **[Keputusan]**. Tidak ada minggu khusus untuk memahami kode: sisihkan 1-2 hari terakhir tiap minggu untuk membaca dan menjelaskan ulang kode yang baru dibuat.

**Keputusan Minggu 4 sudah final (4 November 2026):** gunakan **Laravel + Inertia + Vue 3**; login memakai **NIM + kata sandi**. Username hanyalah nama tampilan dan tidak dipakai untuk login. Tidak ada blocker pemilihan framework atau identifier autentikasi.

### 13.1 Jalur pengembangan

| Minggu | Objective | Isi pekerjaan | Status | Output |
|---|---|---|---|---|
| 1 | Riset dan validasi bisnis | Survei dan BMC awal | Selesai | Hasil survei |
| 2 | Perancangan sistem | Mematangkan 5 kategori, flowchart, ERD MySQL | Selesai | Flowchart, ERD |
| 3 | Perancangan UI/UX web | Wireframe dan desain tata letak di Figma | Selesai | Mockup Figma |
| 4 | Setup backend, database, dan autentikasi | Laravel dengan Inertia, migrasi dan seeder dari ERD v6 (termasuk seeder kategori, merek, paket, dan kode prodi, agar panel pengelolaannya bisa menyusul). Registrasi dengan validasi NIM otomatis, unggah/hapus KTM, pengajuan ulang akun ditolak, login (baseline NIM), panel admin Validasi Akun | Sedang berjalan | Database aktif, login dan registrasi aktif, panel Validasi Akun |
| 5 | Katalog dan halaman publik | Komponen UI, halaman utama, Semua Produk dengan filter 5 kategori, detail produk (katalog publik), tombol WhatsApp (wajib login, klik dicatat), FAQ dan About | Belum | Katalog tampil di web |
| 6 | Beli lapak dan dashboard | Pilih paket, form penjualan, syarat dan ketentuan, checkout dengan pembayaran simulasi, lapak aktif dengan masa sewa, dashboard penjual dan pembeli, tandai terjual, **ganti laptop P0**, constraint listing aktif, perintah proses berbasis waktu (FR-80) | Belum | Listing dan sewa lapak berfungsi (simulasi) |
| 7 | Midtrans dan panel admin | Midtrans sandbox dengan webhook bertanda tangan dan idempoten, pembatasan QRIS/e-wallet, penanganan bayar terlambat, uji expire, panel admin Kelola Paket, **Transaksi P0**, Kategori dan Merek | Belum | Pembayaran sandbox, panel admin inti |
| **Titik cek 7** | Uji alur P0 | Jalankan skenario T-01 sampai T-12 (Lampiran B) untuk registrasi, beli lapak, katalog, dan webhook. Perbaiki yang gagal sebelum lanjut | | Daftar bug dan perbaikannya |
| 8 | Moderasi | Admin menyembunyikan dan menampilkan kembali listing dengan alasan (kode 9.5), notifikasi di dalam website, masa sewa berhenti, banding tahap awal | Belum | Alur moderasi |
| 9 | Banding dan pengujian | Banding maksimal 3 kali dengan batas 3 hari, lapak ditutup otomatis, lapor listing bila waktu ada, uji seluruh skenario Lampiran B dan responsivitas, perbaikan bug, rekaman demo cadangan | Belum | Web responsif tanpa bug |

Pengajuan beli, terima atau tolak, dan atur temu di dalam aplikasi tidak dibuat karena kontak lewat tombol WhatsApp **[Keputusan]**. Ulasan (P2) dan konfirmasi ketersediaan berkala (P2) hanya dikerjakan jika waktu tersisa.

**Peringatan beban.** Minggu 4 dan Minggu 9 adalah minggu yang paling padat, dan tidak ada buffer di rencana ini. Bila Minggu 4 sampai 6 meleset dua hari saja, urutan pemotongan di bawah harus langsung dijalankan, bukan menunggu Minggu 9.

**Urutan pemotongan fitur bila jadwal ketat [Usulan, diperbarui]:** ulasan (P2), konfirmasi ketersediaan berkala (P2), pengingat masa sewa, halaman Ringkasan admin, lapor listing (FR-44), banding berulang (cukup satu pengajuan), lalu banding secara keseluruhan (pakai versi minimal moderasi di 8.6). **Alur inti tidak dipotong:** registrasi, validasi dan pengajuan ulang akun ditolak, penghapusan KTM, listing berbayar, penggantian laptop setelah terjual, katalog, tombol WhatsApp, hide/restore, daftar transaksi untuk tinjauan, serta skenario uji P0.

### 13.2 Jalur paralel Kewirausahaan [Usulan]

Pekerjaan berikut tidak butuh kode dan sebelumnya belum punya minggu. Jadwal ini usulan, dan tenggat laporan harus dikonfirmasi ke dosen **[Terbuka]**.

| Minggu | Pekerjaan | Ketergantungan |
|---|---|---|
| 5 | Analisis hasil survei. Isi tabel Bagian 2 dan tetapkan target angka uji pasar (item terbuka #7) | Data survei Minggu 1 |
| 6 | Draf proyeksi keuangan memakai tarif gateway yang sudah diverifikasi (9.3.1), biaya hosting, dan biaya tenaga admin, dengan skenario konservatif | Verifikasi tarif Midtrans |
| 7 | Siapkan skrip dan rekrut calon pengguna untuk uji coba | Target angka dari Minggu 5 |
| 8 | Uji coba calon pengguna memakai versi yang sudah punya alur beli lapak (sandbox) dan katalog. Mulai pitch deck | Produk Minggu 7 |
| 9 | Pitch deck dan laporan akhir (termasuk keterbatasan: kontak lewat WhatsApp, satu admin, validasi NIM bukan bukti identitas) | Hasil uji coba |

## 14. Keputusan terbuka Δ

Sudah diputuskan dan tidak lagi terbuka: harga paket 5 bulan Rp65.000, status lapak `ditutup`, alumni boleh memakai, kolom jenis kelamin dihapus, berkas KTM dihapus saat keputusan admin, pembayaran kedaluwarsa 30 menit, foto sementara dihapus saat transaksi gagal atau kedaluwarsa dan 24 jam untuk unggahan tanpa transaksi, menutup jendela pembayaran membatalkan **alur checkout di UI** (tidak menjamin pembatalan di gateway). Stack Vue 3, login NIM + kata sandi, pengajuan ulang pada akun yang sama, dan constraint laporan aktif MySQL + Laravel juga sudah final. Perubahan konsep dari "Rekber (escrow)" ke COD dan sewa lapak sudah dikonfirmasikan ke dosen.

| No | Hal yang perlu diputuskan | Catatan |
|---|---|---|
| 1 | Penafsiran "9 minggu": total termasuk Minggu 1 (asumsi dokumen ini) atau 9 minggu pengerjaan setelah Minggu 1 | |
| 2 | **SELESAI: Vue 3** | Keputusan final 4 November 2026. Bukan blocker |
| 3 | **SELESAI: login NIM + kata sandi** | Username nama tampilan, bukan identifier login. Keputusan final 4 November 2026 |
| 4 | Atribut listing tambahan: ukuran layar, sistem operasi, dan bentuk penyimpanan yang bisa difilter (saat ini teks) | Menentukan filter P1 (FR-13) |
| 5 | Metode pembayaran yang diaktifkan di Midtrans | Usulan: QRIS dan e-wallet, P0 (FR-26) |
| 6 | Kode prodi D4 Teknologi Rekayasa Mekatronika belum diketahui, serta celah kode D3 (0111, 0113-0115) | Dampaknya sudah dikurangi lewat FR-02 versi baru, tetapi sebaiknya tetap dilengkapi |
| 7 | Target angka uji pasar, setelah survei dianalisis | Jadwal di 13.2 |
| 8 | Verifikasi alumni: KTM lama, atau bukti kelulusan lain yang diterima admin | Usulan di FR-09 |
| 9 | Katalog publik atau wajib login | Usulan: katalog dan detail publik, kontak penjual wajib login. Alasan: mengurangi hambatan bagi calon pembeli dan beban verifikasi admin, nomor WhatsApp tetap terlindungi. Konsekuensi: katalog terlihat oleh non-mahasiswa, sehingga klaim "internal kampus" melemah |
| 10 | Mekanisme proses berbasis waktu (8.9) | Scheduler + perintah idempoten; pemicu saat akses halaman hanya fallback, harus aman terhadap race condition |
| 11 | Tenggat dan bentuk laporan akhir, pitch deck, dan uji coba pengguna dari dosen | Menentukan jadwal 13.2 |

## 15. Risiko dan mitigasi Δ

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Implementasi Minggu 4-7 terlalu padat, tidak ada buffer | Fitur tidak selesai | Urutan pemotongan fitur (Bagian 13), pembayaran simulasi dulu, titik cek Minggu 7 |
| Pemilihan stack atau login berubah setelah migration dimulai | Rework implementasi | Stack Vue 3 dan autentikasi NIM + kata sandi sudah dibekukan dalam baseline v0.6 |
| Midtrans sandbox atau terowongan gagal saat demo | Pembayaran tidak terkonfirmasi | Mode simulasi, rekaman demo, uji di komputer dan jaringan yang sama dengan presentasi |
| Pembayaran masuk setelah transaksi dibatalkan (misalnya QRIS dibayar setelah jendela ditutup) | Uang diterima tanpa lapak | FR-20b: ditandai dan ditangani manual, pembatalan di sisi Midtrans dicoba bila tersedia |
| Biaya gateway menggerus pendapatan pada nominal kecil | Margin tipis: virtual account flat dilaporkan Rp4.000 (10% dari paket 3 bulan) | Batasi metode ke QRIS dan e-wallet (P0). QRIS 0,7% menurut dokumentasi Midtrans, tarif lain diverifikasi di midtrans.com/pricing |
| Pendapatan per lapak kecil (sekitar Rp13.000 per bulan) dan biaya admin verifikasi manual tidak terhitung | Model bisnis tidak terlihat layak | Masukkan biaya admin dan hosting ke proyeksi, hitung titik impas konservatif |
| Harga belum divalidasi dengan calon pengguna | Penjual enggan membayar | Isi data kesediaan membayar dari survei (Bagian 2), survei lanjutan, promo awal sebagai opsi |
| Katalog kosong saat demo | Terlihat tidak meyakinkan | Data contoh yang diberi keterangan |
| NIM dan KTM dapat dipalsukan | Pengguna non-mahasiswa masuk | Cek format NIM otomatis ditambah pencocokan KTM oleh admin |
| Kode prodi tidak lengkap menolak mahasiswa asli | Pendaftar sah tertolak saat demo | FR-02 versi baru meneruskan kode tidak dikenal ke admin |
| Penyalahgunaan wewenang hide | Sengketa dengan penjual | Kode alasan wajib (9.5), log aktivitas, jalur banding |
| Hanya satu admin: pelaku hide dan pemutus banding orang yang sama | Banding terlihat formalitas | Admin berbeda bila ada, bila tidak dicatat sebagai keterbatasan di laporan |
| Moderasi tanpa jalur pemulihan bila banding dipotong | Lapak jeda tanpa akhir | FR-59 (P0) dan versi minimal moderasi (8.6) |
| Kontak lewat WhatsApp di luar sistem | Tidak ada bukti transaksi terjadi, status terjual bergantung penjual | Dicatat sebagai keterbatasan di laporan, klik tombol dicatat sebagai indikator minat |
| Listing basi (laptop sudah terjual tetapi masih tayang) | Masalah "status tidak jelas" tidak teratasi | Lapor listing (FR-44), konfirmasi berkala (FR-37, P2), dicatat sebagai keterbatasan |
| Proses berbasis waktu tidak jalan saat demo | Kedaluwarsa dan penutupan tidak terjadi | Pemicu ganda: scheduler dan saat halaman diakses, serta pemicu manual (10.4) |
| Pekerjaan Kewirausahaan terlambat karena tidak punya jadwal | Nilai non-kode berkurang | Jalur paralel 13.2, konfirmasi tenggat ke dosen |
| Kode tidak dipahami saat tanya jawab | Nilai berkurang | Pelajari kode tiap minggu, latihan menjelaskan di minggu 9 |
| Pasar satu kampus kecil | Pendapatan terbatas | Hitung titik impas konservatif di laporan keuangan |

## 16. Asumsi

- Pengguna adalah mahasiswa PNP dengan NIM berformat seperti di bagian 9.1.
- Lima kategori dan struktur NIM berasal dari pemilik proyek dan belum diverifikasi ke pihak kampus.
- Daftar kode prodi berasal dari pemilik proyek dan dapat berubah.
- Mode sandbox Midtrans cukup untuk kebutuhan demo.
- Proyek hanya dinilai lewat demo ke dosen, bukan dipakai pengguna nyata.
- Tarif Midtrans selain QRIS belum diverifikasi ke sumber resmi dan dapat berubah **[Usulan]**.

## 17. Riwayat perubahan rancangan

Riwayat v0.3, v0.4, dan v0.5 dirangkum di Bagian 0. **PRD v0.6 dan ERD v6** menjadi baseline migration; usulan di luar empat keputusan final tetap memerlukan keputusan pemilik jika berdampak pada implementasi.

| Dari | Menjadi | Alasan |
|---|---|---|
| Escrow (dana ditahan sampai inspeksi) | COD di kampus | Menghilangkan beban refund dan sengketa berbasis dana |
| Pendapatan dari komisi transaksi | Pendapatan dari sewa lapak | Transaksi tidak lagi melewati platform |
| Verifikasi email kampus | Validasi NIM dan KTM | Keberadaan email institusi untuk semua mahasiswa belum terverifikasi |
| Aplikasi mobile Flutter | Website Laravel dengan Inertia | Satu basis kode, lebih sesuai waktu |
| Paket 1, 2, 3 bulan dan custom | Paket 3 bulan, 5 bulan, dan custom | Keputusan pemilik proyek |
| Harga paket 3 bulan Rp80.000, custom Rp8.000 per minggu | Rp40.000 dan Rp4.000 per minggu | Keputusan pemilik proyek |
| Perpanjangan masa sewa | Tidak ada, lebih dari 1 tahun beli lapak baru | Keputusan pemilik proyek |
| Pengajuan beli di dalam aplikasi | Tombol WhatsApp ke penjual | Keputusan pemilik proyek |
| Notifikasi email | Notifikasi di dalam website | Tidak sepadan dengan usaha untuk demo |
| Timeline 11 minggu | Timeline 9 minggu | Keputusan pemilik proyek |
| ERD v3 (13 tabel) | ERD v4 (14 tabel, tambah `brands`, status alumni, jeda lapak, isian form sementara) | Revisi pemilik proyek |
| Kolom jenis kelamin di pendaftaran | Dihapus | Tidak ada fitur yang membutuhkannya, minimisasi data |
| Status alumni belum ditentukan | Alumni boleh memakai | Keputusan pemilik proyek |
| Status lapak aktif, jeda, habis | Ditambah ditutup | Membedakan lapak yang ditutup karena banding gagal dari yang habis masanya |

## Lampiran A. Kode prodi pada NIM (digit 3-6)

Catatan: kode di bawah berasal dari pemilik proyek dan belum diverifikasi ke kampus. Kode D3 yang tidak muncul di daftar (0111, 0113-0115) dan kode Mekatronika yang belum diketahui ditangani lewat FR-02 versi baru (diteruskan ke admin, tidak ditolak otomatis).

| Jurusan | Prodi | Kode |
|---|---|---|
| Teknik Mesin | D3 Teknik Mesin | 0101 |
| | D3 Teknik Alat Berat | 0110 |
| | D4 Teknik Manufaktur | 1104 |
| | D4 Rekayasa Perancangan Mekanik | 1112 |
| Teknik Sipil | D3 Teknik Sipil | 0102 |
| | D4 Teknik Perencanaan Irigasi dan Rawa | 1103 |
| | D4 Manajemen Rekayasa Konstruksi | 1106 |
| | D4 Perencanaan Jalan dan Jembatan | 1105 |
| Teknik Elektro | D3 Teknik Listrik | 0103 |
| | D3 Teknik Elektronika | 0104 |
| | D3 Teknik Telekomunikasi | 0105 |
| | D4 Teknik Elektronika | 1101 |
| | D4 Teknik Telekomunikasi | 1107 |
| | D4 Teknologi Rekayasa Instalasi Listrik | 1109 |
| | D4 Teknologi Rekayasa Mekatronika | (belum diketahui) |
| Administrasi Niaga | D3 Administrasi Bisnis | 0106 |
| | D4 Usaha Perjalanan Wisata | 1113 |
| | D4 Destinasi Pariwisata | 1111 |
| | D4 Bisnis Digital | 1114 |
| | D4 Logistik Perdagangan Internasional | 1115 |
| Akuntansi | D3 Akuntansi | 0107 |
| | D4 Akuntansi | 1102 |
| Teknologi Informasi | D3 Teknik Komputer | 0108 |
| | D3 Manajemen Informatika | 0109 |
| | D3 Sistem Informasi | 0116 |
| | D4 Teknologi Rekayasa Perangkat Lunak | 1108 |
| | D4 Animasi | 1116 |
| Bahasa Inggris | D3 Bahasa Inggris | 0112 |
| | D4 Bahasa Inggris untuk Komunikasi Bisnis dan Profesional | 1110 |

## Lampiran B. Skenario uji P0 Δ (bagian baru) **[Usulan]**

Skenario ini menjadi dasar ukuran "seluruh skenario uji P0 lulus" di 3.2. Titik cek Minggu 7 menjalankan T-01 sampai T-12.

| ID | Skenario | Hasil yang diharapkan |
|---|---|---|
| T-01 | Daftar dengan NIM valid dan KTM | Akun berstatus menunggu, tidak dapat login |
| T-02 | Daftar dengan NIM yang kode prodinya ada di tabel tetapi tidak cocok dengan prodi dipilih | Ditolak |
| T-03 | Daftar akun baru dengan NIM yang sudah dipakai (termasuk berstatus ditolak) | Ditolak sebagai akun baru; tersedia jalur pengajuan ulang terbatas untuk akun ditolak |
| T-04 | Daftar dengan NIM yang kode prodinya tidak ada di tabel | Diterima, berstatus menunggu dengan penanda untuk admin |
| T-05 | Admin menyetujui akun | Akun dapat login |
| T-06 | Pengunjung membuka katalog, memakai filter kategori, merek, RAM, dan harga | Hanya listing aktif tampil, filter bekerja, nomor WhatsApp tidak tampil |
| T-07 | Beli lapak lewat pembayaran sandbox sampai selesai | Lapak aktif, listing tayang; waktu persetujuan syarat disalin dari transaksi |
| T-08 | Kirim webhook dengan tanda tangan tidak valid | Ditolak, lapak tidak dibuat |
| T-09 | Kirim webhook yang sama dua kali atau paralel | Hanya satu lapak dan satu listing dibuat, `lapak_id` di transaksi tidak berduplikasi |
| T-10 | Kirim webhook dengan jumlah tidak sama dengan harga tersimpan | Ditolak |
| T-11 | Transaksi pending melewati `expires_at` | Direkonsiliasi seperlunya, lalu expire; foto/payload dibersihkan saat final |
| T-12 | Pembayaran masuk setelah status lokal cancel/expire | `gateway_status` dicatat, `perlu_tinjauan` diset, tidak membuat lapak otomatis |
| T-13 | Pembeli login menekan tombol WhatsApp | Klik tercatat (pembeli, listing, waktu), diarahkan ke WhatsApp penjual |
| T-14 | Pengunjung belum login menekan tombol WhatsApp | Diarahkan ke login, klik tidak tercatat |
| T-15 | Penjual menandai listing terjual | Hilang dari katalog, waktu terjual tercatat |
| T-16 | Admin hide listing dengan kode alasan | Hilang dari katalog, penjual menerima notifikasi beserta alasan, lapak berstatus jeda |
| T-17 | Admin menampilkan kembali listing | Muncul di katalog, tanggal berakhir bergeser sebesar lamanya jeda |
| T-18 | Pengguna non-admin membuka rute admin | Ditolak (403) |
| T-19 | Penjual A mencoba mengedit listing penjual B | Ditolak |
| T-20 | Akses berkas KTM lewat URL langsung tanpa login admin | Ditolak |
| T-21 | Kirim field `role=admin` lewat form registrasi | Diabaikan, akun tetap peran user |
| T-22 | Jalankan perintah proses berbasis waktu dua kali berturut-turut | Tidak ada efek ganda |


## Lampiran C. Koreksi v0.5–v0.6, transisi status dan kasus uji tambahan

### C.1 Transisi inti yang harus dijaga

| Objek | Kondisi awal | Pemicu | Hasil sah | Larangan |
|---|---|---|---|---|
| Akun | `ditolak` | Pemilik akun autentikasi terbatas dan ajukan ulang | `menunggu`, KTM baru privat | Membuat akun kedua dengan NIM sama |
| Transaksi | `pending` | Webhook valid sebelum status final | `paid`, buat satu lapak + listing atomik | Membuat lapak dari callback browser |
| Transaksi | `cancel`/`expire` | Webhook gateway menunjukkan pembayaran berhasil | Tetap status lokal final, `perlu_tinjauan=true` | Membuat lapak otomatis |
| Listing/lapak | `aktif`/`aktif` | Admin hide, alasan M1-M8 | `tersembunyi`/`jeda`, catat `paused_at` | Hide terhadap listing terjual/habis |
| Listing/lapak | `tersembunyi`/`jeda` | Admin restore | `aktif`/`aktif`, geser `expires_at` satu kali | Restore jika lapak ditutup |
| Listing/lapak | `aktif`/`aktif` | Penjual tandai terjual | `terjual`/`aktif` | Tetap tayang di katalog |
| Listing/lapak | `terjual`/`aktif` | Penjual pasang laptop pengganti | Listing baru `aktif`, riwayat lama tetap `terjual` | Dua listing aktif sekaligus |
| Moderasi | `menunggu` | Penjual banding tepat waktu | `banding`, tunggu admin | Penutupan karena tenggat penjual saat admin memeriksa |
| Moderasi | `banding` | Admin tolak banding 1/2 | `menunggu`, tenggat penjual berikutnya +3 hari | Menutup sebelum kesempatan berikutnya berakhir |
| Moderasi | `banding` | Admin tolak banding 3 | `hangus`, lapak `ditutup` | Restore lewat tombol P0 |
| Moderasi | `menunggu` | Tenggat pengajuan terlewat, tak ada banding menunggu | `tanpa_banding`, lapak `ditutup` | Menutup saat status `banding` |

### C.2 Kasus uji tambahan v0.5

| ID | Skenario | Hasil yang diharapkan |
|---|---|---|
| T-23 | Pendaftar ditolak mengajukan ulang dengan NIM dan kata sandi benar | Akun yang sama kembali `menunggu`, tidak ada duplikasi NIM |
| T-24 | Pendaftar ditolak mencoba login dashboard biasa | Ditolak, hanya jalur pengajuan ulang yang tersedia |
| T-25 | Admin menolak atau menyetujui verifikasi | Berkas KTM terhapus dan `ktm_path` NULL |
| T-26 | Dua request menambah listing aktif bersamaan pada satu lapak | Maksimal satu listing aktif di database |
| T-27 | Penjual menandai laptop terjual, lalu menambah laptop lain | Listing lama historis, baru aktif jika lapak masih berlaku |
| T-28 | Penjual mencoba mengganti laptop saat lapak jeda | Ditolak; listing tersembunyi hanya boleh diperbaiki |
| T-29 | Dua proses restore moderasi bersamaan | Penambahan waktu jeda hanya sekali |
| T-30 | Webhook pembayaran dan proses expire tiba hampir bersamaan | Status konsisten; tidak ada lapak ganda atau kehilangan catatan tinjauan |
| T-31 | Banding sedang menunggu keputusan admin melewati tenggat penjual | Lapak tetap jeda, tidak ditutup FR-83 |
| T-32 | Admin restore listing langsung tanpa banding | Moderasi `pulih`, listing aktif, penjual dinotifikasi |
| T-33 | Penjual mencoba mengakses berkas KTM orang lain | Ditolak |
| T-34 | Dua request membuat laporan aktif oleh pembeli sama pada listing sama | Satu sukses, satu ditolak oleh UNIQUE MySQL; Laravel menampilkan pesan yang sesuai |
| T-35 | Laporan pertama menjadi `selesai`, lalu pembeli membuat laporan baru untuk listing sama | Berhasil; laporan selesai tetap menjadi riwayat |
| T-36 | Login NIM + kata sandi benar milik akun `disetujui` | Sesi login sah; username tidak dipakai sebagai kredensial |
| T-37 | Login menggunakan username atau salah kata sandi | Ditolak tanpa mengungkap keberadaan NIM |
| T-38 | Jalankan migrasi pada MySQL 8.0+ | Semua FK/index/generated column berhasil dibuat dan dapat dibuktikan dengan SHOW CREATE TABLE |

**Catatan akhir:** prioritas P0 adalah target demo. Keputusan final v0.6 ialah Vue 3, login NIM + kata sandi, pengajuan ulang akun ditolak pada akun lama, dan UNIQUE laporan aktif di database + validasi Laravel. Verifikasi kode NIM, tarif Midtrans, bukti alumni, serta usulan lain tetap menunggu keputusan/validasi sesuai Bagian 14/16.
