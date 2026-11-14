<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportUsers extends Command
{
    protected $signature = 'emanu:import-users {csv : Ekspor sheet "akun" (CSV) dengan header username,nama,role}';

    protected $description = 'Impor akun dari CSV. Hash lama (werkzeug) diabaikan; password sementara dibuat acak.';

    public function handle(): int
    {
        $path = $this->argument('csv');
        if (! is_readable($path)) {
            $this->error("File tidak terbaca: {$path}");

            return self::FAILURE;
        }

        $fh = fopen($path, 'r');
        $header = fgetcsv($fh);
        if (! $header) {
            $this->error('CSV kosong.');

            return self::FAILURE;
        }
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]); // buang BOM
        $header = array_map(fn ($h) => strtolower(trim($h)), $header);

        $out = [];
        while (($row = fgetcsv($fh)) !== false) {
            if (count($row) < count($header)) {
                $row = array_pad($row, count($header), '');
            }
            $r = array_combine($header, array_slice($row, 0, count($header)));
            $username = trim($r['username'] ?? '');
            if ($username === '') {
                continue;
            }
            $temp = Str::password(12, symbols: false);
            User::updateOrCreate(
                ['username' => $username],
                ['nama' => trim($r['nama'] ?? '') ?: $username, 'role' => trim($r['role'] ?? ''), 'password' => $temp],
            );
            $out[] = [$username, $r['role'] ?? '', $temp];
        }
        fclose($fh);

        $this->table(['username', 'role', 'password sementara'], $out);
        $this->warn('Catat password di atas lalu bagikan ke pemilik akun. Tidak disimpan di tempat lain.');

        return self::SUCCESS;
    }
}
