<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BukuController extends Controller
{
    /**
     * Menampilkan daftar buku, pencarian,
     * jumlah peminjaman, dan pagination.
     */
    public function index(Request $request)
    {
        $search = trim($request->query('search', ''));

        // Query Builder untuk mengambil data katalog buku.
        $query = DB::table('bukus')
            ->join(
                'kategoris',
                'bukus.kategori_id',
                '=',
                'kategoris.id'
            )
            ->select(
                'bukus.id',
                'bukus.isbn',
                'bukus.judul',
                'bukus.penulis',
                'bukus.penerbit',
                'bukus.tahun_terbit',
                'bukus.kategori_id',
                'bukus.stok',
                'bukus.sinopsis',
                'bukus.created_at',
                'bukus.updated_at',
                'kategoris.nama_kategori'
            );

        // Menghitung jumlah seluruh riwayat peminjaman
        // setiap buku menggunakan Eloquent withCount().
        $jumlahPeminjaman = Buku::withCount('peminjamans')
            ->select('id');

        $query->addSelect([
            'peminjamans_count' => $jumlahPeminjaman
                ->whereColumn('bukus.id', 'bukus.id')
                ->limit(1),
        ]);

        // Pencarian berdasarkan judul, ISBN, penulis,
        // atau nama kategori.
        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery
                    ->where('bukus.judul', 'like', "%{$search}%")
                    ->orWhere('bukus.isbn', 'like', "%{$search}%")
                    ->orWhere('bukus.penulis', 'like', "%{$search}%")
                    ->orWhere(
                        'kategoris.nama_kategori',
                        'like',
                        "%{$search}%"
                    );
            });
        }

        // Mengurutkan dan membatasi hasil menjadi 10 buku per halaman.
        $bukus = $query
            ->orderByDesc('bukus.created_at')
            ->orderByDesc('bukus.id')
            ->paginate(10)
            ->withQueryString();

        return view('buku.index', compact('bukus', 'search'));
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
