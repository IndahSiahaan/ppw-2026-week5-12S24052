# SIPUS-Del — Sistem Informasi Perpustakaan Kampus Del
## Tugas Praktikum Minggu 09: Web Services & REST API (SOAP + RESTful API v1)

---

## 1. Analisis Kebutuhan & Kelayakan (Feasibility) Web Services

### 1.1 Matriks Peran SIPUS-Del: Provider vs Consumer
| Layanan / Sistem Terhubung | Protokol / Format | Peran SIPUS-Del | Alasan Bisnis & Teknis |
|---|---|---|---|
| **Sistem Administrasi Yudisium (Legacy)** | SOAP / XML RPC | **Service Provider** | Memfasilitasi sistem kampus lama untuk memeriksa status bebas pustaka (`CheckClearanceStatus`) mahasiswa secara terstandarisasi dokumen WSDL formal. |
| **Aplikasi Mobile & Kiosk Perpustakaan** | RESTful / JSON | **Service Provider** | Menyediakan layanan katalog buku, pencarian, dan sirkulasi peminjaman/pengembalian buku yang cepat, stateless (Bearer Token Sanctum), serta mendukung HATEOAS navigasi dinamis. |
| **OpenLibrary.org REST API** | REST / JSON | **Service Consumer** | Memperkaya metadata buku (penerbit, deskripsi, preview) otomatis berdasarkan nomor ISBN tanpa membebani penyimpanan lokal. |

### 1.2 Mengapa REST Dipilih untuk Mobile & Kiosk Dibandingkan SOAP?
1. **Efisiensi Bandwidth & Parsing (Payload Size):** JSON berukuran 5–8x lebih ringkas dibanding XML SOAP yang memiliki overhead tag `<soap:Envelope>`, `<soap:Header>`, dan `<soap:Body>`. Sangat hemat baterai dan kuota untuk perangkat mobile.
2. **Statelessness & Skalabilitas:** Autentikasi token Bearer Sanctum memungkinkan penanganan ribuan request dari aplikasi mobile dan kiosk tanpa membebani memori session di server Laravel.
3. **Pemanfaatan Semantik HTTP Alami:** REST memanfaatkan metode bawaan HTTP (`GET`, `POST`, `PUT`, `PATCH`, `DELETE`) dan kode status HTTP standar industri (`200`, `201`, `204`, `401`, `404`, `409`, `422`), sedangkan SOAP men-tunnel semua aksi lewat `POST`.

---

## 2. Katalog Endpoint Web Services (SOAP & REST API v1)

| No | Method | URI Endpoint | Proteksi Auth | Deskripsi & Fungsi Bisnis | Kode Status Normal / Galat |
|---|---|---|---|---|---|
| 1 | `GET` | `/api/v1/soap/sipus/wsdl` | Publik | Menyajikan dokumen kontrak WSDL XML untuk layanan clearance bebas pustaka. | `200 OK` |
| 2 | `POST` | `/api/v1/soap/sipus` | Publik | Menerima amplop SOAP XML `CheckClearanceStatusRequest` dan mengembalikan jumlah pinjaman aktif & status clearance. | `200 OK` / `500 Fault` |
| 3 | `POST` | `/api/v1/auth/login` | Publik | Menerbitkan personal access token stateless (Sanctum) bagi pustakawan. | `200 OK` / `422`, `401` |
| 4 | `GET` | `/api/v1/auth/me` | Bearer Token | Mengambil data profil user pemilik token aktif. | `200 OK` / `401` |
| 5 | `POST` | `/api/v1/auth/logout` | Bearer Token | Mencabut (revoke) personal access token aktif saat ini. | `200 OK` / `401` |
| 6 | `GET` | `/api/v1/buku` | Publik | Menampilkan daftar buku terpaginasi (filter `search`, `kategori_id`, `tersedia`, sort). | `200 OK` |
| 7 | `GET` | `/api/v1/buku/{id}` | Publik | Menampilkan detail 1 buku beserta relasi kategori dan tautan HATEOAS. | `200 OK` / `404` |
| 8 | `GET` | `/api/v1/buku/{id}/referensi-eksternal` | Publik | Mengonsumsi OpenLibrary API eksternal untuk melengkapi metadata buku via ISBN. | `200 OK` / `502` |
| 9 | `POST` | `/api/v1/buku` | Bearer Token | Menambahkan buku baru dengan validasi ketat dan mengembalikan header `Location`. | `201 Created` / `422` |
| 10 | `PUT` | `/api/v1/buku/{id}` | Bearer Token | Mengganti seluruh atribut buku (Full Replacement). | `200 OK` / `422`, `404` |
| 11 | `PATCH` | `/api/v1/buku/{id}` | Bearer Token | Memperbarui sebagian atribut buku (Partial Update, misal: hanya stok). | `200 OK` / `422`, `404` |
| 12 | `DELETE` | `/api/v1/buku/{id}` | Bearer Token | Menghapus buku (ditolak jika masih memiliki sirkulasi pinjaman aktif). | `204 No Content` / `409` |
| 13 | `GET` | `/api/v1/peminjamans` | Bearer Token | Menampilkan riwayat transaksi sirkulasi peminjaman buku terpaginasi. | `200 OK` / `401` |
| 14 | `POST` | `/api/v1/peminjamans` | Bearer Token | Mencatat transaksi peminjaman buku baru (ACID `DB::transaction` + `lockForUpdate`). | `201 Created` / `409`, `422` |
| 15 | `PATCH`| `/api/v1/peminjamans/{id}/kembalikan`| Bearer Token | Memproses pengembalian buku dan mengembalikan kuota stok secara atomik. | `200 OK` / `409`, `404` |

---

## 3. Panduan Instalasi & Pengujian

### 3.1 Prasyarat & Menjalankan Aplikasi
```bash
# 1. Pastikan ekstensi PHP & Composer aktif (Laragon)
composer install

# 2. Migrasi database dan isi seeder
php artisan migrate
php artisan db:seed

# 3. Akun Uji Pustakawan Lokal:
# Email:    pustakawan@del.ac.id
# Password: password123

# 4. Jalankan server lokal
php artisan serve
```

### 3.2 Mengimpor Koleksi Postman / Thunder Client
- Spesifikasi OpenAPI 3.0: `docs/openapi-sipus-v1.yaml`
- Koleksi Postman API v1: `docs/SIPUS-Del-API-v1.postman_collection.json`

---

## 4. Galeri Bukti Pengujian (Test Results)

1. **Uji SOAP Clearance (`POST /api/v1/soap/sipus`):**  
   Status: `200 OK`. Mengembalikan XML Envelope berisi `<m:active_loans>` dan `<m:is_cleared>true</m:is_cleared>`.  
   *(Jika mahasiswa tidak ditemukan atau XML cacat, mengembalikan blok `<soap:Fault>`)*

2. **Login Stateless Bearer Token (`POST /api/v1/auth/login`):**  
   Status: `200 OK`. Mengembalikan Sanctum `access_token` dan tautan navigasi HATEOAS `_links`.

3. **Katalog Buku & HATEOAS (`GET /api/v1/buku`):**  
   Status: `200 OK`. Bebas N+1 query (`with('kategori')`), menyertakan navigasi paginasi dan tautan dinamis `pinjam_buku` ketika `stok > 0`.

4. **POST Buku Baru (`POST /api/v1/buku`):**  
   Status: `201 Created`. Mengembalikan data buku baru beserta header respon `Location: http://localhost:8000/api/v1/buku/{id}`.

5. **Perbedaan PUT vs PATCH (`PATCH /api/v1/buku/{id}`):**  
   Status: `200 OK`. Berhasil memperbarui atribut tertentu (misal: `stok: 10`) tanpa menghapus atau mereset kolom `penulis`, `penerbit`, dan `sinopsis`.

6. **Peminjaman Buku ACID (`POST /api/v1/peminjamans`):**  
   Status: `201 Created`. Terproteksi `lockForUpdate()`, otomatis memotong stok buku (-1) dan menghasilkan `kode_pinjam` atomik.  
   *Uji Negatif Duplikasi:* Mengirim ulang request menghasilkan `409 Conflict` (mencegah peminjaman ganda buku yang sama).

7. **Pengembalian Buku (`PATCH /api/v1/peminjamans/{id}/kembalikan`):**  
   Status: `200 OK`. Mengubah status menjadi `dikembalikan`, mencatat tanggal pengembalian, dan mengembalikan stok buku (+1).

8. **REST Service Consumer (`GET /api/v1/buku/{id}/referensi-eksternal`):**  
   Status: `200 OK`. Memanggil OpenLibrary REST API via fasad `Http::timeout(8)->get(...)` dengan penanganan graceful fallback.

---
*Dikerjakan untuk memenuhi Tugas Mandiri Praktikum Minggu 09 PPW TA 2026/2027 — Institut Teknologi Del*
