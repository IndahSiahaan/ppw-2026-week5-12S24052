<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PeminjamanController extends Controller
{
    // 1. FITUR PINJAM BUKU (ACID Transaction + Pessimistic Locking)
    public function pinjam(Request $request, int $bukuId)
    {
        $validated = $request->validate([
            'nim_peminjam'   => 'required|string|max:15',
            'nama_peminjam'  => 'required|string|max:150',
            'tanggal_pinjam' => 'required|date',
            'lama_hari'      => 'required|integer|min:1|max:14',
        ]);

        try {
            DB::transaction(function () use ($validated, $bukuId) {
                // KUNCI baris buku dengan lockForUpdate() agar aman dari Race Condition
                $buku = Buku::where('id', $bukuId)->lockForUpdate()->firstOrFail();

                // Validasi Stok
                if ($buku->stok < 1) {
                    throw new \RuntimeException("Maaf, stok buku '{$buku->judul}' sedang habis (0)!");
                }

                // Cek apakah NIM yang sama sedang meminjam buku ini dan belum dikembalikan
                $sedangPinjam = Peminjaman::where('buku_id', $bukuId)
                    ->where('nim_peminjam', $validated['nim_peminjam'])
                    ->where('status', 'dipinjam')
                    ->exists();

                if ($sedangPinjam) {
                    throw new \RuntimeException("NIM {$validated['nim_peminjam']} masih meminjam buku ini dan belum mengembalikannya!");
                }

                // Hitung tanggal jatuh tempo
                $tglPinjam = \Carbon\Carbon::parse($validated['tanggal_pinjam']);
                $tglJatuhTempo = $tglPinjam->copy()->addDays((int) $validated['lama_hari']);

                // Kurangi stok buku secara atomik
                $buku->decrement('stok');

                // Catat transaksi peminjaman
                Peminjaman::create([
                    'kode_pinjam'         => 'PJM-' . strtoupper(uniqid()),
                    'buku_id'             => $buku->id,
                    'nim_peminjam'        => strtoupper(trim($validated['nim_peminjam'])),
                    'nama_peminjam'       => $validated['nama_peminjam'],
                    'tanggal_pinjam'      => $tglPinjam->toDateString(),
                    'tanggal_jatuh_tempo' => $tglJatuhTempo->toDateString(),
                    'status'              => 'dipinjam',
                ]);

                // Simpan NIM terakhir ke Session & Cookie (Persyaratan Modul)
                session(['last_peminjam_nim' => $validated['nim_peminjam']]);
                cookie()->queue('last_peminjam_name', $validated['nama_peminjam'], 60);
            }, 3);

            return back()->with('success', 'Buku berhasil dipinjam secara aman & atomik!');
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            Log::error('[DB TRANSACTION FAILED]: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan basis data saat memproses peminjaman.');
        }
    }

    // 2. FITUR PENGEMBALIAN BUKU (ACID Transaction)
    public function kembalikan(int $id)
    {
        try {
            DB::transaction(function () use ($id) {
                $peminjaman = Peminjaman::where('id', $id)->lockForUpdate()->firstOrFail();

                if ($peminjaman->status === 'dikembalikan') {
                    throw new \RuntimeException("Buku ini sudah pernah dikembalikan sebelumnya.");
                }

                // Ubah status dan catat tanggal kembali hari ini
                $peminjaman->update([
                    'status'          => 'dikembalikan',
                    'tanggal_kembali' => now()->toDateString(),
                ]);

                // Kembalikan stok buku (tambah 1)
                $buku = Buku::where('id', $peminjaman->buku_id)->lockForUpdate()->firstOrFail();
                $buku->increment('stok');
            }, 3);

            return back()->with('success', 'Buku berhasil dikembalikan dan stok diperbarui!');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            Log::error('[DB RETURN FAILED]: ' . $e->getMessage());
            return back()->with('error', 'Gagal memproses pengembalian buku.');
        }
    }
}