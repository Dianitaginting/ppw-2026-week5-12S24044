
<x-layout title="Login Petugas">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm mt-5">
                <div class="card-header bg-white">
                    <h1 class="h5 mb-0">
                        Login Petugas Perpustakaan
                    </h1>

                    <p class="text-muted small mb-0 mt-1">
                        Sistem Informasi Perpustakaan Kampus Del
                    </p>
                </div>

                <div class="card-body">
                    {{-- Form login petugas --}}
                    <form action="{{ route('login.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">
                                Email Petugas
                            </label>

                            <input
                                type="email"
                                name="email"
                                id="email"
                                class="form-control"
                                value="{{ old('email') }}"
                                placeholder="Masukkan email petugas"
                                autocomplete="username"
                                required
                                autofocus
                            >
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                Password
                            </label>

                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="form-control"
                                placeholder="Masukkan password"
                                autocomplete="current-password"
                                required
                            >
                        </div>

                        <div class="form-check mb-3">
                            <input
                                type="checkbox"
                                name="remember"
                                id="remember"
                                class="form-check-input"
                                value="1"
                            >

                            <label for="remember" class="form-check-label">
                                Ingat saya
                            </label>
                        </div>

                        <button
                            type="submit"
                            class="btn btn-del w-100"
                        >
                            Login
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layout>
