<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BookingRuangan extends Model
{
    public const PENDING = 'pending';
    public const DISETUJUI = 'disetujui';
    public const DITOLAK = 'ditolak';

    protected $table = 'booking_ruangan';

    protected $fillable = [
        'uuid', 'nama_pemesan', 'email', 'eksternal', 'nama_kegiatan', 'tanggal',
        'waktu_mulai', 'waktu_selesai', 'catatan_tambahan', 'status',
        'alasan_penolakan', 'ditinjau_oleh', 'ditinjau_pada',
    ];

    protected $casts = [
        'eksternal' => 'boolean',
        'tanggal' => 'date',
        'ditinjau_pada' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(fn (self $m) => $m->uuid ??= (string) Str::uuid());
    }

    public function scopeDisetujui(Builder $q): Builder
    {
        return $q->where('status', self::DISETUJUI);
    }

    // Overlap: [mulai, selesai) bersinggungan dengan booking disetujui di tanggal sama.
    // Waktu WAJIB format H:i:s supaya perbandingan string benar.
    public function scopeBentrok(Builder $q, string $tanggal, string $mulai, string $selesai, ?int $kecualiId = null): Builder
    {
        return $q->disetujui()
            ->whereDate('tanggal', $tanggal)
            ->where('waktu_mulai', '<', $selesai)
            ->where('waktu_selesai', '>', $mulai)
            ->when($kecualiId, fn ($x) => $x->where('id', '!=', $kecualiId));
    }

    public function getJamAttribute(): string
    {
        return substr($this->waktu_mulai, 0, 5).' - '.substr($this->waktu_selesai, 0, 5);
    }

    /** Nomor pengajuan untuk dikutip di WhatsApp: BK-00012. */
    public function getKodeAttribute(): string
    {
        return 'BK-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }
}
