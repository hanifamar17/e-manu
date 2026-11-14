<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Inventaris extends Model
{
    // Nama tabel WAJIB eksplisit: pluralisasi otomatis Laravel salah untuk "inventaris".
    protected $table = 'inventaris';

    public const KONDISI = ['Baik', 'Rusak Ringan', 'Rusak Berat'];
    public const SUMBER_DANA = ['BOS', 'BOSDA', 'Hibah', 'Lainnya'];
    public const SUB_BIDANG = ['Perpustakaan', 'Lab IPA', 'Lab Komputer', 'Asrama', 'Waka & Guru', 'Madrasah'];

    // slug URL => nilai tersimpan di kolom sub_bidang
    public const BIDANG = [
        'perpus' => 'Perpustakaan',
        'lab_komp' => 'Lab Komputer',
        'lab_ipa' => 'Lab IPA',
        'asrama' => 'Asrama',
        'waka_guru' => 'Waka & Guru',
        'madrasah' => 'Madrasah',
    ];

    // slug URL => label tab
    public const BIDANG_LABEL = [
        'perpus' => 'Perpus',
        'lab_komp' => 'Lab Komp',
        'lab_ipa' => 'Lab IPA',
        'asrama' => 'Asrama',
        'waka_guru' => 'Waka & Guru',
        'madrasah' => 'Madrasah',
    ];

    protected $fillable = [
        'kode', 'nama_barang', 'merk', 'kondisi', 'lokasi', 'tahun_pengadaan',
        'sumber_dana', 'sub_bidang', 'jumlah_total', 'jumlah_dipinjam',
    ];

    protected $casts = [
        'tahun_pengadaan' => 'integer',
        'jumlah_total' => 'integer',
        'jumlah_dipinjam' => 'integer',
    ];

    public function getJumlahTersediaAttribute(): int
    {
        return max($this->jumlah_total - $this->jumlah_dipinjam, 0);
    }

    public function getStatusAttribute(): string
    {
        return $this->jumlah_tersedia > 0 ? 'Tersedia' : 'Habis';
    }

    public function scopeCari(Builder $q, string $s): Builder
    {
        if ($s === '') {
            return $q;
        }
        $like = '%'.addcslashes($s, '\\%_').'%';

        return $q->where(fn ($w) => $w
            ->where('nama_barang', 'like', $like)
            ->orWhere('merk', 'like', $like)
            ->orWhere('lokasi', 'like', $like)
            ->orWhere('kode', 'like', $like));
    }

    public static function nextKode(): string
    {
        $max = static::query()->pluck('kode')
            ->map(fn ($k) => (int) preg_replace('/\D/', '', $k))
            ->max() ?? 0;

        return sprintf('INV-%04d', $max + 1);
    }
}