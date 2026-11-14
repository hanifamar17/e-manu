<?php

namespace App\Mail;

use App\Models\BookingRuangan;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class BookingStatusMail extends Mailable
{
    public function __construct(public BookingRuangan $booking) {}

    public function envelope(): Envelope
    {
        $label = $this->booking->status === BookingRuangan::DISETUJUI ? 'Disetujui' : 'Ditolak';

        return new Envelope(subject: "Peminjaman Lab Komputer {$label}: {$this->booking->nama_kegiatan}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.booking_status');
    }
}
