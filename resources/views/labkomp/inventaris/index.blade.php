@extends('layouts.app')
@section('title', 'Lab Komp: Daftar Inventaris')

@section('content')
<div class="max-w-5xl mx-auto px-5 py-9 sm:py-14 text-[var(--ink)]">
  <a href="{{ route('inventory_labkomp.inventory_landing') }}" class="back-link inline-flex items-center gap-2 text-sm font-semibold text-[var(--ink-soft)] hover:text-[var(--ink)]">
    <i class="ph ph-arrow-left"></i> Kembali
  </a>

  <div class="flex flex-wrap items-end justify-between gap-3 mt-8 mb-6">
    <div>
      <h1 class="page-title text-2xl font-bold m-0">Inventaris Lab Komputer</h1>
      <p class="text-sm text-[var(--ink-soft)] mt-1 mb-0">Master data barang dan ketersediaan stok. Total: <span id="totalCount" class="font-semibold text-[var(--ink)]">{{ $items->count() }}</span>.</p>
    </div>
    <div class="flex items-center gap-3 text-sm">
      @if (auth()->user()->hasRole('admin'))
        <a data-lock class="inline-block px-4 py-2.5 text-sm font-semibold border border-[var(--line)] text-[var(--ink)] cursor-pointer touch-manipulation select-none transition hover:bg-[var(--ink)] hover:text-[var(--paper)] hover:border-[var(--ink)] active:scale-[0.97] active:bg-[var(--ink)] active:text-[var(--paper)]" href="{{ route('admin.impor.form', ['type' => 'labkomp_items']) }}">Impor</a>
      @endif
      <a data-lock class="inline-block px-4 py-2.5 text-sm font-semibold bg-[var(--ink)] text-[var(--paper)] cursor-pointer touch-manipulation select-none transition hover:bg-[var(--accent)] active:scale-[0.97] active:bg-[var(--accent)]" href="{{ route('inventory_labkomp.inventory_add') }}">+ Tambah</a>
    </div>
  </div>

  @include('partials.flash')

  <form onsubmit="return false" class="flex items-end gap-3 max-w-sm mb-2">
    <input id="filter" placeholder="Cari nama, kode, lokasi..."
      class="flex-1 py-2.5 px-0 text-base sm:text-sm bg-transparent border-0 border-b border-[var(--line)] focus:outline-none focus:ring-0 focus:border-[var(--accent)] rounded-none text-[var(--ink)]">
    <button type="button" onclick="document.getElementById('filter').dispatchEvent(new Event('input'))" class="inline-block px-4 py-2.5 text-sm font-semibold border border-[var(--line)] text-[var(--ink)] cursor-pointer touch-manipulation select-none transition hover:bg-[var(--ink)] hover:text-[var(--paper)] hover:border-[var(--ink)] active:scale-[0.97] active:bg-[var(--ink)] active:text-[var(--paper)]">Cari</button>
  </form>
  <div id="searchCount" class="text-xs text-[var(--ink-faint)] mb-6 hidden"></div>

  <div class="overflow-x-auto">
    <table class="w-full text-sm whitespace-nowrap">
      <thead>
        <tr>
          <th class="text-left py-2 eyebrow pr-6">Barang</th>
          <th class="text-left py-2 eyebrow pr-6 hidden sm:table-cell">Penempatan</th>
          <th class="text-left py-2 eyebrow pr-6">Stok</th>
          <th></th>
        </tr>
      </thead>
      <tbody id="tableBody">
        @foreach ($items as $item)
        <tr data-row style="border-top:1px solid var(--line)">
          <td class="py-3 pr-6">
            <div class="font-semibold">{{ $item->nama_barang }}</div>
            <div class="mono text-[11px] text-[var(--ink-soft)]">{{ $item->kode }}</div>
            <div class="text-[12px] text-[var(--ink-soft)] sm:hidden">{{ $item->lokasi }} ({{ $item->kondisi }})</div>
          </td>
          <td class="py-3 pr-6 hidden sm:table-cell">
            <div>{{ $item->lokasi }}</div>
            <div class="text-[12px] text-[var(--ink-soft)]">{{ $item->kondisi }}</div>
          </td>
          <td class="py-3 pr-6">
            <div class="font-semibold">{{ $item->stok }} <span class="font-normal text-[var(--ink-soft)]">{{ $item->satuan }}</span></div>
            <div class="mono text-[10px] font-bold uppercase tracking-wider {{ $item->stok > 0 ? 'text-[var(--accent)]' : 'text-red-600' }}">{{ $item->stok > 0 ? 'Tersedia' : 'Habis' }}</div>
          </td>
          <td class="py-3 text-right">
            <a data-lock class="inline-block py-2 px-1 underline text-xs mr-2 cursor-pointer touch-manipulation select-none transition hover:opacity-70 active:scale-95" href="{{ route('inventory_labkomp.inventory_edit', $item) }}">Edit</a>
            <a data-lock class="inline-block py-2 px-1 text-xs text-red-600 cursor-pointer touch-manipulation select-none transition hover:underline active:scale-95" href="{{ route('inventory_labkomp.inventory_delete', $item) }}">Hapus</a>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div id="emptyState" class="hidden py-10 text-center text-[var(--ink-faint)]">Tidak ada data ditemukan.</div>

  <div class="mt-6 flex items-center justify-between text-xs text-[var(--ink-soft)]">
    <div>Hal <span id="currentPage" class="text-[var(--ink)]">1</span>/<span id="totalPages" class="text-[var(--ink)]">1</span></div>
    <div class="flex gap-4">
      <button id="prevBtn" class="inline-block py-2 px-2 uppercase hover:text-[var(--ink)] disabled:opacity-30 disabled:pointer-events-none cursor-pointer touch-manipulation select-none transition active:scale-95">&larr; Prev</button>
      <button id="nextBtn" class="inline-block py-2 px-2 uppercase hover:text-[var(--ink)] disabled:opacity-30 disabled:pointer-events-none cursor-pointer touch-manipulation select-none transition active:scale-95">Next &rarr;</button>
    </div>
  </div>
</div>
<script>
// Tombol/tautan data-lock: sekali klik -> terkunci (cegah klik ganda), dibuka lagi saat halaman ditampilkan ulang (tombol Back).
document.addEventListener('DOMContentLoaded', () => {
  const LOCK = ['pointer-events-none', 'opacity-60'];
  const lock = el => { el.setAttribute('aria-busy', 'true'); el.classList.add(...LOCK); };
  document.querySelectorAll('a[data-lock]').forEach(a => a.addEventListener('click', e => {
    if (e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey || a.target === '_blank') return;   // tab baru: jangan kunci
    if (a.hasAttribute('aria-busy')) { e.preventDefault(); return; }
    lock(a);
  }));
  window.addEventListener('pageshow', () => document.querySelectorAll('a[aria-busy]').forEach(el => {
    el.removeAttribute('aria-busy'); el.classList.remove(...LOCK);
  }));
});
</script>
@include('labkomp.partials.tabel-js', ['perPage' => 15])
@endsection