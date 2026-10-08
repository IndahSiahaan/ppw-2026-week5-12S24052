<?php

use App\Http\Controllers\BukuController;
use App\Http\Controllers\MahasiswaController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('buku.index'));
Route::resource('mahasiswa', MahasiswaController::class);
Route::resource('buku', BukuController::class);