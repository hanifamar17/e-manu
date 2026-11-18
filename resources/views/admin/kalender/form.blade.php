@extends('layouts.app')
@section('title', $item->exists ? 'Edit Kalender' : 'Tambah Kalender')

@section('content')
@php
    $inp = 'block w-full py-2.5 px-0 text-base sm:text-sm bg-transparent border-0 border-b border-[var(--line)] focus:outline-none focus:ring-0 focus:border-[var(--accent)] rounded-none text-[var(--ink)]';
    $lbl = 'eyebrow mono text-[11px] font-bold uppercase tracking-widest text-[var(--ink-soft)] mb-1 block';
@endphp
<div class="max-w-xl mx-auto px-5 py-9 sm:py-14 text-[var(--ink)]">
  <a data-lock href="{{ route('admin.kalender.index') }}" class="back-link inline-flex items-center gap-2 text-sm font-semibold text-[var(--ink-soft)] hover:text-[var(--ink)] cursor-pointer touch-manipulation select-none transition active:scale-95">
    <i class="ph ph-arrow-left"></i> Kembali
  </a>
  <h1 class="page-title text-2xl font-bold mt-8 mb-6">{{ $item->exists ? 'Edit' : 'Tambah' }} Kalender</h1>

  @include('partials.flash')

  <form data-lock method="POST" action="{{ $item->exists ? route('admin.kalender.update', $item) : route('admin.kalender.store') }}" class="space-y-6">
    @csrf
    @if ($item->exists) @method('PUT') @endif
    <div class="grid grid-cols-2 gap-6">
      <div>
        <label class="{{ $lbl }}" for="tanggal_mulai">Mulai</label>
        <input class="{{ $inp }} cursor-pointer" type="date" id="tanggal_mulai" name="tanggal_mulai" required
          value="{{ old('tanggal_mulai', $item->tanggal_mulai?->format('Y-m-d')) }}">
      </div>
      <div>
        <label class="{{ $lbl }}" for="tanggal_selesai">Selesai (kosong = 1 hari)</label>
        <input class="{{ $inp }} cursor-pointer" type="date" id="tanggal_selesai" name="tanggal_selesai"
          value="{{ old('tanggal_selesai', $item->tanggal_selesai?->format('Y-m-d')) }}">
      </div>
    </div>
    <div>
      <label class="{{ $lbl }}" for="kategori">Kategori</label>
      <select class="{{ $inp }} cursor-pointer" id="kategori" name="kategori" required>
        @foreach (\App\Models\KalenderAkademik::KATEGORI as $k)
          <option value="{{ $k }}" @selected(old('kategori', $item->kategori) === $k)>{{ ucfirst($k) }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="{{ $lbl }}" for="keterangan">Keterangan</label>
      <input class="{{ $inp }}" id="keterangan" name="keterangan" maxlength="200" required value="{{ old('keterangan', $item->keterangan) }}">
    </div>
    <button type="submit" class="inline-block px-6 py-2.5 text-sm font-semibold bg-[var(--ink)] text-[var(--paper)] cursor-pointer touch-manipulation select-none transition hover:bg-[var(--accent)] active:scale-[0.97] active:bg-[var(--accent)]">Simpan</button>
  </form>
</div>

<script>
// data-lock: sekali klik -> terkunci (cegah klik ganda). aria-busy + pointer-events-none, BUKAN disabled
// (disabled sebelum FormData bikin tombol/field tidak ikut terkirim). Dibuka lagi saat pageshow (Back).
document.addEventListener('DOMContentLoaded', () => {
  const LOCK = ['pointer-events-none', 'opacity-60'];
  const lock = el => { el.setAttribute('aria-busy', 'true'); el.classList.add(...LOCK); };
  const unlockAll = () => document.querySelectorAll('[aria-busy="true"]').forEach(el => {
    el.removeAttribute('aria-busy'); el.classList.remove(...LOCK);
  });

  document.querySelectorAll('a[data-lock]').forEach(a => a.addEventListener('click', e => {
    if (e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey || a.target === '_blank') return;
    if (a.hasAttribute('aria-busy')) { e.preventDefault(); return; }
    lock(a);
  }));

  document.querySelectorAll('form[data-lock]').forEach(f => f.addEventListener('submit', e => {
    const b = f.querySelector('button[type="submit"]');
    if (b && b.hasAttribute('aria-busy')) { e.preventDefault(); return; }
    if (b) lock(b);
  }));

  window.addEventListener('pageshow', unlockAll);
});
</script>
@endsection