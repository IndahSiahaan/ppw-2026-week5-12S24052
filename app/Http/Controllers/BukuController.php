<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BukuController extends Controller
{
    public function index(Request $request)
    {
        // Simpan riwayat pencarian terakhir ke sesi (persyaratan modul)
        if ($request->filled('q')) {
            session(['last_search_query' => $request->q]);
        }

        // 1. STATISTIK PERPUSTAKAAN MENGGUNAKAN FLUENT QUERY BUILDER (DB::table)
        $stats = [
            'total_judul'        => DB::table('bukus')->count(),
            'total_stok'         => DB::table('bukus')->sum('stok'),
            'total_dipinjam'     => DB::table('peminjamans')->where('status', 'dipinjam')->count(),
            'total_dikembalikan' => DB::table('peminjamans')->where('status', 'dikembalikan')->count(),
        ];

        // 2. KATALOG BUKU DENGAN withCount('peminjamans') DAN FILTER DINAMIS when()
        $bukus = Buku::with('kategori')
            ->withCount('peminjamans')
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = $request->q;
                $query->where(function ($q) use ($search) {
                    $q->where('judul', 'like', "%{$search}%")
                      ->orWhere('penulis', 'like', "%{$search}%")
                      ->orWhere('isbn', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('kategori_id'), function ($query) use ($request) {
                $query->where('kategori_id', $request->kategori_id);
            })
            ->latest()
            ->paginate(5)
            ->withQueryString();

        $kategoris = Kategori::all();

        // Ambil data peminjaman untuk tabel riwayat & pengembalian buku
        $peminjamans = Peminjaman::with('buku')->latest()->paginate(5, ['*'], 'pinjam_page');

        return view('buku.index', compact('bukus', 'kategoris', 'stats', 'peminjamans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'isbn'         => 'required|string|size:13|unique:bukus,isbn',
            'judul'        => 'required|string|min:5|max:200',
            'penulis'      => 'required|string|max:150',
            'penerbit'     => 'required|string|max:100',
            'tahun_terbit' => 'required|digits:4|integer|min:1900|max:2026',
            'kategori_id'  => 'required|exists:kategoris,id',
            'stok'         => 'required|integer|min:0',
            'sinopsis'     => 'nullable|string',
        ]);

        Buku::create($validated);

        return redirect()->route('buku.index')->with('success', 'Buku baru berhasil ditambahkan!');
    }
}