<?php

namespace App\Services;

use App\Models\Kelas;

/** Cocokkan teks kelas siswa ("11 SOS1", "10") ke nama_kelas ("XI SOS 1", "X"); normalisasi no HP. */
class KelasMatcher
{
    private const ROMAN = ['10' => 'X', '11' => 'XI', '12' => 'XII'];

    public static function key(string $v): string
    {
        $v = strtoupper(preg_replace('/\s+/', '', $v));
        if (preg_match('/^(10|11|12)(.*)$/', $v, $m)) {
            $v = self::ROMAN[$m[1]].$m[2];
        }

        return $v;
    }

    public static function cari(?string $kelasUmum): ?Kelas
    {
        if (! $kelasUmum) {
            return null;
        }
        $k = self::key($kelasUmum);

        return Kelas::all()->first(fn ($x) => self::key($x->nama_kelas) === $k);
    }

    /** 08xx / +628xx / 628xx -> 628xx. Kosong -> null. Tidak valid -> null (cek input kosong di pemanggil). */
    public static function hp(string $v): ?string
    {
        $d = preg_replace('/\D+/', '', $v);
        if ($d === '') {
            return null;
        }
        if (str_starts_with($d, '0')) {
            $d = '62'.substr($d, 1);
        } elseif (! str_starts_with($d, '62')) {
            $d = '62'.$d;
        }

        return strlen($d) >= 10 && strlen($d) <= 15 ? $d : null;
    }
}
