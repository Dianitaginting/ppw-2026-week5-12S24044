<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Kategori;
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

        // Query katalog menggunakan JOIN kategori dan subquery
        // jumlah peminjaman, sehingga tidak perlu query kategori
        // terpisah untuk setiap buku.
        $this->assertCount(
            2,
            $queries,
            'The paginated index should use a count query and a data query.'
        );
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

        $this->delete(route('buku.destroy', $buku))
            ->assertRedirect(route('buku.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('bukus', [
            'id' => $buku->id,
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
