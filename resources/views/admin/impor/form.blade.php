@extends('layouts.app')
@section('title', 'Admin: Impor Data')

@section('content')
@php
    $inp = 'block w-full py-2.5 px-0 text-base sm:text-sm bg-transparent border-0 border-b border-[var(--line)] focus:outline-none focus:ring-0 focus:border-[var(--accent)] rounded-none text-[var(--ink)]';
    $lbl = 'eyebrow mono text-[11px] font-bold uppercase tracking-widest text-[var(--ink-soft)] mb-1 block';
    $btnGhost = 'inline-block px-4 py-2.5 text-sm font-semibold border border-[var(--line)] text-[var(--ink)] cursor-pointer touch-manipulation select-none transition hover:bg-[var(--ink)] hover:text-[var(--paper)] hover:border-[var(--ink)] active:scale-[0.97] active:bg-[var(--ink)] active:text-[var(--paper)]';
@endphp
<div class="max-w-3xl mx-auto px-5 py-9 sm:py-14 text-[var(--ink)]">
  <a data-lock href="{{ route('admin.home') }}" class="back-link inline-flex items-center gap-2 text-sm font-semibold text-[var(--ink-soft)] hover:text-[var(--ink)] cursor-pointer touch-manipulation select-none transition active:scale-95">
    <i class="ph ph-arrow-left"></i> Kembali
  </a>
  <h1 class="page-title text-2xl font-bold mt-8 mb-2">Impor Data</h1>
  <p class="text-sm text-[var(--ink-soft)] mt-0 mb-6">Format: Excel (.xlsx) atau CSV. Satu baris salah = seluruh impor dibatalkan. Tanggal: DD-MM-YYYY. Data asli: menu <a data-lock class="underline cursor-pointer touch-manipulation" href="{{ route('admin.ekspor.index') }}">Ekspor</a>.</p>

  @include('partials.flash')

  <div class="mb-10">
    <p class="{{ $lbl }}">1. Unduh template (header saja, format pasti sama dengan sistem)</p>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr>
            <th class="text-left py-2 eyebrow pr-6">Data</th>
            <th class="text-left py-2 eyebrow pr-6 hidden sm:table-cell">Kolom</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach ($types as $k => $label)
            <tr style="border-top:1px solid var(--line)">
              <td class="py-3 pr-6 align-top">
                <div class="font-semibold">{{ $label }}</div>
                <div class="mono text-[11px] text-[var(--ink-faint)] mt-1 break-words sm:hidden">{{ implode(', ', $columns[$k]) }}</div>
              </td>
              <td class="py-3 pr-6 align-top hidden sm:table-cell">
                <div class="mono text-[11px] text-[var(--ink-faint)] break-words">{{ implode(', ', $columns[$k]) }}</div>
              </td>
              <td class="py-3 text-right align-top whitespace-nowrap">
                <a data-lock data-download class="{{ $btnGhost }}" href="{{ route('admin.impor.template', $k) }}">Template</a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

    <div>
    <p class="{{ $lbl }}">2. Unggah</p>
    <form data-lock method="POST" action="{{ route('admin.impor.store') }}" enctype="multipart/form-data" class="space-y-6 mt-3">
      @csrf
      <div class="sm:grid sm:grid-cols-[13rem_1fr] sm:gap-x-8 sm:items-center">
        <label class="{{ $lbl }} sm:mb-0" for="type">Data</label>
        <select class="{{ $inp }} cursor-pointer" id="type" name="type" required>
          @foreach ($types as $k => $label)
            <option value="{{ $k }}" @selected(old('type', request('type')) === $k)>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div class="sm:grid sm:grid-cols-[13rem_1fr] sm:gap-x-8 sm:items-center">
        <label class="{{ $lbl }} sm:mb-0" for="file">File .xlsx / .csv (maks 2 MB)</label>
        <input class="{{ $inp }} cursor-pointer file:mr-4 file:px-4 file:py-2 file:text-sm file:font-semibold file:border file:border-[var(--line)] file:bg-transparent file:text-[var(--ink)] file:cursor-pointer file:transition hover:file:bg-[var(--ink)] hover:file:text-[var(--paper)] hover:file:border-[var(--ink)]" type="file" id="file" name="file" accept=".xlsx,.csv,.txt" required>
      </div>
      <div class="sm:grid sm:grid-cols-[13rem_1fr] sm:gap-x-8 sm:items-center">
        <label class="{{ $lbl }} sm:mb-0" for="mode">Mode</label>
        <select class="{{ $inp }} cursor-pointer" id="mode" name="mode" required>
          <option value="replace" @selected(old('mode', 'replace') === 'replace')>Ganti semua data lama</option>
          <option value="append" @selected(old('mode') === 'append')>Tambah ke data lama</option>
        </select>
        <p class="mono text-[11px] text-[var(--ink-faint)] mt-1 mb-0 sm:col-start-2">Sarpras: kosongkan kolom id untuk barang baru (kode dibuat otomatis).</p>
      </div>
      <div class="sm:grid sm:grid-cols-[13rem_1fr] sm:gap-x-8">
        <button type="submit" class="sm:col-start-2 justify-self-start inline-block px-6 py-2.5 text-sm font-semibold bg-[var(--ink)] text-[var(--paper)] cursor-pointer touch-manipulation select-none transition hover:bg-[var(--accent)] active:scale-[0.97] active:bg-[var(--accent)]">Impor</button>
      </div>
    </form>
  </div>
</div>

<script>
// data-lock: sekali klik -> terkunci (cegah klik ganda). aria-busy + pointer-events-none, BUKAN disabled
// (disabled sebelum FormData bikin field/tombol tidak ikut terkirim). Dibuka lagi saat pageshow (Back).
// data-download: halaman tidak pindah -> buka otomatis setelah 1,5 dtk.
document.addEventListener('DOMContentLoaded', () => {
  const LOCK = ['pointer-events-none', 'opacity-60'];
  const lock = el => { el.setAttribute('aria-busy', 'true'); el.classList.add(...LOCK); };
  const unlock = el => { el.removeAttribute('aria-busy'); el.classList.remove(...LOCK); };
  const unlockAll = () => document.querySelectorAll('[aria-busy="true"]').forEach(unlock);

  document.querySelectorAll('a[data-lock]').forEach(a => a.addEventListener('click', e => {
    if (e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey || a.target === '_blank') return;
    if (a.hasAttribute('aria-busy')) { e.preventDefault(); return; }
    lock(a);
    if (a.hasAttribute('data-download')) setTimeout(() => unlock(a), 1500);
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