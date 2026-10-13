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
        // 1. Akun Pustakawan Uji (Sesuai Modul Minggu 6)
        User::updateOrCreate(
            ['email' => 'pustakawan@del.ac.id'],
            [
                'name'     => 'Pustakawan Del',
                'password' => Hash::make('password123'),
            ]
        );

        // 2. Kategori Sesuai Kurikulum SI & FITE (5 Kategori)
        $kategoriList = [
            ['kode_kategori' => 'KAT-01', 'nama_kategori' => 'Teknologi Informasi'],
            ['kode_kategori' => 'KAT-02', 'nama_kategori' => 'Sistem Informasi'],
            ['kode_kategori' => 'KAT-03', 'nama_kategori' => 'Ilmu Komputer'],
            ['kode_kategori' => 'KAT-04', 'nama_kategori' => 'Matematika & Sains'],
            ['kode_kategori' => 'KAT-05', 'nama_kategori' => 'Manajemen & Bisnis'],
        ];

        $katModels = [];
        foreach ($kategoriList as $kat) {
            $katModels[$kat['kode_kategori']] = Kategori::updateOrCreate(
                ['kode_kategori' => $kat['kode_kategori']],
                $kat
            );
        }

        // 3. Daftar 20 Buku Lengkap Seperti di Week 5
        // Catatan: Buku 1 diset stok=0 (uji rollback) & Buku 2 diset stok=1 (uji lockForUpdate) sesuai modul Week 6
        $bukuList = [
            [
                'isbn' => '9786020000001',
                'judul' => 'Pemrograman dan Pengujian Web Modern dengan Laravel 11',
                'penulis' => 'Chandro Pardede',
                'penerbit' => 'IT Del Press',
                'tahun_terbit' => 2026,
                'kategori_id' => $katModels['KAT-01']->id,
                'stok' => 0, // STOK 0: Untuk menguji penolakan ACID transaksi saat stok habis
                'sinopsis' => 'Panduan praktis implementasi arsitektur MVC, Eloquent ORM, dan pengujian aplikasi web.'
            ],
            [
                'isbn' => '9786020000002',
                'judul' => 'Analisis dan Perancangan Sistem Informasi Bisnis',
                'penulis' => 'Indah Siahaan',
                'penerbit' => 'Informatika Bandung',
                'tahun_terbit' => 2025,
                'kategori_id' => $katModels['KAT-02']->id,
                'stok' => 1, // STOK 1: Untuk menguji lockForUpdate dan concurrency
                'sinopsis' => 'Metode pemodelan UML, BPMN, dan analisis kebutuhan sistem informasi enterprise.'
            ],
            [
                'isbn' => '9786020000003',
                'judul' => 'Manajemen Basis Data Relasional dan NoSQL',
                'penulis' => 'Budi Raharjo',
                'penerbit' => 'Andi Publisher',
                'tahun_terbit' => 2024,
                'kategori_id' => $katModels['KAT-03']->id,
                'stok' => 20,
                'sinopsis' => 'Konsep perancangan ERD, optimasi kueri SQL, dan tata kelola basis data enterprise.'
            ],
            [
                'isbn' => '9786020000004',
                'judul' => 'Matematika Diskrit untuk Ilmu Komputer',
                'penulis' => 'Rinaldi Munir',
                'penerbit' => 'Informatika',
                'tahun_terbit' => 2023,
                'kategori_id' => $katModels['KAT-04']->id,
                'stok' => 10,
                'sinopsis' => 'Teori himpunan, logika matematika, graf, dan kombinatorika untuk mahasiswa komputer.'
            ],
            [
                'isbn' => '9786020000005',
                'judul' => 'Tata Kelola Teknologi Informasi (IT Governance)',
                'penulis' => 'Eko Indrajit',
                'penerbit' => 'Prehallindo',
                'tahun_terbit' => 2025,
                'kategori_id' => $katModels['KAT-05']->id,
                'stok' => 8,
                'sinopsis' => 'Kerangka kerja COBIT dan ITIL untuk manajemen audit dan risiko TI pada organisasi.'
            ],
            [
                'isbn' => '9786020000006',
                'judul' => 'Konsep Dasar Rekayasa Perangkat Lunak',
                'penulis' => 'Rosa A.S.',
                'penerbit' => 'Modula',
                'tahun_terbit' => 2024,
                'kategori_id' => $katModels['KAT-01']->id,
                'stok' => 25,
                'sinopsis' => 'Siklus hidup pengembangan perangkat lunak SDLC, metode Agile, dan Scrum.'
            ],
            [
                'isbn' => '9786020000007',
                'judul' => 'Sistem Informasi Manajemen Enterprise',
                'penulis' => 'Raymond McLeod',
                'penerbit' => 'Salemba Empat',
                'tahun_terbit' => 2023,
                'kategori_id' => $katModels['KAT-02']->id,
                'stok' => 14,
                'sinopsis' => 'Penerapan ERP, CRM, dan SCM dalam pengambilan keputusan bisnis strategic.'
            ],
            [
                'isbn' => '9786020000008',
                'judul' => 'Struktur Data dan Algoritma Pemrograman Java',
                'penulis' => 'Sani Kurniawan',
                'penerbit' => 'Gava Media',
                'tahun_terbit' => 2025,
                'kategori_id' => $katModels['KAT-03']->id,
                'stok' => 18,
                'sinopsis' => 'Implementasi stack, queue, linked list, tree, dan algoritma pencarian lanjutan.'
            ],
            [
                'isbn' => '9786020000009',
                'judul' => 'Aljabar Linear dan Matriks Terapan',
                'penulis' => 'Mahmud Imrona',
                'penerbit' => 'Rekayasa Sains',
                'tahun_terbit' => 2022,
                'kategori_id' => $katModels['KAT-04']->id,
                'stok' => 9,
                'sinopsis' => 'Vektor, transformasi matriks, perkalian titik, dan orthogonalitas dalam komputasi.'
            ],
            [
                'isbn' => '9786020000010',
                'judul' => 'Pengantar Bisnis Digital dan E-Commerce',
                'penulis' => 'Dave Chaffey',
                'penerbit' => 'Erlangga',
                'tahun_terbit' => 2026,
                'kategori_id' => $katModels['KAT-05']->id,
                'stok' => 11,
                'sinopsis' => 'Strategi pemasaran digital, arsitektur marketplace, dan model bisnis startup P2P.'
            ],
            [
                'isbn' => '9786020000011',
                'judul' => 'Jaringan Komputer dan Keamanan Siber',
                'penulis' => 'Andrew S. Tanenbaum',
                'penerbit' => 'Andi',
                'tahun_terbit' => 2025,
                'kategori_id' => $katModels['KAT-01']->id,
                'stok' => 16,
                'sinopsis' => 'Protokol TCP/IP, model OSI, analisis paket Wireshark, dan enkripsi data.'
            ],
            [
                'isbn' => '9786020000012',
                'judul' => 'Interaksi Manusia dan Komputer (UI/UX Design)',
                'penulis' => 'Dwi Prasetyo',
                'penerbit' => 'Informatika',
                'tahun_terbit' => 2024,
                'kategori_id' => $katModels['KAT-02']->id,
                'stok' => 22,
                'sinopsis' => 'Prinsip desain antarmuka, usability testing, dan pemodelan pengalaman pengguna.'
            ],
            [
                'isbn' => '9786020000013',
                'judul' => 'Kecerdasan Buatan dan Sistem Pakar',
                'penulis' => 'Sutojo T.',
                'penerbit' => 'Andi Publisher',
                'tahun_terbit' => 2025,
                'kategori_id' => $katModels['KAT-03']->id,
                'stok' => 13,
                'sinopsis' => 'Penerapan Constraint Satisfaction Problem (CSP), algoritma A*, dan backtracking.'
            ],
            [
                'isbn' => '9786020000014',
                'judul' => 'Kalkulus Lanjut untuk Informatika',
                'penulis' => 'Purcell & Varberg',
                'penerbit' => 'Erlangga',
                'tahun_terbit' => 2023,
                'kategori_id' => $katModels['KAT-04']->id,
                'stok' => 7,
                'sinopsis' => 'Diferensial, integral parsial, serta deret untuk pemodelan algoritma sains.'
            ],
            [
                'isbn' => '9786020000015',
                'judul' => 'Manajemen Proyek Sistem Informasi',
                'penulis' => 'Jack T. Marchewka',
                'penerbit' => 'Wiley Indonesia',
                'tahun_terbit' => 2025,
                'kategori_id' => $katModels['KAT-05']->id,
                'stok' => 17,
                'sinopsis' => 'Perencanaan estimasi biaya, analisis risiko proyek software, dan penjadwalan.'
            ],
            [
                'isbn' => '9786020000016',
                'judul' => 'Pengembangan Web Service dan RESTful API',
                'penulis' => 'Ahmad Mulyana',
                'penerbit' => 'Informatika',
                'tahun_terbit' => 2026,
                'kategori_id' => $katModels['KAT-01']->id,
                'stok' => 19,
                'sinopsis' => 'Pembangunan arsitektur microservices, otentikasi token JWT, dan pengujian API.'
            ],
            [
                'isbn' => '9786020000017',
                'judul' => 'Business Intelligence dan Data Warehousing',
                'penulis' => 'Efraim Turban',
                'penerbit' => 'Andi',
                'tahun_terbit' => 2024,
                'kategori_id' => $katModels['KAT-02']->id,
                'stok' => 15,
                'sinopsis' => 'Pengolahan OLAP, ekstraksi data ETL, dan perancangan dasbor eksekutif.'
            ],
            [
                'isbn' => '9786020000018',
                'judul' => 'Pemrograman Orientasi Objek dengan Java',
                'penulis' => 'Rismon Hasiholan',
                'penerbit' => 'Gava Media',
                'tahun_terbit' => 2025,
                'kategori_id' => $katModels['KAT-03']->id,
                'stok' => 21,
                'sinopsis' => 'Penerapan konsep Abstraksi, Enkapsulasi, Inheritansi, dan Polimorfisme.'
            ],
            [
                'isbn' => '9786020000019',
                'judul' => 'Metode Penelitian Sistem Informasi',
                'penulis' => 'Jogiyanto HM',
                'penerbit' => 'Andi Publisher',
                'tahun_terbit' => 2023,
                'kategori_id' => $katModels['KAT-04']->id,
                'stok' => 11,
                'sinopsis' => 'Metodologi studi kasus, kuesioner kuantitatif, dan penyusunan tugas akhir.'
            ],
            [
                'isbn' => '9786020000020',
                'judul' => 'Kewirausahaan Teknologi (Technopreneurship)',
                'penulis' => 'Irwan Santoso',
                'penerbit' => 'Informatika',
                'tahun_terbit' => 2026,
                'kategori_id' => $katModels['KAT-05']->id,
                'stok' => 25,
                'sinopsis' => 'Pengembangan produk TI, pitch deck, validation test, dan komersialisasi produk.'
            ],
        ];

        $firstCreatedBuku = null;
        foreach ($bukuList as $bukuData) {
            $created = Buku::updateOrCreate(
                ['isbn' => $bukuData['isbn']],
                $bukuData
            );
            if (!$firstCreatedBuku && $created->stok > 0) {
                $firstCreatedBuku = $created;
            }
        }

        // 4. Sampel Transaksi Peminjaman (Sesuai Modul Minggu 6)
        if ($firstCreatedBuku) {
            Peminjaman::updateOrCreate(
                ['kode_pinjam' => 'PJM-DEMO-001'],
                [
                    'buku_id'             => $firstCreatedBuku->id,
                    'nim_peminjam'        => '12S24052',
                    'nama_peminjam'       => 'Indah Triyuni Siahaan',
                    'tanggal_pinjam'      => now()->subDays(3)->toDateString(),
                    'tanggal_jatuh_tempo' => now()->addDays(4)->toDateString(),
                    'status'              => 'dipinjam',
                ]
            );
        }
    }
}