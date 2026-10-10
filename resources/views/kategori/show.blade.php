<x-layout title="Detail Kategori">
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <strong>Detail Kategori</strong>
            <div>
                <a href="{{ route('kategori.edit', $kategori) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                <a href="{{ route('kategori.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
            </div>
        </div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Kode Kategori</dt><dd class="col-sm-9">{{ $kategori->kode_kategori }}</dd>
                <dt class="col-sm-3">Nama Kategori</dt><dd class="col-sm-9">{{ $kategori->nama_kategori }}</dd>
            </dl>
        </div>
    </div>

    <h2 class="h5 mb-3">Buku dalam Kategori Ini</h2>
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>ISBN</th><th>Judul</th><th>Penulis</th><th>Stok</th></tr>
                </thead>
                <tbody>
                    @forelse($bukus as $buku)
                        <tr>
                            <td>{{ $buku->isbn }}</td>
                            <td><a href="{{ route('buku.show', $buku) }}">{{ $buku->judul }}</a></td>
                            <td>{{ $buku->penulis }}</td>
                            <td>{{ $buku->stok }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">Belum ada buku pada kategori ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $bukus->links() }}</div>
    </div>
</x-layout>
