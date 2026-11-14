<?php

namespace App\Http\Controllers;

use App\Models\GuruPiket;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use Illuminate\View\View;

class JadwalPelajaranController extends Controller
{
    public function index(): View
    {
        $all = JadwalPelajaran::orderBy('urutan')->orderBy('id')->get();

        // hari -> [[nama_kelas, rows], ...]; urutan kelas = kemunculan pertama (sama seperti Flask)
        $perHari = [];
        foreach (JadwalPelajaran::HARI as $hari) {
            $perKelas = $all->where('hari', $hari)->groupBy('nama_kelas');
            $perHari[$hari] = $perKelas->map(
                fn ($rows, $kelas) => [$kelas, $rows->sortBy('menit_mulai')->values()] // sortBy stabil
            )->values()->all();
        }

        return view('jadwal_pelajaran.jadwal_pelajaran_landing', [
            'jadwalPerHari' => $perHari,
            'piketPerHari' => GuruPiket::orderBy('id')->get()->groupBy('hari')->map->pluck('nama'),
            'waliKelasMap' => Kelas::pluck('wali_kelas', 'nama_kelas'),
        ]);
    }
}
