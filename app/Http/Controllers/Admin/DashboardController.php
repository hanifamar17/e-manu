<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingRuangan;
use App\Models\JadwalPelajaran;
use App\Models\KalenderAkademik;
use App\Models\Pengumuman;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'stat' => [
                'Booking menunggu' => BookingRuangan::where('status', BookingRuangan::PENDING)->count(),
                'Pengumuman'       => Pengumuman::count(),
                'Agenda kalender'  => KalenderAkademik::count(),
                'Baris jadwal'     => JadwalPelajaran::count(),
            ],
            'pending' => BookingRuangan::where('status', BookingRuangan::PENDING)
                ->orderBy('tanggal')->orderBy('waktu_mulai')->take(5)->get(),
        ]);
    }
}