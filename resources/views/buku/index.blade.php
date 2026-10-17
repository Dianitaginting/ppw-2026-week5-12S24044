
<x-layout title="Katalog Buku">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1">Katalog Buku</h1>
            <p class="text-muted mb-0">
                Daftar koleksi buku Perpustakaan IT Del.
            </p>
        </div>

        <a href="{{ route('buku.create') }}" class="btn btn-del">
            Tambah Buku
        </a>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Judul Buku</p>
                    <p class="h3 mb-0">{{ number_format($stats['total_judul_buku']) }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Stok Tersedia</p>
                    <p class="h3 mb-0">{{ number_format($stats['total_stok_tersedia']) }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Sedang Dipinjam</p>
                    <p class="h3 mb-0">{{ number_format($stats['total_dipinjam']) }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Dikembalikan</p>
                    <p class="h3 mb-0">{{ number_format($stats['total_dikembalikan']) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Form pencarian dinamis --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form action="{{ route('buku.index') }}" method="GET">
                <div class="row g-2 align-items-end">
                    <div class="col-md-10">
                        <label for="search" class="form-label">
                            Cari Buku
                        </label>

                        <input
                            type="search"
                            name="search"
                            id="search"
                            class="form-control"
                            placeholder="Cari judul, ISBN, penulis, atau kategori..."
                            value="{{ $search ?? request('search') }}"
                        >
                    </div>

                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-primary">
                            Cari
                        </button>
                    </div>
                </div>

                @if (!empty($search))
                    <div class="mt-2">
                        <a
                            href="{{ route('buku.index') }}"
                            class="btn btn-sm btn-outline-secondary"
                        >
                            Reset Pencarian
                        </a>
                    </div>
                @endif

                @if (session('last_book_search') && empty($search))
                    <small class="text-muted d-block mt-2">
                        Pencarian terakhir: {{ session('last_book_search') }}
                    </small>
                @endif
            </form>
        </div>
    </div>

    {{-- Tabel katalog buku --}}
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Daftar Koleksi Buku</span>

            <span class="badge bg-secondary">
                {{ $bukus->total() }} buku
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle small mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ISBN</th>
                        <th>Judul</th>
                        <th>Penulis</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                        <th>Jumlah Peminjaman</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($bukus as $buku)
                        <tr>
                            <td class="text-nowrap">
                                <small class="text-muted">
                                    {{ $buku->isbn }}
                                </small>
                            </td>

                            <td class="fw-semibold">
                                {{ $buku->judul }}
                            </td>

                            <td>
                                {{ $buku->penulis }}
                            </td>

                            <td>
                                {{ $buku->nama_kategori }}
                            </td>

                            <td>
                                @if ($buku->stok > 0)
                                    <span class="badge bg-success">
                                        {{ $buku->stok }}
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        Habis
                                    </span>
                                @endif
                            </td>

                            <td>
                                {{ $buku->peminjamans_count }}
                            </td>

                            <td class="text-end text-nowrap">
                                <a
                                    href="{{ route('buku.show', $buku->id) }}"
                                    class="btn btn-sm btn-outline-secondary"
                                >
                                    Detail
                                </a>

                                <a
                                    href="{{ route('buku.edit', $buku->id) }}"
                                    class="btn btn-sm btn-outline-primary"
                                >
                                    Edit
                                </a>

                                <form
                                    action="{{ route('buku.destroy', $buku->id) }}"
                                    method="POST"
                                    class="d-inline"
                                    onsubmit="return confirm('Hapus buku ini?')"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="btn btn-sm btn-outline-danger"
                                    >
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                @if (!empty($search))
                                    Buku tidak ditemukan untuk pencarian
                                    "{{ $search }}".
                                @else
                                    Belum ada koleksi buku.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="card-footer bg-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <small class="text-muted">
                    Menampilkan {{ $bukus->firstItem() ?? 0 }}
                    sampai {{ $bukus->lastItem() ?? 0 }}
                    dari {{ $bukus->total() }} data.
                </small>

                {{ $bukus->links() }}
            </div>
        </div>
    </div>
</x-layout>
