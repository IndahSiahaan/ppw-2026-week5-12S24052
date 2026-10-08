<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Http\Request;

class BukuController extends Controller
{
    public function index()
    {
        $bukus = Buku::with('kategori')->latest()->paginate(10);
        $kategoris = Kategori::all();
        return view('buku.index', compact('bukus', 'kategoris'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'isbn' => 'required|string|size:13|unique:bukus,isbn',
            'judul' => 'required|string|min:5|max:200',
            'penulis' => 'required|string|max:150',
            'penerbit' => 'required|string|max:100',
            'tahun_terbit' => 'required|digits:4|integer|min:1900|max:2026',
            'kategori_id' => 'required|exists:kategoris,id',
            'stok' => 'required|integer|min:0',
            'sinopsis' => 'nullable|string',
        ]);

        Buku::create($validated);

        return redirect()->route('buku.index')->with('success', 'Buku baru berhasil ditambahkan!');
    }
}