<?php

namespace App\Http\Controllers;

use App\Models\Inventaris;
use App\Models\Peminjaman;
use Dompdf\Dompdf;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PeminjamanAsetController extends Controller
{
    private const ROMAWI = [
        1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
        7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
    ];

    public function landing()
    {
        return view('peminjaman_aset.landing');
    }

    public function form()
    {
        return view('peminjaman_aset.form');
    }

    // ---------- autocomplete barang ----------
    public function searchBarang(Request $r): JsonResponse
    {
        $q = trim((string) $r->query('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['items' => []]);
        }
        $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';

        $items = Inventaris::query()
            ->where(fn ($w) => $w->where('nama_barang', 'like', $like)
                ->orWhere('merk', 'like', $like)
                ->orWhere('lokasi', 'like', $like))
            ->limit(20)->get()
            ->map(fn ($i) => [
                'id' => $i->id,
                'nama_barang' => $i->nama_barang,
                'merk' => $i->merk ?? '',
                'kondisi' => $i->kondisi ?? '',
                'lokasi' => $i->lokasi ?? '',
                'sub_bidang' => $i->sub_bidang ?? '',
                'jumlah_tersedia' => max($i->jumlah_total - $i->jumlah_dipinjam, 0),
            ])
            ->sortBy(fn ($x) => $x['jumlah_tersedia'] <= 0)
            ->values();

        return response()->json(['items' => $items]);
    }

    // ---------- submit peminjaman ----------
    public function submit(Request $r): JsonResponse
    {
        $data = $r->validate([
            'nama' => 'required|string|max:150',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'pekerjaan' => 'required|string|max:150',
            'alamat' => 'required|string|max:255',
            'telepon' => 'required|string|max:30',
            'no_identitas' => 'required|string|max:50',
            'tujuan' => 'required|string|max:255',
            'lokasi_penggunaan' => 'required|string|max:150',
            'tgl_pinjam' => 'required|date',
            'tgl_kembali' => 'required|date|after_or_equal:tgl_pinjam',
            'kota' => 'required|string|max:80',
            'tgl_surat' => 'required|date',
            'nama_petugas' => 'required|string|max:150',
            'jenis_identitas_jaminan' => 'nullable|in:KTP,SIM',
            'barang_id' => 'required|array|min:1',
            'barang_id.*' => 'required|integer|exists:inventaris,id',
            'barang_jumlah' => 'required|array',
            'barang_jumlah.*' => 'required|integer|min:1',
        ], [
            'barang_id.required' => 'Minimal pilih 1 barang.',
            'barang_id.min' => 'Minimal pilih 1 barang.',
            'tgl_kembali.after_or_equal' => 'Tanggal kembali tidak boleh sebelum tanggal pinjam.',
        ]);

        if (count($data['barang_id']) !== count($data['barang_jumlah'])) {
            throw ValidationException::withMessages(['barang' => 'Data barang tidak valid.']);
        }
        if (! $r->boolean('jaminan_identitas') && ! $r->boolean('jaminan_surat')) {
            throw ValidationException::withMessages(['jaminan' => 'Pilih minimal 1 jaminan.']);
        }

        // gabungkan id ganda
        $want = [];
        foreach ($data['barang_id'] as $i => $id) {
            $want[(int) $id] = ($want[(int) $id] ?? 0) + (int) $data['barang_jumlah'][$i];
        }

        $fields = Arr::only($data, [
            'nama', 'jenis_kelamin', 'pekerjaan', 'alamat', 'telepon', 'no_identitas', 'tujuan',
            'lokasi_penggunaan', 'tgl_pinjam', 'tgl_kembali', 'kota', 'tgl_surat', 'nama_petugas',
        ]);
        $fields['jaminan_identitas'] = $r->boolean('jaminan_identitas');
        $fields['jaminan_surat'] = $r->boolean('jaminan_surat');
        $fields['jenis_identitas_jaminan'] = $fields['jaminan_identitas']
            ? ($data['jenis_identitas_jaminan'] ?? 'KTP') : null;

        // retry kalau nomor surat bentrok (dua submit bersamaan)
        $p = retry(
            3,
            fn () => DB::transaction(fn () => $this->createLoan($fields, $want)),
            50,
            fn ($e) => $e instanceof UniqueConstraintViolationException
        );

        return response()->json([
            'success' => true,
            'message' => 'Peminjaman berhasil diajukan.',
            'ringkasan_url' => route('peminjaman.ringkasan', $p),
        ]);
    }

    private function createLoan(array $fields, array $want): Peminjaman
    {
        $inv = Inventaris::whereIn('id', array_keys($want))->lockForUpdate()->get()->keyBy('id');

        foreach ($want as $id => $qty) {
            $item = $inv->get($id);
            if (! $item) {
                throw ValidationException::withMessages(['barang' => "Barang id {$id} tidak ditemukan."]);
            }
            $tersedia = $item->jumlah_total - $item->jumlah_dipinjam;
            if ($qty > $tersedia) {
                throw ValidationException::withMessages([
                    'barang' => "Stok {$item->nama_barang} tidak cukup (tersedia: {$tersedia}, diminta: {$qty}).",
                ]);
            }
        }

        $p = Peminjaman::create($fields + [
            'record_id' => (string) Str::uuid(),
            'nomor_surat' => $this->nextNomorSurat(),
        ]);

        $no = 1;
        foreach ($want as $id => $qty) {
            $item = $inv[$id];
            $p->items()->create([
                'inventaris_id' => $item->id,
                'no' => $no++,
                'nama_barang' => $item->nama_barang,   // snapshot dari DB, bukan dari input klien
                'merk' => $item->merk,
                'jumlah' => $qty,
                'kondisi' => $item->kondisi ?: 'Baik',
            ]);
            $item->increment('jumlah_dipinjam', $qty);
        }

        return $p;
    }

    private function nextNomorSurat(): string
    {
        $now = now();
        $suffix = '/PA/MANU/' . self::ROMAWI[$now->month] . '/' . $now->year;
        $max = Peminjaman::where('nomor_surat', 'like', '%' . $suffix)
            ->pluck('nomor_surat')
            ->map(fn ($n) => (int) Str::before($n, '/'))
            ->max() ?? 0;

        return sprintf('%03d%s', $max + 1, $suffix);
    }

    // ---------- ringkasan + PDF ----------
    public function ringkasan(Peminjaman $peminjaman)
    {
        $peminjaman->load('items');
        // halaman publik: samarkan no. identitas, petugas login lihat penuh
        $noIdentitas = auth()->check()
            ? $peminjaman->no_identitas
            : Str::mask($peminjaman->no_identitas, '*', 0, -4);

        return view('peminjaman_aset.ringkasan', ['p' => $peminjaman, 'noIdentitas' => $noIdentitas]);
    }

    public function generate(Request $r, Peminjaman $peminjaman): JsonResponse
    {
        if (! $r->boolean('setuju')) {
            return response()->json(['success' => false, 'message' => 'Anda harus menyetujui ringkasan terlebih dahulu.'], 422);
        }
        $disk = Storage::disk('local');
        if ($peminjaman->pdf_path && $disk->exists($peminjaman->pdf_path)) {
            return response()->json(['success' => true, 'message' => 'PDF sudah pernah dibuat.']);
        }

        try {
            $peminjaman->load('items');
            $pdf = new Dompdf(['defaultFont' => 'Times', 'isRemoteEnabled' => false]);
            $pdf->loadHtml(view('peminjaman_aset.pdf', ['p' => $peminjaman])->render());
            $pdf->setPaper('A4', 'portrait');
            $pdf->render();

            $path = "peminjaman/{$peminjaman->record_id}.pdf";
            $disk->put($path, $pdf->output());
            $peminjaman->update(['pdf_path' => $path]);

            return response()->json(['success' => true, 'message' => 'PDF berhasil dibuat.']);
        } catch (\Throwable $e) {
            Log::error("Gagal generate PDF peminjaman {$peminjaman->record_id}: {$e->getMessage()}");
            return response()->json(['success' => false, 'message' => 'Gagal membuat PDF, coba lagi.'], 500);
        }
    }

    public function preview(Peminjaman $peminjaman)
    {
        $disk = Storage::disk('local');
        abort_unless($peminjaman->pdf_path && $disk->exists($peminjaman->pdf_path), 404);

        $name = str_replace('/', '-', $peminjaman->nomor_surat) . '_' . Str::slug($peminjaman->nama, '_') . '.pdf';

        return $disk->response($peminjaman->pdf_path, $name, ['Content-Type' => 'application/pdf']);
    }

    // ---------- riwayat ----------
    public function riwayat(Request $r)
    {
        $q = trim((string) $r->query('q', ''));
        $rows = Peminjaman::with('items')
            ->when($q !== '', fn ($qq) => $qq->where(fn ($w) => $w
                ->where('nama', 'like', "%{$q}%")
                ->orWhere('nomor_surat', 'like', "%{$q}%")))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('peminjaman_aset.riwayat', ['rows' => $rows, 'q' => $q]);
    }

    // ---------- pengembalian ----------
    public function returnPage()
    {
        return view('peminjaman_aset.return');
    }

    public function apiLoan(Request $r): JsonResponse
    {
        $code = strtoupper(trim((string) $r->query('code', '')));
        $p = Peminjaman::with('items')->where('nomor_surat', $code)->first();
        if (! $p) {
            return response()->json(['ok' => false, 'error' => 'Tidak ditemukan']);
        }

        return response()->json(['ok' => true, 'loan' => [
            'code' => $p->nomor_surat,
            'borrower_name' => $p->nama,
            'loan_date' => $p->tgl_pinjam->toDateString(),
            'return_date' => $p->tgl_kembali->toDateString(),
            'note' => $p->tujuan,
            'status' => $p->sudah_kembali ? 'dikembalikan' : 'dipinjam',
            'items' => $p->items->map(fn ($i) => [
                'item_name' => $i->nama_barang,
                'merk' => $i->merk,
                'qty' => $i->jumlah,
                'dikembalikan' => $i->dikembalikan,
            ])->values(),
        ]]);
    }

    public function returnSubmit(Request $r): JsonResponse
    {
        $r->validate(['code' => 'required|string|max:40', 'catatan' => 'nullable|string|max:255']);
        $code = strtoupper(trim($r->input('code')));

        $result = DB::transaction(function () use ($r, $code) {
            $p = Peminjaman::where('nomor_surat', $code)->lockForUpdate()->first();
            if (! $p) {
                return ['ok' => false, 'error' => 'Peminjaman tidak ditemukan'];
            }
            $pending = $p->items()->where('dikembalikan', false)->lockForUpdate()->get();
            if ($pending->isEmpty()) {
                return ['ok' => false, 'error' => 'Semua barang sudah dikembalikan'];
            }
            foreach ($pending as $it) {
                $it->update([
                    'dikembalikan' => true,
                    'tgl_dikembalikan' => now(),
                    'catatan_kembali' => $r->input('catatan'),
                ]);
                if ($it->inventaris_id) {
                    Inventaris::whereKey($it->inventaris_id)->update([
                        'jumlah_dipinjam' => DB::raw('GREATEST(jumlah_dipinjam - ' . (int) $it->jumlah . ', 0)'),
                    ]);
                }
            }
            return ['ok' => true];
        });

        return response()->json($result);
    }
}
