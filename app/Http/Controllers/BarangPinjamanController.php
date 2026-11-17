<?php

namespace App\Http\Controllers;

use App\Models\BarangPinjaman;
use App\Models\Inventaris;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BarangPinjamanController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->query('q');
        $q = is_string($q) ? trim($q) : '';

        return view('barang_pinjaman.index', [
            'items' => BarangPinjaman::query()->cari($q)->orderBy('kode')->paginate(15)->withQueryString(),
            'q' => $q,
        ]);
    }

    public function create(): View
    {
        return view('barang_pinjaman.form', [
            'item' => new BarangPinjaman(['kondisi' => 'Baik', 'jumlah_total' => 0]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['kode'] = BarangPinjaman::nextKode();
        $item = BarangPinjaman::create($data);

        return redirect()->route('barang_pinjaman.index')->with('status', "Barang pinjaman {$item->kode} ditambahkan.");
    }

    public function edit(BarangPinjaman $barang_pinjaman): View
    {
        return view('barang_pinjaman.form', ['item' => $barang_pinjaman]);
    }

    public function update(Request $request, BarangPinjaman $barang_pinjaman): RedirectResponse
    {
        $barang_pinjaman->update($this->validated($request, $barang_pinjaman));

        return redirect()->route('barang_pinjaman.index')->with('status', "Barang pinjaman {$barang_pinjaman->kode} disimpan.");
    }

    public function konfirmasiHapus(BarangPinjaman $barang_pinjaman): View
    {
        return view('barang_pinjaman.hapus', ['item' => $barang_pinjaman]);
    }

    public function destroy(BarangPinjaman $barang_pinjaman): RedirectResponse
    {
        if ($barang_pinjaman->jumlah_dipinjam > 0) {
            return back()->withErrors(['barang_pinjaman' => "Tidak bisa dihapus: masih dipinjam {$barang_pinjaman->jumlah_dipinjam} unit."]);
        }
        $kode = $barang_pinjaman->kode;
        $barang_pinjaman->delete();

        return redirect()->route('barang_pinjaman.index')->with('status', "Barang pinjaman {$kode} dihapus.");
    }

    private function validated(Request $request, ?BarangPinjaman $item = null): array
    {
        $dipinjam = $item?->jumlah_dipinjam ?? 0;

        // jumlah_dipinjam sengaja TIDAK dari form (hanya dari modul peminjaman).
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
