@extends('layouts.app')
@section('title', 'Admin: Barang Pinjaman')

@section('content')
<div class="max-w-5xl mx-auto px-5 py-9 sm:py-14 text-[var(--ink)]">
  <a href="{{ route('repository') }}" class="back-link inline-flex items-center gap-2 text-sm font-semibold text-[var(--ink-soft)] hover:text-[var(--ink)]">
    <i class="ph ph-arrow-left"></i> Kembali
  </a>

  <div class="flex flex-wrap items-end justify-between gap-3 mt-8 mb-6">
    <div>
      <h1 class="page-title text-2xl font-bold m-0">Barang Pinjaman</h1>
      <p class="text-sm text-[var(--ink-soft)] mt-1 mb-0">Hanya barang di daftar ini yang muncul di form Peminjaman Aset.</p>
    </div>
    <div class="flex items-center gap-3 text-sm">
      @if (auth()->user()->hasRole('admin'))
        <a data-lock class="inline-block px-4 py-2.5 text-sm font-semibold border border-[var(--line)] text-[var(--ink)] cursor-pointer touch-manipulation select-none transition hover:bg-[var(--ink)] hover:text-[var(--paper)] hover:border-[var(--ink)] active:scale-[0.97] active:bg-[var(--ink)] active:text-[var(--paper)]" href="{{ route('admin.impor.form', ['type' => 'barang_pinjaman']) }}">Impor</a>
      @endif
      <a data-lock class="inline-block px-4 py-2.5 bg-[var(--ink)] text-[var(--paper)] font-semibold cursor-pointer touch-manipulation select-none transition hover:bg-[var(--accent)] active:scale-[0.97] active:bg-[var(--accent)]" href="{{ route('barang_pinjaman.create') }}">+ Tambah Barang</a>
    </div>
  </div>

  @include('partials.flash')

  <div id="tabs">@include('partials.sarpras_tabs', ['active' => '__pinjam', 'q' => $q])</div>

  <form id="formCari" method="GET" action="{{ route('barang_pinjaman.index') }}" autocomplete="off" class="flex items-end gap-3 max-w-sm mb-2">
    <input id="q" name="q" value="{{ $q }}" maxlength="100" enterkeyhint="search" placeholder="Cari nama, merk, lokasi, kode..."
      class="flex-1 py-2.5 px-0 text-base sm:text-sm bg-transparent border-0 border-b border-[var(--line)] focus:outline-none focus:ring-0 focus:border-[var(--accent)] rounded-none text-[var(--ink)]">
  </form>

  <div id="hasil" class="transition-opacity">
    <p class="text-xs text-[var(--ink-faint)] m-0 mb-6 min-h-[1rem]">{{ ($q !== '' && method_exists($items, 'total')) ? 'Ditemukan '.$items->total().' data' : '' }}</p>

    @unless ($items->isEmpty())
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
        <tbody>
          @foreach ($items as $r)
            <tr style="border-top:1px solid var(--line)">
              <td class="py-3 pr-6">
                <div class="font-semibold">{{ $r->nama_barang }}</div>
                <div class="mono text-[11px] text-[var(--ink-soft)]">{{ $r->merk }} &middot; {{ $r->kode }}</div>
                <div class="text-[12px] text-[var(--ink-soft)] sm:hidden">{{ $r->lokasi }} ({{ $r->sub_bidang }})</div>
              </td>
              <td class="py-3 pr-6 hidden sm:table-cell">
                <div>{{ $r->lokasi }}</div>
                <div class="text-[12px] text-[var(--ink-soft)]">{{ $r->sub_bidang }}</div>
              </td>
              <td class="py-3 pr-6">
                <div class="font-semibold">{{ $r->jumlah_tersedia }} / {{ $r->jumlah_total }}</div>
                <div class="mono text-[10px] font-bold uppercase tracking-wider {{ $r->status === 'Habis' ? 'text-red-600' : 'text-[var(--accent)]' }}">{{ $r->status }}</div>
              </td>
              <td class="py-3 text-right">
                <a data-lock class="inline-block py-2 px-1 underline text-xs mr-2 cursor-pointer touch-manipulation select-none transition hover:opacity-70 active:scale-95" href="{{ route('barang_pinjaman.edit', $r) }}">Edit</a>
                <a data-lock class="inline-block py-2 px-1 text-xs text-red-600 cursor-pointer touch-manipulation select-none transition hover:underline active:scale-95" href="{{ route('barang_pinjaman.hapus', $r) }}">Hapus</a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @else
      <div class="py-10 text-center text-[var(--ink-faint)]">{{ $q !== '' ? 'Tidak ada data ditemukan.' : 'Belum ada barang pinjaman. Klik "+ Tambah Barang".' }}</div>
    @endunless

    @php
      $items->withQueryString();
      $last = method_exists($items, 'lastPage') ? max($items->lastPage(), 1) : null;
      $pg = 'inline-block py-2 px-2 uppercase cursor-pointer touch-manipulation select-none transition hover:text-[var(--ink)] active:scale-95';
    @endphp
    <div class="mt-6 flex items-center justify-between text-xs text-[var(--ink-soft)]">
      <div>Hal <span class="text-[var(--ink)]">{{ $items->currentPage() }}</span>@if ($last)/<span class="text-[var(--ink)]">{{ $last }}</span>@endif</div>
      <div class="flex gap-4">
        @if ($items->onFirstPage())
          <span class="{{ $pg }} opacity-30 pointer-events-none">&larr; Prev</span>
        @else
          <a data-lock class="{{ $pg }}" href="{{ $items->previousPageUrl() }}">&larr; Prev</a>
        @endif
        @if ($items->hasMorePages())
          <a data-lock class="{{ $pg }}" href="{{ $items->nextPageUrl() }}">Next &rarr;</a>
        @else
          <span class="{{ $pg }} opacity-30 pointer-events-none">Next &rarr;</span>
        @endif
      </div>
    </div>
  </div>
</div>

<script>
// Tautan data-lock: sekali klik -> terkunci (cegah klik ganda); dibuka lagi saat halaman ditampilkan ulang (Back). Delegasi, jadi tautan hasil fetch ikut terkunci.
document.addEventListener('DOMContentLoaded', () => {
  const LOCK = ['pointer-events-none', 'opacity-60'];
  const lock = el => { el.setAttribute('aria-busy', 'true'); el.classList.add(...LOCK); };

  document.addEventListener('click', e => {
    const a = e.target.closest('a[data-lock]');
    if (!a) return;
    if (e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey || a.target === '_blank') return;
    if (a.hasAttribute('aria-busy')) { e.preventDefault(); return; }
    lock(a);
  });
  window.addEventListener('pageshow', () => document.querySelectorAll('a[aria-busy]').forEach(el => {
    el.removeAttribute('aria-busy'); el.classList.remove(...LOCK);
  }));

  // Pencarian: debounce 300 ms, ganti #hasil + #tabs tanpa reload.
  const form = document.getElementById('formCari'), q = document.getElementById('q');
  let timer, ctrl;
  const muat = async () => {
    ctrl?.abort(); ctrl = new AbortController();
    const p = new URLSearchParams(new FormData(form));
    for (const [k, v] of [...p]) if (!v) p.delete(k);
    const url = new URL(location.pathname + (p.toString() ? '?' + p : ''), location.origin);
    document.getElementById('hasil').classList.add('opacity-60');
    try {
      const r = await fetch(url, { signal: ctrl.signal, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      if (!r.ok) throw new Error(r.status);
      const doc = new DOMParser().parseFromString(await r.text(), 'text/html');
      document.getElementById('hasil').replaceWith(doc.getElementById('hasil'));
      document.getElementById('tabs').replaceWith(doc.getElementById('tabs'));
      history.replaceState(null, '', url);
    } catch (e) {
      if (e.name !== 'AbortError') form.submit();
    }
  };
  q.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(muat, 300); });
  form.addEventListener('submit', e => { e.preventDefault(); clearTimeout(timer); muat(); });
});
</script>
@endsection