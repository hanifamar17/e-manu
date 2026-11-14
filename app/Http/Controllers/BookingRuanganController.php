<?php

namespace App\Http\Controllers;

use App\Models\BookingRuangan;
use App\Models\KalenderAkademik;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingRuanganController extends Controller
{
    public function landing(): View
    {
        return view('booking_ruangan.booking_landing');
    }

    public function prosedur(): View
    {
        return view('booking_ruangan.booking_prosedur');
    }

    public function jadwal(): View
    {
        // Publik: hanya field non-sensitif (email TIDAK dikirim ke browser).
        $jadwal = BookingRuangan::disetujui()
            ->orderBy('tanggal')->orderBy('waktu_mulai')
            ->get(['nama_pemesan', 'nama_kegiatan', 'tanggal', 'waktu_mulai', 'waktu_selesai', 'catatan_tambahan'])
            ->map(fn ($b) => [
                'nama_pemesan' => $b->nama_pemesan,
                'nama_kegiatan' => $b->nama_kegiatan,
                'tanggal_iso' => $b->tanggal->format('Y-m-d'),
                'waktu_mulai_raw' => substr($b->waktu_mulai, 0, 5),
                'waktu_selesai_raw' => substr($b->waktu_selesai, 0, 5),
                'catatan_tambahan' => $b->catatan_tambahan ?? '',
            ])->values();

        $kalender = KalenderAkademik::orderBy('tanggal_mulai')->get()
            ->map(fn ($k) => [
                'tanggal_mulai_iso' => $k->tanggal_mulai->format('Y-m-d'),
                'tanggal_selesai_iso' => $k->tanggal_selesai->format('Y-m-d'),
                'keterangan' => $k->keterangan,
                'kategori' => $k->kategori,
            ])->values();

        return view('booking_ruangan.jadwal_penggunaan', ['jadwal' => $jadwal, 'kalender' => $kalender]);
    }

    public function form(): View
    {
        return view('booking_ruangan.form_peminjaman');
    }

    public function store(Request $request): RedirectResponse
    {
        // Honeypot: bot mengisi, manusia tidak. Pura-pura sukses.
        if ($request->filled('website')) {
            return redirect()->route('booking_ruangan.booking_landing');
        }

        $besok = Carbon::now('Asia/Jakarta')->addDay()->toDateString();

        $data = $request->validate([
            'nama_pemesan' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:150'],
            'eksternal' => ['required', 'in:0,1'],
            'nama_kegiatan' => ['required', 'string', 'max:150'],
            'tanggal' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.$besok],
            'waktu_mulai' => ['required', 'date_format:H:i'],
            'waktu_selesai' => ['required', 'date_format:H:i', 'after:waktu_mulai'],
            'catatan_tambahan' => ['nullable', 'string', 'max:1000'],
        ], [
            'tanggal.after_or_equal' => 'Pengajuan minimal H-1 (paling cepat besok).',
            'waktu_selesai.after' => 'Waktu selesai harus setelah waktu mulai.',
        ]);

        $mulai = $data['waktu_mulai'].':00';
        $selesai = $data['waktu_selesai'].':00';

        if (BookingRuangan::bentrok($data['tanggal'], $mulai, $selesai)->exists()) {
            return back()->withInput()->withErrors(['waktu_mulai' => 'Bentrok dengan jadwal yang sudah disetujui. Cek halaman Jadwal Penggunaan.']);
        }

        BookingRuangan::create([
            'nama_pemesan' => $data['nama_pemesan'],
            'email' => $data['email'],
            'eksternal' => $data['eksternal'] === '1',
            'nama_kegiatan' => $data['nama_kegiatan'],
            'tanggal' => $data['tanggal'],
            'waktu_mulai' => $mulai,
            'waktu_selesai' => $selesai,
            'catatan_tambahan' => $data['catatan_tambahan'] ?? null,
            'status' => BookingRuangan::PENDING,
        ]);

        return redirect()->route('booking_ruangan.booking_landing')
            ->with('status', 'Pengajuan terkirim. Hasil persetujuan dikirim ke email Anda.');
    }
}
