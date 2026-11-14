<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventaris;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InventarisController extends Controller
{
    public function index(Request $request): View
    {
        $bidang = $request->query('bidang');
        $bidang = is_string($bidang) && $bidang !== '' ? $bidang : null;
        abort_if($bidang && ! isset(Inventaris::BIDANG[$bidang]), 404);

        $q = $request->query('q');
        $q = is_string($q) ? trim($q) : '';

        return view('admin.inventaris.index', [
            'items' => Inventaris::query()
                ->when($bidang, fn ($x) => $x->where('sub_bidang', Inventaris::BIDANG[$bidang]))
                ->cari($q)
                ->orderBy('kode')
                ->paginate(15)->withQueryString(),
            'bidang' => $bidang,
            'q' => $q,
        ]);
    }

    public function create(): View
    {
        return view('admin.inventaris.form', [
            'item' => new Inventaris(['kondisi' => 'Baik', 'jumlah_total' => 0]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['kode'] = Inventaris::nextKode();
        $item = Inventaris::create($data);

        return redirect()->route('admin.inventaris.index')->with('status', "Barang {$item->kode} ditambahkan.");
    }

    public function edit(Inventaris $inventaris): View
    {
        return view('admin.inventaris.form', ['item' => $inventaris]);
    }

    public function update(Request $request, Inventaris $inventaris): RedirectResponse
    {
        $inventaris->update($this->validated($request, $inventaris));

        return redirect()->route('admin.inventaris.index')->with('status', "Barang {$inventaris->kode} disimpan.");
    }

    public function destroy(Inventaris $inventaris): RedirectResponse
    {
        if ($inventaris->jumlah_dipinjam > 0) {
            return back()->withErrors(['inventaris' => "Tidak bisa dihapus: masih dipinjam {$inventaris->jumlah_dipinjam} unit."]);
        }
        $inventaris->delete();

        return back()->with('status', 'Dihapus.');
    }

    private function validated(Request $request, ?Inventaris $item = null): array
    {
        $dipinjam = $item?->jumlah_dipinjam ?? 0;

        // jumlah_dipinjam sengaja TIDAK dari form (hanya dari modul peminjaman / impor).
        return $request->validate([
            'nama_barang' => ['required', 'string', 'max:150'],
            'merk' => ['required', 'string', 'max:100'],
            'kondisi' => ['required', Rule::in(Inventaris::KONDISI)],
            'sumber_dana' => ['nullable', Rule::in(Inventaris::SUMBER_DANA)],
            'jumlah_total' => ['required', 'integer', "min:{$dipinjam}", 'max:100000'],
            'lokasi' => ['required', 'string', 'max:100'],
            'tahun_pengadaan' => ['required', 'integer', 'between:1990,2100'],
            'sub_bidang' => ['required', Rule::in(Inventaris::SUB_BIDANG)],
        ], [
            'jumlah_total.min' => "Jumlah total tidak boleh kurang dari jumlah yang sedang dipinjam ({$dipinjam}).",
        ]);
    }
}