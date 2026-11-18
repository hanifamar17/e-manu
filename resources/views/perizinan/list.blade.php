@extends('layouts.app')
@section('title', $mode === 'acc' ? 'ACC Izin' : 'Riwayat Perizinan')

@section('content')
@php
    $inp = 'block w-full py-2.5 px-0 text-base sm:text-sm bg-transparent border-0 border-b border-[var(--line)] focus:outline-none focus:ring-0 focus:border-[var(--accent)] rounded-none text-[var(--ink)]';
    $statuses = ['menunggu_acc' => 'Menunggu ACC', 'aktif' => 'Aktif', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak', 'dibatalkan' => 'Dibatalkan'];
@endphp
<div class="max-w-5xl mx-auto px-5 py-9 sm:py-14 text-[var(--ink)]">
  <a data-lock href="{{ route('perizinan.index') }}" class="back-link inline-flex items-center gap-2 text-sm font-semibold text-[var(--ink-soft)] hover:text-[var(--ink)] cursor-pointer touch-manipulation select-none transition active:scale-95">
    <i class="ph ph-arrow-left"></i> Perizinan
  </a>
  <h1 class="page-title text-2xl font-bold mt-8 mb-6">{{ $mode === 'acc' ? 'ACC Izin' : 'Riwayat Perizinan' }}</h1>

  @include('partials.flash')

  <form data-lock method="GET" class="flex flex-wrap items-end gap-4 mb-6">
    <div class="flex-1 min-w-[180px]">
      <input class="{{ $inp }}" name="q" value="{{ $cari }}" placeholder="Cari nama / NISN / ID izin" enterkeyhint="search">
    </div>
    @if ($mode === 'riwayat')
      <select class="{{ $inp }} !w-auto cursor-pointer" name="status">
        <option value="">Semua status</option>
        @foreach ($statuses as $k => $l)
          <option value="{{ $k }}" @selected($status === $k)>{{ $l }}</option>
        @endforeach
      </select>
    @endif
    <button type="submit" class="px-4 py-2.5 text-sm font-semibold bg-white border border-[var(--line)] cursor-pointer touch-manipulation select-none transition hover:bg-[var(--ink)] hover:text-[var(--paper)] hover:border-[var(--ink)] active:scale-[0.97]">Cari</button>
  </form>

  <div class="space-y-2">
    @forelse ($items as $p)
      <a data-lock href="{{ route('perizinan.show', $p) }}" class="card-outline p-4 border border-[var(--line)] flex flex-wrap items-center justify-between gap-3 no-underline text-inherit transition hover:border-[var(--ink)] cursor-pointer">
        <div class="min-w-0">
          <p class="font-bold text-[15px] m-0">{{ $p->nama_siswa }} <span class="mono text-[11px] text-[var(--ink-soft)] font-normal">&middot; {{ $p->kelas_umum }}</span></p>
          <p class="text-sm m-0 mt-0.5">{{ $p->alasan }}</p>
          <p class="mono text-[11px] text-[var(--ink-soft)] m-0 mt-1">
            {{ $p->id_perizinan }} &middot; {{ $p->durasi }} &middot;
            {{ $p->tanggal_mulai->translatedFormat('j M Y') }} {{ substr($p->jam_mulai, 0, 5) }}
            &rarr; {{ $p->tanggal_selesai->translatedFormat('j M Y') }} {{ substr($p->jam_selesai, 0, 5) }}
          </p>
        </div>
        @include('partials.pz-badge', ['s' => $p->status])
      </a>
    @empty
      <div class="card-outline py-8 text-center mono text-[11px] font-bold uppercase tracking-widest text-[var(--ink-faint)]">— {{ $mode === 'acc' ? 'TIDAK ADA IZIN MENUNGGU' : 'KOSONG' }} —</div>
    @endforelse
  </div>

  @if ($items->hasPages())
    <div class="flex items-center justify-between mt-6 mono text-[11px] font-bold uppercase tracking-widest">
      <span class="text-[var(--ink-soft)]">Hal {{ $items->currentPage() }}/{{ $items->lastPage() }}</span>
      <div class="flex gap-2">
        @if ($items->previousPageUrl())<a data-lock href="{{ $items->previousPageUrl() }}" class="px-3 py-2 border border-[var(--line)] no-underline text-inherit hover:bg-[var(--ink)] hover:text-[var(--paper)] transition cursor-pointer active:scale-95">&larr; Prev</a>@endif
        @if ($items->nextPageUrl())<a data-lock href="{{ $items->nextPageUrl() }}" class="px-3 py-2 border border-[var(--line)] no-underline text-inherit hover:bg-[var(--ink)] hover:text-[var(--paper)] transition cursor-pointer active:scale-95">Next &rarr;</a>@endif
      </div>
    </div>
  @endif
</div>
@include('partials.lock')
@endsection
