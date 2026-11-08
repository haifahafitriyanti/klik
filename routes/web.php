<?php

use App\Http\Controllers\Admin\VerificationController;
use App\Http\Controllers\Auth\VerificationStatusController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('katalog', [CatalogController::class, 'index'])->name('catalog.index');

Route::middleware(['auth', 'approved'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::get('verifikasi/status', [VerificationStatusController::class, 'show'])->name('verification.status');
    Route::post('verifikasi/ktm', [VerificationStatusController::class, 'uploadKtm'])->name('verification.ktm.upload');
});

Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [VerificationController::class, 'index'])->name('admin.dashboard');
    Route::get('verifikasi/{user}/ktm', [VerificationController::class, 'ktm'])->name('admin.verifikasi.ktm');
    Route::post('verifikasi/{user}/setujui', [VerificationController::class, 'approve'])->name('admin.verifikasi.approve');
    Route::post('verifikasi/{user}/tolak', [VerificationController::class, 'reject'])->name('admin.verifikasi.reject');
});

require __DIR__.'/settings.php';
