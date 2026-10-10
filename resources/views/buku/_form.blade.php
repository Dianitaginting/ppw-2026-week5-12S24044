<div class="mb-3">
    <label for="isbn" class="form-label">ISBN</label>
    <input id="isbn" type="text" name="isbn" maxlength="20" required
        class="form-control @error('isbn') is-invalid @enderror"
        value="{{ old('isbn', $buku->isbn ?? '') }}">
    @error('isbn')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="judul" class="form-label">Judul Buku</label>
    <input id="judul" type="text" name="judul" maxlength="255" minlength="5" required
        class="form-control @error('judul') is-invalid @enderror"
        value="{{ old('judul', $buku->judul ?? '') }}">
    @error('judul')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="penulis" class="form-label">Penulis</label>
        <input id="penulis" type="text" name="penulis" maxlength="150" required
            class="form-control @error('penulis') is-invalid @enderror"
            value="{{ old('penulis', $buku->penulis ?? '') }}">
        @error('penulis')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="penerbit" class="form-label">Penerbit</label>
        <input id="penerbit" type="text" name="penerbit" maxlength="150" required
            class="form-control @error('penerbit') is-invalid @enderror"
            value="{{ old('penerbit', $buku->penerbit ?? '') }}">
        @error('penerbit')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="tahun_terbit" class="form-label">Tahun Terbit</label>
        <input id="tahun_terbit" type="number" name="tahun_terbit" min="1000" max="9999" required
            class="form-control @error('tahun_terbit') is-invalid @enderror"
            value="{{ old('tahun_terbit', $buku->tahun_terbit ?? date('Y')) }}">
        @error('tahun_terbit')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="stok" class="form-label">Stok</label>
        <input id="stok" type="number" name="stok" min="0" required
            class="form-control @error('stok') is-invalid @enderror"
            value="{{ old('stok', $buku->stok ?? 0) }}">
        @error('stok')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="mb-3">
    <label for="kategori_id" class="form-label">Kategori</label>
    <select id="kategori_id" name="kategori_id" required
        class="form-select @error('kategori_id') is-invalid @enderror">
        <option value="">-- Pilih Kategori --</option>
        @foreach($kategoris as $kategori)
            <option value="{{ $kategori->id }}"
                @selected((string) old('kategori_id', $buku->kategori_id ?? '') === (string) $kategori->id)>
                {{ $kategori->nama_kategori }}
            </option>
        @endforeach
    </select>
    @error('kategori_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="sinopsis" class="form-label">Sinopsis <span class="text-muted">(opsional)</span></label>
    <textarea id="sinopsis" name="sinopsis" rows="4"
        class="form-control @error('sinopsis') is-invalid @enderror">{{ old('sinopsis', $buku->sinopsis ?? '') }}</textarea>
    @error('sinopsis')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
