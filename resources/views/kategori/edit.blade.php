<x-layout title="Edit Kategori">
    <div class="card shadow-sm">
        <div class="card-header bg-white fw-bold">Edit Kategori</div>
        <div class="card-body">
            <form action="{{ route('kategori.update', $kategori) }}" method="POST">
                @csrf
                @method('PUT')
                @include('kategori._form')
                <button type="submit" class="btn btn-del">Simpan Perubahan</button>
                <a href="{{ route('kategori.index') }}" class="btn btn-outline-secondary">Batal</a>
            </form>
        </div>
    </div>
</x-layout>
