
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Peminjaman - SIPUS-Del</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">
    <div class="container py-5">

        <h1 class="mb-2">Peminjaman Buku</h1>
        <p class="text-muted">
            Sistem Informasi Perpustakaan Kampus Del
        </p>

        {{-- Pesan jika proses berhasil --}}
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        {{-- Menampilkan pesan validasi --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Tombol kembali --}}
        <div class="mb-4">
            <a
                href="{{ route('buku.index') }}"
                class="btn btn-outline-secondary"
            >
                Kembali ke Daftar Buku
            </a>
        </div>

        {{-- Form peminjaman --}}
        <div class="card mb-4">
            <div class="card-header">
                Form Peminjaman
            </div>

            <div class="card-body">
                <form
                    action="{{ route('peminjaman.store') }}"
                    method="POST"
                >
                    @csrf

                    {{-- Input NIM peminjam --}}
                    <div class="mb-3">
                        <label for="nim_peminjam" class="form-label">
                            NIM Peminjam
                        </label>

                        <input
                            type="text"
                            name="nim_peminjam"
                            id="nim_peminjam"
                            class="form-control"
                            value="{{ old('nim_peminjam') }}"
                            maxlength="30"
                            required
                        >
                    </div>

                    {{-- Input nama peminjam --}}
                    <div class="mb-3">
                        <label for="nama_peminjam" class="form-label">
                            Nama Peminjam
                        </label>

                        <input
                            type="text"
                            name="nama_peminjam"
                            id="nama_peminjam"
                            class="form-control"
                            value="{{ old('nama_peminjam') }}"
                            maxlength="255"
                            required
                        >
                    </div>

                    {{-- Memilih buku --}}
                    <div class="mb-3">
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
                                    {{ $buku->judul }}
                                    — Stok: {{ $buku->stok }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        Pinjam Buku
                    </button>
                </form>
            </div>
        </div>

        {{-- Tabel riwayat peminjaman --}}
        <div class="card">
            <div class="card-header">
                Riwayat Peminjaman
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Kode Peminjaman</th>
                            <th>NIM</th>
                            <th>Buku</th>
                            <th>Nama Peminjam</th>
                            <th>Tanggal Pinjam</th>
                            <th>Jatuh Tempo</th>
                            <th>Tanggal Kembali</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($peminjamans as $peminjaman)
                            <tr>
                                <td>
                                    {{ $peminjamans->firstItem() + $loop->index }}
                                </td>

                                <td>
                                    {{ $peminjaman->id_kode_pinjam }}
                                </td>

                                <td>
                                    {{ $peminjaman->nim_peminjam }}
                                </td>

                                <td>
                                    {{ $peminjaman->buku?->judul ?? 'Buku tidak ditemukan' }}
                                </td>

                                <td>
                                    {{ $peminjaman->nama_peminjam }}
                                </td>

                                <td>
                                    {{ $peminjaman->tanggal_pinjam?->format('d-m-Y') ?? '-' }}
                                </td>

                                <td>
                                    {{ $peminjaman->tanggal_jatuh_tempo?->format('d-m-Y') ?? '-' }}
                                </td>

                                <td>
                                    {{ $peminjaman->tanggal_kembali?->format('d-m-Y') ?? '-' }}
                                </td>

                                <td>
                                    @if ($peminjaman->status === 'dipinjam')
                                        <span class="badge bg-warning text-dark">
                                            Dipinjam
                                        </span>
                                    @elseif ($peminjaman->status === 'dikembalikan')
                                        <span class="badge bg-success">
                                            Dikembalikan
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            {{ $peminjaman->status }}
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    @if ($peminjaman->status === 'dipinjam')
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
                                    @else
                                        <span class="text-muted">
                                            Selesai
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    Belum ada data peminjaman.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Navigasi halaman --}}
            <div class="card-body">
                {{ $peminjamans->links() }}
            </div>
        </div>

    </div>
</body>
</html>
