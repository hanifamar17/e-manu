<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KalenderAkademik extends Model
{
    public const KATEGORI = ['libur', 'agenda', 'akademik'];

    protected $table = 'kalender_akademik';

    protected $fillable = ['tanggal_mulai', 'tanggal_selesai', 'keterangan', 'kategori'];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];
}
