@extends('layouts.app')
@section('title', 'Perizinan Siswa')

@section('content')
@php
    $wali = auth()->user()->hasRole('wali_kelas', 'admin');
    $menu = [
        ['route' => 'perizinan.form', 'icon' => 'ph-note-pencil', 'judul' => 'Buat Izin', 'desc' => 'Formulir izin siswa baru'],
        ['route' => 'perizinan.riwayat', 'icon' => 'ph-clock-counter-clockwise', 'judul' => 'Riwayat', 'desc' => 'Cari & lihat semua izin'],
    ];
    $tv = config('emanu.layar_token');
    if ($tv) {
        $menu[] = ['url' => route('perizinan.layar', $tv), 'blank' => true, 'icon' => 'ph-television-simple', 'judul' => 'Layar TV', 'desc' => 'Daftar izin hari ini, tampil otomatis (tab baru)'];
    }
    if ($wali) {
        array_splice($menu, 1, 0, [['route' => 'perizinan.acc', 'icon' => 'ph-seal-check', 'judul' => 'ACC Izin', 'desc' => $menunggu.' izin menunggu persetujuan']]);
    }
@endphp
<div class="max-w-5xl mx-auto px-5 py-9 sm:py-14 text-[var(--ink)]">
  <a data-lock href="{{ route('repository') }}" class="back-link inline-flex items-center gap-2 text-sm font-semibold text-[var(--ink-soft)] hover:text-[var(--ink)] cursor-pointer touch-manipulation select-none transition active:scale-95">
    <i class="ph ph-arrow-left"></i> Repositori
  </a>
  <h1 class="page-title text-2xl font-bold mt-8 mb-6">Perizinan Siswa</h1>

  @include('partials.flash')

  <div class="grid grid-cols-2 gap-3 mb-8">
    <div class="card-outline p-4 border border-[var(--line)]">
      <p class="eyebrow mono text-[11px] font-bold uppercase tracking-widest text-[var(--ink-soft)] m-0">Izin berlaku hari ini</p>
      <p class="text-3xl font-bold m-0 mt-1">{{ $hariIni->count() }}</p>
    </div>
    <div class="card-outline p-4 border border-[var(--line)]">
      <p class="eyebrow mono text-[11px] font-bold uppercase tracking-widest text-[var(--ink-soft)] m-0">Menunggu ACC</p>
      <p class="text-3xl font-bold m-0 mt-1">{{ $menunggu }}</p>
    </div>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-10">
    @foreach ($menu as $m)
      <a data-lock href="{{ $m['url'] ?? route($m['route']) }}"{!! empty($m['blank']) ? '' : ' target="_blank" rel="noopener"' !!}
         class="group card-outline flex items-start gap-3 p-4 border border-[var(--line)] cursor-pointer touch-manipulation select-none transition hover:bg-[var(--ink)] hover:text-[var(--paper)] hover:border-[var(--ink)] active:scale-[0.98] no-underline text-inherit">
        <i class="ph {{ $m['icon'] }} text-xl opacity-70 transition group-hover:opacity-100"></i>
        <div>
          <p class="font-bold text-[15px] m-0">{{ $m['judul'] }}</p>
          <p class="text-[12px] text-[var(--ink-soft)] m-0 mt-0.5 transition group-hover:text-[var(--paper)]/75">{{ $m['desc'] }}</p>
        </div>
      </a>
    @endforeach
  </div>

  <p class="eyebrow mono text-[11px] font-bold uppercase tracking-widest text-[var(--ink-soft)] mb-3">Izin berlaku hari ini</p>
  <div class="space-y-2">
    @forelse ($hariIni as $p)
      <a data-lock href="{{ route('perizinan.show', $p) }}" class="card-outline p-3 border border-[var(--line)] flex flex-wrap items-center justify-between gap-2 text-sm no-underline text-inherit transition hover:border-[var(--ink)] cursor-pointer">
        <span class="font-semibold">{{ $p->nama_siswa }} <span class="mono text-[11px] text-[var(--ink-soft)] font-normal">&middot; {{ $p->kelas_umum }} &middot; {{ $p->durasi }}</span></span>
        <span class="mono text-[11px]">{{ substr($p->jam_mulai, 0, 5) }}&ndash;{{ substr($p->jam_selesai, 0, 5) }} &middot; {{ $p->alasan }}</span>
      </a>
    @empty
      <div class="card-outline py-8 text-center mono text-[11px] font-bold uppercase tracking-widest text-[var(--ink-faint)]">— KOSONG —</div>
    @endforelse
  </div>
</div>
@include('partials.lock')
@endsection
