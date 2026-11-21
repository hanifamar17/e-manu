@extends('layouts.app')
@section('title', 'Form Peminjaman Lab')

@section('content')
@php
    $inp = 'block w-full py-2.5 px-0 text-sm bg-transparent border-0 border-b border-[var(--line)] focus:outline-none focus:ring-0 focus:border-[var(--accent)] rounded-none text-[var(--ink)]';
    $lbl = 'eyebrow mono text-[11px] font-bold uppercase tracking-widest text-[var(--ink-soft)] mb-1 block';
    $hariIni = \Carbon\Carbon::now('Asia/Jakarta')->toDateString();
@endphp
<div class="max-w-2xl mx-auto px-5 py-9 sm:py-14 text-[var(--ink)]">

  <a href="{{ route('booking_ruangan.booking_landing') }}"
    class="back-link inline-flex items-center gap-2 text-sm font-semibold text-[var(--ink-soft)] hover:text-[var(--ink)] transition-colors select-none touch-manipulation">
    <i class="ph ph-arrow-left"></i> Kembali
  </a>

  <div class="mt-8 mb-8">
    <h1 class="page-title text-2xl sm:text-[26px] font-bold tracking-tight leading-snug m-0">Form Peminjaman Lab</h1>
    <p class="page-sub text-md leading-relaxed text-[var(--ink-soft)] mt-2 max-w-[46ch]">MA Nurul Ummah Kotagede. Cek dulu
      <a href="{{ route('booking_ruangan.jadwal_penggunaan') }}" class="underline font-semibold">jadwal penggunaan</a>.</p>
  </div>

  @include('partials.flash')

  <form method="POST" action="{{ route('booking_ruangan.booking_store') }}" class="space-y-6">
    @csrf
    {{-- honeypot --}}
    <div style="position:absolute; left:-9999px" aria-hidden="true">
      <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
    </div>

    <div>
      <label class="{{ $lbl }}" for="nama_pemesan">Nama pemesan</label>
      <input class="{{ $inp }}" id="nama_pemesan" name="nama_pemesan" value="{{ old('nama_pemesan') }}" maxlength="100" required>
    </div>
    <div>
      <label class="{{ $lbl }}" for="email">Email (hasil persetujuan dikirim ke sini)</label>
      <input class="{{ $inp }}" type="email" id="email" name="email" value="{{ old('email') }}" maxlength="150" required>
    </div>
    <div>
      <label class="{{ $lbl }}" for="eksternal">Asal peminjam</label>
      <select class="{{ $inp }}" id="eksternal" name="eksternal" required>
        <option value="0" @selected(old('eksternal', '0') === '0')>Internal MA Nurul Ummah</option>
        <option value="1" @selected(old('eksternal') === '1')>Eksternal (wajib kontribusi perawatan)</option>
      </select>
    </div>
    <div>
      <label class="{{ $lbl }}" for="nama_kegiatan">Nama kegiatan</label>
      <input class="{{ $inp }}" id="nama_kegiatan" name="nama_kegiatan" value="{{ old('nama_kegiatan') }}" maxlength="150" required>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
      <div>
        <label class="{{ $lbl }}" for="tanggal">Tanggal</label>
<input class="{{ $inp }}" type="date" id="tanggal" name="tanggal" min="{{ $hariIni }}" value="{{ old('tanggal') }}" required>
      </div>
      <div>
        <label class="{{ $lbl }}" for="waktu_mulai">Mulai</label>
        <input class="{{ $inp }}" type="time" id="waktu_mulai" name="waktu_mulai" value="{{ old('waktu_mulai') }}" required>
      </div>
      <div>
        <label class="{{ $lbl }}" for="waktu_selesai">Selesai</label>
        <input class="{{ $inp }}" type="time" id="waktu_selesai" name="waktu_selesai" value="{{ old('waktu_selesai') }}" required>
      </div>
    </div>
    <div>
      <label class="{{ $lbl }}" for="catatan_tambahan">Catatan tambahan (opsional)</label>
      <textarea class="{{ $inp }}" id="catatan_tambahan" name="catatan_tambahan" rows="3" maxlength="1000">{{ old('catatan_tambahan') }}</textarea>
    </div>

    <button type="submit" data-lock
      class="w-full sm:w-auto px-6 py-2.5 text-sm font-semibold bg-[var(--ink)] text-[var(--paper)] cursor-pointer select-none touch-manipulation transition hover:opacity-90 active:scale-[0.98] active:opacity-80 [&[aria-busy=true]]:opacity-60 [&[aria-busy=true]]:cursor-wait">
      Kirim pengajuan
    </button>
  </form>
</div>
@endsection
