<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengumuman;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PengumumanAdminController extends Controller
{
    public function index(): View
    {
        return view('admin.pengumuman.index', [
            'items' => Pengumuman::orderByDesc('updated_at')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.pengumuman.form', ['item' => new Pengumuman]);
    }

    public function store(Request $request): RedirectResponse
    {
        Pengumuman::create($this->validated($request));

        return redirect()->route('admin.pengumuman.index')->with('status', 'Ditambahkan.');
    }

    public function edit(Pengumuman $pengumuman): View
    {
        return view('admin.pengumuman.form', ['item' => $pengumuman]);
    }

    public function update(Request $request, Pengumuman $pengumuman): RedirectResponse
    {
        $pengumuman->update($this->validated($request));

        return redirect()->route('admin.pengumuman.index')->with('status', 'Disimpan.');
    }

    public function destroy(Pengumuman $pengumuman): RedirectResponse
    {
        $pengumuman->delete();

        return back()->with('status', 'Dihapus.');
    }

    private function validated(Request $request): array
    {
        $d = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'deskripsi' => ['required', 'string', 'max:200000'],
        ], [
            'title.required' => 'Judul wajib diisi.',
            'deskripsi.required' => 'Isi pengumuman wajib diisi.',
        ]);

        $d['title'] = trim($d['title']);
        $d['deskripsi'] = clean($d['deskripsi'], 'quill'); // sanitasi sisi server

        // Quill kosong = <p><br></p>
        if (trim(strip_tags($d['deskripsi'])) === '') {
            throw ValidationException::withMessages(['deskripsi' => 'Isi pengumuman wajib diisi.']);
        }

        return $d;
    }
}