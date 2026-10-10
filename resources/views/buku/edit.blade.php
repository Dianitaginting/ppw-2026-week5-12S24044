<x-layout title="Edit Buku">
    <div class="card shadow-sm">
        <div class="card-header bg-white fw-bold">Edit Buku</div>
        <div class="card-body">
            <form action="{{ route('buku.update', $buku) }}" method="POST">
                @csrf
                @method('PUT')
                @include('buku._form')
                <button type="submit" class="btn btn-del">Simpan Perubahan</button>
                <a href="{{ route('buku.index') }}" class="btn btn-outline-secondary">Batal</a>
            </form>
        </div>
    </div>
</x-layout>
