<x-layout title="Katalog Buku">
    <div class="row">
        <!-- Form Tambah Buku -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-bold">Tambah Buku Baru</div>
                <div class="card-body">
                    <form action="{{ route('buku.store') }}" method="POST">
                        @csrf

                        <div class="mb-2">
                            <label class="form-label">ISBN</label>
                            <input type="text" name="isbn" class="form-control @error('isbn') is-invalid @enderror" value="{{ old('isbn') }}">
                            @error('isbn')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Judul Buku</label>
                            <input type="text" name="judul" class="form-control @error('judul') is-invalid @enderror" value="{{ old('judul') }}">
                            @error('judul')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Penulis</label>
                            <input type="text" name="penulis" class="form-control @error('penulis') is-invalid @enderror" value="{{ old('penulis') }}">
                            @error('penulis')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Penerbit</label>
                            <input type="text" name="penerbit" class="form-control @error('penerbit') is-invalid @enderror" value="{{ old('penerbit') }}">
                            @error('penerbit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row">
                            <div class="col-6 mb-2">
                                <label class="form-label">Tahun</label>
                                <input type="number" name="tahun_terbit" class="form-control @error('tahun_terbit') is-invalid @enderror" value="{{ old('tahun_terbit', date('Y')) }}">
                                @error('tahun_terbit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-6 mb-2">
                                <label class="form-label">Stok</label>
                                <input type="number" name="stok" class="form-control @error('stok') is-invalid @enderror" value="{{ old('stok', 0) }}">
                                @error('stok')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select name="kategori_id" class="form-select @error('kategori_id') is-invalid @enderror">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($kategoris as $ktg)
                                    <option value="{{ $ktg->id }}" {{ old('kategori_id') == $ktg->id ? 'selected' : '' }}>
                                        {{ $ktg->nama_kategori }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kategori_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <button type="submit" class="btn btn-del w-100">Simpan Buku</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tabel Daftar Buku -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-bold">Daftar Katalog Buku</div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ISBN</th>
                                <th>Judul</th>
                                <th>Penulis</th>
                                <th>Kategori</th>
                                <th>Stok</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bukus as $b)
                                <tr>
                                    <td><small class="text-muted">{{ $b->isbn }}</small></td>
                                    <td class="fw-bold">{{ $b->judul }}</td>
                                    <td>{{ $b->penulis }}</td>
                                    <td><span class="badge bg-secondary">{{ $b->kategori->nama_kategori ?? '-' }}</span></td>
                                    <td>{{ $b->stok }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Belum ada koleksi buku.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white">
                    {{ $bukus->links() }}
                </div>
            </div>
        </div>
    </div>
</x-layout>