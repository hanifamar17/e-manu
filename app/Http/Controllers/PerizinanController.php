<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Perizinan;
use App\Models\Siswa;
use App\Services\KelasMatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;

/**
 * Akun dipakai bersama (1 akun guru, 1 akun wali_kelas), jadi identitas orang
 * TIDAK diambil dari login: petugas = ketik manual, pemroses ACC = pilih dari kelas.wali_kelas.
 */
class PerizinanController extends Controller
{
    public function index(): View
    {
        return view('perizinan.index', [
            'hariIni' => Perizinan::berlakuHariIni()->orderBy('jam_mulai')->get(),
            'menunggu' => Perizinan::where('status', Perizinan::MENUNGGU)->count(),
        ]);
    }

    public function form(Request $request): View
    {
        $nisn = old('nisn');

        return view('perizinan.form', [
            'durasi' => Perizinan::DURASI,
            'statusPetugas' => Perizinan::STATUS_PETUGAS,
            'defaultStatusPetugas' => $request->user()->role === 'wali_kelas' ? 'wali_kelas' : 'guru',
            'pilih' => $nisn ? Siswa::where('nisn', $nisn)->first() : null,
        ]);
    }

    /** Autocomplete siswa: nama / awalan nisn. */
    public function siswa(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q'));
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }
        $like = addcslashes($q, '%_\\');
        $kelas = Kelas::all()->keyBy(fn ($k) => KelasMatcher::key($k->nama_kelas));

        $rows = Siswa::where('nama_siswa', 'like', "%{$like}%")->orWhere('nisn', 'like', "{$like}%")
            ->orderBy('nama_siswa')->limit(10)->get()
            ->map(fn ($s) => [
                'nisn' => $s->nisn,
                'nama' => $s->nama_siswa,
                'kelas' => $s->kelas_umum,
                'wali' => $kelas[KelasMatcher::key($s->kelas_umum)]->wali_kelas ?? null,
            ]);

        return response()->json($rows);
    }

    public function store(Request $request): RedirectResponse
    {
        $d = $request->validate([
            'nisn' => ['required', 'string', 'max:20'],
            'tanggal_mulai' => ['required', 'date_format:Y-m-d'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'tanggal_selesai' => ['required', 'date_format:Y-m-d', 'after_or_equal:tanggal_mulai'],
            'jam_selesai' => ['required', 'date_format:H:i'],
            'durasi' => ['required', Rule::in(array_keys(Perizinan::DURASI))],
            'alasan' => ['required', 'string', 'max:200'],
            'petugas' => ['required', 'string', 'max:100'],
            'status_petugas' => ['required', Rule::in(Perizinan::STATUS_PETUGAS)],
            'need_acc' => ['nullable', 'boolean'],
        ]);

        $siswa = Siswa::where('nisn', $d['nisn'])->first();
        if (! $siswa) {
            return back()->withInput()->withErrors(['nisn' => 'Siswa tidak ditemukan. Pilih dari hasil pencarian.']);
        }

        $mulai = "{$d['tanggal_mulai']} {$d['jam_mulai']}:00";
        $selesai = "{$d['tanggal_selesai']} {$d['jam_selesai']}:00";
        if ($selesai <= $mulai) {
            return back()->withInput()->withErrors(['jam_selesai' => 'Waktu selesai harus setelah waktu mulai.']);
        }
        if (Perizinan::adaTumpangTindih($siswa->nisn, $mulai, $selesai)) {
            return back()->withInput()->withErrors(['nisn' => "{$siswa->nama_siswa} sudah punya izin (menunggu / aktif / disetujui) yang bentrok dengan rentang waktu ini."]);
        }

        $need = $request->boolean('need_acc');
        $p = Perizinan::create([
            'nisn' => $siswa->nisn,
            'nama_siswa' => $siswa->nama_siswa,
            'kelas_umum' => $siswa->kelas_umum,
            'wali_kelas_tujuan' => KelasMatcher::cari($siswa->kelas_umum)?->wali_kelas,
            'tanggal_mulai' => $d['tanggal_mulai'],
            'jam_mulai' => $d['jam_mulai'],
            'tanggal_selesai' => $d['tanggal_selesai'],
            'jam_selesai' => $d['jam_selesai'],
            'durasi' => Perizinan::DURASI[$d['durasi']],
            'alasan' => $d['alasan'],
            'petugas' => $d['petugas'],
            'status_petugas' => $d['status_petugas'],
            'need_acc' => $need,
            'status' => $need ? Perizinan::MENUNGGU : Perizinan::AKTIF,
        ]);

        $msg = $need
            ? "Izin {$p->id_perizinan} untuk {$p->nama_siswa} tersimpan, tapi BELUM aktif. Kirim tautan ACC ke wali kelas lewat tombol WhatsApp di bawah."
            : "Izin {$p->id_perizinan} untuk {$p->nama_siswa} langsung aktif (tanpa ACC wali kelas).";

        return redirect()->route('perizinan.show', $p)->with('status', $msg);
    }

    public function riwayat(Request $request): View
    {
        return $this->daftar($request, 'riwayat');
    }

    public function acc(Request $request): View
    {
        return $this->daftar($request, 'acc');
    }

    private function daftar(Request $request, string $mode): View
    {
        $q = Perizinan::query();
        $cari = trim((string) $request->query('q'));
        $status = (string) $request->query('status');

        if ($mode === 'acc') {
            $q->where('status', Perizinan::MENUNGGU)->orderBy('id');
        } else {
            $q->orderByDesc('id');
            if (in_array($status, [Perizinan::MENUNGGU, Perizinan::AKTIF, Perizinan::DISETUJUI, Perizinan::DITOLAK, Perizinan::DIBATALKAN], true)) {
                $q->where('status', $status);
            }
        }
        if ($cari !== '') {
            $like = '%'.addcslashes($cari, '%_\\').'%';
            $q->where(fn ($w) => $w->where('nama_siswa', 'like', $like)->orWhere('nisn', 'like', $like)->orWhere('id_perizinan', 'like', $like));
        }

        return view('perizinan.list', [
            'items' => $q->paginate(20)->withQueryString(),
            'mode' => $mode,
            'cari' => $cari,
            'status' => $status,
        ]);
    }

    public function show(Perizinan $perizinan): View
    {
        $extra = [];
        if ($perizinan->status === Perizinan::MENUNGGU) {
            $hp = $this->hpWali($perizinan);
            $acc = route('perizinan.acc_link', $perizinan->uuid);
            $pesan = "Assalamualaikum {$perizinan->wali_kelas_tujuan}. Mohon ACC izin siswa:\n"
                ."{$perizinan->nama_siswa} ({$perizinan->kelas_umum})\n"
                ."{$perizinan->durasi}: ".$perizinan->tanggal_mulai->translatedFormat('j M Y').' '.substr($perizinan->jam_mulai, 0, 5)
                .' s.d. '.$perizinan->tanggal_selesai->translatedFormat('j M Y').' '.substr($perizinan->jam_selesai, 0, 5)."\n"
                ."Alasan: {$perizinan->alasan}\n\n"
                ."Buka tautan ini untuk ACC (siapkan 4 digit terakhir no HP Anda):\n{$acc}";
            $extra = [
                'hpWali' => $hp,
                'accUrl' => $acc,
                'waUrl' => 'https://wa.me/'.($hp ?? '').'?text='.rawurlencode($pesan),
            ];
        }

        return view('perizinan.show', ['item' => $perizinan, 'walis' => $this->namaWali()] + $extra);
    }

    public function setujui(Request $request, Perizinan $perizinan): RedirectResponse
    {
        $d = $request->validate([
            'diproses_oleh' => ['required', 'string', Rule::in($this->namaWali()->all())],
            'catatan_wali_kelas' => ['nullable', 'string', 'max:200'],
        ], ['diproses_oleh.in' => 'Pilih nama wali kelas dari daftar.']);

        return $this->proses($perizinan, Perizinan::DISETUJUI, $d, 'disetujui');
    }

    public function tolak(Request $request, Perizinan $perizinan): RedirectResponse
    {
        $d = $request->validate([
            'diproses_oleh' => ['required', 'string', Rule::in($this->namaWali()->all())],
            'catatan_wali_kelas' => ['required', 'string', 'max:200'],
        ], ['diproses_oleh.in' => 'Pilih nama wali kelas dari daftar.', 'catatan_wali_kelas.required' => 'Alasan penolakan wajib diisi.']);

        return $this->proses($perizinan, Perizinan::DITOLAK, $d, 'ditolak');
    }

    /** Ubah status menunggu_acc -> disetujui/ditolak dengan lock. false = sudah diproses duluan. */
    private function prosesDb(Perizinan $perizinan, string $status, array $d): bool
    {
        return DB::transaction(function () use ($perizinan, $status, $d) {
            $p = Perizinan::whereKey($perizinan->getKey())->lockForUpdate()->first();
            if ($p->status !== Perizinan::MENUNGGU) {
                return false; // sudah diproses / dibatalkan orang lain
            }
            $p->update([
                'status' => $status,
                'diproses_oleh' => $d['diproses_oleh'],
                'tanggal_acc' => now(),
                'catatan_wali_kelas' => ($d['catatan_wali_kelas'] ?? '') ?: null,
            ]);

            return true;
        });
    }

    private function proses(Perizinan $perizinan, string $status, array $d, string $kata): RedirectResponse
    {
        if (! $this->prosesDb($perizinan, $status, $d)) {
            return redirect()->route('perizinan.show', $perizinan)
                ->with('warning', 'Izin ini sudah diproses atau dibatalkan lebih dulu, jadi tidak bisa diubah lagi.');
        }

        return redirect()->route('perizinan.acc')
            ->with('status', "Izin {$perizinan->id_perizinan} ({$perizinan->nama_siswa}) berhasil {$kata} oleh {$d['diproses_oleh']}.");
    }

    public function batal(Request $request, Perizinan $perizinan): RedirectResponse
    {
        $d = $request->validate([
            'alasan_batal' => ['required', 'string', 'max:200'],
            'dibatalkan_oleh' => ['required', 'string', 'max:100'],
        ]);
        $role = $request->user()->role;

        $ok = DB::transaction(function () use ($perizinan, $d, $role) {
            $p = Perizinan::whereKey($perizinan->getKey())->lockForUpdate()->first();
            if (! $p->bisaDibatalkanOleh($role)) {
                return false;
            }
            $p->update([
                'status' => Perizinan::DIBATALKAN,
                'dibatalkan_oleh' => $d['dibatalkan_oleh'],
                'alasan_batal' => $d['alasan_batal'],
                'dibatalkan_at' => now(),
            ]);

            return true;
        });

        if (! $ok) {
            return redirect()->route('perizinan.show', $perizinan)
                ->with('warning', 'Izin ini tidak bisa dibatalkan: sudah diproses, atau izin aktif / disetujui hanya bisa dibatalkan admin.');
        }

        return redirect()->route('perizinan.show', $perizinan)
            ->with('status', "Izin {$perizinan->id_perizinan} ({$perizinan->nama_siswa}) dibatalkan oleh {$d['dibatalkan_oleh']}.");
    }

    /** Surat digital: publik via uuid, tidak diindeks mesin pencari. */
    public function surat(string $uuid): Response
    {
        $p = Perizinan::where('uuid', $uuid)->firstOrFail();

        return response()->view('perizinan.surat', ['item' => $p, 'noAuth' => true])
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    /**
     * ACC lewat tautan (tanpa login): dikirim guru ke WhatsApp wali kelas.
     * Dijaga uuid + 4 digit terakhir no HP wali kelas (kelas.no_hp).
     */
    public function accLink(string $uuid): Response
    {
        $p = Perizinan::where('uuid', $uuid)->firstOrFail();

        return response()->view('perizinan.acc_link', [
            'item' => $p,
            'noAuth' => true,
            'bisaLink' => $p->wali_kelas_tujuan && $this->hpWali($p),
        ])->header('X-Robots-Tag', 'noindex, nofollow')->header('Cache-Control', 'no-store');
    }

    public function accLinkSetujui(Request $request, string $uuid): RedirectResponse
    {
        return $this->accLinkProses($request, $uuid, Perizinan::DISETUJUI, 'disetujui');
    }

    public function accLinkTolak(Request $request, string $uuid): RedirectResponse
    {
        return $this->accLinkProses($request, $uuid, Perizinan::DITOLAK, 'ditolak');
    }

    private function accLinkProses(Request $request, string $uuid, string $status, string $kata): RedirectResponse
    {
        $p = Perizinan::where('uuid', $uuid)->firstOrFail();
        $d = $request->validate([
            'pin' => ['required', 'digits:4'],
            'catatan_wali_kelas' => [$status === Perizinan::DITOLAK ? 'required' : 'nullable', 'string', 'max:200'],
        ], [
            'pin.required' => 'Isi 4 digit terakhir no HP Anda.',
            'pin.digits' => 'PIN harus 4 angka.',
            'catatan_wali_kelas.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $hp = $p->wali_kelas_tujuan ? $this->hpWali($p) : null;
        if (! $hp || ! hash_equals(substr($hp, -4), $d['pin'])) {
            return back()->withInput($request->only('catatan_wali_kelas'))
                ->withErrors(['pin' => '4 digit terakhir no HP tidak cocok.']);
        }

        $ok = $this->prosesDb($p, $status, [
            'diproses_oleh' => $p->wali_kelas_tujuan,
            'catatan_wali_kelas' => $d['catatan_wali_kelas'] ?? null,
        ]);

        return redirect()->route('perizinan.acc_link', $uuid)->with(
            $ok ? 'status' : 'warning',
            $ok ? "Izin {$p->nama_siswa} berhasil {$kata}. Terima kasih." : 'Izin ini sudah diproses atau dibatalkan lebih dulu.'
        );
    }

    /** No HP (628xx) wali kelas tujuan dari tabel kelas. */
    private function hpWali(Perizinan $p): ?string
    {
        if (! $p->wali_kelas_tujuan) {
            return null;
        }

        return Kelas::where('wali_kelas', $p->wali_kelas_tujuan)->whereNotNull('no_hp')->value('no_hp');
    }

    /** Layar TV: publik tapi dikunci token rahasia (PERIZINAN_TV_TOKEN -> config emanu.layar_token). */
    public function layar(string $token): Response
    {
        $this->cekToken($token);

        return response()->view('perizinan.layar', ['dataUrl' => route('perizinan.layar.data', $token)])
            ->header('X-Robots-Tag', 'noindex, nofollow')
            ->header('Cache-Control', 'no-store');
    }

    public function layarData(string $token): JsonResponse
{
    $this->cekToken($token);

    $data = Cache::remember('layar.izin', 20, function () {
        $now = now();
        $rank = ['berlangsung' => 0, 'akan' => 1, 'selesai' => 2];
        $rows = Perizinan::berlakuHariIni()->orderBy('tanggal_mulai')->orderBy('jam_mulai')->get()
            ->map(function (Perizinan $p) use ($now) {
                $mulai = $p->tanggal_mulai->copy()->setTimeFromTimeString($p->jam_mulai);
                $selesai = $p->tanggal_selesai->copy()->setTimeFromTimeString($p->jam_selesai);
                $multi = ! $p->tanggal_mulai->isSameDay($p->tanggal_selesai);

                return [
                    'id' => $p->id_perizinan,
                    'nama' => $p->nama_siswa,
                    'kelas' => $p->kelas_umum,
                    'durasi' => $p->durasi,
                    'alasan' => $p->alasan,
                    'waktu' => $multi
                        ? $mulai->translatedFormat('j M H:i').' → '.$selesai->translatedFormat('j M H:i')
                        : $mulai->format('H:i').' – '.$selesai->format('H:i'),
                    'status' => $now->lt($mulai) ? 'akan' : ($now->gt($selesai) ? 'selesai' : 'berlangsung'),
                    'ts' => $mulai->timestamp,
                ];
            })
            ->sortBy(fn ($r) => [$rank[$r['status']], $r['ts']])
            ->values()
            ->all();

        return [
            'tanggal' => $now->translatedFormat('l, j F Y'),
            'diperbarui' => $now->format('H:i:s'),
            'items' => $rows,
        ];
    });

    return response()->json($data)->header('Cache-Control', 'no-store');
}

    private function cekToken(string $token): void
    {
        $t = (string) config('emanu.layar_token');
        abort_if($t === '' || ! hash_equals($t, $token), 404);
    }

    /** Nama wali kelas unik dari tabel kelas (pilihan "nama pemroses"). */
    private function namaWali()
    {
        return Kelas::whereNotNull('wali_kelas')->where('wali_kelas', '!=', '')
            ->orderBy('wali_kelas')->pluck('wali_kelas')->unique()->values();
    }
}
