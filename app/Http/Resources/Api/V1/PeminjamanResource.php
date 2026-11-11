<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PeminjamanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'nim_peminjam'    => $this->nim_peminjam,
            'id_buku'         => $this->id_buku,
            'tanggal_pinjam'  => $this->tanggal_pinjam,
            'tanggal_kembali' => $this->tanggal_kembali,
            'status'          => $this->status,
            'buku'            => $this->whenLoaded('buku', fn() => [
                'id'    => $this->buku->id,
                'judul' => $this->buku->judul,
            ]),
            'created_at'      => $this->created_at?->toISOString(),
            '_links'          => [
                'self'       => ['href' => url("/api/v1/peminjaman/{$this->id}")],
                'kembalikan' => ['href' => url("/api/v1/peminjaman/{$this->id}/kembalikan")],
                'buku'       => ['href' => url("/api/v1/buku/{$this->id_buku}")],
            ],
        ];
    }
}
