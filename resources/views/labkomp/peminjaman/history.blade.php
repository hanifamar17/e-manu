@extends('layouts.app')
@section('title', 'Lab Komp: Riwayat Peminjaman')

@section('content')
<div class="max-w-5xl mx-auto px-5 py-9 sm:py-14 text-[var(--ink)]">
  <a href="{{ route('peminjaman_invlabkomp.peminjaman_landing') }}" class="back-link inline-flex items-center gap-2 text-sm font-semibold text-[var(--ink-soft)] hover:text-[var(--ink)]">
    <i class="ph ph-arrow-left"></i> Kembali
  </a>

  <div class="flex flex-wrap items-end justify-between gap-3 mt-8 mb-6">
    <div>
      <h1 class="page-title text-2xl font-bold m-0">Riwayat Peminjaman</h1>
      <p class="text-sm text-[var(--ink-soft)] mt-1 mb-0">Catatan peminjaman barang Lab Komputer. Total: <span id="totalCount" class="font-semibold text-[var(--ink)]">{{ $loans->count() }}</span>.</p>
    </div>
  </div>

  @include('partials.flash')

  <form onsubmit="return false" class="flex items-end gap-3 max-w-sm mb-2">
    <input id="filter" placeholder="Cari kode, barang, atau peminjam..."
      class="flex-1 py-2.5 px-0 text-sm bg-transparent border-0 border-b border-[var(--line)] focus:outline-none focus:ring-0 focus:border-[var(--accent)] rounded-none text-[var(--ink)]">
    <button type="button" onclick="document.getElementById('filter').dispatchEvent(new Event('input'))" class="px-3 py-1.5 text-xs font-semibold border border-[var(--line)]">Cari</button>
  </form>
  <div id="searchCount" class="text-xs text-[var(--ink-faint)] mb-6 hidden"></div>

  <div class="overflow-x-auto">
    <table class="w-full text-sm whitespace-nowrap">
      <thead>
        <tr>
          <th class="text-left py-2 eyebrow pr-6">Barang &amp; Peminjam</th>
          <th class="text-left py-2 eyebrow pr-6 hidden sm:table-cell">Tanggal</th>
          <th class="text-left py-2 eyebrow pr-6">Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody id="tableBody">
        @foreach ($loans as $l)
        <tr data-row style="border-top:1px solid var(--line)">
          <td class="py-3 pr-6">
            <div class="font-semibold">{{ $l->items->map(fn ($i) => $i->nama_barang.' ×'.$i->qty)->implode(', ') }}</div>
            <div class="mono text-[11px] text-[var(--ink-soft)]">{{ $l->kode }} &middot; {{ $l->nama_peminjam }}</div>
            <div class="text-[12px] text-[var(--ink-soft)] sm:hidden">{{ $l->tgl_pinjam->format('d-m-Y') }}</div>
          </td>
          <td class="py-3 pr-6 hidden sm:table-cell">{{ $l->tgl_pinjam->format('d-m-Y') }}</td>
          <td class="py-3 pr-6">
            <div class="mono text-[10px] font-bold uppercase tracking-wider {{ $l->status === 'dipinjam' ? 'text-red-600' : 'text-[var(--accent)]' }}">{{ $l->status }}</div>
          </td>
          <td class="py-3 text-right">
            <a class="underline text-xs" href="{{ route('peminjaman_invlabkomp.loan_detail', $l->kode) }}">Detail</a>
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
      <button id="prevBtn" class="uppercase hover:text-[var(--ink)] disabled:opacity-30 disabled:pointer-events-none cursor-pointer">&larr; Prev</button>
      <button id="nextBtn" class="uppercase hover:text-[var(--ink)] disabled:opacity-30 disabled:pointer-events-none cursor-pointer">Next &rarr;</button>
    </div>
  </div>
</div>
@include('labkomp.partials.tabel-js', ['perPage' => 20])
@endsection