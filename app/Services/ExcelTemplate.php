<?php

namespace App\Services;

use App\Models\GuruPiket;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\KalenderAkademik;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\Inventaris;
use App\Models\BarangPinjaman;

/** Template .xlsx dibangun dari CsvImporter::COLUMNS => selalu sama dengan format importer. */
class ExcelTemplate
{
    /** @return string path file sementara (hapus setelah kirim) */
    public function build(string $type, bool $withData = false): string
    {
        $cols = CsvImporter::COLUMNS[$type];
        $ss = new Spreadsheet();
        $s = $ss->getActiveSheet()->setTitle('data');

        foreach ($cols as $i => $c) {
            $s->setCellValueExplicit([$i + 1, 1], $c, DataType::TYPE_STRING);
            $s->getColumnDimensionByColumn($i + 1)->setWidth(max(16, strlen($c) + 4));
        }
        $last = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($cols));
        $s->getStyle("A1:{$last}1")->getFont()->setBold(true);
        $s->getStyle("A1:{$last}1")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F3E6DC');
        // Semua sel data = teks, supaya Excel tidak mengubah tanggal / "07.00" / angka.
        $s->getStyle("A2:{$last}2000")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
        $s->freezePane('A2');

        $rows = $withData ? $this->rows($type) : CsvImporter::SAMPLES[$type];
        foreach ($rows as $r => $row) {
            foreach (array_values($row) as $c => $v) {
                $s->setCellValueExplicit([$c + 1, $r + 2], (string) $v, DataType::TYPE_STRING);
            }
        }

        $p = $ss->createSheet()->setTitle('petunjuk');
        $lines = array_merge(
            ['Jangan ubah / hapus baris header di sheet "data".', 'Impor membaca sheet pertama ("data").', 'Satu baris salah = seluruh impor dibatalkan.'],
            CsvImporter::NOTES[$type]
        );
        foreach ($lines as $i => $t) {
            $p->setCellValueExplicit([1, $i + 1], $t, DataType::TYPE_STRING);
        }
        $p->getColumnDimension('A')->setWidth(90);
        $ss->setActiveSheetIndex(0);

        $tmp = tempnam(sys_get_temp_dir(), 'emanu');
        (new Xlsx($ss))->save($tmp);

        return $tmp;
    }

    private function rows(string $type): array
    {
        return match ($type) {
            'kalender' => KalenderAkademik::orderBy('tanggal_mulai')->get()->map(fn ($k) => [
                $k->tanggal_mulai->format('Y-m-d'),
                $k->tanggal_selesai->eq($k->tanggal_mulai) ? '' : $k->tanggal_selesai->format('Y-m-d'),
                $k->keterangan, $k->kategori,
            ])->all(),
            'kelas' => Kelas::orderBy('nama_kelas')->get()->map(fn ($k) => [$k->nama_kelas, $k->wali_kelas])->all(),
            'jadwal' => JadwalPelajaran::orderBy('urutan')->orderBy('id')->get()->map(fn ($j) => [
                $j->urutan, $j->hari, $j->nama_kelas, $j->sesi, $j->waktu, $j->nama_mapel, $j->nama_guru,
            ])->all(),
            'guru_piket' => GuruPiket::orderBy('id')->get()->map(fn ($g) => [$g->hari, $g->nama])->all(),
            'inventaris' => Inventaris::orderBy('kode')->get()->map(fn ($i) => [
                $i->kode, $i->nama_barang, $i->merk, $i->kondisi, $i->lokasi, $i->tahun_pengadaan,
                $i->sumber_dana, $i->sub_bidang, $i->jumlah_total, $i->jumlah_dipinjam,
            ])->all(),
            'barang_pinjaman' => BarangPinjaman::orderBy('kode')->get()->map(fn ($i) => [
                $i->kode, $i->nama_barang, $i->merk, $i->kondisi, $i->lokasi, $i->tahun_pengadaan,
                $i->sumber_dana, $i->sub_bidang, $i->jumlah_total,
            ])->all(),
        };
    }
}
