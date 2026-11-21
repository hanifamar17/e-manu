@extends('layouts.app')
@section('title', $mode === 'acc' ? 'ACC Izin' : 'Riwayat Perizinan')

@section('content')
@php
    $statuses = ['menunggu_acc' => 'Menunggu ACC', 'aktif' => 'Aktif', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak', 'dibatalkan' => 'Dibatalkan'];
    $fld = 'py-2.5 px-0 text-sm bg-transparent border-0 border-b border-[var(--line)] focus:outline-none focus:ring-0 focus:border-[var(--accent)] rounded-none text-[var(--ink)]';
@endphp
<div class="max-w-5xl mx-auto px-5 py-9 sm:py-14 text-[var(--ink)]">
  <a data-lock href="{{ route('perizinan.index') }}" class="back-link inline-flex items-center gap-2 text-sm font-semibold text-[var(--ink-soft)] hover:text-[var(--ink)] cursor-pointer touch-manipulation select-none transition active:scale-95">
    <i class="ph ph-arrow-left"></i> Kembali
  </a>

  <div class="flex flex-wrap items-end justify-between gap-3 mt-8 mb-6">
    <div>
      <h1 class="page-title text-2xl font-bold m-0">{{ $mode === 'acc' ? 'ACC Izin' : 'Riwayat Perizinan' }}</h1>
      <p class="text-sm text-[var(--ink-soft)] mt-1 mb-0">
        {{ $mode === 'acc' ? 'Izin yang menunggu persetujuan wali kelas.' : 'Catatan izin siswa.' }}
        Total: <span id="total" class="font-semibold text-[var(--ink)]">{{ $items->total() }}</span>.
      </p>
    </div>
  </div>

  @include('partials.flash')

  <form id="formCari" method="GET" autocomplete="off" class="flex max-w-sm gap-3 mb-2">
  <input id="q" name="q" value="{{ $cari }}" placeholder="Cari nama, NISN, atau ID izin..." enterkeyhint="search" maxlength="100" class="flex-1 min-w-0 {{ $fld }}">
  @if ($mode === 'riwayat')
    <select id="status" name="status" class="{{ $fld }} cursor-pointer">
      <option value="">Semua status</option>
      @foreach ($statuses as $k => $l)
        <option value="{{ $k }}" @selected($status === $k)>{{ $l }}</option>
      @endforeach
    </select>
  @endif
</form>

<div id="hasil" class="transition-opacity">
  <p class="text-xs text-[var(--ink-faint)] m-0 mb-6 min-h-[1rem]">{{ ($cari !== '' || $status !== '') ? 'Ditemukan '.$items->total().' data' : '' }}</p>
  
  @unless ($items->isEmpty())
  <div class="overflow-x-auto">
    <table class="w-full text-sm whitespace-nowrap">
      <thead>
        <tr>
          <th class="text-left py-2 eyebrow pr-6">Siswa &amp; Alasan</th>
          <th class="text-left py-2 eyebrow pr-6 hidden sm:table-cell">Waktu</th>
          <th class="text-left py-2 eyebrow pr-6">Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach ($items as $p)
        <tr style="border-top:1px solid var(--line)">
          <td class="py-3 pr-6 whitespace-normal min-w-[220px]">
            <div class="font-semibold">{{ $p->ringkas() }}</div>
            <div class="mono text-[11px] text-[var(--ink-soft)]">{{ $p->id_perizinan }} &middot; {{ $p->daftarKelas() }} &middot; {{ $p->durasi }}</div>
            <div class="text-[12px] text-[var(--ink-soft)]">{{ $p->alasan }}</div>
            <div class="mono text-[11px] text-[var(--ink-soft)] sm:hidden">
              {{ $p->tanggal_mulai->translatedFormat('j M Y') }} {{ substr($p->jam_mulai, 0, 5) }} &rarr; {{ $p->tanggal_selesai->translatedFormat('j M Y') }} {{ substr($p->jam_selesai, 0, 5) }}
            </div>
          </td>
          <td class="py-3 pr-6 hidden sm:table-cell mono text-[12px]">
            {{ $p->tanggal_mulai->translatedFormat('j M Y') }} {{ substr($p->jam_mulai, 0, 5) }}<br>
            <span class="text-[var(--ink-soft)]">&rarr; {{ $p->tanggal_selesai->translatedFormat('j M Y') }} {{ substr($p->jam_selesai, 0, 5) }}</span>
          </td>
          <td class="py-3 pr-6">@include('partials.pz-badge', ['s' => $p->status])</td>
          <td class="py-3 text-right">
            <a data-lock class="underline text-xs cursor-pointer" href="{{ route('perizinan.show', $p) }}">Detail</a>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endunless

  @if ($items->isEmpty())
    <div class="py-10 text-center text-[var(--ink-faint)]">{{ $mode === 'acc' ? 'Tidak ada izin menunggu ACC.' : 'Tidak ada data ditemukan.' }}</div>
  @endif

</div>

  <div class="mt-6 flex items-center justify-between text-xs text-[var(--ink-soft)]">
    <div>Hal <span class="text-[var(--ink)]">{{ $items->currentPage() }}</span>/<span class="text-[var(--ink)]">{{ max(1, $items->lastPage()) }}</span></div>
    <div class="flex gap-4">
      @if ($items->onFirstPage())
        <span class="uppercase opacity-30 select-none">&larr; Prev</span>
      @else
        <a data-lock href="{{ $items->previousPageUrl() }}" class="uppercase hover:text-[var(--ink)] cursor-pointer touch-manipulation select-none">&larr; Prev</a>
      @endif
      @if ($items->hasMorePages())
        <a data-lock href="{{ $items->nextPageUrl() }}" class="uppercase hover:text-[var(--ink)] cursor-pointer touch-manipulation select-none">Next &rarr;</a>
      @else
        <span class="uppercase opacity-30 select-none">Next &rarr;</span>
      @endif
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
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
      document.getElementById('total').textContent = doc.getElementById('total').textContent;
      history.replaceState(null, '', url);
    } catch (e) {
      if (e.name !== 'AbortError') form.submit();   // gagal: reload biasa
    }
  };

  q.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(muat, 300); });
  form.addEventListener('submit', e => { e.preventDefault(); clearTimeout(timer); muat(); });
  document.getElementById('status')?.addEventListener('change', muat);
});
</script>
@include('partials.lock')
@endsection