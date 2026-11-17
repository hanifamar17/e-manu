<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeminjamanItem extends Model
{
    protected $table = 'peminjaman_item';

    protected $fillable = [
        'peminjaman_id', 'barang_pinjaman_id', 'no', 'nama_barang', 'merk', 'jumlah',
        'kondisi', 'dikembalikan', 'tgl_dikembalikan', 'catatan_kembali',
    ];

    protected $casts = [
        'dikembalikan' => 'boolean',
        'tgl_dikembalikan' => 'datetime',
    ];

    public function peminjaman(): BelongsTo
    {
        return $this->belongsTo(Peminjaman::class, 'peminjaman_id');
    }
}
