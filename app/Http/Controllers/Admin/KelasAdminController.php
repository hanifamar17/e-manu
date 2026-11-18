<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\User;
use App\Services\KelasMatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Kelas dibuat/dihapus lewat Impor; di sini hanya ubah wali kelas, no HP, akun login. */
class KelasAdminController extends Controller
{
    public function index(): View
    {
        return view('admin.kelas.index', [
            'items' => Kelas::with('user')->orderBy('nama_kelas')->get(),
        ]);
    }

    public function edit(Kelas $kelas): View
    {
        return view('admin.kelas.form', [
            'item' => $kelas,
            'akun' => User::where('role', 'wali_kelas')->orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, Kelas $kelas): RedirectResponse
    {
        $data = $request->validate([
            'wali_kelas' => ['nullable', 'string', 'max:100'],
            'no_hp' => ['nullable', 'string', 'max:25'],
            'user_id' => ['nullable', Rule::exists('users', 'id')->where('role', 'wali_kelas')],
        ], [
            'user_id.exists' => 'Akun tidak ditemukan atau bukan role wali_kelas.',
        ]);

        $hp = KelasMatcher::hp($data['no_hp'] ?? '');
        if (($data['no_hp'] ?? '') !== '' && $hp === null) {
            return back()->withInput()->withErrors(['no_hp' => 'No HP tidak valid (10-15 digit, mis. 081234567890).']);
        }

        $kelas->update([
            'wali_kelas' => ($data['wali_kelas'] ?? '') ?: null,
            'no_hp' => $hp,
            'user_id' => $data['user_id'] ?? null,
        ]);

        return redirect()->route('admin.kelas.index')
            ->with('status', "Data kelas {$kelas->nama_kelas} berhasil diperbarui: wali kelas, nomor HP, dan akun login tersimpan.");
    }
}
