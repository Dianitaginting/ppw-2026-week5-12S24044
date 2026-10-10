<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KategoriCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_kategori_index_and_create_form_are_available(): void
    {
        $kategori = Kategori::factory()->create();

        $this->get(route('kategori.index'))
            ->assertOk()
            ->assertViewIs('kategori.index')
            ->assertSee($kategori->kode_kategori)
            ->assertSee($kategori->nama_kategori);

        $this->get(route('kategori.create'))
            ->assertOk()
            ->assertViewIs('kategori.create');
    }

    public function test_kategori_can_be_created_and_shown_with_its_books(): void
    {
        $payload = [
            'kode_kategori' => 'WEB001',
            'nama_kategori' => 'Pemrograman Web',
        ];

        $this->post(route('kategori.store'), $payload)
            ->assertRedirect(route('kategori.index'))
            ->assertSessionHas('success');

        $kategori = Kategori::where('kode_kategori', $payload['kode_kategori'])->firstOrFail();
        $buku = Buku::factory()->create(['kategori_id' => $kategori->id]);

        $this->get(route('kategori.show', $kategori))
            ->assertOk()
            ->assertViewIs('kategori.show')
            ->assertSee($kategori->nama_kategori)
            ->assertSee($buku->judul);
    }

    public function test_kategori_creation_validates_required_and_unique_fields(): void
    {
        Kategori::factory()->create(['kode_kategori' => 'KTG00001']);

        $this->post(route('kategori.store'), [
            'kode_kategori' => 'KTG00001',
            'nama_kategori' => '',
        ])->assertSessionHasErrors(['kode_kategori', 'nama_kategori']);
    }

    public function test_kategori_can_be_edited_without_changing_its_code(): void
    {
        $kategori = Kategori::factory()->create();

        $this->get(route('kategori.edit', $kategori))
            ->assertOk()
            ->assertViewIs('kategori.edit');

        $this->put(route('kategori.update', $kategori), [
            'kode_kategori' => $kategori->kode_kategori,
            'nama_kategori' => 'Nama Kategori Baru',
        ])->assertRedirect(route('kategori.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('kategoris', [
            'id' => $kategori->id,
            'kode_kategori' => $kategori->kode_kategori,
            'nama_kategori' => 'Nama Kategori Baru',
        ]);
    }

    public function test_kategori_update_rejects_an_existing_code(): void
    {
        Kategori::factory()->create(['kode_kategori' => 'KTG00001']);
        $kategori = Kategori::factory()->create(['kode_kategori' => 'KTG00002']);

        $this->put(route('kategori.update', $kategori), [
            'kode_kategori' => 'KTG00001',
            'nama_kategori' => 'Nama Kategori',
        ])->assertSessionHasErrors('kode_kategori');
    }

    public function test_deleting_kategori_also_deletes_related_books(): void
    {
        $kategori = Kategori::factory()->create();
        $buku = Buku::factory()->create(['kategori_id' => $kategori->id]);

        $this->delete(route('kategori.destroy', $kategori))
            ->assertRedirect(route('kategori.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('kategoris', ['id' => $kategori->id]);
        $this->assertDatabaseMissing('bukus', ['id' => $buku->id]);
    }
}
