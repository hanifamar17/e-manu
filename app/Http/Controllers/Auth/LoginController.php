<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function show(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
{
    $data = $request->validate([
        'username' => ['required', 'string', 'max:100'],
        'password' => ['required', 'string', 'max:255'],
    ], [
        'username.required' => 'Username wajib diisi.',
        'password.required' => 'Password wajib diisi.',
    ]);

    $username = trim($data['username']);
    $u  = Str::lower($username);
    $ip = $request->ip();

    $kUI = "login:$u|$ip";    // username + IP
    $kIp = "login-ip:$ip";    // IP saja
    $kU  = "login-user:$u";   // username global

    // 1. Cek terkunci dulu
    foreach ([[$kUI, 5], [$kIp, 20], [$kU, 15]] as [$k, $max]) {
        if (RateLimiter::tooManyAttempts($k, $max)) {
            Log::warning('Login terkunci', ['user' => Str::limit(preg_replace('/\s+/', ' ', $u), 30), 'ip' => $ip, 'kunci' => $k]);

            throw ValidationException::withMessages([
                'username' => 'Terlalu banyak percobaan. Coba lagi dalam '.RateLimiter::availableIn($k).' detik.',
            ]);
        }
    }

    // 2. Gagal: catat di 3 lapis
    if (! Auth::attempt(['username' => $username, 'password' => $data['password']])) {
        RateLimiter::hit($kUI, 900);    // 15 menit
        RateLimiter::hit($kIp, 900);
        RateLimiter::hit($kU, 1800);    // 30 menit

        throw ValidationException::withMessages(['username' => 'Username atau password salah.']);
    }

    // 3. Berhasil: reset lapis username+IP
    RateLimiter::clear($kUI);
    $request->session()->regenerate();

    // admin -> dashboard, lainnya -> repositori
    $tujuan = $request->user()->hasRole('admin') ? route('admin.home') : route('repository');

    // intended() = URL tujuan disimpan server-side -> tidak ada open redirect
    return redirect()->intended($tujuan);
}

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('repository');
    }
}
