@extends('layouts.app')
@section('title', 'Ringkasan Pengajuan '.$b->kode)
@section('head')
<meta name="robots" content="noindex, nofollow">
@endsection

@section('content')
@php
    $lbl = 'eyebrow mono text-[11px] font-bold uppercase tracking-widest text-[var(--ink-soft)] mb-1 block';
    $row = fn ($k, $v) => '<div class="py-2.5 border-b border-[var(--line)]"><p class="'.$lbl.' m-0">'.e($k).'</p><p class="text-sm m-0 mt-0.5">'.e($v ?: '—').'</p></div>';
    $pending = $b->status === \App\Models\BookingRuangan::PENDING;
@endphp
<div class="max-w-2xl mx-auto px-5 py-9 sm:py-14 text-[var(--ink)]">
  <a data-lock href="{{ route('booking_ruangan.booking_landing') }}" class="back-link inline-flex items-center gap-2 text-sm font-semibold text-[var(--ink-soft)] hover:text-[var(--ink)] cursor-pointer touch-manipulation select-none transition active:scale-95">
    <i class="ph ph-arrow-left"></i> Beranda peminjaman
  </a>

  <div class="flex flex-wrap items-center justify-between gap-3 mt-8 mb-6">
    <div>
      <h1 class="page-title text-2xl font-bold m-0">Ringkasan pengajuan</h1>
      <p class="mono text-[11px] text-[var(--ink-soft)] m-0 mt-1">{{ $b->kode }}</p>
    </div>
    @include('partials.pz-badge', ['s' => $pending ? 'menunggu_acc' : $b->status])
  </div>

  @include('partials.flash')

  @if ($pending)
    <div class="border-2 border-[var(--ink)] p-5 mb-8">
      <p class="{{ $lbl }}">Langkah berikutnya</p>
      <p class="text-base font-bold m-0 mt-1">Kirim ke WhatsApp petugas Lab Komputer</p>
      <p class="text-sm text-[var(--ink-soft)] m-0 mt-2">
        Pengajuan sudah tersimpan, tapi petugas belum tahu sebelum Anda mengirim pesan. Tekan tombol di bawah, lalu pilih kontak petugas Lab Komputer di WhatsApp. Pesan sudah terisi otomatis.
      </p>
      <div class="flex flex-wrap gap-2 mt-4">
        <a href="{{ $waUrl }}" target="_blank" rel="noopener"
           class="px-5 py-2.5 text-sm font-semibold bg-[var(--ink)] text-[var(--paper)] cursor-pointer touch-manipulation select-none transition hover:bg-[var(--accent)] active:scale-[0.97] no-underline">Kirim lewat WhatsApp</a>
        <button type="button" id="salinPesan" data-pesan="{{ $pesan }}"
           class="px-5 py-2.5 text-sm font-semibold bg-white border border-[var(--line)] cursor-pointer touch-manipulation select-none transition hover:bg-[var(--ink)] hover:text-[var(--paper)] hover:border-[var(--ink)] active:scale-[0.97]">Salin pesan</button>
      </div>
      <p class="text-[11px] text-[var(--ink-soft)] m-0 mt-3">Hasil persetujuan dikirim ke email {{ $b->email }}.</p>
    </div>
  @elseif ($b->status === \App\Models\BookingRuangan::DISETUJUI)
    <p class="text-sm border border-[var(--line)] p-4 mb-8 m-0">Pengajuan disetujui. Jadwal tampil di halaman Jadwal Penggunaan.</p>
  @else
    <p class="text-sm border border-[var(--line)] p-4 mb-8 m-0">Pengajuan ditolak. Alasan: {{ $b->alasan_penolakan ?: '—' }}</p>
  @endif

  <div>
    {!! $row('Kegiatan', $b->nama_kegiatan) !!}
    {!! $row('Pemesan', $b->nama_pemesan.' · '.($b->eksternal ? 'Eksternal' : 'Internal MA Nurul Ummah')) !!}
    {!! $row('Tanggal', $b->tanggal->translatedFormat('l, j F Y')) !!}
    {!! $row('Jam', $b->jam) !!}
    {!! $row('Email', $b->email) !!}
    {!! $row('Catatan', $b->catatan_tambahan) !!}
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const btn = document.getElementById('salinPesan');
  if (!btn) return;
  btn.addEventListener('click', async () => {
    try { await navigator.clipboard.writeText(btn.dataset.pesan); btn.textContent = 'Pesan tersalin'; }
    catch (e) { window.prompt('Salin pesan:', btn.dataset.pesan); }
    setTimeout(() => btn.textContent = 'Salin pesan', 2000);
  });
});
</script>
@include('partials.lock')
@endsection
