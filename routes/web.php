<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\PeminjamanController;
use Illuminate\Support\Facades\Route;

// Halaman utama langsung diarahkan ke katalog buku
Route::get('/', fn() => redirect()->route('buku.index'));

// Rute Publik (Bisa diakses siapa saja untuk melihat katalog)
Route::get('/buku', [BukuController::class, 'index'])->name('buku.index');

// Rute Autentikasi Pustakawan Web Blade
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rute Terproteksi Auth Web Blade
Route::middleware('auth')->group(function () {
    Route::post('/buku', [BukuController::class, 'store'])->name('buku.store');
    Route::post('/buku/{id}/pinjam', [PeminjamanController::class, 'pinjam'])->name('peminjaman.pinjam');
    Route::post('/peminjaman/{id}/kembali', [PeminjamanController::class, 'kembalikan'])->name('peminjaman.kembali');
});

use App\Http\Controllers\Soap\LibrarySoapController;

Route::get('/soap/sipus.wsdl', [LibrarySoapController::class, 'wsdl']);
Route::post('/soap/sipus', [LibrarySoapController::class, 'handle'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);