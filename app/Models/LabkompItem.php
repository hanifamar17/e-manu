<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabkompItem extends Model
{
    protected $table = 'labkomp_items';

    protected $fillable = ['kode', 'nama_barang', 'stok', 'satuan', 'kondisi', 'lokasi', 'catatan'];

    public const KONDISI = ['Baik', 'Rusak Ringan', 'Rusak Berat'];

    public const SATUAN = ['Unit', 'Buah', 'Set', 'Pcs', 'Roll', 'Box'];

    public function peminjamanItems(): HasMany
    {
        return $this->hasMany(LabkompPeminjamanItem::class, 'item_id');
    }

    /** Masih ada peminjaman aktif yang memuat barang ini? */
    public function sedangDipinjam(): bool
    {
        return $this->peminjamanItems()
            ->whereHas('peminjaman', fn ($q) => $q->where('status', LabkompPeminjaman::DIPINJAM))
            ->exists();
    }

    /** INV-0001 dst. (angka terbesar + 1). Panggil di dalam DB::transaction. */
    public static function nextKode(): string
    {
        $max = 0;
        foreach (static::query()->lockForUpdate()->pluck('kode') as $k) {
            if (preg_match('/^INV-(\d+)$/', (string) $k, $m)) {
                $max = max($max, (int) $m[1]);
            }
        }

        return 'INV-'.str_pad((string) ($max + 1), 4, '0', STR_PAD_LEFT);
    }
}
