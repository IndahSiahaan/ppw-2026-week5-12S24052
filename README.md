# 📚 SIPUS-Del (Tahap 2) - Sistem Informasi Perpustakaan Kampus Del

**Tugas Praktikum Minggu 06 — Pemrograman & Pengujian Web (12S3101)**  
Program Studi Sarjana Sistem Informasi • Fakultas Informatika dan Teknik Elektro  
**Institut Teknologi Del**

---

## 👤 Identitas Mahasiswa
* **Nama:** Indah Triyuni Siahaan
* **NIM:** 12S24052
* **Program Studi:** S1 Sistem Informasi
* **Kelas:** 12SI2
* **Tahun Akademik:** 2026/2027 (Semester Ganjil)

---

## 🔐 Kredensial Akun Uji Pustakawan
Untuk login dan menguji hak akses pustakawan (tambah buku, proses peminjaman & pengembalian buku):
* **URL Login:** `http://127.0.0.1:8000/login`
* **Email:** `pustakawan@del.ac.id`
* **Password:** `password123`

---

## 🚀 Fitur & Implementasi Praktikum Minggu 6

### 1. Skema Tabel `peminjamans` & Relasi Eloquent (Bobot: 20%)
* Tabel `peminjamans` memiliki kolom: `id`, `kode_pinjam` (unik), `buku_id` (foreign key `cascadeOnDelete`), `nim_peminjam`, `nama_peminjam`, `tanggal_pinjam`, `tanggal_jatuh_tempo`, `tanggal_kembali` (nullable), `status` (enum: dipinjam, dikembalikan), dan `timestamps`.
* **Relasi Eloquent:**
  * `Buku` hasMany `Peminjaman` (`$buku->peminjamans`)
  * `Peminjaman` belongsTo `Buku` (`$peminjaman->buku`)

### 2. Transaksi Basis Data ACID & Concurrency Locking (Bobot: 25%)
* **Peminjaman Buku:**
  * Dibungkus dengan `DB::transaction(function() { ... }, 3)`
  * Menggunakan **Pessimistic Locking** `Buku::where('id', $id)->lockForUpdate()->firstOrFail()` untuk mencegah *Race Condition* kuota/stok.
  * Memeriksa `stok > 0` (otomatis melempar pengecualian / rollback jika stok habis).
  * Validasi NIM agar mahasiswa tidak meminjam buku yang sama sebelum mengembalikannya.
  * Mutasi stok atomik: `$buku->decrement('stok')`.
* **Pengembalian Buku:**
  * Dibungkus dengan `DB::transaction()`.
  * Status diubah menjadi `'dikembalikan'` dan mencatat `tanggal_kembali`.
  * Mengembalikan stok buku secara atomik: `$buku->increment('stok')`.
  * Menangkap `QueryException` dan mencatatnya ke `Log::error()`.

### 3. Fluent Query Builder (`DB::table`) & Agregasi Dashboard (Bobot: 15%)
* Menghitung metrik analitik secara efisien:
  * Total Judul Buku: `DB::table('bukus')->count()`
  * Total Stok Tersedia: `DB::table('bukus')->sum('stok')`
  * Total Buku Sedang Dipinjam: `DB::table('peminjamans')->where('status', 'dipinjam')->count()`
  * Total Buku Dikembalikan: `DB::table('peminjamans')->where('status', 'dikembalikan')->count()`
* Optimasi Eager Loading & Bebas N+1: `Buku::with('kategori')->withCount('peminjamans')`.
* Pencarian dinamis menggunakan filter conditional `when()`.

### 4. Manajemen Sesi Pengguna, Cookie & Autentikasi (Bobot: 25%)
* **Proteksi Session Fixation:** Memanggil `$request->session()->regenerate()` saat login berhasil.
* **Logout Aman:** Memanggil `Auth::logout()`, `$request->session()->invalidate()`, dan `$request->session()->regenerateToken()`.
* **HTTP Cookie:** Menyimpan email terakhir menggunakan `Cookie::queue('sipus_last_email', ..., 10080)`.
* **Proteksi Rute:** Menggunakan `Route::middleware('auth')` untuk semua aksi mutasi data (tambah buku, pinjam, dan kembalikan).

---

## 🛠️ Panduan Instalasi & Menjalankan Aplikasi

1. **Clone Repositori:**
   ```bash
   git clone <url-repository>
   cd ppw-2026-week5-12S24052
   git checkout week6-db-session
   ```

2. **Konfigurasi Environment `.env`:**
   Pastikan konfigurasi database mengarah ke MySQL Laragon:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=ppw-week6
   DB_USERNAME=root
   DB_PASSWORD=
   ```

3. **Migrasi dan Seeding Data:**
   ```bash
   php artisan migrate:fresh --seed
   ```

4. **Jalankan Development Server:**
   ```bash
   php artisan serve
   ```
   Buka di peramban: `http://127.0.0.1:8000`

---

## 📸 Bukti Pengujian Praktikum (Audit DevTools & Transaksi)

*(Sertakan tangkapan layar pengujian berikut saat pelaporan tugas)*:
1. **Bukti Transaksi Peminjaman & Pengembalian Buku Berhasil**
2. **Bukti Penolakan Transaksi Saat Stok = 0 (Rollback Transaksi)**
3. **Inspeksi Cookie `HttpOnly` dan Sesi pada Browser DevTools (Tab Application -> Cookies)**
