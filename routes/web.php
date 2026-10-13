<?php

use App\Http\Controllers\BukuController;
use App\Http\Controllers\MahasiswaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PeminjamanController;

Route::get('/', fn() => redirect()->route('buku.index'));
Route::resource('mahasiswa', MahasiswaController::class);
Route::resource('buku', BukuController::class);
Route::post('/buku/{id}/pinjam', [PeminjamanController::class, 'pinjam'])->name('peminjaman.pinjam');
Route::post('/peminjaman/{id}/kembali', [PeminjamanController::class, 'kembalikan'])->name('peminjaman.kembali');