@extends('layouts.app')
@section('title', 'Admin: Kelas & Wali Kelas')

@section('content')
<div class="max-w-5xl mx-auto px-5 py-9 sm:py-14 text-[var(--ink)]">
  <a data-lock href="{{ route('admin.home') }}" class="back-link inline-flex items-center gap-2 text-sm font-semibold text-[var(--ink-soft)] hover:text-[var(--ink)] cursor-pointer touch-manipulation select-none transition active:scale-95">
    <i class="ph ph-arrow-left"></i> Dashboard Admin
  </a>
  <h1 class="page-title text-2xl font-bold mt-8 mb-2">Kelas &amp; Wali Kelas</h1>
  <p class="text-sm text-[var(--ink-soft)] mb-6">Kelas ditambah / dihapus lewat menu Impor Data. Di sini: ubah wali kelas, nomor HP, dan akun login (role wali_kelas) tiap kelas.</p>

  @include('partials.flash')

  <div class="space-y-2">
    @forelse ($items as $k)
      <div class="card-outline p-4 border border-[var(--line)] flex flex-wrap items-center justify-between gap-3">
        <div class="min-w-0">
          <p class="font-bold text-[15px] m-0">{{ $k->nama_kelas }}</p>
          <p class="text-sm m-0 mt-0.5">{{ $k->wali_kelas ?: '—' }}</p>
          <p class="mono text-[11px] text-[var(--ink-soft)] m-0 mt-1">
            HP: {{ $k->no_hp ?: '—' }} &middot; Akun: {{ $k->user?->username ?: 'belum dihubungkan' }}
          </p>
        </div>
        <a data-lock href="{{ route('admin.kelas.edit', $k) }}"
           class="px-4 py-2.5 text-sm font-semibold bg-white border border-[var(--line)] cursor-pointer touch-manipulation select-none transition hover:bg-[var(--ink)] hover:text-[var(--paper)] hover:border-[var(--ink)] active:scale-[0.97] no-underline text-inherit">
          Ubah
        </a>
      </div>
    @empty
      <div class="card-outline py-8 text-center mono text-[11px] font-bold uppercase tracking-widest text-[var(--ink-faint)]">— KOSONG: IMPOR KELAS DULU —</div>
    @endforelse
  </div>
</div>
@include('partials.lock')
@endsection
