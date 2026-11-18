<?php

namespace App\Http\Controllers;

use App\Models\LabkompItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LabkompInventarisController extends Controller
{
    public function landing(): View
    {
        return view('labkomp.inventaris.landing');
    }

    /** Publik (dipakai form peminjaman). Hanya barang yang stoknya masih ada. */
    public function search(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        $items = LabkompItem::query()
            ->where('stok', '>', 0)
            ->when($q !== '', fn ($x) => $x->where(function ($w) use ($q) {
                $w->where('nama_barang', 'like', "%{$q}%")->orWhere('catatan', 'like', "%{$q}%");
            }))
            ->orderBy('nama_barang')
            ->limit($q === '' ? 20 : 50)
            ->get(['id', 'kode', 'nama_barang', 'stok', 'satuan']);

        return response()->json($items);
    }

    public function index(): View
    {
        return view('labkomp.inventaris.index', ['items' => LabkompItem::orderBy('kode')->get()]);
    }

    public function create(): View
    {
        return view('labkomp.inventaris.form', ['item' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());

        $item = DB::transaction(fn () => LabkompItem::create($data + ['kode' => LabkompItem::nextKode()]));

        return redirect()->route('inventory_labkomp.inventory_list')
            ->with('status', "Barang {$item->kode} ditambahkan.");
    }

    public function edit(LabkompItem $item): View
    {
        return view('labkomp.inventaris.form', ['item' => $item]);
    }

    public function update(Request $request, LabkompItem $item): RedirectResponse
    {
        $item->update($request->validate($this->rules()));

        return redirect()->route('inventory_labkomp.inventory_list')
            ->with('status', "Barang {$item->kode} diperbarui.");
    }

    public function konfirmasiHapus(LabkompItem $item): View
    {
        return view('labkomp.inventaris.hapus', [
            'item' => $item,
            'dipinjam' => $item->sedangDipinjam(),
        ]);
    }

    public function destroy(LabkompItem $item): RedirectResponse
    {
        if ($item->sedangDipinjam()) {
            return redirect()->route('inventory_labkomp.inventory_list')
                ->with('warning', "Barang {$item->kode} masih dipinjam, tidak bisa dihapus.");
        }

        $item->delete(); // riwayat peminjaman tetap utuh (item_id jadi null, nama tersimpan)

        return redirect()->route('inventory_labkomp.inventory_list')
            ->with('status', "Barang {$item->kode} dihapus.");
    }

    private function rules(): array
    {
        return [
            'nama_barang' => ['required', 'string', 'max:255'],
            'stok' => ['required', 'integer', 'min:0', 'max:1000000'],
            'satuan' => ['required', 'string', 'max:20'],
            'kondisi' => ['required', Rule::in(LabkompItem::KONDISI)],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
