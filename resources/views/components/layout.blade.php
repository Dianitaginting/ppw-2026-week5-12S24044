<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'SIPUS-Del' }} - Perpustakaan IT Del</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .navbar-del { background-color: #4C1D95; }
        .btn-del { background-color: #4C1D95; color: #ffffff; }
        .btn-del:hover { background-color: #3B0764; color: #ffffff; }
    </style>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>