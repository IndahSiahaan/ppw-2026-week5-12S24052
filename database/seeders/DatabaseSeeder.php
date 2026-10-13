<?php

namespace Database\Seeders;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Pustakawan Uji (Sesuai Modul)
        User::updateOrCreate(
            ['email' => 'pustakawan@del.ac.id'],
            [
                'name'     => 'Pustakawan Del',
                'password' => Hash::make('password123'),
            ]
        );

        // 2. Kategori Buku
        $kategoriList = [
            ['kode_kategori' => 'PROG', 'nama_kategori' => 'Pemrograman & Rekayasa Web'],
            ['kode_kategori' => 'DATA', 'nama_kategori' => 'Basis Data & SQL'],
            ['kode_kategori' => 'NET',  'nama_kategori' => 'Jaringan Komputer'],
            ['kode_kategori' => 'AI',   'nama_kategori' => 'Kecerdasan Buatan'],
            ['kode_kategori' => 'SI',   'nama_kategori' => 'Sistem Informasi'],
        ];

        $kategoris = [];
        foreach ($kategoriList as $k) {
            $kategoris[$k['kode_kategori']] = Kategori::updateOrCreate(
                ['kode_kategori' => $k['kode_kategori']],
                $k
            );
        }

        // 3. Buku Sampel (Termasuk Stok = 0 untuk Uji Rollback, & Stok = 1 untuk Concurrency)
        $bukuHabis = Buku::updateOrCreate(
            ['isbn' => '9786020000001'],
            [
                'judul'        => 'Mastering Laravel 11 Architecture',
                'penulis'      => 'Chandro Pardede',
                'penerbit'     => 'Del Press',
                'tahun_terbit' => 2024,
                'kategori_id'  => $kategoris['PROG']->id,
                'stok'         => 0, // STOK 0: Untuk menguji penolakan ACID transaksi!
                'sinopsis'     => 'Buku arsitektur Laravel lanjutan.',
            ]
        );

        $bukuSatu = Buku::updateOrCreate(
            ['isbn' => '9786020000002'],
            [
                'judul'        => 'Basis Data Relasional & Transaksi ACID',
                'penulis'      => 'Indah Siahaan',
                'penerbit'     => 'Informatika IT Del',
                'tahun_terbit' => 2024,
                'kategori_id'  => $kategoris['DATA']->id,
                'stok'         => 1, // STOK 1: Untuk menguji lockForUpdate
                'sinopsis'     => 'Panduan transaksi ACID dan lock database.',
            ]
        );

        $bukuNormal = Buku::updateOrCreate(
            ['isbn' => '9786020000003'],
            [
                'judul'        => 'Analisis Sistem Informasi Modern',
                'penulis'      => 'Tim Dosen FITE',
                'penerbit'     => 'Del Publishing',
                'tahun_terbit' => 2023,
                'kategori_id'  => $kategoris['SI']->id,
                'stok'         => 5,
                'sinopsis'     => 'Desain sistem dan pemodelan bisnis.',
            ]
        );

        // 4. Sampel Transaksi Peminjaman
        Peminjaman::updateOrCreate(
            ['kode_pinjam' => 'PJM-DEMO-001'],
            [
                'buku_id'             => $bukuNormal->id,
                'nim_peminjam'        => '12S24052',
                'nama_peminjam'       => 'Indah Triyuni',
                'tanggal_pinjam'      => now()->subDays(3)->toDateString(),
                'tanggal_jatuh_tempo' => now()->addDays(4)->toDateString(),
                'status'              => 'dipinjam',
            ]
        );
    }
}