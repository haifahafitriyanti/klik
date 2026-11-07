<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class VerificationStatusController extends Controller
{
    /**
     * Tampilkan halaman status verifikasi akun untuk pengguna yang login.
     */
    public function show(Request $request): Response|RedirectResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->isApproved()) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('auth/VerificationStatus', [
            'user' => [
                'name' => $user->name,
                'nim' => $user->nim,
                'status_verifikasi' => $user->status_verifikasi,
                'alasan_penolakan' => $user->alasan_penolakan,
                'has_ktm' => ! empty($user->ktm_path),
            ],
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Unggah foto KTM pertama kali atau pengajuan ulang verifikasi akun yang ditolak.
     */
    public function uploadKtm(Request $request): RedirectResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->isAdmin()) {
            abort(403, 'Aksi ini hanya untuk akun pengguna/mahasiswa.');
        }

        if ($user->isSuspended()) {
            abort(403, 'Akun Anda sedang ditangguhkan.');
        }

        if ($user->isApproved()) {
            return redirect()->route('dashboard');
        }

        if ($user->status_verifikasi === 'menunggu' && ! empty($user->ktm_path)) {
            return redirect()->route('verification.status')->withErrors([
                'ktm' => 'Dokumen KTM Anda sudah terunggah dan sedang dalam antrean verifikasi admin.',
            ]);
        }

        $request->validate([
            'ktm' => [
                'required',
                File::image()
                    ->types(['jpeg', 'jpg', 'png', 'webp'])
                    ->max(5 * 1024),
            ],
        ], [
            'ktm.required' => 'Foto KTM wajib diunggah.',
            'ktm.image' => 'File harus berupa gambar.',
            'ktm.mimes' => 'Format file harus berupa JPG, JPEG, PNG, atau WebP.',
            'ktm.types' => 'Format file harus berupa JPG, JPEG, PNG, atau WebP.',
            'ktm.max' => 'Ukuran file foto KTM maksimal 5 MB.',
        ]);

        $uploadedFile = $request->file('ktm');
        if (! $uploadedFile) {
            return back()->withErrors(['ktm' => 'File tidak ditemukan.']);
        }

        // Tentukan ekstensi dan nama file acak yang aman di server
        $extension = $uploadedFile->guessExtension() ?: 'jpg';
        $storedFilename = 'ktm_'.Str::random(40).'.'.$extension;

        // Simpan file ke disk privat ktm
        $storedPath = Storage::disk('ktm')->putFileAs('', $uploadedFile, $storedFilename);

        if (! $storedPath) {
            return back()->withErrors(['ktm' => 'Gagal menyimpan file ke penyimpanan privat.']);
        }

        $oldKtmPath = null;

        try {
            DB::transaction(function () use ($user, $storedFilename, &$oldKtmPath) {
                /** @var User $lockedUser */
                $lockedUser = User::where('id', $user->id)->lockForUpdate()->firstOrFail();

                // Pengecekan ulang terhadap kemungkinan race condition
                if ($lockedUser->status_verifikasi === 'menunggu' && ! empty($lockedUser->ktm_path)) {
                    throw new DomainException('Dokumen KTM Anda sudah terunggah dan sedang dalam antrean verifikasi admin.');
                }

                if ($lockedUser->isApproved() || $lockedUser->isAdmin()) {
                    throw new DomainException('Aksi tidak diizinkan untuk status akun Anda.');
                }

                $oldKtmPath = $lockedUser->ktm_path;

                // Perbarui record di database
                $lockedUser->ktm_path = $storedFilename;
                $lockedUser->status_verifikasi = 'menunggu';
                $lockedUser->alasan_penolakan = null;
                $lockedUser->verified_by = null;
                $lockedUser->verified_at = null;
                $lockedUser->save();
            });
        } catch (DomainException $e) {
            // Hapus file baru jika validasi bisnis pada transaksi terkunci gagal
            Storage::disk('ktm')->delete($storedFilename);

            return redirect()->route('verification.status')->withErrors([
                'ktm' => $e->getMessage(),
            ]);
        } catch (Throwable $e) {
            // Hapus file baru jika terjadi kegagalan transaksi database
            Storage::disk('ktm')->delete($storedFilename);

            throw $e;
        }

        // Jika transaksi berhasil dan terdapat file KTM lama, bersihkan file lama dengan aman
        if ($oldKtmPath && $oldKtmPath !== $storedFilename && Storage::disk('ktm')->exists($oldKtmPath)) {
            Storage::disk('ktm')->delete($oldKtmPath);
        }

        return redirect()->route('verification.status')->with(
            'status',
            'Foto KTM berhasil diunggah dan sedang menunggu verifikasi administrator.'
        );
    }
}
