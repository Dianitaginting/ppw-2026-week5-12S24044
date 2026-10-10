<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Http\Request;

class BukuController extends Controller
{
    public function index()
    {
        // Eager Loading (Mencegah N+1 Problem) + Pagination
        $bukus = Buku::with('kategori')->latest()->paginate(10);
        $kategoris = Kategori::all();
        return view('buku.index', compact('bukus', 'kategoris'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'isbn'         => 'required|string|max:20|unique:bukus,isbn',
            'judul'        => 'required|string|min:5|max:255',
            'penulis'      => 'required|string|max:150',
            'penerbit'     => 'required|string|max:150',
            'tahun_terbit' => 'required|integer|digits:4',
            'kategori_id'  => 'required|exists:kategoris,id',
            'stok'         => 'required|integer|min:0',
            'sinopsis'     => 'nullable|string',
        ]);

        Buku::create($validated);

        return redirect()->route('buku.index')->with('success', 'Buku berhasil ditambahkan!');
    }

    public function destroy(Buku $buku)
    {
        $buku->delete();
        return redirect()->route('buku.index')->with('success', 'Buku berhasil dihapus!');
    }
}