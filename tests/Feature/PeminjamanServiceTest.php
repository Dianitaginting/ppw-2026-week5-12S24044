<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use App\Services\PeminjamanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Cookie;
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
            'isbn' => 'TEST-'.uniqid(),
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

        $this->assertNotEmpty($peminjaman->kode_pinjam);
        $this->assertSame('12S24044', $peminjaman->nim_peminjam);
        $this->assertSame('Mahasiswa Test', $peminjaman->nama_peminjam);

        $this->assertNotNull($peminjaman->tanggal_pinjam);
        $this->assertNotNull($peminjaman->tanggal_jatuh_tempo);

        $this->assertSame(
            $peminjaman->tanggal_pinjam->copy()->addDays(7)->toDateString(),
            $peminjaman->tanggal_jatuh_tempo->toDateString()
        );
    }

    public function test_peminjaman_menyimpan_state_session_dan_mengantre_cookie_transaksi(): void
    {
        $kategori = Kategori::create([
            'kode_kategori' => 'HTTP',
            'nama_kategori' => 'Kategori HTTP',
        ]);
        $buku = Buku::create([
            'isbn' => 'HTTP-'.uniqid(),
            'judul' => 'Buku HTTP',
            'penulis' => 'Penulis Test',
            'penerbit' => 'Penerbit Test',
            'tahun_terbit' => 2025,
            'kategori_id' => $kategori->id,
            'stok' => 1,
            'sinopsis' => 'Buku untuk pengujian HTTP.',
        ]);

        $response = $this->actingAs(User::factory()->create())
            ->post(route('peminjaman.store'), [
                'buku_id' => $buku->id,
                'nim_peminjam' => '12S24044',
                'nama_peminjam' => 'Mahasiswa HTTP',
            ]);

        $peminjaman = Peminjaman::query()->firstOrFail();
        $response->assertRedirect(route('peminjaman.index'))
            ->assertSessionHas('last_borrower', [
                'nim' => '12S24044',
                'nama' => 'Mahasiswa HTTP',
            ])
            ->assertSessionHas('last_peminjaman', $peminjaman->kode_pinjam);

        $transactionCookie = collect($response->headers->getCookies())
            ->first(
                fn (Cookie $cookie): bool => $cookie->getName() === 'last_peminjaman'
            );

        $this->assertNotNull($transactionCookie);
        $this->assertTrue($transactionCookie->isHttpOnly());
        $this->assertSame('lax', $transactionCookie->getSameSite());
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

    public function test_stok_dipulihkan_jika_pembuatan_transaksi_gagal(): void
    {
        $buku = $this->buatBuku(1);

        Peminjaman::creating(static function (Peminjaman $peminjaman): void {
            throw new \RuntimeException('Simulasi kegagalan penyimpanan.');
        });

        try {
            app(PeminjamanService::class)->pinjam(
                $buku->id,
                '12S24044',
                'Mahasiswa Test'
            );

            $this->fail('Kegagalan penyimpanan seharusnya diteruskan.');
        } catch (\RuntimeException $exception) {
            $this->assertSame(
                'Simulasi kegagalan penyimpanan.',
                $exception->getMessage()
            );
        }

        $this->assertSame(1, $buku->fresh()->stok);
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
            $peminjamanTerbaru->kode_pinjam
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
