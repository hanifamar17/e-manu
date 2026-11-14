<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KalenderAkademik;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KalenderAkademikController extends Controller
{
    public function index(): View
    {
        return view('admin.kalender.index', [
            'items' => KalenderAkademik::orderByDesc('tanggal_mulai')->paginate(30),
        ]);
    }

    public function create(): View
    {
        return view('admin.kalender.form', ['item' => new KalenderAkademik(['kategori' => 'agenda'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        KalenderAkademik::create($this->validated($request));

        return redirect()->route('admin.kalender.index')->with('status', 'Ditambahkan.');
    }

    public function edit(KalenderAkademik $kalender): View
    {
        return view('admin.kalender.form', ['item' => $kalender]);
    }

    public function update(Request $request, KalenderAkademik $kalender): RedirectResponse
    {
        $kalender->update($this->validated($request));

        return redirect()->route('admin.kalender.index')->with('status', 'Disimpan.');
    }

    public function destroy(KalenderAkademik $kalender): RedirectResponse
    {
        $kalender->delete();

        return back()->with('status', 'Dihapus.');
    }

    private function validated(Request $request): array
    {
        $d = $request->validate([
            'tanggal_mulai' => ['required', 'date_format:Y-m-d'],
            'tanggal_selesai' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:tanggal_mulai'],
            'keterangan' => ['required', 'string', 'max:200'],
            'kategori' => ['required', Rule::in(KalenderAkademik::KATEGORI)],
        ]);
        $d['tanggal_selesai'] ??= $d['tanggal_mulai'];

        return $d;
    }
}
