<!DOCTYPE html>
<html lang="id">
<body style="font-family: Arial, sans-serif; color:#1f1c19; line-height:1.5">
    <h2 style="margin:0 0 12px">Peminjaman Lab Komputer
        {{ $booking->status === 'disetujui' ? 'Disetujui' : 'Ditolak' }}</h2>
    <p>Halo <b>{{ $booking->nama_pemesan }}</b>,</p>
    <table cellpadding="6" cellspacing="0" style="border-collapse:collapse; border:1px solid #e6e0d6">
        <tr><td>Kegiatan</td><td><b>{{ $booking->nama_kegiatan }}</b></td></tr>
        <tr><td>Tanggal</td><td>{{ $booking->tanggal->translatedFormat('l, j F Y') }}</td></tr>
        <tr><td>Waktu</td><td>{{ $booking->jam }} WIB</td></tr>
    </table>
    @if ($booking->status === 'disetujui')
        <p>Pengajuan Anda <b>disetujui</b>. Pintu lab dibuka oleh Kepala Lab. Mohon patuhi prosedur dan jaga kebersihan.</p>
        @if ($booking->eksternal)
            <p>Peminjam eksternal wajib menyelesaikan kontribusi perawatan sebelum kegiatan dimulai.</p>
        @endif
    @else
        <p>Pengajuan Anda <b>ditolak</b>.</p>
        <p>Alasan: {{ $booking->alasan_penolakan }}</p>
        <p>Silakan ajukan ulang dengan jadwal lain.</p>
    @endif
    <p style="color:#6b6459; font-size:12px">MA Nurul Ummah Kotagede</p>
</body>
</html>
