<?php

namespace App\Http\Controllers;

use App\Models\LabkompItem;
use App\Models\LabkompPeminjaman;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LabkompPeminjamanController extends Controller
{
    public function landing(): View
    {
        return view('labkomp.peminjaman.landing');
    }

    // ---------- publik (peminjam) ----------

    public function borrow(): View
    {
        // Pulihkan pilihan barang kalau submit sebelumnya ditolak (mis. stok kurang).
        $old = collect(old('items', []));
        $pre = LabkompItem::whereIn('id', $old->pluck('item_id')->filter()->all())->get()
            ->map(fn ($i) => [
                'id' => $i->id,
                'nama_barang' => $i->nama_barang,
                'stok' => $i->stok,
                'qty' => (int) ($old->firstWhere('item_id', $i->id)['qty'] ?? 1),
            ])->values();

        return view('labkomp.peminjaman.borrow', ['preselected' => $pre]);
    }

    public function submit(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama_peminjam' => ['required', 'string', 'max:100'],
            'email_peminjam' => ['required', 'email', 'max:150'],
            'tgl_pinjam' => ['required', 'date'],
            'tgl_kembali' => ['required', 'date', 'after_or_equal:tgl_pinjam'],
            'catatan' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1', 'max:30'],
            'items.*.item_id' => ['required', 'integer', 'distinct'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        try {
            $loan = DB::transaction(function () use ($data) {
                $items = LabkompItem::whereIn('id', array_column($data['items'], 'item_id'))
                    ->lockForUpdate()->get()->keyBy('id');

                $loan = LabkompPeminjaman::create([
                    'uuid' => (string) Str::uuid(),
                    'kode' => LabkompPeminjaman::buatKode(),
                    'nama_peminjam' => $data['nama_peminjam'],
                    'email_peminjam' => $data['email_peminjam'],
                    'tgl_pinjam' => $data['tgl_pinjam'],
                    'tgl_kembali' => $data['tgl_kembali'],
                    'catatan' => $data['catatan'] ?? null,
                    'status' => LabkompPeminjaman::DIPINJAM,
                ]);

                foreach ($data['items'] as $row) {
                    $item = $items->get((int) $row['item_id']);
                    $qty = (int) $row['qty'];

                    if (! $item) {
                        throw new \RuntimeException('Ada barang yang tidak ditemukan.');
                    }
                    if ($qty > $item->stok) {
                        throw new \RuntimeException("Stok {$item->nama_barang} tidak cukup (tersisa {$item->stok}).");
                    }

                    $item->decrement('stok', $qty);
                    $loan->items()->create([
                        'item_id' => $item->id,
                        'kode_barang' => $item->kode,
                        'nama_barang' => $item->nama_barang,
                        'qty' => $qty,
                    ]);
                }

                return $loan;
            });
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('warning', $e->getMessage());
        }

        return redirect()->route('peminjaman_invlabkomp.receipt', $loan->uuid)
            ->with('status', "Peminjaman berhasil. Kode: {$loan->kode}");
    }

    /** Bukti peminjaman, publik lewat uuid (bisa dibagikan). */
    public function receipt(string $uuid): View
    {
        $loan = LabkompPeminjaman::with('items')->where('uuid', $uuid)->firstOrFail();

        return view('labkomp.peminjaman.receipt', ['loan' => $loan]);
    }

    // ---------- login-only (petugas) ----------

    public function history(): View
    {
        return view('labkomp.peminjaman.history', [
            'loans' => LabkompPeminjaman::with('items')->latest('id')->get(),
        ]);
    }

    public function detail(string $kode): View
    {
        $loan = LabkompPeminjaman::with('items')->where('kode', strtoupper($kode))->firstOrFail();

        return view('labkomp.peminjaman.detail', [
            'loan' => $loan,
            'receiptUrl' => route('peminjaman_invlabkomp.receipt', $loan->uuid),
        ]);
    }

    public function returnPage(): View
    {
        return view('labkomp.peminjaman.return');
    }

    public function apiLoan(Request $request): JsonResponse
    {
        $kode = strtoupper(trim((string) $request->query('code', '')));
        $loan = $kode === '' ? null : LabkompPeminjaman::with('items')->where('kode', $kode)->first();

        if (! $loan) {
            return response()->json(['ok' => false, 'error' => 'Kode tidak ditemukan']);
        }

        return response()->json(['ok' => true, 'loan' => [
            'kode' => $loan->kode,
            'nama_peminjam' => $loan->nama_peminjam,
            'tgl_pinjam' => $loan->tgl_pinjam->format('d-m-Y'),
            'tgl_kembali' => $loan->tgl_kembali->format('d-m-Y'),
            'catatan' => $loan->catatan,
            'status' => $loan->status,
            'items' => $loan->items->map(fn ($i) => ['nama_barang' => $i->nama_barang, 'qty' => $i->qty])->values(),
        ]]);
    }

    public function returnSubmit(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20'],
            'proof' => ['nullable', 'image', 'max:2048'],
        ]);

        try {
            $loan = DB::transaction(function () use ($data, $request) {
                $loan = LabkompPeminjaman::with('items')
                    ->where('kode', strtoupper($data['code']))->lockForUpdate()->first();

                if (! $loan) {
                    throw new \RuntimeException('Kode peminjaman tidak ditemukan.');
                }
                if ($loan->status === LabkompPeminjaman::DIKEMBALIKAN) {
                    throw new \RuntimeException('Peminjaman ini sudah dikembalikan.');
                }

                foreach ($loan->items as $li) {
                    if ($li->item_id) {
                        LabkompItem::whereKey($li->item_id)->increment('stok', $li->qty);
                    }
                }

                $path = null;
                if ($request->hasFile('proof')) {
                    $file = $request->file('proof');
                    $name = $loan->kode.'-'.time().'.'.$file->extension();
                    $file->move(public_path('uploads/labkomp-bukti'), $name);
                    $path = 'uploads/labkomp-bukti/'.$name;
                }

                $loan->update([
                    'status' => LabkompPeminjaman::DIKEMBALIKAN,
                    'bukti_kembali' => $path,
                    'dikembalikan_at' => now(),
                ]);

                return $loan;
            });
        } catch (\RuntimeException $e) {
            return back()->with('warning', $e->getMessage());
        }

        return redirect()->route('peminjaman_invlabkomp.return_page')
            ->with('status', "Peminjaman {$loan->kode} dikembalikan.");
    }
}
