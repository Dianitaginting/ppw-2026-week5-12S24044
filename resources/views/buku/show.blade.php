<x-layout title="Detail Buku">
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <strong>Detail Buku</strong>
            <div>
                <a href="{{ route('buku.edit', $buku) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                <a href="{{ route('buku.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
            </div>
        </div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">ISBN</dt><dd class="col-sm-9">{{ $buku->isbn }}</dd>
                <dt class="col-sm-3">Judul</dt><dd class="col-sm-9">{{ $buku->judul }}</dd>
                <dt class="col-sm-3">Penulis</dt><dd class="col-sm-9">{{ $buku->penulis }}</dd>
                <dt class="col-sm-3">Penerbit</dt><dd class="col-sm-9">{{ $buku->penerbit }}</dd>
                <dt class="col-sm-3">Tahun Terbit</dt><dd class="col-sm-9">{{ $buku->tahun_terbit }}</dd>
                <dt class="col-sm-3">Kategori</dt><dd class="col-sm-9">{{ $buku->kategori->nama_kategori }}</dd>
                <dt class="col-sm-3">Stok</dt><dd class="col-sm-9">{{ $buku->stok }}</dd>
                <dt class="col-sm-3">Sinopsis</dt>
                <dd class="col-sm-9">{{ $buku->sinopsis ?: 'Tidak ada sinopsis.' }}</dd>
            </dl>
        </div>
    </div>
</x-layout>
