<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Peminjaman;
use App\Services\PeminjamanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PeminjamanController extends Controller
{
    public function index(): View
    {
        $peminjamans = Peminjaman::with('buku')
            ->latest()
            ->paginate(10);

        $bukus = Buku::orderBy('judul')->get();

        return view('peminjaman.index', compact('peminjamans', 'bukus'));
    }

    public function store(
        Request $request,
        PeminjamanService $service
    ): RedirectResponse {
        $validated = $request->validate([
            'buku_id' => ['required', 'integer', 'exists:bukus,id'],
            'nim_peminjam' => ['required', 'string', 'max:30'],
            'nama_peminjam' => ['required', 'string', 'max:255'],
        ]);

        $service->pinjam(
            (int) $validated['buku_id'],
            $validated['nim_peminjam'],
            $validated['nama_peminjam']
        );

        return redirect()
            ->route('peminjaman.index')
            ->with('success', 'Buku berhasil dipinjam.');
    }

    public function kembalikan(
        Peminjaman $peminjaman,
        PeminjamanService $service
    ): RedirectResponse {
        $service->kembalikan($peminjaman->id);

        return redirect()
            ->route('peminjaman.index')
            ->with('success', 'Buku berhasil dikembalikan.');
    }
}
