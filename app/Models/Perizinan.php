<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class Perizinan extends Model
{
    protected $table = 'perizinan';

    public const MENUNGGU = 'menunggu_acc';
    public const AKTIF = 'aktif';          // tanpa ACC
    public const DISETUJUI = 'disetujui';  // setelah ACC wali kelas
    public const DITOLAK = 'ditolak';
    public const DIBATALKAN = 'dibatalkan';

    public const DURASI = [
        '2_jpl' => '2 JPL',
        '4_jpl' => '4 JPL',
        '6_jpl' => '6 JPL',
        '8_jpl' => '8 JPL',
        '10_jpl' => '10 JPL',
        '1_hari' => '1 Hari',
        'lebih_1_hari' => 'Lebih dari 1 Hari',
    ];

    public const STATUS_PETUGAS = ['guru', 'wali_kelas', 'pembimbing'];

    protected $fillable = [
        'id_perizinan', 'uuid', 'nisn', 'nama_siswa', 'kelas_umum', 'wali_kelas_tujuan',
        'tanggal_mulai', 'jam_mulai', 'tanggal_selesai', 'jam_selesai', 'durasi', 'alasan',
        'petugas', 'status_petugas', 'need_acc', 'status',
        'diproses_oleh', 'tanggal_acc', 'catatan_wali_kelas',
        'dibatalkan_oleh', 'alasan_batal', 'dibatalkan_at',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date:Y-m-d',
        'tanggal_selesai' => 'date:Y-m-d',
        'need_acc' => 'boolean',
        'tanggal_acc' => 'datetime',
        'dibatalkan_at' => 'datetime',
    ];

    protected static function booted(): void
{
    static::creating(function (self $m) {
        $m->uuid ??= (string) Str::uuid();
        $m->id_perizinan ??= self::buatId($m->tanggal_mulai);
    });

    static::saved(fn () => Cache::forget('layar.izin'));
    static::deleted(fn () => Cache::forget('layar.izin'));
}

    /** PZ-YYYYMMDD-XXXX, unik. */
    public static function buatId($tanggal): string
    {
        $tgl = \Illuminate\Support\Carbon::parse($tanggal)->format('Ymd');
        do {
            $id = 'PZ-' . $tgl . '-' . strtoupper(Str::random(4));
        } while (static::where('id_perizinan', $id)->exists());
        return $id;
    }

    public function getRouteKeyName(): string
    {
        return 'id_perizinan';
    }

    /** Izin yang berlaku hari ini (rentang mulai..selesai, bukan hanya tanggal mulai). */
    public function scopeBerlakuHariIni(Builder $q): Builder
    {
        $hari = now()->toDateString();
        return $q->whereIn('status', [self::AKTIF, self::DISETUJUI])
            ->whereDate('tanggal_mulai', '<=', $hari)
            ->whereDate('tanggal_selesai', '>=', $hari);
    }

    /** Cek tumpang tindih rentang waktu untuk siswa yang sama (status berjalan). */
    public static function adaTumpangTindih(string $nisn, string $mulai, string $selesai): bool
    {
        return static::where('nisn', $nisn)
            ->whereIn('status', [self::MENUNGGU, self::AKTIF, self::DISETUJUI])
            ->whereRaw("TIMESTAMP(tanggal_mulai, jam_mulai) < ?", [$selesai])
            ->whereRaw("TIMESTAMP(tanggal_selesai, jam_selesai) > ?", [$mulai])
            ->exists();
    }

    public function bisaDibatalkanOleh(?string $role): bool
    {
        if ($this->status === self::MENUNGGU) {
            return true; // siapa saja yang login
        }
        return in_array($this->status, [self::AKTIF, self::DISETUJUI], true) && $role === 'admin';
    }
}
