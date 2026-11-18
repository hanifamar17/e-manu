@extends('layouts.app')
@section('title', 'Admin: Dashboard')

@section('content')
@php
    $menu = [
        ['route' => 'admin.booking.index',  'icon' => 'ph-calendar-check', 'judul' => 'Booking Ruangan',   'desc' => 'Setujui / tolak pengajuan'],
        ['route' => 'admin.kalender.index', 'icon' => 'ph-calendar-dots',  'judul' => 'Kalender Akademik', 'desc' => 'Tambah, ubah, hapus agenda'],
        ['route' => 'admin.impor.form',     'icon' => 'ph-upload-simple',  'judul' => 'Impor Data',        'desc' => 'Unduh template, unggah (xlsx / CSV)'],
        ['route' => 'admin.ekspor.index',   'icon' => 'ph-download-simple', 'judul' => 'Ekspor Data',      'desc' => 'Unduh data asli (xlsx)'],
        ['route' => 'admin.pengumuman.index', 'icon' => 'ph-megaphone', 'judul' => 'Pengumuman', 'desc' => 'Tambah, ubah, hapus pengumuman'],
        ['route' => 'admin.kelas.index',    'icon' => 'ph-chalkboard-teacher', 'judul' => 'Kelas & Wali Kelas', 'desc' => 'Wali kelas, no HP, akun login'],
        ['route' => 'admin.akun.index',     'icon' => 'ph-users',          'judul' => 'Akun Pengguna',     'desc' => 'Admin, guru, wali kelas'],
    ];
@endphp
<div class="max-w-5xl mx-auto px-5 py-9 sm:py-14 text-[var(--ink)]">
  <a href="{{ route('repository') }}" class="back-link inline-flex items-center gap-2 text-sm font-semibold text-[var(--ink-soft)] hover:text-[var(--ink)]">
    <i class="ph ph-arrow-left"></i> Repositori
  </a>
  <h1 class="page-title text-2xl font-bold mt-8 mb-6">Dashboard Admin</h1>

  @include('partials.flash')

  <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-10">
    @foreach ($stat as $label => $n)
      <div class="card-outline p-4 border border-[var(--line)]">
        <p class="eyebrow mono text-[11px] font-bold uppercase tracking-widest text-[var(--ink-soft)] m-0">{{ $label }}</p>
        <p class="text-3xl font-bold m-0 mt-1">{{ $n }}</p>
      </div>
    @endforeach
  </div>

  <p class="eyebrow mono text-[11px] font-bold uppercase tracking-widest text-[var(--ink-soft)] mb-3">Menu</p>
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-10">
    @foreach ($menu as $m)
      @if ($m['route'])
        <a data-lock href="{{ route($m['route']) }}"
   class="group card-outline flex items-start gap-3 p-4 border border-[var(--line)] cursor-pointer touch-manipulation select-none transition
          hover:bg-[var(--ink)] hover:text-[var(--paper)] hover:border-[var(--ink)]
          active:scale-[0.98] no-underline text-inherit">
  <i class="ph {{ $m['icon'] }} text-xl opacity-70 transition group-hover:opacity-100 group-hover:text-[var(--paper)]"></i>
  <div>
    <p class="font-bold text-[15px] m-0">{{ $m['judul'] }}</p>
    <p class="text-[12px] text-[var(--ink-soft)] m-0 mt-0.5 transition group-hover:text-[var(--paper)]/75">{{ $m['desc'] }}</p>
  </div>
</a>
      @else
        <div class="flex items-start gap-3 p-4 border border-dashed border-[var(--line)] opacity-50">
          <i class="ph {{ $m['icon'] }} text-xl"></i>
          <div>
            <p class="font-bold text-[15px] m-0">{{ $m['judul'] }}</p>
            <p class="text-[12px] m-0 mt-0.5">{{ $m['desc'] }}</p>
          </div>
        </div>
      @endif
    @endforeach
  </div>

  <div class="flex items-baseline justify-between mb-3">
    <p class="eyebrow mono text-[11px] font-bold uppercase tracking-widest text-[var(--ink-soft)] m-0">Pengajuan menunggu</p>
    <a data-lock href="{{ route('admin.booking.index') }}"
       class="underline text-xs cursor-pointer touch-manipulation select-none transition hover:text-[var(--ink)] active:scale-95">
      Lihat semua
    </a>
  </div>
  <div class="space-y-2">
    @forelse ($pending as $b)
      <div class="card-outline p-3 border border-[var(--line)] flex flex-wrap justify-between gap-2 text-sm">
        <span class="font-semibold">{{ $b->nama_kegiatan }} <span class="mono text-[11px] text-[var(--ink-soft)] font-normal">&middot; {{ $b->nama_pemesan }}</span></span>
        <span class="mono text-[11px]">{{ $b->tanggal->translatedFormat('D, j M Y') }} &middot; {{ $b->jam }}</span>
      </div>
    @empty
      <div class="card-outline py-8 text-center mono text-[11px] font-bold uppercase tracking-widest text-[var(--ink-faint)]">— KOSONG —</div>
    @endforelse
  </div>
</div>

<script>
// Tombol/tautan data-lock: sekali klik -> terkunci (cegah klik ganda), dibuka lagi saat halaman ditampilkan ulang (tombol Back).
document.addEventListener('DOMContentLoaded', () => {
  const LOCK = ['pointer-events-none', 'opacity-60'];
  const lock = el => { el.setAttribute('aria-busy', 'true'); el.classList.add(...LOCK); };
  const unlockAll = () => document.querySelectorAll('[data-lock] [aria-busy], a[data-lock][aria-busy], button[aria-busy]').forEach(el => {
    el.removeAttribute('aria-busy'); el.classList.remove(...LOCK);
  });

  document.querySelectorAll('a[data-lock]').forEach(a => a.addEventListener('click', e => {
    if (e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey || a.target === '_blank') return;
    if (a.hasAttribute('aria-busy')) { e.preventDefault(); return; }
    lock(a);
  }));

  window.addEventListener('pageshow', unlockAll);
});
</script>
@endsection