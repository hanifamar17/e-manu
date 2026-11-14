<?php

namespace App\Services;

use App\Models\GuruPiket;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\KalenderAkademik;
use Illuminate\Support\Facades\DB;
use App\Models\Inventaris;

/**
 * Impor CSV / XLSX (jenis file dideteksi dari isi, bukan ekstensi).
 * Validasi SEMUA baris dulu; ada satu error -> tidak ada yang ditulis.
 * COLUMNS = satu-satunya sumber format: dipakai importer DAN template Excel.
 */
class CsvImporter
{
    public const TYPES = [
        'kalender' => 'Kalender akademik',
        'kelas' => 'Kelas + wali kelas',
        'jadwal' => 'Jadwal pelajaran',
        'guru_piket' => 'Guru piket',
        'inventaris' => 'Database sarpras (inventaris)',
    ];

    public const COLUMNS = [
        'kalender' => ['tanggal_mulai', 'tanggal_selesai', 'keterangan', 'kategori'],
        'kelas' => ['nama_kelas', 'wali_kelas'],
        'jadwal' => ['id', 'hari', 'nama_kelas', 'sesi', 'waktu', 'nama_mapel', 'nama_guru'],
        'guru_piket' => ['hari', 'nama'],
        'inventaris' => ['id', 'nama_barang', 'merk', 'kondisi', 'lokasi', 'tahun_pengadaan', 'sumber_dana', 'sub_bidang', 'jumlah_total', 'jumlah_dipinjam'],
    ];

    public const REQUIRED = [
        'kalender' => ['tanggal_mulai', 'keterangan', 'kategori'],
        'kelas' => ['nama_kelas'],
        'jadwal' => ['hari', 'nama_kelas', 'waktu', 'nama_mapel'],
        'guru_piket' => ['hari', 'nama'],
        'inventaris' => ['id', 'nama_barang', 'sub_bidang', 'jumlah_total'],
    ];

    public const SAMPLES = [
        'kalender' => [
            ['2026-08-17', '', 'HUT RI', 'libur'],
            ['2026-12-10', '2026-12-15', 'Ujian Semester', 'akademik'],
        ],
        'kelas' => [['X-A', 'Pak Budi'], ['X-B', 'Bu Siti']],
        'jadwal' => [
            ['1', 'SENIN', 'X-A', '1', '07.00-07.45', 'Informatika', 'Pak Budi'],
            ['2', 'SENIN', 'X-A', '2', '07.45-08.30', 'Qiroah Kitab: أدب العالم', 'Bu Siti'],
        ],
        'guru_piket' => [['SENIN', 'Pak Budi'], ['SENIN', 'Bu Siti']],
        'inventaris' => [
            ['INV-0001', 'Laptop', 'Asus', 'Baik', 'Lab Komputer 1', '2022', 'BOS', 'Lab Komputer', '10', '0'],
            ['INV-0002', 'Proyektor', 'Epson', 'Rusak Ringan', 'Ruang Guru', '2020', 'Hibah', 'Waka & Guru', '2', '1'],
        ],
    ];

    public const NOTES = [
        'kalender' => ['kategori: libur / agenda / akademik', 'tanggal_selesai kosong = 1 hari', 'tanggal: tulis YYYY-MM-DD (format default impor)'],
        'kelas' => ['nama_kelas harus unik'],
        'jadwal' => ['hari: SENIN / SELASA / RABU / KAMIS / JUMAT / SABTU', 'id = urutan sesi (angka)', 'waktu: teks, mis. 07.00-07.45'],
        'guru_piket' => ['hari: SENIN / SELASA / RABU / KAMIS / JUMAT / SABTU'],
        'inventaris' => [
            'id = kode barang (INV-0001), jangan diubah: dipakai riwayat peminjaman',
            'kondisi: Baik / Rusak Ringan / Rusak Berat (kosong = Baik)',
            'sumber_dana: BOS / BOSDA / Hibah / Lainnya (boleh kosong)',
            'sub_bidang: Perpustakaan / Lab IPA / Lab Komputer / Asrama / Waka & Guru / Madrasah',
            'jumlah_dipinjam kosong = 0, tidak boleh lebih dari jumlah_total',
        ],
    ];

    public const DATE_FORMATS = ['Y-m-d' => 'YYYY-MM-DD', 'd/m/Y' => 'DD/MM/YYYY', 'm/d/Y' => 'MM/DD/YYYY'];

    private const MAX_ROWS = 5000;

    /** @return array{inserted:int, errors:string[]} */
    public function import(string $type, string $path, bool $replace = true, string $dateFormat = 'Y-m-d'): array
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
            'jadwal' => $this->parseJadwal($rows),
            'guru_piket' => $this->parsePiket($rows),
            'inventaris' => $this->parseInventaris($rows),
        };

        if ($errors || ! $valid) {
            return ['inserted' => 0, 'errors' => $errors ?: ['Tidak ada baris data.']];
        }

        DB::transaction(function () use ($type, $valid, $replace) {
            $model = match ($type) {
                'kalender' => KalenderAkademik::class,
                'kelas' => Kelas::class,
                'jadwal' => JadwalPelajaran::class,
                'guru_piket' => GuruPiket::class,
                'inventaris' => Inventaris::class,
            };
            if ($replace) {
                $model::query()->delete();
            }
            $now = now();
            foreach (array_chunk($valid, 200) as $chunk) {
                if ($type === 'kelas' && ! $replace) {
                    foreach ($chunk as $r) {
                        Kelas::updateOrCreate(['nama_kelas' => $r['nama_kelas']], ['wali_kelas' => $r['wali_kelas']]);
                    }
                    continue;
                }
                if ($type === 'inventaris' && ! $replace) {
                    foreach ($chunk as $r) {
                        Inventaris::updateOrCreate(['kode' => $r['kode']], array_diff_key($r, ['kode' => 1]));
                    }
                    continue;
                }
                $model::insert(array_map(fn ($r) => $r + ['created_at' => $now, 'updated_at' => $now], $chunk));
            }
        });

        return ['inserted' => count($valid), 'errors' => []];
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
                $err[] = "Baris {$n}: tanggal tidak valid (format {$fmt}).";
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
            } else {
                $seen[$nama] = true;
                $ok[] = ['nama_kelas' => $nama, 'wali_kelas' => ($r['wali_kelas'] ?? '') ?: null];
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

            if ($kode === '' || $nama === '') {
                $err[] = "Baris {$n}: id / nama_barang kosong.";
            } elseif (isset($seen[$kode])) {
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
                $seen[$kode] = true;
                $ok[] = [
                    'kode' => $kode,
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
