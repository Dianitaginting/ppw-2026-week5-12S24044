<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BukuCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Semua pengujian dijalankan sebagai petugas yang sudah login.
        $this->actingAs(User::factory()->create());
    }

    public function test_buku_index_loads_categories_without_n_plus_one_queries(): void
    {
        $kategori = Kategori::factory()->create();

        Buku::factory(5)->create([
            'kategori_id' => $kategori->id,
        ]);

        $collectQueries = false;
        $queries = [];

        DB::listen(function (QueryExecuted $query) use (
            &$collectQueries,
            &$queries
        ): void {
            if (
                $collectQueries &&
                preg_match('/^\s*select\b/i', $query->sql) === 1
            ) {
                $queries[] = $query->sql;
            }
        });

        $collectQueries = true;

        $response = $this->get(route('buku.index'));

        $collectQueries = false;

        $response->assertOk()
            ->assertViewIs('buku.index')
            ->assertSee($kategori->nama_kategori);

        // Pagination, eager-loaded categories, and aggregate statistics stay bounded.
        $this->assertCount(
            5,
            $queries,
            'The index should avoid extra per-book category or loan queries.'
        );
    }

    public function test_catalog_search_counts_loans_and_shows_library_statistics(): void
    {
        $kategori = Kategori::factory()->create();
        $buku = Buku::factory()->create([
            'isbn' => 'ISBN-QUERY-001',
            'judul' => 'Buku Query Builder',
            'penulis' => 'Penulis Query Builder',
            'stok' => 4,
            'kategori_id' => $kategori->id,
        ]);
        Buku::factory()->create([
            'isbn' => 'ISBN-OTHER-001',
            'stok' => 1,
            'kategori_id' => $kategori->id,
        ]);

        Peminjaman::create([
            'kode_pinjam' => 'PJM-QUERY-001',
            'buku_id' => $buku->id,
            'nim_peminjam' => '12S24044',
            'nama_peminjam' => 'Peminjam Aktif',
            'tanggal_pinjam' => '2026-10-10',
            'tanggal_jatuh_tempo' => '2026-10-17',
            'status' => 'dipinjam',
        ]);
        Peminjaman::create([
            'kode_pinjam' => 'PJM-QUERY-002',
            'buku_id' => $buku->id,
            'nim_peminjam' => '12S24045',
            'nama_peminjam' => 'Peminjam Selesai',
            'tanggal_pinjam' => '2026-10-01',
            'tanggal_jatuh_tempo' => '2026-10-08',
            'tanggal_kembali' => '2026-10-07',
            'status' => 'dikembalikan',
        ]);

        foreach (['Query Builder', 'ISBN-QUERY-001', 'Penulis Query Builder'] as $search) {
            $this->get(route('buku.index', ['search' => $search]))
                ->assertOk()
                ->assertViewHas('bukus', function ($bukus) use ($buku): bool {
                    return $bukus->total() === 1
                        && $bukus->first()->id === $buku->id
                        && $bukus->first()->peminjamans_count === 2;
                })
                ->assertViewHas('stats', [
                    'total_judul_buku' => 2,
                    'total_stok_tersedia' => 5,
                    'total_dipinjam' => 1,
                    'total_dikembalikan' => 1,
                ]);
        }

        $this->get(route('buku.index'))
            ->assertOk()
            ->assertSessionHas('last_book_search', 'Penulis Query Builder')
            ->assertSee('Pencarian terakhir: Penulis Query Builder');
    }

    public function test_catalog_rejects_non_string_search_parameters(): void
    {
        $this->get(route('buku.index', ['search' => ['unexpected']]))
            ->assertRedirect()
            ->assertSessionHasErrors('search');
    }

    public function test_buku_can_be_created_and_shown(): void
    {
        $kategori = Kategori::factory()->create();
        $payload = $this->validBukuPayload($kategori);

        $this->get(route('buku.create'))
            ->assertOk()
            ->assertViewIs('buku.create');

        $this->post(route('buku.store'), $payload)
            ->assertRedirect(route('buku.index'))
            ->assertSessionHas('success');

        $buku = Buku::where('isbn', $payload['isbn'])->firstOrFail();

        $this->assertSame($kategori->id, $buku->kategori_id);

        $this->get(route('buku.show', $buku))
            ->assertOk()
            ->assertViewIs('buku.show')
            ->assertSee($payload['judul'])
            ->assertSee($kategori->nama_kategori);
    }

    public function test_buku_creation_validates_required_fields_and_constraints(): void
    {
        $this->post(route('buku.store'), [
            'isbn' => '',
            'judul' => 'No',
            'penulis' => '',
            'penerbit' => '',
            'tahun_terbit' => '20',
            'kategori_id' => 9999,
            'stok' => -1,
        ])->assertSessionHasErrors([
            'isbn',
            'judul',
            'penulis',
            'penerbit',
            'tahun_terbit',
            'kategori_id',
            'stok',
        ]);
    }

    public function test_buku_isbn_must_be_unique(): void
    {
        $kategori = Kategori::factory()->create();

        $existing = Buku::factory()->create([
            'kategori_id' => $kategori->id,
        ]);

        $payload = $this->validBukuPayload($kategori);
        $payload['isbn'] = $existing->isbn;

        $this->post(route('buku.store'), $payload)
            ->assertSessionHasErrors('isbn');
    }

    public function test_buku_can_be_edited_and_updated_without_changing_its_isbn(): void
    {
        $kategori = Kategori::factory()->create();

        $buku = Buku::factory()->create([
            'kategori_id' => $kategori->id,
        ]);

        $payload = $this->validBukuPayload($kategori);
        $payload['isbn'] = $buku->isbn;
        $payload['judul'] = 'Judul Buku yang Diperbarui';

        $this->get(route('buku.edit', $buku))
            ->assertOk()
            ->assertViewIs('buku.edit');

        $this->put(route('buku.update', $buku), $payload)
            ->assertRedirect(route('buku.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('bukus', [
            'id' => $buku->id,
            'isbn' => $buku->isbn,
            'judul' => $payload['judul'],
        ]);
    }

    public function test_buku_update_rejects_an_isbn_used_by_another_buku(): void
    {
        $kategori = Kategori::factory()->create();

        $first = Buku::factory()->create([
            'kategori_id' => $kategori->id,
        ]);

        $second = Buku::factory()->create([
            'kategori_id' => $kategori->id,
        ]);

        $payload = $this->validBukuPayload($kategori);
        $payload['isbn'] = $first->isbn;

        $this->put(route('buku.update', $second), $payload)
            ->assertSessionHasErrors('isbn');
    }

    public function test_buku_can_be_deleted(): void
    {
        $kategori = Kategori::factory()->create();

        $buku = Buku::factory()->create([
            'kategori_id' => $kategori->id,
        ]);
        $peminjaman = Peminjaman::create([
            'kode_pinjam' => 'PJM-DELETE-001',
            'buku_id' => $buku->id,
            'nim_peminjam' => '12S24044',
            'nama_peminjam' => 'Peminjam Test',
            'tanggal_pinjam' => '2026-10-10',
            'tanggal_jatuh_tempo' => '2026-10-17',
            'status' => 'dipinjam',
        ]);

        $this->delete(route('buku.destroy', $buku))
            ->assertRedirect(route('buku.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('bukus', [
            'id' => $buku->id,
        ]);
        $this->assertDatabaseMissing('peminjamans', [
            'id' => $peminjaman->id,
        ]);
    }

    /**
     * @return array<string, int|string>
     */
    private function validBukuPayload(Kategori $kategori): array
    {
        return [
            'isbn' => fake()->unique()->isbn13(),
            'judul' => 'Pengantar Pemrograman Web',
            'penulis' => 'Penulis Contoh',
            'penerbit' => 'Penerbit Contoh',
            'tahun_terbit' => 2025,
            'kategori_id' => $kategori->id,
            'stok' => 10,
            'sinopsis' => 'Sinopsis buku untuk pengujian.',
        ];
    }
}
