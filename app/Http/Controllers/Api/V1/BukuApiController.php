<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\BukuResource;
use App\Models\Buku;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class BukuApiController extends Controller
{
    use ApiResponse;

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Buku::with('kategori');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('penulis', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        if ($kategoriId = $request->query('kategori_id')) {
            $query->where('kategori_id', $kategoriId);
        }

        if ($request->boolean('tersedia')) {
            $query->where('stok', '>', 0);
        }

        $sort = $request->query('sort', 'id');
        $dir = $request->query('dir', 'desc');
        $query->orderBy($sort, $dir);

        $buku = $query->paginate((int) $request->query('per_page', 10));
        return BukuResource::collection($buku);
    }

    public function show(int $id): JsonResponse
    {
        $buku = Buku::with('kategori')->find($id);
        if (!$buku) {
            return $this->problemResponse(
                404,
                'Not Found',
                "Buku dengan ID '{$id}' tidak ditemukan.",
                "/api/v1/buku/{$id}"
            );
        }
        return $this->successResponse(new BukuResource($buku));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'judul'        => 'required|string|max:255',
            'penulis'      => 'required|string|max:255',
            'penerbit'     => 'required|string|max:100',
            'isbn'         => 'required|string|max:20|unique:bukus,isbn',
            'tahun_terbit' => 'required|integer|min:1900|max:2099',
            'kategori_id'  => 'required|integer|exists:kategoris,id',
            'stok'         => 'sometimes|integer|min:0',
            'sinopsis'     => 'sometimes|nullable|string',
        ]);

        if (!isset($validated['stok'])) {
            $validated['stok'] = 0;
        }

        $buku = Buku::create($validated);
        $buku->load('kategori');

        $locationUrl = url("/api/v1/buku/{$buku->id}");
        return $this->createdResponse(new BukuResource($buku), 'Buku berhasil ditambahkan.')
                    ->header('Location', $locationUrl);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $buku = Buku::find($id);
        if (!$buku) {
            return $this->problemResponse(404, 'Not Found', "Buku dengan ID '{$id}' tidak ditemukan.");
        }

        $isPut = $request->isMethod('put');

        if ($isPut) {
            $validated = $request->validate([
                'judul'        => 'required|string|max:255',
                'penulis'      => 'required|string|max:255',
                'penerbit'     => 'required|string|max:100',
                'isbn'         => 'required|string|max:20|unique:bukus,isbn,' . $id,
                'tahun_terbit' => 'required|integer|min:1900|max:2099',
                'kategori_id'  => 'required|integer|exists:kategoris,id',
                'stok'         => 'required|integer|min:0',
                'sinopsis'     => 'nullable|string',
            ]);
            $buku->update($validated);
            $msg = 'Seluruh atribut buku berhasil diganti (PUT).';
        } else {
            $validated = $request->validate([
                'judul'        => 'sometimes|string|max:255',
                'penulis'      => 'sometimes|string|max:255',
                'penerbit'     => 'sometimes|string|max:100',
                'isbn'         => 'sometimes|string|max:20|unique:bukus,isbn,' . $id,
                'tahun_terbit' => 'sometimes|integer|min:1900|max:2099',
                'kategori_id'  => 'sometimes|integer|exists:kategoris,id',
                'stok'         => 'sometimes|integer|min:0',
                'sinopsis'     => 'sometimes|nullable|string',
            ]);
            $buku->update($validated);
            $msg = 'Atribut buku berhasil diperbarui sebagian (PATCH).';
        }

        $buku->load('kategori');
        return $this->successResponse(new BukuResource($buku), $msg);
    }

    public function destroy(int $id): JsonResponse
    {
        $buku = Buku::find($id);
        if (!$buku) {
            return $this->problemResponse(404, 'Not Found', "Buku dengan ID '{$id}' tidak ditemukan.");
        }

        $activeLoans = DB::table('peminjamans')
            ->where(function($q) use ($id) {
                if (\Illuminate\Support\Facades\Schema::hasColumn('peminjamans', 'id_buku')) {
                    $q->where('id_buku', $id);
                } else {
                    $q->where('buku_id', $id);
                }
            })
            ->where('status', 'dipinjam')
            ->count();

        if ($activeLoans > 0) {
            return $this->problemResponse(
                409,
                'Conflict',
                "Buku '{$buku->judul}' tidak dapat dihapus karena masih sedang dipinjam.",
                "/api/v1/buku/{$id}",
                ['active_loans' => $activeLoans]
            );
        }

        $buku->delete();
        return $this->noContentResponse();
    }

    public function referensiEksternal(int $id): JsonResponse
    {
        $buku = Buku::findOrFail($id);
        $isbn = preg_replace('/[^0-9]/', '', $buku->isbn);

        try {
            $response = Http::timeout(8)->get("https://openlibrary.org/api/books", [
                'bibkeys' => "ISBN:{$isbn}",
                'format'  => 'json',
                'jscmd'   => 'data',
            ]);

            if ($response->successful() && !empty($response->json())) {
                return $this->successResponse($response->json(), 'Data buku eksternal berhasil diambil.');
            }
        } catch (\Exception $e) {
            // fallback gracefully
        }

        return $this->successResponse([
            'isbn'    => $buku->isbn,
            'catatan' => 'Metadata eksternal tidak ditemukan atau layanan eksternal sedang offline.'
        ], 'Pencarian metadata eksternal selesai (fallback).');
    }
}
