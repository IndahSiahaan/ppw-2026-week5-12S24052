<x-layout title="SIPUS-Del - Kelola Buku">
    <div class="row g-4">
        <!-- Form Tambah Buku -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">Tambah Buku Baru</div>
                <div class="card-body">
                    <form action="{{ route('buku.store') }}" method="POST">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label">ISBN (13 digit)</label>
                            <input type="text" name="isbn" class="form-control" value="{{ old('isbn') }}" required placeholder="9786020000000">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Judul Buku</label>
                            <input type="text" name="judul" class="form-control" value="{{ old('judul') }}" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Penulis</label>
                            <input type="text" name="penulis" class="form-control" value="{{ old('penulis') }}" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Penerbit</label>
                            <input type="text" name="penerbit" class="form-control" value="{{ old('penerbit') }}" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Tahun Terbit</label>
                            <input type="number" name="tahun_terbit" class="form-control" value="{{ old('tahun_terbit', 2024) }}" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Kategori</label>
                            <select name="kategori_id" class="form-select" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($kategoris as $kat)
                                    <option value="{{ $kat->id }}" {{ old('kategori_id') == $kat->id ? 'selected' : '' }}>
                                        {{ $kat->kode_kategori }} - {{ $kat->nama_kategori }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Stok</label>
                            <input type="number" name="stok" class="form-control" value="{{ old('stok', 1) }}" required>
                        </div>
                        <button type="submit" class="btn btn-del w-100">Simpan Buku</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tabel Daftar Buku -->
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">Daftar Koleksi Buku Perpustakaan</div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ISBN</th>
                                <th>Judul</th>
                                <th>Kategori</th>
                                <th>Penerbit/Tahun</th>
                                <th>Stok</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bukus as $b)
                                <tr>
                                    <td><small class="text-muted">{{ $b->isbn }}</small></td>
                                    <td class="fw-semibold">{{ $b->judul }}<br><small class="text-muted">By: {{ $b->penulis }}</small></td>
                                    <td><span class="badge bg-secondary">{{ $b->kategori->nama_kategori ?? '-' }}</span></td>
                                    <td>{{ $b->penerbit }} ({{ $b->tahun_terbit }})</td>
                                    <td><span class="badge bg-info text-dark">{{ $b->stok }} pcs</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Koleksi buku belum tersedia.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-3">
                {{ $bukus->links() }}
            </div>
        </div>
    </div>
</x-layout>