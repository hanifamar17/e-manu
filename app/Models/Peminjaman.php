<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Peminjaman extends Model
{
    // WAJIB eksplisit: pluralizer Laravel mengubah "peminjaman" -> "peminjamen"
    protected $table = 'peminjaman';

    protected $fillable = [
        'record_id', 'nomor_surat', 'nama', 'jenis_kelamin', 'pekerjaan', 'alamat',
        'telepon', 'no_identitas', 'tujuan', 'lokasi_penggunaan', 'tgl_pinjam',
        'tgl_kembali', 'kota', 'tgl_surat', 'nama_petugas', 'jaminan_identitas',
        'jenis_identitas_jaminan', 'jaminan_surat', 'pdf_path',
    ];

    protected $casts = [
        'tgl_pinjam' => 'date',
        'tgl_kembali' => 'date',
        'tgl_surat' => 'date',
        'jaminan_identitas' => 'boolean',
        'jaminan_surat' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'record_id';
    }

    public function items(): HasMany
    {
        return $this->hasMany(PeminjamanItem::class, 'peminjaman_id')->orderBy('no');
    }

    public function getSudahKembaliAttribute(): bool
    {
        return $this->items->isNotEmpty() && $this->items->every(fn ($i) => $i->dikembalikan);
    }
}
