<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'SIPUS-Del' }} - Perpustakaan IT Del</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark navbar-del shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('buku.index') }}">SIPUS-Del Perpustakaan</a>
            <div class="navbar-nav flex-row gap-3">
                <a class="nav-link text-white" href="{{ route('buku.index') }}">Buku</a>
                <a class="nav-link text-white" href="{{ route('kategori.index') }}">Kategori</a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger" role="alert">
                Periksa kembali isian form. Masih ada data yang belum valid.
            </div>
        @endif

        {{ $slot }}
    </div>
</body>
</html>