<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIPUS-Del - Sistem Perpustakaan Kampus Del</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .navbar-del { background-color: #4C1D95; }
        .btn-del { background-color: #4C1D95; color: #ffffff; }
        .btn-del:hover { background-color: #3B0764; color: #ffffff; }
        .badge-del { background-color: #4C1D95; }
    </style>
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-del shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('buku.index') }}">📚 SIPUS-Del Perpustakaan</a>
            <div class="d-flex align-items-center text-white">
                @auth
                    <span class="me-3 small">Halo, <strong>{{ Auth::user()->name }}</strong> (Pustakawan)</span>
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-light">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-sm btn-light text-dark fw-bold">Login Pustakawan</a>
                @endauth
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        <!-- Notifikasi Flash Message -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- 4 KARTU STATISTIK (FLUENT QUERY BUILDER) -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm border-0 bg-white p-3 text-center">
                    <small class="text-muted text-uppercase fw-semibold">Total Judul Buku</small>
                    <h2 class="fw-bold text-primary mb-0">{{ $stats['total_judul'] }}</h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 bg-white p-3 text-center">
                    <small class="text-muted text-uppercase fw-semibold">Total Stok Tersedia</small>
                    <h2 class="fw-bold text-success mb-0">{{ $stats['total_stok'] }}</h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 bg-white p-3 text-center">
                    <small class="text-muted text-uppercase fw-semibold">Buku Sedang Dipinjam</small>
                    <h2 class="fw-bold text-warning mb-0">{{ $stats['total_dipinjam'] }}</h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 bg-white p-3 text-center">
                    <small class="text-muted text-uppercase fw-semibold">Total Dikembalikan</small>
                    <h2 class="fw-bold text-secondary mb-0">{{ $stats['total_dikembalikan'] }}</h2>
                </div>
            </div>
        </div>

        <!-- FORM PENCARIAN & FILTER DINAMIS -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('buku.index') }}" class="row g-2">
                    <div class="col-md-6">
                        <input type="text" name="q" class="form-control" placeholder="Cari Judul, Penulis, atau ISBN..." value="{{ request('q') }}">
                    </div>
                    <div class="col-md-4">
                        <select name="kategori_id" class="form-select">
                            <option value="">Semua Kategori</option>
                            @foreach($kategoris as $k)
                                <option value="{{ $k->id }}" {{ request('kategori_id') == $k->id ? 'selected' : '' }}>
                                    {{ $k->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-del w-100">Cari</button>
                        <a href="{{ route('buku.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-4">
            <!-- TABEL KATALOG BUKU -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                        <span>Katalog Buku</span>
                        <span class="badge bg-secondary">Total: {{ $bukus->total() }}</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Judul & Penulis</th>
                                        <th>Kategori</th>
                                        <th>Stok</th>
                                        <th>Dipinjam</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($bukus as $buku)
                                        <tr>
                                            <td>
                                                <div class="fw-bold">{{ $buku->judul }}</div>
                                                <small class="text-muted">{{ $buku->penulis }} | ISBN: {{ $buku->isbn }}</small>
                                            </td>
                                            <td><span class="badge badge-del">{{ $buku->kategori->nama_kategori ?? '-' }}</span></td>
                                            <td>
                                                @if($buku->stok > 0)
                                                    <span class="badge bg-success">{{ $buku->stok }}</span>
                                                @else
                                                    <span class="badge bg-danger">Habis (0)</span>
                                                @endif
                                            </td>
                                            <td><span class="badge bg-info text-dark">{{ $buku->peminjamans_count }}x</span></td>
                                            <td class="text-center">
                                                @if($buku->stok > 0)
                                                    <button type="button" class="btn btn-sm btn-del" data-bs-toggle="modal" data-bs-target="#modalPinjam{{ $buku->id }}">
                                                        Pinjam
                                                    </button>
                                                @else
                                                    <button class="btn btn-sm btn-secondary" disabled>Stok Habis</button>
                                                @endif
                                            </td>
                                        </tr>

                                        <!-- MODAL FORM PINJAM BUKU -->
                                        <div class="modal fade" id="modalPinjam{{ $buku->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Form Pinjam: {{ $buku->judul }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="{{ route('peminjaman.pinjam', $buku->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">NIM Mahasiswa</label>
                                                                <input type="text" name="nim_peminjam" class="form-control" required placeholder="Contoh: 12S24052">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Nama Mahasiswa</label>
                                                                <input type="text" name="nama_peminjam" class="form-control" required placeholder="Nama Lengkap">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Tanggal Pinjam</label>
                                                                <input type="date" name="tanggal_pinjam" class="form-control" value="{{ date('Y-m-d') }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Lama Pinjam (Hari)</label>
                                                                <input type="number" name="lama_hari" class="form-control" value="7" min="1" max="14" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-del">Konfirmasi Pinjam</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">Belum ada buku yang cocok.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        {{ $bukus->links() }}
                    </div>
                </div>
            </div>

            <!-- FORM TAMBAH BUKU BARU (Pustakawan) -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white fw-bold">Tambah Buku Baru</div>
                    <div class="card-body">
                        <form action="{{ route('buku.store') }}" method="POST">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label">ISBN (13 Digit)</label>
                                <input type="text" name="isbn" class="form-control form-control-sm" required placeholder="Contoh: 9786020000001">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Judul Buku</label>
                                <input type="text" name="judul" class="form-control form-control-sm" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Penulis</label>
                                <input type="text" name="penulis" class="form-control form-control-sm" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Penerbit</label>
                                <input type="text" name="penerbit" class="form-control form-control-sm" required>
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label">Tahun Terbit</label>
                                    <input type="number" name="tahun_terbit" class="form-control form-control-sm" value="2024" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Stok</label>
                                    <input type="number" name="stok" class="form-control form-control-sm" value="5" min="0" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Kategori</label>
                                <select name="kategori_id" class="form-select form-select-sm" required>
                                    @foreach($kategoris as $k)
                                        <option value="{{ $k->id }}">{{ $k->nama_kategori }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-del btn-sm w-100">Simpan Buku</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABEL TRANSAKSI SIRKULASI PEMINJAMAN & PENGEMBALIAN BUKU -->
        <div class="card shadow-sm border-0 mt-4">
            <div class="card-header bg-white fw-bold">Riwayat Sirkulasi & Pengembalian Buku</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kode Pinjam</th>
                                <th>Buku</th>
                                <th>Peminjam (NIM)</th>
                                <th>Tgl Pinjam - Tempo</th>
                                <th>Status</th>
                                <th class="text-center">Aksi Pengembalian</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($peminjamans as $pjm)
                                <tr>
                                    <td><code>{{ $pjm->kode_pinjam }}</code></td>
                                    <td><strong>{{ $pjm->buku->judul ?? '-' }}</strong></td>
                                    <td>{{ $pjm->nama_peminjam }} <br><small class="text-muted">({{ $pjm->nim_peminjam }})</small></td>
                                    <td>
                                        <small>{{ $pjm->tanggal_pinjam }} s/d {{ $pjm->tanggal_jatuh_tempo }}</small>
                                        @if($pjm->tanggal_kembali)
                                            <br><small class="text-success">Kembali: {{ $pjm->tanggal_kembali }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($pjm->status === 'dipinjam')
                                            <span class="badge bg-warning text-dark">Dipinjam</span>
                                        @else
                                            <span class="badge bg-success">Dikembalikan</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($pjm->status === 'dipinjam')
                                            <form action="{{ route('peminjaman.kembali', $pjm->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success">
                                                    ✓ Kembalikan Buku
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted small">Selesai</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Belum ada transaksi peminjaman buku.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white">
                {{ $peminjamans->links() }}
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>