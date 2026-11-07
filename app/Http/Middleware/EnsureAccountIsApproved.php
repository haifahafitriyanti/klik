<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsApproved
{
    /**
     * Pastikan akun pengguna telah disetujui untuk mengakses fitur terproteksi.
     * Jika akun masih menunggu atau ditolak, alihkan ke halaman status verifikasi.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Administrator memiliki akses penuh ke fitur aplikasi
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Pengguna yang belum disetujui (menunggu atau ditolak) dialihkan ke status verifikasi
        if (! $user->isApproved()) {
            return redirect()->route('verification.status');
        }

        return $next($request);
    }
}
