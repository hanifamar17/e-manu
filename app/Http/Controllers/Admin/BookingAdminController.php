<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\BookingStatusMail;
use App\Models\BookingRuangan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class BookingAdminController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', BookingRuangan::PENDING);
        if (! in_array($status, [BookingRuangan::PENDING, BookingRuangan::DISETUJUI, BookingRuangan::DITOLAK], true)) {
            $status = BookingRuangan::PENDING;
        }

        $cari = trim((string) $request->query('q'));
        $q = BookingRuangan::where('status', $status);
        if ($cari !== '') {
            $like = '%'.addcslashes($cari, '%_\\').'%';
            $q->where(fn ($w) => $w->where('nama_kegiatan', 'like', $like)->orWhere('nama_pemesan', 'like', $like)->orWhere('email', 'like', $like));
        }
        $items = $q->orderBy('tanggal')->orderBy('waktu_mulai')->paginate(20)->withQueryString();

        // Tandai pending yang bentrok dengan yang sudah disetujui.
        $items->getCollection()->each(function ($b) {
            $b->ada_bentrok = $b->status === BookingRuangan::PENDING
                && BookingRuangan::bentrok($b->tanggal->format('Y-m-d'), $b->waktu_mulai, $b->waktu_selesai, $b->id)->exists();
        });

        return view('admin.booking.index', [
            'items' => $items,
            'status' => $status,
            'cari' => $cari,
            'jumlahPending' => BookingRuangan::where('status', BookingRuangan::PENDING)->count(),
        ]);
    }

    public function setujui(Request $request, BookingRuangan $booking): RedirectResponse
    {
        $ok = DB::transaction(function () use ($request, $booking) {
            $b = BookingRuangan::whereKey($booking->id)->lockForUpdate()->first();
            if ($b->status !== BookingRuangan::PENDING) {
                return 'Sudah diproses sebelumnya.';
            }
            if (BookingRuangan::bentrok($b->tanggal->format('Y-m-d'), $b->waktu_mulai, $b->waktu_selesai, $b->id)->exists()) {
                return 'Bentrok dengan booking lain yang sudah disetujui.';
            }
            $b->update([
                'status' => BookingRuangan::DISETUJUI,
                'alasan_penolakan' => null,
                'ditinjau_oleh' => $request->user()->id,
                'ditinjau_pada' => now(),
            ]);

            return true;
        });

        if ($ok !== true) {
            return back()->withErrors(['booking' => $ok]);
        }

        $this->kirimEmail($booking->fresh());

        return back()->with('status', 'Disetujui. Email dikirim ke pemesan.');
    }

    public function tolak(Request $request, BookingRuangan $booking): RedirectResponse
    {
        $data = $request->validate(['alasan_penolakan' => ['required', 'string', 'max:500']]);

        if ($booking->status !== BookingRuangan::PENDING) {
            return back()->withErrors(['booking' => 'Sudah diproses sebelumnya.']);
        }

        $booking->update([
            'status' => BookingRuangan::DITOLAK,
            'alasan_penolakan' => $data['alasan_penolakan'],
            'ditinjau_oleh' => $request->user()->id,
            'ditinjau_pada' => now(),
        ]);

        $this->kirimEmail($booking->fresh());

        return back()->with('status', 'Ditolak. Email dikirim ke pemesan.');
    }

    public function destroy(BookingRuangan $booking): RedirectResponse
    {
        $booking->delete();

        return back()->with('status', 'Dihapus.');
    }

    // Gagal kirim email tidak boleh membatalkan keputusan.
    private function kirimEmail(BookingRuangan $b): void
    {
        try {
            Mail::to($b->email)->send(new BookingStatusMail($b));
        } catch (\Throwable $e) {
            report($e);
            session()->flash('warning', 'Status tersimpan, tapi email gagal terkirim. Cek konfigurasi MAIL_*.');
        }
    }
}
