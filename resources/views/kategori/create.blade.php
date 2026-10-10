<x-layout title="Tambah Kategori">
    <div class="card shadow-sm">
        <div class="card-header bg-white fw-bold">Tambah Kategori Baru</div>
        <div class="card-body">
            <form action="{{ route('kategori.store') }}" method="POST">
                @csrf
                @include('kategori._form')
                <button type="submit" class="btn btn-del">Simpan Kategori</button>
                <a href="{{ route('kategori.index') }}" class="btn btn-outline-secondary">Batal</a>
            </form>
        </div>
    </div>
</x-layout>
