<x-layout title="Peminjaman Buku">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1">Peminjaman Buku</h1>
            <p class="text-muted mb-0">
                Kelola peminjaman dan pengembalian koleksi perpustakaan.
            </p>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">
            Form Peminjaman
        </div>

        <div class="card-body">
            <form action="{{ route('peminjaman.store') }}" method="POST">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nim_peminjam" class="form-label">
                            NIM Peminjam
                        </label>
                        <input
                            type="text"
                            name="nim_peminjam"
                            id="nim_peminjam"
                            class="form-control"
                            value="{{ old('nim_peminjam', session('last_borrower.nim')) }}"
                            maxlength="30"
                            required
                        >
                    </div>

                    <div class="col-md-6">
                        <label for="nama_peminjam" class="form-label">
                            Nama Peminjam
                        </label>
                        <input
                            type="text"
                            name="nama_peminjam"
                            id="nama_peminjam"
                            class="form-control"
                            value="{{ old('nama_peminjam', session('last_borrower.nama')) }}"
                            maxlength="255"
                            required
                        >
                    </div>

                    <div class="col-md-8">
                        <label for="buku_id" class="form-label">
                            Pilih Buku
                        </label>
                        <select
                            name="buku_id"
                            id="buku_id"
                            class="form-select"
                            required
                        >
                            <option value="">-- Pilih Buku --</option>

                            @foreach ($bukus as $buku)
                                <option
                                    value="{{ $buku->id }}"
                                    @selected(old('buku_id') == $buku->id)
                                    @disabled($buku->stok <= 0)
                                >
                                    {{ $buku->judul }} — Stok: {{ $buku->stok }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-del w-100">
                            Pinjam Buku
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">
            Riwayat Peminjaman
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover table-sm align-middle small mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No.</th>
                        <th>Kode</th>
                        <th>NIM</th>
                        <th>Buku</th>
                        <th>Peminjam</th>
                        <th>Pinjam / Tempo</th>
                        <th>Kembali</th>
                        <th>Status / Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($peminjamans as $peminjaman)
                        <tr>
                            <td>
                                {{ $peminjamans->firstItem() + $loop->index }}
                            </td>
                            <td>
                                <span class="text-nowrap">
                                    {{ $peminjaman->kode_pinjam }}
                                </span>
                            </td>
                            <td class="text-nowrap">{{ $peminjaman->nim_peminjam }}</td>
                            <td>{{ $peminjaman->buku?->judul ?? 'Buku tidak ditemukan' }}</td>
                            <td>{{ $peminjaman->nama_peminjam }}</td>
                            <td class="text-nowrap">
                                <div>{{ $peminjaman->tanggal_pinjam?->format('d-m-Y') ?? '-' }}</div>
                                <small class="text-muted">
                                    Tempo: {{ $peminjaman->tanggal_jatuh_tempo?->format('d-m-Y') ?? '-' }}
                                </small>
                            </td>
                            <td class="text-nowrap">
                                {{ $peminjaman->tanggal_kembali?->format('d-m-Y') ?? '-' }}
                            </td>
                            <td>
                                <div class="d-grid gap-1">
                                    @if ($peminjaman->status === 'dipinjam')
                                        <span class="badge bg-warning text-dark">
                                            Dipinjam
                                        </span>
                                        <form
                                            action="{{ route('peminjaman.kembalikan', $peminjaman) }}"
                                            method="POST"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-success"
                                                onclick="return confirm('Proses pengembalian buku ini?')"
                                            >
                                                Kembalikan
                                            </button>
                                        </form>
                                    @elseif ($peminjaman->status === 'dikembalikan')
                                        <span class="badge bg-success">
                                            Dikembalikan
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            {{ $peminjaman->status }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Belum ada data peminjaman.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white">
            {{ $peminjamans->links() }}
        </div>
    </div>

    @if (session('last_peminjaman'))
        <p class="small text-muted mt-3 mb-0">
            Transaksi terakhir: {{ session('last_peminjaman') }}
        </p>
    @endif
</x-layout>
