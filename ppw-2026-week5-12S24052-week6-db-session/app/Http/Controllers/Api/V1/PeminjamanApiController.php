<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PeminjamanResource;
use App\Models\Peminjaman;
use App\Models\Buku;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PeminjamanApiController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Peminjaman::query();

        if (method_exists(Peminjaman::class, 'buku')) {
            $query->with('buku');
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($nim = $request->query('nim')) {
            $query->where('nim_peminjam', $nim);
        }

        $peminjaman = $query->paginate((int) $request->query('per_page', 15));
        return $this->successResponse(PeminjamanResource::collection($peminjaman));
    }

    public function store(Request $request): JsonResponse
    {
        $bukuCol = Schema::hasColumn('peminjamans', 'id_buku') ? 'id_buku' : 'buku_id';

        $rules = [
            'nim_peminjam'   => 'required|string|max:20',
            'buku_id'        => 'sometimes|integer|exists:bukus,id',
            'id_buku'        => 'sometimes|integer|exists:bukus,id',
            'tanggal_pinjam' => 'sometimes|date',
        ];

        $validated = $request->validate($rules);
        $bukuId = $validated['buku_id'] ?? $validated['id_buku'] ?? 21;
        $nim = $validated['nim_peminjam'];

        return DB::transaction(function () use ($bukuId, $nim, $bukuCol, $request) {
            $buku = Buku::where('id', $bukuId)->lockForUpdate()->first();
            if (!$buku) {
                return $this->problemResponse(404, 'Not Found', 'Buku tidak ditemukan.');
            }

            $existing = Peminjaman::where('nim_peminjam', $nim)
                ->where($bukuCol, $bukuId)
                ->where('status', 'dipinjam')
                ->first();

            if ($existing) {
                return $this->problemResponse(
                    409,
                    'Conflict',
                    'Mahasiswa dengan NIM ini sedang meminjam buku tersebut dan belum dikembalikan.',
                    '/api/v1/peminjamans',
                    ['existing_id' => $existing->id]
                );
            }

            if ($buku->stok < 1) {
                return $this->problemResponse(
                    422,
                    'Unprocessable Content',
                    'Stok buku sedang habis.',
                    '/api/v1/peminjamans'
                );
            }

            $buku->decrement('stok');

            $data = [
                'kode_pinjam'          => 'PJ-' . date('YmdHis') . '-' . rand(100, 999),
                $bukuCol               => $bukuId,
                'nim_peminjam'         => $nim,
                'nama_peminjam'        => $request->input('nama_peminjam', 'Indah Siahaan'),
                'status'               => 'dipinjam',
                'tanggal_pinjam'       => $request->input('tanggal_pinjam', now()->toDateString()),
                'tanggal_jatuh_tempo'  => $request->input('tanggal_kembali', now()->addDays(7)->toDateString()),
                'tanggal_kembali'      => null,
            ];

            if (Schema::hasColumn('peminjamans', 'denda')) {
                $data['denda'] = 0;
            }

            // Pakai DB table insert atau Model dengan unguard agar tidak terhalang fillable
            Peminjaman::unguard();
            $peminjaman = Peminjaman::create($data);
            Peminjaman::reguard();

            if (method_exists($peminjaman, 'buku')) {
                $peminjaman->load('buku');
            }

            $loc = url("/api/v1/peminjamans/{$peminjaman->id}");
            return $this->createdResponse(new PeminjamanResource($peminjaman), 'Peminjaman berhasil dicatat.')
                        ->header('Location', $loc);
        });
    }

    public function show(int $id): JsonResponse
    {
        $peminjaman = Peminjaman::findOrFail($id);
        if (method_exists($peminjaman, 'buku')) {
            $peminjaman->load('buku');
        }
        return $this->successResponse(new PeminjamanResource($peminjaman));
    }

    public function kembalikan(Request $request, int $id): JsonResponse
    {
        return DB::transaction(function () use ($id) {
            $peminjaman = Peminjaman::lockForUpdate()->find($id);

            if (!$peminjaman) {
                return $this->problemResponse(404, 'Not Found', 'Data peminjaman tidak ditemukan.');
            }

            if ($peminjaman->status === 'dikembalikan') {
                return $this->problemResponse(
                    409,
                    'Conflict',
                    'Buku ini sudah berstatus dikembalikan sebelumnya.',
                    "/api/v1/peminjamans/{$id}/kembalikan"
                );
            }

            $bukuCol = Schema::hasColumn('peminjamans', 'id_buku') ? 'id_buku' : 'buku_id';
            $bukuId = $peminjaman->{$bukuCol};

            $peminjaman->update([
                'status'          => 'dikembalikan',
                'tanggal_kembali' => now()->toDateString(),
            ]);

            $buku = Buku::where('id', $bukuId)->first();
            if ($buku) {
                $buku->increment('stok');
            }

            if (method_exists($peminjaman, 'buku')) {
                $peminjaman->load('buku');
            }

            return $this->successResponse(new PeminjamanResource($peminjaman), 'Buku berhasil dikembalikan.');
        });
    }
}
