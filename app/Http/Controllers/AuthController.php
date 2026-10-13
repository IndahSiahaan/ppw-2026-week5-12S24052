<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class AuthController extends Controller
{
    // Menampilkan halaman login
    public function showLogin()
    {
        return view('auth.login');
    }

    // Memproses login pustakawan
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // KRUSIAL (Sesuai Modul): Cegah serangan Session Fixation
            $request->session()->regenerate();

            // Simpan timestamp login ke Sesi
            session(['login_timestamp' => now()->format('d M Y, H:i:s') . ' WIB']);

            // Simpan email terakhir di HTTP Cookie selama 7 hari (10080 menit)
            Cookie::queue('sipus_last_email', $request->email, 10080);

            return redirect()->intended(route('buku.index'))->with('success', 'Selamat datang! Sesi Pustakawan aktif.');
        }

        return back()->with('error', 'Kredensial login email atau password salah.');
    }

    // Memproses logout pustakawan
    public function logout(Request $request)
    {
        Auth::logout();

        // Bersihkan seluruh sesi dan token CSRF
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Sesi Anda telah diakhiri dengan aman.');
    }
}