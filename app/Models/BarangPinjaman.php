<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BarangPinjaman extends Model
{
    // WAJIB eksplisit: pluralizer Laravel mengubah "pinjaman" -> "pinjamen"
    protected $table = 'barang_pinjaman';

    protected $fillable = [
        'kode', 'nama_barang', 'merk', 'kondisi', 'lokasi', 'tahun_pengadaan',
        'sumber_dana', 'sub_bidang', 'jumlah_total', 'jumlah_dipinjam',
    ];

    public function scopeCari(Builder $q, string $s): Builder
    {
        $s = trim($s);
        if ($s === '') {
            return $q;
        }
        $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $s) . '%';

        return $q->where(fn ($w) => $w->where('nama_barang', 'like', $like)
            ->orWhere('merk', 'like', $like)
            ->orWhere('lokasi', 'like', $like)
            ->orWhere('kode', 'like', $like));
    }

    public function getJumlahTersediaAttribute(): int
    {
        return max($this->jumlah_total - $this->jumlah_dipinjam, 0);
    }

    public function getStatusAttribute(): string
    {
        return $this->jumlah_tersedia <= 0 ? 'Habis' : 'Tersedia';
    }

    public static function nextKode(): string
    {
        $last = static::query()->orderByDesc('kode')->value('kode');
        $n = $last ? (int) preg_replace('/\D/', '', $last) : 0;

        return sprintf('PJM-%04d', $n + 1);
    }
}
