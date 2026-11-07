<?php

use App\Http\Controllers\Auth\VerificationStatusController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'approved'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::get('verifikasi/status', [VerificationStatusController::class, 'show'])->name('verification.status');
    Route::post('verifikasi/ktm', [VerificationStatusController::class, 'uploadKtm'])->name('verification.ktm.upload');
});

Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::inertia('/', 'admin/Dashboard')->name('admin.dashboard');
});

require __DIR__.'/settings.php';
