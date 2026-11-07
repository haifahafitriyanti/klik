<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotSuspended
{
    /**
     * Pastikan akun yang sedang mengakses tidak dalam status suspended.
     * Jika akun dibekukan, segera putuskan session dan alihkan ke login.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isSuspended()) {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'nim' => 'Akun Anda sedang ditangguhkan. Silakan hubungi administrator.',
            ]);
        }

        return $next($request);
    }
}
