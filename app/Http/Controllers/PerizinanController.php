<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Perizinan;
use App\Models\PerizinanSiswa;
use App\Models\Siswa;
use App\Services\KelasMatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Akun dipakai bersama (1 akun guru, 1 akun wali_kelas), jadi identitas orang
 * TIDAK diambil dari login: petugas = ketik manual, pemroses ACC = pilih dari kelas.wali_kelas.
 *
 * 1 izin (perizinan) = header; siswa di tabel perizinan_siswa (1..banyak, dalam SATU kelas).
 * ACC / tolak / batal berlaku untuk seluruh izin.
 */
class PerizinanController extends Controller
{
    public function index(): View
    {
        return view('perizinan.index', [
            // jumlah siswa yang sedang izin hari ini
            'hariIni' => PerizinanSiswa::whereHas('perizinan', fn ($q) => $q->berlakuHariIni())->count(),
            'menunggu' => Perizinan::where('status', Perizinan::MENUNGGU)->count(),
        ]);
    }

    public function form(Request $request): View
    {
        $nisn = array_filter((array) old('nisn', []));
        $kelasMap = Kelas::all()->keyBy(fn ($k) => KelasMatcher::key($k->nama_kelas));

        $terpilih = $nisn
            ? Siswa::whereIn('nisn', $nisn)->get()->map(fn ($s) => $this->bentukSiswa($s, $kelasMap))->values()
            : collect();

        return view('perizinan.form', [
            'durasi' => Perizinan::DURASI,
            'statusPetugas' => Perizinan::STATUS_PETUGAS,
            'defaultStatusPetugas' => $request->user()->role === 'wali_kelas' ? 'wali_kelas' : 'guru',
            'kelasList' => Siswa::query()->distinct()->orderBy('kelas_umum')->pluck('kelas_umum'),
            'terpilih' => $terpilih,
        ]);
    }

    private function bentukSiswa(Siswa $s, $kelasMap): array
    {
        return [
            'nisn' => $s->nisn,
            'nama' => $s->nama_siswa,
            'kelas' => $s->kelas_umum,
            'wali' => $kelasMap[KelasMatcher::key($s->kelas_umum)]->wali_kelas ?? null,
        ];
    }

    /** Autocomplete siswa: nama / awalan nisn. */
    public function siswa(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q'));
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }
        $like = addcslashes($q, '%_\\');
        $kelasMap = Kelas::all()->keyBy(fn ($k) => KelasMatcher::key($k->nama_kelas));

        $rows = Siswa::where('nama_siswa', 'like', "%{$like}%")->orWhere('nisn', 'like', "{$like}%")
            ->orderBy('nama_siswa')->limit(10)->get()
            ->map(fn ($s) => $this->bentukSiswa($s, $kelasMap));

        return response()->json($rows);
    }

    /** Semua siswa satu kelas (tab "Per kelas"). */
    public function siswaKelas(Request $request): JsonResponse
    {
        $kelas = trim((string) $request->query('kelas'));
        if ($kelas === '') {
            return response()->json([]);
        }
        $kelasMap = Kelas::all()->keyBy(fn ($k) => KelasMatcher::key($k->nama_kelas));

        return response()->json(
            Siswa::where('kelas_umum', $kelas)->orderBy('nama_siswa')->get()
                ->map(fn ($s) => $this->bentukSiswa($s, $kelasMap))->values()
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $d = $request->validate([
            'nisn' => ['required', 'array', 'min:1', 'max:300'],
            'nisn.*' => ['required', 'string', 'max:20', 'distinct'],
            'tanggal_mulai' => ['required', 'date_format:Y-m-d'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'tanggal_selesai' => ['required', 'date_format:Y-m-d', 'after_or_equal:tanggal_mulai'],
            'jam_selesai' => ['required', 'date_format:H:i'],
            'durasi' => ['required', Rule::in(array_keys(Perizinan::DURASI))],
            'alasan' => ['required', 'string', 'max:200'],
            'petugas' => ['required', 'string', 'max:100'],
            'status_petugas' => ['required', Rule::in(Perizinan::STATUS_PETUGAS)],
            'need_acc' => ['nullable', 'boolean'],
        ], [
            'nisn.required' => 'Pilih minimal 1 siswa.',
            'nisn.min' => 'Pilih minimal 1 siswa.',
            'nisn.*.distinct' => 'Ada siswa yang terpilih dua kali.',
        ]);

        $siswa = Siswa::whereIn('nisn', $d['nisn'])->get();
        if ($siswa->count() !== count($d['nisn'])) {
            return back()->withInput()->withErrors(['nisn' => 'Ada siswa yang tidak ditemukan. Pilih ulang dari pencarian / daftar kelas.']);
        }

        $mulai = "{$d['tanggal_mulai']} {$d['jam_mulai']}:00";
        $selesai = "{$d['tanggal_selesai']} {$d['jam_selesai']}:00";
        if ($selesai <= $mulai) {
            return back()->withInput()->withErrors(['jam_selesai' => 'Waktu selesai harus setelah waktu mulai.']);
        }

        $kelasSet = $siswa->pluck('kelas_umum')->unique();
        if ($kelasSet->count() > 1) {
            return back()->withInput()->withErrors(['nisn' => 'Satu izin hanya untuk satu kelas ('.$kelasSet->implode(', ').'). Buat izin terpisah per kelas.']);
        }
        $kelas = $kelasSet->first();

        $bentrok = Perizinan::bentrok($d['nisn'], $mulai, $selesai);
        if ($bentrok->isNotEmpty()) {
            $nama = $bentrok->values()->take(5)->implode(', ').($bentrok->count() > 5 ? ' dan '.($bentrok->count() - 5).' lainnya' : '');

            return back()->withInput()->withErrors(['nisn' => "Sudah punya izin (menunggu / aktif / disetujui) yang bentrok dengan rentang waktu ini: {$nama}. Hapus dari daftar atau batalkan izin lamanya."]);
        }

        $need = $request->boolean('need_acc');
        $wali = KelasMatcher::cari($kelas)?->wali_kelas;

        if ($need && ! $wali) {
            return back()->withInput()->withErrors(['nisn' => "Wali kelas {$kelas} belum terdaftar di data Kelas, jadi ACC belum bisa dikirim. Isi dulu di menu Kelas & Wali Kelas (admin), atau simpan tanpa ACC."]);
        }

        $p = DB::transaction(function () use ($d, $siswa, $need, $wali) {
            $p = Perizinan::create([
                'wali_kelas_tujuan' => $wali,
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
            $p->siswa()->createMany($siswa->map(fn ($s) => [
                'nisn' => $s->nisn,
                'nama_siswa' => $s->nama_siswa,
                'kelas_umum' => $s->kelas_umum,
            ])->all());

            return $p;
        });
        Cache::forget('layar.izin');

        $untuk = $p->ringkas();
        $msg = $need
            ? "Izin {$p->id_perizinan} untuk {$untuk} tersimpan, tapi BELUM aktif. Kirim tautan ACC ke wali kelas lewat tombol WhatsApp di bawah."
            : "Izin {$p->id_perizinan} untuk {$untuk} langsung aktif (tanpa ACC wali kelas).";

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
        $q = Perizinan::query()->with('siswa');
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
            $q->where(fn ($w) => $w->where('id_perizinan', 'like', $like)
                ->orWhereHas('siswa', fn ($s) => $s->where('nama_siswa', 'like', $like)->orWhere('nisn', 'like', $like)));
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
        $perizinan->load('siswa');
        $extra = [];
        if ($perizinan->status === Perizinan::MENUNGGU) {
            $hp = $this->hpWali($perizinan);
            $acc = route('perizinan.acc_link', $perizinan->uuid);
            $n = $perizinan->siswa->count();
            $pesan = "Assalamualaikum {$perizinan->wali_kelas_tujuan}. Mohon ACC izin siswa:\n"
                .($n > 1 ? "{$n} siswa ({$perizinan->daftarKelas()}), a.n. {$perizinan->ringkas()}\n" : "{$perizinan->ringkas()} ({$perizinan->daftarKelas()})\n")
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
        return $this->proses($perizinan, Perizinan::DISETUJUI, $this->dataProses($request, $perizinan, false), 'disetujui');
    }

    public function tolak(Request $request, Perizinan $perizinan): RedirectResponse
    {
        return $this->proses($perizinan, Perizinan::DITOLAK, $this->dataProses($request, $perizinan, true), 'ditolak');
    }

    /**
     * Pemroses TERKUNCI ke wali kelas dari kelas siswa (wali_kelas_tujuan); input form diabaikan.
     * Hanya izin lama tanpa wali_kelas_tujuan yang masih memakai pilihan manual.
     */
    private function dataProses(Request $request, Perizinan $p, bool $wajibCatatan): array
    {
        $d = $request->validate([
            'diproses_oleh' => [$p->wali_kelas_tujuan ? 'nullable' : 'required', 'string', Rule::in($this->namaWali()->all())],
            'catatan_wali_kelas' => [$wajibCatatan ? 'required' : 'nullable', 'string', 'max:200'],
        ], ['diproses_oleh.in' => 'Pilih nama wali kelas dari daftar.', 'catatan_wali_kelas.required' => 'Alasan penolakan wajib diisi.']);

        $d['diproses_oleh'] = $p->wali_kelas_tujuan ?: $d['diproses_oleh'];

        return $d;
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
            ->with('status', "Izin {$perizinan->id_perizinan} ({$perizinan->ringkas()}) berhasil {$kata} oleh {$d['diproses_oleh']}.");
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
            ->with('status', "Izin {$perizinan->id_perizinan} ({$perizinan->ringkas()}) dibatalkan oleh {$d['dibatalkan_oleh']}.");
    }

    /** Surat digital: publik via uuid, tidak diindeks mesin pencari. */
    public function surat(string $uuid): Response
    {
        $p = Perizinan::with('siswa')->where('uuid', $uuid)->firstOrFail();

        return response()->view('perizinan.surat', ['item' => $p, 'noAuth' => true])
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    /**
     * ACC lewat tautan (tanpa login): dikirim guru ke WhatsApp wali kelas.
     * Dijaga uuid + 4 digit terakhir no HP wali kelas (kelas.no_hp).
     */
    public function accLink(string $uuid): Response
    {
        $p = Perizinan::with('siswa')->where('uuid', $uuid)->firstOrFail();

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
        $p = Perizinan::with('siswa')->where('uuid', $uuid)->firstOrFail();
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
            $ok ? "Izin {$p->ringkas()} berhasil {$kata}. Terima kasih." : 'Izin ini sudah diproses atau dibatalkan lebih dulu.'
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

    /** Satu baris per siswa (bentuk JSON sama seperti sebelumnya, layar.blade tidak perlu diubah). */
    public function layarData(string $token): JsonResponse
    {
        $this->cekToken($token);

        $data = Cache::remember('layar.izin', 20, function () {
            $now = now();
            $rank = ['berlangsung' => 0, 'akan' => 1, 'selesai' => 2];
            $rows = Perizinan::berlakuHariIni()->with('siswa')->orderBy('tanggal_mulai')->orderBy('jam_mulai')->get()
                ->flatMap(function (Perizinan $p) use ($now) {
                    $mulai = $p->tanggal_mulai->copy()->setTimeFromTimeString($p->jam_mulai);
                    $selesai = $p->tanggal_selesai->copy()->setTimeFromTimeString($p->jam_selesai);
                    $multi = ! $p->tanggal_mulai->isSameDay($p->tanggal_selesai);
                    $waktu = $multi
                        ? $mulai->translatedFormat('j M H:i').' → '.$selesai->translatedFormat('j M H:i')
                        : $mulai->format('H:i').' – '.$selesai->format('H:i');
                    $status = $now->lt($mulai) ? 'akan' : ($now->gt($selesai) ? 'selesai' : 'berlangsung');

                    return $p->siswaUrut()->map(fn ($s) => [
                        'id' => $p->id_perizinan.'-'.$s->nisn,
                        'nama' => $s->nama_siswa,
                        'kelas' => $s->kelas_umum,
                        'durasi' => $p->durasi,
                        'alasan' => $p->alasan,
                        'waktu' => $waktu,
                        'status' => $status,
                        'ts' => $mulai->timestamp,
                    ]);
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

    /** Nama wali kelas unik dari tabel kelas (pilihan "nama pemroses" / "wali tujuan"). */
    private function namaWali()
    {
        return Kelas::whereNotNull('wali_kelas')->where('wali_kelas', '!=', '')
            ->orderBy('wali_kelas')->pluck('wali_kelas')->unique()->values();
    }
}
