<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Peminjaman;
use App\Services\PeminjamanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
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

        $peminjaman = $service->pinjam(
            (int) $validated['buku_id'],
            $validated['nim_peminjam'],
            $validated['nama_peminjam']
        );

        $request->session()->put('last_borrower', [
            'nim' => $peminjaman->nim_peminjam,
            'nama' => $peminjaman->nama_peminjam,
        ]);
        $request->session()->put('last_peminjaman', $peminjaman->kode_pinjam);

        Cookie::queue(cookie(
            'last_peminjaman',
            $peminjaman->kode_pinjam,
            120,
            '/',
            null,
            (bool) config('session.secure'),
            true,
            false,
            'lax'
        ));

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
