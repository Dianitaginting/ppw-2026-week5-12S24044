<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Database\Seeders\PetugasSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Cookie;
use Tests\TestCase;

class AuthSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_petugas_can_log_in_and_session_cookie_is_http_only(): void
    {
        $this->seed(PetugasSeeder::class);
        $petugas = User::query()
            ->where('email', 'pustakawan@del.ac.id')
            ->firstOrFail();
        $sessionIdSebelumLogin = session()->getId();

        $response = $this->post(route('login.store'), [
            'email' => $petugas->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('buku.index'))
            ->assertSessionHas('success');
        $this->assertAuthenticatedAs($petugas);
        $this->assertNotSame($sessionIdSebelumLogin, session()->getId());

        $sessionCookie = collect($response->headers->getCookies())
            ->first(
                fn (Cookie $cookie): bool => $cookie->getName()
                    === config('session.cookie')
            );

        $this->assertNotNull($sessionCookie);
        $this->assertTrue($sessionCookie->isHttpOnly());
    }

    public function test_invalid_credentials_do_not_authenticate_the_petugas(): void
    {
        $petugas = User::factory()->create();

        $this->post(route('login.store'), [
            'email' => $petugas->email,
            'password' => 'password-salah',
        ])->assertRedirect()
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_logout_clears_authentication_and_rotates_the_session_token(): void
    {
        $this->actingAs(User::factory()->create());
        $tokenSebelumLogout = session()->token();

        $this->post(route('logout'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('success');

        $this->assertGuest();
        $this->assertNotSame($tokenSebelumLogout, session()->token());
    }

    public function test_protected_pages_and_borrowing_action_redirect_guests_to_login(): void
    {
        $this->get(route('buku.index'))
            ->assertRedirect(route('login'));
        $this->get(route('kategori.index'))
            ->assertRedirect(route('login'));
        $this->get(route('peminjaman.index'))
            ->assertRedirect(route('login'));

        $this->post(route('peminjaman.store'), [])
            ->assertRedirect(route('login'));
    }

    public function test_catalog_and_borrowing_mutations_redirect_guests_to_login(): void
    {
        $kategori = Kategori::factory()->create();
        $buku = Buku::factory()->create([
            'kategori_id' => $kategori->id,
        ]);
        $peminjaman = Peminjaman::create([
            'kode_pinjam' => 'PJM-GUEST-001',
            'buku_id' => $buku->id,
            'nim_peminjam' => '12S24044',
            'nama_peminjam' => 'Peminjam Test',
            'tanggal_pinjam' => '2026-10-10',
            'tanggal_jatuh_tempo' => '2026-10-17',
            'status' => 'dipinjam',
        ]);

        $mutations = [
            ['POST', route('buku.store')],
            ['PUT', route('buku.update', $buku)],
            ['DELETE', route('buku.destroy', $buku)],
            ['POST', route('kategori.store')],
            ['PUT', route('kategori.update', $kategori)],
            ['DELETE', route('kategori.destroy', $kategori)],
            ['POST', route('peminjaman.store')],
            ['PATCH', route('peminjaman.kembalikan', $peminjaman)],
        ];

        foreach ($mutations as [$method, $uri]) {
            $this->call($method, $uri)
                ->assertRedirect(route('login'));
        }
    }
}
