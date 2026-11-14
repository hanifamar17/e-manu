<?php

namespace App\Console\Commands;

use App\Services\CsvImporter;
use Illuminate\Console\Command;

class ImporCsv extends Command
{
    protected $signature = 'emanu:impor {type : kalender|kelas|jadwal|guru_piket} {file : path CSV}
                            {--append : tambah, jangan hapus data lama}
                            {--format=Y-m-d : format tanggal kalender (Y-m-d|d/m/Y|m/d/Y)}';

    protected $description = 'Impor CSV (ekspor sheet lama) ke database';

    public function handle(CsvImporter $importer): int
    {
        $res = $importer->import($this->argument('type'), $this->argument('file'), ! $this->option('append'), $this->option('format'));

        if ($res['errors']) {
            foreach ($res['errors'] as $e) {
                $this->error($e);
            }
            $this->error('Dibatalkan, tidak ada data ditulis.');

            return self::FAILURE;
        }
        $this->info("OK: {$res['inserted']} baris diimpor.");

        return self::SUCCESS;
    }
}
