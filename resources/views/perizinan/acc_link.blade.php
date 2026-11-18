@extends('layouts.app')
@section('title', 'ACC Izin '.$item->id_perizinan)
@section('head')
<meta name="robots" content="noindex, nofollow">
@endsection

@section('content')
@php
    $inp = 'block w-full py-2.5 px-0 text-base sm:text-sm bg-transparent border-0 border-b border-[var(--line)] focus:outline-none focus:ring-0 focus:border-[var(--accent)] rounded-none text-[var(--ink)]';
    $lbl = 'eyebrow mono text-[11px] font-bold uppercase tracking-widest text-[var(--ink-soft)] mb-1 block';
    $err = 'text-xs text-red-600 mt-1 m-0';
    $menunggu = $item->status === \App\Models\Perizinan::MENUNGGU;
    $row = fn ($k, $v) => '<div class="py-2.5 border-b border-[var(--line)]"><p class="'.$lbl.' m-0">'.e($k).'</p><p class="text-sm m-0 mt-0.5">'.e($v ?: '—').'</p></div>';
@endphp
<div class="max-w-xl mx-auto px-5 py-9 sm:py-14 text-[var(--ink)]">
  <p class="eyebrow mono text-[11px] font-bold uppercase tracking-widest text-[var(--ink-soft)] m-0">MA Nurul Ummah Kotagede</p>
  <div class="flex flex-wrap items-center justify-between gap-3 mt-2 mb-6">
    <h1 class="page-title text-2xl font-bold m-0">Permohonan izin siswa</h1>
    @include('partials.pz-badge', ['s' => $item->status])
  </div>

  @include('partials.flash')

  <div class="mb-8">
    {!! $row('Siswa', $item->nama_siswa.' · '.$item->kelas_umum) !!}
    {!! $row('Mulai', $item->tanggal_mulai->translatedFormat('l, j F Y').' · '.substr($item->jam_mulai, 0, 5)) !!}
    {!! $row('Selesai', $item->tanggal_selesai->translatedFormat('l, j F Y').' · '.substr($item->jam_selesai, 0, 5)) !!}
    {!! $row('Durasi', $item->durasi) !!}
    {!! $row('Alasan', $item->alasan) !!}
    {!! $row('Diajukan oleh', $item->petugas.' ('.str_replace('_', ' ', $item->status_petugas).')') !!}
    {!! $row('Wali kelas', $item->wali_kelas_tujuan) !!}
    @if ($item->diproses_oleh)
      {!! $row('Diproses', $item->diproses_oleh.' · '.$item->tanggal_acc?->translatedFormat('j M Y H:i')) !!}
      {!! $row('Catatan', $item->catatan_wali_kelas) !!}
    @endif
  </div>

  @if ($menunggu && $bisaLink)
    <form data-lock method="POST" class="border border-[var(--line)] p-5 space-y-5" autocomplete="off">
      @csrf
      <p class="{{ $lbl }} m-0">ACC oleh {{ $item->wali_kelas_tujuan }}</p>
      <div>
        <label class="{{ $lbl }}" for="pin">4 digit terakhir no HP Anda</label>
        <input class="{{ $inp }} mono tracking-[.4em]" id="pin" name="pin" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" required autocomplete="off">
        @error('pin')<p class="{{ $err }}">{{ $message }}</p>@enderror
      </div>
      <div>
        <label class="{{ $lbl }}" for="catatan_wali_kelas">Catatan (wajib jika ditolak)</label>
        <input class="{{ $inp }}" id="catatan_wali_kelas" name="catatan_wali_kelas" maxlength="200" value="{{ old('catatan_wali_kelas') }}">
        @error('catatan_wali_kelas')<p class="{{ $err }}">{{ $message }}</p>@enderror
      </div>
      <div class="flex flex-wrap gap-2">
        <button type="submit" formaction="{{ route('perizinan.acc_link.setujui', $item->uuid) }}" class="px-6 py-2.5 text-sm font-semibold bg-[var(--ink)] text-[var(--paper)] cursor-pointer touch-manipulation select-none transition hover:bg-[var(--accent)] active:scale-[0.97]">Setujui</button>
        <button type="submit" formaction="{{ route('perizinan.acc_link.tolak', $item->uuid) }}" class="px-6 py-2.5 text-sm font-semibold bg-white border border-[var(--line)] text-red-600 cursor-pointer touch-manipulation select-none transition hover:bg-red-600 hover:text-white hover:border-red-600 active:scale-[0.97]">Tolak</button>
      </div>
    </form>
  @elseif ($menunggu)
    <p class="text-sm border border-[var(--line)] p-4 m-0">Izin ini belum bisa di-ACC lewat tautan karena no HP wali kelas belum terdaftar di sistem. Hubungi admin, atau ACC lewat akun wali kelas di e-manu.</p>
  @else
    <p class="text-sm border border-[var(--line)] p-4 m-0">Izin ini sudah diproses ({{ str_replace('_', ' ', $item->status) }}). Tidak ada tindakan lagi.</p>
  @endif
</div>
@include('partials.lock')
@endsection
