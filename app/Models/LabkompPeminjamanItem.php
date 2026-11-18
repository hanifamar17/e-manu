<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabkompPeminjamanItem extends Model
{
    protected $table = 'labkomp_peminjaman_item';

    public $timestamps = false;

    protected $fillable = ['peminjaman_id', 'item_id', 'kode_barang', 'nama_barang', 'qty'];

    public function peminjaman(): BelongsTo
    {
        return $this->belongsTo(LabkompPeminjaman::class, 'peminjaman_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(LabkompItem::class, 'item_id');
    }
}
