<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Peminjaman extends Model
{
    use HasFactory;

    protected $table = 'peminjamans';

    protected $fillable = [
    'kode_pinjam',
    'nim_peminjam',
    'nama_peminjam',
    'buku_id',
    'status',
    'tanggal_pinjam',
    'tanggal_kembali',
];

    // Setiap transaksi peminjaman dimiliki oleh satu Buku (Inverse One-to-Many)
    public function buku(): BelongsTo
    {
        return $this->belongsTo(Buku::class);
    }
}