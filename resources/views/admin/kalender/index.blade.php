@extends('layouts.app')
@section('title', 'Admin: Kalender Akademik')

@section('content')
<div class="max-w-4xl mx-auto px-5 py-9 sm:py-14 text-[var(--ink)]">
  <a data-lock href="{{ route('admin.home') }}" class="back-link inline-flex items-center gap-2 text-sm font-semibold text-[var(--ink-soft)] hover:text-[var(--ink)] cursor-pointer touch-manipulation select-none transition active:scale-95">
    <i class="ph ph-arrow-left"></i> Kembali
  </a>

  <div class="flex flex-wrap items-end justify-between gap-3 mt-8 mb-6">
    <h1 class="page-title text-2xl font-bold m-0">Kalender Akademik</h1>
    <div class="flex items-center gap-3 text-sm">
      <a data-lock class="inline-block px-4 py-2.5 text-sm font-semibold border border-[var(--line)] text-[var(--ink)] cursor-pointer touch-manipulation select-none transition hover:bg-[var(--ink)] hover:text-[var(--paper)] hover:border-[var(--ink)] active:scale-[0.97] active:bg-[var(--ink)] active:text-[var(--paper)]" href="{{ route('admin.impor.form') }}">Impor</a>
      <a data-lock class="inline-block px-4 py-2.5 bg-[var(--ink)] text-[var(--paper)] font-semibold cursor-pointer touch-manipulation select-none transition hover:bg-[var(--accent)] active:scale-[0.97] active:bg-[var(--accent)]" href="{{ route('admin.kalender.create') }}">+ Tambah</a>
    </div>
  </div>

  @include('partials.flash')

  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr>
          <th class="text-left py-2 eyebrow pr-4">Tanggal</th>
          <th class="text-left py-2 eyebrow pr-4">Kategori</th>
          <th class="text-left py-2 eyebrow">Keterangan</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse ($items as $k)
          <tr style="border-top:1px solid var(--line)">
            <td class="mono text-xs py-3 pr-4 whitespace-nowrap">
              {{ $k->tanggal_mulai->format('d/m/Y') }}@if (! $k->tanggal_selesai->eq($k->tanggal_mulai)) &ndash; {{ $k->tanggal_selesai->format('d/m/Y') }}@endif
            </td>
            <td class="mono text-xs py-3 pr-4 uppercase">{{ $k->kategori }}</td>
            <td class="py-3">{{ $k->keterangan }}</td>
            <td class="py-3 text-right whitespace-nowrap">
              <a data-lock class="inline-block py-2 px-1 underline text-xs mr-2 cursor-pointer touch-manipulation select-none transition hover:opacity-70 active:scale-95" href="{{ route('admin.kalender.edit', $k) }}">Edit</a>
              <form data-lock data-confirm="Hapus?" method="POST" action="{{ route('admin.kalender.destroy', $k) }}" class="inline">
                @csrf @method('DELETE')
                <button type="submit" class="inline-block py-2 px-1 text-xs text-red-600 cursor-pointer touch-manipulation select-none transition hover:underline active:scale-95">Hapus</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="4" class="py-10 text-center text-[var(--ink-faint)]">Belum ada data.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-6 [&_a]:cursor-pointer [&_a]:touch-manipulation [&_a]:select-none [&_a]:transition [&_a]:active:scale-95">{{ $items->links() }}</div>
</div>

<script>
// data-lock: sekali klik -> terkunci (cegah klik ganda). Pakai aria-busy + pointer-events-none, BUKAN disabled
// (disabled sebelum FormData bikin field/tombol tidak ikut terkirim). Dibuka lagi saat pageshow (tombol Back).
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
    const b = f.querySelector('button');
    if (b && b.hasAttribute('aria-busy')) { e.preventDefault(); return; }
    const msg = f.dataset.confirm;
    if (msg && !confirm(msg)) { e.preventDefault(); return; }   // batal -> jangan kunci
    if (b) lock(b);
  }));

  window.addEventListener('pageshow', unlockAll);
});
</script>
@endsection