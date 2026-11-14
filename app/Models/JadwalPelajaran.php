<?php

namespace App\Models;

use App\Support\MixedScript;
use Illuminate\Database\Eloquent\Model;

class JadwalPelajaran extends Model
{
    public const HARI = ['SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU'];

    protected $table = 'jadwal_pelajaran';

    protected $fillable = ['urutan', 'hari', 'nama_kelas', 'sesi', 'waktu', 'nama_mapel', 'nama_guru'];

    // Pengganti split_mixed_script() Flask: [[segmen, is_arabic], ...]
    public function getMapelSegmentsAttribute(): array
    {
        return MixedScript::split($this->nama_mapel);
    }

    // "07.00-07.45" / "7:00" -> menit sejak 00:00; tak terbaca -> 9999
    public function getMenitMulaiAttribute(): int
    {
        if (preg_match('/(\d{1,2})[.:](\d{2})/', trim((string) $this->waktu), $m)) {
            return (int) $m[1] * 60 + (int) $m[2];
        }

        return 9999;
    }
}
