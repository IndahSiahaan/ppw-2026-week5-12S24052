<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BukuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'judul'        => $this->judul,
            'penulis'      => $this->penulis,
            'penerbit'     => $this->penerbit,
            'isbn'         => $this->isbn,
            'tahun_terbit' => (int) $this->tahun_terbit,
            'stok'         => (int) $this->stok,
            'kategori_id'  => $this->kategori_id,
            'kategori'     => $this->whenLoaded('kategori', fn() => [
                'id'            => $this->kategori->id,
                'nama_kategori' => $this->kategori->nama_kategori,
            ]),
            'created_at'   => $this->created_at?->toISOString(),
            'updated_at'   => $this->updated_at?->toISOString(),
            '_links'       => array_merge([
                'self'       => ['href' => url("/api/v1/buku/{$this->id}"), 'method' => 'GET'],
                'collection' => ['href' => url('/api/v1/buku'), 'method' => 'GET'],
            ], $this->stok > 0 ? [
                'pinjam_buku' => ['href' => url('/api/v1/peminjamans'), 'method' => 'POST']
            ] : []),
        ];
    }
}
