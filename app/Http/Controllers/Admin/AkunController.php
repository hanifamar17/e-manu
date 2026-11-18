<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AkunController extends Controller
{
    public const ROLES = ['admin' => 'Admin', 'guru' => 'Guru', 'wali_kelas' => 'Wali kelas'];

    public function index(): View
    {
        return view('admin.akun.index', [
            'items' => User::orderBy('role')->orderBy('username')->get(),
            'kelasBy' => Kelas::whereNotNull('user_id')->pluck('nama_kelas', 'user_id'),
            'roles' => self::ROLES,
        ]);
    }

    public function create(): View
    {
        return view('admin.akun.form', ['item' => new User, 'roles' => self::ROLES]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/', 'unique:users,username'],
            'nama' => ['required', 'string', 'max:100'],
            'role' => ['required', Rule::in(array_keys(self::ROLES))],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], ['username.regex' => 'Username hanya huruf, angka, titik, strip, garis bawah (tanpa spasi).']);

        $data['username'] = strtolower($data['username']);
        User::create($data);

        return redirect()->route('admin.akun.index')
            ->with('status', "Akun {$data['username']} ({$data['role']}) berhasil dibuat. Untuk wali kelas, hubungkan akun ini ke kelasnya lewat menu Kelas & Wali Kelas.");
    }

    public function edit(User $akun): View
    {
        return view('admin.akun.form', ['item' => $akun, 'roles' => self::ROLES]);
    }

    public function update(Request $request, User $akun): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'role' => ['required', Rule::in(array_keys(self::ROLES))],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if ($data['role'] !== $akun->role && $akun->role === 'admin' && ($akun->is($request->user()) || User::where('role', 'admin')->count() <= 1)) {
            return back()->withInput()->withErrors(['role' => 'Role admin tidak boleh diubah untuk akun sendiri / admin terakhir.']);
        }

        if (($data['password'] ?? '') === '') {
            unset($data['password']); // kosong = password tetap
        }
        $akun->update($data);

        return redirect()->route('admin.akun.index')
            ->with('status', "Akun {$akun->username} berhasil diperbarui".(isset($data['password']) ? ', password diganti.' : '.'));
    }

    public function konfirmasiHapus(User $akun): View
    {
        return view('admin.akun.hapus', [
            'item' => $akun,
            'kelas' => Kelas::where('user_id', $akun->id)->pluck('nama_kelas'),
            'roles' => self::ROLES,
        ]);
    }

    public function destroy(Request $request, User $akun): RedirectResponse
    {
        if ($akun->is($request->user())) {
            return redirect()->route('admin.akun.index')->with('warning', 'Akun yang sedang dipakai login tidak bisa dihapus.');
        }
        if ($akun->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return redirect()->route('admin.akun.index')->with('warning', 'Admin terakhir tidak bisa dihapus.');
        }

        $nama = $akun->username;
        $akun->delete(); // kelas.user_id otomatis null (nullOnDelete)

        return redirect()->route('admin.akun.index')->with('status', "Akun {$nama} berhasil dihapus. Kelas yang terhubung kehilangan akun wali kelasnya.");
    }
}
