<x-layout title="Tambah Buku">
    <div class="card shadow-sm">
        <div class="card-header bg-white fw-bold">Tambah Buku Baru</div>
        <div class="card-body">
            <form action="{{ route('buku.store') }}" method="POST">
                @csrf
                @include('buku._form')
                <button type="submit" class="btn btn-del">Simpan Buku</button>
                <a href="{{ route('buku.index') }}" class="btn btn-outline-secondary">Batal</a>
            </form>
        </div>
    </div>
</x-layout>
