<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class LabkompPeminjaman extends Model
{
    protected $table = 'labkomp_peminjaman';

    protected $fillable = [
        'uuid', 'kode', 'nama_peminjam', 'email_peminjam', 'tgl_pinjam', 'tgl_kembali',
        'status', 'catatan', 'bukti_kembali', 'dikembalikan_at',
    ];

    protected $casts = [
        'tgl_pinjam' => 'date',
        'tgl_kembali' => 'date',
        'dikembalikan_at' => 'datetime',
    ];

    public const DIPINJAM = 'dipinjam';

    public const DIKEMBALIKAN = 'dikembalikan';

    public function items(): HasMany
    {
        return $this->hasMany(LabkompPeminjamanItem::class, 'peminjaman_id');
    }

    /** LK-YYMMDD-XXXX (huruf besar + angka, mudah diketik saat pengembalian). */
    public static function buatKode(): string
    {
        do {
            $kode = 'LK-'.now()->format('ymd').'-'.strtoupper(Str::random(4));
        } while (static::where('kode', $kode)->exists());

        return $kode;
    }
}
