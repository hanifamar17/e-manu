@extends('layouts.app')
@section('title', 'Lab Komp: Hapus Inventaris')

@section('content')
<div class="max-w-4xl mx-auto px-5 pt-12 pb-20 text-[var(--ink)]">
  <a href="{{ route('inventory_labkomp.inventory_list') }}" class="back-link inline-flex items-center gap-2 text-sm font-semibold text-[var(--ink-soft)] hover:text-[var(--ink)]">
    <i class="ph ph-arrow-left"></i> Kembali
  </a>
  <h1 class="page-title text-2xl font-bold mt-8 mb-2">Penghapusan Data</h1>
  <p class="text-sm text-[var(--ink-soft)] mt-0 mb-8">Konfirmasi terakhir sebelum data dihapus permanen.</p>

  <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
    <div class="lg:col-span-7 space-y-8">
      <div class="border-l-2 border-[var(--ink)] pl-4 py-1">
        <h3 class="text-[15px] font-semibold m-0">Apakah Anda yakin?</h3>
        <p class="text-[14px] text-[var(--ink-soft)] mt-1 mb-0">Data hilang permanen. Riwayat peminjaman lama tetap tersimpan (nama barang tercatat).</p>
      </div>

      @if ($dipinjam)
        <p class="text-sm text-red-700 m-0">Barang ini masih ada di peminjaman aktif. Selesaikan pengembalian dulu.</p>
      @endif

      <div class="flex flex-col sm:flex-row gap-4">
        <form data-lock method="POST" action="{{ route('inventory_labkomp.inventory_destroy', $item) }}">
          @csrf @method('DELETE')
          <button type="submit" @disabled($dipinjam)
            class="w-full sm:w-auto inline-block px-6 py-3 text-[12px] font-bold uppercase bg-[var(--ink)] text-[var(--paper)] cursor-pointer touch-manipulation select-none transition hover:bg-[var(--accent)] active:scale-[0.97] active:bg-[var(--accent)] disabled:opacity-40 disabled:cursor-not-allowed disabled:pointer-events-none">
            Ya, Hapus Sekarang
          </button>
        </form>
        <a data-lock href="{{ route('inventory_labkomp.inventory_list') }}"
           class="inline-flex items-center justify-center text-[13px] font-semibold text-[var(--ink-soft)] cursor-pointer touch-manipulation select-none transition hover:text-[var(--ink)] active:scale-95 px-4">
          Batalkan
        </a>
      </div>
    </div>

    <div class="lg:col-span-5 space-y-6 border-t lg:border-t-0 border-[var(--line)] pt-8 lg:pt-0">
      <div>
        <span class="block mono text-[11px] font-bold text-[var(--ink-faint)] uppercase tracking-wider mb-1">Kode</span>
        <span class="mono text-[14px] font-semibold">{{ $item->kode }}</span>
      </div>
      <div>
        <span class="block mono text-[11px] font-bold text-[var(--ink-faint)] uppercase tracking-wider mb-1">Nama Barang</span>
        <p class="text-[15px] font-semibold m-0">{{ $item->nama_barang }}</p>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <span class="block mono text-[11px] font-bold text-[var(--ink-faint)] uppercase tracking-wider mb-1">Stok</span>
          <p class="text-[14px] font-semibold m-0">{{ $item->stok }} {{ $item->satuan }}</p>
        </div>
        <div>
          <span class="block mono text-[11px] font-bold text-[var(--ink-faint)] uppercase tracking-wider mb-1">Kondisi</span>
          <p class="text-[14px] font-semibold m-0">{{ $item->kondisi }}</p>
        </div>
      </div>
      <div class="pt-4 border-t border-[var(--line)]">
        <span class="block mono text-[11px] font-bold text-[var(--ink-faint)] uppercase tracking-wider mb-1">Lokasi</span>
        <p class="text-[14px] text-[var(--ink-soft)] italic m-0">{{ $item->lokasi ?: '-' }}</p>
      </div>
    </div>
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

  document.querySelectorAll('form[data-lock]').forEach(f => f.addEventListener('submit', e => {
    const b = f.querySelector('button[type="submit"], button:not([type])');
    if (!b || b.disabled || b.hasAttribute('aria-busy')) { e.preventDefault(); return; }
    lock(b);
  }));

  window.addEventListener('pageshow', unlockAll);
});
</script>
@endsection