<?php

namespace App\Services;

use App\Models\GuruPiket;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\KalenderAkademik;
use Illuminate\Support\Facades\DB;
use App\Models\Inventaris;
use App\Models\BarangPinjaman;
use App\Models\LabkompItem;
use App\Models\Siswa;
use App\Models\LabkompPeminjaman;
use App\Models\LabkompPeminjamanItem;
use Illuminate\Support\Str;

/**
 * Impor CSV / XLSX (jenis file dideteksi dari isi, bukan ekstensi).
 * Validasi SEMUA baris dulu; ada satu error -> tidak ada yang ditulis.
 * COLUMNS = satu-satunya sumber format: dipakai importer DAN template Excel.
 */
class CsvImporter
{
    public const TYPES = [
        'kalender' => 'Kalender akademik',
        'kelas' => 'Kelas + wali kelas (+ no HP)',
        'siswa' => 'Data siswa',
        'jadwal' => 'Jadwal pelajaran',
        'guru_piket' => 'Guru piket',
        'inventaris' => 'Database sarpras (inventaris)',
        'barang_pinjaman' => 'Barang pinjaman (Peminjaman Aset)',
        'labkomp_items' => 'Inventaris Lab Komp',
        'labkomp_peminjaman' => 'Riwayat peminjaman Lab Komp',
    ];

    public const COLUMNS = [
        'kalender' => ['tanggal_mulai', 'tanggal_selesai', 'keterangan', 'kategori'],
        'kelas' => ['nama_kelas', 'wali_kelas', 'no_hp'],
        'siswa' => ['nisn', 'nama_siswa', 'jenis_kelamin', 'kelas_umum', 'kelas_diniyyah', 'tahun_masuk'],
        'jadwal' => ['id', 'hari', 'nama_kelas', 'sesi', 'waktu', 'nama_mapel', 'nama_guru'],
        'guru_piket' => ['hari', 'nama'],
        'inventaris' => ['id', 'nama_barang', 'merk', 'kondisi', 'lokasi', 'tahun_pengadaan', 'sumber_dana', 'sub_bidang', 'jumlah_total', 'jumlah_dipinjam'],
        'barang_pinjaman' => ['id', 'nama_barang', 'merk', 'kondisi', 'lokasi', 'tahun_pengadaan', 'sumber_dana', 'sub_bidang', 'jumlah_total'],
        'labkomp_items' => ['id', 'nama_barang', 'stok', 'satuan', 'kondisi', 'lokasi', 'catatan'],
        // 1 baris = 1 barang dalam 1 peminjaman; baris dengan id sama digabung jadi 1 peminjaman
        'labkomp_peminjaman' => ['id', 'nama_peminjam', 'email_peminjam', 'tgl_pinjam', 'tgl_kembali', 'status', 'catatan', 'kode_barang', 'nama_barang', 'qty'],
    ];

    public const REQUIRED = [
        'kalender' => ['tanggal_mulai', 'keterangan', 'kategori'],
        'kelas' => ['nama_kelas'],
        'siswa' => ['nisn', 'nama_siswa', 'jenis_kelamin', 'kelas_umum'],
        'jadwal' => ['hari', 'nama_kelas', 'waktu', 'nama_mapel'],
        'guru_piket' => ['hari', 'nama'],
        'inventaris' => ['nama_barang', 'sub_bidang', 'jumlah_total'],
        'barang_pinjaman' => ['nama_barang', 'sub_bidang', 'jumlah_total'],
        'labkomp_items' => ['nama_barang', 'stok'],
        'labkomp_peminjaman' => ['id', 'nama_peminjam', 'tgl_pinjam', 'status', 'nama_barang', 'qty'],
    ];

    /** Template = header saja (tanpa baris contoh). Contoh format ada di sheet "petunjuk" (NOTES). */
    public const SAMPLES = [
        'kalender' => [],
        'kelas' => [],
        'siswa' => [],
        'jadwal' => [],
        'guru_piket' => [],
        'inventaris' => [],
        'barang_pinjaman' => [],
        'labkomp_items' => [],
        'labkomp_peminjaman' => [],
    ];

    public const NOTES = [
        'kalender' => ['kategori: libur / agenda / akademik', 'tanggal_selesai kosong = 1 hari', 'tanggal: tulis DD-MM-YYYY, mis. 17-08-2026'],
        'kelas' => [
            'nama_kelas harus unik',
            'no_hp wali kelas: boleh kosong; tulis 08xx / +628xx / 628xx, disimpan sebagai 628xx',
        ],
        'siswa' => [
            'Impor KELAS dulu: kelas_umum harus cocok dengan nama_kelas (10 = X, 11 = XI, 12 = XII; spasi & huruf besar/kecil diabaikan, mis. "11 SOS1" = "XI SOS 1")',
            'nisn: angka, unik. jenis_kelamin: L / P',
            'kelas_diniyyah: teks/angka bebas (boleh kosong). tahun_masuk: 2000-2100 (boleh kosong)',
            'mode Ganti = hapus semua siswa lalu isi ulang; mode Tambah = update per nisn',
        ],
        'jadwal' => ['hari: SENIN / SELASA / RABU / KAMIS / JUMAT / SABTU', 'id = urutan sesi (angka)', 'waktu: teks, mis. 07.00-07.45'],
        'guru_piket' => ['hari: SENIN / SELASA / RABU / KAMIS / JUMAT / SABTU'],
        'inventaris' => [
            'id = kode barang (INV-0001). Barang BARU: kosongkan id, sistem isi otomatis (kode terakhir + 1)',
            'Barang lama (hasil unduhan Data saat ini): jangan ubah id, dipakai riwayat peminjaman',
            'kondisi: Baik / Rusak Ringan / Rusak Berat (kosong = Baik)',
            'sumber_dana: BOS / BOSDA / Hibah / Lainnya (boleh kosong)',
            'sub_bidang: Perpustakaan / Lab IPA / Lab Komputer / Asrama / Waka & Guru / Madrasah',
            'jumlah_dipinjam kosong = 0, tidak boleh lebih dari jumlah_total',
        ],
        'barang_pinjaman' => [
            'id = kode barang pinjaman (PJM-0001), maks 20 karakter. Barang BARU: kosongkan id, sistem isi otomatis (kode terakhir + 1)',
            'Barang lama (hasil unduhan Data saat ini): jangan ubah id, dipakai riwayat peminjaman',
            'kondisi: Baik / Rusak Ringan / Rusak Berat (kosong = Baik)',
            'sumber_dana: BOS / BOSDA / Hibah / Lainnya (boleh kosong)',
            'sub_bidang: Perpustakaan / Lab IPA / Lab Komputer / Asrama / Waka & Guru / Madrasah',
            'stok dipinjam diisi otomatis oleh sistem, tidak ada di file',
            'mode Ganti ditolak kalau ada barang yang masih dipinjam: pakai mode Tambah',
        ],
        'labkomp_items' => [
            'id = kode barang (INV-0001). Barang BARU: kosongkan id, sistem isi otomatis (kode terakhir + 1)',
            'stok = stok TERSEDIA saat ini (sudah dikurangi yang sedang dipinjam)',
            'satuan: Unit / Buah / Set / Pcs / Roll / Box (kosong = Unit)',
            'kondisi: Baik / Rusak Ringan / Rusak Berat (kosong = Baik)',
            'mode Ganti ditolak kalau sudah ada riwayat peminjaman: pakai mode Tambah',
        ],
        'labkomp_peminjaman' => [
            'Impor labkomp_items DULU: barang di sini harus sudah ada',
            '1 baris = 1 barang. Baris dengan id (kode peminjaman) sama digabung jadi 1 peminjaman',
            'id wajib (kode lama dari Sheets boleh, mis. LK-241118-AB12). nama_peminjam, tgl_pinjam, status sama di semua baris satu id',
            'status: dipinjam / dikembalikan. tgl_kembali kosong = belum kembali',
            'kode_barang = kode di Inventaris Lab Komp (INV-xxxx); kosong = dicocokkan lewat nama_barang',
            'qty bulat >= 1. Impor ini TIDAK mengubah stok barang',
            'tanggal: DD-MM-YYYY. mode Ganti ditolak kalau ada yang masih dipinjam',
        ],
    ];

    public const DATE_FORMATS = ['d-m-Y' => 'DD-MM-YYYY', 'Y-m-d' => 'YYYY-MM-DD', 'd/m/Y' => 'DD/MM/YYYY', 'm/d/Y' => 'MM/DD/YYYY'];

    public const DEFAULT_DATE_FORMAT = 'd-m-Y';

    private const MAX_ROWS = 5000;

    /** @return array{inserted:int, errors:string[]} */
    public function import(string $type, string $path, bool $replace = true, string $dateFormat = self::DEFAULT_DATE_FORMAT): array
    {
        if (! isset(self::TYPES[$type])) {
            return ['inserted' => 0, 'errors' => ["Tipe tidak dikenal: {$type}"]];
        }
        if (! isset(self::DATE_FORMATS[$dateFormat])) {
            return ['inserted' => 0, 'errors' => ["Format tanggal tidak dikenal: {$dateFormat}"]];
        }

        try {
            $rows = $this->isXlsx($path) ? $this->readXlsx($path) : $this->readCsv($path);
        } catch (\Throwable $e) {
            return ['inserted' => 0, 'errors' => ['File tidak bisa dibaca: '.$e->getMessage()]];
        }
        if ($rows === null) {
            return ['inserted' => 0, 'errors' => ['File kosong / tidak bisa dibaca.']];
        }
        if (! $rows) {
            return ['inserted' => 0, 'errors' => ['Tidak ada baris data.']];
        }

        $have = array_keys(reset($rows));
        $missing = array_diff(self::REQUIRED[$type], $have);
        if ($missing) {
            return ['inserted' => 0, 'errors' => [
                'Header kurang: '.implode(', ', $missing).'. Header terbaca: '.implode(', ', $have).'. Wajib: '.implode(', ', self::COLUMNS[$type]).'.',
            ]];
        }

        [$valid, $errors] = match ($type) {
            'kalender' => $this->parseKalender($rows, $dateFormat),
            'kelas' => $this->parseKelas($rows),
            'siswa' => $this->parseSiswa($rows),
            'jadwal' => $this->parseJadwal($rows),
            'guru_piket' => $this->parsePiket($rows),
            'inventaris' => $this->parseInventaris($rows),
            'barang_pinjaman' => $this->parseInventaris($rows),
            'labkomp_items' => $this->parseLabkompItems($rows),
            'labkomp_peminjaman' => $this->parseLabkompPeminjaman($rows, $dateFormat),
        };

        if ($errors || ! $valid) {
            return ['inserted' => 0, 'errors' => $errors ?: ['Tidak ada baris data.']];
        }

        if ($type === 'barang_pinjaman' && ($guard = $this->guardBarangPinjaman($valid, $replace))) {
            return ['inserted' => 0, 'errors' => $guard];
        }
        if (str_starts_with($type, 'labkomp_') && ($guard = $this->guardLabkomp($type, $replace))) {
            return ['inserted' => 0, 'errors' => $guard];
        }

        $generated = [];
        DB::transaction(function () use ($type, &$valid, $replace, &$generated) {
            $model = match ($type) {
                'kalender' => KalenderAkademik::class,
                'kelas' => Kelas::class,
                'siswa' => Siswa::class,
                'jadwal' => JadwalPelajaran::class,
                'guru_piket' => GuruPiket::class,
                'inventaris' => Inventaris::class,
                'barang_pinjaman' => BarangPinjaman::class,
                'labkomp_items' => LabkompItem::class,
                'labkomp_peminjaman' => LabkompPeminjaman::class,
            };
            if ($replace) {
                if ($type === 'labkomp_peminjaman') {
                    LabkompPeminjamanItem::query()->delete();
                }
                $model::query()->delete();
            }
            $valid = $this->assignKodes($type, $valid, $generated);
            if ($type === 'labkomp_peminjaman') {
                $this->writePeminjaman($valid, $replace);

                return;
            }
            $now = now();
            foreach (array_chunk($valid, 200) as $chunk) {
                if ($type === 'kelas' && ! $replace) {
                    foreach ($chunk as $r) {
                        Kelas::updateOrCreate(['nama_kelas' => $r['nama_kelas']], ['wali_kelas' => $r['wali_kelas'], 'no_hp' => $r['no_hp']]);
                    }
                    continue;
                }
                if ($type === 'siswa' && ! $replace) {
                    foreach ($chunk as $r) {
                        Siswa::updateOrCreate(['nisn' => $r['nisn']], array_diff_key($r, ['nisn' => 1]));
                    }
                    continue;
                }
                if ($type === 'inventaris' && ! $replace) {
                    foreach ($chunk as $r) {
                        Inventaris::updateOrCreate(['kode' => $r['kode']], array_diff_key($r, ['kode' => 1]));
                    }
                    continue;
                }
                if ($type === 'labkomp_items' && ! $replace) {
                    foreach ($chunk as $r) {
                        LabkompItem::updateOrCreate(['kode' => $r['kode']], array_diff_key($r, ['kode' => 1]));
                    }
                    continue;
                }
                if ($type === 'barang_pinjaman' && ! $replace) {
                    foreach ($chunk as $r) {
                        // jumlah_dipinjam tidak disentuh: hitungan pinjam hidup di sistem
                        BarangPinjaman::updateOrCreate(['kode' => $r['kode']], array_diff_key($r, ['kode' => 1, 'jumlah_dipinjam' => 1]));
                    }
                    continue;
                }
                $model::insert(array_map(fn ($r) => $r + ['created_at' => $now, 'updated_at' => $now], $chunk));
            }
        });

        return ['inserted' => count($valid), 'errors' => [], 'generated' => $generated];
    }

    private function isXlsx(string $path): bool
    {
        $fh = @fopen($path, 'r');
        if (! $fh) {
            return false;
        }
        $sig = fread($fh, 2);
        fclose($fh);

        return $sig === 'PK';
    }

    private function normHeader(mixed $h): string
    {
        return strtolower(preg_replace('/\s+/', '_', trim((string) $h)));
    }

    /** @return array<int, array<string,string>>|null  key = nomor baris file (header = 1) */
    private function readXlsx(string $path): ?array
    {
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
        $reader->setReadEmptyCells(false);
        $sheet = $reader->load($path)->getSheet(0);

        $maxRow = min($sheet->getHighestDataRow(), self::MAX_ROWS + 1);
        $maxCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sheet->getHighestDataColumn());

        $header = [];
        for ($c = 1; $c <= $maxCol; $c++) {
            $header[$c] = $this->normHeader($this->cellText($sheet, $c, 1));
        }
        if (! array_filter($header)) {
            return null;
        }

        $out = [];
        for ($r = 2; $r <= $maxRow; $r++) {
            $row = [];
            $any = false;
            foreach ($header as $c => $name) {
                $v = trim($this->cellText($sheet, $c, $r));
                $any = $any || $v !== '';
                if ($name !== '') {
                    $row[$name] = $v;
                }
            }
            if ($any) {
                $out[$r] = $row;
            }
        }

        return $out;
    }

    private function cellText($sheet, int $col, int $row): string
    {
        $cell = $sheet->getCell([$col, $row]);
        $v = $cell->isFormula() ? $cell->getCalculatedValue() : $cell->getValue();
        if ($v instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
            $v = $v->getPlainText();
        }
        if ($v === null || $v === '') {
            return '';
        }
        if (is_numeric($v) && \PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($cell)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($v)->format('Y-m-d');
        }
        if (is_float($v) && floor($v) === $v) {
            return (string) (int) $v; // 1.0 -> "1"
        }

        return (string) $v;
    }

    /** @return array<int, array<string,string>>|null */
    private function readCsv(string $path): ?array
    {
        $raw = @file_get_contents($path);
        if ($raw === false || $raw === '') {
            return null;
        }
        if (str_starts_with($raw, "\xFF\xFE") || str_starts_with($raw, "\xFE\xFF")) {
            $raw = mb_convert_encoding($raw, 'UTF-8', 'UTF-16'); // PowerShell / Notepad "Unicode"
        }
        $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);

        // Tiap baris dibungkus kutip penuh ("a,b") akibat simpan lewat Excel 1 kolom.
        $lines = preg_split('/\r\n|\n|\r/', $raw);
        $non = array_filter($lines, fn ($l) => trim($l) !== '');
        if ($non && count(array_filter($non, fn ($l) => preg_match('/^".*"$/', trim($l)))) === count($non)) {
            $raw = implode("\n", array_map(fn ($l) => str_replace('""', '"', substr(trim($l), 1, -1)), $lines));
        }

        $fh = fopen('php://temp', 'r+');
        fwrite($fh, $raw);
        rewind($fh);

        $first = (string) fgets($fh);
        $counts = [',' => substr_count($first, ','), ';' => substr_count($first, ';'), "\t" => substr_count($first, "\t")];
        arsort($counts);
        $delim = array_key_first($counts);
        rewind($fh);

        $header = null;
        $out = [];
        $line = 0;
        while (($cols = fgetcsv($fh, 0, $delim, '"', '')) !== false) {
            $line++;
            if ($line === 1) {
                $header = array_map(fn ($h) => $this->normHeader($h), $cols);
                continue;
            }
            if (! array_filter($cols, fn ($c) => trim((string) $c) !== '')) {
                continue;
            }
            if (count($out) >= self::MAX_ROWS) {
                break;
            }
            $cols = array_pad($cols, count($header), '');
            $out[$line] = array_map('trim', array_combine($header, array_slice($cols, 0, count($header))));
        }
        fclose($fh);

        return $header ? $out : null;
    }

    private function parseDate(?string $v, string $format): ?string
    {
        $v = trim((string) $v);
        if ($v === '') {
            return null;
        }
        foreach (array_unique([$format, 'Y-m-d']) as $f) {
            foreach ([$f, str_replace(['d', 'm'], ['j', 'n'], $f)] as $try) {
                $d = \DateTime::createFromFormat('!'.$try, $v);
                if ($d && $d->format($try) === $v) {
                    return $d->format('Y-m-d');
                }
            }
        }

        return null;
    }

    private function parseKalender(array $rows, string $fmt): array
    {
        $ok = $err = [];
        foreach ($rows as $n => $r) {
            $mulai = $this->parseDate($r['tanggal_mulai'] ?? '', $fmt);
            $selesai = ($r['tanggal_selesai'] ?? '') === '' ? $mulai : $this->parseDate($r['tanggal_selesai'], $fmt);
            $kat = strtolower($r['kategori'] ?? '');
            $ket = $r['keterangan'] ?? '';
            if (! $mulai || ! $selesai) {
                $err[] = "Baris {$n}: tanggal tidak valid (tulis ".self::DATE_FORMATS[$fmt].").";
            } elseif ($selesai < $mulai) {
                $err[] = "Baris {$n}: tanggal_selesai < tanggal_mulai.";
            } elseif ($ket === '') {
                $err[] = "Baris {$n}: keterangan kosong.";
            } elseif (! in_array($kat, KalenderAkademik::KATEGORI, true)) {
                $err[] = "Baris {$n}: kategori '{$kat}' harus salah satu: ".implode('/', KalenderAkademik::KATEGORI).'.';
            } else {
                $ok[] = ['tanggal_mulai' => $mulai, 'tanggal_selesai' => $selesai, 'keterangan' => $ket, 'kategori' => $kat];
            }
        }

        return [$ok, $err];
    }

    private function parseKelas(array $rows): array
    {
        $ok = $err = [];
        $seen = [];
        foreach ($rows as $n => $r) {
            $nama = $r['nama_kelas'] ?? '';
            if ($nama === '') {
                $err[] = "Baris {$n}: nama_kelas kosong.";
            } elseif (isset($seen[$nama])) {
                $err[] = "Baris {$n}: nama_kelas '{$nama}' duplikat.";
            } elseif (($r['no_hp'] ?? '') !== '' && KelasMatcher::hp($r['no_hp']) === null) {
                $err[] = "Baris {$n}: no_hp '{$r['no_hp']}' tidak valid (10-15 digit, mis. 081234567890).";
            } else {
                $seen[$nama] = true;
                $ok[] = ['nama_kelas' => $nama, 'wali_kelas' => ($r['wali_kelas'] ?? '') ?: null, 'no_hp' => KelasMatcher::hp($r['no_hp'] ?? '')];
            }
        }

        return [$ok, $err];
    }

    private function parseSiswa(array $rows): array
    {
        $kelasKey = [];
        foreach (Kelas::pluck('nama_kelas') as $k) {
            $kelasKey[KelasMatcher::key($k)] = $k;
        }

        $ok = $err = $seen = [];
        foreach ($rows as $n => $r) {
            $nisn = $r['nisn'] ?? '';
            $nama = $r['nama_siswa'] ?? '';
            $jk = strtoupper($r['jenis_kelamin'] ?? '');
            $ku = $r['kelas_umum'] ?? '';
            $kd = $r['kelas_diniyyah'] ?? '';
            $th = $r['tahun_masuk'] ?? '';

            if (! ctype_digit($nisn) || strlen($nisn) > 15) {
                $err[] = "Baris {$n}: nisn harus angka (maks 15 digit).";
            } elseif (isset($seen[$nisn])) {
                $err[] = "Baris {$n}: nisn '{$nisn}' duplikat.";
            } elseif ($nama === '') {
                $err[] = "Baris {$n}: nama_siswa kosong.";
            } elseif (! in_array($jk, ['L', 'P'], true)) {
                $err[] = "Baris {$n}: jenis_kelamin '".($r['jenis_kelamin'] ?? '')."' harus L / P.";
            } elseif ($ku === '' || ! isset($kelasKey[KelasMatcher::key($ku)])) {
                $err[] = "Baris {$n}: kelas_umum '{$ku}' tidak cocok dengan data kelas. Impor kelas dulu / samakan nama.";
            } elseif ($th !== '' && (! ctype_digit($th) || $th < 2000 || $th > 2100)) {
                $err[] = "Baris {$n}: tahun_masuk '{$th}' tidak valid (2000-2100).";
            } else {
                $seen[$nisn] = true;
                $ok[] = [
                    'nisn' => $nisn,
                    'nama_siswa' => $nama,
                    'jenis_kelamin' => $jk,
                    'kelas_umum' => $ku,
                    'kelas_diniyyah' => $kd === '' ? null : $kd,
                    'tahun_masuk' => $th === '' ? null : (int) $th,
                ];
            }
        }

        return [$ok, $err];
    }

    private function parseJadwal(array $rows): array
    {
        $ok = $err = [];
        $i = 0;
        foreach ($rows as $n => $r) {
            $i++;
            $hari = strtoupper($r['hari'] ?? '');
            foreach (['nama_kelas', 'waktu', 'nama_mapel'] as $req) {
                if (($r[$req] ?? '') === '') {
                    $err[] = "Baris {$n}: {$req} kosong.";
                    continue 2;
                }
            }
            if (! in_array($hari, JadwalPelajaran::HARI, true)) {
                $err[] = "Baris {$n}: hari '{$hari}' harus salah satu: ".implode('/', JadwalPelajaran::HARI).'.';
                continue;
            }
            $id = $r['id'] ?? '';
            $ok[] = [
                'urutan' => ctype_digit($id) ? (int) $id : $i,
                'hari' => $hari,
                'nama_kelas' => $r['nama_kelas'],
                'sesi' => ($r['sesi'] ?? '') ?: null,
                'waktu' => $r['waktu'],
                'nama_mapel' => $r['nama_mapel'],
                'nama_guru' => ($r['nama_guru'] ?? '') ?: null,
            ];
        }

        return [$ok, $err];
    }

        private function pick(string $v, array $allowed): ?string
    {
        foreach ($allowed as $a) {
            if (strcasecmp($a, trim($v)) === 0) {
                return $a;
            }
        }

        return null;
    }

    private function parseInventaris(array $rows): array
    {
        $ok = $err = $seen = [];
        foreach ($rows as $n => $r) {
            $kode = strtoupper($r['id'] ?? '');
            $nama = $r['nama_barang'] ?? '';
            $rawKondisi = $r['kondisi'] ?? '';
            $rawDana = $r['sumber_dana'] ?? '';
            $kondisi = $rawKondisi === '' ? 'Baik' : $this->pick($rawKondisi, Inventaris::KONDISI);
            $dana = $rawDana === '' ? null : $this->pick($rawDana, Inventaris::SUMBER_DANA);
            $bidang = $this->pick($r['sub_bidang'] ?? '', Inventaris::SUB_BIDANG);
            $tahun = $r['tahun_pengadaan'] ?? '';
            $total = $r['jumlah_total'] ?? '';
            $pinjam = ($r['jumlah_dipinjam'] ?? '') === '' ? '0' : $r['jumlah_dipinjam'];

            if ($nama === '') {
                $err[] = "Baris {$n}: nama_barang kosong.";
            } elseif ($kode !== '' && isset($seen[$kode])) {
                $err[] = "Baris {$n}: id '{$kode}' duplikat.";
            } elseif (! $kondisi) {
                $err[] = "Baris {$n}: kondisi '{$rawKondisi}' harus: ".implode('/', Inventaris::KONDISI).'.';
            } elseif ($rawDana !== '' && ! $dana) {
                $err[] = "Baris {$n}: sumber_dana '{$rawDana}' harus: ".implode('/', Inventaris::SUMBER_DANA).'.';
            } elseif (! $bidang) {
                $err[] = "Baris {$n}: sub_bidang '".($r['sub_bidang'] ?? '')."' harus: ".implode(' / ', Inventaris::SUB_BIDANG).'.';
            } elseif ($tahun !== '' && (! ctype_digit($tahun) || $tahun < 1990 || $tahun > 2100)) {
                $err[] = "Baris {$n}: tahun_pengadaan '{$tahun}' tidak valid (1990-2100).";
            } elseif (! ctype_digit($total) || ! ctype_digit($pinjam)) {
                $err[] = "Baris {$n}: jumlah_total / jumlah_dipinjam harus angka bulat >= 0.";
            } elseif ((int) $pinjam > (int) $total) {
                $err[] = "Baris {$n}: jumlah_dipinjam > jumlah_total.";
            } else {
                if ($kode !== '') {
                    $seen[$kode] = true;
                }
                $ok[] = [
                    'kode' => $kode === '' ? null : $kode, // null = dibuat otomatis (assignKodes)
                    'nama_barang' => $nama,
                    'merk' => ($r['merk'] ?? '') ?: null,
                    'kondisi' => $kondisi,
                    'lokasi' => ($r['lokasi'] ?? '') ?: null,
                    'tahun_pengadaan' => $tahun === '' ? null : (int) $tahun,
                    'sumber_dana' => $dana,
                    'sub_bidang' => $bidang,
                    'jumlah_total' => (int) $total,
                    'jumlah_dipinjam' => (int) $pinjam,
                ];
            }
        }

        return [$ok, $err];
    }
    
    /**
     * Isi kode otomatis (INV-/PJM- + angka maks + 1) untuk baris tanpa id.
     * Dipanggil di dalam transaksi; lockForUpdate mencegah dua impor bersamaan memakai nomor sama.
     *
     * @param  string[]  $generated  diisi kode yang dibuat
     */
    private function assignKodes(string $type, array $valid, array &$generated): array
    {
        if (! in_array($type, ['inventaris', 'barang_pinjaman', 'labkomp_items'], true)) {
            return $valid;
        }
        if (! array_filter($valid, fn ($r) => $r['kode'] === null)) {
            return $valid;
        }

        $prefix = $type === 'barang_pinjaman' ? 'PJM-' : 'INV-';
        $model = match ($type) {
            'inventaris' => Inventaris::class,
            'barang_pinjaman' => BarangPinjaman::class,
            'labkomp_items' => LabkompItem::class,
        };
        $codes = array_merge(
            $model::query()->lockForUpdate()->pluck('kode')->all(),
            array_filter(array_column($valid, 'kode'))
        );

        $max = 0;
        foreach ($codes as $k) {
            if (preg_match('/^'.preg_quote($prefix, '/').'(\d+)$/', (string) $k, $m)) {
                $max = max($max, (int) $m[1]);
            }
        }

        foreach ($valid as &$r) {
            if ($r['kode'] === null) {
                $r['kode'] = $prefix.str_pad((string) ++$max, 4, '0', STR_PAD_LEFT);
                $generated[] = $r['kode'];
            }
        }
        unset($r);

        return $valid;
    }

    private function guardBarangPinjaman(array $valid, bool $replace): array
    {
        if ($replace) {
            $n = BarangPinjaman::where('jumlah_dipinjam', '>', 0)->count();

            return $n ? ["Mode Ganti ditolak: {$n} barang pinjaman masih dipinjam. Pakai mode Tambah."] : [];
        }

        $dipinjam = BarangPinjaman::pluck('jumlah_dipinjam', 'kode');
        $err = [];
        foreach ($valid as $r) {
            if ($r['kode'] === null) {
                continue; // kode baru otomatis: belum ada di DB
            }
            if (strlen($r['kode']) > 20) {
                $err[] = "id '{$r['kode']}' lebih dari 20 karakter.";
            }
            $d = $dipinjam[$r['kode']] ?? 0;
            if ($r['jumlah_total'] < $d) {
                $err[] = "{$r['kode']}: jumlah_total {$r['jumlah_total']} < sedang dipinjam {$d}.";
            }
        }

        return $err;
    }
    
    private function parseLabkompItems(array $rows): array
    {
        $ok = $err = $seen = [];
        foreach ($rows as $n => $r) {
            $kode = strtoupper($r['id'] ?? '');
            $nama = $r['nama_barang'] ?? '';
            $stok = $r['stok'] ?? '';
            $rawKondisi = $r['kondisi'] ?? '';
            $rawSatuan = $r['satuan'] ?? '';
            $kondisi = $rawKondisi === '' ? 'Baik' : $this->pick($rawKondisi, LabkompItem::KONDISI);
            $satuan = $rawSatuan === '' ? 'Unit' : $this->pick($rawSatuan, LabkompItem::SATUAN);

            if ($nama === '') {
                $err[] = "Baris {$n}: nama_barang kosong.";
            } elseif ($kode !== '' && isset($seen[$kode])) {
                $err[] = "Baris {$n}: id '{$kode}' duplikat.";
            } elseif (! ctype_digit($stok)) {
                $err[] = "Baris {$n}: stok harus angka bulat >= 0.";
            } elseif (! $satuan) {
                $err[] = "Baris {$n}: satuan '{$rawSatuan}' harus: ".implode('/', LabkompItem::SATUAN).'.';
            } elseif (! $kondisi) {
                $err[] = "Baris {$n}: kondisi '{$rawKondisi}' harus: ".implode('/', LabkompItem::KONDISI).'.';
            } else {
                if ($kode !== '') {
                    $seen[$kode] = true;
                }
                $ok[] = [
                    'kode' => $kode === '' ? null : $kode, // null = otomatis (assignKodes)
                    'nama_barang' => $nama,
                    'stok' => (int) $stok,
                    'satuan' => $satuan,
                    'kondisi' => $kondisi,
                    'lokasi' => ($r['lokasi'] ?? '') ?: null,
                    'catatan' => ($r['catatan'] ?? '') ?: null,
                ];
            }
        }

        return [$ok, $err];
    }

    /** Baris dengan id sama digabung jadi 1 peminjaman + banyak item. */
    private function parseLabkompPeminjaman(array $rows, string $fmt): array
    {
        $all = LabkompItem::all(['id', 'kode', 'nama_barang']);
        $byKode = $all->keyBy(fn ($i) => strtoupper($i->kode));
        $byNama = $all->keyBy(fn ($i) => mb_strtolower($i->nama_barang));

        $groups = $err = [];
        foreach ($rows as $n => $r) {
            $kode = strtoupper($r['id'] ?? '');
            $peminjam = $r['nama_peminjam'] ?? '';
            $tgl = $this->parseDate($r['tgl_pinjam'] ?? '', $fmt);
            $rawKembali = $r['tgl_kembali'] ?? '';
            $kembali = $rawKembali === '' ? null : $this->parseDate($rawKembali, $fmt);
            $status = $this->pick($r['status'] ?? '', [LabkompPeminjaman::DIPINJAM, LabkompPeminjaman::DIKEMBALIKAN]);
            $nama = $r['nama_barang'] ?? '';
            $kb = strtoupper($r['kode_barang'] ?? '');
            $qty = $r['qty'] ?? '';
            $item = ($kb !== '' ? ($byKode[$kb] ?? null) : null) ?? ($byNama[mb_strtolower($nama)] ?? null);

            if ($kode === '') {
                $err[] = "Baris {$n}: id (kode peminjaman) kosong.";
            } elseif ($peminjam === '') {
                $err[] = "Baris {$n}: nama_peminjam kosong.";
            } elseif (! $tgl) {
                $err[] = "Baris {$n}: tgl_pinjam tidak valid (tulis ".self::DATE_FORMATS[$fmt].').';
            } elseif ($rawKembali !== '' && ! $kembali) {
                $err[] = "Baris {$n}: tgl_kembali tidak valid (tulis ".self::DATE_FORMATS[$fmt].').';
            } elseif ($kembali && $kembali < $tgl) {
                $err[] = "Baris {$n}: tgl_kembali < tgl_pinjam.";
            } elseif (! $status) {
                $err[] = "Baris {$n}: status '".($r['status'] ?? '')."' harus: dipinjam / dikembalikan.";
            } elseif (! $item) {
                $err[] = "Baris {$n}: barang '{$nama}' ({$kb}) tidak ada di Inventaris Lab Komp. Impor inventaris dulu.";
            } elseif (! ctype_digit($qty) || (int) $qty < 1) {
                $err[] = "Baris {$n}: qty harus angka bulat >= 1.";
            } else {
                $head = [
                    'kode' => $kode,
                    'nama_peminjam' => $peminjam,
                    'email_peminjam' => ($r['email_peminjam'] ?? '') ?: null,
                    'tgl_pinjam' => $tgl,
                    'tgl_kembali' => $kembali,
                    'status' => $status,
                    'catatan' => ($r['catatan'] ?? '') ?: null,
                ];
                $line = ['item_id' => $item->id, 'kode_barang' => $item->kode, 'nama_barang' => $nama, 'qty' => (int) $qty];

                if (! isset($groups[$kode])) {
                    $groups[$kode] = $head + ['items' => [$line]];
                    continue;
                }
                $g = $groups[$kode];
                if ($g['nama_peminjam'] !== $head['nama_peminjam'] || $g['tgl_pinjam'] !== $head['tgl_pinjam'] || $g['status'] !== $head['status']) {
                    $err[] = "Baris {$n}: id '{$kode}' sama tapi nama_peminjam / tgl_pinjam / status beda dari baris sebelumnya.";
                } elseif (in_array($item->id, array_column($g['items'], 'item_id'), true)) {
                    $err[] = "Baris {$n}: barang '{$item->kode}' dobel di peminjaman '{$kode}'.";
                } else {
                    $groups[$kode]['items'][] = $line;
                }
            }
        }

        return [array_values($groups), $err];
    }

    private function writePeminjaman(array $groups, bool $replace): void
    {
        foreach ($groups as $g) {
            $items = $g['items'];
            unset($g['items']);
            $g['dikembalikan_at'] = ($g['status'] === LabkompPeminjaman::DIKEMBALIKAN && $g['tgl_kembali'])
                ? $g['tgl_kembali'].' 00:00:00' : null;

            $p = $replace ? null : LabkompPeminjaman::where('kode', $g['kode'])->first();
            if ($p) {
                $p->update($g); // uuid lama dipertahankan (link bukti tetap hidup)
                $p->items()->delete();
            } else {
                $p = LabkompPeminjaman::create($g + ['uuid' => (string) Str::uuid()]);
            }
            $p->items()->createMany($items);
        }
    }

    private function guardLabkomp(string $type, bool $replace): array
    {
        if (! $replace) {
            return [];
        }
        if ($type === 'labkomp_items') {
            $n = LabkompPeminjamanItem::count();

            return $n ? ["Mode Ganti ditolak: {$n} baris riwayat peminjaman memakai barang ini. Pakai mode Tambah."] : [];
        }
        $n = LabkompPeminjaman::where('status', LabkompPeminjaman::DIPINJAM)->count();

        return $n ? ["Mode Ganti ditolak: {$n} peminjaman Lab Komp masih berstatus dipinjam. Kembalikan dulu atau pakai mode Tambah."] : [];
    }

    /**
     * Data asli untuk Ekspor, urutan kolom = COLUMNS[$type], tanggal DD-MM-YYYY.
     * Dipakai ExcelTemplate::build($type, true) untuk tipe labkomp_*.
     *
     * @return array<int, array<int, mixed>>
     */
    public function exportRows(string $type): array
    {
        $f = fn ($d) => $d ? $d->format('d-m-Y') : '';

        return match ($type) {
            'labkomp_items' => LabkompItem::orderBy('kode')->get()
                ->map(fn ($i) => [$i->kode, $i->nama_barang, $i->stok, $i->satuan, $i->kondisi, $i->lokasi, $i->catatan])
                ->all(),
            'labkomp_peminjaman' => LabkompPeminjaman::with('items')->orderBy('tgl_pinjam')->orderBy('id')->get()
                ->flatMap(fn ($p) => $p->items->map(fn ($it) => [
                    $p->kode, $p->nama_peminjam, $p->email_peminjam, $f($p->tgl_pinjam), $f($p->tgl_kembali),
                    $p->status, $p->catatan, $it->kode_barang, $it->nama_barang, $it->qty,
                ]))->all(),
            default => [],
        };
    }

    private function parsePiket(array $rows): array
    {
        $ok = $err = [];
        foreach ($rows as $n => $r) {
            $hari = strtoupper($r['hari'] ?? '');
            if (! in_array($hari, JadwalPelajaran::HARI, true) || ($r['nama'] ?? '') === '') {
                $err[] = "Baris {$n}: hari/nama tidak valid.";
                continue;
            }
            $ok[] = ['hari' => $hari, 'nama' => $r['nama']];
        }

        return [$ok, $err];
    }
}