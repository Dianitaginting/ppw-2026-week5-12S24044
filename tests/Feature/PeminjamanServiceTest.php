<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Kategori;
use App\Services\PeminjamanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PeminjamanServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Membuat data buku untuk kebutuhan pengujian.
     */
    private function buatBuku(int $stok = 3): Buku
    {
        $kategori = Kategori::create([
            'kode_kategori' => 'TEST',
            'nama_kategori' => 'Kategori Test',
        ]);

        return Buku::create([
            'isbn' => 'TEST-' . uniqid(),
            'judul' => 'Buku Pengujian',
            'penulis' => 'Penulis Test',
            'penerbit' => 'Penerbit Test',
            'tahun_terbit' => 2025,
            'kategori_id' => $kategori->id,
            'stok' => $stok,
            'sinopsis' => 'Buku untuk pengujian.',
        ]);
    }

    /**
     * Memastikan peminjaman mengurangi stok buku.
     */
    public function test_peminjaman_mengurangi_stok_buku(): void
    {
        $buku = $this->buatBuku(3);

        $peminjaman = app(PeminjamanService::class)->pinjam(
            $buku->id,
            '12S24044',
            'Mahasiswa Test'
        );

        $this->assertSame('dipinjam', $peminjaman->status);
        $this->assertSame(2, $buku->fresh()->stok);

        $this->assertNotEmpty($peminjaman->id_kode_pinjam);
        $this->assertSame('12S24044', $peminjaman->nim_peminjam);
        $this->assertSame('Mahasiswa Test', $peminjaman->nama_peminjam);

        $this->assertNotNull($peminjaman->tanggal_pinjam);
        $this->assertNotNull($peminjaman->tanggal_jatuh_tempo);

        $this->assertSame(
            $peminjaman->tanggal_pinjam->copy()->addDays(7)->toDateString(),
            $peminjaman->tanggal_jatuh_tempo->toDateString()
        );
    }

    /**
     * Memastikan buku dengan stok kosong tidak dapat dipinjam.
     */
    public function test_buku_dengan_stok_kosong_tidak_bisa_dipinjam(): void
    {
        $buku = $this->buatBuku(0);

        try {
            app(PeminjamanService::class)->pinjam(
                $buku->id,
                '12S24044',
                'Mahasiswa Test'
            );

            $this->fail('Peminjaman seharusnya ditolak.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey(
                'buku_id',
                $e->errors()
            );
        }

        $this->assertSame(0, $buku->fresh()->stok);
        $this->assertDatabaseCount('peminjamen', 0);
    }

    /**
     * Memastikan pengembalian menambah stok buku.
     */
    public function test_pengembalian_menambah_stok_buku(): void
    {
        $buku = $this->buatBuku(3);

        $service = app(PeminjamanService::class);

        $peminjaman = $service->pinjam(
            $buku->id,
            '12S24044',
            'Mahasiswa Test'
        );

        $service->kembalikan($peminjaman->id);

        $this->assertSame(3, $buku->fresh()->stok);

        $peminjamanTerbaru = $peminjaman->fresh();

        $this->assertSame(
            'dikembalikan',
            $peminjamanTerbaru->status
        );

        $this->assertNotNull(
            $peminjamanTerbaru->tanggal_kembali
        );

        $this->assertNotEmpty(
            $peminjamanTerbaru->id_kode_pinjam
        );

        $this->assertSame(
            '12S24044',
            $peminjamanTerbaru->nim_peminjam
        );
    }

    /**
     * Memastikan transaksi yang sudah dikembalikan
     * tidak dapat dikembalikan untuk kedua kalinya.
     */
    public function test_peminjaman_yang_sudah_dikembalikan_tidak_bisa_dikembalikan_lagi(): void
    {
        $buku = $this->buatBuku(3);

        $service = app(PeminjamanService::class);

        $peminjaman = $service->pinjam(
            $buku->id,
            '12S24044',
            'Mahasiswa Test'
        );

        $service->kembalikan($peminjaman->id);

        try {
            $service->kembalikan($peminjaman->id);

            $this->fail(
                'Pengembalian kedua seharusnya ditolak.'
            );
        } catch (ValidationException $e) {
            $this->assertArrayHasKey(
                'status',
                $e->errors()
            );
        }

        $this->assertSame(3, $buku->fresh()->stok);

        $this->assertSame(
            'dikembalikan',
            $peminjaman->fresh()->status
        );
    }
}
