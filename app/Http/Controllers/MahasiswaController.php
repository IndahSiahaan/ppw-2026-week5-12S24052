<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use Illuminate\Http\Request;

class MahasiswaController extends Controller
{
    public function index()
    {
        // Fetch data mahasiswa beserta relasi programStudi (Eager Loading)
        $mahasiswas = Mahasiswa::with('programStudi')->latest()->paginate(10);
        return view('mahasiswa.index', compact('mahasiswas'));
    }

    public function store(Request $request)
    {
        // Validasi input form
        $validated = $request->validate([
    'nim' => 'required|string|size:8|unique:mahasiswas,nim',
    'nama' => 'required|string|min:3|max:150',
    'email' => 'required|email|unique:mahasiswas,email',
    'program_studi_id' => 'required|exists:program_studis,id',
    'ipk' => 'required|numeric|between:0.00,4.00',
]);

        Mahasiswa::create($validated);

        return redirect()->route('mahasiswa.index')->with('success', 'Data mahasiswa berhasil ditambahkan!');
    }
}