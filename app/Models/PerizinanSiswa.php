<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerizinanSiswa extends Model
{
    protected $table = 'perizinan_siswa';

    protected $fillable = ['perizinan_id', 'nisn', 'nama_siswa', 'kelas_umum'];

    public function perizinan(): BelongsTo
    {
        return $this->belongsTo(Perizinan::class);
    }
}
