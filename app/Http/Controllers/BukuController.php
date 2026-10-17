<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BukuController extends Controller
{
    /**
     * Menampilkan daftar buku, pencarian,
     * jumlah peminjaman, dan pagination.
     */
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $search = trim($validated['search'] ?? '');

        if ($search !== '') {
            $request->session()->put('last_book_search', $search);
        }

        $query = Buku::query()
            ->with('kategori')
            ->withCount('peminjamans');

        // Pencarian berdasarkan judul, ISBN, penulis,
        // atau nama kategori.
        $query->when($search !== '', function (Builder $query) use ($search) {
            $query->where(function (Builder $subQuery) use ($search) {
                $subQuery
                    ->where('bukus.judul', 'like', "%{$search}%")
                    ->orWhere('bukus.isbn', 'like', "%{$search}%")
                    ->orWhere('bukus.penulis', 'like', "%{$search}%")
                    ->orWhereHas('kategori', function (Builder $kategoriQuery) use ($search) {
                        $kategoriQuery->where(
                            'nama_kategori',
                            'like',
                            "%{$search}%"
                        );
                    });
            });
        });

        // Mengurutkan dan membatasi hasil menjadi 10 buku per halaman.
        $bukus = $query
            ->orderByDesc('bukus.created_at')
            ->orderByDesc('bukus.id')
            ->paginate(10)
            ->withQueryString();

        $statistikKatalog = DB::table('bukus')
            ->selectRaw(
                'COUNT(*) as total_judul_buku, COALESCE(SUM(stok), 0) as total_stok_tersedia'
            )
            ->first();

        $statistikPeminjaman = DB::table('peminjamans')
            ->selectRaw(
                "COUNT(CASE WHEN status = 'dipinjam' THEN 1 END) as total_dipinjam, COUNT(CASE WHEN status = 'dikembalikan' THEN 1 END) as total_dikembalikan"
            )
            ->first();

        $stats = [
            'total_judul_buku' => (int) $statistikKatalog->total_judul_buku,
            'total_stok_tersedia' => (int) $statistikKatalog->total_stok_tersedia,
            'total_dipinjam' => (int) $statistikPeminjaman->total_dipinjam,
            'total_dikembalikan' => (int) $statistikPeminjaman->total_dikembalikan,
        ];

        return view('buku.index', compact('bukus', 'search', 'stats'));
    }

    /**
     * Menampilkan formulir tambah buku.
     */
    public function create()
    {
        $kategoris = Kategori::orderBy('nama_kategori')->get();

        return view('buku.create', compact('kategoris'));
    }

    /**
     * Menampilkan detail buku.
     */
    public function show(Buku $buku)
    {
        $buku->load('kategori');

        return view('buku.show', compact('buku'));
    }

    /**
     * Menampilkan formulir edit buku.
     */
    public function edit(Buku $buku)
    {
        $kategoris = Kategori::orderBy('nama_kategori')->get();

        return view('buku.edit', compact('buku', 'kategoris'));
    }

    /**
     * Menyimpan buku baru.
     */
    public function store(Request $request)
    {
        $validated = $this->validateBuku($request);

        Buku::create($validated);

        return redirect()
            ->route('buku.index')
            ->with('success', 'Buku berhasil ditambahkan.');
    }

    /**
     * Memperbarui data buku.
     */
    public function update(Request $request, Buku $buku)
    {
        $validated = $this->validateBuku($request, $buku);

        $buku->update($validated);

        return redirect()
            ->route('buku.index')
            ->with('success', 'Buku berhasil diperbarui.');
    }

    /**
     * Menghapus buku.
     */
    public function destroy(Buku $buku)
    {
        $buku->delete();

        return redirect()
            ->route('buku.index')
            ->with('success', 'Buku berhasil dihapus.');
    }

    /**
     * Validasi data buku.
     */
    private function validateBuku(
        Request $request,
        ?Buku $buku = null
    ): array {
        $isbnRule = Rule::unique('bukus', 'isbn');

        if ($buku !== null) {
            $isbnRule->ignore($buku->getKey());
        }

        return $request->validate([
            'isbn' => ['required', 'string', 'max:20', $isbnRule],
            'judul' => ['required', 'string', 'min:5', 'max:255'],
            'penulis' => ['required', 'string', 'max:150'],
            'penerbit' => ['required', 'string', 'max:150'],
            'tahun_terbit' => ['required', 'integer', 'digits:4'],
            'kategori_id' => ['required', 'exists:kategoris,id'],
            'stok' => ['required', 'integer', 'min:0'],
            'sinopsis' => ['nullable', 'string'],
        ], [], [
            'isbn' => 'ISBN',
            'judul' => 'judul',
            'penulis' => 'penulis',
            'penerbit' => 'penerbit',
            'tahun_terbit' => 'tahun terbit',
            'kategori_id' => 'kategori',
            'stok' => 'stok',
            'sinopsis' => 'sinopsis',
        ]);
    }
}
