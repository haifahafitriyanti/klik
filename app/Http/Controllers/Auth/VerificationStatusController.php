<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

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
            ],
        ]);
    }
}
