<?php

namespace App\Models;

use App\Services\KelasMatcher;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $table = 'siswa';

    protected $fillable = ['nisn', 'nama_siswa', 'jenis_kelamin', 'kelas_umum', 'kelas_diniyyah', 'tahun_masuk'];

    /** Kelas (+ wali kelas, no_hp) hasil pencocokan kelas_umum. */
    public function kelas(): ?Kelas
    {
        return KelasMatcher::cari($this->kelas_umum);
    }
}
