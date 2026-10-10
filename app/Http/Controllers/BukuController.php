<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BukuController extends Controller
{
    public function index()
    {
        $bukus = Buku::with('kategori')->latest()->paginate(10);

        return view('buku.index', compact('bukus'));
    }

    public function create()
    {
        $kategoris = Kategori::orderBy('nama_kategori')->get();

        return view('buku.create', compact('kategoris'));
    }

    public function show(Buku $buku)
    {
        $buku->load('kategori');

        return view('buku.show', compact('buku'));
    }

    public function edit(Buku $buku)
    {
        $kategoris = Kategori::orderBy('nama_kategori')->get();

        return view('buku.edit', compact('buku', 'kategoris'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateBuku($request);

        Buku::create($validated);

        return redirect()->route('buku.index')->with('success', 'Buku berhasil ditambahkan.');
    }

    public function update(Request $request, Buku $buku)
    {
        $validated = $this->validateBuku($request, $buku);

        $buku->update($validated);

        return redirect()->route('buku.index')->with('success', 'Buku berhasil diperbarui.');
    }

    public function destroy(Buku $buku)
    {
        $buku->delete();

        return redirect()->route('buku.index')->with('success', 'Buku berhasil dihapus.');
    }

    private function validateBuku(Request $request, ?Buku $buku = null): array
    {
        $isbnRule = Rule::unique('bukus', 'isbn');

        if ($buku !== null) {
            $isbnRule->ignore($buku->getKey());
        }

        return $request->validate([
            'isbn' => ['required', 'string', 'max:20', $isbnRule],
            'judul' => 'required|string|min:5|max:255',
            'penulis' => 'required|string|max:150',
            'penerbit' => 'required|string|max:150',
            'tahun_terbit' => 'required|integer|digits:4',
            'kategori_id' => 'required|exists:kategoris,id',
            'stok' => 'required|integer|min:0',
            'sinopsis' => 'nullable|string',
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
