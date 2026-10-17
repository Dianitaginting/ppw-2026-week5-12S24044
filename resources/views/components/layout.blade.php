
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'SIPUS-Del' }} - Perpustakaan IT Del</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark navbar-del shadow-sm mb-4">
        <div class="container">
            <a
                class="navbar-brand fw-bold"
                href="{{ auth()->check() ? route('buku.index') : route('login') }}"
            >
                SIPUS-Del Perpustakaan
            </a>

            <div class="navbar-nav flex-row align-items-center gap-3">
                @auth
                    <a class="nav-link text-white" href="{{ route('buku.index') }}">
                        Buku
                    </a>

                    <a class="nav-link text-white" href="{{ route('kategori.index') }}">
                        Kategori
                    </a>

                    <a class="nav-link text-white" href="{{ route('peminjaman.index') }}">
                        Peminjaman
                    </a>

                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf

                        <button
                            type="submit"
                            class="btn btn-link nav-link text-white text-decoration-none"
                        >
                            Logout
                        </button>
                    </form>
                @else
                    <a class="nav-link text-white" href="{{ route('login') }}">
                        Login
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="container pb-5">

        {{-- Pesan sukses ditampilkan satu kali melalui layout --}}
        @if (session('success'))
            <div
                class="alert alert-success alert-dismissible fade show"
                role="alert"
            >
                {{ session('success') }}

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Tutup"
                ></button>
            </div>
        @endif

        {{-- Pesan validasi ditampilkan satu kali melalui layout --}}
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                Periksa kembali isian form. Masih ada data yang belum valid.

                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{ $slot }}

    </main>

</body>
</html>
