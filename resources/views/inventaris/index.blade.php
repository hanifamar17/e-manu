@extends('layouts.app')
@section('title', 'Admin: Database Sarpras')

@section('content')
@php
    $tab = 'shrink-0 inline-flex items-center mono text-[11px] font-bold uppercase tracking-widest px-4 py-2 border border-[var(--line)]';
    $on = 'bg-[var(--ink)] text-[var(--paper)]';
    $off = 'text-[var(--ink-soft)]';
@endphp
<div class="max-w-5xl mx-auto px-5 py-9 sm:py-14 text-[var(--ink)]">
  <a href="{{ route('repository') }}" class="back-link inline-flex items-center gap-2 text-sm font-semibold text-[var(--ink-soft)] hover:text-[var(--ink)]">
    <i class="ph ph-arrow-left"></i> Kembali
  </a>

  <div class="flex flex-wrap items-end justify-between gap-3 mt-8 mb-6">
    <div>
      <h1 class="page-title text-2xl font-bold m-0">Database Sarpras</h1>
      <p class="text-sm text-[var(--ink-soft)] mt-1 mb-0">Master data barang dan ketersediaan stok.</p>
    </div>
    <div class="flex gap-3 text-sm">
      @if (auth()->user()->hasRole('admin'))
        <a class="underline" href="{{ route('admin.impor.form') }}">Impor</a>
      @endif
      <a class="px-3 py-1.5 bg-[var(--ink)] text-[var(--paper)] font-semibold" href="{{ route('inventaris.create') }}">+ Tambah Barang</a>
    </div>
  </div>

  @include('partials.flash')

  <div class="flex gap-2 mb-6 overflow-x-auto pb-1">
    <a href="{{ route('inventaris.index', array_filter(['q' => $q])) }}" class="{{ $tab }} {{ ! $bidang ? $on : $off }}">Semua</a>
    @foreach (\App\Models\Inventaris::BIDANG_LABEL as $slug => $label)
      <a href="{{ route('inventaris.index', array_filter(['bidang' => $slug, 'q' => $q])) }}" class="{{ $tab }} {{ $bidang === $slug ? $on : $off }}">{{ $label }}</a>
    @endforeach
  </div>

  <form method="GET" action="{{ route('inventaris.index') }}" class="flex items-end gap-3 max-w-sm mb-8">
    @if ($bidang)<input type="hidden" name="bidang" value="{{ $bidang }}">@endif
    <input name="q" value="{{ $q }}" placeholder="Cari nama, merk, lokasi, kode..."
      class="flex-1 py-2.5 px-0 text-sm bg-transparent border-0 border-b border-[var(--line)] focus:outline-none focus:ring-0 focus:border-[var(--accent)] rounded-none text-[var(--ink)]">
    <button class="px-3 py-1.5 text-xs font-semibold border border-[var(--line)]">Cari</button>
  </form>

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
        @forelse ($items as $r)
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
              <a class="underline text-xs mr-3" href="{{ route('inventaris.edit', $r) }}">Edit</a>
              <a class="text-xs text-red-600" href="{{ route('inventaris.hapus', $r) }}">Hapus</a>
            </td>
          </tr>
        @empty
          <tr><td colspan="4" class="py-10 text-center text-[var(--ink-faint)]">Tidak ada data ditemukan.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="mt-6">{{ $items->links() }}</div>
</div>
@endsection