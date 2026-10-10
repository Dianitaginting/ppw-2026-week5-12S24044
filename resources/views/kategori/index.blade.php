<x-layout title="Kategori Buku">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1">Kategori Buku</h1>
            <p class="text-muted mb-0">Kelola kategori koleksi perpustakaan.</p>
        </div>
        <a href="{{ route('kategori.create') }}" class="btn btn-del">Tambah Kategori</a>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kode</th>
                        <th>Nama Kategori</th>
                        <th>Jumlah Buku</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kategoris as $kategori)
                        <tr>
                            <td>{{ $kategori->kode_kategori }}</td>
                            <td class="fw-semibold">{{ $kategori->nama_kategori }}</td>
                            <td>{{ $kategori->bukus_count }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('kategori.show', $kategori) }}" class="btn btn-sm btn-outline-secondary">Detail</a>
                                <a href="{{ route('kategori.edit', $kategori) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form action="{{ route('kategori.destroy', $kategori) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus kategori beserta seluruh buku di dalamnya?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Belum ada kategori.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $kategoris->links() }}
        </div>
    </div>
</x-layout>
