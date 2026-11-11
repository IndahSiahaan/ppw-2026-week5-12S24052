<?php

use App\Http\Controllers\Api\V1\AuthTokenController;
use App\Http\Controllers\Api\V1\BukuApiController;
use App\Http\Controllers\Api\V1\PeminjamanApiController;
use App\Http\Controllers\Soap\LibrarySoapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — SIPUS-Del v1
|--------------------------------------------------------------------------
*/

// ── SOAP Endpoints ──────────────────────────────────────────────────────
Route::prefix('v1/soap')->group(function () {
    Route::get('/sipus/wsdl', [LibrarySoapController::class, 'wsdl']);
    Route::post('/sipus',     [LibrarySoapController::class, 'handle']);
});

// ── REST v1 ─────────────────────────────────────────────────────────────
Route::prefix('v1')->group(function () {

    // Auth
    Route::post('/auth/login',  [AuthTokenController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me',     [AuthTokenController::class, 'me']);
        Route::post('/auth/logout', [AuthTokenController::class, 'logout']);
    });

    // Buku — read public
    Route::get('/buku',                            [BukuApiController::class, 'index']);
    Route::get('/buku/{id}',                       [BukuApiController::class, 'show']);
    Route::get('/buku/{id}/referensi-eksternal',   [BukuApiController::class, 'referensiEksternal']);

    // Buku — write protected
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/buku',          [BukuApiController::class, 'store']);
        Route::put('/buku/{id}',      [BukuApiController::class, 'update']);
        Route::patch('/buku/{id}',    [BukuApiController::class, 'update']);
        Route::delete('/buku/{id}',   [BukuApiController::class, 'destroy']);

        // Peminjaman
        Route::get('/peminjamans',                       [PeminjamanApiController::class, 'index']);
        Route::post('/peminjamans',                      [PeminjamanApiController::class, 'store']);
        Route::get('/peminjamans/{id}',                  [PeminjamanApiController::class, 'show']);
        Route::patch('/peminjamans/{id}/kembalikan',     [PeminjamanApiController::class, 'kembalikan']);

        // Alias singular /peminjaman
        Route::get('/peminjaman',                        [PeminjamanApiController::class, 'index']);
        Route::post('/peminjaman',                       [PeminjamanApiController::class, 'store']);
        Route::get('/peminjaman/{id}',                   [PeminjamanApiController::class, 'show']);
        Route::patch('/peminjaman/{id}/kembalikan',      [PeminjamanApiController::class, 'kembalikan']);
    });
});
