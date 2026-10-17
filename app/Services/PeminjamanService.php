<?php

namespace App\Services;

use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PeminjamanService
{
    public function pinjam(
        int $bukuId,
        string $nimPeminjam,
        string $namaPeminjam
    ): Peminjaman {
        return DB::transaction(function () use (
            $bukuId,
            $nimPeminjam,
            $namaPeminjam
        ) {
            $buku = Buku::whereKey($bukuId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($buku->stok <= 0) {
                throw ValidationException::withMessages([
                    'buku_id' => 'Stok buku tidak tersedia.',
                ]);
            }

            $buku->decrement('stok');

            return Peminjaman::create([
                'id_kode_pinjam' => 'PJM-' . Str::upper(Str::random(10)),
                'buku_id' => $buku->id,
                'nim_peminjam' => $nimPeminjam,
                'nama_peminjam' => $namaPeminjam,
                'tanggal_pinjam' => now()->toDateString(),
                'tanggal_jatuh_tempo' => now()->addDays(7)->toDateString(),
                'status' => 'dipinjam',
            ]);
        });
    }

    public function kembalikan(int $peminjamanId): Peminjaman
    {
        return DB::transaction(function () use ($peminjamanId) {
            $peminjaman = Peminjaman::whereKey($peminjamanId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($peminjaman->status !== 'dipinjam') {
                throw ValidationException::withMessages([
                    'status' => 'Peminjaman ini sudah dikembalikan.',
                ]);
            }

            $buku = Buku::whereKey($peminjaman->buku_id)
                ->lockForUpdate()
                ->firstOrFail();

            $buku->increment('stok');

            $peminjaman->update([
                'tanggal_kembali' => now()->toDateString(),
                'status' => 'dikembalikan',
            ]);

            return $peminjaman->fresh();
        });
    }
}
