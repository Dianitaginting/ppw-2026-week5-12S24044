<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PetugasAuthController extends Controller
{
    /**
     * Menampilkan halaman login petugas.
     */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Memproses login petugas.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // Mencegah session fixation setelah login.
            $request->session()->regenerate();

            return redirect()
                ->intended(route('buku.index'))
                ->with('success', 'Login berhasil.');
        }

        return back()
            ->withErrors([
                'email' => 'Email atau password tidak sesuai.',
            ])
            ->onlyInput('email');
    }

    /**
     * Memproses logout petugas.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        // Menghapus data session dan membuat token CSRF baru.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'Kamu berhasil logout.');
    }
}
